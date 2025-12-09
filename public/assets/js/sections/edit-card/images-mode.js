document.addEventListener("DOMContentLoaded", function () {
    const fileInput = document.getElementById('card_images');
    const uploadArea = document.getElementById('uploadArea');
    const fileList = document.getElementById('fileList');
    const imagePreview = document.getElementById('imagePreview');
    const selectedCount = document.getElementById('selectedCount');
    const uploadCount = document.getElementById('uploadCount');
    const uploadButton = document.getElementById('uploadButton');
    const clearSelection = document.getElementById('clearSelection');
    const imageCounter = document.getElementById('imageCounter');
    
    let selectedFiles = [];
    
    // Gestion du drag & drop
    if (uploadArea && fileInput) {
        // Prévenir les comportements par défaut
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        // Surligner la zone de drop
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            uploadArea.classList.add('drag-over');
        }
        
        function unhighlight() {
            uploadArea.classList.remove('drag-over');
        }
        
        // Gérer le drop
        uploadArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }
        
        // Gérer la sélection via le bouton
        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });
    }
    
    // Gérer les fichiers sélectionnés
    function handleFiles(files) {
        selectedFiles = Array.from(files);
        updateFileList();
        updatePreview();
        updateCounter();
        updateUploadButton();
        
        // Afficher le bouton d'annulation
        if (selectedFiles.length > 0) {
            clearSelection.style.display = 'inline-block';
        }
    }
    
    // Mettre à jour la liste des fichiers
    function updateFileList() {
        fileList.innerHTML = '';
        
        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
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
        document.querySelectorAll('.file-remove').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                removeFile(index);
            });
        });
    }
    
    // Mettre à jour la prévisualisation
    function updatePreview() {
        imagePreview.innerHTML = '';
        
        selectedFiles.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    previewItem.innerHTML = `
                        <div class="preview-image-container">
                            <img src="${e.target.result}" alt="${file.name}" class="preview-image">
                            <button type="button" class="preview-remove" data-index="${index}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="preview-info">
                            <span class="preview-name">${truncateFileName(file.name, 15)}</span>
                            <span class="preview-type">${getFileType(file)}</span>
                        </div>
                    `;
                    imagePreview.appendChild(previewItem);
                    
                    // Ajouter l'événement de suppression
                    previewItem.querySelector('.preview-remove').addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-index'));
                        removeFile(index);
                    });
                };
                reader.readAsDataURL(file);
            } else if (file.type === 'application/pdf') {
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
                        <span class="preview-name">${truncateFileName(file.name, 15)}</span>
                        <span class="preview-type">PDF</span>
                    </div>
                `;
                imagePreview.appendChild(previewItem);
                
                // Ajouter l'événement de suppression
                previewItem.querySelector('.preview-remove').addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
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
            imageCounter.style.display = 'block';
        } else {
            imageCounter.style.display = 'none';
        }
    }
    
    // Mettre à jour le bouton d'upload
    function updateUploadButton() {
        if (selectedFiles.length > 0) {
            uploadButton.disabled = false;
            uploadButton.classList.remove('disabled');
        } else {
            uploadButton.disabled = true;
            uploadButton.classList.add('disabled');
        }
    }
    
    // Supprimer un fichier
    function removeFile(index) {
        selectedFiles.splice(index, 1);
        updateFileList();
        updatePreview();
        updateCounter();
        updateUploadButton();
        
        if (selectedFiles.length === 0) {
            clearSelection.style.display = 'none';
        }
    }
    
    // Vider la sélection
    if (clearSelection) {
        clearSelection.addEventListener('click', function() {
            selectedFiles = [];
            updateFileList();
            updatePreview();
            updateCounter();
            updateUploadButton();
            fileInput.value = '';
            clearSelection.style.display = 'none';
        });
    }
    
    // Fonctions utilitaires
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function truncateFileName(name, maxLength) {
        if (name.length <= maxLength) return name;
        return name.substring(0, maxLength) + '...';
    }
    
    function getFileType(file) {
        if (file.type.startsWith('image/')) {
            const extension = file.name.split('.').pop().toUpperCase();
            return extension;
        }
        return 'PDF';
    }
    
    // Validation avant soumission
    const uploadForm = document.querySelector('.upload-form');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            // Vérifier qu'il y a des fichiers
            if (selectedFiles.length === 0) {
                e.preventDefault();
                Swal.fire('Aucun fichier', 'Veuillez sélectionner au moins un fichier à télécharger.', 'warning');
                return;
            }
            
            // Vérifier la taille des fichiers
            let totalSize = 0;
            let hasInvalidFile = false;
            
            selectedFiles.forEach(file => {
                totalSize += file.size;
                
                // Vérifier le type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    hasInvalidFile = true;
                    Swal.fire('Type de fichier invalide', 
                        `Le fichier "${file.name}" n'est pas d'un type autorisé.`, 
                        'error');
                }
                
                // Vérifier la taille (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    hasInvalidFile = true;
                    Swal.fire('Fichier trop volumineux', 
                        `Le fichier "${file.name}" dépasse la taille maximale de 5MB.`, 
                        'error');
                }
            });
            
            if (hasInvalidFile) {
                e.preventDefault();
                return;
            }
            
            // Confirmation
            if (selectedFiles.length > 3) {
                e.preventDefault();
                Swal.fire({
                    title: 'Confirmer le téléchargement',
                    text: `Vous êtes sur le point de télécharger ${selectedFiles.length} fichiers. Êtes-vous sûr ?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, télécharger',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        uploadForm.submit();
                    }
                });
            }
        });
    }
});