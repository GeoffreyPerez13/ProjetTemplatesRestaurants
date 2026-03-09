/**
 * Gestion de la sélection d'abonnement basique + premium
 */
document.addEventListener('DOMContentLoaded', function() {
    const basiqueCheckbox = document.getElementById('basique-checkbox');
    const premiumCartBar = document.getElementById('premium-cart-bar');
    const cartCount = document.getElementById('cart-count');
    const cartTotal = document.getElementById('cart-total');
    const checkoutBtn = document.getElementById('premium-checkout-btn');

    if (!premiumCartBar) return;

    // Prix des options premium
    const premiumPrices = {
        'google_reviews': 5,
        'advanced_analytics': 5,
        'online_booking': 8,
        'delivery_integration': 7
    };

    // État du panier
    let selectedFeatures = new Set();
    let basiqueSelected = false;

    // Mettre à jour l'affichage du panier
    function updateCart() {
        // Calculer le total d'abord
        let total = 0;
        let count = 0;

        if (basiqueSelected) {
            total += 9; // Prix basique
            count++;
        }

        selectedFeatures.forEach(feature => {
            total += premiumPrices[feature] || 0;
            count++;
        });

        const hasSelections = basiqueSelected || selectedFeatures.size > 0;
        
        // Afficher/masquer le panier premium
        // Pour les utilisateurs avec abonnement basique, afficher dès qu'il y a des sélections premium
        // Pour les autres, afficher seulement s'il y a des sélections (basique + premium)
        const basiqueCard = document.querySelector('.basique-sub-card');
        const hasBasiqueSubscription = basiqueCard && basiqueCard.dataset.basiqueActive === '1';
        const showPremiumCart = hasBasiqueSubscription ? selectedFeatures.size > 0 : hasSelections;
        
                
        if (showPremiumCart && premiumCartBar) {
            premiumCartBar.style.display = 'flex';
            premiumCartBar.classList.add('cart-visible');
        } else if (premiumCartBar) {
            premiumCartBar.style.display = 'none';
            premiumCartBar.classList.remove('cart-visible');
        }

        // Afficher/masquer le combined-cart-section
        const combinedCartSection = document.getElementById('combined-cart-section');
        if (combinedCartSection) {
            if (hasSelections) {
                combinedCartSection.style.display = 'block';
                
                // Mettre à jour l'affichage de l'abonnement basique
                const basiqueCartItem = document.getElementById('basique-cart-item');
                if (basiqueCartItem) {
                    basiqueCartItem.style.display = basiqueSelected ? 'flex' : 'none';
                }
                
                // Mettre à jour les options premium
                const premiumItems = document.getElementById('premium-items');
                if (premiumItems) {
                    if (selectedFeatures.size === 0) {
                        premiumItems.innerHTML = '<div class="premium-placeholder"><p>Aucune option premium sélectionnée</p></div>';
                    } else {
                        const itemsHtml = Array.from(selectedFeatures).map(feature => {
                            const featureNames = {
                                'google_reviews': 'Avis Google',
                                'advanced_analytics': 'Statistiques avancées',
                                'online_booking': 'Réservations en ligne',
                                'delivery_integration': 'Intégration livraison'
                            };
                            return `
                                <div class="premium-item">
                                    <div class="item-info">
                                        <i class="fas fa-star"></i>
                                        <span>${featureNames[feature] || feature}</span>
                                    </div>
                                    <span class="item-price">+${premiumPrices[feature]}€/mois</span>
                                </div>
                            `;
                        }).join('');
                        premiumItems.innerHTML = itemsHtml;
                    }
                }
                
                // Mettre à jour le total
                const combinedTotal = document.getElementById('combined-total');
                if (combinedTotal) {
                    combinedTotal.textContent = `${total}€/mois`;
                }
            } else {
                combinedCartSection.style.display = 'none';
            }
        }

        // Mettre à jour l'affichage du panier premium
        cartCount.textContent = count;
        cartTotal.textContent = `${total}€`;

        // Activer/désactiver le bouton premium - nécessite des options premium sélectionnées
        if (checkoutBtn) {
            checkoutBtn.disabled = selectedFeatures.size === 0;
        }
        
        // Activer/désactiver le bouton combiné - nécessite au moins l'abonnement basique
        const combinedCheckoutBtn = document.getElementById('combined-checkout-btn');
        if (combinedCheckoutBtn) {
            combinedCheckoutBtn.disabled = !basiqueSelected;
        }
    }

    // Gérer la case à cocher basique
    if (basiqueCheckbox) {
        basiqueCheckbox.addEventListener('change', function() {
            basiqueSelected = this.checked;
        
        // Ajouter/retirer la classe pour le style orange
        const label = this.closest('.basique-select-label');
        if (this.checked) {
            label.classList.add('selected');
            
            // Ouvrir automatiquement l'accordéon premium
            const premiumAccordion = document.querySelector('[data-target="premium-options-content"]');
            const premiumContent = document.getElementById('premium-options-content');
            
            if (premiumAccordion && premiumContent) {
                // Mettre à jour l'icône
                const icon = premiumAccordion.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
                
                // Ouvrir le contenu
                premiumContent.classList.add('expanded');
                premiumContent.style.display = 'block';
            }
        } else {
            label.classList.remove('selected');
        }
        
        // Activer/désactiver les options premium
        updatePremiumCardsState();
        
        updateCart();
        });
    }

    // Mettre à jour l'état des cartes premium
    function updatePremiumCardsState() {
        const premiumCards = document.querySelectorAll('.premium-feature-card');
        
        premiumCards.forEach(card => {
            const checkbox = card.querySelector('.feature-checkbox');
            const feature = card.dataset.feature;
            const statusBadge = card.querySelector('.status-badge');
            const label = card.querySelector('.feature-select-label');
            
            if (!basiqueSelected) {
                // Désactiver toutes les cartes premium
                if (checkbox) {
                    checkbox.checked = false;
                    checkbox.disabled = true;
                }
                selectedFeatures.delete(feature);
                card.classList.remove('selectable', 'selected');
                card.classList.add('locked');
                
                // Mettre à jour le statut
                if (statusBadge) {
                    statusBadge.className = 'status-badge locked';
                    statusBadge.innerHTML = '<i class="fas fa-lock"></i> Basique requis';
                }
                
                // Cacher la checkbox et afficher le bouton locked
                if (label) {
                    label.style.display = 'none';
                }
                const lockedBtn = card.querySelector('.premium-btn');
                if (lockedBtn) {
                    lockedBtn.style.display = 'inline-block';
                    lockedBtn.disabled = true;
                    lockedBtn.innerHTML = '<i class="fas fa-lock"></i> Basique requis';
                }
            } else {
                // Activer les cartes qui ne sont pas déjà actives
                if (!card.classList.contains('active')) {
                    card.classList.remove('locked');
                    card.classList.add('selectable');
                    
                    // Mettre à jour le statut
                    if (statusBadge) {
                        statusBadge.className = 'status-badge available';
                        statusBadge.innerHTML = '<i class="fas fa-unlock"></i> Disponible';
                    }
                    
                    // Afficher la checkbox et cacher le bouton locked
                    if (label) {
                        label.style.display = 'inline-flex';
                        if (checkbox) {
                            checkbox.disabled = false;
                        }
                    }
                    const lockedBtn = card.querySelector('.premium-btn');
                    if (lockedBtn) {
                        lockedBtn.style.display = 'none';
                    }
                }
            }
        });
    }

    // Gérer les clics sur les cartes premium (unifié)
    document.addEventListener('click', function(e) {
        const card = e.target.closest('.premium-feature-card');
        if (!card) return;

        const checkbox = card.querySelector('.feature-checkbox');
        const feature = card.dataset.feature;
        
                
        if (!card.classList.contains('selectable') || !checkbox || !feature) return;
        
        // Si on clique sur la carte ou sur le label (mais pas sur la checkbox elle-même)
        if (e.target.closest('.feature-select-label') || 
            (!e.target.closest('.feature-checkbox') && 
             !e.target.closest('button') && 
             !e.target.closest('a') && 
             !e.target.closest('i'))) {
            
            e.preventDefault();
            
            // Inverser l'état de la checkbox
            const wasChecked = checkbox.checked;
            checkbox.checked = !wasChecked;
            
            // Mettre à jour l'état visuel et la sélection
            if (checkbox.checked) {
                selectedFeatures.add(feature);
                card.classList.add('selected');
            } else {
                selectedFeatures.delete(feature);
                card.classList.remove('selected');
            }
            
            updateCart();
        }
    });

    // Gérer le changement direct de la checkbox
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('feature-checkbox')) {
            const checkbox = e.target;
            const feature = checkbox.value;
            const card = checkbox.closest('.premium-feature-card');
            
            // Mettre à jour l'état visuel
            if (checkbox.checked) {
                selectedFeatures.add(feature);
                card.classList.add('selected');
            } else {
                selectedFeatures.delete(feature);
                card.classList.remove('selected');
            }
            
            updateCart();
        }
    });

    // Soumission du formulaire premium
    const premiumForm = document.getElementById('premium-cart-form');
    if (premiumForm) {
        premiumForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Créer le formulaire de paiement
            const formData = new FormData();
            formData.append('csrf_token', this.querySelector('input[name="csrf_token"]').value);
            
            // Ajouter l'abonnement basique si sélectionné
            if (basiqueSelected) {
                formData.append('include_basique', '1');
            }
            
            // Ajouter les fonctionnalités premium sélectionnées
            selectedFeatures.forEach(feature => {
                formData.append('features[]', feature);
            });
            
            // Soumettre via POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?page=stripe-checkout';
            
            for (let [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        });
    }

    // Soumission du formulaire combiné
    const combinedForm = document.getElementById('combined-cart-form');
    if (combinedForm) {
        combinedForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Créer le formulaire de paiement
            const formData = new FormData();
            formData.append('csrf_token', this.querySelector('input[name="csrf_token"]').value);
            
            // Ajouter l'abonnement basique si sélectionné
            if (basiqueSelected) {
                formData.append('include_basique', '1');
            }
            
            // Ajouter les fonctionnalités premium sélectionnées
            selectedFeatures.forEach(feature => {
                formData.append('features[]', feature);
            });
            
            // Soumettre via POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?page=stripe-checkout';
            
            for (let [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        });
    }

    // Initialisation - cacher seulement le combined-cart-section au départ
    const combinedCartSection = document.getElementById('combined-cart-section');
    if (combinedCartSection) {
        combinedCartSection.style.display = 'none';
    }
    
    // S'assurer que l'état basique est correct au chargement
    if (basiqueCheckbox) {
        basiqueSelected = basiqueCheckbox.checked;
        if (!basiqueSelected) {
            const label = basiqueCheckbox.closest('.basique-select-label');
            if (label) {
                label.classList.remove('selected');
            }
        }
    } else {
        // Si pas de checkbox basique, vérifier si l'utilisateur a déjà un abonnement basique
        const basiqueCard = document.querySelector('.basique-sub-card');
        if (basiqueCard && basiqueCard.dataset.basiqueActive === '1') {
            basiqueSelected = true;
        }
    }
    
    // Initialiser l'état des cartes premium
    updatePremiumCardsState();
    
    updateCart();
});
