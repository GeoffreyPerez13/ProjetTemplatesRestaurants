document.addEventListener("DOMContentLoaded", function () {
  // ========== AUTO-DISSIPATION DES MESSAGES ==========
  const messages = document.querySelectorAll('.message-success, .message-error');
  const scrollDelay = window.scrollParams?.scrollDelay || 1500;
  
  if (messages.length > 0) {
    // Faire défiler jusqu'au message si un anchor est défini
    const anchor = window.scrollParams?.anchor;
    if (anchor) {
      const targetElement = document.getElementById(anchor);
      if (targetElement) {
        targetElement.scrollIntoView({ behavior: 'smooth' });
      }
    }
    
    // Auto-dissipation après le délai
    setTimeout(function() {
      messages.forEach(function(message) {
        message.style.transition = 'opacity 0.5s ease';
        message.style.opacity = '0';
        
        setTimeout(function() {
          message.remove();
        }, 500);
      });
    }, scrollDelay);
  }

  // ========== GESTION DU FORMULAIRE ==========
  const form = document.querySelector(".invitation-form");
  const submitBtn = form.querySelector(".send-invitation-btn");

  form.addEventListener("submit", function () {
    // Ajouter un état de chargement
    submitBtn.classList.add("btn-loading");
    submitBtn.disabled = true;
    submitBtn.innerHTML =
      '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
  });

  // Validation en temps réel
  const emailInput = document.getElementById("email");
  const nameInput = document.getElementById("restaurant_name");

  function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }

  function updateValidationState(input, isValid) {
    const group = input.closest(".invitation-form-group");
    if (isValid) {
      group.classList.remove("has-error");
      group.classList.add("has-success");
    } else {
      group.classList.remove("has-success");
      group.classList.add("has-error");
    }
  }

  emailInput.addEventListener("blur", function () {
    updateValidationState(this, validateEmail(this.value));
  });

  nameInput.addEventListener("blur", function () {
    updateValidationState(this, this.value.trim().length >= 2);
  });

  // Tooltips d'aide
  const helpTexts = document.querySelectorAll(".help-text");
  helpTexts.forEach((text) => {
    text.style.cursor = "help";
    text.title = text.textContent;
  });
});