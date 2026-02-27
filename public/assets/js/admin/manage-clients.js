/**
 * JavaScript pour la gestion des clients Premium
 */

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('.manage-clients-container')?.dataset?.csrfToken || 
                      document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Gestion des filtres
    const filterTabs = document.querySelectorAll('.filter-tab');
    const clientRows = document.querySelectorAll('.client-row');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Mettre à jour les tabs actifs
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Filtrer les clients
            clientRows.forEach(row => {
                if (filter === 'all') {
                    row.style.display = '';
                } else {
                    const plan = row.dataset.plan;
                    row.style.display = plan === filter ? '' : 'none';
                }
            });
        });
    });

    // Gestion des dropdowns d'actions
    const actionButtons = document.querySelectorAll('.actions-btn');
    
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const clientId = this.dataset.client;
            const dropdown = document.getElementById(`dropdown-${clientId}`);
            
            // Fermer tous les autres dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== dropdown) {
                    menu.classList.remove('show');
                }
            });
            
            // Toggle le dropdown actuel
            dropdown.classList.toggle('show');
        });
    });

    // Fermer les dropdowns en cliquant ailleurs
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    });

    // Modal d'activation Premium
    const modal = document.getElementById('activateModal');
    const modalClose = document.querySelectorAll('.modal-close');
    const activateButtons = document.querySelectorAll('.activate-premium, .activate-pro');
    
    // Ouvrir le modal
    activateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const clientId = this.dataset.client;
            const planType = this.dataset.plan;
            const clientRow = document.querySelector(`.client-row[data-client-id="${clientId}"]`);
            
            // Remplir les infos du client
            const clientName = clientRow.querySelector('.client-name')?.textContent || '';
            const clientRestaurant = clientRow.querySelector('.client-email')?.textContent || '';
            
            document.getElementById('modal-client-name').textContent = clientName;
            document.getElementById('modal-client-restaurant').textContent = clientRestaurant;
            
            // Sélectionner le plan
            document.querySelectorAll('.plan-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.querySelector(`.plan-option[data-plan="${planType}"]`)?.classList.add('selected');
            
            // Stocker le client ID
            modal.dataset.clientId = clientId;
            modal.dataset.planType = planType;
            
            // Calculer le prix
            updatePrice();
            
            // Afficher le modal
            modal.classList.add('show');
        });
    });
    
    // Fermer le modal
    modalClose.forEach(button => {
        button.addEventListener('click', function() {
            modal.classList.remove('show');
        });
    });
    
    // Fermer le modal en cliquant sur le fond
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('show');
        }
    });

    // Sélection du plan dans le modal
    const planOptions = document.querySelectorAll('.plan-option');
    planOptions.forEach(option => {
        option.addEventListener('click', function() {
            planOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            modal.dataset.planType = this.dataset.plan;
            updatePrice();
        });
    });

    // Mise à jour du prix selon la durée
    const durationSelect = document.getElementById('duration');
    durationSelect.addEventListener('change', updatePrice);

    function updatePrice() {
        const planType = modal.dataset.planType || 'premium';
        const duration = parseInt(durationSelect.value);
        
        const basePrices = {
            'premium': 19,
            'pro': 39
        };
        
        const discounts = {
            1: 0,
            3: 10,
            6: 15,
            12: 20
        };
        
        const basePrice = basePrices[planType] || 19;
        const discountPercent = discounts[duration] || 0;
        const discount = Math.round(basePrice * duration * discountPercent / 100);
        const total = basePrice * duration - discount;
        
        document.getElementById('base-price').textContent = `${basePrice * duration}€`;
        document.getElementById('discount').textContent = `-${discount}€`;
        document.getElementById('total-price').textContent = `${total}€`;
    }

    // Confirmation de l'activation
    const confirmButton = document.getElementById('confirm-activation');
    confirmButton.addEventListener('click', function() {
        const clientId = modal.dataset.clientId;
        const planType = modal.dataset.planType;
        const duration = parseInt(durationSelect.value);
        
        if (!clientId || !planType) {
            alert('Veuillez sélectionner un client et un plan');
            return;
        }
        
        // Désactiver le bouton
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
        
        // Envoyer la requête
        fetch('?page=manage-clients&action=activate-subscription', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'csrf_token': csrfToken,
                'client_id': clientId,
                'plan_type': planType,
                'duration': duration
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                modal.classList.remove('show');
                location.reload(); // Recharger pour voir les changements
            } else {
                throw new Error(data.message || 'Erreur lors de l\'activation');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur : ' + error.message);
        })
        .finally(() => {
            // Réactiver le bouton
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-crown"></i> Activer l\'abonnement';
        });
    });

    // Annulation d'abonnement
    const cancelButtons = document.querySelectorAll('.cancel-subscription');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            const clientId = this.dataset.client;
            
            if (!confirm('Êtes-vous sûr de vouloir annuler l\'abonnement de ce client ?')) {
                return;
            }
            
            // Envoyer la requête
            fetch('?page=manage-clients&action=cancel-subscription', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'csrf_token': csrfToken,
                    'client_id': clientId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    throw new Error(data.message || 'Erreur lors de l\'annulation');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur : ' + error.message);
            });
        });
    });

    // Prolongation d'abonnement
    const extendButtons = document.querySelectorAll('.extend-subscription');
    extendButtons.forEach(button => {
        button.addEventListener('click', function() {
            const clientId = this.dataset.client;
            const months = prompt('Combien de mois souhaitez-vous ajouter ? (1-12)');
            
            if (!months || isNaN(months) || months < 1 || months > 12) {
                alert('Veuillez entrer un nombre entre 1 et 12');
                return;
            }
            
            // Envoyer la requête
            fetch('?page=manage-clients&action=extend-subscription', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'csrf_token': csrfToken,
                    'client_id': clientId,
                    'months': months
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    throw new Error(data.message || 'Erreur lors de la prolongation');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur : ' + error.message);
            });
        });
    });

    // Voir les détails d'un client
    const viewButtons = document.querySelectorAll('.view-details');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const clientId = this.dataset.client;
            
            // Envoyer la requête
            fetch(`?page=manage-clients&action=get-client-details&client_id=${clientId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const client = data.data;
                    const features = JSON.parse(client.features_enabled || '[]');
                    
                    const details = `
Détails du client:
----------------
Nom: ${client.username}
Restaurant: ${client.restaurant_name || 'Non défini'}
Email: ${client.email}
Plan: ${client.plan_type}
Statut: ${client.status}
Fonctionnalités: ${features.join(', ') || 'Aucune'}
Début: ${client.started_at || 'Non défini'}
Expiration: ${client.expires_at || 'Non défini'}
Créé le: ${client.created_at}
                    `;
                    
                    alert(details);
                } else {
                    throw new Error(data.message || 'Erreur lors du chargement');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur : ' + error.message);
            });
        });
    });
});
