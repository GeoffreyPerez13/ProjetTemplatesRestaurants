(function () {
  "use strict";

  // Configuration
  const CONFIG = {
    scrollDelay: 500,
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

    // ==================== SCROLL VERS L'ANCRE ====================
    const anchor = getUrlParameter("anchor");
    if (anchor) {
      handleAnchorScroll(anchor, CONFIG.scrollDelay);
    }

    // ==================== GESTION DES MESSAGES ====================
    setupMessageHandlers();

    // ==================== CONFIRMATION DE SUPPRESSION ====================
    setupDeleteConfirmations();

    // ==================== GESTION DES ACCORDÉONS APRÈS ACTIONS ====================
    handleAccordionActions();

    // ==================== CONFIGURATION SPÉCIFIQUE AU MODE ====================
    const isImagesMode =
      document.querySelector(".images-mode-container") !== null;

    if (isImagesMode) {
      // ==================== CONFIRMATION DE SUPPRESSION D'IMAGES ====================
      setupImageDeleteConfirmations();
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
  function handleAccordionActions() {
    // Fermer l'accordéon spécifié
    const closeAccordionId =
      getUrlParameter("close_accordion") || window.scrollParams?.closeAccordion;
    if (closeAccordionId) {
      setTimeout(() => {
        if (window.AccordionManager) {
          window.AccordionManager.closeAccordion(closeAccordionId);
        } else {
          closeAccordion(closeAccordionId);
        }
      }, 500);
    }

    // Ouvrir l'accordéon spécifié
    const openAccordionId =
      getUrlParameter("open_accordion") || window.scrollParams?.openAccordion;
    if (openAccordionId) {
      setTimeout(() => {
        if (window.AccordionManager) {
          window.AccordionManager.openAccordion(openAccordionId);
        } else {
          openAccordion(openAccordionId);
        }
      }, 600);
    }

    // Fermer tous les plats d'une catégorie
    const closeAllDishesId =
      getUrlParameter("close_all_dishes") ||
      window.scrollParams?.closeAllDishes;
    if (closeAllDishesId) {
      setTimeout(() => {
        closeAllDishesInCategory(closeAllDishesId);
      }, 700);
    }

    // Fermer l'accordéon spécifique d'un plat
    const closeDishAccordionId =
      getUrlParameter("close_dish_accordion") ||
      window.scrollParams?.closeDishAccordion;
    if (closeDishAccordionId) {
      setTimeout(() => {
        if (window.AccordionManager) {
          window.AccordionManager.closeDishAccordion(closeDishAccordionId);
        }
      }, 800);
    }

    // Fermer l'accordéon secondaire
    const closeAccordionSecondaryId =
      getUrlParameter("close_accordion_secondary") ||
      window.scrollParams?.closeAccordionSecondary;
    if (closeAccordionSecondaryId) {
      setTimeout(() => {
        if (window.AccordionManager) {
          window.AccordionManager.closeAccordion(closeAccordionSecondaryId);
        }
      }, 700);
    }
  }

  /**
   * Ferme tous les plats d'une catégorie
   */
  function closeAllDishesInCategory(categoryId) {
    document
      .querySelectorAll(
        `.dish-accordion-content[data-category="${categoryId}"]`,
      )
      .forEach((dishSection) => {
        if (dishSection.classList.contains("expanded")) {
          if (window.AccordionManager) {
            window.AccordionManager.closeDishAccordion(dishSection.id);
          } else {
            closeDishAccordion(dishSection.id);
          }
        }
      });
  }

  /**
   * Configure les gestionnaires de messages (5 secondes, pas de croix)
   */
  function setupMessageHandlers() {
    const messages = document.querySelectorAll(
      ".message-success, .message-error",
    );

    messages.forEach((message) => {
      // Auto-dismiss après 5 secondes
      setTimeout(() => {
        message.style.opacity = "0";
        message.style.transition = "opacity 0.5s ease";

        setTimeout(() => {
          if (message.parentNode) {
            message.parentNode.removeChild(message);
          }
        }, 500);
      }, 5000); // 5 secondes
    });
  }

  /**
   * Configure les confirmations de suppression pour catégories, plats et images
   */
  function setupDeleteConfirmations() {
    // ==================== 1. BOUTONS "SUPPRIMER L'IMAGE" DES PLATS ====================
    document
      .querySelectorAll('button[name="remove_dish_image"]')
      .forEach((button) => {
        button.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();

          // Trouver le formulaire et les données nécessaires
          const form = this.closest("form.edit-form");
          if (!form) {
            return;
          }

          // Récupérer les données du plat
          const dishId = form.querySelector('input[name="dish_id"]')?.value;
          const categoryId = form.querySelector(
            'input[name="current_category_id"]',
          )?.value;
          const dishName =
            form.querySelector('input[name="dish_name"]')?.value?.trim() ||
            "ce plat";

          // Confirmation avec SweetAlert si disponible, sinon confirm() natif
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
                submitRemoveDishImage(dishId, categoryId, button.value);
              }
            });
          } else {
            if (
              confirm(
                `Voulez-vous vraiment supprimer l'image du plat "${dishName}" ?`,
              )
            ) {
              submitRemoveDishImage(dishId, categoryId, button.value);
            }
          }
        });

        // Fonction pour soumettre la suppression d'image de plat
        function submitRemoveDishImage(dishId, categoryId, buttonValue) {
          // Créer un formulaire dynamique
          const dynamicForm = document.createElement('form');
          dynamicForm.method = 'POST';
          dynamicForm.action = window.location.pathname + window.location.search;
          dynamicForm.style.display = 'none';
          
          // Ajouter le paramètre page si nécessaire
          const urlParams = new URLSearchParams(window.location.search);
          const pageParam = urlParams.get('page');
          if (pageParam) {
              const pageInput = document.createElement('input');
              pageInput.type = 'hidden';
              pageInput.name = 'page';
              pageInput.value = pageParam;
              dynamicForm.appendChild(pageInput);
          }
          
          // Ajouter tous les champs nécessaires
          const fields = [
              { name: 'remove_dish_image', value: buttonValue },
              { name: 'dish_id', value: dishId },
              { name: 'current_category_id', value: categoryId },
              { name: 'anchor', value: `category-${categoryId}` }
          ];
          
          fields.forEach(field => {
              const input = document.createElement('input');
              input.type = 'hidden';
              input.name = field.name;
              input.value = field.value;
              dynamicForm.appendChild(input);
          });
          
          // Ajouter le formulaire au DOM
          document.body.appendChild(dynamicForm);
          
          // Soumettre le formulaire
          try {
              dynamicForm.submit();
          } catch (error) {
              // Essayer avec une redirection GET
              const params = new URLSearchParams();
              fields.forEach(field => {
                  params.append(field.name, field.value);
              });
              if (pageParam) params.append('page', pageParam);
              
              window.location.href = `${window.location.pathname}?${params.toString()}`;
          }
        }
      });

    // ==================== 2. BOUTONS "SUPPRIMER L'IMAGE" DES CATÉGORIES ====================
    document
      .querySelectorAll('button[name="remove_category_image"]')
      .forEach((button) => {
        button.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();

          const form = this.closest("form.edit-category-form");
          if (!form) return;

          const categoryId = form.querySelector(
            'input[name="category_id"]',
          )?.value;
          const categoryName =
            form
              .querySelector('input[name="edit_category_name"]')
              ?.value?.trim() || "cette catégorie";

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
                submitRemoveCategoryImage(categoryId, button.value);
              }
            });
          } else {
            if (
              confirm(
                `Voulez-vous vraiment supprimer l'image de la catégorie "${categoryName}" ?`,
              )
            ) {
              submitRemoveCategoryImage(categoryId, button.value);
            }
          }
        });

        function submitRemoveCategoryImage(categoryId, buttonValue) {
          const dynamicForm = document.createElement("form");
          dynamicForm.method = "POST";
          dynamicForm.action =
            window.location.pathname + window.location.search;
          dynamicForm.style.display = "none";

          const fields = [
            { name: "remove_category_image", value: buttonValue },
            { name: "category_id", value: categoryId },
            { name: "anchor", value: `category-${categoryId}` },
          ];

          fields.forEach((field) => {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = field.name;
            input.value = field.value;
            dynamicForm.appendChild(input);
          });

          document.body.appendChild(dynamicForm);
          dynamicForm.submit();
        }
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
   * Configure les confirmations de suppression pour les images en mode images
   */
  function setupImageDeleteConfirmations() {
    // Sélectionner les formulaires de suppression d'image dans le mode images
    const imageDeleteForms = document.querySelectorAll(
      ".images-grid .inline-form",
    );

    imageDeleteForms.forEach((form) => {
      const deleteButton = form.querySelector('button[name="delete_image"]');

      if (deleteButton) {
        form.addEventListener("submit", function (e) {
          e.preventDefault();

          // Trouver le nom de l'image
          const imageCard = form.closest(".image-card");
          const imageNameElement = imageCard
            ? imageCard.querySelector(".image-name")
            : null;
          const imageName = imageNameElement
            ? imageNameElement.textContent.trim()
            : "cette image";

          // Vérifier si c'est un PDF ou une image
          const pdfPreview = imageCard
            ? imageCard.querySelector(".pdf-preview")
            : null;
          const fileType = pdfPreview ? "PDF" : "image";

          // Vérifier que SweetAlert est disponible
          if (typeof Swal === "undefined") {
            form.submit();
            return;
          }

          Swal.fire({
            title: "Confirmer la suppression",
            html: `Voulez-vous vraiment supprimer l'${fileType} <strong>"${imageName}"</strong> ?<br><br>`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Oui, supprimer",
            cancelButtonText: "Annuler",
            backdrop: true,
            allowOutsideClick: false,
            width: 500,
          }).then((result) => {
            if (result.isConfirmed) {
              // Afficher un loader pendant la suppression
              showLoading("Suppression en cours...");

              // Soumettre le formulaire
              setTimeout(() => {
                form.submit();
              }, 100);
            }
          });
        });
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
    closeAllDishesInCategory(categoryId);
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