document.addEventListener("DOMContentLoaded", function () {
  const fileInput = document.getElementById("card_images");
  const uploadArea = document.getElementById("uploadArea");
  const fileList = document.getElementById("fileList");
  const imagePreview = document.getElementById("imagePreview");
  const selectedCount = document.getElementById("selectedCount");
  const uploadCount = document.getElementById("uploadCount");
  const uploadButton = document.getElementById("uploadButton");
  const clearSelection = document.getElementById("clearSelection");
  const imageCounter = document.getElementById("imageCounter");

  let selectedFiles = [];

  // Gestion du drag & drop
  if (uploadArea && fileInput) {
    // Prévenir les comportements par défaut
    ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
      uploadArea.addEventListener(eventName, preventDefaults, false);
      document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    // Surligner la zone de drop
    ["dragenter", "dragover"].forEach((eventName) => {
      uploadArea.addEventListener(eventName, highlight, false);
    });

    ["dragleave", "drop"].forEach((eventName) => {
      uploadArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
      uploadArea.classList.add("drag-over");
    }

    function unhighlight() {
      uploadArea.classList.remove("drag-over");
    }

    // Gérer le drop
    uploadArea.addEventListener("drop", handleDrop, false);

    function handleDrop(e) {
      const dt = e.dataTransfer;
      const files = dt.files;
      handleFiles(files);
    }

    // Gérer la sélection via le bouton
    fileInput.addEventListener("change", function () {
      handleFiles(this.files);
    });
  }

  // Limite POST max (récupérée depuis PHP via window.uploadConfig ou fallback 8MB)
  var postMaxSize = (window.uploadConfig && window.uploadConfig.postMaxSize) || 8 * 1024 * 1024;
  var gaugeEl = document.getElementById("uploadSizeGauge");
  var gaugeFill = document.getElementById("gaugeFill");
  var gaugeText = document.getElementById("gaugeText");

  // Gérer les fichiers sélectionnés
  function handleFiles(files) {
    selectedFiles = Array.from(files);
    updateFileList();
    updatePreview();
    updateCounter();
    updateUploadButton();
    updateSizeGauge();

    // Afficher le bouton d'annulation
    if (selectedFiles.length > 0) {
      clearSelection.style.display = "inline-block";
    }
  }

  // Mettre à jour la liste des fichiers
  function updateFileList() {
    fileList.innerHTML = "";

    selectedFiles.forEach((file, index) => {
      const fileItem = document.createElement("div");
      fileItem.className = "file-item";
      fileItem.innerHTML = `
                <span class="file-name">${file.name}</span>
                <span class="file-size">${formatFileSize(file.size)}</span>
                <button type="button" class="file-remove" data-index="${index}">
                    <i class="fas fa-times"></i>
                </button>
            `;
      fileList.appendChild(fileItem);
    });

    // Ajouter les événements de suppression
    document.querySelectorAll(".file-remove").forEach((button) => {
      button.addEventListener("click", function () {
        const index = parseInt(this.getAttribute("data-index"));
        removeFile(index);
      });
    });
  }

  // Mettre à jour la prévisualisation
  function updatePreview() {
    imagePreview.innerHTML = "";

    selectedFiles.forEach((file, index) => {
      if (file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = function (e) {
          const previewItem = document.createElement("div");
          previewItem.className = "preview-item";
          previewItem.innerHTML = `
                        <div class="preview-image-container">
                            <img src="${e.target.result}" alt="${
            file.name
          }" class="preview-image">
                            <button type="button" class="preview-remove" data-index="${index}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="preview-info">
                            <span class="preview-name">${truncateFileName(
                              file.name,
                              15
                            )}</span>
                            <span class="preview-type">${getFileType(
                              file
                            )}</span>
                        </div>
                    `;
          imagePreview.appendChild(previewItem);

          // Ajouter l'événement de suppression
          previewItem
            .querySelector(".preview-remove")
            .addEventListener("click", function () {
              const index = parseInt(this.getAttribute("data-index"));
              removeFile(index);
            });
        };
        reader.readAsDataURL(file);
      } else if (file.type === "application/pdf") {
        const previewItem = document.createElement("div");
        previewItem.className = "preview-item pdf";
        previewItem.innerHTML = `
                    <div class="preview-image-container">
                        <div class="pdf-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <button type="button" class="preview-remove" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="preview-info">
                        <span class="preview-name">${truncateFileName(
                          file.name,
                          15
                        )}</span>
                        <span class="preview-type">PDF</span>
                    </div>
                `;
        imagePreview.appendChild(previewItem);

        // Ajouter l'événement de suppression
        previewItem
          .querySelector(".preview-remove")
          .addEventListener("click", function () {
            const index = parseInt(this.getAttribute("data-index"));
            removeFile(index);
          });
      }
    });
  }

  // Mettre à jour le compteur
  function updateCounter() {
    const count = selectedFiles.length;
    selectedCount.textContent = count;
    uploadCount.textContent = count;

    if (count > 0) {
      imageCounter.style.display = "block";
    } else {
      imageCounter.style.display = "none";
    }
  }

  // Mettre à jour le bouton d'upload
  function updateUploadButton() {
    if (selectedFiles.length > 0) {
      uploadButton.disabled = false;
      uploadButton.classList.remove("disabled");
    } else {
      uploadButton.disabled = true;
      uploadButton.classList.add("disabled");
    }
  }

  // Supprimer un fichier
  function removeFile(index) {
    selectedFiles.splice(index, 1);
    updateFileList();
    updatePreview();
    updateCounter();
    updateUploadButton();
    updateSizeGauge();

    if (selectedFiles.length === 0) {
      clearSelection.style.display = "none";
    }
  }

  // Vider la sélection
  if (clearSelection) {
    clearSelection.addEventListener("click", function () {
      selectedFiles = [];
      updateFileList();
      updatePreview();
      updateCounter();
      updateUploadButton();
      updateSizeGauge();
      fileInput.value = "";
      clearSelection.style.display = "none";
    });
  }

  // Jauge de taille totale
  function updateSizeGauge() {
    if (!gaugeEl || !gaugeFill || !gaugeText) return;

    if (selectedFiles.length === 0) {
      gaugeEl.style.display = "none";
      return;
    }

    var totalSize = 0;
    selectedFiles.forEach(function(f) { totalSize += f.size; });

    var percent = Math.min((totalSize / postMaxSize) * 100, 100);
    gaugeEl.style.display = "flex";
    gaugeFill.style.width = percent.toFixed(1) + "%";

    gaugeFill.classList.remove("warning", "danger");
    if (percent > 90) {
      gaugeFill.classList.add("danger");
    } else if (percent > 60) {
      gaugeFill.classList.add("warning");
    }

    var totalMB = (totalSize / (1024 * 1024)).toFixed(1);
    var maxMB = (postMaxSize / (1024 * 1024)).toFixed(0);
    gaugeText.textContent = totalMB + " Mo / " + maxMB + " Mo";
  }

  // Fonctions utilitaires
  function formatFileSize(bytes) {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
  }

  function truncateFileName(name, maxLength) {
    if (name.length <= maxLength) return name;
    return name.substring(0, maxLength) + "...";
  }

  function getFileType(file) {
    if (file.type.startsWith("image/")) {
      const extension = file.name.split(".").pop().toUpperCase();
      return extension;
    }
    return "PDF";
  }

  // Soumettre le formulaire d'upload via AJAX
  function submitUploadForm() {
    ajaxSubmit(uploadForm, '?page=edit-card', {
      beforeSend: function(formData) {
        // Remplacer les fichiers du input par les selectedFiles (gère le drag & drop)
        formData.delete('card_images[]');
        selectedFiles.forEach(function(file) {
          formData.append('card_images[]', file);
        });
      }
    });
  }

  // Validation avant soumission
  const uploadForm = document.querySelector(".upload-form");
  if (uploadForm) {
    uploadForm.addEventListener("submit", function (e) {
      e.preventDefault();

      // Vérifier qu'il y a des fichiers
      if (selectedFiles.length === 0) {
        Swal.fire(
          "Aucun fichier",
          "Veuillez sélectionner au moins un fichier à télécharger.",
          "warning"
        );
        return;
      }

      // Vérifier la taille des fichiers
      let hasInvalidFile = false;

      selectedFiles.forEach((file) => {
        // Vérifier le type
        const allowedTypes = [
          "image/jpeg",
          "image/png",
          "image/gif",
          "image/webp",
          "application/pdf",
        ];
        if (!allowedTypes.includes(file.type)) {
          hasInvalidFile = true;
          Swal.fire(
            "Type de fichier invalide",
            `Le fichier "${file.name}" n'est pas d'un type autorisé.`,
            "error"
          );
        }

        // Vérifier la taille (5MB max)
        if (file.size > 5 * 1024 * 1024) {
          hasInvalidFile = true;
          Swal.fire(
            "Fichier trop volumineux",
            `Le fichier "${file.name}" dépasse la taille maximale de 5MB.`,
            "error"
          );
        }
      });

      if (hasInvalidFile) {
        return;
      }

      // Confirmation si beaucoup de fichiers
      if (selectedFiles.length > 3) {
        Swal.fire({
          title: "Confirmer le téléchargement",
          text: `Vous êtes sur le point de télécharger ${selectedFiles.length} fichiers. Êtes-vous sûr ?`,
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Oui, télécharger",
          cancelButtonText: "Annuler",
        }).then((result) => {
          if (result.isConfirmed) {
            submitUploadForm();
          }
        });
      } else {
        submitUploadForm();
      }
    });
  }

  // ==================== SÉLECTION MULTIPLE D'IMAGES ====================
  const selectAllCheckbox = document.getElementById("select-all-images");
  const bulkDeleteBtn = document.getElementById("bulk-delete-btn");
  const selectionCount = document.getElementById("selection-count");
  const selectedCountSpan = document.getElementById("selected-count");
  const imageCheckboxes = document.querySelectorAll(".image-checkbox");

  if (selectAllCheckbox && imageCheckboxes.length > 0) {
    // Mettre à jour le compteur et la visibilité du bouton
    function updateSelectionUI() {
      const checked = document.querySelectorAll(".image-checkbox:checked");
      const count = checked.length;

      if (selectedCountSpan) selectedCountSpan.textContent = count;
      if (selectionCount) selectionCount.style.display = count > 0 ? "inline" : "none";
      if (bulkDeleteBtn) bulkDeleteBtn.style.display = count > 0 ? "inline-flex" : "none";

      // Mettre à jour l'état du "Tout sélectionner"
      selectAllCheckbox.checked = count === imageCheckboxes.length && count > 0;
      selectAllCheckbox.indeterminate = count > 0 && count < imageCheckboxes.length;

      // Classe .selected sur les cards
      imageCheckboxes.forEach(function (cb) {
        var card = cb.closest(".image-card");
        if (card) {
          if (cb.checked) {
            card.classList.add("selected");
          } else {
            card.classList.remove("selected");
          }
        }
      });
    }

    // Tout sélectionner / désélectionner
    selectAllCheckbox.addEventListener("change", function () {
      imageCheckboxes.forEach(function (cb) {
        cb.checked = selectAllCheckbox.checked;
      });
      updateSelectionUI();
    });

    // Chaque checkbox individuelle
    imageCheckboxes.forEach(function (cb) {
      cb.addEventListener("change", updateSelectionUI);
    });

    // Suppression en masse
    if (bulkDeleteBtn) {
      bulkDeleteBtn.addEventListener("click", function () {
        var checked = document.querySelectorAll(".image-checkbox:checked");
        var ids = [];
        checked.forEach(function (cb) { ids.push(cb.value); });

        if (ids.length === 0) return;

        var msg = ids.length === 1
          ? "Voulez-vous vraiment supprimer cette image ?"
          : "Voulez-vous vraiment supprimer ces " + ids.length + " images ?";

        if (typeof Swal !== "undefined") {
          Swal.fire({
            title: "Confirmer la suppression",
            text: msg,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Oui, supprimer",
            cancelButtonText: "Annuler",
          }).then(function (result) {
            if (result.isConfirmed) {
              bulkDeleteSubmit(ids);
            }
          });
        } else {
          if (confirm(msg)) {
            bulkDeleteSubmit(ids);
          }
        }
      });
    }

    function bulkDeleteSubmit(ids) {
      var form = document.createElement("form");
      form.style.display = "none";

      // CSRF token
      var csrfInput = document.querySelector('input[name="csrf_token"]');
      if (csrfInput) {
        var csrf = document.createElement("input");
        csrf.type = "hidden";
        csrf.name = "csrf_token";
        csrf.value = csrfInput.value;
        form.appendChild(csrf);
      }

      var action = document.createElement("input");
      action.type = "hidden";
      action.name = "bulk_delete_images";
      action.value = "1";
      form.appendChild(action);

      var idsInput = document.createElement("input");
      idsInput.type = "hidden";
      idsInput.name = "image_ids";
      idsInput.value = ids.join(",");
      form.appendChild(idsInput);

      var anchor = document.createElement("input");
      anchor.type = "hidden";
      anchor.name = "anchor";
      anchor.value = "images-list";
      form.appendChild(anchor);

      document.body.appendChild(form);
      ajaxSubmit(form, "?page=edit-card");
      document.body.removeChild(form);
    }
  }
});
