(function () {
  "use strict";

  // Configuration
  const CONFIG = {
    scrollDelay: 3500, // 3,5 secondes par défaut (comme dans PHP)
    animationDuration: 300,
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

    // ==================== GESTION DES MESSAGES ====================
    setupMessageHandlers();

    // ==================== CONFIRMATION DE SUPPRESSION ====================
    setupDeleteConfirmations();

    // ==================== GESTION DES ACCORDÉONS APRÈS ACTIONS ====================
    handleAccordionActions(scrollParams);

    // ==================== CONFIGURATION SPÉCIFIQUE AU MODE ====================
    const isImagesMode =
      document.querySelector(".images-mode-container") !== null;

    if (isImagesMode) {
      // Désactiver SweetAlert TEMPORAIREMENT pour les suppressions
      disableSweetAlertForDeletes();

      // Appeler notre fonction simplifiée
      setupImageDeleteConfirmations();

      // ==================== INITIALISATION DU DRAG & DROP ====================
      setTimeout(() => {
        if (window.ImageReorder) {
          window.ImageReorder.init();
        }
      }, 200);
    } else {
      // ==================== VALIDATION DES PRIX ====================
      setupPriceValidation();

      // ==================== GESTION DES ACCORDÉONS PAR CATÉGORIE ====================
      setupCategoryAccordionControls();

      // ==================== FORMATAGE AUTOMATIQUE DES PRIX ====================
      setupPriceAutoFormat();
    }

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
        }, 2000);
      }
    }, delay);
  }

  /**
   * Gère les actions sur les accordéons après soumission
   */
  function handleAccordionActions(scrollParams) {
    // Fermer l'accordéon principal
    if (scrollParams.closeAccordion) {
      setTimeout(() => {
        if (window.AccordionManager) {
          window.AccordionManager.closeAccordion(scrollParams.closeAccordion);
        } else {
          closeAccordion(scrollParams.closeAccordion);
        }
      }, 500);
    }

    // Fermer l'accordéon secondaire
    if (scrollParams.closeAccordionSecondary) {
      setTimeout(() => {
        if (window.AccordionManager) {
          window.AccordionManager.closeAccordion(
            scrollParams.closeAccordionSecondary,
          );
        } else {
          closeAccordion(scrollParams.closeAccordionSecondary);
        }
      }, 600);
    }

    // Ouvrir l'accordéon spécifié
    if (scrollParams.openAccordion) {
      setTimeout(() => {
        if (window.AccordionManager) {
          window.AccordionManager.openAccordion(scrollParams.openAccordion);
        } else {
          openAccordion(scrollParams.openAccordion);
        }
      }, 700);
    }

    // Fermer l'accordéon spécifique d'un plat
    if (scrollParams.closeDishAccordion) {
      setTimeout(() => {
        if (window.AccordionManager) {
          window.AccordionManager.closeDishAccordion(
            scrollParams.closeDishAccordion,
          );
        } else {
          closeDishAccordion(scrollParams.closeDishAccordion);
        }
      }, 800);
    }
  }

  /**
   * Configure les gestionnaires de messages (5 secondes, pas de croix)
   */
  function setupMessageHandlers() {
    const messages = document.querySelectorAll(
      ".message-success, .message-error",
    );

    messages.forEach((message) => {
      // Auto-dismiss après le délai configuré ou 3,5 secondes par défaut
      const scrollDelay = window.scrollParams?.scrollDelay || 3500;
      setTimeout(() => {
        message.style.opacity = "0";
        message.style.transition = "opacity 0.5s ease";

        setTimeout(() => {
          if (message.parentNode) {
            message.parentNode.removeChild(message);
          }
        }, 500);
      }, scrollDelay);
    });
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
   * Configuration SIMPLIFIÉE des confirmations de suppression d'images
   */
  function setupImageDeleteConfirmations() {
    console.log("=== SETUP IMAGE DELETE CONFIRMATIONS ===");

    // Sélectionner tous les boutons de suppression d'image
    const deleteButtons = document.querySelectorAll(".delete-image-btn");
    console.log("Delete buttons found:", deleteButtons.length);

    deleteButtons.forEach((button) => {
      console.log("Setting up button:", button);

      // Supprimer tous les événements existants
      const newButton = button.cloneNode(true);
      button.parentNode.replaceChild(newButton, button);

      // Ajouter notre propre gestionnaire
      newButton.addEventListener("click", function (e) {
        console.log("=== DELETE BUTTON CLICKED ===");
        e.preventDefault();
        e.stopPropagation();

        const form = this.closest("form");
        if (!form) {
          console.error("No form found!");
          return;
        }

        const imageId = this.getAttribute("data-image-id");
        const imageName = this.getAttribute("data-image-name");
        const isPDF =
          this.closest(".image-card").querySelector(".pdf-preview") !== null;
        const fileType = isPDF ? "PDF" : "image";

        console.log("Image ID:", imageId);
        console.log("Image name:", imageName);
        console.log("File type:", fileType);

        // Confirmation SIMPLE sans SweetAlert
        if (
          confirm(
            `Voulez-vous vraiment supprimer l'${fileType} "${imageName}" ?`,
          )
        ) {
          console.log("User confirmed - submitting form");

          // Ajouter un timestamp pour éviter le cache
          const timestamp = Date.now();
          const timestampInput = document.createElement("input");
          timestampInput.type = "hidden";
          timestampInput.name = "timestamp";
          timestampInput.value = timestamp;
          form.appendChild(timestampInput);

          // Soumettre le formulaire
          form.submit();
        } else {
          console.log("User cancelled");
        }
      });
    });
  }

  /**
   * Confirmation simple de suppression d'image
   */
  function confirmDeleteImage(form) {
    const imageCard = form.closest(".image-card");
    const imageName = imageCard.querySelector(".image-name").textContent.trim();
    const isPDF = imageCard.querySelector(".pdf-preview") !== null;
    const fileType = isPDF ? "PDF" : "image";

    if (typeof Swal !== "undefined") {
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
          form.submit();
        }
      });
      return false; // Empêche la soumission immédiate
    } else {
      return confirm(
        `Voulez-vous vraiment supprimer l'${fileType} "${imageName}" ?`,
      );
    }
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
   * Configure les contrôles d'accordéon par catégorie
   */
  function setupCategoryAccordionControls() {
    // Bouton "Tout ouvrir" pour une catégorie
    document.querySelectorAll(".expand-category").forEach((btn) => {
      btn.addEventListener("click", function () {
        const categoryId = this.dataset.categoryId;
        expandAllInCategory(categoryId);
      });
    });

    // Bouton "Tout fermer" pour une catégorie
    document.querySelectorAll(".collapse-category").forEach((btn) => {
      btn.addEventListener("click", function () {
        const categoryId = this.dataset.categoryId;
        collapseAllInCategory(categoryId);
      });
    });
  }

  /**
   * Ouvre tous les accordéons d'une catégorie
   */
  function expandAllInCategory(categoryId) {
    const categoryBlock = document.querySelector(`#category-${categoryId}`);
    if (!categoryBlock) return;

    // Ouvrir tous les accordéons de catégorie
    categoryBlock
      .querySelectorAll(".accordion-content.collapsed")
      .forEach((content) => {
        const toggleBtn =
          content.previousElementSibling?.querySelector(".accordion-toggle");
        if (toggleBtn && !content.classList.contains("expanded")) {
          toggleBtn.click();
        }
      });

    // Ouvrir tous les accordéons de plat
    categoryBlock
      .querySelectorAll(".dish-accordion-content.collapsed")
      .forEach((content) => {
        const toggleBtn = content.previousElementSibling?.querySelector(
          ".dish-accordion-toggle",
        );
        if (toggleBtn && !content.classList.contains("expanded")) {
          toggleBtn.click();
        }
      });
  }

  /**
   * Ferme tous les accordéons d'une catégorie
   */
  function collapseAllInCategory(categoryId) {
    const categoryBlock = document.querySelector(`#category-${categoryId}`);
    if (!categoryBlock) return;

    // Fermer tous les accordéons de catégorie
    categoryBlock
      .querySelectorAll(".accordion-content.expanded")
      .forEach((content) => {
        const toggleBtn =
          content.previousElementSibling?.querySelector(".accordion-toggle");
        if (toggleBtn && !content.classList.contains("collapsed")) {
          toggleBtn.click();
        }
      });

    // Fermer tous les accordéons de plat
    categoryBlock
      .querySelectorAll(".dish-accordion-content.expanded")
      .forEach((content) => {
        const toggleBtn = content.previousElementSibling?.querySelector(
          ".dish-accordion-toggle",
        );
        if (toggleBtn && !content.classList.contains("collapsed")) {
          toggleBtn.click();
        }
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
   * Ferme un accordéon principal (fallback)
   */
  function closeAccordion(accordionId) {
    const accordion = document.getElementById(accordionId);
    if (!accordion) return;

    accordion.classList.remove("expanded");
    accordion.classList.add("collapsed");

    const toggle = document.querySelector(
      `.accordion-toggle[data-target="${accordionId}"]`,
    );
    if (toggle) {
      const icon = toggle.querySelector("i");
      if (icon) {
        icon.classList.remove("fa-chevron-up");
        icon.classList.add("fa-chevron-down");
      }
    }
  }

  /**
   * Ouvre un accordéon principal (fallback)
   */
  function openAccordion(accordionId) {
    const accordion = document.getElementById(accordionId);
    if (!accordion) return;

    accordion.classList.remove("collapsed");
    accordion.classList.add("expanded");

    const toggle = document.querySelector(
      `.accordion-toggle[data-target="${accordionId}"]`,
    );
    if (toggle) {
      const icon = toggle.querySelector("i");
      if (icon) {
        icon.classList.remove("fa-chevron-down");
        icon.classList.add("fa-chevron-up");
      }
    }
  }

  /**
   * Ferme un accordéon de plat (fallback)
   */
  function closeDishAccordion(accordionId) {
    const accordion = document.getElementById(accordionId);
    if (!accordion) return;

    accordion.classList.remove("expanded");
    accordion.classList.add("collapsed");
    accordion.style.display = "none";

    const toggle = document.querySelector(
      `.dish-accordion-toggle[data-target="${accordionId}"]`,
    );
    if (toggle) {
      const icon = toggle.querySelector("i");
      if (icon) {
        icon.classList.remove("fa-chevron-up");
        icon.classList.add("fa-chevron-down");
      }
    }
  }

  // ==================== FONCTIONS D'API PUBLIQUE ====================

  /**
   * Ferme un accordéon spécifique
   */
  window.closeAccordion = function (accordionId) {
    const accordionContent = document.getElementById(accordionId);
    if (accordionContent && accordionContent.classList.contains("expanded")) {
      const toggle = document.querySelector(
        `.accordion-toggle[data-target="${accordionId}"]`,
      );
      if (toggle) {
        toggle.click();
      }
    }
  };

  /**
   * Ferme tous les accordéons d'une catégorie
   */
  window.collapseCategory = function (categoryId) {
    // Fermer les sections principales
    document
      .querySelectorAll(
        `[id="edit-category-${categoryId}"],
       [id="add-dish-${categoryId}"],
       [id="edit-dishes-${categoryId}"]`,
      )
      .forEach((section) => {
        if (section.classList.contains("expanded")) {
          const toggle = document.querySelector(
            `.accordion-toggle[data-target="${section.id}"]`,
          );
          if (toggle) {
            toggle.click();
          }
        }
      });

    // Fermer tous les plats
    setTimeout(() => {
      document
        .querySelectorAll(
          `.dish-accordion-content[data-category="${categoryId}"]`,
        )
        .forEach((dishSection) => {
          if (dishSection.style.display === "block") {
            const toggle = document.querySelector(
              `.dish-accordion-toggle[data-target="${dishSection.id}"]`,
            );
            if (toggle) {
              toggle.click();
            }
          }
        });
    }, 100);
  };

  /**
   * Ferme tous les plats d'une catégorie
   */
  window.closeAllDishesInCategory = function (categoryId) {
    const categoryBlock = document.querySelector(`#category-${categoryId}`);
    if (!categoryBlock) return;

    categoryBlock
      .querySelectorAll(".dish-accordion-content.expanded")
      .forEach((content) => {
        const toggleBtn = content.previousElementSibling?.querySelector(
          ".dish-accordion-toggle",
        );
        if (toggleBtn && !content.classList.contains("collapsed")) {
          toggleBtn.click();
        }
      });
  };

  /**
   * Rafraîchit les confirmations (utile après AJAX)
   */
  window.refreshConfirmations = function () {
    setupDeleteConfirmations();
    setupImageDeleteConfirmations();
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
    closeAccordion: window.closeAccordion,
    collapseCategory: window.collapseCategory,
    closeAllDishesInCategory: window.closeAllDishesInCategory,
    refreshConfirmations: window.refreshConfirmations,
    expandAllInCategory: expandAllInCategory,
    collapseAllInCategory: collapseAllInCategory,
  };

  // Initialisation automatique
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    // Déjà chargé, initialiser après un court délai
    setTimeout(init, 100);
  }
})();
