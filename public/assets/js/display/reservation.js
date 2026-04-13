/**
 * Réservation en ligne — JavaScript côté vitrine
 */
(function () {
  "use strict";

  var form = document.getElementById("booking-form");
  if (!form) return;

  var adminId = form.dataset.adminId;
  var dateInput = document.getElementById("booking-date");
  var slotsContainer = document.getElementById("booking-slots");
  var submitBtn = document.getElementById("booking-submit-btn");
  var resultDiv = document.getElementById("booking-result");
  var selectedSlot = null;

  // ==================== CHARGEMENT DES CRÉNEAUX ====================

  if (dateInput) {
    dateInput.addEventListener("change", function () {
      var date = dateInput.value;
      if (!date) {
        slotsContainer.innerHTML = '<p class="slots-placeholder">Sélectionnez d\'abord une date</p>';
        selectedSlot = null;
        return;
      }
      loadSlots(date);
    });
  }

  function loadSlots(date) {
    selectedSlot = null;
    slotsContainer.innerHTML = '<div class="slots-loading"><i class="fas fa-spinner"></i> Chargement des créneaux...</div>';

    fetch("?page=booking-slots&admin_id=" + encodeURIComponent(adminId) + "&date=" + encodeURIComponent(date))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.success) {
          slotsContainer.innerHTML = '<p class="slots-placeholder">Impossible de charger les créneaux.</p>';
          return;
        }

        if (data.closed) {
          slotsContainer.innerHTML = '<div class="slots-closed"><i class="fas fa-times-circle"></i> Le restaurant est fermé ce jour-là.</div>';
          return;
        }

        if (!data.slots || data.slots.length === 0) {
          slotsContainer.innerHTML = '<p class="slots-placeholder">Aucun créneau disponible pour cette date.</p>';
          return;
        }

        slotsContainer.innerHTML = "";
        data.slots.forEach(function (slot) {
          var btn = document.createElement("button");
          btn.type = "button";
          btn.className = "slot-btn" + (slot.available ? "" : " disabled");
          btn.textContent = slot.time;
          btn.dataset.time = slot.time;

          if (slot.available) {
            btn.addEventListener("click", function () {
              // Désélectionner l'ancien
              var prev = slotsContainer.querySelector(".slot-btn.selected");
              if (prev) prev.classList.remove("selected");

              // Sélectionner le nouveau
              btn.classList.add("selected");
              selectedSlot = slot.time;
            });
          }

          slotsContainer.appendChild(btn);
        });
      })
      .catch(function () {
        slotsContainer.innerHTML = '<p class="slots-placeholder">Erreur lors du chargement.</p>';
      });
  }

  // ==================== SOUMISSION DU FORMULAIRE ====================

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    // Validation côté client
    var name = document.getElementById("booking-name").value.trim();
    var phone = document.getElementById("booking-phone").value.trim();
    var date = dateInput.value;

    if (!name || name.length < 2) {
      showResult("Veuillez indiquer votre nom.", "error");
      return;
    }
    if (!phone || phone.length < 8) {
      showResult("Veuillez indiquer un numéro de téléphone valide.", "error");
      return;
    }
    if (!date) {
      showResult("Veuillez choisir une date.", "error");
      return;
    }
    if (!selectedSlot) {
      showResult("Veuillez choisir un créneau horaire.", "error");
      return;
    }

    // Préparer les données
    var formData = new FormData(form);
    formData.append("admin_id", adminId);
    formData.append("reservation_time", selectedSlot);

    // Désactiver le bouton
    submitBtn.disabled = true;
    submitBtn.classList.add("loading");
    var originalHtml = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner"></i> Envoi en cours...';

    fetch("?page=booking-submit", {
      method: "POST",
      body: formData,
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          showResult('<i class="fas fa-check-circle"></i> ' + data.message, "success");
          form.reset();
          slotsContainer.innerHTML = '<p class="slots-placeholder">Sélectionnez d\'abord une date</p>';
          selectedSlot = null;
        } else {
          showResult('<i class="fas fa-exclamation-circle"></i> ' + data.message, "error");
        }
      })
      .catch(function () {
        showResult('<i class="fas fa-exclamation-triangle"></i> Erreur de communication. Veuillez réessayer.', "error");
      })
      .finally(function () {
        submitBtn.disabled = false;
        submitBtn.classList.remove("loading");
        submitBtn.innerHTML = originalHtml;
      });
  });

  // ==================== AFFICHAGE DU RÉSULTAT ====================

  function showResult(message, type) {
    resultDiv.style.display = "block";
    resultDiv.className = "booking-result result-" + type;
    resultDiv.innerHTML = message;

    // Scroll vers le résultat
    resultDiv.scrollIntoView({ behavior: "smooth", block: "nearest" });

    // Auto-hide après 8s pour les succès
    if (type === "success") {
      setTimeout(function () {
        resultDiv.style.display = "none";
      }, 8000);
    }
  }
})();
