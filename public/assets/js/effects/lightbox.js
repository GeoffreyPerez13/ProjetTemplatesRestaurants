// Lightbox functionality
document.addEventListener("DOMContentLoaded", function () {
    const lightbox = document.getElementById("image-lightbox");
    const lightboxImg = document.getElementById("lightbox-image");
    const lightboxCaption = document.getElementById("lightbox-caption");
    const lightboxClose = document.querySelector(".lightbox-close");
    const lightboxPrev = document.querySelector(".lightbox-prev");
    const lightboxNext = document.querySelector(".lightbox-next");

    // Collection de toutes les images cliquables
    let images = [];
    let currentIndex = 0;

    // Initialiser les images cliquables
    function initLightbox() {
        // Sélectionner toutes les images qui doivent être cliquables
        // Images de la page edit-card.php
        const editImages = Array.from(document.querySelectorAll(".image-preview, .dish-image-preview"));
        
        // Images de la page view-card.php
        const viewImages = Array.from(document.querySelectorAll(".category-preview-image, .dish-preview-image"));
        
        // Combiner toutes les images
        images = [...editImages, ...viewImages];
        
        images.forEach((img, index) => {
            // Éviter d'ajouter plusieurs fois le même event listener
            if (!img.hasAttribute('data-lightbox-initialized')) {
                img.addEventListener("click", function (e) {
                    e.stopPropagation();
                    openLightbox(index);
                });
                
                // Ajouter un indicateur visuel (optionnel)
                img.style.cursor = "zoom-in";
                img.title = "Cliquez pour agrandir";
                img.setAttribute('data-lightbox-initialized', 'true');
            }
        });
    }

    // Ouvrir le lightbox
    function openLightbox(index) {
        if (images.length === 0) return;
        
        currentIndex = index;
        updateLightboxImage();
        lightbox.classList.add("active");
        document.body.style.overflow = "hidden"; // Empêcher le scroll
    }

    // Fermer le lightbox
    function closeLightbox() {
        lightbox.classList.remove("active");
        document.body.style.overflow = ""; // Réactiver le scroll
    }

    // Mettre à jour l'image affichée
    function updateLightboxImage() {
        if (images.length === 0) return;
        
        const img = images[currentIndex];
        lightboxImg.src = img.src;
        
        // Utiliser data-caption si présent, sinon utiliser alt
        const caption = img.getAttribute('data-caption') || img.alt || "Image";
        lightboxCaption.textContent = caption;
    }

    // Navigation
    function showPrevImage() {
        if (images.length === 0) return;
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        updateLightboxImage();
    }

    function showNextImage() {
        if (images.length === 0) return;
        currentIndex = (currentIndex + 1) % images.length;
        updateLightboxImage();
    }

    // Navigation au clavier
    document.addEventListener("keydown", function (e) {
        if (!lightbox.classList.contains("active")) return;
        
        switch (e.key) {
            case "Escape":
                closeLightbox();
                break;
            case "ArrowLeft":
                showPrevImage();
                break;
            case "ArrowRight":
                showNextImage();
                break;
        }
    });

    // Fermer en cliquant sur le fond
    lightbox.addEventListener("click", function (e) {
        if (e.target === lightbox || e.target.classList.contains("lightbox-content")) {
            closeLightbox();
        }
    });

    // Événements
    lightboxClose.addEventListener("click", closeLightbox);
    lightboxPrev.addEventListener("click", function (e) {
        e.stopPropagation();
        showPrevImage();
    });
    lightboxNext.addEventListener("click", function (e) {
        e.stopPropagation();
        showNextImage();
    });

    // Initialiser le lightbox
    initLightbox();
    
    // Réinitialiser le lightbox quand le DOM est mis à jour
    const observer = new MutationObserver(function (mutations) {
        let shouldReinit = false;
        mutations.forEach(function (mutation) {
            if (mutation.addedNodes.length) {
                shouldReinit = true;
            }
        });
        if (shouldReinit) {
            // Petit délai pour laisser le temps aux nouvelles images de se charger
            setTimeout(initLightbox, 100);
        }
    });
    
    observer.observe(document.body, { childList: true, subtree: true });
});