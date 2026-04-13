<?php if (!empty($bookingEnabled)): ?>
<section id="reservation" class="reservation-section">
    <div class="container">
        <h2><i class="fas fa-calendar-check"></i> Réserver une table</h2>

        <?php if (!empty($bookingMessage)): ?>
            <p class="booking-message"><?= htmlspecialchars($bookingMessage) ?></p>
        <?php endif; ?>

        <form id="booking-form" class="booking-form" data-admin-id="<?= $adminId ?>">
            <div class="booking-form-grid">
                <!-- Nom -->
                <div class="booking-field">
                    <label for="booking-name"><i class="fas fa-user"></i> Nom <span class="required">*</span></label>
                    <input type="text" id="booking-name" name="customer_name" required minlength="2" maxlength="100" placeholder="Votre nom">
                </div>

                <!-- Téléphone -->
                <div class="booking-field">
                    <label for="booking-phone"><i class="fas fa-phone"></i> Téléphone <span class="required">*</span></label>
                    <input type="tel" id="booking-phone" name="customer_phone" required minlength="8" maxlength="20" placeholder="06 12 34 56 78">
                </div>

                <!-- Email -->
                <div class="booking-field">
                    <label for="booking-email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="booking-email" name="customer_email" maxlength="255" placeholder="votre@email.com (optionnel)">
                </div>

                <!-- Nombre de personnes -->
                <div class="booking-field">
                    <label for="booking-party"><i class="fas fa-users"></i> Personnes <span class="required">*</span></label>
                    <select id="booking-party" name="party_size" required>
                        <?php for ($i = ($bookingMinParty ?? 1); $i <= ($bookingMaxParty ?? 10); $i++): ?>
                            <option value="<?= $i ?>" <?= $i === 2 ? 'selected' : '' ?>><?= $i ?> personne<?= $i > 1 ? 's' : '' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Date -->
                <div class="booking-field">
                    <label for="booking-date"><i class="fas fa-calendar-alt"></i> Date <span class="required">*</span></label>
                    <input type="date" id="booking-date" name="reservation_date" required
                           min="<?= date('Y-m-d') ?>"
                           max="<?= date('Y-m-d', strtotime('+' . ($bookingAdvanceDays ?? 30) . ' days')) ?>">
                </div>

                <!-- Créneaux horaires -->
                <div class="booking-field">
                    <label><i class="fas fa-clock"></i> Créneau <span class="required">*</span></label>
                    <div class="booking-slots" id="booking-slots">
                        <p class="slots-placeholder">Sélectionnez d'abord une date</p>
                    </div>
                </div>

                <!-- Demande spéciale -->
                <div class="booking-field booking-field-full">
                    <label for="booking-requests"><i class="fas fa-comment-alt"></i> Demande particulière</label>
                    <textarea id="booking-requests" name="special_requests" rows="3" maxlength="500" placeholder="Allergie, anniversaire, chaise bébé..."></textarea>
                </div>
            </div>

            <!-- Bouton submit -->
            <div class="booking-submit">
                <button type="submit" class="btn-booking-submit" id="booking-submit-btn">
                    <i class="fas fa-calendar-check"></i> Réserver
                </button>
            </div>

            <!-- Message de résultat -->
            <div class="booking-result" id="booking-result" style="display: none;"></div>
        </form>
    </div>
</section>
<?php endif; ?>
