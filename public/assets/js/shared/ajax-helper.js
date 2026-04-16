/* MenuMiam - AJAX Helper Réutilisable */

/**
 * Effectue une requête AJAX avec gestion centralisée des erreurs et du CSRF
 * @param {string} url - URL de la requête
 * @param {string} method - Méthode HTTP (GET, POST, PUT, DELETE)
 * @param {object} data - Données à envoyer
 * @param {object} callbacks - Callbacks { onSuccess, onError, onComplete }
 * @returns {Promise}
 */
function ajaxRequest(url, method = 'POST', data = {}, callbacks = {}) {
    const { onSuccess, onError, onComplete } = callbacks;

    // Ajouter le token CSRF automatiquement pour les requêtes POST/PUT/DELETE
    if (method !== 'GET' && !data.csrf_token) {
        const csrfInput = document.querySelector('input[name="csrf_token"]');
        if (csrfInput) {
            data.csrf_token = csrfInput.value;
        }
    }

    return $.ajax({
        url: url,
        method: method,
        data: data,
        dataType: 'json'
    })
    .done(function(response) {
        if (response.success) {
            if (onSuccess) onSuccess(response);
        } else {
            if (onError) {
                onError(response);
            } else {
                showToast(response.message || 'Une erreur est survenue', 'error');
            }
        }
    })
    .fail(function(xhr, status, error) {
        const errorMessage = xhr.responseJSON?.message || 'Erreur de connexion au serveur';
        if (onError) {
            onError({ success: false, message: errorMessage });
        } else {
            showToast(errorMessage, 'error');
        }
        console.error('AJAX Error:', error);
    })
    .always(function() {
        if (onComplete) onComplete();
    });
}

/**
 * Affiche une notification toast
 * @param {string} message - Message à afficher
 * @param {string} type - Type de notification (success, error, info, warning)
 * @param {number} duration - Durée d'affichage en ms (défaut: 3000)
 */
function showToast(message, type = 'info', duration = 3000) {
    // Si SweetAlert2 est disponible, l'utiliser
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type === 'error' ? 'error' : type === 'success' ? 'success' : 'info',
            title: message,
            showConfirmButton: false,
            timer: duration,
            timerProgressBar: true
        });
    } else {
        // Fallback : créer un toast simple
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            background: ${type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : '#3b82f6'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            animation: slideIn 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
}

/**
 * Confirme une action avec SweetAlert2
 * @param {string} title - Titre de la confirmation
 * @param {string} text - Texte de la confirmation
 * @param {function} onConfirm - Callback si confirmé
 * @param {object} options - Options supplémentaires
 */
function confirmAction(title, text, onConfirm, options = {}) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: options.confirmText || 'Oui, supprimer',
            cancelButtonText: options.cancelText || 'Annuler',
            ...options
        }).then((result) => {
            if (result.isConfirmed && onConfirm) {
                onConfirm();
            }
        });
    } else {
        // Fallback : confirm natif
        if (confirm(`${title}\n${text}`)) {
            if (onConfirm) onConfirm();
        }
    }
}

/**
 * Valide un formulaire côté client
 * @param {HTMLFormElement} form - Formulaire à valider
 * @returns {boolean} - True si valide, false sinon
 */
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    });
    
    if (!isValid) {
        showToast('Veuillez remplir tous les champs obligatoires', 'error');
    }
    
    return isValid;
}

/**
 * Réinitialise la validation d'un formulaire
 * @param {HTMLFormElement} form - Formulaire à réinitialiser
 */
function resetFormValidation(form) {
    form.querySelectorAll('.is-valid, .is-invalid').forEach(field => {
        field.classList.remove('is-valid', 'is-invalid');
    });
}
