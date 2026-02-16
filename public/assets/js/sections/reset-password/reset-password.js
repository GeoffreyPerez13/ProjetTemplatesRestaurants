document.addEventListener("DOMContentLoaded", function () {
    // ==================== GESTION DES CHAMPS DE MOT DE PASSE ====================
    const newPasswordInput = document.getElementById("new_password");
    const confirmPasswordInput = document.getElementById("confirm_password");

    // Si on est sur la page avec les champs de mot de passe (étape 2)
    if (newPasswordInput && confirmPasswordInput) {
        const toggleButtons = document.querySelectorAll(".password-toggle-btn");
        const passwordRequirements = document.querySelectorAll(".requirement");
        const strengthBar = document.getElementById("strength-bar");
        const strengthText = document.getElementById("strength-text");
        const matchError = document.getElementById("password-match-error");
        const matchSuccess = document.getElementById("password-match-success");
        const submitBtn = document.querySelector(".reset-password-container .btn[type='submit']");

        // Initialiser les boutons de bascule (œil)
        toggleButtons.forEach((button) => {
            button.addEventListener("click", function () {
                const targetId = this.getAttribute("data-target");
                const targetInput = document.getElementById(targetId);

                if (targetInput.type === "password") {
                    targetInput.type = "text";
                    this.innerHTML = '<i class="fa-regular fa-eye-slash"></i>';
                    this.setAttribute("aria-label", "Masquer le mot de passe");
                } else {
                    targetInput.type = "password";
                    this.innerHTML = '<i class="fa-regular fa-eye"></i>';
                    this.setAttribute("aria-label", "Afficher le mot de passe");
                }
                targetInput.focus();
            });
        });

        // Fonction pour vérifier une exigence spécifique
        function checkPasswordRequirement(password, requirement) {
            switch (requirement) {
                case "length":
                    return password.length >= 8;
                case "letter":
                    return /[a-zA-Z]/.test(password);
                case "uppercase":
                    return /[A-Z]/.test(password);
                case "number":
                    return /\d/.test(password);
                case "special":
                    return /[^a-zA-Z0-9]/.test(password);
                default:
                    return false;
            }
        }

        // Fonction pour calculer la force du mot de passe
        function calculatePasswordStrength(password) {
            let strength = 0;

            if (password.length >= 8) strength += 20;
            if (password.length >= 12) strength += 10;

            if (/[a-z]/.test(password)) strength += 10;
            if (/[A-Z]/.test(password)) strength += 15;
            if (/\d/.test(password)) strength += 15;
            if (/[^a-zA-Z0-9]/.test(password)) strength += 20;

            if (
                password.length >= 8 &&
                /[a-zA-Z]/.test(password) &&
                /\d/.test(password)
            )
                strength += 10;
            if (
                password.length >= 10 &&
                /[A-Z]/.test(password) &&
                /[^a-zA-Z0-9]/.test(password)
            )
                strength += 10;

            return Math.min(strength, 100);
        }

        // Fonction pour mettre à jour l'affichage de la force
        function updateStrengthBar(strength) {
            if (!strengthBar) return;

            strengthBar.style.width = strength + "%";

            if (strength < 30) {
                strengthBar.style.backgroundColor = "#f44336";
                if (strengthText) {
                    strengthText.textContent = "Force : faible";
                    strengthText.style.color = "#f44336";
                }
            } else if (strength < 70) {
                strengthBar.style.backgroundColor = "#ff9800";
                if (strengthText) {
                    strengthText.textContent = "Force : moyenne";
                    strengthText.style.color = "#ff9800";
                }
            } else {
                strengthBar.style.backgroundColor = "#4CAF50";
                if (strengthText) {
                    strengthText.textContent = "Force : forte";
                    strengthText.style.color = "#4CAF50";
                }
            }
        }

        // Fonction pour mettre à jour les exigences
        function updatePasswordRequirements(password) {
            if (!password) {
                // Réinitialiser les exigences si le champ est vide
                passwordRequirements.forEach((req) => {
                    req.classList.remove("valid", "invalid");
                    const icon = req.querySelector("i");
                    if (icon) {
                        icon.className = "fa-solid fa-circle";
                        icon.style.color = "";
                    }
                });
                return false;
            }

            let allValid = true;

            passwordRequirements.forEach((req) => {
                const requirement = req.getAttribute("data-requirement");
                const isValid = checkPasswordRequirement(password, requirement);

                if (isValid) {
                    req.classList.add("valid");
                    req.classList.remove("invalid");
                    const icon = req.querySelector("i");
                    if (icon) {
                        icon.className = "fa-solid fa-check-circle";
                        icon.style.color = "#4CAF50";
                    }
                } else {
                    req.classList.add("invalid");
                    req.classList.remove("valid");
                    const icon = req.querySelector("i");
                    if (icon) {
                        icon.className = "fa-solid fa-times-circle";
                        icon.style.color = "#f44336";
                    }
                    allValid = false;
                }
            });

            return allValid;
        }

        // Fonction pour vérifier la correspondance des mots de passe
        function checkPasswordMatch() {
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (!newPassword || !confirmPassword) {
                if (matchError) matchError.style.display = "none";
                if (matchSuccess) matchSuccess.style.display = "none";
                return false;
            }

            if (newPassword === confirmPassword) {
                if (matchError) matchError.style.display = "none";
                if (matchSuccess) matchSuccess.style.display = "block";
                return true;
            } else {
                if (matchError) matchError.style.display = "block";
                if (matchSuccess) matchSuccess.style.display = "none";
                return false;
            }
        }

        // Fonction pour activer/désactiver le bouton de soumission
        function updateSubmitButton() {
            if (!submitBtn) return;

            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const allFieldsFilled = newPassword && confirmPassword;

            if (allFieldsFilled) {
                const allRequirementsMet = updatePasswordRequirements(newPassword);
                const passwordsMatch = checkPasswordMatch();

                if (allRequirementsMet && passwordsMatch) {
                    submitBtn.disabled = false;
                    return;
                }
            }

            submitBtn.disabled = true;
        }

        // Événements
        newPasswordInput.addEventListener("input", function () {
            const password = this.value;
            if (password) {
                const strength = calculatePasswordStrength(password);
                updateStrengthBar(strength);
            } else {
                // Réinitialiser
                if (strengthBar) {
                    strengthBar.style.width = "0";
                    strengthBar.style.backgroundColor = "";
                }
                if (strengthText) {
                    strengthText.textContent = "Force : faible";
                    strengthText.style.color = "";
                }
            }
            updatePasswordRequirements(password);
            updateSubmitButton();
        });

        confirmPasswordInput.addEventListener("input", function () {
            checkPasswordMatch();
            updateSubmitButton();
        });

        // Initialiser l'état
        updatePasswordRequirements("");
        if (submitBtn) submitBtn.disabled = true;
    }
});