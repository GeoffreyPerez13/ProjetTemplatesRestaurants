// lightbox.js - Gestion de la lightbox (version IIFE)
(function () {
  "use strict";

  // Variables privées - isolées dans la closure
  let lightboxImages = [];
  let lightboxCurrentIndex = 0;
  let lightboxInstance = null;
  let isInitialized = false;

  // Éléments DOM
  const elements = {
    lightbox: null,
    lightboxImg: null,
    lightboxCaption: null,
    lightboxClose: null,
    lightboxPrev: null,
    lightboxNext: null,
  };

  /**
   * Initialise la lightbox automatiquement au chargement du DOM
   */
  document.addEventListener("DOMContentLoaded", function () {
    if (!isInitialized) {
      initLightbox();
      isInitialized = true;
    }
  });

  /**
   * Fonction d'initialisation principale
   */
  function initLightbox() {
    // Récupérer les éléments DOM
    elements.lightbox = document.getElementById("image-lightbox");
    elements.lightboxImg = document.getElementById("lightbox-image");
    elements.lightboxCaption = document.getElementById("lightbox-caption");
    elements.lightboxClose = document.querySelector(".lightbox-close");
    elements.lightboxPrev = document.querySelector(".lightbox-prev");
    elements.lightboxNext = document.querySelector(".lightbox-next");

    // Vérifier que les éléments essentiels existent
    if (!elements.lightbox || !elements.lightboxImg) {
      console.warn("Éléments Lightbox manquants, initialisation annulée");
      return;
    }

    // Stocker l'instance
    lightboxInstance = {
      lightbox: elements.lightbox,
      lightboxImg: elements.lightboxImg,
      lightboxCaption: elements.lightboxCaption,
    };

    // Mettre à jour les images cliquables
    updateLightboxImages();

    // Configurer les événements
    setupEventListeners();

    // Observer les changements du DOM pour les nouvelles images
    setupMutationObserver();
  }

  /**
   * Configure tous les écouteurs d'événements
   */
  function setupEventListeners() {
    // Fermer la lightbox
    if (elements.lightboxClose) {
      elements.lightboxClose.addEventListener("click", closeLightbox);
    }

    // Navigation précédente
    if (elements.lightboxPrev) {
      elements.lightboxPrev.addEventListener("click", function (e) {
        e.stopPropagation();
        showPrevImage();
      });
    }

    // Navigation suivante
    if (elements.lightboxNext) {
      elements.lightboxNext.addEventListener("click", function (e) {
        e.stopPropagation();
        showNextImage();
      });
    }

    // Fermer en cliquant sur le fond
    elements.lightbox.addEventListener("click", function (e) {
      if (
        e.target === elements.lightbox ||
        e.target.classList.contains("lightbox-content")
      ) {
        closeLightbox();
      }
    });

    // Navigation clavier
    document.addEventListener("keydown", function (e) {
      if (!elements.lightbox.classList.contains("active")) return;

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
  }

  /**
   * Configure un observer pour détecter les nouvelles images
   */
  function setupMutationObserver() {
    const observer = new MutationObserver(function (mutations) {
      let shouldUpdate = false;

      mutations.forEach(function (mutation) {
        if (mutation.type === "childList" && mutation.addedNodes.length > 0) {
          shouldUpdate = true;
        }
      });

      if (shouldUpdate) {
        setTimeout(updateLightboxImages, 100);
      }
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true,
    });
  }

  /**
   * Met à jour la collection d'images cliquables
   */
  function updateLightboxImages() {
    // Sélectionner toutes les images potentiellement cliquables
    const selectors = [
      ".image-preview",
      ".dish-image-preview",
      ".lightbox-image",
      ".carte-image-preview",
      ".category-preview-image",
      ".dish-preview-image",
      '[data-lightbox="true"]',
    ];

    // Combiner tous les sélecteurs
    const selectorString = selectors.join(", ");
    const allImages = document.querySelectorAll(selectorString);

    // Filtrer les images valides (avec src)
    lightboxImages = Array.from(allImages).filter((img) => {
      return img.src && img.src !== "" && !img.src.includes("data:");
    });

    // Ajouter les événements de clic
    lightboxImages.forEach((img, index) => {
      if (!img.hasAttribute("data-lightbox-initialized")) {
        img.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();

          // Trouver l'index actuel (peut avoir changé)
          const currentIndex = lightboxImages.indexOf(img);
          if (currentIndex !== -1) {
            lightboxCurrentIndex = currentIndex;
            openLightboxAt(currentIndex);
          }
        });

        // Ajouter des styles et attributs
        img.style.cursor = "zoom-in";
        img.title = img.title || "Cliquez pour agrandir";
        img.setAttribute("data-lightbox-initialized", "true");

        // Ajouter aria-label si manquant
        if (!img.hasAttribute("aria-label")) {
          const altText = img.alt || "";
          const caption = img.getAttribute("data-caption") || altText;
          img.setAttribute("aria-label", `Agrandir: ${caption}`);
        }
      }
    });
  }

  /**
   * Ouvre la lightbox à un index spécifique
   */
  function openLightboxAt(index) {
    if (
      lightboxImages.length === 0 ||
      index < 0 ||
      index >= lightboxImages.length
    ) {
      console.warn("Index invalide pour la lightbox");
      return;
    }

    const img = lightboxImages[index];
    lightboxCurrentIndex = index;

    // Mettre à jour l'image et la légende
    elements.lightboxImg.src = img.src;

    const caption =
      img.getAttribute("data-caption") || img.alt || img.title || "Image";
    elements.lightboxCaption.textContent = caption;

    // Afficher la lightbox
    elements.lightbox.classList.add("active");
    document.body.style.overflow = "hidden";

    // Mettre à jour les attributs ARIA
    elements.lightbox.setAttribute("aria-hidden", "false");
    elements.lightboxImg.setAttribute("alt", caption);
  }

  /**
   * Ouvre la lightbox avec une URL spécifique
   */
  window.openLightbox = function (src, caption) {
    if (!src) {
      console.warn("openLightbox appelé sans source");
      return;
    }

    // Si on a déjà cette image dans la collection, l'ouvrir directement
    const existingIndex = lightboxImages.findIndex((img) => img.src === src);
    if (existingIndex !== -1) {
      openLightboxAt(existingIndex);
      return;
    }

    // Sinon, ouvrir avec les paramètres fournis
    elements.lightboxImg.src = src;
    elements.lightboxCaption.textContent = caption || "Image";
    elements.lightbox.classList.add("active");
    document.body.style.overflow = "hidden";
    elements.lightbox.setAttribute("aria-hidden", "false");
  };

  /**
   * Ferme la lightbox
   */
  function closeLightbox() {
    elements.lightbox.classList.remove("active");
    document.body.style.overflow = "";
    elements.lightbox.setAttribute("aria-hidden", "true");

    // Réinitialiser l'image après un délai pour économiser la mémoire
    setTimeout(() => {
      if (!elements.lightbox.classList.contains("active")) {
        elements.lightboxImg.src = "";
      }
    }, 300);
  }

  /**
   * Affiche l'image précédente
   */
  function showPrevImage() {
    if (lightboxImages.length === 0) return;

    lightboxCurrentIndex =
      (lightboxCurrentIndex - 1 + lightboxImages.length) %
      lightboxImages.length;
    openLightboxAt(lightboxCurrentIndex);
  }

  /**
   * Affiche l'image suivante
   */
  function showNextImage() {
    if (lightboxImages.length === 0) return;

    lightboxCurrentIndex = (lightboxCurrentIndex + 1) % lightboxImages.length;
    openLightboxAt(lightboxCurrentIndex);
  }

  /**
   * Fonction utilitaire pour ajouter une image à la lightbox manuellement
   */
  window.addToLightbox = function (imageElement) {
    if (!imageElement || !imageElement.src) {
      console.warn("addToLightbox: élément image invalide");
      return false;
    }

    if (!imageElement.hasAttribute("data-lightbox-initialized")) {
      // Ajouter aux images
      lightboxImages.push(imageElement);

      // Configurer l'événement
      imageElement.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const index = lightboxImages.indexOf(imageElement);
        if (index !== -1) {
          lightboxCurrentIndex = index;
          openLightboxAt(index);
        }
      });

      imageElement.style.cursor = "zoom-in";
      imageElement.setAttribute("data-lightbox-initialized", "true");

      return true;
    }

    return false;
  };

  /**
   * Fonction pour rafraîchir manuellement la collection d'images
   */
  window.refreshLightbox = function () {
    updateLightboxImages();
  };

  /**
   * Fonction pour obtenir des informations sur l'état de la lightbox
   */
  window.getLightboxInfo = function () {
    return {
      totalImages: lightboxImages.length,
      currentIndex: lightboxCurrentIndex,
      isActive: elements.lightbox
        ? elements.lightbox.classList.contains("active")
        : false,
      isInitialized: isInitialized,
    };
  };

  // Exposer certaines fonctions pour le débogage
  if (window.console && console.debug) {
    window.lightboxDebug = {
      updateImages: updateLightboxImages,
      openAtIndex: openLightboxAt,
      close: closeLightbox,
      next: showNextImage,
      prev: showPrevImage,
    };
  }
})();
