<!-- ==================== SECTION SERVICES & PAIEMENTS ==================== -->
<?php
// Vérifier si au moins un service est actif
$hasServices = (
    $services['service_sur_place'] == '1' ||
    $services['service_a_emporter'] == '1' ||
    $services['service_livraison_ubereats'] == '1' ||
    $services['service_livraison_etablissement'] == '1' ||
    $services['service_wifi'] == '1' ||
    $services['service_climatisation'] == '1' ||
    $services['service_pmr'] == '1'
);
// Vérifier si au moins un paiement est actif
$hasPayments = (
    $payments['payment_visa'] == '1' ||
    $payments['payment_mastercard'] == '1' ||
    $payments['payment_cb'] == '1' ||
    $payments['payment_especes'] == '1' ||
    $payments['payment_cheques'] == '1'
);
if ($hasServices || $hasPayments):
?>
    <section id="services" class="services-payments-section">
        <div class="container">
            <h2>Services & Moyens de paiement</h2>
            <div class="services-payments-grid">
                <?php if ($hasServices): ?>
                    <div class="services-column">
                        <h3><i class="fas fa-concierge-bell"></i> Nos services</h3>
                        <ul class="services-list">
                            <?php if ($services['service_sur_place'] == '1'): ?>
                                <li><i class="fas fa-store"></i> Sur place</li>
                            <?php endif; ?>
                            <?php if ($services['service_a_emporter'] == '1'): ?>
                                <li><i class="fas fa-shopping-bag"></i> À emporter</li>
                            <?php endif; ?>
                            <?php if ($services['service_livraison_ubereats'] == '1' || $services['service_livraison_etablissement'] == '1'): ?>
                                <li class="delivery-group">
                                    <div class="delivery-header">
                                        <i class="fas fa-truck"></i> Livraison
                                    </div>
                                    <div class="delivery-options">
                                        <?php if ($services['service_livraison_ubereats'] == '1'): ?>
                                            <div class="delivery-option"><i class="fa-solid fa-car"></i> Uber Eats / Deliveroo</div>
                                        <?php endif; ?>
                                        <?php if ($services['service_livraison_etablissement'] == '1'): ?>
                                            <div class="delivery-option"><i class="fas fa-motorcycle"></i> Par l'établissement</div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endif; ?>
                            <?php if ($services['service_wifi'] == '1'): ?>
                                <li><i class="fas fa-wifi"></i> Wi-Fi gratuit</li>
                            <?php endif; ?>
                            <?php if ($services['service_climatisation'] == '1'): ?>
                                <li><i class="fas fa-wind"></i> Climatisation</li>
                            <?php endif; ?>
                            <?php if ($services['service_pmr'] == '1'): ?>
                                <li><i class="fas fa-wheelchair"></i> Accès personnes à mobilité réduite</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($hasPayments): ?>
                    <div class="payments-column">
                        <h3><i class="fas fa-credit-card"></i> Moyens de paiement</h3>
                        <ul class="payments-list">
                            <?php if ($payments['payment_visa'] == '1'): ?>
                                <li><i class="fab fa-cc-visa"></i> Visa</li>
                            <?php endif; ?>
                            <?php if ($payments['payment_mastercard'] == '1'): ?>
                                <li><i class="fab fa-cc-mastercard"></i> Mastercard</li>
                            <?php endif; ?>
                            <?php if ($payments['payment_cb'] == '1'): ?>
                                <li><i class="fas fa-credit-card"></i> Carte bancaire</li>
                            <?php endif; ?>
                            <?php if ($payments['payment_especes'] == '1'): ?>
                                <li><i class="fas fa-money-bill-wave"></i> Espèces</li>
                            <?php endif; ?>
                            <?php if ($payments['payment_cheques'] == '1'): ?>
                                <li><i class="fas fa-money-check"></i> Chèques</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
