document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const menuContent = document.getElementById('mobile-menu-content');
    
    if (menuToggle && menuContent) {
        menuToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            menuContent.classList.toggle('show');
            
            // Changer l'icône
            const menuIcon = this.querySelector('.menu-icon');
            if (menuIcon) {
                menuIcon.textContent = isExpanded ? '☰' : '✕';
            }
        });
        
        // Fermer le menu en cliquant à l'extérieur
        document.addEventListener('click', function(event) {
            if (!menuToggle.contains(event.target) && !menuContent.contains(event.target)) {
                menuToggle.setAttribute('aria-expanded', 'false');
                menuContent.classList.remove('show');
                
                const menuIcon = menuToggle.querySelector('.menu-icon');
                if (menuIcon) {
                    menuIcon.textContent = '☰';
                }
            }
        });
        
        // Fermer le menu quand on clique sur un lien
        menuContent.addEventListener('click', function(event) {
            if (event.target.tagName === 'A') {
                menuToggle.setAttribute('aria-expanded', 'false');
                this.classList.remove('show');
                
                const menuIcon = menuToggle.querySelector('.menu-icon');
                if (menuIcon) {
                    menuIcon.textContent = '☰';
                }
            }
        });
    }
});