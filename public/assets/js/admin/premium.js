/**
 * JavaScript pour la gestion des fonctionnalités Premium
 */

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('.settings-container')?.dataset?.csrfToken || '';
    
    // Debug: vérifier si le token est disponible
    if (!csrfToken) {
        console.error('CSRF token non trouvé dans .settings-container');
    } else {
        console.log('CSRF token trouvé:', csrfToken.substring(0, 20) + '...');
    }

    // Fonction pour attacher les événements toggle (évite les doublons)
    function attachToggleEvents() {
        // Supprimer tous les event listeners existants
        document.querySelectorAll('.toggle-premium').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
        });

        // Attacher les nouveaux événements
        document.querySelectorAll('.toggle-premium').forEach(button => {
            button.addEventListener('click', handleToggleClick);
        });
    }

    // Fonction pour attacher les événements de configuration
    function attachConfigureEvents() {
        // Supprimer tous les event listeners existants
        document.querySelectorAll('.configure-google-reviews').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
        });

        // Attacher les nouveaux événements
        document.querySelectorAll('.configure-google-reviews').forEach(button => {
            button.addEventListener('click', handleConfigureClick);
        });
    }

    // Fonction pour gérer le clic toggle
    function handleToggleClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const button = e.currentTarget;
        const feature = button.dataset.feature;
        const card = button.closest('.premium-feature-card');
        const isActive = card.classList.contains('active');

        console.log('Toggle clicked - feature:', feature, 'isActive:', isActive);

        // Confirmer l'action
        const confirmMessage = isActive 
            ? 'Êtes-vous sûr de vouloir désactiver cette fonctionnalité ?' 
            : 'Êtes-vous sûr de vouloir activer cette fonctionnalité ?';
        
        if (!confirm(confirmMessage)) {
            return;
        }

        // Désactiver le bouton
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';

        // Envoyer la requête
        fetch('?page=settings&action=toggle-premium', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'csrf_token': csrfToken,
                'feature': feature
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Content-Type:', response.headers.get('content-type'));
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.log('Réponse brute du serveur:', text);
                    throw new Error('Réponse serveur invalide: ' + text.substring(0, 200));
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            
            if (data.success) {
                showNotification(data.message || 'Fonctionnalité mise à jour avec succès !', 'success');
                
                // Petit délai puis recharger pour éviter les problèmes de UI
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                throw new Error(data.message || 'Erreur lors de la mise à jour');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Erreur : ' + error.message, 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = isActive ? '<i class="fas fa-times"></i> Désactiver' : '<i class="fas fa-crown"></i> Activer Premium';
        });
    }

    // Fonction pour gérer le clic configuration
    function handleConfigureClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const button = e.currentTarget;
        const configDiv = document.getElementById('google-reviews-config');
        
        console.log('Configure clicked');
        
        // Toggle l'affichage de la configuration
        if (configDiv.style.display === 'none') {
            configDiv.style.display = 'block';
            configDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            button.innerHTML = '<i class="fas fa-times"></i> Masquer';
            button.classList.add('active');
        } else {
            configDiv.style.display = 'none';
            button.innerHTML = '<i class="fas fa-cog"></i> Configurer';
            button.classList.remove('active');
        }
    }

    // Fonction pour afficher les notifications
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
                <button type="button" class="notification-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);

        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        });
    }

    // Initialiser les événements
    attachToggleEvents();
    attachConfigureEvents();
});
