/**
 * Gestion de la section Intégration Livraison
 */

(function() {
    'use strict';

    const PLATFORMS = ['ubereats', 'deliveroo', 'justeat'];
    let csrfToken = '';

    /**
     * Initialisation
     */
    function init() {
        if (!document.querySelector('.delivery-section')) return;

        // Récupérer le CSRF token
        const container = document.querySelector('.settings-container');
        if (container) {
            csrfToken = container.dataset.csrfToken || '';
            console.log('CSRF Token récupéré:', csrfToken ? 'OK' : 'MANQUANT');
        } else {
            console.error('Container .settings-container non trouvé');
        }

        setupToggleButtons();
        setupTestButtons();
        setupSaveButtons();
        setupPasswordToggles();
        loadConfigurations();
        generateWebhookUrls();
        updateStats();

        console.log('Delivery integration initialized');
    }

    /**
     * Configuration des toggles pour afficher/masquer les mots de passe
     */
    function setupPasswordToggles() {
        document.querySelectorAll('.password-toggle-btn').forEach(button => {
            button.addEventListener('click', function() {
                const card = this.closest('.delivery-card');
                const target = this.dataset.target;
                const input = card.querySelector(`[data-field="${target}"]`);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                    this.setAttribute('aria-label', `Masquer ${target === 'api_key' ? 'la clé API' : "l'ID restaurant"}`);
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                    this.setAttribute('aria-label', `Afficher ${target === 'api_key' ? 'la clé API' : "l'ID restaurant"}`);
                }
            });
        });
    }

    /**
     * Configuration des toggles
     */
    function setupToggleButtons() {
        document.querySelectorAll('.delivery-switch').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const platform = this.dataset.platform;
                const card = this.closest('.delivery-card');
                const config = card.querySelector('.delivery-config');
                
                if (this.checked) {
                    config.style.display = 'block';
                    updateStatus(platform, 'testing');
                } else {
                    config.style.display = 'none';
                    updateStatus(platform, 'inactive');
                    clearConfiguration(platform);
                }
            });
        });
    }

    /**
     * Configuration des boutons de test
     */
    function setupTestButtons() {
        document.querySelectorAll('.test-connection').forEach(button => {
            button.addEventListener('click', function() {
                const card = this.closest('.delivery-card');
                const platform = card.dataset.platform;
                testConnection(platform, this);
            });
        });
    }

    /**
     * Configuration des boutons de sauvegarde
     */
    function setupSaveButtons() {
        document.querySelectorAll('.save-config').forEach(button => {
            button.addEventListener('click', function() {
                const card = this.closest('.delivery-card');
                const platform = card.dataset.platform;
                saveConfiguration(platform, this);
            });
        });
    }

    /**
     * Tester la connexion à une plateforme
     */
    function testConnection(platform, button) {
        const card = document.querySelector(`[data-platform="${platform}"]`);
        const config = getConfiguration(platform);

        if (!config.api_key || !config.store_id) {
            Swal.fire({
                icon: 'warning',
                title: 'Configuration incomplète',
                text: 'Veuillez renseigner la clé API et l\'ID restaurant avant de tester.',
                timer: 3000,
                showConfirmButton: false
            });
            return;
        }

        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.classList.add('loading');
        button.innerHTML = '<i class="fas fa-spinner"></i> Test en cours...';

        updateStatus(platform, 'testing');

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('platform', platform);
        formData.append('api_key', config.api_key);
        formData.append('store_id', config.store_id);

        fetch('?page=settings&action=test-delivery-connection', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.csrf_token) csrfToken = data.csrf_token;

            if (data.success) {
                updateStatus(platform, 'active');
                Swal.fire({
                    icon: 'success',
                    title: 'Connexion réussie',
                    text: `La connexion à ${getPlatformName(platform)} a été établie avec succès.`,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                updateStatus(platform, 'inactive');
                Swal.fire({
                    icon: 'error',
                    title: 'Échec de connexion',
                    text: data.message || `Impossible de se connecter à ${getPlatformName(platform)}.`
                });
            }
        })
        .catch(error => {
            console.error('Test error:', error);
            updateStatus(platform, 'inactive');
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Une erreur est survenue lors du test de connexion.'
            });
        })
        .finally(() => {
            button.disabled = false;
            button.classList.remove('loading');
            button.innerHTML = originalHtml;
        });
    }

    /**
     * Sauvegarder la configuration d'une plateforme
     */
    function saveConfiguration(platform, button) {
        const config = getConfiguration(platform);

        if (!config.api_key || !config.store_id) {
            Swal.fire({
                icon: 'warning',
                title: 'Configuration incomplète',
                text: 'Veuillez renseigner tous les champs obligatoires.',
                timer: 3000,
                showConfirmButton: false
            });
            return;
        }

        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.classList.add('loading');
        button.innerHTML = '<i class="fas fa-spinner"></i> Sauvegarde...';

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('platform', platform);
        formData.append('api_key', config.api_key);
        formData.append('store_id', config.store_id);
        formData.append('enabled', '1');

        fetch('?page=settings&action=save-delivery-config', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.csrf_token) csrfToken = data.csrf_token;

            if (data.success) {
                updateStatus(platform, 'active');
                updateStats();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Configuration sauvegardée',
                    text: `La configuration de ${getPlatformName(platform)} a été enregistrée.`,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: data.message || 'Erreur lors de la sauvegarde.'
                });
            }
        })
        .catch(error => {
            console.error('Save error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Une erreur est survenue lors de la sauvegarde.'
            });
        })
        .finally(() => {
            button.disabled = false;
            button.classList.remove('loading');
            button.innerHTML = originalHtml;
        });
    }

    /**
     * Charger les configurations existantes
     */
    function loadConfigurations() {
        fetch('?page=settings&action=get-delivery-configs')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.configs) {
                    Object.keys(data.configs).forEach(platform => {
                        const config = data.configs[platform];
                        setConfiguration(platform, config);
                        
                        if (config.enabled === '1') {
                            const toggle = document.querySelector(`[data-platform="${platform}"] .delivery-switch`);
                            const configDiv = document.querySelector(`[data-platform="${platform}"] .delivery-config`);
                            
                            if (toggle) toggle.checked = true;
                            if (configDiv) configDiv.style.display = 'block';
                            
                            updateStatus(platform, 'active');
                        }
                    });
                    updateStats();
                }
            })
            .catch(error => {
                console.error('Load error:', error);
            });
    }

    /**
     * Récupérer la configuration d'une plateforme
     */
    function getConfiguration(platform) {
        const card = document.querySelector(`[data-platform="${platform}"]`);
        if (!card) return {};

        const config = {};
        card.querySelectorAll('[data-field]').forEach(input => {
            const field = input.dataset.field;
            config[field] = input.value;
        });

        return config;
    }

    /**
     * Définir la configuration d'une plateforme
     */
    function setConfiguration(platform, config) {
        const card = document.querySelector(`[data-platform="${platform}"]`);
        if (!card) return;

        Object.keys(config).forEach(key => {
            const input = card.querySelector(`[data-field="${key}"]`);
            if (input && config[key]) {
                input.value = config[key];
            }
        });
    }

    /**
     * Effacer la configuration d'une plateforme
     */
    function clearConfiguration(platform) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('platform', platform);
        formData.append('enabled', '0');

        fetch('?page=settings&action=save-delivery-config', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.csrf_token) csrfToken = data.csrf_token;
            if (data.success) {
                updateStats();
            }
        })
        .catch(error => {
            console.error('Clear error:', error);
        });
    }

    /**
     * Générer les URLs webhook
     */
    function generateWebhookUrls() {
        const baseUrl = window.location.origin;
        
        PLATFORMS.forEach(platform => {
            const webhookInput = document.querySelector(`[data-platform="${platform}"] .webhook-url`);
            if (webhookInput) {
                webhookInput.value = `${baseUrl}/webhook/delivery/${platform}`;
            }
        });
    }

    /**
     * Mettre à jour le statut d'une plateforme
     */
    function updateStatus(platform, status) {
        const card = document.querySelector(`[data-platform="${platform}"]`);
        if (!card) return;

        const statusElement = card.querySelector('.delivery-status');
        if (!statusElement) return;

        statusElement.dataset.status = status;

        const statusText = {
            'inactive': 'Non connecté',
            'active': 'Connecté',
            'testing': 'Test en cours...'
        };

        const textNode = Array.from(statusElement.childNodes).find(node => node.nodeType === 3);
        if (textNode) {
            textNode.textContent = ' ' + statusText[status];
        }
    }

    /**
     * Mettre à jour les statistiques
     */
    function updateStats() {
        let connectedCount = 0;

        PLATFORMS.forEach(platform => {
            const toggle = document.querySelector(`[data-platform="${platform}"] .delivery-switch`);
            if (toggle && toggle.checked) {
                connectedCount++;
                console.log(`Plateforme ${platform} connectée`);
            }
        });

        console.log(`Total plateformes connectées: ${connectedCount}`);

        const connectedElement = document.getElementById('connected-platforms');
        if (connectedElement) {
            connectedElement.textContent = connectedCount;
            console.log(`Statistiques mises à jour: ${connectedCount} plateformes`);
        } else {
            console.error('Élément #connected-platforms non trouvé');
        }

        // Commandes du jour (0 en dev, à remplacer par de vraies données en prod)
        const ordersElement = document.getElementById('today-orders');
        if (ordersElement) {
            ordersElement.textContent = 0;
        }

        const syncElement = document.getElementById('last-sync');
        if (syncElement && connectedCount > 0) {
            const now = new Date();
            syncElement.textContent = now.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        }
    }

    /**
     * Obtenir le nom d'une plateforme
     */
    function getPlatformName(platform) {
        const names = {
            'ubereats': 'Uber Eats',
            'deliveroo': 'Deliveroo',
            'justeat': 'Just Eat'
        };
        return names[platform] || platform;
    }

    // Initialisation au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Export pour usage global si nécessaire
    window.deliveryIntegration = {
        testConnection,
        saveConfiguration,
        updateStats
    };
})();
