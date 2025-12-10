// drag-and-drop.js - Gestion de la réorganisation des images par drag & drop
(function () {
  "use strict";

  console.log("drag-and-drop.js initialisé (drag sur toute la carte)");

  // Variables globales du module
  let imagesGrid = null;
  let startReorderBtn = null;
  let saveOrderBtn = null;
  let cancelOrderBtn = null;
  let reorderButtons = null;
  let reorderInstructions = null;
  let newOrderInput = null;

  let originalOrder = [];
  let isReorderMode = false;
  let sortableInstance = null;

  /**
   * Initialise le module de réorganisation
   */
  function init() {
    // Récupérer les éléments DOM
    imagesGrid = document.getElementById("sortable-images");
    startReorderBtn = document.getElementById("start-reorder-btn");
    saveOrderBtn = document.getElementById("save-order-btn");
    cancelOrderBtn = document.getElementById("cancel-order-btn");
    reorderButtons = document.getElementById("reorder-buttons");
    reorderInstructions = document.getElementById("reorder-instructions");
    newOrderInput = document.getElementById("new-order-input");

    // Vérifier que les éléments nécessaires existent
    if (!imagesGrid || !startReorderBtn) {
      console.warn("Éléments de réorganisation non trouvés");
      return;
    }

    // Sauvegarder l'ordre original
    originalOrder = getCurrentOrder();

    // Configurer les événements
    setupEventListeners();

    console.log("Module de réorganisation prêt (drag sur carte complète)");
  }

  /**
   * Configure tous les écouteurs d'événements
   */
  function setupEventListeners() {
    // Bouton pour démarrer la réorganisation
    startReorderBtn.addEventListener("click", enableReorderMode);

    // Bouton pour sauvegarder
    if (saveOrderBtn) {
      saveOrderBtn.addEventListener("click", saveNewOrder);
    }

    // Bouton pour annuler
    if (cancelOrderBtn) {
      cancelOrderBtn.addEventListener("click", cancelReorder);
    }
  }

  /**
   * Récupère l'ordre actuel des images
   */
  function getCurrentOrder() {
    const imageCards = imagesGrid.querySelectorAll(".image-card");
    return Array.from(imageCards).map((card) =>
      card.getAttribute("data-image-id")
    );
  }

  /**
   * Active le mode de réorganisation
   */
  function enableReorderMode() {
    if (isReorderMode) return;

    isReorderMode = true;

    // Afficher l'interface de réorganisation
    showReorderInterface();

    // Activer le drag & drop sur toute la carte
    enableFullCardDragAndDrop();

    // Mettre à jour les numéros de position
    updatePositionNumbers();

    // Afficher un feedback
    showFeedback(
      "Mode réorganisation activé. Glissez-déposez les cartes pour les réorganiser.",
      "info"
    );
  }

  /**
   * Affiche l'interface de réorganisation
   */
  function showReorderInterface() {
    // Cacher le bouton de départ
    startReorderBtn.style.display = "none";

    // Afficher les boutons de contrôle
    if (reorderButtons) {
      reorderButtons.style.display = "flex";
    }

    // Afficher les instructions
    if (reorderInstructions) {
      reorderInstructions.style.display = "block";
      // Mettre à jour les instructions pour refléter le drag sur toute la carte
      reorderInstructions.innerHTML = `
                <p>Glissez-déposez les cartes d'images pour les réorganiser. Cliquez sur "Enregistrer" pour valider.</p>
            `;
    }

    // Ajouter la classe de style
    imagesGrid.classList.add("reorder-mode");
  }

  /**
   * Active le drag & drop sur toute la carte
   */
  function enableFullCardDragAndDrop() {
    if (typeof Sortable === "undefined") {
      console.error("Sortable.js n'est pas chargé");
      showFeedback("Erreur: Bibliothèque de drag & drop non chargée", "error");
      return;
    }

    // Ajouter les drag-handles visuels (mais ils ne seront pas utilisés comme handles fonctionnels)
    addVisualDragHandles();

    // Initialiser Sortable.js avec la carte complète comme handle
    sortableInstance = Sortable.create(imagesGrid, {
      animation: 200,
      ghostClass: "sortable-ghost",
      chosenClass: "sortable-chosen",
      dragClass: "sortable-drag",
      handle: ".image-card", // La carte complète est le handle !
      filter: ".image-actions *, .reorder-controls *, .btn, button, a", // Exclure les boutons du drag
      preventOnFilter: true,
      draggable: ".image-card",

      onStart: function (evt) {
        imagesGrid.classList.add("dragging");
        const item = evt.item;
        item.style.cursor = "grabbing";

        showFeedback(`Déplacement de "${getImageName(item)}"`, "info");
      },

      onEnd: function (evt) {
        imagesGrid.classList.remove("dragging");
        const item = evt.item;
        item.style.cursor = "";

        updatePositionNumbers();
        updateOrderInput();

        showFeedback(
          `"${getImageName(item)}" déplacé à la position ${
            getItemPosition(item) + 1
          }`,
          "success"
        );
      },

      onSort: function (evt) {
        // Déclencher un événement personnalisé
        const event = new CustomEvent("imagesReordered", {
          detail: {
            order: getCurrentOrder(),
            oldIndex: evt.oldIndex,
            newIndex: evt.newIndex,
          },
        });
        document.dispatchEvent(event);
      },

      // Amélioration pour mobile
      delay: 100, // Délai pour éviter les conflits avec les clics
      delayOnTouchOnly: true,

      // Support du touch
      touchStartThreshold: 5,
    });

    console.log("Drag & drop activé sur les cartes complètes");
  }

  /**
   * Ajoute les drag-handles visuels (pour l'indication seulement)
   */
  function addVisualDragHandles() {
    const imageCards = imagesGrid.querySelectorAll(".image-card");

    imageCards.forEach((card) => {
      // Vérifier si un handle existe déjà
      if (card.querySelector(".drag-handle")) return;

      const dragHandle = document.createElement("div");
      dragHandle.className = "drag-handle";
      dragHandle.innerHTML = '<i class="fas fa-grip-vertical"></i>';
      dragHandle.title = "Glisser pour réorganiser";

      card.appendChild(dragHandle);
    });
  }

  /**
   * Récupère le nom d'une image depuis sa carte
   */
  function getImageName(card) {
    const nameElement = card.querySelector(".image-name");
    return nameElement ? nameElement.textContent.trim() : "Image";
  }

  /**
   * Récupère la position d'un élément dans la grille
   */
  function getItemPosition(item) {
    const items = imagesGrid.querySelectorAll(".image-card");
    return Array.from(items).indexOf(item);
  }

  /**
   * Active les boutons de déplacement (alternative)
   */
  function enableMoveButtons() {
    const reorderControls = imagesGrid.querySelectorAll(".reorder-controls");

    reorderControls.forEach((controls) => {
      controls.style.display = "flex";

      const upBtn = controls.querySelector(".move-up");
      const downBtn = controls.querySelector(".move-down");

      if (upBtn) {
        upBtn.addEventListener("click", moveImageUp);
      }
      if (downBtn) {
        downBtn.addEventListener("click", moveImageDown);
      }
    });

    // Mettre à jour l'état des boutons
    updateMoveButtons();

    showFeedback(
      'Utilisez les boutons "Monter" et "Descendre" pour réorganiser',
      "info"
    );
  }

  /**
   * Monte une image d'une position
   */
  function moveImageUp(event) {
    const card = event.target.closest(".image-card");
    const prevCard = card.previousElementSibling;

    if (prevCard) {
      imagesGrid.insertBefore(card, prevCard);
      updatePositionNumbers();
      updateMoveButtons();
      updateOrderInput();
      showFeedback("Image remontée", "success");

      // Déclencher un événement personnalisé
      const customEvent = new CustomEvent("imageMoved", {
        detail: {
          direction: "up",
          imageId: card.getAttribute("data-image-id"),
        },
      });
      document.dispatchEvent(customEvent);
    }
  }

  /**
   * Descend une image d'une position
   */
  function moveImageDown(event) {
    const card = event.target.closest(".image-card");
    const nextCard = card.nextElementSibling;

    if (nextCard) {
      imagesGrid.insertBefore(nextCard, card);
      updatePositionNumbers();
      updateMoveButtons();
      updateOrderInput();
      showFeedback("Image descendue", "success");

      // Déclencher un événement personnalisé
      const customEvent = new CustomEvent("imageMoved", {
        detail: {
          direction: "down",
          imageId: card.getAttribute("data-image-id"),
        },
      });
      document.dispatchEvent(customEvent);
    }
  }

  /**
   * Met à jour l'état des boutons de déplacement
   */
  function updateMoveButtons() {
    const imageCards = imagesGrid.querySelectorAll(".image-card");

    imageCards.forEach((card, index) => {
      const upBtn = card.querySelector(".move-up");
      const downBtn = card.querySelector(".move-down");

      if (upBtn) {
        upBtn.disabled = index === 0;
        upBtn.title =
          index === 0 ? "Déjà en première position" : "Monter d'une position";
      }
      if (downBtn) {
        downBtn.disabled = index === imageCards.length - 1;
        downBtn.title =
          index === imageCards.length - 1
            ? "Déjà en dernière position"
            : "Descendre d'une position";
      }
    });
  }

  /**
   * Met à jour les numéros de position
   */
  function updatePositionNumbers() {
    const imageCards = imagesGrid.querySelectorAll(".image-card");

    imageCards.forEach((card, index) => {
      const positionSpan = card.querySelector(".position-number");
      if (positionSpan) {
        positionSpan.textContent = index + 1;
        positionSpan.title = `Position ${index + 1} sur ${imageCards.length}`;
      }
    });
  }

  /**
   * Met à jour l'input hidden avec le nouvel ordre
   */
  function updateOrderInput() {
    const newOrder = getCurrentOrder();
    if (newOrderInput) {
      newOrderInput.value = JSON.stringify(newOrder);
    }
  }

  /**
   * Désactive le mode de réorganisation
   */
  function disableReorderMode() {
    if (!isReorderMode) return;

    isReorderMode = false;

    // Restaurer l'interface normale
    hideReorderInterface();

    // Désactiver le drag & drop
    disableDragAndDrop();

    // Restaurer l'ordre original
    restoreOriginalOrder();

    showFeedback("Réorganisation annulée", "warning");
  }

  /**
   * Cache l'interface de réorganisation
   */
  function hideReorderInterface() {
    // Afficher le bouton de départ
    startReorderBtn.style.display = "inline-block";

    // Cacher les boutons de contrôle
    if (reorderButtons) {
      reorderButtons.style.display = "none";
    }

    // Cacher les instructions
    if (reorderInstructions) {
      reorderInstructions.style.display = "none";
    }

    // Retirer la classe de style
    imagesGrid.classList.remove("reorder-mode");
    imagesGrid.classList.remove("dragging");
  }

  /**
   * Désactive le drag & drop
   */
  function disableDragAndDrop() {
    if (sortableInstance) {
      sortableInstance.destroy();
      sortableInstance = null;
      console.log("Drag & drop désactivé");
    }

    // Retirer les handles visuels
    const dragHandles = imagesGrid.querySelectorAll(".drag-handle");
    dragHandles.forEach((handle) => handle.remove());
  }

  /**
   * Désactive les boutons de déplacement
   */
  function disableMoveButtons() {
    const reorderControls = imagesGrid.querySelectorAll(".reorder-controls");

    reorderControls.forEach((controls) => {
      controls.style.display = "none";

      const upBtn = controls.querySelector(".move-up");
      const downBtn = controls.querySelector(".move-down");

      if (upBtn) {
        upBtn.removeEventListener("click", moveImageUp);
      }
      if (downBtn) {
        downBtn.removeEventListener("click", moveImageDown);
      }
    });
  }

  /**
   * Restaure l'ordre original
   */
  function restoreOriginalOrder() {
    const fragment = document.createDocumentFragment();

    originalOrder.forEach((id) => {
      const card = imagesGrid.querySelector(`[data-image-id="${id}"]`);
      if (card) {
        fragment.appendChild(card);
      }
    });

    imagesGrid.innerHTML = "";
    imagesGrid.appendChild(fragment);
    updatePositionNumbers();
  }

  /**
   * Annule la réorganisation
   */
  function cancelReorder() {
    Swal.fire({
      title: "Annuler la réorganisation ?",
      html: 'Les modifications non enregistrées seront perdues.<br><br><small class="text-muted">L\'ordre original sera restauré.</small>',
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Oui, annuler",
      cancelButtonText: "Continuer",
      backdrop: true,
      allowOutsideClick: false,
    }).then((result) => {
      if (result.isConfirmed) {
        disableReorderMode();
      }
    });
  }

  /**
   * Sauvegarde le nouvel ordre
   */
  function saveNewOrder() {
    const newOrder = getCurrentOrder();

    // Vérifier si l'ordre a changé
    const hasChanged =
      JSON.stringify(newOrder) !== JSON.stringify(originalOrder);

    if (!hasChanged) {
      Swal.fire({
        title: "Aucun changement",
        text: "L'ordre des images n'a pas été modifié.",
        icon: "info",
        confirmButtonText: "OK",
      });
      return;
    }

    Swal.fire({
      title: "Confirmer la réorganisation",
      html: `Voulez-vous enregistrer le nouvel ordre des images ?<br><br>
                  <small class="text-muted">Les images seront affichées dans cet ordre sur la vitrine.</small>`,
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#28a745",
      cancelButtonColor: "#6c757d",
      confirmButtonText: '<i class="fas fa-save"></i> Enregistrer',
      cancelButtonText: "Annuler",
      backdrop: true,
      allowOutsideClick: false,
    }).then((result) => {
      if (result.isConfirmed) {
        // Afficher un loader
        Swal.fire({
          title: "Enregistrement en cours...",
          text: "Veuillez patienter",
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          },
        });

        // Mettre à jour l'input et soumettre le formulaire
        updateOrderInput();

        // Soumettre après un court délai pour l'UX
        setTimeout(() => {
          const form = document.getElementById("reorder-form");
          if (form) {
            form.submit();
          }
        }, 500);
      }
    });
  }

  /**
   * Affiche un feedback à l'utilisateur
   */
  function showFeedback(message, type = "info") {
    // Créer ou récupérer l'élément de feedback
    let feedbackEl = document.getElementById("reorder-feedback");

    if (!feedbackEl) {
      feedbackEl = document.createElement("div");
      feedbackEl.id = "reorder-feedback";
      feedbackEl.className = "reorder-feedback";
      document.body.appendChild(feedbackEl);
    }

    // Mettre à jour le contenu et le style
    feedbackEl.textContent = message;
    feedbackEl.className = `reorder-feedback ${type}`;
    feedbackEl.style.display = "block";

    // Masquer après 3 secondes
    setTimeout(() => {
      feedbackEl.style.display = "none";
    }, 3000);
  }

  /**
   * Réinitialise le module (utile après AJAX)
   */
  function refresh() {
    // Sauvegarder le nouvel ordre original
    originalOrder = getCurrentOrder();

    // Réinitialiser l'état
    isReorderMode = false;

    // Réappliquer les styles si nécessaire
    if (imagesGrid.classList.contains("reorder-mode")) {
      imagesGrid.classList.remove("reorder-mode");
    }

    console.log("Module de réorganisation rafraîchi");
  }

  /**
   * API publique
   */
  window.ImageReorder = {
    init: init,
    enable: enableReorderMode,
    disable: disableReorderMode,
    save: saveNewOrder,
    cancel: cancelReorder,
    refresh: refresh,
    getCurrentOrder: getCurrentOrder,
    getOriginalOrder: () => originalOrder,
    isReorderMode: () => isReorderMode,
  };

  // Initialiser au chargement du DOM
  document.addEventListener("DOMContentLoaded", function () {
    // Petit délai pour s'assurer que tout est chargé
    setTimeout(init, 100);
  });
})();
