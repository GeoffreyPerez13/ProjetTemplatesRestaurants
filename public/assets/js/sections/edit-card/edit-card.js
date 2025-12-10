// edit-card.js - Gestion de l'édition de la carte
document.addEventListener("DOMContentLoaded", function () {
  // ==================== SCROLL VERS L'ANCRE ====================
  const anchor = getUrlParameter("anchor");
  if (anchor) {
    scrollToAnchor(anchor);
  }

  // ==================== CONFIRMATION DE SUPPRESSION ====================
  setupDeleteConfirmations();

  // ==================== CONFIRMATION DE SUPPRESSION D'IMAGES ====================
  setupImageDeleteConfirmations();

  // ==================== VALIDATION DES PRIX ====================
  setupPriceValidation();

  // ==================== GESTION DES ACCORDÉONS PAR CATÉGORIE ====================
  setupCategoryAccordionControls();

  // ==================== FORMATAGE AUTOMATIQUE DES PRIX ====================
  setupPriceAutoFormat();
});

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
 * Scroll vers une ancre spécifique
 */
function scrollToAnchor(anchorId) {
  setTimeout(function () {
    const element = document.getElementById(anchorId);
    if (element) {
      element.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });

      // Ouvrir l'accordéon parent si nécessaire
      const accordionSection = element.closest(".accordion-section");
      if (accordionSection) {
        const accordionContent =
          accordionSection.querySelector(".accordion-content");
        const accordionToggle = accordionSection.querySelector(
          ".accordion-toggle i"
        );

        if (
          accordionContent &&
          accordionContent.classList.contains("collapsed")
        ) {
          const toggleBtn = accordionSection.querySelector(".accordion-toggle");
          if (toggleBtn) {
            toggleBtn.click();
          }
        }

        // Pour les sous-sections (plats), ouvrir aussi le parent
        const dishAccordion = element.closest(".dish-accordion-content");
        if (dishAccordion && dishAccordion.classList.contains("collapsed")) {
          const dishToggle =
            dishAccordion.previousElementSibling?.querySelector(
              ".dish-accordion-toggle"
            );
          if (dishToggle) {
            dishToggle.click();
          }
        }
      }
    }
  }, 300);
}

/**
 * Configure les confirmations de suppression pour catégories et plats
 */
function setupDeleteConfirmations() {
  document.querySelectorAll("form.inline-form").forEach((form) => {
    const deleteCategory = form.querySelector("input[name='delete_category']");
    const deleteDish = form.querySelector("input[name='delete_dish']");
    const deleteImage = form.querySelector('button[name="delete_image"]');

    // Ne pas appliquer aux images (elles ont leur propre gestion)
    if (deleteImage) return;

    if (deleteCategory || deleteDish) {
      form.addEventListener("submit", function (e) {
        e.preventDefault();

        let itemName = "";
        let type = "";
        let warningMessage = "";

        if (deleteCategory) {
          const categoryBlock = form.closest(".category-block");
          const categoryNameElement = categoryBlock.querySelector("strong");
          itemName = categoryNameElement
            ? categoryNameElement.textContent.trim()
            : "cette catégorie";
          type = "la catégorie";

          const dishList = categoryBlock.querySelector(".dish-list");
          const hasPlats = dishList && dishList.querySelector("li");

          if (hasPlats) {
            warningMessage =
              "\n\n⚠️ Attention, tous les plats associés seront également supprimés !";
          }
        } else if (deleteDish) {
          const dishEditContainer = form.closest(".dish-edit-container");
          const dishNameInput = dishEditContainer.querySelector(
            'input[name="dish_name"]'
          );
          itemName = dishNameInput ? dishNameInput.value.trim() : "cet élément";
          type = "le plat";
        }

        Swal.fire({
          title: "Confirmer la suppression",
          text: `Voulez-vous vraiment supprimer ${type} "${itemName}" ?${warningMessage}`,
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
            Swal.fire({
              title: "Suppression en cours...",
              text: "Veuillez patienter",
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              },
            });

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
 * Configure les confirmations de suppression pour les images
 */
function setupImageDeleteConfirmations() {
  // Sélectionner les formulaires de suppression d'image dans le mode images
  const imageDeleteForms = document.querySelectorAll(
    ".images-grid .inline-form"
  );

  imageDeleteForms.forEach((form) => {
    const deleteButton = form.querySelector('button[name="delete_image"]');

    if (deleteButton) {
      form.addEventListener("submit", function (e) {
        e.preventDefault();

        // Trouver le nom de l'image
        const imageCard = form.closest(".image-card");
        const imageNameElement = imageCard.querySelector(".image-name");
        const imageName = imageNameElement
          ? imageNameElement.textContent.trim()
          : "cette image";

        // Vérifier si c'est un PDF ou une image
        const pdfPreview = imageCard.querySelector(".pdf-preview");
        const fileType = pdfPreview ? "PDF" : "image";

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
            Swal.fire({
              title: "Suppression en cours...",
              text: "Veuillez patienter",
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              },
            });

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
        ".dish-accordion-toggle"
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
        ".dish-accordion-toggle"
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
 * Initialise les accordéons (si nécessaire)
 */
function initAccordions() {
  // Gestion des accordéons principaux
  document.querySelectorAll(".accordion-toggle").forEach((button) => {
    button.addEventListener("click", function () {
      const targetId = this.getAttribute("data-target");
      const target = document.getElementById(targetId);
      const icon = this.querySelector("i");

      if (target) {
        target.classList.toggle("expanded");
        target.classList.toggle("collapsed");

        // Changer l'icône
        if (icon) {
          if (target.classList.contains("expanded")) {
            icon.classList.remove("fa-chevron-down");
            icon.classList.add("fa-chevron-up");
          } else {
            icon.classList.remove("fa-chevron-up");
            icon.classList.add("fa-chevron-down");
          }
        }
      }
    });
  });

  // Gestion des accordéons de plats
  document.querySelectorAll(".dish-accordion-toggle").forEach((button) => {
    button.addEventListener("click", function () {
      const targetId = this.getAttribute("data-target");
      const target = document.getElementById(targetId);
      const icon = this.querySelector("i");

      if (target) {
        target.classList.toggle("expanded");
        target.classList.toggle("collapsed");

        // Changer l'icône
        if (icon) {
          if (target.classList.contains("expanded")) {
            icon.classList.remove("fa-chevron-down");
            icon.classList.add("fa-chevron-up");
          } else {
            icon.classList.remove("fa-chevron-up");
            icon.classList.add("fa-chevron-down");
          }
        }
      }
    });
  });
}

// Initialiser les accordéons au chargement
initAccordions();

// Fonction pour rafraîchir les confirmations (utile après AJAX)
function refreshConfirmations() {
  setupDeleteConfirmations();
  setupImageDeleteConfirmations();
}

// Export des fonctions pour un usage externe si nécessaire
if (typeof module !== "undefined" && module.exports) {
  module.exports = {
    getUrlParameter,
    scrollToAnchor,
    setupDeleteConfirmations,
    setupImageDeleteConfirmations,
    setupPriceValidation,
    setupCategoryAccordionControls,
    setupPriceAutoFormat,
    expandAllInCategory,
    collapseAllInCategory,
    initAccordions,
    refreshConfirmations,
  };
}
