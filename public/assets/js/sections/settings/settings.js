document.addEventListener("DOMContentLoaded", function () {
  // ==================== GESTION DES ANCRES ====================
  // Ouvrir l'accordéon des fermetures exceptionnelles si on arrive avec l'ancre
  if (window.location.hash === '#closure-dates-section') {
    const closureSection = document.getElementById('closure-dates-content');
    const toggleBtn = document.querySelector('[data-target="closure-dates-content"]');
    
    if (closureSection && toggleBtn) {
      // Ouvrir l'accordéon
      closureSection.classList.remove('collapsed');
      const icon = toggleBtn.querySelector('i');
      if (icon) icon.classList.remove('rotated');
      
      // Scroll vers la section après un petit délai pour que l'animation se fasse
      setTimeout(() => {
        closureSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 100);
    }
  }

  // ==================== GESTION DU MENU MOBILE ====================
  const menuToggle = document.querySelector(".settings-mobile-toggle");
  const menuContent = document.getElementById("settings-mobile-content");

  if (menuToggle && menuContent) {
    menuToggle.addEventListener("click", function () {
      const isExpanded = this.getAttribute("aria-expanded") === "true";
      this.setAttribute("aria-expanded", !isExpanded);
      menuContent.classList.toggle("show");

      // Changer l'icône
      const menuIcon = this.querySelector(".settings-menu-icon");
      if (menuIcon) {
        menuIcon.textContent = isExpanded ? "☰" : "✕";
      }
    });

    // Fermer le menu quand on clique sur un lien
    menuContent.addEventListener("click", function (event) {
      if (event.target.tagName === "A") {
        menuToggle.setAttribute("aria-expanded", "false");
        this.classList.remove("show");

        const menuIcon = menuToggle.querySelector(".settings-menu-icon");
        if (menuIcon) {
          menuIcon.textContent = "☰";
        }

        // Mettre à jour le texte du bouton avec la section sélectionnée
        const linkText = event.target.textContent;
        const menuText = menuToggle.querySelector(".settings-menu-text");
        if (menuText) {
          menuText.textContent = linkText;
        }
      }
    });

    // Fermer le menu en cliquant à l'extérieur
    document.addEventListener("click", function (event) {
      if (
        !menuToggle.contains(event.target) &&
        !menuContent.contains(event.target)
      ) {
        menuToggle.setAttribute("aria-expanded", "false");
        menuContent.classList.remove("show");

        const menuIcon = menuToggle.querySelector(".settings-menu-icon");
        if (menuIcon) {
          menuIcon.textContent = "☰";
        }
      }
    });
  }

  // ==================== GESTION DES OPTIONS ====================
  // Récupérer le token CSRF depuis l'attribut data
  const container = document.querySelector(".settings-container");
  const csrfToken = container ? container.dataset.csrfToken : "";

  // Charger les options actuelles seulement si on est dans la section options
  if (
    window.location.search.includes("section=options") ||
    document.querySelector("#options-form")
  ) {
    loadOptions();

    // Gérer les clics sur les boutons d'options
    document.querySelectorAll(".option-btn").forEach((button) => {
      button.addEventListener("click", function () {
        updateButtonState(this);
      });
    });

    // Sauvegarder toutes les options
    document
      .getElementById("save-all-options")
      ?.addEventListener("click", function() {
        saveAllOptions(this);
      });

    // Réinitialiser les options
    document
      .getElementById("reset-options")
      ?.addEventListener("click", function() {
        resetOptions(this);
      });
  }

  function updateButtonState(clickedButton) {
    // Trouver le groupe de boutons parent
    const buttons = clickedButton.parentElement.querySelectorAll(".option-btn");

    // Retirer la classe active de tous les boutons du groupe
    buttons.forEach((btn) => {
      btn.classList.remove("option-active");
    });

    // Ajouter la classe active au bouton cliqué
    clickedButton.classList.add("option-active");
  }

  function loadOptions() {
    // Charger les options depuis le serveur
    fetch("?page=settings&action=get-options")
      .then((response) => {
        if (!response.ok) {
          throw new Error("Erreur réseau");
        }
        return response.json();
      })
      .then((options) => {
        // Mettre à jour les boutons selon les options (uniquement pour les options standards)
        const standardOptions = ['site_online', 'mail_reminder', 'hide_dark_mode', 'hide_tour_button', 'email_notifications'];
        Object.keys(options).forEach((option) => {
          // Ignorer les options qui ne sont pas des boutons (comme closure_dates)
          if (!standardOptions.includes(option)) {
            return;
          }
          
          const button = document.querySelector(
            `.option-btn[data-option="${option}"][data-value="${options[option]}"]`,
          );
          if (button) {
            updateButtonState(button);
          }
        });

        // Le message d'erreur sera affiché côté PHP après redirection
      })
      .catch((error) => {
        // Le message d'erreur sera affiché côté PHP après redirection
      });
  }

  function saveAllOptions(button) {
    // Collecter toutes les options
    const options = {};
    const optionButtons = document.querySelectorAll(
      ".option-btn.option-active",
    );

    optionButtons.forEach((btn) => {
      options[btn.dataset.option] = btn.dataset.value;
    });

    // Créer un formulaire pour soumettre via AJAX
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "?page=settings&action=save-options-batch";

    const optionsInput = document.createElement("input");
    optionsInput.type = "hidden";
    optionsInput.name = "options";
    optionsInput.value = JSON.stringify(options);
    form.appendChild(optionsInput);

    const csrfInput = document.createElement("input");
    csrfInput.type = "hidden";
    csrfInput.name = "csrf_token";
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    // Bouton submit pour que ajaxSubmit puisse le trouver
    const submitBtn = button || document.createElement("button");
    if (!button) {
      submitBtn.type = "submit";
      form.appendChild(submitBtn);
    }

    document.body.appendChild(form);
    ajaxSubmit(form, form.action, {
      onSuccess: function() {
        applyButtonVisibility(options);
      }
    });
    setTimeout(function() { form.remove(); }, 100);
  }

  function resetOptions(button) {
    // Réinitialiser visuellement (sans confirmation)
    document
      .querySelectorAll('.option-btn[data-value="1"]')
      .forEach((btn) => {
        updateButtonState(btn);
      });

    // Préparer les valeurs par défaut
    const defaultOptions = {
      site_online: "1",
      mail_reminder: "0",
      hide_dark_mode: "0",
      hide_tour_button: "0",
      email_notifications: "1",
    };

    // Créer un formulaire pour soumettre les données
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "?page=settings&action=save-options-batch";

    // Ajouter les données
    const optionsInput = document.createElement("input");
    optionsInput.type = "hidden";
    optionsInput.name = "options";
    optionsInput.value = JSON.stringify(defaultOptions);
    form.appendChild(optionsInput);

    const csrfInput = document.createElement("input");
    csrfInput.type = "hidden";
    csrfInput.name = "csrf_token";
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    const submitBtn = button || document.createElement("button");
    if (!button) {
      submitBtn.type = "submit";
      form.appendChild(submitBtn);
    }

    document.body.appendChild(form);
    ajaxSubmit(form, form.action, {
      onSuccess: function() {
        applyButtonVisibility(defaultOptions);
      }
    });
    setTimeout(function() { form.remove(); }, 100);
  }

  function applyButtonVisibility(options) {
    const darkModeBtn = document.getElementById('dark-mode-toggle');
    const tourBtn = document.getElementById('tour-toggle');

    if (darkModeBtn) {
      darkModeBtn.style.display = (options.hide_dark_mode === '1') ? 'none' : '';
    }

    if (tourBtn) {
      tourBtn.style.display = (options.hide_tour_button === '1') ? 'none' : '';
    }
  }

  // ==================== VALIDATION DU MOT DE PASSE ====================
  if (document.getElementById("password-change-form")) {
    const passwordForm = document.getElementById("password-change-form");
    const currentPasswordInput = document.getElementById("current_password");
    const newPasswordInput = document.getElementById("new_password");
    const confirmPasswordInput = document.getElementById("confirm_password");
    const passwordRequirements = document.querySelectorAll(".requirement");
    const strengthBar = document.getElementById("strength-bar");
    const strengthText = document.getElementById("strength-text");
    const matchError = document.getElementById("password-match-error");
    const matchSuccess = document.getElementById("password-match-success");
    const submitBtn = document.getElementById("submit-password");
    const toggleButtons = document.querySelectorAll(".password-toggle-btn");
    const newPasswordError = document.getElementById("new_password_error");

    // Réinitialiser l'état initial du bouton
    submitBtn.disabled = true;
    submitBtn.classList.add("btn-disabled");
    submitBtn.classList.remove("btn-enabled");
    submitBtn.innerHTML = "Modifier le mot de passe";

    // Fonction pour basculer l'affichage du mot de passe
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

    // Fonction pour afficher une erreur sur le nouveau mot de passe
    function showNewPasswordError(message) {
      if (newPasswordError) {
        newPasswordError.textContent = message;
        newPasswordError.style.display = "block";
        newPasswordError.style.color = "#f44336";
      }
    }

    // Fonction pour effacer l'erreur du nouveau mot de passe
    function clearNewPasswordError() {
      if (newPasswordError) {
        newPasswordError.textContent = "";
        newPasswordError.style.display = "none";
      }
    }

    // Fonction pour vérifier si le nouveau mot de passe est différent de l'ancien
    function checkPasswordDifferent() {
      const currentPassword = currentPasswordInput.value;
      const newPassword = newPasswordInput.value;

      if (!currentPassword || !newPassword) {
        clearNewPasswordError();
        return true; // Si un champ est vide, on ne vérifie pas encore
      }

      if (currentPassword === newPassword) {
        showNewPasswordError("Le nouveau mot de passe doit être différent de l'actuel");
        return false;
      } else {
        clearNewPasswordError();
        return true;
      }
    }

    // Fonction pour mettre à jour l'état du bouton
    function updateSubmitButton() {
      const currentPassword = currentPasswordInput.value;
      const newPassword = newPasswordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      // Vérifier que tous les champs sont remplis
      const allFieldsFilled = currentPassword && newPassword && confirmPassword;

      if (allFieldsFilled) {
        // Vérifier que le nouveau mot de passe est différent de l'actuel
        const isDifferent = checkPasswordDifferent();
        
        // Vérifier les exigences et la correspondance
        const allRequirementsMet = updatePasswordRequirements(newPassword);
        const passwordsMatch = checkPasswordMatch();

        if (isDifferent && allRequirementsMet && passwordsMatch) {
          submitBtn.disabled = false;
          submitBtn.classList.remove("btn-disabled");
          submitBtn.classList.add("btn-enabled");
          return;
        }
      }

      // Désactiver le bouton dans tous les autres cas
      submitBtn.disabled = true;
      submitBtn.classList.remove("btn-enabled");
      submitBtn.classList.add("btn-disabled");
    }

    // Fonction pour gérer les changements de mot de passe
    function handlePasswordInput() {
      const password = newPasswordInput.value;

      if (password) {
        const strength = calculatePasswordStrength(password);
        updateStrengthBar(strength);
      } else {
        // Réinitialiser la force si le champ est vide
        if (strengthBar) {
          strengthBar.style.width = "0";
          strengthBar.style.backgroundColor = "";
        }
        if (strengthText) {
          strengthText.textContent = "Force : faible";
          strengthText.style.color = "";
        }
      }

      // Vérifier si le nouveau mot de passe est différent de l'actuel
      checkPasswordDifferent();
      
      updatePasswordRequirements(password);
      updateSubmitButton();
    }

    // Fonction pour gérer les changements du mot de passe actuel
    function handleCurrentPasswordInput() {
      // Vérifier si le nouveau mot de passe est différent de l'actuel
      checkPasswordDifferent();
      updateSubmitButton();
    }

    // Événements pour la validation en temps réel
    newPasswordInput.addEventListener("input", handlePasswordInput);
    
    confirmPasswordInput.addEventListener("input", function() {
      checkPasswordMatch();
      updateSubmitButton();
    });

    currentPasswordInput.addEventListener("input", handleCurrentPasswordInput);

    // Validation à la soumission
    passwordForm.addEventListener("submit", function (event) {
      const currentPassword = currentPasswordInput.value;
      const newPassword = newPasswordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      // Vérifier que tous les champs sont remplis
      if (!currentPassword || !newPassword || !confirmPassword) {
        event.preventDefault();
        alert("Veuillez remplir tous les champs.");
        return;
      }

      // Vérifier que le nouveau mot de passe est différent de l'actuel
      if (currentPassword === newPassword) {
        event.preventDefault();
        showNewPasswordError("Le nouveau mot de passe doit être différent de l'actuel");
        newPasswordInput.focus();
        return;
      }

      // Vérifier la correspondance
      if (newPassword !== confirmPassword) {
        event.preventDefault();
        if (matchError) matchError.style.display = "block";
        if (matchSuccess) matchSuccess.style.display = "none";
        confirmPasswordInput.focus();
        return;
      }

      // Vérifier toutes les exigences
      const allValid = updatePasswordRequirements(newPassword);
      if (!allValid) {
        event.preventDefault();
        alert(
          "Veuillez respecter toutes les exigences de sécurité pour votre mot de passe.",
        );
        newPasswordInput.focus();
        return;
      }

      // Désactiver le bouton pendant la soumission
      submitBtn.disabled = true;
    });

    // Initialiser l'état des exigences
    updatePasswordRequirements("");
  }

  // ==================== FONCTIONS UTILITAIRES ====================
  // Gestion du délai de défilement pour les boutons haut/bas
  // (assurez-vous que scroll-buttons.js est inclus)

  // ==================== GESTION DES ACCORDÉONS OPTIONS ====================
  const expandOptionsBtn = document.getElementById("expand-options-accordions");
  const collapseOptionsBtn = document.getElementById("collapse-options-accordions");

  if (expandOptionsBtn && collapseOptionsBtn) {
    expandOptionsBtn.addEventListener("click", function () {
      // Ouvrir tous les accordéons dans la section options
      const optionsAccordions = document.querySelectorAll("#options-section .accordion-content");
      optionsAccordions.forEach(function (content) {
        content.classList.remove("collapsed");
        content.classList.add("expanded");
        
        // Mettre à jour les boutons toggle
        const toggle = content.previousElementSibling?.querySelector(".accordion-toggle");
        if (toggle) {
          const icon = toggle.querySelector("i");
          if (icon) {
            icon.classList.remove('rotated');
          }
        }
      });
    });

    collapseOptionsBtn.addEventListener("click", function () {
      // Fermer tous les accordéons dans la section options
      const optionsAccordions = document.querySelectorAll("#options-section .accordion-content");
      optionsAccordions.forEach(function (content) {
        content.classList.remove("expanded");
        content.classList.add("collapsed");
        
        // Mettre à jour les boutons toggle
        const toggle = content.previousElementSibling?.querySelector(".accordion-toggle");
        if (toggle) {
          const icon = toggle.querySelector("i");
          if (icon) {
            icon.classList.add('rotated');
          }
        }
      });
    });
  }

  // Vérifier si scroll-buttons.js est chargé
  if (typeof window.scrollToWithDelay === "undefined") {
    // Définir une fonction de repli si scroll-buttons.js n'est pas chargé
    window.scrollToWithDelay = function (targetY, duration = 1500) {
      const startY = window.scrollY;
      const distance = targetY - startY;
      let startTime = null;

      function animation(currentTime) {
        if (startTime === null) startTime = currentTime;
        const timeElapsed = currentTime - startTime;
        const run = ease(timeElapsed, startY, distance, duration);
        window.scrollTo(0, run);
        if (timeElapsed < duration) requestAnimationFrame(animation);
      }

      function ease(t, b, c, d) {
        t /= d / 2;
        if (t < 1) return (c / 2) * t * t + b;
        t--;
        return (-c / 2) * (t * (t - 2) - 1) + b;
      }

      requestAnimationFrame(animation);
    };
  }
});