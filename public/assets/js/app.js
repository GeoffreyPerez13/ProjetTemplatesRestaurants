/**
 * MenuMiam V2 - Application JavaScript Globale
 * Helpers AJAX réutilisables pour toute l'application
 */

const App = {
    /**
     * Configuration
     */
    config: {
        baseUrl: window.location.origin + '/ProjetTemplatesRestaurants/public',
        toastDuration: 3000
    },

    /**
     * Soumettre un formulaire en AJAX
     * @param {HTMLFormElement} form - Le formulaire à soumettre
     * @param {Object} options - Options (onSuccess, onError, onComplete)
     */
    ajaxForm: function(form, options = {}) {
        const formData = new FormData(form);
        const url = form.action;
        const method = form.method.toUpperCase();

        this.ajaxRequest({
            url: url,
            method: method,
            data: formData,
            onSuccess: options.onSuccess,
            onError: options.onError,
            onComplete: options.onComplete
        });
    },

    /**
     * Requête AJAX générique
     * @param {Object} options - Configuration de la requête
     */
    ajaxRequest: function(options) {
        const {
            url,
            method = 'POST',
            data = null,
            headers = {},
            onSuccess = null,
            onError = null,
            onComplete = null
        } = options;

        // Afficher un loader si nécessaire
        if (options.showLoader !== false) {
            this.showLoader();
        }

        fetch(url, {
            method: method,
            body: data,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...headers
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Masquer le loader
            this.hideLoader();

            // Afficher le toast selon le résultat
            if (data.success) {
                this.showToast(data.message || 'Opération réussie', 'success');
                if (onSuccess) onSuccess(data);
            } else {
                this.showToast(data.message || 'Une erreur est survenue', 'error');
                if (onError) onError(data);
            }

            if (onComplete) onComplete(data);
        })
        .catch(error => {
            this.hideLoader();
            this.showToast('Erreur de connexion au serveur', 'error');
            console.error('AJAX Error:', error);
            if (onError) onError(error);
            if (onComplete) onComplete(null);
        });
    },

    /**
     * Afficher une notification toast
     * @param {string} message - Message à afficher
     * @param {string} type - Type : success, error, info, warning
     */
    showToast: function(message, type = 'info') {
        // Créer le conteneur de toasts s'il n'existe pas
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }

        // Créer le toast
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        // Icône selon le type
        const icons = {
            success: '✓',
            error: '✗',
            info: 'ℹ',
            warning: '⚠'
        };
        
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || icons.info}</span>
            <span class="toast-message">${message}</span>
        `;

        // Ajouter au conteneur
        toastContainer.appendChild(toast);

        // Animation d'entrée
        setTimeout(() => toast.classList.add('show'), 10);

        // Supprimer après la durée définie
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, this.config.toastDuration);
    },

    /**
     * Afficher un loader global
     */
    showLoader: function() {
        let loader = document.getElementById('global-loader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'global-loader';
            loader.className = 'global-loader';
            loader.innerHTML = '<div class="loader-spinner"></div>';
            document.body.appendChild(loader);
        }
        loader.classList.add('show');
    },

    /**
     * Masquer le loader global
     */
    hideLoader: function() {
        const loader = document.getElementById('global-loader');
        if (loader) {
            loader.classList.remove('show');
        }
    },

    /**
     * Initialiser les formulaires AJAX automatiquement
     * Ajouter la classe 'ajax-form' à un formulaire pour l'activer
     */
    initAjaxForms: function() {
        document.querySelectorAll('.ajax-form').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                
                // Options personnalisées via data-attributes
                const options = {
                    onSuccess: (data) => {
                        // Réinitialiser le formulaire si demandé
                        if (form.dataset.resetOnSuccess === 'true') {
                            form.reset();
                        }
                        
                        // Rediriger si URL fournie
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1000);
                        }
                    }
                };

                this.ajaxForm(form, options);
            });
        });
    },

    /**
     * Initialiser l'application
     */
    init: function() {
        // Initialiser les formulaires AJAX
        this.initAjaxForms();

        // Réinitialiser après chargement dynamique de contenu
        document.addEventListener('contentLoaded', () => {
            this.initAjaxForms();
        });
    }
};

// Initialiser au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    App.init();
});

// Exposer App globalement
window.App = App;
