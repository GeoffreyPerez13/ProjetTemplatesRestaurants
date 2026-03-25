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
   */
  function initDeleteConfirmation() {
    document.querySelectorAll(".delete-daily-menu-form").forEach(function (form) {
      form.addEventListener("submit", function (e) {
        e.preventDefault();
        var btn = form.querySelector("[name='delete_daily_menu']");
        var title = btn ? btn.dataset.menuTitle : "ce menu";

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

  // Initialiser au chargement du DOM
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
