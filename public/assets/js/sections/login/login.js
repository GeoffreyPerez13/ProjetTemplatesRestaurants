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
        }, 4000); // 4 secondes
    }
    
    // =============== GESTION DES MOTS DE PASSE (VOTRE CODE) ===============
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    
    passwordInputs.forEach(input => {
        // Créer le conteneur
        const container = document.createElement('div');
        container.className = 'password-input-group';
        
        // Créer le wrapper pour l'input
        const inputWrapper = document.createElement('div');
        inputWrapper.className = 'password-input-wrapper';
        
        // Déplacer l'input dans le wrapper
        input.parentNode.insertBefore(container, input);
        input.classList.add('password-input-with-toggle');
        inputWrapper.appendChild(input);
        container.appendChild(inputWrapper);
        
        // Créer le bouton
        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.className = 'password-toggle-btn';
        toggleButton.innerHTML = '<i class="fa-regular fa-eye"></i>';
        toggleButton.setAttribute('aria-label', 'Afficher le mot de passe');
        
        container.appendChild(toggleButton);
        
        // Événement click
        toggleButton.addEventListener('click', function () {
            if (input.type === 'password') {
                input.type = 'text';
                toggleButton.innerHTML = '<i class="fa-regular fa-eye-slash"></i>';
                toggleButton.classList.add('showing');
            } else {
                input.type = 'password';
                toggleButton.innerHTML = '<i class="fa-regular fa-eye"></i>';
                toggleButton.classList.remove('showing');
            }
            input.focus();
        });
    });
});