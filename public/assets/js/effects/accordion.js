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
        // Ne pas fermer si :
        // - c'est le premier accordéon
        // - ou s'il a la classe "prevent-auto-close"
        // - et qu'il est actuellement ouvert (expanded)
        if (
          accordion !== firstAccordion &&
          !accordion.classList.contains("prevent-auto-close") &&
          accordion.classList.contains("expanded")
        ) {
          closeAccordion(accordion.id);
        }
      });

      // S'assurer que le premier accordéon est ouvert
      if (firstAccordion && firstAccordion.classList.contains("collapsed")) {
        openAccordion(firstAccordion.id);
      }
      
      // Vérifier l'état initial de l'accordéon premium
      const premiumAccordion = document.getElementById("premium-options-content");
      if (premiumAccordion && premiumAccordion.classList.contains("collapsed")) {
        hideRetractationNotice();
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

      // Synchroniser l'état initial du chevron
      const icon = toggle.querySelector('i');
      if (icon && target) {
        if (target.classList.contains('collapsed')) {
          icon.classList.add('rotated');     // Fermé -> chevron haut
        } else {
          icon.classList.remove('rotated');  // Ouvert -> chevron bas
        }
      }

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

      // Synchroniser l'état initial du chevron
      const isVisible = target.style.display !== "none" && !target.classList.contains("collapsed");
      const icon = toggle.querySelector('i');
      if (icon) {
        if (isVisible) {
          icon.classList.remove('rotated');  // Ouvert -> chevron bas
        } else {
          icon.classList.add('rotated');     // Fermé -> chevron haut
        }
      }

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

    // Contrôles plats par catégorie - Tout ouvrir
    document.querySelectorAll(".expand-dishes").forEach((button) => {
      button.addEventListener("click", function () {
        const categoryId = this.getAttribute("data-category-id");
        expandDishesInCategory(categoryId);
      });
    });

    // Contrôles plats par catégorie - Tout fermer
    document.querySelectorAll(".collapse-dishes").forEach((button) => {
      button.addEventListener("click", function () {
        const categoryId = this.getAttribute("data-category-id");
        collapseDishesInCategory(categoryId);
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
      // Gérer le retractation-notice pour l'accordéon premium
      if (target.id === 'premium-options-content') {
        hideRetractationNotice();
      }
    } else {
      openAccordion(target.id);
      // Gérer le retractation-notice pour l'accordéon premium
      if (target.id === 'premium-options-content') {
        showRetractationNotice();
      }
    }
  }

  /**
   * Bascule un accordéon de plat
   */
  function toggleDishAccordion(target, toggle) {
    // Utiliser la même logique que la synchronisation initiale
    const isExpanded = target.style.display !== "none" && !target.classList.contains("collapsed");

    if (isExpanded) {
      closeDishAccordion(target.id);
      if (toggle) {
        const icon = toggle.querySelector('i');
        if (icon) icon.classList.add('rotated');    // Fermé -> chevron haut
      }
    } else {
      openDishAccordion(target.id);
      if (toggle) {
        const icon = toggle.querySelector('i');
        if (icon) icon.classList.remove('rotated'); // Ouvert -> chevron bas
      }
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

    // Mettre à jour le chevron (ouvert = chevron bas, pas de rotation)
    const toggle = document.querySelector('.accordion-toggle[data-target="' + accordionId + '"]');
    if (toggle) {
      const icon = toggle.querySelector('i');
      if (icon) icon.classList.remove('rotated');
    }
    
    // Gérer le retractation-notice pour l'accordéon premium
    if (accordionId === 'premium-options-content') {
      showRetractationNotice();
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

    // Mettre à jour le chevron (fermé = chevron haut, rotation 180°)
    const toggle = document.querySelector('.accordion-toggle[data-target="' + accordionId + '"]');
    if (toggle) {
      const icon = toggle.querySelector('i');
      if (icon) icon.classList.add('rotated');
    }
    
    // Gérer le retractation-notice pour l'accordéon premium
    if (accordionId === 'premium-options-content') {
      hideRetractationNotice();
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
   * Ouvre tous les plats d'une catégorie
   */
  function expandDishesInCategory(categoryId) {
    document
      .querySelectorAll(
        `.dish-accordion-content[data-category="${categoryId}"]`,
      )
      .forEach((dishSection) => {
        openDishAccordion(dishSection.id);
        // Mettre à jour l'icône du toggle correspondant (logique inversée)
        const toggle = document.querySelector(
          `.dish-accordion-toggle[data-target="${dishSection.id}"]`,
        );
        if (toggle) {
          const icon = toggle.querySelector('i');
          if (icon) icon.classList.remove('rotated');  // Ouvert -> chevron bas
        }
      });
  }

  /**
   * Ferme tous les plats d'une catégorie
   */
  function collapseDishesInCategory(categoryId) {
    document
      .querySelectorAll(
        `.dish-accordion-content[data-category="${categoryId}"]`,
      )
      .forEach((dishSection) => {
        closeDishAccordion(dishSection.id);
        // Mettre à jour l'icône du toggle correspondant (logique inversée)
        const toggle = document.querySelector(
          `.dish-accordion-toggle[data-target="${dishSection.id}"]`,
        );
        if (toggle) {
          const icon = toggle.querySelector('i');
          if (icon) icon.classList.add('rotated');     // Fermé -> chevron haut
        }
      });
  }

  /**
   * Affiche le retractation-notice
   */
  function showRetractationNotice() {
    const notice = document.querySelector('.retractation-notice');
    if (notice) {
      notice.style.display = 'block';
    }
  }

  /**
   * Masque le retractation-notice
   */
  function hideRetractationNotice() {
    const notice = document.querySelector('.retractation-notice');
    if (notice) {
      notice.style.display = 'none';
    }
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
    showRetractationNotice: showRetractationNotice,
    hideRetractationNotice: hideRetractationNotice,
  };

  // Initialisation
  document.addEventListener("DOMContentLoaded", init);
})();
