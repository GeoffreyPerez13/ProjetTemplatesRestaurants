/**
 * Fonction globale pour soumettre le checkout combiné
 */
window.submitCombinedCheckout = function() {
    // Récupérer les éléments
    const basiqueCheckbox = document.getElementById('basique-checkbox');
    const combinedForm = document.getElementById('combined-cart-form');
    
    if (!combinedForm) {
        alert('Erreur : formulaire non trouvé');
        return;
    }
    
    const csrfToken = combinedForm.querySelector('input[name="csrf_token"]').value;
    const basiqueSelected = basiqueCheckbox ? basiqueCheckbox.checked : false;
    
    // Récupérer les features premium sélectionnées
    const selectedFeatures = [];
    document.querySelectorAll('.premium-feature-card.selected .feature-checkbox:checked').forEach(checkbox => {
        const card = checkbox.closest('.premium-feature-card');
        if (card && card.dataset.feature) {
            selectedFeatures.push(card.dataset.feature);
        }
    });
    
    // Créer un formulaire dynamique pour la soumission
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '?page=stripe-checkout';
    
    // Ajouter le token CSRF
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    // Ajouter l'abonnement basique si sélectionné
    if (basiqueSelected) {
        const basiqueInput = document.createElement('input');
        basiqueInput.type = 'hidden';
        basiqueInput.name = 'include_basique';
        basiqueInput.value = '1';
        form.appendChild(basiqueInput);
    }
    
    // Ajouter les features premium
    selectedFeatures.forEach(feature => {
        const featureInput = document.createElement('input');
        featureInput.type = 'hidden';
        featureInput.name = 'features[]';
        featureInput.value = feature;
        form.appendChild(featureInput);
    });
    
    // Ajouter le formulaire au body et le soumettre
    document.body.appendChild(form);
    form.submit();
};
