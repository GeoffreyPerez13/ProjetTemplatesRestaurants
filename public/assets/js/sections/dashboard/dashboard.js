document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const menuContent = document.getElementById('mobile-menu-content');
    
    if (menuToggle && menuContent) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            menuContent.classList.toggle('show');
            
            // Animation simple du bouton
            if (!isExpanded) {
                // Ouverture
                this.style.borderRadius = '6px 6px 0 0';
            } else {
                // Fermeture
                setTimeout(() => {
                    this.style.borderRadius = '6px';
                }, 300);
            }
        });
        
        // Fermer le menu en cliquant à l'extérieur
        document.addEventListener('click', function(event) {
            if (menuContent.classList.contains('show') && 
                !menuToggle.contains(event.target) && 
                !menuContent.contains(event.target)) {
                
                menuToggle.setAttribute('aria-expanded', 'false');
                menuContent.classList.remove('show');
                menuToggle.style.borderRadius = '6px';
            }
        });
        
        // Empêcher la fermeture quand on clique dans le menu
        menuContent.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});