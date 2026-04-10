/**
 * Gestion du dropdown de notifications pour les réservations en attente
 */
(function() {
    'use strict';

    const notificationToggle = document.getElementById('notification-toggle');
    const notificationDropdown = document.getElementById('notification-dropdown');
    const notificationList = document.getElementById('notification-list');
    const notificationCount = document.getElementById('notification-count');
    
    // Flag pour empêcher la fermeture pendant le traitement d'une action
    let isProcessingAction = false;
    
    // Compteur de notifications précédent pour détecter les changements
    let previousCount = 0;
    let isFirstCheck = true; // Flag pour la première vérification
    
    // Préférence son de notification (localStorage)
    let soundEnabled = localStorage.getItem('notificationSoundEnabled') !== 'false'; // true par défaut

    if (!notificationToggle || !notificationDropdown) {
        return; // Pas de notifications sur cette page
    }
    
    // Polling optimisé toutes les 10 secondes pour notifications quasi temps réel
    setInterval(function() {
        checkForNewReservations();
    }, 10000); // 10 secondes
    
    // Vérification initiale après 2 secondes pour initialiser le compteur
    setTimeout(function() {
        checkForNewReservations();
    }, 2000);
    
    // Initialiser l'état du bouton son au chargement
    setTimeout(function() {
        updateSoundButtonState();
    }, 100);

    // Toggle dropdown au clic sur le bouton
    notificationToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        const isVisible = notificationDropdown.style.display === 'block';
        
        if (isVisible) {
            notificationDropdown.style.display = 'none';
        } else {
            notificationDropdown.style.display = 'block';
            loadPendingReservations();
        }
    });

    // Fermer le dropdown si on clique ailleurs
    document.addEventListener('click', function(e) {
        // Ne pas fermer si on clique sur le bouton (géré par le toggle) ou dans le dropdown
        // Ne pas fermer non plus si une action est en cours de traitement
        // Ne pas fermer si on clique sur le bouton de son
        const soundButton = document.getElementById('toggle-notification-sound');
        const clickedOnSoundButton = soundButton && (e.target === soundButton || soundButton.contains(e.target));
        
        if (!isProcessingAction && 
            !notificationDropdown.contains(e.target) && 
            !notificationToggle.contains(e.target) &&
            !clickedOnSoundButton) {
            notificationDropdown.style.display = 'none';
        }
    });

    // Charger les réservations en attente
    function loadPendingReservations() {
        notificationList.innerHTML = '<div class="notification-loading"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';

        fetch('?page=get-pending-reservations')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.reservations) {
                    displayReservations(data.reservations);
                } else {
                    notificationList.innerHTML = '<div class="notification-empty"><i class="fas fa-check-circle"></i><p>Aucune réservation en attente</p></div>';
                }
            })
            .catch(error => {
                console.error('Erreur chargement notifications:', error);
                notificationList.innerHTML = '<div class="notification-empty"><p>Erreur de chargement</p></div>';
            });
    }

    // Afficher les réservations
    function displayReservations(reservations) {
        if (reservations.length === 0) {
            notificationList.innerHTML = '<div class="notification-empty"><i class="fas fa-check-circle"></i><p>Aucune réservation en attente</p></div>';
            updateBadgeCount(0);
            return;
        }

        const now = new Date();
        let html = '';
        
        reservations.forEach(function(r) {
            const date = new Date(r.reservation_date);
            const dateStr = date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
            const time = r.reservation_time.substring(0, 5);
            
            // Vérifier si la réservation est passée
            const reservationDateTime = new Date(r.reservation_date + ' ' + r.reservation_time);
            const isPast = reservationDateTime < now;
            const pastClass = isPast ? ' past-reservation' : '';
            
            html += `
                <div class="notification-item${pastClass}" data-id="${r.id}">
                    <div class="notification-item-header">
                        <span class="notification-item-time">
                            <i class="fas fa-clock"></i> ${dateStr} à ${time}
                        </span>
                        <span class="notification-item-party">
                            <i class="fas fa-users"></i> ${r.party_size} pers.
                        </span>
                    </div>
                    <div class="notification-item-name">
                        <i class="fas fa-user"></i> ${escapeHtml(r.customer_name)}
                    </div>
                    <div class="notification-item-phone">
                        <i class="fas fa-phone"></i> ${escapeHtml(r.customer_phone)}
                    </div>
                    <div class="notification-item-actions">
                        <button class="btn success btn-confirm-notif" data-id="${r.id}">
                            <i class="fas fa-check"></i> Confirmer
                        </button>
                        <button class="btn danger btn-cancel-notif" data-id="${r.id}">
                            <i class="fas fa-times"></i> Refuser
                        </button>
                    </div>
                </div>
            `;
        });

        notificationList.innerHTML = html;

        // Attacher les événements aux boutons
        attachActionButtons();
    }

    // Attacher les événements aux boutons d'action
    function attachActionButtons() {
        // Boutons confirmer
        document.querySelectorAll('.btn-confirm-notif').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = btn.dataset.id;
                confirmReservation(id);
            });
        });

        // Boutons refuser
        document.querySelectorAll('.btn-cancel-notif').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = btn.dataset.id;
                cancelReservation(id);
            });
        });
    }

    // Confirmer une réservation
    function confirmReservation(id) {
        isProcessingAction = true; // Empêcher la fermeture du dropdown
        
        if (typeof Swal === 'undefined') {
            if (!confirm('Confirmer cette réservation ?')) {
                isProcessingAction = false;
                return;
            }
            updateReservationStatus(id, 'confirmed');
            return;
        }

        // Récupérer les paramètres de réservation depuis le serveur
        fetch('?page=reservation-get-settings')
            .then(r => r.json())
            .then(settingsData => {
                if (!settingsData.success) {
                    throw new Error('Impossible de récupérer les paramètres');
                }
                
                const settings = settingsData.settings;
                const assignTableEnabled = settings.booking_assign_table && !settings.booking_auto_confirm;

                if (assignTableEnabled) {
                    // Charger les tables disponibles
                    return fetch('?page=reservation-get-tables')
                        .then(r => r.json())
                        .then(data => ({ settings, tablesData: data }));
                } else {
                    // Pas d'attribution de table, confirmation simple
                    return { settings, tablesData: null };
                }
            })
            .then(({ settings, tablesData }) => {
                const assignTableEnabled = settings.booking_assign_table && !settings.booking_auto_confirm;
                
                if (assignTableEnabled && tablesData && tablesData.success && tablesData.tables && tablesData.tables.length > 0) {
                    // Créer les options du select
                    let tableOptions = '<option value="">Aucune table (confirmation sans attribution)</option>';
                    tablesData.tables.forEach(table => {
                        let label = `${table.floor_name} - Table ${table.table_number} (${table.capacity_min}-${table.capacity_max} pers.)`;
                        
                        // Ajouter les informations de réservations existantes
                        if (table.reservations && table.reservations.length > 0) {
                            const times = table.reservations.map(r => r.time).join(', ');
                            label += ` ⚠️ Déjà réservée : ${times}`;
                        }
                        
                        tableOptions += `<option value="${table.id}">${label}</option>`;
                    });
                    
                    Swal.fire({
                        title: 'Confirmer cette réservation',
                        html: `
                            <div style="text-align: left; margin-bottom: 15px;">
                                <label for="table-select-notif" style="display: block; margin-bottom: 10px; font-weight: 600; color: #1f2937; font-size: 0.95rem;">Attribuer une table :</label>
                                <select id="table-select-notif" style="width: 100%; padding: 10px 12px; border: 2px solid #d1d5db; border-radius: 6px; background: #ffffff; color: #1f2937; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; outline: none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                                    ${tableOptions}
                                </select>
                            </div>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        confirmButtonText: '<i class="fas fa-check"></i> Confirmer',
                        cancelButtonText: 'Annuler',
                        preConfirm: () => {
                            return document.getElementById('table-select-notif').value;
                        }
                    }).then(result => {
                        if (result.isConfirmed) {
                            updateReservationStatus(id, 'confirmed', { table_id: result.value || null });
                        } else {
                            isProcessingAction = false;
                        }
                    });
                } else {
                    // Confirmation simple sans attribution de table
                    Swal.fire({
                        title: 'Confirmer la réservation ?',
                        text: 'Le client sera considéré comme attendu.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: '<i class="fas fa-check"></i> Confirmer',
                        cancelButtonText: 'Annuler'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            updateReservationStatus(id, 'confirmed');
                        } else {
                            isProcessingAction = false;
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Erreur lors de la confirmation:', error);
                isProcessingAction = false;
                Swal.fire({
                    title: 'Erreur',
                    text: 'Impossible de charger les paramètres de réservation.',
                    icon: 'error'
                });
            });
    }

    // Refuser une réservation
    function cancelReservation(id) {
        isProcessingAction = true; // Empêcher la fermeture du dropdown
        
        if (typeof Swal === 'undefined') {
            const reason = prompt('Raison du refus (optionnel) :');
            if (reason === null) {
                isProcessingAction = false;
                return;
            }
            updateReservationStatus(id, 'cancelled', { cancelled_reason: reason });
            return;
        }

        Swal.fire({
            title: 'Refuser cette réservation ?',
            input: 'textarea',
            inputLabel: 'Raison du refus (optionnel)',
            inputPlaceholder: 'Ex: Complet, fermeture exceptionnelle...',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-times"></i> Refuser',
            cancelButtonText: 'Annuler'
        }).then(function(result) {
            if (result.isConfirmed) {
                updateReservationStatus(id, 'cancelled', { cancelled_reason: result.value || '' });
            } else {
                isProcessingAction = false; // Réactiver la fermeture si annulé
            }
        });
    }

    // Mettre à jour le statut d'une réservation via AJAX
    function updateReservationStatus(id, status, extra) {
        extra = extra || {};
        
        // Récupérer le token CSRF à chaque requête (important pour éviter les erreurs après la première action)
        const csrfTokenElement = document.getElementById('csrf-token');
        const csrfToken = csrfTokenElement ? csrfTokenElement.value : '';
        
        if (!csrfToken) {
            showToast('Erreur: Token CSRF manquant. Rechargez la page.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('id', id);
        formData.append('status', status);

        if (extra.cancelled_reason) {
            formData.append('cancelled_reason', extra.cancelled_reason);
        }
        
        if (extra.table_id) {
            formData.append('table_id', extra.table_id);
        }

        fetch('?page=reservation-update-status', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                
                // Mettre à jour le token CSRF si le serveur en renvoie un nouveau
                if (data.new_csrf_token && csrfTokenElement) {
                    csrfTokenElement.value = data.new_csrf_token;
                }
                
                // Retirer l'élément de la liste
                const item = document.querySelector(`.notification-item[data-id="${id}"]`);
                if (item) {
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(-20px)';
                    setTimeout(function() {
                        item.remove();
                        
                        // Vérifier s'il reste des réservations
                        const remainingItems = document.querySelectorAll('.notification-item');
                        if (remainingItems.length === 0) {
                            notificationList.innerHTML = '<div class="notification-empty"><i class="fas fa-check-circle"></i><p>Aucune réservation en attente</p></div>';
                            updateBadgeCount(0);
                            
                            // Fermer le dropdown automatiquement après un court délai si plus aucune réservation
                            setTimeout(function() {
                                if (notificationDropdown) {
                                    notificationDropdown.style.display = 'none';
                                }
                                isProcessingAction = false; // Réactiver après fermeture
                            }, 1500);
                        } else {
                            updateBadgeCount(remainingItems.length);
                            // Garder le dropdown ouvert pour traiter les autres réservations
                            isProcessingAction = false; // Réactiver pour permettre les prochaines actions
                        }
                    }, 300);
                }
                
                // Si c'est une confirmation, mettre à jour l'interface de la page reservations
                if (status === 'confirmed') {
                    // Vérifier si nous sommes sur la page reservations et si la fonction loadReservationsForDate existe
                    if (typeof loadReservationsForDate === 'function') {
                        const dateInput = document.getElementById('dashboard-date');
                        if (dateInput) {
                            loadReservationsForDate(dateInput.value);
                        }
                    }
                }
                
                // IMPORTANT: Forcer une vérification immédiate pour réinitialiser le compteur
                setTimeout(function() {
                    checkForNewReservations();
                }, 1000);
            } else {
                showToast(data.message || 'Erreur', 'error');
                isProcessingAction = false; // Réactiver en cas d'erreur
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur de communication avec le serveur.', 'error');
            isProcessingAction = false; // Réactiver en cas d'erreur réseau
        });
    }

    // Mettre à jour le compteur de notifications
    function updateBadgeCount(count) {
        if (notificationCount) {
            notificationCount.textContent = count;
            
            // Cacher le bouton si count = 0
            if (count === 0) {
                if (notificationToggle) {
                    notificationToggle.style.display = 'none';
                }
                if (notificationDropdown) {
                    notificationDropdown.style.display = 'none';
                }
            }
        }
    }

    // Toast notification
    function showToast(message, type) {
        type = type || 'info';
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type === 'error' ? 'error' : 'success',
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            return;
        }

        // Fallback simple
        const toast = document.createElement('div');
        toast.className = 'toast-message toast-' + type;
        toast.textContent = message;
        toast.style.cssText = 'position:fixed;top:20px;right:20px;padding:14px 20px;border-radius:8px;color:#fff;font-size:0.9rem;z-index:99999;transition:opacity 0.3s;max-width:400px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
        toast.style.background = type === 'error' ? '#ef4444' : '#10b981';
        document.body.appendChild(toast);
        
        setTimeout(function() {
            toast.style.opacity = '0';
            setTimeout(function() { toast.remove(); }, 300);
        }, 3000);
    }

    // Échapper le HTML pour éviter les XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Vérifier les nouvelles réservations avec notification sonore et visuelle
    function checkForNewReservations() {
        fetch('?page=get-pending-reservations')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.reservations) {
                    const currentCount = data.reservations.length;
                    
                    // Nouvelle réservation détectée (mais pas lors de la première vérification)
                    if (!isFirstCheck && currentCount > previousCount) {
                        const newCount = currentCount - previousCount;
                        
                        // Notification visuelle avec icône
                        showToast(`🔔 ${newCount} nouvelle${newCount > 1 ? 's' : ''} réservation${newCount > 1 ? 's' : ''} !`, 'success');
                        
                        // Notification sonore
                        playNotificationSound();
                        
                        // Afficher le bouton de notification s'il était caché
                        if (notificationToggle && notificationToggle.style.display === 'none') {
                            notificationToggle.style.display = '';
                        }
                    }
                    
                    // Marquer que la première vérification est terminée
                    if (isFirstCheck) {
                        isFirstCheck = false;
                    }
                    
                    // Mettre à jour le compteur
                    previousCount = currentCount;
                    updateBadgeCount(currentCount);
                    
                    // Si le dropdown est ouvert, rafraîchir la liste
                    if (notificationDropdown && notificationDropdown.style.display === 'block') {
                        displayReservations(data.reservations);
                    }
                }
            })
            .catch(error => {
                console.error('Erreur vérification nouvelles réservations:', error);
            });
    }

    // Jouer un son de notification (si activé)
    function playNotificationSound() {
        // Vérifier si le son est activé
        if (!soundEnabled) {
            return;
        }
        
        try {
            // Créer un son simple avec Web Audio API
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800; // Fréquence en Hz
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        } catch (error) {
            console.log('Son de notification non disponible');
        }
    }
    
    // Basculer le son de notification
    function toggleNotificationSound(event) {
        // Empêcher la propagation de l'événement pour ne pas fermer le dropdown
        if (event) {
            event.stopPropagation();
        }
        
        soundEnabled = !soundEnabled;
        localStorage.setItem('notificationSoundEnabled', soundEnabled);
        updateSoundButtonState();
        
        // Feedback visuel
        const message = soundEnabled ? 'Son activé 🔔' : 'Son désactivé 🔕';
        showToast(message, 'info');
    }
    
    // Mettre à jour l'état du bouton son
    function updateSoundButtonState() {
        const soundButton = document.getElementById('toggle-notification-sound');
        if (soundButton) {
            soundButton.innerHTML = soundEnabled 
                ? '<i class="fas fa-volume-up"></i>' 
                : '<i class="fas fa-volume-mute"></i>';
            soundButton.className = soundEnabled ? 'notification-sound-btn active' : 'notification-sound-btn';
            soundButton.title = soundEnabled ? 'Son activé - Cliquer pour désactiver' : 'Son désactivé - Cliquer pour activer';
        }
    }
    
    // Exposer la fonction globalement pour qu'elle soit accessible depuis le HTML
    window.toggleNotificationSound = toggleNotificationSound;
})();
