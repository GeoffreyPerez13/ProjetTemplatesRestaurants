(function () {
  "use strict";

  // Configuration
  const CONFIG = {
    scrollDelay: 1500, // 1,5 secondes par défaut (comme dans PHP)
  };

  /**
   * Initialisation principale
   */
  function init() {
    // Vérifier si nous sommes dans la bonne page
    if (
      !document.querySelector(".edit-carte-container") &&
      !document.querySelector(".images-mode-container")
    ) {
      return;
    }

    // ==================== RÉCUPÉRATION DES PARAMÈTRES ====================
    const scrollParams = window.scrollParams || {};

    // Récupérer l'ancre depuis les paramètres ou l'URL
    const anchor = scrollParams.anchor || getUrlParameter("anchor");
    if (anchor) {
      handleAnchorScroll(
        anchor,
        scrollParams.scrollDelay || CONFIG.scrollDelay,
      );
    }

    // ==================== CONFIRMATION DE SUPPRESSION ====================
    setupDeleteConfirmations();

    // ==================== GESTION DES ACCORDÉONS APRÈS ACTIONS ====================
    handleAccordionActions(scrollParams);

    // ==================== CONFIGURATION SPÉCIFIQUE AU MODE ====================
    const isImagesMode =
      document.querySelector(".images-mode-container") !== null;

    if (isImagesMode) {
      // Appeler notre fonction pour les confirmations de suppression
      setupImageDeletionConfirmations();

      // ==================== INITIALISATION DU DRAG & DROP ====================
      setTimeout(() => {
        if (window.ImageReorder) {
          window.ImageReorder.init();
        }
      }, 200);
    } else {
      // ==================== VALIDATION DES PRIX ====================
      setupPriceValidation();

      // ==================== FORMATAGE AUTOMATIQUE DES PRIX ====================
      setupPriceAutoFormat();
    }

    // ==================== ALLERGENES : BOUTONS COCHER/TOUT DECOCHER ====================
    setupAllergenesToggles();

    // ==================== GESTION DES FORMULAIRES ====================
    setupFormHandlers();
  }

  // ==================== FONCTIONS UTILITAIRES ====================

  /**
   * Récupère un paramètre d'URL par son nom
   */
  function getUrlParameter(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    const regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
    const results = regex.exec(location.search);
    return results === null
      ? ""
      : decodeURIComponent(results[1].replace(/\+/g, " "));
  }

  /**
   * Gère le scroll vers une ancre avec délai
   */
  function handleAnchorScroll(anchorId, delay = CONFIG.scrollDelay) {
    // Ne pas scroller si pas d'ancre
    if (!anchorId) return;

    setTimeout(function () {
      const element = document.getElementById(anchorId);
      if (element) {
        // Petit décalage pour éviter d'être collé en haut
        const yOffset = -20;
        const y =
          element.getBoundingClientRect().top + window.pageYOffset + yOffset;

        window.scrollTo({
          top: y,
          behavior: "smooth",
        });

        // Effet visuel
        element.style.boxShadow = "0 0 0 3px rgba(52, 152, 219, 0.5)";
        element.style.transition = "box-shadow 0.3s ease";

        setTimeout(() => {
          element.style.boxShadow = "";
        }, 1500);
      }
    }, delay);
  }

  /**
   * Gère les actions sur les accordéons après soumission
   */
  function handleAccordionActions(scrollParams) {
    // Utiliser AccordionManager s'il existe, sinon ne rien faire
    if (!window.AccordionManager) return;

    // Fermer l'accordéon principal
    if (scrollParams.closeAccordion) {
      setTimeout(() => {
        window.AccordionManager.closeAccordion(scrollParams.closeAccordion);
      }, 500);
    }

    // Fermer l'accordéon secondaire
    if (scrollParams.closeAccordionSecondary) {
      setTimeout(() => {
        window.AccordionManager.closeAccordion(
          scrollParams.closeAccordionSecondary,
        );
      }, 600);
    }

    // Ouvrir l'accordéon spécifié
    if (scrollParams.openAccordion) {
      setTimeout(() => {
        window.AccordionManager.openAccordion(scrollParams.openAccordion);
      }, 700);
    }

    // Fermer l'accordéon spécifique d'un plat
    if (scrollParams.closeDishAccordion) {
      setTimeout(() => {
        window.AccordionManager.closeDishAccordion(
          scrollParams.closeDishAccordion,
        );
      }, 800);
    }
  }

  /**
   * Configure les confirmations de suppression pour catégories, plats et images
   */
  function setupDeleteConfirmations() {
    // ==================== 1. FORMULAIRES DE SUPPRESSION D'IMAGE DE CATÉGORIE ====================
    document
      .querySelectorAll('form.inline-form button[name="remove_category_image"]')
      .forEach((button) => {
        button.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();

          const form = this.closest("form");
          if (!form) return;

          const categoryBlock = form.closest(".category-block");
          const categoryName =
            categoryBlock?.querySelector("strong")?.textContent?.trim() ||
            "cette catégorie";

          // Confirmation avec SweetAlert si disponible, sinon confirm() natif
          if (typeof Swal !== "undefined") {
            Swal.fire({
              title: "Confirmer la suppression",
              text: `Voulez-vous vraiment supprimer l'image de la catégorie "${categoryName}" ?`,
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#d33",
              cancelButtonColor: "#3085d6",
              confirmButtonText: "Oui, supprimer",
              cancelButtonText: "Annuler",
              backdrop: true,
              allowOutsideClick: false,
            }).then((result) => {
              if (result.isConfirmed) {
                showLoading("Suppression en cours...");
                setTimeout(() => form.submit(), 100);
              }
            });
          } else {
            if (
              confirm(
                `Voulez-vous vraiment supprimer l'image de la catégorie "${categoryName}" ?`,
              )
            ) {
              form.submit();
            }
          }
        });
      });

    // ==================== 2. FORMULAIRES DE SUPPRESSION D'IMAGE DE PLAT ====================
    document
      .querySelectorAll('form.inline-form button[name="remove_dish_image"]')
      .forEach((button) => {
        button.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();

          const form = this.closest("form");
          if (!form) return;

          const dishContainer = form.closest(".dish-edit-container");
          const dishName =
            dishContainer
              ?.querySelector('input[name="dish_name"]')
              ?.value?.trim() || "ce plat";

          if (typeof Swal !== "undefined") {
            Swal.fire({
              title: "Confirmer la suppression",
              text: `Voulez-vous vraiment supprimer l'image du plat "${dishName}" ?`,
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#d33",
              cancelButtonColor: "#3085d6",
              confirmButtonText: "Oui, supprimer",
              cancelButtonText: "Annuler",
              backdrop: true,
              allowOutsideClick: false,
            }).then((result) => {
              if (result.isConfirmed) {
                showLoading("Suppression en cours...");
                setTimeout(() => form.submit(), 100);
              }
            });
          } else {
            if (
              confirm(
                `Voulez-vous vraiment supprimer l'image du plat "${dishName}" ?`,
              )
            ) {
              form.submit();
            }
          }
        });
      });

    // ==================== 3. FORMULAIRES DE SUPPRESSION DE CATÉGORIES ====================
    document
      .querySelectorAll('form.inline-form input[name="delete_category"]')
      .forEach((input) => {
        const form = input.closest("form");
        if (!form) return;

        form.addEventListener("submit", function (e) {
          e.preventDefault();

          const categoryBlock = form.closest(".category-block");
          const categoryName =
            categoryBlock?.querySelector("strong")?.textContent?.trim() ||
            "cette catégorie";

          // Vérifier si la catégorie a des plats
          const dishList = categoryBlock?.querySelector(".dish-list");
          const hasPlats = dishList && dishList.querySelector("li");
          const warningMessage = hasPlats
            ? "\n\n⚠️ Attention, tous les plats associés seront également supprimés !"
            : "";

          if (typeof Swal !== "undefined") {
            Swal.fire({
              title: "Confirmer la suppression",
              text: `Voulez-vous vraiment supprimer la catégorie "${categoryName}" ?${warningMessage}`,
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#d33",
              cancelButtonColor: "#3085d6",
              confirmButtonText: "Oui, supprimer",
              cancelButtonText: "Annuler",
              backdrop: true,
              allowOutsideClick: false,
            }).then((result) => {
              if (result.isConfirmed) {
                showLoading("Suppression en cours...");
                setTimeout(() => form.submit(), 100);
              }
            });
          } else {
            if (
              confirm(
                `Voulez-vous vraiment supprimer la catégorie "${categoryName}" ?${warningMessage}`,
              )
            ) {
              form.submit();
            }
          }
        });
      });

    // ==================== 4. FORMULAIRES DE SUPPRESSION DE PLATS ====================
    document
      .querySelectorAll('form.inline-form input[name="delete_dish"]')
      .forEach((input) => {
        const form = input.closest("form");
        if (!form) return;

        form.addEventListener("submit", function (e) {
          e.preventDefault();

          const dishEditContainer = form.closest(".dish-edit-container");
          const dishName =
            dishEditContainer
              ?.querySelector('input[name="dish_name"]')
              ?.value?.trim() || "ce plat";

          if (typeof Swal !== "undefined") {
            Swal.fire({
              title: "Confirmer la suppression",
              text: `Voulez-vous vraiment supprimer le plat "${dishName}" ?`,
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#d33",
              cancelButtonColor: "#3085d6",
              confirmButtonText: "Oui, supprimer",
              cancelButtonText: "Annuler",
              backdrop: true,
              allowOutsideClick: false,
            }).then((result) => {
              if (result.isConfirmed) {
                showLoading("Suppression en cours...");
                setTimeout(() => form.submit(), 100);
              }
            });
          } else {
            if (
              confirm(`Voulez-vous vraiment supprimer le plat "${dishName}" ?`)
            ) {
              form.submit();
            }
          }
        });
      });
  }

  /**
   * Affiche un loader SweetAlert
   */
  function showLoading(message = "Traitement en cours...") {
    if (typeof Swal === "undefined") return;

    Swal.fire({
      title: message,
      text: "Veuillez patienter",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });
  }

  /**
   * Configure les confirmations de suppression d'images (mode images)
   */
  function setupImageDeletionConfirmations() {
    // Attendre que le DOM soit complètement chargé
    setTimeout(() => {
      // Sélectionner TOUS les boutons de suppression
      const deleteButtons = document.querySelectorAll(
        'button[name="delete_image"]',
      );

      console.log(`Trouvé ${deleteButtons.length} boutons de suppression`);

      deleteButtons.forEach((button, index) => {
        // Supprimer TOUS les anciens écouteurs en clonant l'élément
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);

        // Ajouter le nouvel écouteur
        newButton.addEventListener("click", handleImageDelete);

        console.log(`Bouton ${index + 1} réinitialisé`);
      });
    }, 1000); // Délai plus long pour s'assurer que tout est chargé
  }

  /**
   * Gestionnaire de suppression d'image - VERSION CHAMP CACHÉ
   */
  function handleImageDelete(e) {
    e.preventDefault();
    e.stopPropagation();

    const form = this.closest("form");
    if (!form) return;

    const imageCard = this.closest(".image-card");
    const imageName =
      imageCard.querySelector(".image-name")?.textContent?.trim() ||
      "cette image";
    const isPDF = imageCard.querySelector(".pdf-preview") !== null;
    const fileType = isPDF ? "PDF" : "image";
    const imageId = form.querySelector('input[name="image_id"]').value;

    Swal.fire({
      title: "Confirmer la suppression",
      html: `Voulez-vous vraiment supprimer l'${fileType} <strong>"${imageName}"</strong> ?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Oui, supprimer",
      cancelButtonText: "Annuler",
    }).then((result) => {
      if (result.isConfirmed) {
        // Créer un formulaire caché et le soumettre
        const hiddenForm = document.createElement("form");
        hiddenForm.method = "POST";
        hiddenForm.action = form.action || window.location.href;
        hiddenForm.style.display = "none";

        // Ajouter les champs nécessaires
        const imageIdInput = document.createElement("input");
        imageIdInput.type = "hidden";
        imageIdInput.name = "image_id";
        imageIdInput.value = imageId;

        const deleteInput = document.createElement("input");
        deleteInput.type = "hidden";
        deleteInput.name = "delete_image";
        deleteInput.value = "1";

        const anchorInput = document.createElement("input");
        anchorInput.type = "hidden";
        anchorInput.name = "anchor";
        anchorInput.value = "images-list";

        hiddenForm.appendChild(imageIdInput);
        hiddenForm.appendChild(deleteInput);
        hiddenForm.appendChild(anchorInput);

        document.body.appendChild(hiddenForm);
        hiddenForm.submit();
      }
    });
  }

  /**
   * Configure la validation des prix
   */
  function setupPriceValidation() {
    const priceForms = document.querySelectorAll(".new-dish-form, .edit-form");

    priceForms.forEach((form) => {
      form.addEventListener("submit", function (e) {
        const priceInput = form.querySelector("input[name='dish_price']");
        if (!priceInput) return;

        // Remplacer la virgule par un point si nécessaire
        let priceValue = priceInput.value.replace(",", ".");

        // Valider que c'est un nombre
        let price = parseFloat(priceValue);

        if (isNaN(price) || price < 0) {
          e.preventDefault();

          // Vérifier que SweetAlert est disponible
          if (typeof Swal === "undefined") {
            alert("Veuillez saisir un prix valide pour le plat.");
            priceInput.focus();
            return;
          }

          Swal.fire({
            title: "Erreur",
            text: "Veuillez saisir un prix valide pour le plat.",
            icon: "error",
            confirmButtonColor: "#3085d6",
          });
          priceInput.focus();
          return;
        }

        // Formater à 2 décimales
        priceInput.value = price.toFixed(2);
      });
    });

    // Validation en temps réel pour une meilleure UX
    document.querySelectorAll("input[name='dish_price']").forEach((input) => {
      input.addEventListener("blur", function () {
        let priceValue = this.value.replace(",", ".");
        let price = parseFloat(priceValue);

        if (!isNaN(price) && price >= 0) {
          this.value = price.toFixed(2);
        }
      });
    });
  }

  /**
   * Formate automatiquement les champs de prix
   */
  function setupPriceAutoFormat() {
    document.querySelectorAll(".price-input").forEach((input) => {
      input.addEventListener("input", function (e) {
        // Autoriser uniquement les chiffres, point et virgule
        this.value = this.value.replace(/[^0-9.,]/g, "");

        // Remplacer les virgules par des points
        if (this.value.includes(",")) {
          this.value = this.value.replace(",", ".");
        }

        // Empêcher plus d'un point décimal
        const parts = this.value.split(".");
        if (parts.length > 2) {
          this.value = parts[0] + "." + parts.slice(1).join("");
        }
      });
    });
  }

  /**
   * Configure les gestionnaires de formulaires
   */
  function setupFormHandlers() {
    // Stocker l'ancre avant soumission
    document.querySelectorAll("form").forEach((form) => {
      form.addEventListener("submit", function (e) {
        const anchorInput = this.querySelector('input[name="anchor"]');
        if (anchorInput && anchorInput.value) {
          sessionStorage.setItem("pending_scroll", anchorInput.value);
        }
      });
    });

    // Gérer le scroll depuis le sessionStorage
    const pendingScroll = sessionStorage.getItem("pending_scroll");
    if (pendingScroll) {
      handleAnchorScroll(pendingScroll, CONFIG.scrollDelay);
      sessionStorage.removeItem("pending_scroll");
    }
  }

  /**
   * Configure les boutons "Tout (dé)cocher" pour les allergènes
   */
  function setupAllergenesToggles() {
    document.querySelectorAll(".btn-allergenes-toggle").forEach((btn) => {
      // Supprimer les anciens écouteurs pour éviter les doublons
      btn.removeEventListener("click", handleAllergenesToggle);
      btn.addEventListener("click", handleAllergenesToggle);
    });
  }

  /**
   * Gestionnaire du clic sur le bouton "Tout (dé)cocher"
   */
  function handleAllergenesToggle(e) {
    e.preventDefault();
    const targetId = this.dataset.target;
    const grid = document.getElementById(targetId);
    if (!grid) return;

    const checkboxes = grid.querySelectorAll('input[type="checkbox"]');
    const allChecked = Array.from(checkboxes).every((cb) => cb.checked);
    checkboxes.forEach((cb) => (cb.checked = !allChecked));
  }

  /**
   * Rafraîchit les confirmations (utile après AJAX)
   */
  window.refreshConfirmations = function () {
    setupDeleteConfirmations();
    setupImageDeletionConfirmations();
  };

  /**
   * Fait défiler vers une ancre avec délai
   */
  window.scrollToAnchor = function (anchorId, delay = CONFIG.scrollDelay) {
    handleAnchorScroll(anchorId, delay);
  };

  /**
   * API principale
   */
  window.EditCard = {
    init: init,
    scrollToAnchor: window.scrollToAnchor,
    refreshConfirmations: window.refreshConfirmations,
  };

  // Initialisation automatique
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    // Déjà chargé, initialiser après un court délai
    setTimeout(init, 100);
  }
})();
