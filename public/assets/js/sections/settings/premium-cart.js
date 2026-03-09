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
        console.log('=== UPDATE CART CALLED ===');
        console.log('selectedFeatures:', Array.from(selectedFeatures));
        console.log('basiqueSelected:', basiqueSelected);
        
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

        // Mettre à jour l'affichage du panier premium (uniquement les options premium)
        cartCount.textContent = count;
        
        // Pour le panier premium, n'afficher que le coût des options premium
        const premiumOnlyTotal = basiqueSelected ? total - 9 : total;
        cartTotal.textContent = `${premiumOnlyTotal}€`;
        
        // Ajouter un récapitulatif du coût total si l'utilisateur a déjà un abonnement basique
        // Utiliser les variables déjà déclarées plus haut
        
        if (hasBasiqueSubscription && selectedFeatures.size > 0) {
            // Ajouter ou mettre à jour le récapitulatif du coût total
            let totalSummary = document.querySelector('.premium-total-summary');
            if (!totalSummary) {
                totalSummary = document.createElement('div');
                totalSummary.className = 'premium-total-summary';
                premiumCartBar.appendChild(totalSummary);
            }
            
            // Adapter les couleurs selon le mode (light/dark) à chaque mise à jour
            // Tester plusieurs méthodes de détection du mode dark
            const isDarkMode1 = document.body.classList.contains('dark-mode');
            const isDarkMode2 = document.documentElement.classList.contains('dark-mode');
            const isDarkMode3 = getComputedStyle(document.body).getPropertyValue('--color-bg') === '#1c1917';
            const isDarkMode4 = window.getComputedStyle(document.body).backgroundColor === 'rgb(28, 25, 23)';
            
            // Utiliser la première méthode qui fonctionne
            const isDarkMode = isDarkMode1 || isDarkMode2 || isDarkMode3 || isDarkMode4;
            
            const textColor = isDarkMode ? '#ffffff' : '#1f2937';
            const borderColor = isDarkMode ? 'rgba(255,255,255,0.2)' : 'rgba(0,0,0,0.1)';
            const totalColor = isDarkMode ? '#fbbf24' : '#d97706';
            
            // Utiliser la vraie détection du mode
            const useTextColor = isDarkMode ? '#ffffff' : '#1f2937';
            const useTotalColor = isDarkMode ? '#fbbf24' : '#d97706';
            
            // Debug pour vérifier la détection
            console.log('=== DEBUG MODE ===');
            console.log('body.className:', document.body.className);
            console.log('html.className:', document.documentElement.className);
            console.log('--color-bg:', getComputedStyle(document.body).getPropertyValue('--color-bg'));
            console.log('body.backgroundColor:', window.getComputedStyle(document.body).backgroundColor);
            console.log('isDarkMode1 (body):', isDarkMode1);
            console.log('isDarkMode2 (html):', isDarkMode2);
            console.log('isDarkMode3 (css var):', isDarkMode3);
            console.log('isDarkMode4 (bg color):', isDarkMode4);
            console.log('isDarkMode final:', isDarkMode);
            console.log('useTextColor:', useTextColor);
            console.log('useTotalColor:', useTotalColor);
            
            totalSummary.style.cssText = `
                margin-top: 10px; 
                padding-top: 10px; 
                border-top: 1px solid ${borderColor}; 
                font-size: 12px; 
                color: ${textColor};
            `;
            // Utiliser les variables déjà déclarées plus haut
            
            // Générer le HTML avec les couleurs forcées pour TEST
            totalSummary.innerHTML = `
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color: ${useTextColor};">Abonnement Basique:</span>
                    <span style="margin-left: 20px; color: ${useTextColor};">9€/mois</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color: ${useTextColor};">Options premium:</span>
                    <span style="margin-left: 20px; color: ${useTextColor};">+${premiumOnlyTotal}€/mois</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: bold;">
                    <span style="color: ${useTotalColor};">Total mensuel:</span>
                    <span style="margin-left: 20px; color: ${useTotalColor};">${total}€/mois</span>
                </div>
            `;
        } else {
            // Supprimer le récapitulatif s'il existe et n'est pas nécessaire
            const totalSummary = document.querySelector('.premium-total-summary');
            if (totalSummary) {
                totalSummary.remove();
            }
        }

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
            
            // Ajouter l'abonnement basique si sélectionné ET si l'utilisateur n'en a pas déjà un
            const basiqueCard = document.querySelector('.basique-sub-card');
            const hasBasiqueSubscription = basiqueCard && basiqueCard.dataset.basiqueActive === '1';
            
            if (basiqueSelected && !hasBasiqueSubscription) {
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
    
    // Écouter les changements de mode (light/dark) pour mettre à jour les couleurs
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && 
                (mutation.attributeName === 'class' || mutation.attributeName === 'style')) {
                // Vérifier si le mode dark a changé
                const wasDarkMode = mutation.oldValue ? mutation.oldValue.includes('dark-mode') : false;
                const isDarkMode = document.body.classList.contains('dark-mode') || 
                                 document.documentElement.classList.contains('dark-mode');
                
                if (wasDarkMode !== isDarkMode) {
                    // Le mode a changé, mettre à jour le récapitulatif s'il existe
                    const totalSummary = document.querySelector('.premium-total-summary');
                    if (totalSummary && selectedFeatures.size > 0) {
                        console.log('Mode dark/light changé, mise à jour du récapitulatif');
                        updateCart();
                    }
                }
            }
        });
    });
    
    // Observer les changements sur le body et html
    observer.observe(document.body, { 
        attributes: true, 
        attributeOldValue: true,
        attributeFilter: ['class', 'style']
    });
    observer.observe(document.documentElement, { 
        attributes: true, 
        attributeOldValue: true,
        attributeFilter: ['class', 'style']
    });
});
