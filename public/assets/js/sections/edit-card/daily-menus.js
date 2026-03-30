/**
 * JavaScript pour la gestion des menus du jour dans edit-card
 * Gère : ajout/suppression dynamique de lignes, confirmation de suppression
 */
(function () {
  "use strict";

  function init() {
    var section = document.querySelector(".daily-menus-section");
    if (!section) return;

    initAddItemButtons();
    initRemoveItemButtons();
    initDeleteConfirmation();
    initEditToggleButtons();
  }

  /**
   * Boutons "Ajouter une ligne" — ajoute une nouvelle ligne au builder
   */
  function initAddItemButtons() {
    document.addEventListener("click", function (e) {
      var btn = e.target.closest(".add-menu-item-btn");
      if (!btn) return;

      var targetId = btn.dataset.target;
      var builder = document.getElementById(targetId);
      if (!builder) return;

      var list = builder.querySelector(".menu-items-list");
      var row = document.createElement("div");
      row.className = "menu-item-row";
      row.innerHTML =
        '<input type="text" name="item_label[]" placeholder="Catégorie (ex: Entrée)" class="item-label">' +
        '<input type="text" name="item_value[]" placeholder="Nom du plat *" class="item-value" required>' +
        '<button type="button" class="btn-icon remove-item" title="Supprimer cette ligne"><i class="fas fa-times"></i></button>';

      list.appendChild(row);

      // Focus sur le premier champ de la nouvelle ligne
      row.querySelector(".item-label").focus();
    });
  }

  /**
   * Boutons de suppression de ligne — délégation d'événements
   */
  function initRemoveItemButtons() {
    document.addEventListener("click", function (e) {
      var btn = e.target.closest(".remove-item");
      if (!btn) return;

      var row = btn.closest(".menu-item-row");
      var list = row.parentElement;

      // Ne pas supprimer si c'est la dernière ligne
      if (list.querySelectorAll(".menu-item-row").length <= 1) {
        // Vider les champs au lieu de supprimer
        row.querySelectorAll("input").forEach(function (input) {
          input.value = "";
        });
        return;
      }

      row.remove();
    });
  }

  /**
   * Confirmation SweetAlert2 avant suppression d'un menu
   * Utilise un click handler sur le bouton (type="button") pour éviter les conflits de submit
   */
  function initDeleteConfirmation() {
    document.querySelectorAll(".delete-daily-menu-btn").forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        var form = btn.closest("form");
        if (!form) return;
        var title = btn.dataset.menuTitle || "ce menu";

        if (typeof Swal !== "undefined") {
          Swal.fire({
            title: "Supprimer ce menu ?",
            text: 'Le menu "' + title + '" sera supprimé définitivement.',
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ef4444",
            cancelButtonColor: "#6b7280",
            confirmButtonText: "Oui, supprimer",
            cancelButtonText: "Annuler",
          }).then(function (result) {
            if (result.isConfirmed) {
              ajaxSubmit(form, '?page=edit-card');
            }
          });
        } else {
          if (confirm('Supprimer le menu "' + title + '" ?')) {
            ajaxSubmit(form, '?page=edit-card');
          }
        }
      });
    });
  }

  /**
   * Boutons d'édition (accordion-toggle) dans daily-menu-actions et sub-header
   * Ces boutons ne sont pas dans .accordion-header donc accordion.js ne les gère pas
   */
  function initEditToggleButtons() {
    // Gérer les toggles dans daily-menu-actions
    document.querySelectorAll(".daily-menu-actions .accordion-toggle").forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        var targetId = btn.getAttribute("data-target");
        var target = document.getElementById(targetId);
        if (!target) return;

        var isExpanded = target.classList.contains("expanded");
        if (isExpanded) {
          target.classList.remove("expanded");
          target.classList.add("collapsed");
        } else {
          target.classList.remove("collapsed");
          target.classList.add("expanded");
        }
      });
    });

    // Gérer le sub-header (add-daily-menu)
    document.querySelectorAll(".sub-header .accordion-toggle").forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        var targetId = btn.getAttribute("data-target");
        var target = document.getElementById(targetId);
        if (!target) return;

        var isExpanded = target.classList.contains("expanded");
        var icon = btn.querySelector('i');
        if (isExpanded) {
          target.classList.remove("expanded");
          target.classList.add("collapsed");
          if (icon) icon.classList.add('rotated');    // Fermé -> chevron haut
        } else {
          target.classList.remove("collapsed");
          target.classList.add("expanded");
          if (icon) icon.classList.remove('rotated'); // Ouvert -> chevron bas
        }
      });
    });
  }

  // Initialiser au chargement du DOM
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
