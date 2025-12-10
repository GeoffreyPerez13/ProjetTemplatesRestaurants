document.addEventListener("DOMContentLoaded", function () {
  // Gestion des accordéons principaux
  document.querySelectorAll(".accordion-header").forEach((header) => {
    const toggle = header.querySelector(".accordion-toggle");
    if (!toggle) return;

    const targetId = toggle.getAttribute("data-target");
    const target = document.getElementById(targetId);

    if (!target) return;

    // État initial : tous les accordéons sont ouverts
    target.classList.add("expanded");

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

  // Gestion des accordéons individuels de plats
  document.querySelectorAll(".dish-accordion-header").forEach((header) => {
    const toggle = header.querySelector(".dish-accordion-toggle");
    if (!toggle) return;

    const targetId = toggle.getAttribute("data-target");
    const target = document.getElementById(targetId);

    if (!target) return;

    // État initial : tous les plats sont ouverts
    target.style.display = "block";

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

  // Boutons généraux pour tous les accordéons
  const expandAllBtn = document.getElementById("expand-all-accordions");
  const collapseAllBtn = document.getElementById("collapse-all-accordions");

  if (expandAllBtn && collapseAllBtn) {
    // Fonction pour ouvrir tous les accordéons
    expandAllBtn.addEventListener("click", function () {
      console.log("Ouvrir tous les accordéons");

      // Ouvrir tous les accordéons principaux
      document
        .querySelectorAll(".accordion-content.collapsed")
        .forEach((content) => {
          const toggleBtn =
            content.previousElementSibling?.querySelector(".accordion-toggle");
          if (toggleBtn && !content.classList.contains("expanded")) {
            toggleBtn.click();
          }
        });

      // Ouvrir tous les accordéons de plats
      document
        .querySelectorAll(".dish-accordion-content.collapsed")
        .forEach((content) => {
          const toggleBtn = content.previousElementSibling?.querySelector(
            ".dish-accordion-toggle"
          );
          if (toggleBtn && !content.classList.contains("expanded")) {
            toggleBtn.click();
          }
        });
    });

    // Fonction pour fermer tous les accordéons
    collapseAllBtn.addEventListener("click", function () {
      console.log("Fermer tous les accordéons");

      // Fermer tous les accordéons principaux
      document
        .querySelectorAll(".accordion-content.expanded")
        .forEach((content) => {
          const toggleBtn =
            content.previousElementSibling?.querySelector(".accordion-toggle");
          if (toggleBtn && !content.classList.contains("collapsed")) {
            toggleBtn.click();
          }
        });

      // Fermer tous les accordéons de plats
      document
        .querySelectorAll(".dish-accordion-content.expanded")
        .forEach((content) => {
          const toggleBtn = content.previousElementSibling?.querySelector(
            ".dish-accordion-toggle"
          );
          if (toggleBtn && !content.classList.contains("collapsed")) {
            toggleBtn.click();
          }
        });
    });
  }

  // Fonction pour basculer un accordéon principal
  function toggleAccordion(target, toggle) {
    const isExpanded = target.classList.contains("expanded");
    const icon = toggle.querySelector("i");

    if (isExpanded) {
      // Fermer
      target.classList.remove("expanded");
      target.classList.add("collapsed");
      if (icon) icon.classList.add("rotated");
    } else {
      // Ouvrir
      target.classList.remove("collapsed");
      target.classList.add("expanded");
      if (icon) icon.classList.remove("rotated");
    }
  }

  // Fonction pour basculer un accordéon de plat
  function toggleDishAccordion(target, toggle) {
    const icon = toggle.querySelector("i");
    const isVisible = target.style.display !== "none";

    if (isVisible) {
      // Fermer
      target.style.display = "none";
      if (icon) icon.classList.add("rotated");
    } else {
      // Ouvrir
      target.style.display = "block";
      if (icon) icon.classList.remove("rotated");
    }
  }

  // Fonction pour ouvrir toutes les sections d'une catégorie
  function expandCategory(categoryId) {
    // Ouvrir les sections principales
    document
      .querySelectorAll(
        `[id="edit-category-${categoryId}"],
                                   [id="add-dish-${categoryId}"],
                                   [id="edit-dishes-${categoryId}"]`
      )
      .forEach((section) => {
        section.classList.remove("collapsed");
        section.classList.add("expanded");

        // Mettre à jour l'icône du toggle correspondant
        const toggle = document.querySelector(
          `.accordion-toggle[data-target="${section.id}"]`
        );
        if (toggle) {
          const icon = toggle.querySelector("i");
          if (icon) icon.classList.remove("rotated");
        }
      });

    // Ouvrir tous les plats de cette catégorie
    document
      .querySelectorAll(
        `.dish-accordion-content[data-category="${categoryId}"]`
      )
      .forEach((dishSection) => {
        dishSection.style.display = "block";

        // Mettre à jour l'icône du toggle correspondant
        const toggle = document.querySelector(
          `.dish-accordion-toggle[data-target="${dishSection.id}"]`
        );
        if (toggle) {
          const icon = toggle.querySelector("i");
          if (icon) icon.classList.remove("rotated");
        }
      });
  }

  // Fonction pour fermer toutes les sections d'une catégorie
  function collapseCategory(categoryId) {
    // Fermer les sections principales
    document
      .querySelectorAll(
        `[id="edit-category-${categoryId}"],
                                   [id="add-dish-${categoryId}"],
                                   [id="edit-dishes-${categoryId}"]`
      )
      .forEach((section) => {
        section.classList.remove("expanded");
        section.classList.add("collapsed");

        // Mettre à jour l'icône du toggle correspondant
        const toggle = document.querySelector(
          `.accordion-toggle[data-target="${section.id}"]`
        );
        if (toggle) {
          const icon = toggle.querySelector("i");
          if (icon) icon.classList.add("rotated");
        }
      });

    // Fermer tous les plats de cette catégorie
    document
      .querySelectorAll(
        `.dish-accordion-content[data-category="${categoryId}"]`
      )
      .forEach((dishSection) => {
        dishSection.style.display = "none";

        // Mettre à jour l'icône du toggle correspondant
        const toggle = document.querySelector(
          `.dish-accordion-toggle[data-target="${dishSection.id}"]`
        );
        if (toggle) {
          const icon = toggle.querySelector("i");
          if (icon) icon.classList.add("rotated");
        }
      });
  }
});
