/**
 * Gestion des médias (logo, bannière) et du texte de bannière
 * Version avec support du texte de bannière
 */
(function () {
    "use strict";

    const CONFIG = {
        maxFileSize: 5 * 1024 * 1024,
        allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
        allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']
    };

    class MediaUploader {
        constructor(options) {
            this.prefix = options.prefix;
            this.hasMedia = options.hasMedia || false;
            this.selectedFile = null;

            this.fileInputId = `${this.prefix}-input`;
            this.selectBtnId = `select${this.prefix.charAt(0).toUpperCase() + this.prefix.slice(1)}Btn`;
            this.uploadBtnId = `upload${this.prefix.charAt(0).toUpperCase() + this.prefix.slice(1)}Btn`;
            this.resetBtnId = `reset${this.prefix.charAt(0).toUpperCase() + this.prefix.slice(1)}Btn`;
            this.uploadAreaId = `upload${this.prefix.charAt(0).toUpperCase() + this.prefix.slice(1)}Area`;
            this.previewContainerId = `${this.prefix}-preview-container`;
            this.formId = `upload-${this.prefix}-form`;
            this.currentImageId = `current-${this.prefix}-image`;
            this.enlargeBtnClass = `enlarge-${this.prefix}`;
            this.deleteFormClass = `delete-${this.prefix}-form`;
            this.deleteBtnName = `delete_${this.prefix}`;

            this.init();
        }

        getElement(id) {
            return document.getElementById(id);
        }

        init() {
            this.fileInput = this.getElement(this.fileInputId);
            this.selectBtn = this.getElement(this.selectBtnId);
            this.uploadBtn = this.getElement(this.uploadBtnId);
            this.resetBtn = this.getElement(this.resetBtnId);
            this.uploadArea = this.getElement(this.uploadAreaId);
            this.previewContainer = this.getElement(this.previewContainerId);
            this.currentImage = this.getElement(this.currentImageId);
            this.form = this.getElement(this.formId);

            if (!this.fileInput || !this.selectBtn) return;

            this.selectBtn.addEventListener('click', () => this.fileInput.click());
            this.fileInput.addEventListener('change', (e) => this.handleFileSelection(e));
            if (this.resetBtn) this.resetBtn.addEventListener('click', () => this.resetSelection());
            if (this.uploadBtn) this.uploadBtn.addEventListener('click', (e) => this.validateBeforeUpload(e));

            this.setupDragAndDrop();
            this.setupEnlargeButtons();
            this.setupDeleteConfirmation();
        }

        validateFile(file) {
            if (!CONFIG.allowedTypes.includes(file.type)) {
                return { valid: false, message: 'Type de fichier non autorisé. Formats acceptés: JPG, PNG, GIF, WebP, SVG.' };
            }
            if (file.size > CONFIG.maxFileSize) {
                return { valid: false, message: 'Le fichier est trop volumineux. Taille maximale: 5 Mo.' };
            }
            const extension = file.name.split('.').pop().toLowerCase();
            if (!CONFIG.allowedExtensions.includes(extension)) {
                return { valid: false, message: 'Extension de fichier non autorisée.' };
            }
            return { valid: true };
        }

        handleFileSelection(e) {
            const file = e.target.files[0];
            if (!file) return;

            const validation = this.validateFile(file);
            if (!validation.valid) {
                this.showErrorAlert(validation.message);
                this.resetSelection();
                return;
            }

            this.selectedFile = file;
            this.updatePreview(file);
            this.enableUploadButton();

            if (this.uploadArea) this.uploadArea.classList.add('file-selected');
        }

        updatePreview(file) {
            if (!this.previewContainer) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                const dimensionsSpanId = `${this.prefix}-image-dimensions`;
                this.previewContainer.innerHTML = this.getPreviewHTML(file, e.target.result, dimensionsSpanId);
                this.previewContainer.style.display = 'block';

                const img = this.previewContainer.querySelector('.preview-image-main');
                const dimensionsSpan = document.getElementById(dimensionsSpanId);

                img.onload = () => {
                    if (dimensionsSpan) {
                        dimensionsSpan.textContent = `${img.naturalWidth}×${img.naturalHeight}px`;
                    }
                };

                img.addEventListener('click', () => {
                    window.openLightbox(img.src, `Aperçu: ${file.name}`);
                });

                const enlargeBtn = this.previewContainer.querySelector('.preview-enlarge-side');
                if (enlargeBtn) {
                    enlargeBtn.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        window.openLightbox(img.src, `Aperçu: ${file.name}`);
                    });
                }

                const removeBtn = this.previewContainer.querySelector('.remove-file-btn');
                if (removeBtn) {
                    removeBtn.addEventListener('click', () => this.resetSelection());
                }
            };
            reader.readAsDataURL(file);
        }

        getPreviewHTML(file, dataUrl, dimensionsId) {
            return `
                <div class="preview-side-by-side">
                    <div class="preview-image-column">
                        <img src="${dataUrl}" alt="Aperçu" class="preview-image-main">
                        <div class="preview-image-overlay">
                            <button type="button" class="btn-icon preview-enlarge-side" title="Agrandir"><i class="fas fa-search-plus"></i></button>
                        </div>
                    </div>
                    <div class="preview-info-column">
                        <div class="preview-info-header">
                            <h3><i class="fas fa-file-image"></i> Aperçu</h3>
                        </div>
                        <div class="file-info-side">
                            <p class="file-info-name-side">
                                <i class="fas fa-file-alt"></i>
                                <strong>Fichier :</strong> <span>${this.escapeHtml(file.name)}</span>
                            </p>
                            <p class="file-info-size-side">
                                <strong>Taille :</strong> ${this.formatFileSize(file.size)}
                            </p>
                            <p class="file-info-type-side">
                                <strong>Type :</strong> ${file.type}
                            </p>
                            <p class="file-info-dimensions-side">
                                <strong>Dimensions :</strong> <span id="${dimensionsId}">Calcul en cours...</span>
                            </p>
                        </div>
                        <div class="preview-actions">
                            <button type="button" class="btn danger remove-file-btn"><i class="fas fa-times"></i> Retirer ce fichier</button>
                        </div>
                    </div>
                </div>
            `;
        }

        enableUploadButton() {
            if (this.uploadBtn) {
                this.uploadBtn.disabled = false;
                this.uploadBtn.classList.add('active');
            }
        }

        disableUploadButton() {
            if (this.uploadBtn) {
                this.uploadBtn.disabled = true;
                this.uploadBtn.classList.remove('active');
            }
        }

        resetSelection() {
            if (!this.selectedFile) return;

            const confirmReset = () => {
                this.fileInput.value = '';
                if (this.previewContainer) {
                    this.previewContainer.innerHTML = '';
                    this.previewContainer.style.display = 'none';
                }
                this.disableUploadButton();
                if (this.uploadArea) this.uploadArea.classList.remove('file-selected');
                this.selectedFile = null;
            };

            if (typeof Swal !== "undefined") {
                Swal.fire({
                    title: "Confirmer l'annulation",
                    text: "Voulez-vous vraiment retirer le fichier sélectionné ?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Oui, retirer",
                    cancelButtonText: "Annuler"
                }).then((result) => {
                    if (result.isConfirmed) confirmReset();
                });
            } else {
                if (confirm("Voulez-vous vraiment retirer le fichier sélectionné ?")) confirmReset();
            }
        }

        validateBeforeUpload(e) {
            if (!this.selectedFile) {
                e.preventDefault();
                this.showErrorAlert("Veuillez sélectionner un fichier avant de continuer.", "Fichier manquant");
                return false;
            }

            const validation = this.validateFile(this.selectedFile);
            if (!validation.valid) {
                e.preventDefault();
                this.showErrorAlert(validation.message);
                this.resetSelection();
                return false;
            }

            if (this.hasMedia) {
                e.preventDefault();
                const mediaName = this.prefix === 'logo' ? 'logo' : 'bannière';
                const confirmUpload = () => {
                    if (this.form) {
                        this.showLoading("Upload en cours...");
                        setTimeout(() => this.form.submit(), 100);
                    }
                };

                if (typeof Swal !== "undefined") {
                    Swal.fire({
                        title: `Remplacer ${mediaName}`,
                        text: `Êtes-vous sûr de vouloir remplacer ${mediaName === 'logo' ? 'le logo' : 'la bannière'} actuel${mediaName === 'logo' ? '' : 'le'} ? Cette action ne peut pas être annulée.`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Oui, remplacer",
                        cancelButtonText: "Annuler"
                    }).then((result) => {
                        if (result.isConfirmed) confirmUpload();
                    });
                } else {
                    if (confirm(`Êtes-vous sûr de vouloir remplacer ${mediaName === 'logo' ? 'le logo' : 'la bannière'} actuel${mediaName === 'logo' ? '' : 'le'} ?`)) confirmUpload();
                }
                return false;
            }
            return true;
        }

        setupDragAndDrop() {
            if (!this.uploadArea || !this.fileInput) return;

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                this.uploadArea.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            this.uploadArea.addEventListener('dragenter', () => this.uploadArea.classList.add('drag-over'));
            this.uploadArea.addEventListener('dragover', () => this.uploadArea.classList.add('drag-over'));
            this.uploadArea.addEventListener('dragleave', () => this.uploadArea.classList.remove('drag-over'));

            this.uploadArea.addEventListener('drop', (e) => {
                this.uploadArea.classList.remove('drag-over');
                const dt = e.dataTransfer;
                const file = dt.files[0];
                if (file) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    this.fileInput.files = dataTransfer.files;
                    this.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }

        setupEnlargeButtons() {
            if (!this.currentImage) return;
            this.currentImage.addEventListener('click', () => {
                window.openLightbox(this.currentImage.src, `${this.prefix === 'logo' ? 'Logo' : 'Bannière'} actuel${this.prefix === 'logo' ? '' : 'le'}`);
            });

            document.querySelectorAll(`.${this.enlargeBtnClass}`).forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const img = btn.closest(`.${this.prefix}-image-container`)?.querySelector('img');
                    if (img) window.openLightbox(img.src, `${this.prefix === 'logo' ? 'Logo' : 'Bannière'} agrandi${this.prefix === 'logo' ? '' : 'e'}`);
                });
            });
        }

        setupDeleteConfirmation() {
            const deleteForms = document.querySelectorAll(`.${this.deleteFormClass}`);
            deleteForms.forEach(form => {
                const deleteButton = form.querySelector(`button[name="${this.deleteBtnName}"]`);
                if (!deleteButton) return;

                deleteButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const fileName = deleteButton.getAttribute('data-filename') || (this.prefix === 'logo' ? 'ce logo' : 'cette bannière');
                    const mediaName = this.prefix === 'logo' ? 'logo' : 'bannière';

                    if (typeof Swal !== "undefined") {
                        Swal.fire({
                            title: "Confirmer la suppression",
                            html: `<p>Êtes-vous sûr de vouloir supprimer ${mediaName === 'logo' ? 'le logo' : 'la bannière'} <strong>${this.escapeHtml(fileName)}</strong> ?</p>
                                   <p style="color: #d33; font-weight: 500;">Cette action est irréversible.</p>`,
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#d33",
                            cancelButtonColor: "#3085d6",
                            confirmButtonText: "Oui, supprimer",
                            cancelButtonText: "Annuler"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.showLoading("Suppression en cours...");
                                setTimeout(() => {
                                    const hiddenInput = document.createElement('input');
                                    hiddenInput.type = 'hidden';
                                    hiddenInput.name = this.deleteBtnName;
                                    hiddenInput.value = '1';
                                    form.appendChild(hiddenInput);
                                    form.submit();
                                }, 300);
                            }
                        });
                    } else {
                        if (confirm(`Êtes-vous sûr de vouloir supprimer ${mediaName === 'logo' ? 'le logo' : 'la bannière'} ${fileName} ? Cette action est irréversible.`)) {
                            form.submit();
                        }
                    }
                });

                form.addEventListener('submit', (e) => {
                    if (!e.detail || !e.detail.fromSweetAlert) {
                        e.preventDefault();
                        deleteButton.click();
                    }
                });
            });
        }

        showErrorAlert(message, title = "Erreur") {
            if (typeof Swal !== "undefined") {
                Swal.fire({ title, text: message, icon: "error", confirmButtonColor: "#d33" });
            } else {
                alert(message);
            }
        }

        showLoading(message) {
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    title: message,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => Swal.showLoading()
                });
            }
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }

    function setupGlobalLightbox() {
        const lightbox = document.getElementById('media-lightbox');
        if (!lightbox) return;

        const lightboxImage = document.getElementById('lightbox-image');
        const lightboxClose = document.getElementById('lightboxClose');
        const captionElement = document.getElementById('lightbox-caption');

        window.openLightbox = function (imageSrc, caption = '') {
            lightboxImage.src = imageSrc;
            if (captionElement) captionElement.textContent = caption;
            lightbox.style.display = 'flex';
            setTimeout(() => lightbox.style.opacity = '1', 10);
        };

        function closeLightbox() {
            lightbox.style.opacity = '0';
            setTimeout(() => lightbox.style.display = 'none', 300);
        }

        lightboxClose?.addEventListener('click', closeLightbox);
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) closeLightbox();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && lightbox.style.display === 'flex') closeLightbox();
        });
    }

    function setupBannerTextDelete() {
        const deleteBtn = document.getElementById('deleteBannerTextBtn');
        if (!deleteBtn) return;

        const deleteForm = document.getElementById('deleteBannerTextForm');
        if (!deleteForm) return;

        deleteBtn.addEventListener('click', (e) => {
            e.preventDefault();

            if (typeof Swal !== "undefined") {
                Swal.fire({
                    title: "Confirmer la suppression",
                    text: "Voulez-vous vraiment supprimer le texte de la bannière ?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Oui, supprimer",
                    cancelButtonText: "Annuler"
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteForm.submit();
                    }
                });
            } else {
                if (confirm("Voulez-vous vraiment supprimer le texte de la bannière ?")) {
                    deleteForm.submit();
                }
            }
        });
    }

    function disableAutoCloseAccordions() {
        if (!document.querySelector('.edit-logo-container, .edit-banner-container')) return;
        if (window.AccordionManager && window.AccordionManager.closeAllExceptFirst) {
            const original = window.AccordionManager.closeAllExceptFirst;
            window.AccordionManager.closeAllExceptFirst = function () { return; };
            setTimeout(() => {
                if (window.AccordionManager) {
                    window.AccordionManager.closeAllExceptFirst = original;
                }
            }, 1000);
        }
    }

    function init() {
        setupGlobalLightbox();
        disableAutoCloseAccordions();

        const scrollParams = window.scrollParams || {};

        if (document.getElementById('logo-input')) {
            new MediaUploader({
                prefix: 'logo',
                hasMedia: scrollParams.hasLogo || false
            });
        }
        if (document.getElementById('banner-input')) {
            new MediaUploader({
                prefix: 'banner',
                hasMedia: scrollParams.hasBanner || false
            });
        }

        // Gestion du texte de bannière
        setupBannerTextDelete();

        const messages = document.querySelectorAll('.message-success, .message-error');
        const delay = scrollParams.scrollDelay || 1500;
        messages.forEach(msg => {
            setTimeout(() => {
                msg.style.opacity = '0';
                msg.style.transition = 'opacity 0.5s';
                setTimeout(() => msg.remove(), 500);
            }, delay);
        });

        if (scrollParams.anchor) {
            setTimeout(() => {
                const el = document.getElementById(scrollParams.anchor);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    el.style.boxShadow = '0 0 0 3px rgba(52,152,219,0.5)';
                    setTimeout(() => el.style.boxShadow = '', 1500);
                }
            }, scrollParams.scrollDelay || 1500);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 100);
    }

    window.MediaUploader = MediaUploader;
})();