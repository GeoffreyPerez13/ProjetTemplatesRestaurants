/**
 * Gestion de la page d'édition du logo
 * Version complète avec gestion des accordéons
 */
(function () {
    "use strict";

    // ==================== CONFIGURATION ====================
    const CONFIG = {
        maxFileSize: 5 * 1024 * 1024, // 5MB
        allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
        allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        previewMaxWidth: 300,
        previewMaxHeight: 300
    };

    // ==================== VARIABLES GLOBALES ====================
    let selectedFile = null;
    let isInitialized = false;

    // ==================== FONCTIONS D'AIDE ====================

    /**
     * Échapper le HTML pour la sécurité
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Formate la taille d'un fichier
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

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
                }
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
     * Affiche une alerte d'erreur
     */
    function showErrorAlert(message, title = "Erreur") {
        if (typeof Swal !== "undefined") {
            Swal.fire({
                title: title,
                text: message,
                icon: "error",
                confirmButtonColor: "#d33",
                confirmButtonText: "OK",
                backdrop: true
            });
        } else {
            alert(message);
        }
    }

    /**
     * Empêche les comportements par défaut
     */
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // ==================== GESTION DES ACCORDÉONS ====================

    /**
     * Désactive temporairement la fermeture automatique des accordéons
     */
    function disableAutoCloseAccordions() {
        if (!document.querySelector('.edit-logo-container')) {
            return;
        }
        
        // Remplacer temporairement la fonction closeAllExceptFirst
        if (window.AccordionManager && window.AccordionManager.closeAllExceptFirst) {
            const originalFunction = window.AccordionManager.closeAllExceptFirst;
            
            window.AccordionManager.closeAllExceptFirst = function() {
                // Ne rien faire sur la page logo
                return;
            };
            
            // Rétablir la fonction originale après un délai
            setTimeout(() => {
                if (window.AccordionManager) {
                    window.AccordionManager.closeAllExceptFirst = originalFunction;
                }
            }, 1000);
        }
    }

    /**
     * Corrige l'état des accordéons selon qu'un logo existe ou non
     */
    function fixAccordionState() {
        const scrollParams = window.scrollParams || {};
        const hasLogo = scrollParams.hasLogo || false;
        
        if (!hasLogo) return;
        
        setTimeout(() => {
            const currentLogoContent = document.getElementById('current-logo-content');
            const uploadLogoContent = document.getElementById('upload-logo-content');
            
            // Forcer l'ouverture de l'accordéon "Logo actuel"
            if (currentLogoContent) {
                currentLogoContent.style.display = 'block';
                currentLogoContent.style.maxHeight = 'none';
                currentLogoContent.style.opacity = '1';
                currentLogoContent.style.visibility = 'visible';
                currentLogoContent.classList.add('expanded');
                currentLogoContent.classList.remove('collapsed');
                
                // Mettre à jour l'icône
                const currentLogoToggle = document.querySelector('.accordion-toggle[data-target="current-logo-content"]');
                if (currentLogoToggle) {
                    const icon = currentLogoToggle.querySelector('i');
                    if (icon) {
                        icon.className = 'fas fa-chevron-down';
                    }
                }
            }
            
            // Forcer la fermeture de l'accordéon "Upload logo"
            if (uploadLogoContent) {
                uploadLogoContent.style.display = 'none';
                uploadLogoContent.style.maxHeight = '0';
                uploadLogoContent.style.opacity = '0';
                uploadLogoContent.style.visibility = 'hidden';
                uploadLogoContent.classList.add('collapsed');
                uploadLogoContent.classList.remove('expanded');
                
                // Mettre à jour l'icône
                const uploadLogoToggle = document.querySelector('.accordion-toggle[data-target="upload-logo-content"]');
                if (uploadLogoToggle) {
                    const icon = uploadLogoToggle.querySelector('i');
                    if (icon) {
                        icon.className = 'fas fa-chevron-up';
                    }
                }
            }
            
            // Désactiver les transitions pour éviter les animations parasites
            if (currentLogoContent) currentLogoContent.style.transition = 'none';
            if (uploadLogoContent) uploadLogoContent.style.transition = 'none';
            
        }, 100);
    }

    // ==================== VALIDATION DES FICHIERS ====================

    /**
     * Valide un fichier
     */
    function validateFile(file) {
        // Vérification du type MIME
        if (!CONFIG.allowedTypes.includes(file.type)) {
            return {
                valid: false,
                message: 'Type de fichier non autorisé. Formats acceptés: JPG, PNG, GIF, WebP, SVG.'
            };
        }

        // Vérification de la taille
        if (file.size > CONFIG.maxFileSize) {
            return {
                valid: false,
                message: 'Le fichier est trop volumineux. Taille maximale: 5 Mo.'
            };
        }

        // Vérification de l'extension
        const extension = file.name.split('.').pop().toLowerCase();
        if (!CONFIG.allowedExtensions.includes(extension)) {
            return {
                valid: false,
                message: 'Extension de fichier non autorisée.'
            };
        }

        return { valid: true, message: 'Fichier valide' };
    }

    // ==================== GESTION DES FICHIERS ====================

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
        enableUploadButton();
        
        // Ajouter la classe à la zone d'upload
        const uploadArea = document.getElementById('uploadArea');
        if (uploadArea) {
            uploadArea.classList.add('file-selected');
        }
    }

    /**
     * Met à jour les informations du fichier (côte à côte)
     */
    function updateFileInfo(file) {
        const fileInfoContainer = document.getElementById('file-info-container');
        if (!fileInfoContainer) return;

        // Créer le HTML en côte à côte
        fileInfoContainer.innerHTML = `
            <div class="preview-side-by-side">
                <!-- Colonne de l'image -->
                <div class="preview-image-column">
                    <img src="" alt="Aperçu du logo" class="preview-image-main" id="preview-image-main">
                    <div class="preview-image-overlay">
                        <button type="button" class="btn-icon preview-enlarge-side" title="Agrandir">
                            <i class="fas fa-search-plus"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Colonne des informations -->
                <div class="preview-info-column">
                    <div class="preview-info-header">
                        <h3><i class="fas fa-file-image"></i> Aperçu du logo <span class="preview-badge-side">PRÉVISUALISATION</span></h3>
                    </div>
                    
                    <div class="file-info-side">
                        <p class="file-info-name-side">
                            <i class="fas fa-file-alt"></i>
                            <strong>Fichier :</strong> <span>${escapeHtml(file.name)}</span>
                        </p>
                        <p class="file-info-size-side">
                            <strong>Taille :</strong> ${formatFileSize(file.size)}
                        </p>
                        <p class="file-info-type-side">
                            <strong>Type :</strong> ${file.type}
                        </p>
                        <p class="file-info-dimensions-side">
                            <strong>Dimensions :</strong> <span id="image-dimensions-side">Calcul en cours...</span>
                        </p>
                    </div>
                    
                    <div class="preview-actions">
                        <button type="button" class="btn danger" id="remove-file-btn-side">
                            <i class="fas fa-times"></i> Retirer ce fichier
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Afficher le conteneur
        fileInfoContainer.style.display = 'block';

        // Charger et afficher l'image
        const reader = new FileReader();
        const imgElement = document.getElementById('preview-image-main');
        const dimensionsSpan = document.getElementById('image-dimensions-side');

        reader.onload = function(e) {
            imgElement.src = e.target.result;
            
            imgElement.onload = function() {
                if (dimensionsSpan) {
                    dimensionsSpan.textContent = `${this.naturalWidth}×${this.naturalHeight}px`;
                }
                
                // Ajouter l'événement pour la lightbox
                imgElement.addEventListener('click', () => {
                    openLightbox(imgElement.src, `Aperçu: ${file.name} (${this.naturalWidth}×${this.naturalHeight}px)`);
                });
                
                // Ajouter l'événement pour le bouton d'agrandissement
                const enlargeBtn = document.querySelector('.preview-enlarge-side');
                if (enlargeBtn) {
                    enlargeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        openLightbox(imgElement.src, `Aperçu: ${file.name} (${this.naturalWidth}×${this.naturalHeight}px)`);
                    });
                }
            };
        };
        
        reader.readAsDataURL(file);

        // Ajouter l'événement pour retirer le fichier
        const removeBtn = document.getElementById('remove-file-btn-side');
        if (removeBtn) {
            removeBtn.addEventListener('click', resetFileSelection);
        }
    }

    /**
     * Active le bouton d'upload
     */
    function enableUploadButton() {
        const uploadBtn = document.getElementById('uploadBtn');
        if (uploadBtn) {
            uploadBtn.disabled = false;
            uploadBtn.classList.add('active');
        }
    }

    /**
     * Réinitialise la sélection de fichier
     */
    function resetFileSelection() {
        if (!selectedFile) return;

        const confirmReset = () => {
            const fileInput = document.getElementById('logo-input');
            const fileInfoContainer = document.getElementById('file-info-container');
            const uploadBtn = document.getElementById('uploadBtn');
            const uploadArea = document.getElementById('uploadArea');

            if (fileInput) fileInput.value = '';
            if (fileInfoContainer) {
                fileInfoContainer.innerHTML = '';
                fileInfoContainer.style.display = 'none';
            }
            if (uploadBtn) {
                uploadBtn.disabled = true;
                uploadBtn.classList.remove('active');
            }
            if (uploadArea) {
                uploadArea.classList.remove('file-selected');
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
                allowOutsideClick: false
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

    /**
     * Valide avant l'upload
     */
    function validateBeforeUpload(e) {
        if (!selectedFile) {
            e.preventDefault();
            showErrorAlert("Veuillez sélectionner un fichier avant de continuer.", "Fichier manquant");
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

        // Confirmation si un logo existe déjà
        const hasLogo = window.scrollParams?.hasLogo || false;
        if (hasLogo) {
            e.preventDefault();
            
            const confirmUpload = () => {
                const form = document.getElementById('upload-logo-form');
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
                    allowOutsideClick: false
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

    // ==================== DRAG & DROP ====================

    /**
     * Configure le drag & drop
     */
    function setupDragAndDrop() {
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('logo-input');

        if (!uploadArea || !fileInput) return;

        // Empêcher les comportements par défaut
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        // Gestion des événements de drag
        uploadArea.addEventListener('dragenter', () => {
            uploadArea.classList.add('drag-over');
        });

        uploadArea.addEventListener('dragover', () => {
            uploadArea.classList.add('drag-over');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('drag-over');
        });

        uploadArea.addEventListener('drop', (e) => {
            uploadArea.classList.remove('drag-over');
            
            const dt = e.dataTransfer;
            const file = dt.files[0];
            
            if (file) {
                // Simuler la sélection du fichier
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
                
                // Déclencher l'événement change
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        });
    }

    // ==================== LIGHTBOX ====================

    /**
     * Configure la lightbox
     */
    function setupLightbox() {
        const lightbox = document.getElementById('logo-lightbox');
        const lightboxImage = document.getElementById('lightbox-image');
        const lightboxClose = document.getElementById('lightboxClose');
        const currentLogoImage = document.getElementById('current-logo-image');
        const enlargeButtons = document.querySelectorAll('.enlarge-logo');

        // Ouvrir la lightbox
        function openLightbox(imageSrc, caption = '') {
            if (lightboxImage) {
                lightboxImage.src = imageSrc;
                lightbox.style.display = 'flex';
                
                // Mettre à jour la légende
                const captionElement = document.getElementById('lightbox-caption');
                if (captionElement) {
                    captionElement.textContent = caption;
                }
                
                // Animation
                setTimeout(() => {
                    lightbox.style.opacity = '1';
                }, 10);
            }
        }

        // Fermer la lightbox
        function closeLightbox() {
            lightbox.style.opacity = '0';
            setTimeout(() => {
                lightbox.style.display = 'none';
            }, 300);
        }

        // Événements pour le logo actuel
        if (currentLogoImage) {
            currentLogoImage.addEventListener('click', () => {
                openLightbox(currentLogoImage.src, 'Logo actuel');
            });
        }

        // Événements pour les boutons d'agrandissement
        enlargeButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const target = button.closest('.logo-image-container').querySelector('img');
                if (target) {
                    openLightbox(target.src, 'Logo agrandi');
                }
            });
        });

        // Fermer la lightbox
        if (lightboxClose) {
            lightboxClose.addEventListener('click', closeLightbox);
        }

        // Fermer en cliquant à l'extérieur
        if (lightbox) {
            lightbox.addEventListener('click', (e) => {
                if (e.target === lightbox) {
                    closeLightbox();
                }
            });
        }

        // Fermer avec la touche Échap
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && lightbox.style.display === 'flex') {
                closeLightbox();
            }
        });

        // Exposer la fonction
        window.openLightbox = openLightbox;
    }

    // ==================== CONFIRMATIONS DE SUPPRESSION ====================

    /**
     * Configure les confirmations de suppression du logo
     */
    function setupDeleteConfirmations() {
        const deleteForms = document.querySelectorAll('.delete-logo-form');
        
        deleteForms.forEach(form => {
            // Intercepter le clic sur le bouton de suppression
            const deleteButton = form.querySelector('button[name="delete_logo"]');
            
            if (deleteButton) {
                deleteButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Obtenir le nom du fichier
                    const fileName = this.getAttribute('data-filename') || 'ce logo';
                    
                    // Utiliser SweetAlert pour la confirmation
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
                            reverseButtons: false,
                            focusCancel: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                showLoading("Suppression en cours...");
                                
                                setTimeout(() => {
                                    // Créer un input caché
                                    const hiddenInput = document.createElement('input');
                                    hiddenInput.type = 'hidden';
                                    hiddenInput.name = 'delete_logo';
                                    hiddenInput.value = '1';
                                    form.appendChild(hiddenInput);
                                    
                                    // Soumettre le formulaire
                                    form.submit();
                                }, 300);
                            }
                        });
                    } else {
                        // Fallback vers confirm() natif
                        if (confirm(`Êtes-vous sûr de vouloir supprimer le logo ${fileName} ? Cette action est irréversible.`)) {
                            form.submit();
                        }
                    }
                });
            }
            
            // Également intercepter l'événement submit
            form.addEventListener('submit', function(e) {
                if (!e.detail || !e.detail.fromSweetAlert) {
                    e.preventDefault();
                    const deleteButton = form.querySelector('button[name="delete_logo"]');
                    if (deleteButton) {
                        deleteButton.click();
                    }
                }
            });
        });
    }

    // ==================== GESTION DES MESSAGES ====================

    /**
     * Configure les gestionnaires de messages
     */
    function setupMessageHandlers(delay) {
        const messages = document.querySelectorAll('.message-success, .message-error');

        messages.forEach(message => {
            setTimeout(() => {
                message.style.opacity = '0';
                message.style.transition = 'opacity 0.5s ease';

                setTimeout(() => {
                    if (message.parentNode) {
                        message.parentNode.removeChild(message);
                    }
                }, 500);
            }, delay);
        });
    }

    // ==================== GESTION DU SCROLL ====================

    /**
     * Gère le scroll vers une ancre
     */
    function handleAnchorScroll(anchorId, delay) {
        if (!anchorId) return;

        setTimeout(() => {
            const element = document.getElementById(anchorId);
            if (element) {
                const yOffset = -20;
                const y = element.getBoundingClientRect().top + window.pageYOffset + yOffset;

                window.scrollTo({
                    top: y,
                    behavior: 'smooth'
                });

                // Effet visuel
                element.style.boxShadow = '0 0 0 3px rgba(52, 152, 219, 0.5)';
                element.style.transition = 'box-shadow 0.3s ease';

                setTimeout(() => {
                    element.style.boxShadow = '';
                }, 1500);
            }
        }, delay);
    }

    // ==================== INITIALISATION DES ÉLÉMENTS ====================

    /**
     * Initialise les éléments du DOM
     */
    function initElements() {
        // Éléments principaux
        const fileInput = document.getElementById('logo-input');
        const selectFileBtn = document.getElementById('selectFileBtn');
        const uploadBtn = document.getElementById('uploadBtn');
        const resetBtn = document.getElementById('resetBtn');

        if (fileInput && selectFileBtn) {
            // Ouvrir le sélecteur de fichiers
            selectFileBtn.addEventListener('click', () => {
                fileInput.click();
            });

            // Gérer le changement de fichier
            fileInput.addEventListener('change', handleFileSelection);
        }

        // Bouton de réinitialisation
        if (resetBtn) {
            resetBtn.addEventListener('click', resetFileSelection);
        }

        // Bouton d'upload
        if (uploadBtn) {
            uploadBtn.addEventListener('click', validateBeforeUpload);
        }
    }

    /**
     * Configure le conteneur de prévisualisation
     */
    function setupPreviewContainer() {
        const uploadArea = document.getElementById('uploadArea');
        if (!uploadArea) return;

        // Créer le conteneur pour la prévisualisation
        const fileInfoContainer = document.createElement('div');
        fileInfoContainer.id = 'file-info-container';
        fileInfoContainer.className = 'file-info-container';
        fileInfoContainer.style.display = 'none';
        
        // Ajouter après la zone d'upload
        uploadArea.parentNode.insertBefore(fileInfoContainer, uploadArea.nextSibling);
    }

    // ==================== INITIALISATION PRINCIPALE ====================

    /**
     * Initialisation principale
     */
    function init() {
        if (isInitialized || !document.querySelector('.edit-logo-container')) {
            return;
        }

        isInitialized = true;

        // ==================== DÉSACTIVER LA FERMETURE AUTO DES ACCORDÉONS ====================
        disableAutoCloseAccordions();

        // ==================== RÉCUPÉRATION DES PARAMÈTRES ====================
        const scrollParams = window.scrollParams || {};

        // Gestion du scroll vers une ancre
        if (scrollParams.anchor) {
            handleAnchorScroll(scrollParams.anchor, scrollParams.scrollDelay || 1500);
        }

        // ==================== GESTION DES MESSAGES ====================
        setupMessageHandlers(scrollParams.scrollDelay || 1500);

        // ==================== INITIALISATION DES ÉLÉMENTS ====================
        initElements();

        // ==================== GESTION DU DRAG & DROP ====================
        setupDragAndDrop();

        // ==================== GESTION DE LA LIGHTBOX ====================
        setupLightbox();

        // ==================== GESTION DE LA SUPPRESSION ====================
        setupDeleteConfirmations();

        // ==================== PRÉVISUALISATION CÔTE À CÔTE ====================
        setupPreviewContainer();

        // ==================== CORRECTION DES ACCORDÉONS ====================
        setTimeout(fixAccordionState, 300);
    }

    // ==================== ÉVÉNEMENTS DE CHARGEMENT ====================

    // S'assurer que la correction des accordéons s'applique
    window.addEventListener('load', function() {
        setTimeout(fixAccordionState, 500);
    });

    // Initialisation au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // Si le DOM est déjà chargé
        setTimeout(init, 100);
    }

    // ==================== API PUBLIQUE ====================

    window.EditLogo = {
        init: init,
        resetSelection: resetFileSelection,
        validateFile: validateFile,
        showErrorAlert: showErrorAlert,
        showLoading: showLoading,
        closeLoading: closeLoading,
        fixAccordionState: fixAccordionState,
        disableAutoCloseAccordions: disableAutoCloseAccordions
    };

})();