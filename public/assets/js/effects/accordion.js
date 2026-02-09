// accordion.js - Gestion des accordéons
(function () {
  "use strict";

  /**
   * Initialisation principale
   */
  function init() {
    // Fermer tous les accordéons sauf le premier au démarrage
    closeAllExceptFirst();

    // Gestion des accordéons principaux
    setupMainAccordions();

    // Gestion des accordéons de plats
    setupDishAccordions();

    // Contrôles par catégorie
    setupCategoryControls();

    // Contrôles généraux (tout ouvrir/tout fermer)
    setupGlobalControls();
  }

  /**
   * Ferme tous les accordéons sauf le premier (mode-selector)
   */
  function closeAllExceptFirst() {
    setTimeout(() => {
      const allAccordions = document.querySelectorAll(".accordion-content");
      const firstAccordion = document.getElementById("mode-selector-content");

      allAccordions.forEach((accordion) => {
        if (
          accordion !== firstAccordion &&
          accordion.classList.contains("expanded")
        ) {
          closeAccordion(accordion.id);
        }
      });

      // S'assurer que le premier est ouvert
      if (firstAccordion && firstAccordion.classList.contains("collapsed")) {
        openAccordion(firstAccordion.id);
      }
    }, 50);
  }

  /**
   * Configure les accordéons principaux
   */
  function setupMainAccordions() {
    document.querySelectorAll(".accordion-header").forEach((header) => {
      const toggle = header.querySelector(".accordion-toggle");
      if (!toggle) return;

      const targetId = toggle.getAttribute("data-target");
      const target = document.getElementById(targetId);

      if (!target) return;

      // Vérifier si l'accordéon est déjà initialisé
      if (toggle.hasAttribute("data-accordion-initialized")) {
        return;
      }

      toggle.setAttribute("data-accordion-initialized", "true");

      // Ajouter l'événement de clic sur tout le header
      header.addEventListener("click", function (e) {
        // Ne pas déclencher si on clique sur un lien, un bouton ou un input à l'intérieur du header
        if (
          e.target.tagName === "A" ||
          e.target.tagName === "BUTTON" ||
          e.target.tagName === "INPUT"
        ) {
          return;
        }
        toggleAccordion(target, toggle);
      });

      // Garder aussi l'événement sur le bouton pour la compatibilité
      toggle.addEventListener("click", function (e) {
        e.stopPropagation(); // Empêche le double déclenchement
        toggleAccordion(target, toggle);
      });
    });
  }

  /**
   * Configure les accordéons de plats
   */
  function setupDishAccordions() {
    document.querySelectorAll(".dish-accordion-header").forEach((header) => {
      const toggle = header.querySelector(".dish-accordion-toggle");
      if (!toggle) return;

      const targetId = toggle.getAttribute("data-target");
      const target = document.getElementById(targetId);

      if (!target) return;

      // Vérifier si l'accordéon est déjà initialisé
      if (toggle.hasAttribute("data-accordion-initialized")) {
        return;
      }

      toggle.setAttribute("data-accordion-initialized", "true");

      // Ajouter l'événement de clic sur tout le header du plat
      header.addEventListener("click", function (e) {
        // Ne pas déclencher si on clique sur un lien, un bouton ou un input à l'intérieur du header
        if (
          e.target.tagName === "A" ||
          e.target.tagName === "BUTTON" ||
          e.target.tagName === "INPUT"
        ) {
          return;
        }
        toggleDishAccordion(target, toggle);
      });

      // Garder aussi l'événement sur le bouton
      toggle.addEventListener("click", function (e) {
        e.stopPropagation(); // Empêche le double déclenchement
        toggleDishAccordion(target, toggle);
      });
    });
  }

  /**
   * Configure les contrôles par catégorie
   */
  function setupCategoryControls() {
    // Contrôles par catégorie - Tout ouvrir
    document.querySelectorAll(".expand-category").forEach((button) => {
      button.addEventListener("click", function () {
        const categoryId = this.getAttribute("data-category-id");
        expandCategory(categoryId);
      });
    });

    // Contrôles par catégorie - Tout fermer
    document.querySelectorAll(".collapse-category").forEach((button) => {
      button.addEventListener("click", function () {
        const categoryId = this.getAttribute("data-category-id");
        collapseCategory(categoryId);
      });
    });
  }

  /**
   * Configure les contrôles généraux
   */
  function setupGlobalControls() {
    const expandAllBtn = document.getElementById("expand-all-accordions");
    const collapseAllBtn = document.getElementById("collapse-all-accordions");

    if (expandAllBtn) {
      expandAllBtn.addEventListener("click", expandAllAccordions);
    }

    if (collapseAllBtn) {
      collapseAllBtn.addEventListener("click", collapseAllAccordions);
    }
  }

  /**
   * Ouvre tous les accordéons
   */
  function expandAllAccordions() {
    // Ouvrir tous les accordéons principaux
    document
      .querySelectorAll(".accordion-content.collapsed")
      .forEach((content) => {
        openAccordion(content.id);
      });

    // Ouvrir tous les accordéons de plats
    document
      .querySelectorAll(".dish-accordion-content.collapsed")
      .forEach((content) => {
        openDishAccordion(content.id);
      });
  }

  /**
   * Ferme tous les accordéons
   */
  function collapseAllAccordions() {
    // Fermer tous les accordéons principaux (INCLUS le mode-selector)
    document
      .querySelectorAll(".accordion-content.expanded")
      .forEach((content) => {
        closeAccordion(content.id);
      });

    // Fermer tous les accordéons de plats
    document
      .querySelectorAll(".dish-accordion-content.expanded")
      .forEach((content) => {
        closeDishAccordion(content.id);
      });
  }

  /**
   * Bascule un accordéon principal
   */
  function toggleAccordion(target, toggle) {
    const isExpanded = target.classList.contains("expanded");

    if (isExpanded) {
      closeAccordion(target.id);
    } else {
      openAccordion(target.id);
    }
  }

  /**
   * Bascule un accordéon de plat
   */
  function toggleDishAccordion(target, toggle) {
    const isExpanded = target.classList.contains("expanded");

    if (isExpanded) {
      closeDishAccordion(target.id);
    } else {
      openDishAccordion(target.id);
    }
  }

  /**
   * Ouvre un accordéon principal
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
        icon.classList.remove("fa-chevron-up");   // ← CHANGÉ (était fa-chevron-down)
        icon.classList.add("fa-chevron-down");    // ← CHANGÉ (était fa-chevron-up)
      }
    }
  }

  /**
   * Ferme un accordéon principal
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
        icon.classList.remove("fa-chevron-down"); // ← CHANGÉ (était fa-chevron-up)
        icon.classList.add("fa-chevron-up");      // ← CHANGÉ (était fa-chevron-down)
      }
    }
  }

  /**
   * Ouvre un accordéon de plat
   */
  function openDishAccordion(accordionId) {
    const accordion = document.getElementById(accordionId);
    if (!accordion) return;

    accordion.classList.remove("collapsed");
    accordion.classList.add("expanded");
    accordion.style.display = "block";

    const toggle = document.querySelector(
      `.dish-accordion-toggle[data-target="${accordionId}"]`,
    );
    if (toggle) {
      const icon = toggle.querySelector("i");
      if (icon) {
        icon.classList.remove("fa-chevron-up");   // ← CHANGÉ (était fa-chevron-down)
        icon.classList.add("fa-chevron-down");    // ← CHANGÉ (était fa-chevron-up)
      }
    }
  }

  /**
   * Ferme un accordéon de plat
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
        icon.classList.remove("fa-chevron-down"); // ← CHANGÉ (était fa-chevron-up)
        icon.classList.add("fa-chevron-up");      // ← CHANGÉ (était fa-chevron-down)
      }
    }
  }

  /**
   * Ouvre toutes les sections d'une catégorie
   */
  function expandCategory(categoryId) {
    // Ouvrir les sections principales
    const mainSections = [
      `edit-category-${categoryId}`,
      `add-dish-${categoryId}`,
      `edit-dishes-${categoryId}`,
    ];

    mainSections.forEach((sectionId) => {
      openAccordion(sectionId);
    });

    // Ouvrir tous les plats de cette catégorie
    setTimeout(() => {
      document
        .querySelectorAll(
          `.dish-accordion-content[data-category="${categoryId}"]`,
        )
        .forEach((dishSection) => {
          openDishAccordion(dishSection.id);
        });
    }, 100);
  }

  /**
   * Ferme toutes les sections d'une catégorie
   */
  function collapseCategory(categoryId) {
    // Fermer les sections principales
    const mainSections = [
      `edit-category-${categoryId}`,
      `add-dish-${categoryId}`,
      `edit-dishes-${categoryId}`,
    ];

    mainSections.forEach((sectionId) => {
      closeAccordion(sectionId);
    });

    // Fermer tous les plats de cette catégorie
    setTimeout(() => {
      document
        .querySelectorAll(
          `.dish-accordion-content[data-category="${categoryId}"]`,
        )
        .forEach((dishSection) => {
          closeDishAccordion(dishSection.id);
        });
    }, 100);
  }

  /**
   * API publique
   */
  window.AccordionManager = {
    init: init,
    openAccordion: openAccordion,
    closeAccordion: closeAccordion,
    openDishAccordion: openDishAccordion,
    closeDishAccordion: closeDishAccordion,
    expandCategory: expandCategory,
    collapseCategory: collapseCategory,
    expandAll: expandAllAccordions,
    collapseAll: collapseAllAccordions,
    closeAllExceptFirst: closeAllExceptFirst,
  };

  // Initialisation
  document.addEventListener("DOMContentLoaded", init);
})();