/**
 * Réservations en ligne — JavaScript Admin
 */
document.addEventListener("DOMContentLoaded", function () {
  "use strict";

  const csrfToken =
    document.getElementById("csrf-token")?.value ||
    document.querySelector('input[name="csrf_token"]')?.value ||
    "";

  // ==================== ONGLETS ====================

  document.querySelectorAll(".tab-btn").forEach(function (btn) {
    btn.addEventListener("click", function () {
      // Désactiver tous les onglets
      document
        .querySelectorAll(".tab-btn")
        .forEach(function (b) { b.classList.remove("active"); });
      document
        .querySelectorAll(".tab-content")
        .forEach(function (c) { c.classList.remove("active"); });

      // Activer l'onglet cliqué
      btn.classList.add("active");
      var target = document.getElementById(btn.dataset.tab);
      if (target) target.classList.add("active");
    });
  });

  // ==================== ACTIONS SUR LES RÉSERVATIONS ====================

  function updateReservationStatus(id, status, extra) {
    extra = extra || {};
    var data = new FormData();
    data.append("csrf_token", csrfToken);
    data.append("id", id);
    data.append("status", status);

    if (extra.cancelled_reason) {
      data.append("cancelled_reason", extra.cancelled_reason);
    }
    if (extra.admin_notes) {
      data.append("admin_notes", extra.admin_notes);
    }

    fetch("?page=reservation-update-status", {
      method: "POST",
      body: data,
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d.success) {
          showToast(d.message, "success");
          setTimeout(function () { location.reload(); }, 800);
        } else {
          showToast(d.message || "Erreur", "error");
        }
      })
      .catch(function () {
        showToast("Erreur de communication avec le serveur.", "error");
      });
  }

  function deleteReservation(id) {
    var data = new FormData();
    data.append("csrf_token", csrfToken);
    data.append("id", id);

    fetch("?page=reservation-delete", {
      method: "POST",
      body: data,
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d.success) {
          showToast(d.message, "success");
          setTimeout(function () { location.reload(); }, 800);
        } else {
          showToast(d.message || "Erreur", "error");
        }
      })
      .catch(function () {
        showToast("Erreur de communication avec le serveur.", "error");
      });
  }

  // Confirmer
  document.querySelectorAll(".btn-confirm-reservation").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var id = btn.dataset.id;
      Swal.fire({
        title: "Confirmer la réservation ?",
        text: "Le client sera considéré comme attendu.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#10b981",
        cancelButtonColor: "#6b7280",
        confirmButtonText: '<i class="fas fa-check"></i> Confirmer',
        cancelButtonText: "Annuler"
      }).then(function (result) {
        if (result.isConfirmed) {
          updateReservationStatus(id, "confirmed");
        }
      });
    });
  });

  // Annuler/Refuser
  document.querySelectorAll(".btn-cancel-reservation").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var id = btn.dataset.id;
      Swal.fire({
        title: "Refuser cette réservation ?",
        text: "Vous pouvez indiquer une raison (optionnel).",
        icon: "warning",
        input: "text",
        inputPlaceholder: "Raison de l'annulation...",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#6b7280",
        confirmButtonText: '<i class="fas fa-times"></i> Refuser',
        cancelButtonText: "Retour",
        inputValidator: function () { return null; }
      }).then(function (result) {
        if (result.isConfirmed) {
          updateReservationStatus(id, "cancelled", { cancelled_reason: result.value || "" });
        }
      });
    });
  });

  // Terminée
  document.querySelectorAll(".btn-complete-reservation").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var id = btn.dataset.id;
      Swal.fire({
        title: "Marquer comme terminée ?",
        text: "La réservation sera archivée.",
        icon: "info",
        showCancelButton: true,
        confirmButtonColor: "#6366f1",
        cancelButtonColor: "#6b7280",
        confirmButtonText: '<i class="fas fa-flag-checkered"></i> Terminée',
        cancelButtonText: "Annuler"
      }).then(function (result) {
        if (result.isConfirmed) {
          updateReservationStatus(id, "completed");
        }
      });
    });
  });

  // Absent (no show)
  document.querySelectorAll(".btn-noshow-reservation").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var id = btn.dataset.id;
      Swal.fire({
        title: "Client absent ?",
        text: "Cette réservation sera marquée comme non honorée.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#6b7280",
        confirmButtonText: '<i class="fas fa-user-slash"></i> Absent',
        cancelButtonText: "Annuler"
      }).then(function (result) {
        if (result.isConfirmed) {
          updateReservationStatus(id, "no_show");
        }
      });
    });
  });

  // Supprimer
  document.querySelectorAll(".btn-delete-reservation").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var id = btn.dataset.id;
      Swal.fire({
        title: "Supprimer cette réservation ?",
        text: "Cette action est irréversible.",
        icon: "error",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#6b7280",
        confirmButtonText: '<i class="fas fa-trash"></i> Supprimer',
        cancelButtonText: "Annuler"
      }).then(function (result) {
        if (result.isConfirmed) {
          deleteReservation(id);
        }
      });
    });
  });

  // ==================== PARAMÈTRES ====================

  var settingsForm = document.getElementById("reservation-settings-form");
  if (settingsForm) {
    settingsForm.addEventListener("submit", function (e) {
      e.preventDefault();

      var formData = new FormData(settingsForm);

      // Gérer le checkbox booking_enabled
      if (!settingsForm.querySelector('input[name="booking_enabled"]').checked) {
        formData.set("booking_enabled", "0");
      }

      // Gérer les jours de fermeture (checkboxes multiples)
      var closedDays = [];
      settingsForm.querySelectorAll('input[name="closed_days[]"]:checked').forEach(function (cb) {
        closedDays.push(cb.value);
      });
      formData.set("booking_closed_days", closedDays.join(","));
      formData.delete("closed_days[]");

      fetch("?page=reservation-save-settings", {
        method: "POST",
        body: formData,
      })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d.success) {
            showToast(d.message, "success");
          } else {
            showToast(d.message || "Erreur", "error");
          }
        })
        .catch(function () {
          showToast("Erreur de communication avec le serveur.", "error");
        });
    });
  }

  // ==================== TOAST ====================

  function showToast(message, type) {
    type = type || "info";
    // Vérifier si SweetAlert2 est disponible
    if (typeof Swal !== "undefined") {
      Swal.fire({
        toast: true,
        position: "top-end",
        icon: type === "error" ? "error" : "success",
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
      });
      return;
    }

    // Fallback simple
    var toast = document.createElement("div");
    toast.className = "toast-message toast-" + type;
    toast.textContent = message;
    toast.style.cssText =
      "position:fixed;top:20px;right:20px;padding:14px 20px;border-radius:8px;color:#fff;font-size:0.9rem;z-index:99999;transition:opacity 0.3s;max-width:400px;box-shadow:0 4px 12px rgba(0,0,0,0.15);";
    toast.style.background = type === "error" ? "#ef4444" : "#10b981";
    document.body.appendChild(toast);
    setTimeout(function () {
      toast.style.opacity = "0";
      setTimeout(function () { toast.remove(); }, 300);
    }, 3000);
  }
});
