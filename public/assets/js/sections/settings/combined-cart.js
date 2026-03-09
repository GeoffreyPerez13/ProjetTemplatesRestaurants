/**
 * Gestion du panier combiné Basique + Premium
 */
document.addEventListener('DOMContentLoaded', function() {
    const combinedForm = document.getElementById('combined-cart-form');
    const premiumItems = document.getElementById('premium-items');
    const combinedTotal = document.getElementById('combined-total');
    const clearBtn = document.getElementById('clear-combined-cart');
    
    if (!combinedForm) return;

    // Prix des options premium
    const premiumPrices = {
        'google_reviews': 5,
        'advanced_analytics': 5,
        'online_booking': 8,
        'delivery_integration': 7
    };

    // État du panier
    let selectedFeatures = new Set();

    // Mettre à jour l'affichage du panier
    function updateCombinedCart() {
        // Mettre à jour les items premium
        if (selectedFeatures.size === 0) {
            premiumItems.innerHTML = `
                <div class="premium-placeholder">
                    <p>Aucune option premium sélectionnée</p>
                </div>
            `;
        } else {
            let html = '';
            selectedFeatures.forEach(feature => {
                const price = premiumPrices[feature] || 0;
                const name = getFeatureDisplayName(feature);
                const icon = getFeatureIcon(feature);
                html += `
                    <div class="premium-item">
                        <div class="item-info">
                            <i class="fas ${icon}"></i>
                            <span>${name}</span>
                        </div>
                        <span class="item-price">+${price}€/mois</span>
                    </div>
                `;
            });
            premiumItems.innerHTML = html;
        }

        // Mettre à jour le total
        let total = 9; // Prix de base basique
        selectedFeatures.forEach(feature => {
            total += premiumPrices[feature] || 0;
        });
        combinedTotal.textContent = `${total}€/mois`;
    }

    // Obtenir le nom affiché d'une fonctionnalité
    function getFeatureDisplayName(feature) {
        const names = {
            'google_reviews': 'Avis Google',
            'advanced_analytics': 'Statistiques avancées',
            'online_booking': 'Réservations en ligne',
            'delivery_integration': 'Intégration livraison'
        };
        return names[feature] || feature;
    }

    // Obtenir l'icône d'une fonctionnalité
    function getFeatureIcon(feature) {
        const icons = {
            'google_reviews': 'fa-star',
            'advanced_analytics': 'fa-chart-line',
            'online_booking': 'fa-calendar-check',
            'delivery_integration': 'fa-truck'
        };
        return icons[feature] || 'fa-bolt';
    }

    // Gérer les clics sur les cartes premium
    document.addEventListener('click', function(e) {
        const card = e.target.closest('.premium-feature-card');
        if (!card) return;

        const feature = card.dataset.feature;
        const price = parseInt(card.dataset.price);
        
        if (!feature || !price) return;

        // Si la carte est sélectionnable
        if (card.classList.contains('selectable')) {
            e.preventDefault();
            
            // Inverser la sélection
            if (selectedFeatures.has(feature)) {
                selectedFeatures.delete(feature);
                card.classList.remove('selected');
            } else {
                selectedFeatures.add(feature);
                card.classList.add('selected');
            }
            
            updateCombinedCart();
        }
    });

    // Vider le panier
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            selectedFeatures.clear();
            document.querySelectorAll('.premium-feature-card.selected').forEach(card => {
                card.classList.remove('selected');
            });
            updateCombinedCart();
        });
    }

    // Soumission du formulaire
    combinedForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Ajouter les fonctionnalités sélectionnées au formulaire
        selectedFeatures.forEach(feature => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'features[]';
            input.value = feature;
            combinedForm.appendChild(input);
        });
        
        // Soumettre le formulaire
        combinedForm.submit();
    });

    // Initialisation
    updateCombinedCart();
});
