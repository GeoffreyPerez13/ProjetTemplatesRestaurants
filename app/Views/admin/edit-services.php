<?php
$title = "Gestion des services et options";
$styles = [
    "css/sections/edit-services.css"
];
$scripts = [
    "js/effects/scroll-buttons.js",
    "js/effects/accordion.js",
    "js/sections/edit-services/edit-services.js"
];
require __DIR__ . '/../partials/header.php';
?>

<script>
    window.scrollParams = {
        anchor: '<?= htmlspecialchars($anchor ?? '') ?>',
        scrollDelay: <?= (int)($scroll_delay ?? 1500) ?>,
    };
</script>

<a class="btn-back" href="?page=dashboard">Retour au dashboard</a>

<!-- Boutons de navigation haut/bas -->
<div class="page-navigation-buttons">
    <button type="button" class="btn-navigation scroll-to-bottom" title="Aller en bas de la page"><i class="fas fa-arrow-down"></i></button>
    <button type="button" class="btn-navigation scroll-to-top" title="Aller en haut de la page"><i class="fas fa-arrow-up"></i></button>
</div>

<!-- Boutons de contrôle généraux pour tous les accordéons -->
<div class="global-accordion-controls">
    <button type="button" id="expand-all-accordions" class="btn"><i class="fas fa-expand-alt"></i> Tout ouvrir</button>
    <button type="button" id="collapse-all-accordions" class="btn"><i class="fas fa-compress-alt"></i> Tout fermer</button>
</div>

<!-- Affichage des messages -->
<?php if (!empty($success_message)): ?>
    <p class="message-success"><?= htmlspecialchars($success_message) ?></p>
