/**
 * closure-banner.js — Gestion de la bannière de fermeture exceptionnelle
 * 
 * Fonctionnalités :
 * - Fermeture manuelle de la bannière
 * - Réapparition au rechargement de la page (pas de persistance)
 */

document.addEventListener('DOMContentLoaded', function() {
    const closureBanner = document.getElementById('closure-banner');
    const closeButton = document.getElementById('closure-banner-close');
    
    if (closureBanner && closeButton) {
        // Gérer le clic sur la croix de fermeture
        closeButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Ajouter la classe de masquage avec animation
            closureBanner.style.transition = 'all 0.3s ease-out';
            closureBanner.style.transform = 'translateY(-100%)';
            closureBanner.style.opacity = '0';
            
            // Masquer complètement après l'animation
            setTimeout(function() {
                closureBanner.classList.add('hidden');
            }, 300);
            
            console.log('Bannière de fermeture exceptionnelle fermée manuellement');
        });
        
        // S'assurer que la bannière est visible au chargement si elle existe
        if (!closureBanner.classList.contains('hidden')) {
            closureBanner.style.display = 'block';
            closureBanner.style.transform = 'translateY(0)';
            closureBanner.style.opacity = '1';
        }
    }
});
