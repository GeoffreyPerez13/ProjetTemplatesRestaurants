/**
 * Utilitaire global de notifications toast pour le panel admin.
 * Utilise SweetAlert2 si disponible, sinon fallback DOM.
 */
var ToastMixin = (typeof Swal !== "undefined") ? Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
}) : null;

// Au chargement, afficher un toast stocké en sessionStorage (après reload)
document.addEventListener("DOMContentLoaded", function () {
  var pending = sessionStorage.getItem("pendingToast");
  if (pending) {
    sessionStorage.removeItem("pendingToast");
    try {
      var data = JSON.parse(pending);
      showToast(data.message, data.type);
    } catch (e) {}
  }
});

function showToast(message, type) {
  type = type || "info";

  if (ToastMixin) {
    ToastMixin.fire({
      icon: type === "error" ? "error" : "success",
      title: message,
    });
    return;
  }

  // Fallback DOM
  var toast = document.createElement("div");
  toast.className = "toast-message toast-" + type;
  toast.textContent = message;
  toast.style.cssText =
    "position:fixed;top:20px;right:20px;padding:14px 20px;border-radius:8px;color:#fff;font-size:0.9rem;z-index:99999;transition:opacity 0.3s;max-width:400px;box-shadow:0 4px 12px rgba(0,0,0,0.15);";
  toast.style.backgroundColor = type === "error" ? "#ef4444" : "#10b981";
  document.body.appendChild(toast);
  setTimeout(function () {
    toast.style.opacity = "0";
    setTimeout(function () { toast.remove(); }, 300);
  }, 3000);
}

/**
 * Soumet un formulaire en AJAX et affiche un toast avec le résultat.
 * @param {HTMLFormElement} form - Le formulaire à soumettre
 * @param {string} url - L'URL de destination (ex: "?page=save-contact")
 * @param {object} options - Options supplémentaires
 * @param {Function} options.onSuccess - Callback après succès
 * @param {Function} options.onError - Callback après erreur
 * @param {Function} options.beforeSend - Callback avant envoi (peut modifier FormData)
 */
function ajaxSubmit(form, url, options) {
  options = options || {};

  var formData = new FormData(form);

  if (typeof options.beforeSend === "function") {
    options.beforeSend(formData);
  }

  // Désactiver le bouton submit pendant l'envoi
  var submitBtn = form.querySelector('button[type="submit"]');
  var originalText = "";
  if (submitBtn) {
    originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
  }

  fetch(url, {
    method: "POST",
    headers: { "X-Requested-With": "XMLHttpRequest" },
    body: formData,
  })
    .then(function (response) { return response.json(); })
    .then(function (data) {
      if (data.success) {
        if (data.reload) {
          sessionStorage.setItem("pendingToast", JSON.stringify({
            message: data.message || "Enregistré avec succès.",
            type: "success"
          }));
          location.reload();
        } else {
          showToast(data.message || "Enregistré avec succès.", "success");
        }
        if (typeof options.onSuccess === "function") {
          options.onSuccess(data);
        }
      } else {
        showToast(data.message || "Une erreur est survenue.", "error");
        if (typeof options.onError === "function") {
          options.onError(data);
        }
      }
    })
    .catch(function (error) {
      console.error("Erreur AJAX:", error);
      showToast("Erreur de communication avec le serveur.", "error");
    })
    .finally(function () {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    });
}
