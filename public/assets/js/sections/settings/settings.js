document.addEventListener('DOMContentLoaded', function() {
    // ==================== GESTION DU MENU MOBILE ====================
    const menuToggle = document.querySelector('.settings-mobile-toggle');
    const menuContent = document.getElementById('settings-mobile-content');
    
    if (menuToggle && menuContent) {
        menuToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            menuContent.classList.toggle('show');
            
            // Changer l'icône
            const menuIcon = this.querySelector('.settings-menu-icon');
            if (menuIcon) {
                menuIcon.textContent = isExpanded ? '☰' : '✕';
            }
        });
        
        // Fermer le menu quand on clique sur un lien
        menuContent.addEventListener('click', function(event) {
            if (event.target.tagName === 'A') {
                menuToggle.setAttribute('aria-expanded', 'false');
                this.classList.remove('show');
                
                const menuIcon = menuToggle.querySelector('.settings-menu-icon');
                if (menuIcon) {
                    menuIcon.textContent = '☰';
                }
                
                // Mettre à jour le texte du bouton avec la section sélectionnée
                const linkText = event.target.textContent;
                const menuText = menuToggle.querySelector('.settings-menu-text');
                if (menuText) {
                    menuText.textContent = linkText;
                }
            }
        });
        
        // Fermer le menu en cliquant à l'extérieur
        document.addEventListener('click', function(event) {
            if (!menuToggle.contains(event.target) && !menuContent.contains(event.target)) {
                menuToggle.setAttribute('aria-expanded', 'false');
                menuContent.classList.remove('show');
                
                const menuIcon = menuToggle.querySelector('.settings-menu-icon');
                if (menuIcon) {
                    menuIcon.textContent = '☰';
                }
            }
        });
    }
    
    // ==================== GESTION DES OPTIONS ====================
    // Récupérer le token CSRF depuis l'attribut data
    const container = document.querySelector('.settings-container');
    const csrfToken = container ? container.dataset.csrfToken : '';
    
    // Charger les options actuelles seulement si on est dans la section options
    if (window.location.search.includes('section=options') || 
        document.querySelector('#options-form')) {
        loadOptions();
        
        // Gérer les clics sur les boutons d'options
        document.querySelectorAll('.option-btn').forEach(button => {
            button.addEventListener('click', function() {
                const option = this.dataset.option;
                const value = this.dataset.value;
                
                // Mettre à jour l'état visuel
                updateButtonState(this);
            });
        });
        
        // Sauvegarder toutes les options
        document.getElementById('save-all-options')?.addEventListener('click', saveAllOptions);
        
        // Réinitialiser les options
        document.getElementById('reset-options')?.addEventListener('click', resetOptions);
    }
    
    function updateButtonState(clickedButton) {
        // Trouver le groupe de boutons parent
        const buttons = clickedButton.parentElement.querySelectorAll('.option-btn');
        
        // Retirer la classe active de tous les boutons du groupe
        buttons.forEach(btn => {
            btn.classList.remove('option-active');
        });
        
        // Ajouter la classe active au bouton cliqué
        clickedButton.classList.add('option-active');
    }
    
    function loadOptions() {
        // Charger les options depuis le serveur
        fetch('?action=get-options')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(options => {
                // Mettre à jour les boutons selon les options
                Object.keys(options).forEach(option => {
                    const button = document.querySelector(`.option-btn[data-option="${option}"][data-value="${options[option]}"]`);
                    if (button) {
                        updateButtonState(button);
                    }
                });
                
                // Afficher un message si succès
                console.log('Options chargées avec succès');
            })
            .catch(error => {
                console.error('Erreur:', error);
                showMessage('error', 'Impossible de charger les options');
            });
    }
    
    function saveAllOptions() {
        // Collecter toutes les options
        const options = {};
        const optionButtons = document.querySelectorAll('.option-btn.option-active');
        
        optionButtons.forEach(button => {
            options[button.dataset.option] = button.dataset.value;
        });
        
        // Envoyer toutes les options
        saveOptionsBatch(options);
    }
    
    function saveOptionsBatch(options) {
        const formData = new FormData();
        formData.append('options', JSON.stringify(options));
        formData.append('csrf_token', csrfToken);
        
        fetch('?action=save-options-batch', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMessage('success', 'Options enregistrées avec succès');
            } else {
                showMessage('error', data.message || 'Erreur lors de l\'enregistrement');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showMessage('error', 'Erreur réseau');
        });
    }
    
    function resetOptions() {
        if (confirm('Voulez-vous vraiment restaurer les valeurs par défaut ?')) {
            // Réinitialiser visuellement
            document.querySelectorAll('.option-btn[data-value="1"]').forEach(button => {
                updateButtonState(button);
            });
            
            // Sauvegarder les valeurs par défaut
            const defaultOptions = {
                site_online: '1',
                mail_reminder: '1',
                email_notifications: '1'
            };
            
            saveOptionsBatch(defaultOptions);
        }
    }
    
    function showMessage(type, text) {
        // Supprimer les anciens messages
        const oldMessages = document.querySelectorAll('.option-message');
        oldMessages.forEach(msg => msg.remove());
        
        // Créer le nouveau message
        const message = document.createElement('div');
        message.className = `option-message message-${type}`;
        message.textContent = text;
        message.style.cssText = `
            padding: 12px 20px;
            margin: 15px 0;
            border-radius: 6px;
            font-weight: 500;
            animation: fadeIn 0.3s ease-in;
        `;
        
        if (type === 'success') {
            message.style.backgroundColor = '#d4edda';
            message.style.color = '#155724';
            message.style.border = '1px solid #c3e6cb';
        } else {
            message.style.backgroundColor = '#f8d7da';
            message.style.color = '#721c24';
            message.style.border = '1px solid #f5c6cb';
        }
        
        // Ajouter le message après les actions
        const actions = document.querySelector('.options-actions');
        if (actions) {
            actions.parentNode.insertBefore(message, actions.nextSibling);
        }
        
        // Supprimer le message après 5 secondes
        setTimeout(() => {
            if (message.parentNode) {
                message.style.opacity = '0';
                message.style.transition = 'opacity 0.5s';
                setTimeout(() => message.remove(), 500);
            }
        }, 5000);
    }
});