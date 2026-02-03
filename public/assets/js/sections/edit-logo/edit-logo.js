/**
 * Gestion de la page d'édition du logo
 * Version avec prévisualisation similaire à edit-card mode images
 */
(function () {
  "use strict";

  // Configuration
  const CONFIG = {
    maxFileSize: 5 * 1024 * 1024, // 5MB
    allowedTypes: [
      "image/jpeg",
      "image/jpg",
      "image/png",
      "image/gif",
      "image/webp",
      "image/svg+xml",
    ],
    allowedExtensions: ["jpg", "jpeg", "png", "gif", "webp", "svg"],
    previewMaxWidth: 150, // Taille réduite de l'aperçu
    previewMaxHeight: 150,
  };

  // Variables globales
  let selectedFile = null;

  /**
   * Affiche un message de chargement
   */
  function showLoading(message = "Traitement en cours...") {
    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: message,
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
          Swal.showLoading();
        },
      });
    }
  }

  /**
   * Ferme le message de chargement
   */
  function closeLoading() {
    if (typeof Swal !== "undefined") {
      Swal.close();
    }
  }

  /**
   * Initialisation principale
   */
  function init() {
    if (!document.querySelector(".edit-logo-container")) {
      return;
    }

    // ==================== RÉCUPÉRATION DES PARAMÈTRES ====================
    const scrollParams = window.scrollParams || {};

    // Gestion du scroll vers une ancre
    if (scrollParams.anchor) {
      handleAnchorScroll(scrollParams.anchor, scrollParams.scrollDelay || 3500);
    }

    // ==================== GESTION DES MESSAGES ====================
    setupMessageHandlers(scrollParams.scrollDelay || 3500);

    // ==================== INITIALISATION DES ÉLÉMENTS ====================
    initElements();

    // ==================== GESTION DU DRAG & DROP ====================
    setupDragAndDrop();

    // ==================== GESTION DE LA LIGHTBOX ====================
    setupLightbox();

    // ==================== GESTION DE LA SUPPRESSION ====================
    setupDeleteConfirmations();

    // ==================== PRÉVISUALISATION COMME EDIT-CARD ====================
    setupPreviewLikeEditCard();
  }

  /**
   * Configure la prévisualisation comme edit-card mode images
   */
  function setupPreviewLikeEditCard() {
    const uploadArea = document.getElementById("uploadArea");
    if (!uploadArea) return;

    // Créer le conteneur pour la grille de prévisualisation
    const previewGrid = document.createElement("div");
    previewGrid.id = "preview-grid";
    previewGrid.className = "preview-grid";
    previewGrid.style.display = "none";

    // Créer le conteneur pour les informations du fichier
    const fileInfoContainer = document.createElement("div");
    fileInfoContainer.id = "file-info-container";
    fileInfoContainer.className = "file-info-container";
    fileInfoContainer.style.display = "none";

    // Ajouter après la zone d'upload
    uploadArea.parentNode.insertBefore(
      fileInfoContainer,
      uploadArea.nextSibling,
    );
    uploadArea.parentNode.insertBefore(previewGrid, fileInfoContainer);
  }

  /**
   * Initialise les éléments du DOM
   */
  function initElements() {
    // Éléments principaux
    const fileInput = document.getElementById("logo-input");
    const selectFileBtn = document.getElementById("selectFileBtn");
    const uploadBtn = document.getElementById("uploadBtn");
    const resetBtn = document.getElementById("resetBtn");

    if (fileInput && selectFileBtn) {
      // Ouvrir le sélecteur de fichiers
      selectFileBtn.addEventListener("click", () => {
        fileInput.click();
      });

      // Gérer le changement de fichier
      fileInput.addEventListener("change", handleFileSelection);
    }

    // Bouton de réinitialisation
    if (resetBtn) {
      resetBtn.addEventListener("click", resetFileSelection);
    }

    // Bouton d'upload - validation
    if (uploadBtn) {
      uploadBtn.addEventListener("click", validateBeforeUpload);
    }
  }

  /**
   * Gère la sélection d'un fichier
   */
  function handleFileSelection(e) {
    const file = e.target.files[0];
    if (!file) return;

    // Validation du fichier
    const validation = validateFile(file);

    if (!validation.valid) {
      showErrorAlert(validation.message);
      resetFileSelection();
      return;
    }

    // Fichier valide
    selectedFile = file;
    updateFileInfo(file);
    showPreviewGrid(file);
    enableUploadButton();

    // Ajouter la classe à la zone d'upload
    const uploadArea = document.getElementById("uploadArea");
    if (uploadArea) {
      uploadArea.classList.add("file-selected");
    }
  }

  /**
   * Affiche une alerte d'erreur avec SweetAlert ou alert() natif
   */
  function showErrorAlert(message, title = "Erreur") {
    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: title,
        text: message,
        icon: "error",
        confirmButtonColor: "#d33",
        confirmButtonText: "OK",
        backdrop: true,
      });
    } else {
      alert(message);
    }
  }

  /**
   * Valide un fichier
   */
  function validateFile(file) {
    // Vérification du type MIME
    if (!CONFIG.allowedTypes.includes(file.type)) {
      return {
        valid: false,
        message:
          "Type de fichier non autorisé. Formats acceptés: JPG, PNG, GIF, WebP, SVG.",
      };
    }

    // Vérification de la taille
    if (file.size > CONFIG.maxFileSize) {
      return {
        valid: false,
        message: "Le fichier est trop volumineux. Taille maximale: 5 Mo.",
      };
    }

    // Vérification de l'extension
    const extension = file.name.split(".").pop().toLowerCase();
    if (!CONFIG.allowedExtensions.includes(extension)) {
      return {
        valid: false,
        message: "Extension de fichier non autorisée.",
      };
    }

    return { valid: true, message: "Fichier valide" };
  }

  /**
   * Met à jour les informations du fichier (SIMILAIRE À EDIT-CARD)
   */
  function updateFileInfo(file) {
    const fileInfoContainer = document.getElementById("file-info-container");
    if (!fileInfoContainer) return;

    // Créer le HTML comme dans edit-card
    fileInfoContainer.innerHTML = `
            <div class="file-info-card">
                <div class="file-image-info">
                    <p class="file-info-name"><strong>Fichier :</strong> ${escapeHtml(file.name)}</p>
                    <p class="file-info-size"><strong>Taille :</strong> ${formatFileSize(file.size)}</p>
                    <p class="file-info-type"><strong>Type :</strong> ${file.type}</p>
                    <p class="file-info-dimensions"><strong>Dimensions :</strong> <span id="image-dimensions">Calcul en cours...</span></p>
                </div>
                <div class="file-info-actions">
                    <button type="button" class="btn small danger" id="remove-file-btn">
                        <i class="fas fa-times"></i> Retirer
                    </button>
                </div>
            </div>
        `;

    // Afficher le conteneur
    fileInfoContainer.style.display = "block";

    // Ajouter l'événement pour retirer le fichier
    const removeBtn = document.getElementById("remove-file-btn");
    if (removeBtn) {
      removeBtn.addEventListener("click", resetFileSelection);
    }
  }

  /**
   * Échapper le HTML pour la sécurité
   */
  function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  /**
   * Affiche la prévisualisation en grille (SIMILAIRE À EDIT-CARD)
   */
  function showPreviewGrid(file) {
    const previewGrid = document.getElementById("preview-grid");
    if (!previewGrid) return;

    // Vider la grille
    previewGrid.innerHTML = "";

    // Créer la carte d'image comme dans edit-card
    const imageCard = document.createElement("div");
    imageCard.className = "preview-image-card";
    imageCard.innerHTML = `
            <div class="preview-image-container">
                <img src="" alt="Aperçu" class="preview-image-thumbnail" data-file-name="${escapeHtml(file.name)}">
                <div class="preview-overlay">
                    <button type="button" class="btn-icon preview-enlarge" title="Agrandir">
                        <i class="fas fa-search-plus"></i>
                    </button>
                </div>
            </div>
            <div class="preview-badge">APERÇU</div>
        `;

    previewGrid.appendChild(imageCard);
    previewGrid.style.display = "grid";

    // Charger l'image pour l'aperçu
    const reader = new FileReader();
    const imgElement = imageCard.querySelector(".preview-image-thumbnail");

    reader.onload = function (e) {
      imgElement.src = e.target.result;

      // Une fois l'image chargée, afficher les dimensions
      imgElement.onload = function () {
        const dimensionsSpan = document.getElementById("image-dimensions");
        if (dimensionsSpan) {
          dimensionsSpan.textContent = `${this.naturalWidth}×${this.naturalHeight}px`;
        }

        // Ajouter l'événement pour la lightbox
        const enlargeBtn = imageCard.querySelector(".preview-enlarge");
        if (enlargeBtn) {
          enlargeBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            openLightbox(
              imgElement.src,
              `Aperçu: ${file.name} (${this.naturalWidth}×${this.naturalHeight}px)`,
            );
          });
        }

        // L'image elle-même peut aussi ouvrir la lightbox
        imgElement.addEventListener("click", () => {
          openLightbox(
            imgElement.src,
            `Aperçu: ${file.name} (${this.naturalWidth}×${this.naturalHeight}px)`,
          );
        });
      };
    };

    reader.readAsDataURL(file);
  }

  /**
   * Formate la taille d'un fichier
   */
  function formatFileSize(bytes) {
    if (bytes === 0) return "0 Bytes";

    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
  }

  /**
   * Active le bouton d'upload
   */
  function enableUploadButton() {
    const uploadBtn = document.getElementById("uploadBtn");
    if (uploadBtn) {
      uploadBtn.disabled = false;
      uploadBtn.classList.add("active");
    }
  }

  /**
   * Réinitialise la sélection de fichier
   */
  function resetFileSelection() {
    // Confirmation si un fichier est sélectionné
    if (selectedFile) {
      const confirmReset = () => {
        const fileInput = document.getElementById("logo-input");
        const previewGrid = document.getElementById("preview-grid");
        const fileInfoContainer = document.getElementById(
          "file-info-container",
        );
        const uploadBtn = document.getElementById("uploadBtn");
        const uploadArea = document.getElementById("uploadArea");

        if (fileInput) fileInput.value = "";
        if (previewGrid) {
          previewGrid.innerHTML = "";
          previewGrid.style.display = "none";
        }
        if (fileInfoContainer) {
          fileInfoContainer.innerHTML = "";
          fileInfoContainer.style.display = "none";
        }
        if (uploadBtn) {
          uploadBtn.disabled = true;
          uploadBtn.classList.remove("active");
        }
        if (uploadArea) {
          uploadArea.classList.remove("file-selected");
        }

        selectedFile = null;
      };

      // Utiliser SweetAlert pour la confirmation si disponible
      if (typeof Swal !== "undefined") {
        Swal.fire({
          title: "Confirmer l'annulation",
          text: "Voulez-vous vraiment retirer le fichier sélectionné ?",
          icon: "question",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Oui, retirer",
          cancelButtonText: "Annuler",
          backdrop: true,
          allowOutsideClick: false,
        }).then((result) => {
          if (result.isConfirmed) {
            confirmReset();
          }
        });
      } else {
        if (confirm("Voulez-vous vraiment retirer le fichier sélectionné ?")) {
          confirmReset();
        }
      }
    }
  }

  /**
   * Valide avant l'upload
   */
  function validateBeforeUpload(e) {
    if (!selectedFile) {
      e.preventDefault();
      showErrorAlert(
        "Veuillez sélectionner un fichier avant de continuer.",
        "Fichier manquant",
      );
      return false;
    }

    // Validation finale
    const validation = validateFile(selectedFile);
    if (!validation.valid) {
      e.preventDefault();
      showErrorAlert(validation.message);
      resetFileSelection();
      return false;
    }

    // Confirmation si un logo existe déjà avec SweetAlert
    const hasLogo = window.scrollParams?.hasLogo || false;
    if (hasLogo) {
      e.preventDefault();

      const confirmUpload = () => {
        const form = document.getElementById("upload-logo-form");
        if (form) {
          showLoading("Upload en cours...");
          setTimeout(() => form.submit(), 100);
        }
      };

      if (typeof Swal !== "undefined") {
        Swal.fire({
          title: "Remplacer le logo",
          text: "Êtes-vous sûr de vouloir remplacer le logo actuel ? Cette action ne peut pas être annulée.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Oui, remplacer",
          cancelButtonText: "Annuler",
          backdrop: true,
          allowOutsideClick: false,
        }).then((result) => {
          if (result.isConfirmed) {
            confirmUpload();
          }
        });
      } else {
        if (confirm("Êtes-vous sûr de vouloir remplacer le logo actuel ?")) {
          confirmUpload();
        }
      }

      return false;
    }

    return true;
  }

  /**
   * Configure le drag & drop
   */
  function setupDragAndDrop() {
    const uploadArea = document.getElementById("uploadArea");
    const fileInput = document.getElementById("logo-input");

    if (!uploadArea || !fileInput) return;

    // Empêcher les comportements par défaut
    ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
      uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    // Gestion des événements de drag
    uploadArea.addEventListener("dragenter", () => {
      uploadArea.classList.add("drag-over");
    });

    uploadArea.addEventListener("dragover", () => {
      uploadArea.classList.add("drag-over");
    });

    uploadArea.addEventListener("dragleave", () => {
      uploadArea.classList.remove("drag-over");
    });

    uploadArea.addEventListener("drop", (e) => {
      uploadArea.classList.remove("drag-over");

      const dt = e.dataTransfer;
      const file = dt.files[0];

      if (file) {
        // Simuler la sélection du fichier
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;

        // Déclencher l'événement change
        const event = new Event("change", { bubbles: true });
        fileInput.dispatchEvent(event);
      }
    });
  }

  /**
   * Empêche les comportements par défaut
   */
  function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  /**
   * Configure la lightbox
   */
  function setupLightbox() {
    const lightbox = document.getElementById("logo-lightbox");
    const lightboxImage = document.getElementById("lightbox-image");
    const lightboxClose = document.getElementById("lightboxClose");
    const currentLogoImage = document.getElementById("current-logo-image");
    const enlargeButtons = document.querySelectorAll(".enlarge-logo");

    // Ouvrir la lightbox
    function openLightbox(imageSrc, caption = "") {
      if (lightboxImage) {
        lightboxImage.src = imageSrc;
        lightbox.style.display = "flex";

        // Mettre à jour la légende
        const captionElement = document.getElementById("lightbox-caption");
        if (captionElement) {
          captionElement.textContent = caption;
        }

        // Animation
        setTimeout(() => {
          lightbox.style.opacity = "1";
        }, 10);
      }
    }

    // Fermer la lightbox
    function closeLightbox() {
      lightbox.style.opacity = "0";
      setTimeout(() => {
        lightbox.style.display = "none";
      }, 300);
    }

    // Événements pour le logo actuel
    if (currentLogoImage) {
      currentLogoImage.addEventListener("click", () => {
        openLightbox(currentLogoImage.src, "Logo actuel");
      });
    }

    // Événements pour les boutons d'agrandissement
    enlargeButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        e.stopPropagation();
        const target = button
          .closest(".logo-image-container")
          .querySelector("img");
        if (target) {
          openLightbox(target.src, "Logo agrandi");
        }
      });
    });

    // Fermer la lightbox
    if (lightboxClose) {
      lightboxClose.addEventListener("click", closeLightbox);
    }

    // Fermer en cliquant à l'extérieur
    if (lightbox) {
      lightbox.addEventListener("click", (e) => {
        if (e.target === lightbox) {
          closeLightbox();
        }
      });
    }

    // Fermer avec la touche Échap
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && lightbox.style.display === "flex") {
        closeLightbox();
      }
    });

    // Exposer la fonction pour qu'elle soit accessible depuis d'autres parties du code
    window.openLightbox = openLightbox;
  }

  /**
   * Configure les confirmations de suppression du logo
   */
  function setupDeleteConfirmations() {
    const deleteForms = document.querySelectorAll(".delete-logo-form");

    deleteForms.forEach((form) => {
      // Intercepter le clic sur le bouton de suppression
      const deleteButton = form.querySelector('button[name="delete_logo"]');

      if (deleteButton) {
        deleteButton.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();

          // Obtenir le nom du fichier depuis l'attribut data-filename
          const fileName = this.getAttribute("data-filename") || "ce logo";

          // Utiliser SweetAlert pour la confirmation si disponible
          if (typeof Swal !== "undefined") {
            Swal.fire({
              title: "Confirmer la suppression",
              html: `<p>Êtes-vous sûr de vouloir supprimer le logo <strong>${escapeHtml(fileName)}</strong> ?</p>
                                  <p style="color: #d33; font-weight: 500;">Cette action est irréversible.</p>`,
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#d33",
              cancelButtonColor: "#3085d6",
              confirmButtonText: "Oui, supprimer",
              cancelButtonText: "Annuler",
              backdrop: true,
              allowOutsideClick: false,
              reverseButtons: true,
            }).then((result) => {
              if (result.isConfirmed) {
                showLoading("Suppression en cours...");

                // Soumettre le formulaire après un court délai
                setTimeout(() => {
                  // Créer un input caché pour s'assurer que le formulaire est soumis
                  const hiddenInput = document.createElement("input");
                  hiddenInput.type = "hidden";
                  hiddenInput.name = "delete_logo";
                  hiddenInput.value = "1";
                  form.appendChild(hiddenInput);

                  // Soumettre le formulaire
                  form.submit();
                }, 300);
              }
            });
          } else {
            // Fallback vers confirm() natif
            if (
              confirm(
                `Êtes-vous sûr de vouloir supprimer le logo ${fileName} ? Cette action est irréversible.`,
              )
            ) {
              form.submit();
            }
          }
        });
      }

      // Également intercepter l'événement submit au cas où
      form.addEventListener("submit", function (e) {
        // Si le formulaire est soumis directement (sans passer par notre gestionnaire)
        // on empêche la soumission par défaut et on déclenche notre logique
        if (!e.detail || !e.detail.fromSweetAlert) {
          e.preventDefault();
          // Déclencher le clic sur le bouton
          const deleteButton = form.querySelector('button[name="delete_logo"]');
          if (deleteButton) {
            deleteButton.click();
          }
        }
      });
    });
  }

  /**
   * Gère le scroll vers une ancre
   */
  function handleAnchorScroll(anchorId, delay) {
    if (!anchorId) return;

    setTimeout(() => {
      const element = document.getElementById(anchorId);
      if (element) {
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
   * Configure les gestionnaires de messages
   */
  function setupMessageHandlers(delay) {
    const messages = document.querySelectorAll(
      ".message-success, .message-error",
    );

    messages.forEach((message) => {
      setTimeout(() => {
        message.style.opacity = "0";
        message.style.transition = "opacity 0.5s ease";

        setTimeout(() => {
          if (message.parentNode) {
            message.parentNode.removeChild(message);
          }
        }, 500);
      }, delay);
    });
  }

  // Initialisation
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    setTimeout(init, 100);
  }

  // API publique
  window.EditLogo = {
    init: init,
    resetSelection: resetFileSelection,
    validateFile: validateFile,
    showErrorAlert: showErrorAlert,
    showLoading: showLoading,
    closeLoading: closeLoading,
  };
})();
