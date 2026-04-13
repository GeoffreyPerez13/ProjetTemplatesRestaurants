/**
 * Gestion centralisée des messages flash (succès/erreur)
 * Auto-dismiss après un délai configurable, avec bouton de fermeture
 * Inclus globalement via le footer admin — ne pas dupliquer dans les JS de section
 */
(function () {
  "use strict";

  // Délai par défaut (ms) avant disparition automatique
  var DEFAULT_DELAY = 2500;

  function initFlashMessages() {
    var messages = document.querySelectorAll(
      ".message-success, .message-error"
    );

    if (!messages.length) return;

    // Récupérer le délai configuré côté PHP (via scrollParams) ou utiliser le défaut
    var delay =
      (window.scrollParams && window.scrollParams.scrollDelay) || DEFAULT_DELAY;

    messages.forEach(function (message) {
      // Bouton de fermeture
      var closeBtn = document.createElement("button");
      closeBtn.innerHTML = '<i class="fas fa-times"></i>';
      closeBtn.className = "flash-close-btn";
      closeBtn.setAttribute("aria-label", "Fermer");
      closeBtn.addEventListener("click", function () {
        dismissMessage(message);
      });
      message.style.position = "relative";
      message.appendChild(closeBtn);

      // Auto-dismiss après le délai
      var timer = setTimeout(function () {
        dismissMessage(message);
      }, delay);

      // Si l'utilisateur ferme manuellement, annuler le timer
      closeBtn.addEventListener("click", function () {
        clearTimeout(timer);
      });
    });
  }

  function dismissMessage(el) {
    if (!el || el.classList.contains("dismissing")) return;
    el.classList.add("dismissing");
    el.style.opacity = "0";
    el.style.transition = "opacity 0.5s ease";
    setTimeout(function () {
      if (el.parentNode) el.parentNode.removeChild(el);
    }, 500);
  }

  // Exposer globalement pour les pages qui en ont besoin
  window.FlashMessages = { init: initFlashMessages, dismiss: dismissMessage };

  // Init au chargement
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initFlashMessages);
  } else {
    initFlashMessages();
  }
})();
