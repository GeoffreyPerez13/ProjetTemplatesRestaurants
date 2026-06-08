/**
 * Gestion du Pack Full - sélection de durée et checkout
 */
document.addEventListener('DOMContentLoaded', function() {
    const durationSelector = document.getElementById('pack-full-duration-selector');
    const durationInput = document.getElementById('pack-full-duration-input');
    const priceDisplay = document.getElementById('pack-full-price-display');

    if (!durationSelector || !durationInput) return;

    // Prix du pack par durée
    const packPrices = {
        '1_month':  { price: 29.99, total: 29.99,  label: '1 mois' },
        '3_months': { price: 26.99, total: 80.97,  label: '3 mois' },
        '1_year':   { price: 22.99, total: 275.88, label: '1 an' }
    };

    // Gérer la sélection de durée
    const durationOptions = durationSelector.querySelectorAll('.duration-option');
    durationOptions.forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (!radio) return;

            // Mettre à jour la sélection visuelle
            durationOptions.forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            radio.checked = true;

            const duration = radio.value;
            durationInput.value = duration;

            // Mettre à jour le prix affiché sur le bouton
            const info = packPrices[duration];
            if (info && priceDisplay) {
                if (info.total !== info.price) {
                    priceDisplay.textContent = info.total.toFixed(2).replace('.', ',') + '€';
                } else {
                    priceDisplay.textContent = info.price.toFixed(2).replace('.', ',') + '€';
                }
            }
        });
    });
});
