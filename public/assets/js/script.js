// Fonction pour scroller vers la section "Carte"
function scrollToCarte() {
    const carteSection = document.getElementById('carte'); // récupère l'élément
    if (carteSection) {
        carteSection.scrollIntoView({ behavior: 'smooth' }); 
        // Scroll animé vers la section
    }
}

// Effet sticky sur le menu/header
window.addEventListener('scroll', () => {
    const header = document.querySelector('.header');
    if (window.scrollY > 50) {
        header.classList.add('sticky'); // Ajoute la classe sticky si on scroll > 50px
    } else {
        header.classList.remove('sticky'); // Supprime sinon
    }
});
