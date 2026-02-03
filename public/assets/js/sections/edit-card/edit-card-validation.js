// edit-card-validation.js - Validation des formulaires
(function () {
    "use strict";

    const EditCardValidation = {
        /**
         * Initialisation
         */
        init() {
            if (!this.isEditCardPage()) return;
            
            this.setupFormValidation();
            this.setupRealTimeValidation();
        },

        /**
         * Vérifie si on est sur la page d'édition
         */
        isEditCardPage() {
            return document.querySelector('.edit-carte-container') !== null;
        },

        /**
         * Configure la validation des formulaires
         */
        setupFormValidation() {
            // Validation des formulaires de plat
            const dishForms = document.querySelectorAll('.new-dish-form, .edit-form');
            dishForms.forEach(form => {
                form.addEventListener('submit', (e) => this.validateDishForm(e, form));
            });

            // Validation des formulaires de catégorie
            const categoryForms = document.querySelectorAll('.new-category-form, .edit-category-form');
            categoryForms.forEach(form => {
                form.addEventListener('submit', (e) => this.validateCategoryForm(e, form));
            });
        },

        /**
         * Configure la validation en temps réel
         */
        setupRealTimeValidation() {
            // Validation des prix en temps réel
            document.querySelectorAll('input[name="dish_price"]').forEach(input => {
                input.addEventListener('blur', () => this.formatPrice(input));
            });

            // Validation des noms en temps réel
            document.querySelectorAll('input[name="dish_name"], input[name="new_category"], input[name="edit_category_name"]').forEach(input => {
                input.addEventListener('blur', () => this.validateInputLength(input));
            });
        },

        /**
         * Valide un formulaire de plat
         */
        validateDishForm(e, form) {
            let isValid = true;
            
            // Nom du plat
            const nameInput = form.querySelector('input[name="dish_name"]');
            if (nameInput && !this.validateRequired(nameInput, 1, 100)) {
                isValid = false;
                this.showFieldError(nameInput, 'Le nom du plat est requis (max 100 caractères)');
            }

            // Prix
            const priceInput = form.querySelector('input[name="dish_price"]');
            if (priceInput && !this.validatePrice(priceInput)) {
                isValid = false;
                this.showFieldError(priceInput, 'Le prix doit être un nombre entre 0.01 et 999.99');
            }

            // Description
            const descInput = form.querySelector('textarea[name="dish_description"]');
            if (descInput && !this.validateMaxLength(descInput, 500)) {
                isValid = false;
                this.showFieldError(descInput, 'La description ne doit pas dépasser 500 caractères');
            }

            if (!isValid) {
                e.preventDefault();
                this.scrollToFirstError(form);
            }
        },

        /**
         * Valide un formulaire de catégorie
         */
        validateCategoryForm(e, form) {
            let isValid = true;
            
            // Nom de la catégorie
            const nameInput = form.querySelector('input[name="new_category"], input[name="edit_category_name"]');
            if (nameInput && !this.validateRequired(nameInput, 1, 100)) {
                isValid = false;
                this.showFieldError(nameInput, 'Le nom de la catégorie est requis (max 100 caractères)');
            }

            // Validation des fichiers images
            const fileInput = form.querySelector('input[type="file"]');
            if (fileInput && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                if (!this.validateImageFile(file)) {
                    isValid = false;
                    this.showFieldError(fileInput, 'Le fichier doit être une image (JPEG, PNG, GIF, WebP) de moins de 2MB');
                }
            }

            if (!isValid) {
                e.preventDefault();
                this.scrollToFirstError(form);
            }
        },

        /**
         * Valide un champ obligatoire avec longueur
         */
        validateRequired(input, minLength, maxLength) {
            const value = input.value.trim();
            return value.length >= minLength && value.length <= maxLength;
        },

        /**
         * Valide un prix
         */
        validatePrice(input) {
            const value = input.value.replace(',', '.');
            const price = parseFloat(value);
            return !isNaN(price) && price >= 0.01 && price <= 999.99;
        },

        /**
         * Valide la longueur maximale
         */
        validateMaxLength(input, maxLength) {
            return input.value.length <= maxLength;
        },

        /**
         * Valide un fichier image
         */
        validateImageFile(file) {
            if (!file) return true;
            
            // Vérifier le type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                return false;
            }
            
            // Vérifier la taille (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                return false;
            }
            
            return true;
        },

        /**
         * Formate un prix
         */
        formatPrice(input) {
            let value = input.value.replace(',', '.');
            let price = parseFloat(value);
            
            if (!isNaN(price) && price >= 0) {
                input.value = price.toFixed(2);
                this.clearFieldError(input);
            }
        },

        /**
         * Valide la longueur d'un input en temps réel
         */
        validateInputLength(input) {
            const maxLength = input.getAttribute('maxlength') || 100;
            if (input.value.length > maxLength) {
                this.showFieldError(input, `Maximum ${maxLength} caractères autorisés`);
                return false;
            }
            this.clearFieldError(input);
            return true;
        },

        /**
         * Affiche une erreur sur un champ
         */
        showFieldError(input, message) {
            const formGroup = input.closest('.form-group');
            if (!formGroup) return;
            
            formGroup.classList.add('has-error');
            input.classList.add('error-field');
            
            // Supprimer l'ancien message d'erreur
            const oldError = formGroup.querySelector('.field-error-message');
            if (oldError) oldError.remove();
            
            // Ajouter le nouveau message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error-message';
            errorDiv.textContent = message;
            formGroup.appendChild(errorDiv);
        },

        /**
         * Supprime l'erreur d'un champ
         */
        clearFieldError(input) {
            const formGroup = input.closest('.form-group');
            if (!formGroup) return;
            
            formGroup.classList.remove('has-error');
            input.classList.remove('error-field');
            
            const errorDiv = formGroup.querySelector('.field-error-message');
            if (errorDiv) errorDiv.remove();
        },

        /**
         * Fait défiler vers la première erreur
         */
        scrollToFirstError(form) {
            const firstError = form.querySelector('.has-error');
            if (firstError) {
                const yOffset = -100;
                const y = firstError.getBoundingClientRect().top + window.pageYOffset + yOffset;
                
                window.scrollTo({
                    top: y,
                    behavior: 'smooth'
                });
                
                // Focus sur le premier champ erroné
                const errorInput = firstError.querySelector('input, textarea');
                if (errorInput) errorInput.focus();
            }
        },

        /**
         * Affiche une alerte d'erreur
         */
        showAlert(title, message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    text: message,
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
            } else {
                alert(`${title}: ${message}`);
            }
        }
    };

    // API globale
    window.EditCardValidation = EditCardValidation;

    // Initialisation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => EditCardValidation.init());
    } else {
        setTimeout(() => EditCardValidation.init(), 100);
    }
})();