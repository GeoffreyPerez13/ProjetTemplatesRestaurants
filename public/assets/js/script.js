// Scroll vers la section "Carte" lorsque le bouton est cliqué
function scrollToCarte() {
    const carteSection = document.getElementById('carte');
    if (carteSection) {
        carteSection.scrollIntoView({ behavior: 'smooth' });
    }
}

// Optionnel : ajout d'un effet "menu sticky"
window.addEventListener('scroll', () => {
    const header = document.querySelector('.header');
    if (window.scrollY > 50) {
        header.classList.add('sticky');
    } else {
        header.classList.remove('sticky');
    }
});
