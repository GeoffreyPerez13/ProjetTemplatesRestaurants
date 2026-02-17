document.addEventListener('DOMContentLoaded', function() {
    console.log('Display JS loaded');

    // Smooth scroll pour les ancres
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Lightbox pour toutes les images avec la classe .lightbox-image
    const lightbox = document.getElementById('lightbox');
    if (lightbox) {
        const lightboxImg = lightbox.querySelector('.lightbox-content img');
        const closeBtn = lightbox.querySelector('.lightbox-close');
        const prevBtn = lightbox.querySelector('.lightbox-prev');
        const nextBtn = lightbox.querySelector('.lightbox-next');
        let currentIndex = 0;
        let images = [];

        // Récupérer toutes les images avec la classe .lightbox-image
        const imageElements = document.querySelectorAll('.lightbox-image');
        imageElements.forEach((img, index) => {
            const src = img.src; // Utilise l'attribut src de l'image
            if (src) {
                images.push({ element: img, src: src });
            }
            img.addEventListener('click', (e) => {
                e.stopPropagation();
                currentIndex = index;
                lightboxImg.src = images[currentIndex].src;
                lightbox.classList.add('active');
            });
        });

        // Gérer l'affichage des boutons si peu d'images
        if (images.length <= 1) {
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
        } else {
            if (prevBtn) prevBtn.style.display = 'block';
            if (nextBtn) nextBtn.style.display = 'block';
        }

        closeBtn.addEventListener('click', () => {
            lightbox.classList.remove('active');
        });

        prevBtn.addEventListener('click', () => {
            if (images.length > 1) {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                lightboxImg.src = images[currentIndex].src;
            }
        });

        nextBtn.addEventListener('click', () => {
            if (images.length > 1) {
                currentIndex = (currentIndex + 1) % images.length;
                lightboxImg.src = images[currentIndex].src;
            }
        });

        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                lightbox.classList.remove('active');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (!lightbox.classList.contains('active')) return;
            if (e.key === 'Escape') lightbox.classList.remove('active');
            if (e.key === 'ArrowLeft') prevBtn.click();
            if (e.key === 'ArrowRight') nextBtn.click();
        });
    }
});