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

    // Calculer le prorata pour une option premium
    function calculateProrata(priceMonthly) {
        const now = new Date();
        const currentDay = now.getDate();
        const daysInMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
        
        // Si on est avant le 15, prorata jusqu'au 15 du mois en cours
        // Si on est après le 15, prorata jusqu'au 15 du mois prochain
        let targetDay = 15;
        let targetMonth = now.getMonth();
        
        if (currentDay > 15) {
            targetMonth++;
        }
        
        const targetDate = new Date(now.getFullYear(), targetMonth, targetDay);
        const daysRemaining = Math.ceil((targetDate - now) / (1000 * 60 * 60 * 24));
        
        // Calcul prorata : (jours restants / jours total) * prix mensuel
        const prorata = Math.round((daysRemaining / daysInMonth) * priceMonthly * 100) / 100;
        
        return prorata;
    }

    // Noms des features en français
    const featureNames = {
        'google_reviews': 'Avis Google',
        'advanced_analytics': 'Statistiques avancées',
        'online_booking': 'Réservations en ligne',
        'delivery_integration': 'Intégration livraison'
    };

    // Mettre à jour l'affichage du panier
    function updateCart() {
        const basiqueCard = document.querySelector('.basique-sub-card');
        const hasBasiqueSubscription = basiqueCard && basiqueCard.dataset.basiqueActive === '1';

        // --- Calculs ---
        let premiumMonthly = 0;
        let premiumProrata = 0;
        selectedFeatures.forEach(feature => {
            const price = premiumPrices[feature] || 0;
            premiumMonthly += price;
            premiumProrata += calculateProrata(price);
        });
        premiumProrata = Math.round(premiumProrata * 100) / 100;

        const basiqueProrata = Math.round(calculateProrata(9) * 100) / 100;
        const totalMonthly = (basiqueSelected ? 9 : 0) + premiumMonthly;
        const totalProrata = Math.round(((basiqueSelected ? basiqueProrata : 0) + premiumProrata) * 100) / 100;

        const hasSelections = basiqueSelected || selectedFeatures.size > 0;

        // --- Panier premium (utilisateur AVEC abonnement basique) ---
        const showPremiumCart = hasBasiqueSubscription ? selectedFeatures.size > 0 : hasSelections;

        if (showPremiumCart && premiumCartBar) {
            premiumCartBar.style.display = 'flex';
            premiumCartBar.classList.add('cart-visible');
        } else if (premiumCartBar) {
            premiumCartBar.style.display = 'none';
            premiumCartBar.classList.remove('cart-visible');
        }

        // Mettre à jour le compteur et le total du panier premium
        if (cartCount) cartCount.textContent = selectedFeatures.size;
        if (cartTotal) cartTotal.textContent = `${premiumProrata.toFixed(2)}€`;

        // Récapitulatif détaillé dans le panier premium (pour utilisateur avec basique)
        if (hasBasiqueSubscription && selectedFeatures.size > 0) {
            let totalSummary = document.querySelector('.premium-total-summary');
            if (!totalSummary) {
                totalSummary = document.createElement('div');
                totalSummary.className = 'premium-total-summary';
                premiumCartBar.appendChild(totalSummary);
            }

            let itemsHtml = '';
            selectedFeatures.forEach(feature => {
                const price = premiumPrices[feature] || 0;
                const prorata = calculateProrata(price);
                itemsHtml += `
                    <div class="summary-item">
                        <span>${featureNames[feature] || feature}</span>
                        <span>${prorata.toFixed(2)}€ <small>(${price}€/mois)</small></span>
                    </div>`;
            });

            totalSummary.innerHTML = `
                <div class="summary-items">${itemsHtml}</div>
                <div class="summary-separator"></div>
                <div class="summary-line summary-prorata">
                    <span>À payer maintenant (prorata)</span>
                    <strong>${premiumProrata.toFixed(2)}€</strong>
                </div>
                <div class="summary-line summary-monthly">
                    <span>Abonnement basique</span>
                    <span>9€/mois</span>
                </div>
                <div class="summary-line summary-monthly">
                    <span>Options premium</span>
                    <span>+${premiumMonthly}€/mois</span>
                </div>
                <div class="summary-line summary-total">
                    <span>Total mensuel</span>
                    <strong>${9 + premiumMonthly}€/mois</strong>
                </div>
            `;
        } else {
            const totalSummary = document.querySelector('.premium-total-summary');
            if (totalSummary) totalSummary.remove();
        }

        // --- Panier combiné (utilisateur SANS abonnement basique) ---
        const combinedCartSection = document.getElementById('combined-cart-section');
        if (combinedCartSection) {
            if (hasSelections) {
                combinedCartSection.style.display = 'block';

                // Abonnement basique
                const basiqueCartItem = document.getElementById('basique-cart-item');
                if (basiqueCartItem) {
                    basiqueCartItem.style.display = basiqueSelected ? 'flex' : 'none';
                    if (basiqueSelected) {
                        const priceEl = basiqueCartItem.querySelector('.item-price');
                        if (priceEl) {
                            priceEl.innerHTML = `<span class="item-prorata-price">${basiqueProrata.toFixed(2)}€</span> <small>(9€/mois)</small>`;
                        }
                    }
                }

                // Options premium
                const premiumItems = document.getElementById('premium-items');
                if (premiumItems) {
                    if (selectedFeatures.size === 0) {
                        premiumItems.innerHTML = '<div class="premium-placeholder"><p>Aucune option premium sélectionnée</p></div>';
                    } else {
                        const html = Array.from(selectedFeatures).map(feature => {
                            const price = premiumPrices[feature] || 0;
                            const prorata = calculateProrata(price);
                            return `
                                <div class="premium-item">
                                    <div class="item-info">
                                        <i class="fas fa-star"></i>
                                        <span>${featureNames[feature] || feature}</span>
                                    </div>
                                    <div class="item-price">
                                        <span class="item-prorata-price">${prorata.toFixed(2)}€</span> <small>(${price}€/mois)</small>
                                    </div>
                                </div>`;
                        }).join('');
                        premiumItems.innerHTML = html;
                    }
                }

                // Totaux
                const combinedTotal = document.getElementById('combined-total');
                const combinedProrata = document.getElementById('combined-prorata');
                if (combinedTotal) combinedTotal.textContent = `${totalMonthly}€/mois`;
                if (combinedProrata) combinedProrata.textContent = `${totalProrata.toFixed(2)}€`;
            } else {
                combinedCartSection.style.display = 'none';
            }
        }

        // --- Boutons ---
        if (checkoutBtn) {
            checkoutBtn.disabled = selectedFeatures.size === 0;
        }

        const combinedCheckoutBtn = document.getElementById('combined-checkout-btn');
        if (combinedCheckoutBtn) {
            combinedCheckoutBtn.disabled = !basiqueSelected && !hasBasiqueSubscription;
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
                // Mettre à jour l'icône (ouvert = pas de rotation)
                const icon = premiumAccordion.querySelector('i');
                if (icon) {
                    icon.classList.remove('rotated');
                }
                
                // Ouvrir le contenu
                premiumContent.classList.add('expanded');
                premiumContent.style.display = 'block';
                
                // Scroller vers l'accordéon premium
                setTimeout(() => {
                    premiumAccordion.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
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

    // Reset complet des checkboxes premium au chargement (évite l'état résiduel du navigateur)
    function resetAllPremiumCheckboxes() {
        selectedFeatures.clear();
        document.querySelectorAll('.feature-checkbox').forEach(cb => {
            cb.checked = false;
            const card = cb.closest('.premium-feature-card');
            if (card) card.classList.remove('selected');
        });
    }

    resetAllPremiumCheckboxes();
    updateCart();

    // Réinitialiser au retour arrière du navigateur
    window.addEventListener('pageshow', function() {
        resetAllPremiumCheckboxes();
        updateCart();
    });
});
