// edit-card-images.js - Gestion du mode images
(function () {
    "use strict";

    const EditCardImages = {
        // État
        selectedFiles: [],
        
        /**
         * Initialisation
         */
        init() {
            if (!this.isImagesMode()) return;
            
            this.setupUploadArea();
            this.setupFileHandlers();
            this.setupFormValidation();
        },

        /**
         * Vérifie si on est en mode images
         */
        isImagesMode() {
            return document.querySelector('.images-mode-container') !== null;
        },

        /**
         * Configure la zone d'upload
         */
        setupUploadArea() {
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('card_images');
            
            if (!uploadArea || !fileInput) return;
            
            // Prévenir les comportements par défaut
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, this.preventDefaults, false);
                document.body.addEventListener(eventName, this.preventDefaults, false);
            });

            // Surligner la zone
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => this.highlightArea(uploadArea), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => this.unhighlightArea(uploadArea), false);
            });

            // Gérer le drop
            uploadArea.addEventListener('drop', (e) => this.handleDrop(e), false);
            
            // Gérer la sélection de fichiers
            fileInput.addEventListener('change', (e) => this.handleFileSelection(e.target.files));
        },

        /**
         * Configure les gestionnaires de fichiers
         */
        setupFileHandlers() {
            const clearSelection = document.getElementById('clearSelection');
            if (clearSelection) {
                clearSelection.addEventListener('click', () => this.clearSelection());
            }
        },

        /**
         * Configure la validation du formulaire
         */
        setupFormValidation() {
            const uploadForm = document.querySelector('.upload-form');
            if (uploadForm) {
                uploadForm.addEventListener('submit', (e) => this.validateUpload(e));
            }
        },

        /**
         * Empêche les comportements par défaut
         */
        preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        },

        /**
         * Surligne la zone de drop
         */
        highlightArea(element) {
            element.classList.add('drag-over');
        },

        /**
         * Retire le surlignage
         */
        unhighlightArea(element) {
            element.classList.remove('drag-over');
        },

        /**
         * Gère le drop de fichiers
         */
        handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            this.handleFileSelection(files);
        },

        /**
         * Gère la sélection de fichiers
         */
        handleFileSelection(files) {
            this.selectedFiles = Array.from(files);
            this.updateFileList();
            this.updatePreview();
            this.updateCounter();
            this.updateUploadButton();
            
            // Afficher le bouton d'annulation
            const clearSelection = document.getElementById('clearSelection');
            if (clearSelection && this.selectedFiles.length > 0) {
                clearSelection.style.display = 'inline-block';
            }
        },

        /**
         * Met à jour la liste des fichiers
         */
        updateFileList() {
            const fileList = document.getElementById('fileList');
            if (!fileList) return;
            
            fileList.innerHTML = '';
            
            this.selectedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <span class="file-name">${file.name}</span>
                    <span class="file-size">${this.formatFileSize(file.size)}</span>
                    <button type="button" class="file-remove" data-index="${index}">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                fileList.appendChild(fileItem);
            });

            // Ajouter les événements de suppression
            document.querySelectorAll('.file-remove').forEach(button => {
                button.addEventListener('click', (e) => {
                    const index = parseInt(e.currentTarget.getAttribute('data-index'));
                    this.removeFile(index);
                });
            });
        },

        /**
         * Met à jour la prévisualisation
         */
        updatePreview() {
            const imagePreview = document.getElementById('imagePreview');
            if (!imagePreview) return;
            
            imagePreview.innerHTML = '';
            
            this.selectedFiles.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const previewItem = this.createImagePreview(file, e.target.result, index);
                        imagePreview.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                } else if (file.type === 'application/pdf') {
                    const previewItem = this.createPDFPreview(file, index);
                    imagePreview.appendChild(previewItem);
                }
            });
        },

        /**
         * Crée une prévisualisation d'image
         */
        createImagePreview(file, dataUrl, index) {
            const previewItem = document.createElement('div');
            previewItem.className = 'preview-item';
            previewItem.innerHTML = `
                <div class="preview-image-container">
                    <img src="${dataUrl}" alt="${file.name}" class="preview-image">
                    <button type="button" class="preview-remove" data-index="${index}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="preview-info">
                    <span class="preview-name">${this.truncateFileName(file.name, 15)}</span>
                    <span class="preview-type">${this.getFileExtension(file)}</span>
                </div>
            `;
            
            previewItem.querySelector('.preview-remove').addEventListener('click', (e) => {
                const index = parseInt(e.currentTarget.getAttribute('data-index'));
                this.removeFile(index);
            });
            
            return previewItem;
        },

        /**
         * Crée une prévisualisation de PDF
         */
        createPDFPreview(file, index) {
            const previewItem = document.createElement('div');
            previewItem.className = 'preview-item pdf';
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
                    <span class="preview-name">${this.truncateFileName(file.name, 15)}</span>
                    <span class="preview-type">PDF</span>
                </div>
            `;
            
            previewItem.querySelector('.preview-remove').addEventListener('click', (e) => {
                const index = parseInt(e.currentTarget.getAttribute('data-index'));
                this.removeFile(index);
            });
            
            return previewItem;
        },

        /**
         * Met à jour le compteur
         */
        updateCounter() {
            const selectedCount = document.getElementById('selectedCount');
            const uploadCount = document.getElementById('uploadCount');
            const imageCounter = document.getElementById('imageCounter');
            
            if (selectedCount) selectedCount.textContent = this.selectedFiles.length;
            if (uploadCount) uploadCount.textContent = this.selectedFiles.length;
            
            if (imageCounter) {
                imageCounter.style.display = this.selectedFiles.length > 0 ? 'block' : 'none';
            }
        },

        /**
         * Met à jour le bouton d'upload
         */
        updateUploadButton() {
            const uploadButton = document.getElementById('uploadButton');
            if (!uploadButton) return;
            
            if (this.selectedFiles.length > 0) {
                uploadButton.disabled = false;
                uploadButton.classList.remove('disabled');
            } else {
                uploadButton.disabled = true;
                uploadButton.classList.add('disabled');
            }
        },

        /**
         * Supprime un fichier
         */
        removeFile(index) {
            this.selectedFiles.splice(index, 1);
            this.updateFileList();
            this.updatePreview();
            this.updateCounter();
            this.updateUploadButton();
            
            // Cacher le bouton d'annulation si plus de fichiers
            const clearSelection = document.getElementById('clearSelection');
            if (clearSelection && this.selectedFiles.length === 0) {
                clearSelection.style.display = 'none';
            }
            
            // Réinitialiser l'input file
            const fileInput = document.getElementById('card_images');
            if (fileInput) fileInput.value = '';
        },

        /**
         * Vide la sélection
         */
        clearSelection() {
            this.selectedFiles = [];
            this.updateFileList();
            this.updatePreview();
            this.updateCounter();
            this.updateUploadButton();
            
            const fileInput = document.getElementById('card_images');
            if (fileInput) fileInput.value = '';
            
            const clearSelection = document.getElementById('clearSelection');
            if (clearSelection) clearSelection.style.display = 'none';
        },

        /**
         * Valide l'upload avant soumission
         */
        validateUpload(e) {
            // Vérifier qu'il y a des fichiers
            if (this.selectedFiles.length === 0) {
                e.preventDefault();
                this.showAlert('Aucun fichier', 'Veuillez sélectionner au moins un fichier à télécharger.', 'warning');
                return;
            }

            // Vérifier la taille et le type des fichiers
            let hasInvalidFile = false;
            let errorMessage = '';

            this.selectedFiles.forEach((file) => {
                // Vérifier le type
                const allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp',
                    'application/pdf'
                ];
                
                if (!allowedTypes.includes(file.type)) {
                    hasInvalidFile = true;
                    errorMessage = `Le fichier "${file.name}" n'est pas d'un type autorisé.`;
                    return;
                }

                // Vérifier la taille (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    hasInvalidFile = true;
                    errorMessage = `Le fichier "${file.name}" dépasse la taille maximale de 5MB.`;
                    return;
                }
            });

            if (hasInvalidFile) {
                e.preventDefault();
                this.showAlert('Fichier invalide', errorMessage, 'error');
                return;
            }

            // Confirmation pour plus de 3 fichiers
            if (this.selectedFiles.length > 3) {
                e.preventDefault();
                this.confirmUpload();
            }
        },

        /**
         * Demande confirmation pour l'upload
         */
        confirmUpload() {
            if (typeof Swal === 'undefined') {
                if (confirm(`Vous êtes sur le point de télécharger ${this.selectedFiles.length} fichiers. Êtes-vous sûr ?`)) {
                    document.querySelector('.upload-form').submit();
                }
                return;
            }

            Swal.fire({
                title: 'Confirmer le téléchargement',
                text: `Vous êtes sur le point de télécharger ${this.selectedFiles.length} fichiers. Êtes-vous sûr ?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Oui, télécharger',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelector('.upload-form').submit();
                }
            });
        },

        /**
         * Affiche une alerte
         */
        showAlert(title, text, icon) {
            if (typeof Swal !== 'undefined') {
                Swal.fire(title, text, icon);
            } else {
                alert(`${title}: ${text}`);
            }
        },

        /**
         * Formate la taille d'un fichier
         */
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        /**
         * Tronque un nom de fichier
         */
        truncateFileName(name, maxLength) {
            if (name.length <= maxLength) return name;
            return name.substring(0, maxLength) + '...';
        },

        /**
         * Récupère l'extension d'un fichier
         */
        getFileExtension(file) {
            if (file.type.startsWith('image/')) {
                return file.name.split('.').pop().toUpperCase();
            }
            return 'PDF';
        }
    };

    // API globale
    window.EditCardImages = EditCardImages;

    // Initialisation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => EditCardImages.init());
    } else {
        setTimeout(() => EditCardImages.init(), 150);
    }
})();