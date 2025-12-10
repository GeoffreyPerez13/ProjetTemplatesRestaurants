/**
 * Lightbox pour view-card.php - Version simplifiée
 */
(function () {
  "use strict";

  // Variables
  let lightbox = null;
  let lightboxImg = null;
  let lightboxCaption = null;
  let images = [];
  let currentIndex = 0;

  /**
   * Initialisation
   */
  function init() {
    console.log("View-Card Lightbox: Initialisation...");

    // Vérifier si on est sur la bonne page
    const isViewCardPage =
      document.querySelector(".carte-preview-grid") ||
      document.querySelector(".carte-images-container");
    if (!isViewCardPage) {
      return; // Pas sur view-card.php
    }

    // Créer la lightbox si elle n'existe pas
    createLightbox();

    // Initialiser les images selon le mode
    updateImages();

    console.log(`View-Card Lightbox: ${images.length} images trouvées`);
  }

  /**
   * Crée la structure de la lightbox
   */
  function createLightbox() {
    // Vérifier si elle existe déjà
    if (document.getElementById("view-card-lightbox")) {
      lightbox = document.getElementById("view-card-lightbox");
      lightboxImg = lightbox.querySelector(".view-card-lightbox-image");
      lightboxCaption = lightbox.querySelector(".view-card-lightbox-caption");
      return;
    }

    // Créer la lightbox
    const lightboxHTML = `
            <div id="view-card-lightbox" class="view-card-lightbox">
                <button class="view-card-lightbox-close" aria-label="Fermer">
                    <i class="fas fa-times"></i>
                </button>
                <div class="view-card-lightbox-content">
                    <img class="view-card-lightbox-image" src="" alt="">
                    <div class="view-card-lightbox-caption"></div>
                </div>
            </div>
        `;

    document.body.insertAdjacentHTML("beforeend", lightboxHTML);

    // Récupérer les éléments
    lightbox = document.getElementById("view-card-lightbox");
    lightboxImg = lightbox.querySelector(".view-card-lightbox-image");
    lightboxCaption = lightbox.querySelector(".view-card-lightbox-caption");
    const closeBtn = lightbox.querySelector(".view-card-lightbox-close");

    // Événements
    closeBtn.addEventListener("click", closeLightbox);
    lightbox.addEventListener("click", function (e) {
      if (e.target === lightbox) {
        closeLightbox();
      }
    });

    // Touche ESC
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && lightbox.classList.contains("active")) {
        closeLightbox();
      }
    });
  }

  /**
   * Met à jour la liste des images selon le mode
   */
  function updateImages() {
    // Détecter le mode actuel
    const isEditableMode =
      document.querySelector(".carte-preview-grid") !== null;
    const isImagesMode =
      document.querySelector(".carte-images-container") !== null;

    // Réinitialiser le tableau
    images = [];

    if (isEditableMode) {
      // Mode éditable : seulement les images des catégories et plats
      const categoryImages = document.querySelectorAll(
        ".category-preview-image"
      );
      const dishImages = document.querySelectorAll(".dish-preview-image");

      // Ajouter les images de catégories
      categoryImages.forEach((img) => {
        if (img.src && img.src !== "" && !img.src.includes("data:")) {
          images.push(img);
        }
      });

      // Ajouter les images de plats
      dishImages.forEach((img) => {
        if (img.src && img.src !== "" && !img.src.includes("data:")) {
          images.push(img);
        }
      });

      console.log(
        `Mode éditable: ${categoryImages.length} catégories, ${dishImages.length} plats`
      );
    } else if (isImagesMode) {
      // Mode images : seulement les images du mode images (pas les PDF)
      const imageItems = document.querySelectorAll(
        '.carte-image-item[data-image-type="image"]'
      );

      imageItems.forEach((item) => {
        const img = item.querySelector(".carte-full-image");
        if (img && img.src && img.src !== "" && !img.src.includes("data:")) {
          images.push(img);
        }
      });

      console.log(`Mode images: ${images.length} images (PDF exclus)`);
    }

    // Ajouter les événements aux images
    images.forEach((img, index) => {
      // Supprimer les anciens événements
      const newImg = img.cloneNode(true);
      img.parentNode.replaceChild(newImg, img);

      // Réattribuer les propriétés
      const currentImg = newImg;
      currentImg.src = img.src;
      currentImg.alt = img.alt;
      currentImg.setAttribute(
        "data-caption",
        img.getAttribute("data-caption") || ""
      );

      // Ajouter le nouvel événement
      currentImg.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        openLightbox(index);
      });

      // Style - SUPPRIME le overlay rouge
      currentImg.style.cursor = "zoom-in";
      if (!currentImg.title) currentImg.title = "Cliquez pour agrandir";

      // SUPPRIME le pseudo-élément ::after (loupe) si présent
      currentImg.classList.remove("lightbox-image");

      // Remplacer dans le tableau
      images[index] = currentImg;
    });

    // Gérer les boutons "Agrandir" (seulement en mode images)
    if (isImagesMode) {
      const viewButtons = document.querySelectorAll(".btn-view-full");
      viewButtons.forEach((btn) => {
        // Cloner le bouton pour supprimer les anciens événements
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);

        newBtn.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();

          const container = this.closest(".image-container");
          if (container) {
            const img = container.querySelector(".carte-full-image");
            if (img) {
              const imgIndex = images.findIndex((i) => i === img);
              if (imgIndex !== -1) {
                openLightbox(imgIndex);
              }
            }
          }
        });
      });
    }
  }

  /**
   * Ouvre la lightbox
   */
  function openLightbox(index) {
    if (index < 0 || index >= images.length) {
      console.warn("Index invalide:", index);
      return;
    }

    currentIndex = index;
    const img = images[currentIndex];

    console.log("Opening image:", img.src);

    // Mettre à jour l'image et la légende
    lightboxImg.src = img.src;

    const caption = img.getAttribute("data-caption") || img.alt || "Image";
    lightboxCaption.textContent = caption;
    lightboxImg.alt = caption;

    // Afficher
    lightbox.classList.add("active");
    document.body.style.overflow = "hidden";
  }

  /**
   * Ferme la lightbox
   */
  function closeLightbox() {
    lightbox.classList.remove("active");
    document.body.style.overflow = "";

    // Nettoyer après un délai
    setTimeout(() => {
      if (!lightbox.classList.contains("active")) {
        lightboxImg.src = "";
      }
    }, 300);
  }

  /**
   * API publique
   */
  window.ViewCardLightbox = {
    init: init,
    open: openLightbox,
    close: closeLightbox,
    refresh: updateImages,
  };

  // Initialisation automatique
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    // Si le DOM est déjà chargé
    setTimeout(init, 100);
  }
})();
