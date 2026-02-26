document.addEventListener("DOMContentLoaded", function () {
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