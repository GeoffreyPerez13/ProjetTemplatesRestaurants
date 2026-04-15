/**
 * Système de validation visuelle des formulaires (rouge/vert)
 * Valable pour toute l'application MenuMiam
 */

(function() {
    'use strict';

    // Classe pour gérer la validation d'un champ
    class FieldValidator {
        constructor(field) {
            this.field = field;
            this.isRequired = field.hasAttribute('required');
            this.type = field.type;
            this.min = field.getAttribute('min');
            this.max = field.getAttribute('max');
            this.minLength = field.getAttribute('minlength');
            this.maxLength = field.getAttribute('maxlength');
            this.pattern = field.getAttribute('pattern');
            
            this.init();
        }

        init() {
            // Ajouter les événements de validation
            this.field.addEventListener('input', () => this.validate());
            this.field.addEventListener('blur', () => this.validate());
            this.field.addEventListener('change', () => this.validate());
        }

        validate() {
            const value = this.field.value.trim();
            let isValid = true;
            let errorMessage = '';

            // Vérifier si le champ est requis
            if (this.isRequired && !value) {
                isValid = false;
                errorMessage = 'Ce champ est obligatoire';
            }
            // Vérifier la longueur minimale
            else if (this.minLength && value.length < parseInt(this.minLength)) {
                isValid = false;
                errorMessage = `Minimum ${this.minLength} caractères requis`;
            }
            // Vérifier la longueur maximale
            else if (this.maxLength && value.length > parseInt(this.maxLength)) {
                isValid = false;
                errorMessage = `Maximum ${this.maxLength} caractères autorisés`;
            }
            // Vérifier le pattern
            else if (this.pattern && value && !new RegExp(this.pattern).test(value)) {
                isValid = false;
                errorMessage = 'Format invalide';
            }
            // Vérifier les nombres
            else if (this.type === 'number' && value) {
                const numValue = parseFloat(value);
                if (isNaN(numValue)) {
                    isValid = false;
                    errorMessage = 'Nombre invalide';
                } else if (this.min && numValue < parseFloat(this.min)) {
                    isValid = false;
                    errorMessage = `Minimum: ${this.min}`;
                } else if (this.max && numValue > parseFloat(this.max)) {
                    isValid = false;
                    errorMessage = `Maximum: ${this.max}`;
                }
            }
            // Vérifier les emails
            else if (this.type === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Email invalide';
                }
            }

            this.updateFieldStyle(isValid, errorMessage);
            return isValid;
        }

        updateFieldStyle(isValid, errorMessage) {
            // Retirer les anciennes classes
            this.field.classList.remove('field-valid', 'field-invalid');
            
            // Retirer l'ancien message d'erreur
            const existingError = this.field.parentElement.querySelector('.field-error-message');
            if (existingError) {
                existingError.remove();
            }

            // Si le champ a une valeur
            if (this.field.value.trim()) {
                if (isValid) {
                    this.field.classList.add('field-valid');
                } else {
                    this.field.classList.add('field-invalid');
                    
                    // Ajouter le message d'erreur
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'field-error-message';
                    errorDiv.textContent = errorMessage;
                    this.field.parentElement.appendChild(errorDiv);
                }
            }
        }

        reset() {
            this.field.classList.remove('field-valid', 'field-invalid');
            const existingError = this.field.parentElement.querySelector('.field-error-message');
            if (existingError) {
                existingError.remove();
            }
        }
    }

    // Classe pour gérer la validation d'un formulaire
    class FormValidator {
        constructor(form) {
            this.form = form;
            this.validators = [];
            this.init();
        }

        init() {
            // Trouver tous les champs à valider
            const fields = this.form.querySelectorAll('input:not([type="hidden"]):not([type="file"]), textarea, select');
            
            fields.forEach(field => {
                const validator = new FieldValidator(field);
                this.validators.push(validator);
            });

            // Intercepter la soumission du formulaire
            this.form.addEventListener('submit', (e) => {
                if (!this.validateAll()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    
                    // Afficher un message d'erreur global
                    if (window.App && window.App.showToast) {
                        window.App.showToast('Veuillez remplir tous les champs obligatoires correctement', 'error');
                    }
                    
                    // Scroller vers le premier champ invalide
                    const firstInvalid = this.form.querySelector('.field-invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalid.focus();
                    }
                    
                    return false;
                }
            }, true);
        }

        validateAll() {
            let allValid = true;
            
            this.validators.forEach(validator => {
                if (!validator.validate()) {
                    allValid = false;
                }
            });
            
            return allValid;
        }

        reset() {
            this.validators.forEach(validator => validator.reset());
        }
    }

    // Ajouter les astérisques rouges aux labels des champs obligatoires
    function addRequiredAsterisks() {
        const requiredFields = document.querySelectorAll('input[required], textarea[required], select[required]');
        
        requiredFields.forEach(field => {
            // Trouver le label associé
            let label = null;
            
            if (field.id) {
                label = document.querySelector(`label[for="${field.id}"]`);
            }
            
            // Si pas de label avec for, chercher le label parent
            if (!label) {
                label = field.closest('label');
            }
            
            // Si pas de label parent, chercher le label précédent
            if (!label && field.previousElementSibling && field.previousElementSibling.tagName === 'LABEL') {
                label = field.previousElementSibling;
            }
            
            // Ajouter l'astérisque si le label existe et n'en a pas déjà
            if (label && !label.querySelector('.required-asterisk')) {
                const asterisk = document.createElement('span');
                asterisk.className = 'required-asterisk';
                asterisk.textContent = ' *';
                asterisk.style.color = '#ef4444';
                asterisk.style.fontWeight = 'bold';
                label.appendChild(asterisk);
            }
        });
    }

    // Initialiser la validation pour tous les formulaires
    function initFormValidation() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            // Ne pas valider les formulaires de recherche
            if (form.classList.contains('search-form') || form.id === 'search-form') {
                return;
            }
            
            new FormValidator(form);
        });
        
        // Ajouter les astérisques rouges
        addRequiredAsterisks();
    }

    // Initialiser au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFormValidation);
    } else {
        initFormValidation();
    }

    // Exposer pour réinitialisation manuelle si nécessaire
    window.FormValidation = {
        init: initFormValidation,
        FieldValidator: FieldValidator,
        FormValidator: FormValidator
    };
})();
