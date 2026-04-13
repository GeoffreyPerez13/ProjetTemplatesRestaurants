(function () {
  "use strict";

  console.log("edit-contact.js chargé"); // Debug

  // Configuration
  const CONFIG = {
    scrollDelay: 1500, // Même valeur que dans PHP
  };

  /**
   * Initialisation principale
   */
  function init() {
    console.log("Initialisation edit-contact.js"); // Debug

    // Vérifier si nous sommes dans la bonne page
    const hasForm = document.querySelector('form[action*="edit-contact"]');
    const hasMessages = document.querySelector(
      ".message-success, .message-error",
    );

    console.log("Form trouvé:", !!hasForm);
    console.log("Messages trouvés:", hasMessages ? hasMessages.length : 0);

    if (!hasForm && !hasMessages) {
      console.log("Page non pertinente, arrêt de l'initialisation");
      return;
    }

    // ==================== RÉCUPÉRATION DES PARAMÈTRES ====================
    const scrollParams = window.scrollParams || {};
    console.log("scrollParams:", scrollParams);

    // Récupérer l'ancre depuis les paramètres
    const anchor = scrollParams.anchor || "";
    console.log("Anchor:", anchor);

    if (anchor) {
      handleAnchorScroll(
        anchor,
        scrollParams.scrollDelay || CONFIG.scrollDelay,
      );
    }

    // ==================== GESTION DES FORMULAIRES ====================
    setupFormHandlers();
  }

  // ==================== FONCTIONS UTILITAIRES ====================

  /**
   * Gère le scroll vers une ancre avec délai
   */
  function handleAnchorScroll(anchorId, delay = CONFIG.scrollDelay) {
    console.log("handleAnchorScroll appelé avec:", anchorId, delay);

    // Ne pas scroller si pas d'ancre
    if (!anchorId) return;

    setTimeout(function () {
      const element = document.getElementById(anchorId);
      console.log("Élément trouvé pour le scroll:", element);

      if (element) {
        // Petit décalage pour éviter d'être collé en haut
        const yOffset = -20;
        const y =
          element.getBoundingClientRect().top + window.pageYOffset + yOffset;

        console.log("Scroll vers y:", y);

        window.scrollTo({
          top: y,
          behavior: "smooth",
        });

        // Effet visuel
        element.style.boxShadow = "0 0 0 3px rgba(52, 152, 219, 0.5)";
        element.style.transition = "box-shadow 0.3s ease";

        setTimeout(() => {
          element.style.boxShadow = "";
        }, 2000);
      }
    }, delay);
  }

  /**
   * Configure les gestionnaires de formulaires
   */
  function setupFormHandlers() {
    console.log("setupFormHandlers appelé");

    // Stocker l'ancre avant soumission
    const form = document.querySelector("form");
    if (form) {
      console.log("Formulaire trouvé");

      form.addEventListener("submit", function (e) {
        console.log("Formulaire soumis");
        const anchorInput = this.querySelector('input[name="anchor"]');
        if (anchorInput && anchorInput.value) {
          sessionStorage.setItem("pending_scroll", anchorInput.value);
        }
      });
    }

    // Gérer le scroll depuis le sessionStorage
    const pendingScroll = sessionStorage.getItem("pending_scroll");
    if (pendingScroll) {
      console.log("pending_scroll trouvé:", pendingScroll);
      handleAnchorScroll(pendingScroll, CONFIG.scrollDelay);
      sessionStorage.removeItem("pending_scroll");
    }
  }

  /**
   * Fait défiler vers une ancre avec délai
   */
  window.scrollToAnchor = function (anchorId, delay = CONFIG.scrollDelay) {
    handleAnchorScroll(anchorId, delay);
  };

  /**
   * API principale
   */
  window.EditContact = {
    init: init,
    scrollToAnchor: window.scrollToAnchor,
  };

  // Initialisation automatique
  console.log("edit-contact.js - État du document:", document.readyState);

  if (document.readyState === "loading") {
    console.log("Attente du DOMContentLoaded");
    document.addEventListener("DOMContentLoaded", init);
  } else {
    console.log("DOM déjà chargé, initialisation immédiate");
    // Déjà chargé, initialiser après un court délai
    setTimeout(init, 100);
  }
})();
