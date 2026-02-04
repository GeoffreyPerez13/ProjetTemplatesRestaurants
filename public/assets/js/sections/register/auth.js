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
    
    // =============== VALIDATION DU MOT DE PASSE EN TEMPS RÉEL ===============
    const passwordInput = document.querySelector('input[name="password"]');
    const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
    
    if (passwordInput && confirmPasswordInput) {
        // Validation visuelle
        function validatePasswords() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            // Retirer les classes précédentes
            passwordInput.classList.remove('password-match', 'password-mismatch');
            confirmPasswordInput.classList.remove('password-match', 'password-mismatch');
            
            // Vérifier si les champs ne sont pas vides
            if (password && confirmPassword) {
                if (password === confirmPassword) {
                    passwordInput.classList.add('password-match');
                    confirmPasswordInput.classList.add('password-match');
                    
                    // Appliquer la bordure verte aux deux champs
                    passwordInput.style.borderColor = '#4CAF50';
                    passwordInput.parentNode.parentNode.querySelector('.password-toggle-btn').style.borderColor = '#4CAF50';
                    confirmPasswordInput.style.borderColor = '#4CAF50';
                    confirmPasswordInput.parentNode.parentNode.querySelector('.password-toggle-btn').style.borderColor = '#4CAF50';
                } else {
                    passwordInput.classList.add('password-mismatch');
                    confirmPasswordInput.classList.add('password-mismatch');
                    
                    // Appliquer la bordure rouge aux deux champs
                    passwordInput.style.borderColor = '#f44336';
                    passwordInput.parentNode.parentNode.querySelector('.password-toggle-btn').style.borderColor = '#f44336';
                    confirmPasswordInput.style.borderColor = '#f44336';
                    confirmPasswordInput.parentNode.parentNode.querySelector('.password-toggle-btn').style.borderColor = '#f44336';
                }
            } else {
                // Réinitialiser les bordures si un champ est vide
                passwordInput.style.borderColor = '';
                passwordInput.parentNode.parentNode.querySelector('.password-toggle-btn').style.borderColor = '';
                confirmPasswordInput.style.borderColor = '';
                confirmPasswordInput.parentNode.parentNode.querySelector('.password-toggle-btn').style.borderColor = '';
            }
        }
        
        // Écouter les changements
        passwordInput.addEventListener('input', validatePasswords);
        confirmPasswordInput.addEventListener('input', validatePasswords);
        
        // Validation à la soumission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function (event) {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (password !== confirmPassword) {
                    event.preventDefault();
                    alert("Les mots de passe ne correspondent pas !");
                    passwordInput.focus();
                    return;
                }
                
                // Validation de la force du mot de passe
                if (password.length < 8) {
                    event.preventDefault();
                    alert("Le mot de passe doit contenir au moins 8 caractères !");
                    passwordInput.focus();
                    return;
                }
                
                // Validation supplémentaire : au moins une lettre et un chiffre
                const hasLetter = /[a-zA-Z]/.test(password);
                const hasNumber = /\d/.test(password);
                
                if (!hasLetter || !hasNumber) {
                    event.preventDefault();
                    alert("Le mot de passe doit contenir au moins une lettre et un chiffre !");
                    passwordInput.focus();
                }
            });
        }
    }
});