/**
 * Réservations en ligne — JavaScript Admin
 */
document.addEventListener("DOMContentLoaded", function () {
  "use strict";

  const csrfToken =
    document.getElementById("csrf-token")?.value ||
    document.querySelector('input[name="csrf_token"]')?.value ||
    "";

  // ==================== GESTION AFFICHAGE ATTRIBUTION TABLE ====================

  // Désactiver/activer l'option d'attribution de table selon la validation automatique
  var autoConfirmCheckbox = document.getElementById('booking_auto_confirm');
  var assignTableSetting = document.getElementById('assign-table-setting');
  var assignTableCheckbox = document.querySelector('input[name="booking_assign_table"]');
  
  if (autoConfirmCheckbox && assignTableSetting) {
    autoConfirmCheckbox.addEventListener('change', function() {
      if (this.checked) {
        // Griser et désactiver le champ (incompatible avec validation automatique)
        assignTableSetting.classList.add('disabled');
        if (assignTableCheckbox) {
          assignTableCheckbox.checked = false;
          assignTableCheckbox.disabled = true;
        }
        
        // Afficher le message d'incompatibilité
        var existingMessage = document.getElementById('assign-table-disabled-message');
        if (!existingMessage) {
          var settingInfo = assignTableSetting.querySelector('.setting-info');
          var settingDesc = settingInfo.querySelector('.setting-desc');
          var message = document.createElement('p');
          message.className = 'setting-warning';
          message.id = 'assign-table-disabled-message';
          message.innerHTML = '<i class="fas fa-info-circle"></i> Cette option est incompatible avec la validation automatique. Désactivez d\'abord la validation automatique pour pouvoir l\'activer.';
          settingDesc.after(message);
        }
      } else {
        // Réactiver le champ
        assignTableSetting.classList.remove('disabled');
        if (assignTableCheckbox) {
          assignTableCheckbox.disabled = false;
        }
        
        // Masquer le message d'incompatibilité
        var message = document.getElementById('assign-table-disabled-message');
        if (message) {
          message.remove();
        }
      }
    });
  }

  // ==================== ONGLETS ====================

  // Fonction pour activer un onglet
  function activateTab(tabId) {
    // Désactiver tous les onglets
    document
      .querySelectorAll(".tab-btn")
      .forEach(function (b) { b.classList.remove("active"); });
    document
      .querySelectorAll(".tab-content")
      .forEach(function (c) { c.classList.remove("active"); });

    // Activer l'onglet spécifié
    var btn = document.querySelector('.tab-btn[data-tab="' + tabId + '"]');
    var target = document.getElementById(tabId);
    if (btn) btn.classList.add("active");
    if (target) target.classList.add("active");
  }

  // Gestion des clics sur les onglets
  document.querySelectorAll(".tab-btn").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var tabId = btn.dataset.tab;
      activateTab(tabId);
    });
  });

  // Détecter les paramètres de filtres dans l'URL au chargement
  var urlParams = new URLSearchParams(window.location.search);
  var hasFilters = urlParams.has('status') || urlParams.has('date') || urlParams.has('search');
  var hasTabParam = urlParams.get('tab') === 'list';
  
  // Si des filtres sont présents dans l'URL, ouvrir l'onglet liste
  // Sinon, toujours ouvrir le tableau de bord par défaut
  if (hasFilters || hasTabParam) {
    activateTab('tab-list');
  } else {
    activateTab('tab-dashboard');
  }

  // ==================== ACTIONS SUR LES RÉSERVATIONS ====================

  // Mappings pour mise à jour instantanée du DOM
  var statusLabels = {
    pending: 'En attente',
    confirmed: 'Confirmée',
    cancelled: 'Annulée',
    completed: 'Terminée',
    no_show: 'Absent'
  };
  var statusColors = {
    pending: 'warning',
    confirmed: 'success',
    cancelled: 'danger',
    completed: 'info',
    no_show: 'danger'
  };
  var statusIcons = {
    pending: 'fa-clock',
    confirmed: 'fa-check-circle',
    cancelled: 'fa-times-circle',
    completed: 'fa-flag-checkered',
    no_show: 'fa-user-slash'
  };

  // Mise à jour instantanée d'une ligne du tableau
  function updateRowInstantly(id, newStatus) {
    var row = document.querySelector('tr[data-id="' + id + '"]');
    if (!row) return;

    // Mettre à jour la classe de la ligne
    row.className = 'row-status-' + newStatus;

    // Mettre à jour le badge de statut
    var badge = row.querySelector('.status-badge');
    if (badge) {
      badge.className = 'status-badge badge-' + (statusColors[newStatus] || 'warning');
      badge.innerHTML = '<i class="fas ' + (statusIcons[newStatus] || 'fa-clock') + '"></i> ' + (statusLabels[newStatus] || newStatus);
    }

    // Mettre à jour les boutons d'action
    var actionsCell = row.querySelector('.actions-cell');
    if (actionsCell) {
      var dateVal = '';
      var timeVal = '';
      var editBtn = row.querySelector('.btn-edit-datetime');
      if (editBtn) {
        dateVal = editBtn.dataset.date || '';
        timeVal = editBtn.dataset.time || '';
      }

      var html = '<div class="actions-wrapper">';
      if (newStatus === 'confirmed') {
        html += '<button class="btn small btn-complete-reservation" data-id="' + id + '" title="Terminée"><i class="fas fa-flag-checkered"></i></button>';
        html += '<button class="btn small danger btn-noshow-reservation" data-id="' + id + '" title="Absent"><i class="fas fa-user-slash"></i></button>';
      }
      html += '<button class="btn small info btn-edit-datetime" data-id="' + id + '" data-date="' + dateVal + '" data-time="' + timeVal + '" title="Modifier date/heure"><i class="fas fa-calendar-alt"></i></button>';
      html += '<button class="btn small danger btn-delete-reservation" data-id="' + id + '" title="Supprimer"><i class="fas fa-trash"></i></button>';
      html += '</div>';
      actionsCell.innerHTML = html;
    }

    // Mettre à jour la detail-row adjacente si elle existe
    var nextRow = row.nextElementSibling;
    if (nextRow && nextRow.classList.contains('reservation-detail-row')) {
      nextRow.className = 'reservation-detail-row row-status-' + newStatus;
    }

    // Restaurer l'opacité
    row.style.opacity = '1';
    row.style.pointerEvents = '';
    row.style.transition = 'opacity 0.3s ease';
  }

  // Suppression instantanée d'une ligne du tableau avec animation
  function removeRowInstantly(id) {
    var row = document.querySelector('tr[data-id="' + id + '"]');
    if (!row) return;

    // Vérifier s'il y a une detail-row adjacente
    var nextRow = row.nextElementSibling;
    var detailRow = (nextRow && nextRow.classList.contains('reservation-detail-row')) ? nextRow : null;

    row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    row.style.opacity = '0';
    row.style.transform = 'translateX(-20px)';
    if (detailRow) {
      detailRow.style.transition = 'opacity 0.3s ease';
      detailRow.style.opacity = '0';
    }

    setTimeout(function() {
      row.remove();
      if (detailRow) detailRow.remove();
    }, 300);
  }

  function updateReservationStatus(id, status, extra) {
    extra = extra || {};
    
    // Ajouter un indicateur visuel sur la ligne immédiatement
    var row = document.querySelector('tr[data-id="' + id + '"]');
    if (row) {
      row.style.opacity = '0.5';
      row.style.pointerEvents = 'none';
      var actionsCell = row.querySelector('.actions-cell');
      if (actionsCell) {
        actionsCell.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size: 1.2em; color: var(--color-primary);"></i>';
      }
    }
    
    var data = new FormData();
    var currentCsrfToken = document.getElementById("csrf-token")?.value || 
                           document.querySelector('input[name="csrf_token"]')?.value || "";
    data.append("csrf_token", currentCsrfToken);
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
          if (d.new_csrf_token) {
            var csrfInput = document.getElementById("csrf-token");
            if (csrfInput) csrfInput.value = d.new_csrf_token;
          }
          showToast(d.message, "success");
          
          var dateInput = document.getElementById('dashboard-date');
          if (dateInput && typeof window.loadReservationsForDate === 'function') {
            window.loadReservationsForDate(dateInput.value);
          } else {
            // Mise à jour instantanée du DOM dans l'onglet liste
            updateRowInstantly(id, status);
          }
        } else {
          showToast(d.message || "Erreur", "error");
          // Restaurer la ligne en cas d'erreur
          if (row) {
            row.style.opacity = '1';
            row.style.pointerEvents = '';
          }
        }
      })
      .catch(function () {
        showToast("Erreur de communication avec le serveur.", "error");
        if (row) {
          row.style.opacity = '1';
          row.style.pointerEvents = '';
        }
      });
  }

  function deleteReservation(id) {
    // Ajouter un indicateur visuel sur la ligne immédiatement
    var row = document.querySelector('tr[data-id="' + id + '"]');
    if (row) {
      row.style.opacity = '0.5';
      row.style.pointerEvents = 'none';
      var actionsCell = row.querySelector('.actions-cell');
      if (actionsCell) {
        actionsCell.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size: 1.2em; color: var(--color-primary);"></i>';
      }
    }
    
    var data = new FormData();
    var currentCsrfToken = document.getElementById("csrf-token")?.value || 
                           document.querySelector('input[name="csrf_token"]')?.value || "";
    data.append("csrf_token", currentCsrfToken);
    data.append("id", id);

    fetch("?page=reservation-delete", {
      method: "POST",
      body: data,
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d.success) {
          if (d.new_csrf_token) {
            var csrfInput = document.getElementById("csrf-token");
            if (csrfInput) csrfInput.value = d.new_csrf_token;
          }
          showToast(d.message, "success");
          
          var dateInput = document.getElementById('dashboard-date');
          if (dateInput && typeof window.loadReservationsForDate === 'function') {
            window.loadReservationsForDate(dateInput.value);
          } else {
            // Suppression instantanée de la ligne avec animation
            removeRowInstantly(id);
          }
        } else {
          showToast(d.message || "Erreur", "error");
          if (row) {
            row.style.opacity = '1';
            row.style.pointerEvents = '';
          }
        }
      })
      .catch(function () {
        showToast("Erreur de communication avec le serveur.", "error");
        if (row) {
          row.style.opacity = '1';
          row.style.pointerEvents = '';
        }
      });
  }

  // Confirmer - Délégation d'événements
  document.addEventListener("click", function(e) {
    if (e.target.closest(".btn-confirm-reservation")) {
      var btn = e.target.closest(".btn-confirm-reservation");
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
    }
  });

  // Annuler/Refuser - Délégation d'événements
  document.addEventListener("click", function(e) {
    if (e.target.closest(".btn-cancel-reservation")) {
      var btn = e.target.closest(".btn-cancel-reservation");
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
    }
  });

  // Terminée - Délégation d'événements
  document.addEventListener("click", function(e) {
    if (e.target.closest(".btn-complete-reservation")) {
      var btn = e.target.closest(".btn-complete-reservation");
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
    }
  });

  // Absent (no show) - Délégation d'événements
  document.addEventListener("click", function(e) {
    if (e.target.closest(".btn-noshow-reservation")) {
      var btn = e.target.closest(".btn-noshow-reservation");
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
    }
  });

  // Supprimer - Utiliser la délégation d'événements pour que ça fonctionne après rechargement
  document.addEventListener("click", function(e) {
    if (e.target.closest(".btn-delete-reservation")) {
      var btn = e.target.closest(".btn-delete-reservation");
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
    }
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

      // Gérer le checkbox booking_auto_confirm
      var autoConfirmCheckbox = settingsForm.querySelector('input[name="booking_auto_confirm"]');
      if (autoConfirmCheckbox && !autoConfirmCheckbox.checked) {
        formData.set("booking_auto_confirm", "0");
      }

      // Gérer le checkbox booking_auto_complete
      var autoCompleteCheckbox = settingsForm.querySelector('input[name="booking_auto_complete"]');
      if (autoCompleteCheckbox && !autoCompleteCheckbox.checked) {
        formData.set("booking_auto_complete", "0");
      }

      // Gérer le checkbox booking_assign_table
      var assignTableCheckbox = settingsForm.querySelector('input[name="booking_assign_table"]');
      if (assignTableCheckbox && !assignTableCheckbox.checked) {
        formData.set("booking_assign_table", "0");
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
            
            // Mettre à jour le token CSRF si le serveur en renvoie un nouveau
            if (d.new_csrf_token) {
              // Mettre à jour le token dans le formulaire de paramètres
              var csrfInput = settingsForm.querySelector('input[name="csrf_token"]');
              if (csrfInput) {
                csrfInput.value = d.new_csrf_token;
              }
              
              // Mettre à jour aussi le token global
              var globalCsrfInput = document.getElementById("csrf-token");
              if (globalCsrfInput) {
                globalCsrfInput.value = d.new_csrf_token;
              }
            }
          } else {
            showToast(d.message || "Erreur", "error");
          }
        })
        .catch(function () {
          showToast("Erreur de communication avec le serveur.", "error");
        });
    });
  }

  // ==================== ACTIONS EN MASSE ====================

  // Marquer toutes comme terminées
  var completeAllBtn = document.getElementById("complete-all-btn");
  if (completeAllBtn) {
    completeAllBtn.addEventListener("click", function () {
      Swal.fire({
        title: "Marquer toutes comme terminées ?",
        text: "Toutes vos réservations seront marquées comme terminées.",
        icon: "info",
        showCancelButton: true,
        confirmButtonColor: "#6366f1",
        cancelButtonColor: "#6b7280",
        confirmButtonText: '<i class="fas fa-flag-checkered"></i> Marquer',
        cancelButtonText: "Annuler"
      }).then(function (result) {
        if (result.isConfirmed) {
          completeAllReservations();
        }
      });
    });
  }

  // Supprimer toutes les réservations
  var deleteAllBtn = document.getElementById("delete-all-btn");
  if (deleteAllBtn) {
    deleteAllBtn.addEventListener("click", function () {
      Swal.fire({
        title: "Supprimer toutes les réservations ?",
        text: "Cette action est irréversible. Toutes vos réservations seront supprimées définitivement.",
        icon: "error",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#6b7280",
        confirmButtonText: '<i class="fas fa-trash"></i> Tout supprimer',
        cancelButtonText: "Annuler"
      }).then(function (result) {
        if (result.isConfirmed) {
          deleteAllReservations();
        }
      });
    });
  }

  // Supprimer toutes les réservations terminées
  var deleteCompletedBtn = document.getElementById("delete-completed-btn");
  if (deleteCompletedBtn) {
    deleteCompletedBtn.addEventListener("click", function () {
      Swal.fire({
        title: "Supprimer les réservations terminées ?",
        text: "Toutes les réservations marquées comme terminées seront supprimées.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f59e0b",
        cancelButtonColor: "#6b7280",
        confirmButtonText: '<i class="fas fa-trash-alt"></i> Supprimer',
        cancelButtonText: "Annuler"
      }).then(function (result) {
        if (result.isConfirmed) {
          deleteCompletedReservations();
        }
      });
    });
  }

  function deleteAllReservations() {
    var csrfToken = document.getElementById("csrf-token").value;
    var formData = new FormData();
    formData.append("csrf_token", csrfToken);

    fetch("?page=reservation-delete-all", {
      method: "POST",
      body: formData,
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d.success) {
          showToast(d.message, "success");
          // Mettre à jour le token CSRF
          if (d.new_csrf_token) {
            document.getElementById("csrf-token").value = d.new_csrf_token;
          }
          // Rediriger vers l'onglet Réservations après un court délai
          setTimeout(function () {
            window.location.href = "?page=reservations&tab=list";
          }, 1500);
        } else {
          showToast(d.message || "Erreur", "error");
        }
      })
      .catch(function () {
        showToast("Erreur de communication avec le serveur.", "error");
      });
  }

  function deleteCompletedReservations() {
    var csrfToken = document.getElementById("csrf-token").value;
    var formData = new FormData();
    formData.append("csrf_token", csrfToken);

    fetch("?page=reservation-delete-completed", {
      method: "POST",
      body: formData,
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d.success) {
          showToast(d.message, "success");
          // Mettre à jour le token CSRF
          if (d.new_csrf_token) {
            document.getElementById("csrf-token").value = d.new_csrf_token;
          }
          // Rediriger vers l'onglet Réservations après un court délai
          setTimeout(function () {
            window.location.href = "?page=reservations&tab=list";
          }, 1500);
        } else {
          showToast(d.message || "Erreur", "error");
        }
      })
      .catch(function () {
        showToast("Erreur de communication avec le serveur.", "error");
      });
  }

  function completeAllReservations() {
    var csrfToken = document.getElementById("csrf-token").value;
    var formData = new FormData();
    formData.append("csrf_token", csrfToken);

    fetch("?page=reservation-complete-all", {
      method: "POST",
      body: formData,
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d.success) {
          showToast(d.message, "success");
          // Mettre à jour le token CSRF
          if (d.new_csrf_token) {
            document.getElementById("csrf-token").value = d.new_csrf_token;
          }
          // Rediriger vers l'onglet Réservations après un court délai
          setTimeout(function () {
            window.location.href = "?page=reservations&tab=list";
          }, 1500);
        } else {
          showToast(d.message || "Erreur", "error");
        }
      })
      .catch(function () {
        showToast("Erreur de communication avec le serveur.", "error");
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