<?php endif; ?>
<?php if (!empty($error_message)): ?>
    <p class="message-error"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<div class="edit-services-container">
    <form method="post" action="?page=edit-services&action=save">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <!-- ==================== ACCORDÉON SERVICES ==================== -->
        <div class="accordion-section services-accordion" id="services">
            <div class="accordion-header">
                <h2><i class="fas fa-concierge-bell"></i> Services proposés</h2>
                <button type="button" class="accordion-toggle" data-target="services-content"><i class="fas fa-chevron-up"></i></button>
            </div>
            <div id="services-content" class="accordion-content expanded">
                <!-- Bouton tout cocher/décocher -->
                <div class="allergenes-controls">
                    <button type="button" class="btn-allergenes-toggle" data-target="services-checkboxes">
                        <i class="fas fa-check-double"></i> Tout (dé)cocher
                    </button>
                </div>
                <div class="services-grid" id="services-checkboxes">
                    <div class="service-item">
                        <label>
                            <input type="checkbox" name="service_sur_place" value="1" <?= $services['service_sur_place'] == '1' ? 'checked' : '' ?>>
                            <i class="fas fa-store"></i> Sur place
                        </label>
                    </div>
                    <div class="service-item">
                        <label>
                            <input type="checkbox" name="service_a_emporter" value="1" <?= $services['service_a_emporter'] == '1' ? 'checked' : '' ?>>
                            <i class="fas fa-shopping-bag"></i> À emporter
                        </label>
                    </div>
                    <div class="service-item">
                        <label><i class="fas fa-truck"></i> Livraison</label>
                        <div class="livraison-options">
                            <label>
                                <input type="checkbox" name="service_livraison_ubereats" value="1" <?= $services['service_livraison_ubereats'] == '1' ? 'checked' : '' ?>>
                                <i class="fab fa-ubereats"></i> Uber Eats / Deliveroo
                            </label>
                            <label>
                                <input type="checkbox" name="service_livraison_etablissement" value="1" <?= $services['service_livraison_etablissement'] == '1' ? 'checked' : '' ?>>
                                <i class="fas fa-motorcycle"></i> Livraison par l'établissement
                            </label>
                        </div>
                    </div>
                    <div class="service-item">
                        <label>
                            <input type="checkbox" name="service_wifi" value="1" <?= $services['service_wifi'] == '1' ? 'checked' : '' ?>>
                            <i class="fas fa-wifi"></i> Wi-Fi
                        </label>
                    </div>
                    <div class="service-item">
                        <label>
                            <input type="checkbox" name="service_climatisation" value="1" <?= $services['service_climatisation'] == '1' ? 'checked' : '' ?>>
                            <i class="fas fa-wind"></i> Climatisation
                        </label>
                    </div>
                    <div class="service-item">
                        <label>
                            <input type="checkbox" name="service_pmr" value="1" <?= $services['service_pmr'] == '1' ? 'checked' : '' ?>>
                            <i class="fas fa-wheelchair"></i> Accès PMR
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== ACCORDÉON PAIEMENTS ==================== -->
        <div class="accordion-section payments-accordion" id="payments">
            <div class="accordion-header">
                <h2><i class="fas fa-credit-card"></i> Moyens de paiement acceptés</h2>
                <button type="button" class="accordion-toggle" data-target="payments-content"><i class="fas fa-chevron-up"></i></button>
            </div>
            <div id="payments-content" class="accordion-content expanded">
                <!-- Bouton tout cocher/décocher -->
                <div class="allergenes-controls">
                    <button type="button" class="btn-allergenes-toggle" data-target="payments-checkboxes">
                        <i class="fas fa-check-double"></i> Tout (dé)cocher
                    </button>
                </div>
                <div class="payments-grid" id="payments-checkboxes">
                    <div class="payment-item">
                        <label>
                            <input type="checkbox" name="payment_visa" value="1" <?= $payments['payment_visa'] == '1' ? 'checked' : '' ?>>
                            <i class="fab fa-cc-visa"></i> Visa
                        </label>
                    </div>
                    <div class="payment-item">
                        <label>
                            <input type="checkbox" name="payment_mastercard" value="1" <?= $payments['payment_mastercard'] == '1' ? 'checked' : '' ?>>
                            <i class="fab fa-cc-mastercard"></i> Mastercard
                        </label>
                    </div>
                    <div class="payment-item">
                        <label>
                            <input type="checkbox" name="payment_cb" value="1" <?= $payments['payment_cb'] == '1' ? 'checked' : '' ?>>
                            <i class="fas fa-credit-card"></i> Carte bancaire
                        </label>
                    </div>
                    <div class="payment-item">
                        <label>
                            <input type="checkbox" name="payment_especes" value="1" <?= $payments['payment_especes'] == '1' ? 'checked' : '' ?>>
                            <i class="fas fa-money-bill-wave"></i> Espèces
                        </label>
                    </div>
                    <div class="payment-item">
                        <label>
                            <input type="checkbox" name="payment_cheques" value="1" <?= $payments['payment_cheques'] == '1' ? 'checked' : '' ?>>
                            <i class="fas fa-money-check"></i> Chèques
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== ACCORDÉON RÉSEAUX SOCIAUX ==================== -->
        <div class="accordion-section socials-accordion" id="socials">
            <div class="accordion-header">
                <h2><i class="fas fa-share-alt"></i> Réseaux sociaux</h2>
                <button type="button" class="accordion-toggle" data-target="socials-content"><i class="fas fa-chevron-up"></i></button>
            </div>
            <div id="socials-content" class="accordion-content expanded">
                <div class="socials-grid">
                    <div class="social-item">
                        <label for="social_instagram"><i class="fab fa-instagram"></i> Instagram</label>
                        <input type="url" name="social_instagram" id="social_instagram" value="<?= htmlspecialchars($socials['social_instagram']) ?>" placeholder="https://instagram.com/votrecompte">
                    </div>
                    <div class="social-item">
                        <label for="social_facebook"><i class="fab fa-facebook"></i> Facebook</label>
                        <input type="url" name="social_facebook" id="social_facebook" value="<?= htmlspecialchars($socials['social_facebook']) ?>" placeholder="https://facebook.com/votrepage">
                    </div>
                    <div class="social-item">
                        <label for="social_x"><i class="fab fa-x-twitter"></i> X (Twitter)</label>
                        <input type="url" name="social_x" id="social_x" value="<?= htmlspecialchars($socials['social_x']) ?>" placeholder="https://x.com/votrecompte">
                    </div>
                    <div class="social-item">
                        <label for="social_tiktok"><i class="fab fa-tiktok"></i> TikTok</label>
                        <input type="url" name="social_tiktok" id="social_tiktok" value="<?= htmlspecialchars($socials['social_tiktok']) ?>" placeholder="https://tiktok.com/@votrecompte">
                    </div>
                    <div class="social-item">
                        <label for="social_snapchat"><i class="fab fa-snapchat"></i> Snapchat</label>
                        <input type="text" name="social_snapchat" id="social_snapchat" value="<?= htmlspecialchars($socials['social_snapchat']) ?>" placeholder="Nom d'utilisateur">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions global-save">
            <button type="submit" class="btn success"><i class="fas fa-save"></i> Enregistrer tous les paramètres</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>