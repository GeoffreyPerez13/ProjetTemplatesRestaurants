document.addEventListener("DOMContentLoaded", function () {
    // =============== FAIRE DISPARAÎTRE LES MESSAGES APRÈS 5s ===============
    const errorMessage = document.querySelector('.message-error');
    if (errorMessage) {
        setTimeout(() => {
            errorMessage.remove();
        }, 4000);
    }
    
    const successMessage = document.querySelector('.message-success');
    if (successMessage) {
        setTimeout(() => {
            successMessage.remove();
        }, 4000);
    }
    
    // =============== GESTION DES MOTS DE PASSE ===============
    const passwordToggleButtons = document.querySelectorAll('.password-toggle-btn');
    
    passwordToggleButtons.forEach(button => {
        button.addEventListener('click', function () {
            const passwordInput = this.parentNode.querySelector('.password-input-with-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="fa-regular fa-eye-slash"></i>';
                this.classList.add('showing');
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="fa-regular fa-eye"></i>';
                this.classList.remove('showing');
            }
            passwordInput.focus();
        });
    });
    
    // =============== VALIDATION AVANCÉE DU MOT DE PASSE ===============
    const passwordInput = document.querySelector('#password');
    const confirmPasswordInput = document.querySelector('#confirm_password');
    const passwordRequirements = document.querySelectorAll('.requirement');
    const strengthBar = document.querySelector('.strength-bar');
    const matchError = document.querySelector('#password-match-error');
    const matchSuccess = document.querySelector('#password-match-success');
    const submitBtn = document.querySelector('#submit-btn');
    
    // Fonction pour vérifier une exigence spécifique
    function checkPasswordRequirement(password, requirement) {
        switch (requirement) {
            case 'length':
                return password.length >= 8;
            case 'letter':
                return /[a-zA-Z]/.test(password);
            case 'uppercase':
                return /[A-Z]/.test(password);
            case 'number':
                return /\d/.test(password);
            case 'special':
                return /[^a-zA-Z0-9]/.test(password);
            default:
                return false;
        }
    }
    
    // Fonction pour calculer la force du mot de passe
    function calculatePasswordStrength(password) {
        let strength = 0;
        
        // Longueur
        if (password.length >= 8) strength += 20;
        if (password.length >= 12) strength += 10;
        
        // Diversité des caractères
        if (/[a-z]/.test(password)) strength += 10;
        if (/[A-Z]/.test(password)) strength += 15;
        if (/\d/.test(password)) strength += 15;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 20;
        
        // Variations
        if (password.length >= 8 && /[a-zA-Z]/.test(password) && /\d/.test(password)) strength += 10;
        if (password.length >= 10 && /[A-Z]/.test(password) && /[^a-zA-Z0-9]/.test(password)) strength += 10;
        
        return Math.min(strength, 100);
    }
    
    // Fonction pour mettre à jour l'affichage de la force
    function updateStrengthBar(strength) {
        if (strength < 30) {
            strengthBar.style.width = strength + '%';
            strengthBar.style.backgroundColor = '#f44336'; // Rouge
        } else if (strength < 70) {
            strengthBar.style.width = strength + '%';
            strengthBar.style.backgroundColor = '#ff9800'; // Orange
        } else {
            strengthBar.style.width = strength + '%';
            strengthBar.style.backgroundColor = '#4CAF50'; // Vert
        }
    }
    
    // Fonction pour mettre à jour les exigences
    function updatePasswordRequirements(password) {
        let allValid = true;
        
        passwordRequirements.forEach(req => {
            const requirement = req.getAttribute('data-requirement');
            const isValid = checkPasswordRequirement(password, requirement);
            
            if (isValid) {
                req.classList.add('valid');
                req.classList.remove('invalid');
                req.querySelector('i').className = 'fa-solid fa-check-circle';
                req.querySelector('i').style.color = '#4CAF50';
            } else {
                req.classList.add('invalid');
                req.classList.remove('valid');
                req.querySelector('i').className = 'fa-solid fa-times-circle';
                req.querySelector('i').style.color = '#f44336';
                allValid = false;
            }
        });
        
        return allValid;
    }
    
    // Fonction pour vérifier la correspondance des mots de passe
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (!confirmPassword) {
            matchError.style.display = 'none';
            matchSuccess.style.display = 'none';
            return false;
        }
        
        if (password === confirmPassword) {
            matchError.style.display = 'none';
            matchSuccess.style.display = 'block';
            confirmPasswordInput.style.borderColor = '#4CAF50';
            confirmPasswordInput.parentNode.parentNode.querySelector('.password-toggle-btn').style.borderColor = '#4CAF50';
            return true;
        } else {
            matchError.style.display = 'block';
            matchSuccess.style.display = 'none';
            confirmPasswordInput.style.borderColor = '#f44336';
            confirmPasswordInput.parentNode.parentNode.querySelector('.password-toggle-btn').style.borderColor = '#f44336';
            return false;
        }
    }
    
    // Événements pour la validation en temps réel
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            // Mettre à jour la force
            updateStrengthBar(strength);
            
            // Mettre à jour les exigences
            updatePasswordRequirements(password);
            
            // Vérifier la correspondance
            checkPasswordMatch();
            
            // Mettre à jour l'état du bouton
            updateSubmitButton();
        });
        
        // Style initial
        passwordInput.addEventListener('focus', function() {
            this.style.borderColor = '#2196F3';
            this.parentNode.parentNode.querySelector('.password-toggle-btn').style.borderColor = '#2196F3';
        });
        
        passwordInput.addEventListener('blur', function() {
            if (!this.value) {
                this.style.borderColor = '';
                this.parentNode.parentNode.querySelector('.password-toggle-btn').style.borderColor = '';
            }
        });
    }
    
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            checkPasswordMatch();
            updateSubmitButton();
        });
        
        confirmPasswordInput.addEventListener('focus', function() {
            this.style.borderColor = '#2196F3';
            this.parentNode.parentNode.querySelector('.password-toggle-btn').style.borderColor = '#2196F3';
        });
        
        confirmPasswordInput.addEventListener('blur', function() {
            if (!this.value) {
                this.style.borderColor = '';
                this.parentNode.parentNode.querySelector('.password-toggle-btn').style.borderColor = '';
                matchError.style.display = 'none';
                matchSuccess.style.display = 'none';
            }
        });
    }
    
    // Fonction pour mettre à jour l'état du bouton de soumission
    function updateSubmitButton() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const allRequirementsMet = updatePasswordRequirements(password);
        const passwordsMatch = checkPasswordMatch();
        
        if (password && confirmPassword && allRequirementsMet && passwordsMatch) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-disabled');
            submitBtn.classList.add('btn-enabled');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.remove('btn-enabled');
            submitBtn.classList.add('btn-disabled');
        }
    }
    
    // Validation à la soumission
    const form = document.querySelector('#register-form');
    if (form) {
        form.addEventListener('submit', function (event) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            // Vérifier la correspondance
            if (password !== confirmPassword) {
                event.preventDefault();
                matchError.style.display = 'block';
                matchSuccess.style.display = 'none';
                confirmPasswordInput.focus();
                return;
            }
            
            // Vérifier toutes les exigences
            const allValid = updatePasswordRequirements(password);
            if (!allValid) {
                event.preventDefault();
                alert("Veuillez respecter toutes les exigences de sécurité pour votre mot de passe.");
                passwordInput.focus();
                return;
            }
            
            // Afficher l'animation de chargement
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Création en cours...';
        });
    }
    
    // Initialiser l'état du bouton
    updateSubmitButton();
});