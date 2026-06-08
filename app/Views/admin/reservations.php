<?php
$title = "Réservations en ligne";
$scripts = ["js/sections/reservations/reservations.js"];
require __DIR__ . '/../partials/header.php';

$statusLabels = [
    'pending'   => 'En attente',
    'confirmed' => 'Confirmée',
    'cancelled' => 'Annulée',
    'completed' => 'Terminée',
    'no_show'   => 'Absent',
];
$statusIcons = [
    'pending'   => 'fa-clock',
    'confirmed' => 'fa-check-circle',
    'cancelled' => 'fa-times-circle',
    'completed' => 'fa-flag-checkered',
    'no_show'   => 'fa-user-slash',
];
$statusColors = [
    'pending'   => 'warning',
    'confirmed' => 'success',
    'cancelled' => 'danger',
    'completed' => 'muted',
    'no_show'   => 'danger',
];
?>

<a class="btn-back" href="?page=dashboard">Retour</a>

<div class="reservations-page">
    <div class="reservations-header">
        <h2><i class="fas fa-calendar-check"></i> Réservations en ligne</h2>
        <a href="?page=floor-plan" class="btn-primary">
            <i class="fas fa-map-marked-alt"></i> Plan de salle
        </a>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="message-success"><?= $success_message ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="message-error"><?= $error_message ?></div>
    <?php endif; ?>

    <!-- Onglets -->
    <div class="reservations-tabs">
        <button class="tab-btn active" data-tab="tab-dashboard"><i class="fas fa-tachometer-alt"></i> <span>Tableau de bord</span></button>
        <button class="tab-btn" data-tab="tab-list"><i class="fas fa-list"></i> <span>Réservations</span></button>
        <button class="tab-btn" data-tab="tab-settings">
            <i class="fas fa-cog"></i> <span>Paramètres</span>
            <?php if (!empty($closureDates)): ?>
                <span class="tab-badge" title="<?= count($closureDates) ?> fermeture(s) exceptionnelle(s) configurée(s)"><?= count($closureDates) ?></span>
            <?php endif; ?>
        </button>
    </div>

    <!-- ==================== TAB DASHBOARD ==================== -->
    <div class="tab-content active" id="tab-dashboard">
        <!-- KPIs -->
        <div class="reservations-kpis">
            <div class="kpi-card kpi-today">
                <div class="kpi-icon"><i class="fas fa-calendar-day"></i></div>
                <div class="kpi-data">
                    <span class="kpi-value"><?= $stats['today'] ?? 0 ?></span>
                    <span class="kpi-label">Aujourd'hui</span>
                </div>
            </div>
            <div class="kpi-card kpi-pending">
                <div class="kpi-icon"><i class="fas fa-clock"></i></div>
                <div class="kpi-data">
                    <span class="kpi-value"><?= $stats['pending'] ?? 0 ?></span>
                    <span class="kpi-label">En attente</span>
                </div>
            </div>
            <div class="kpi-card kpi-confirmed">
                <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                <div class="kpi-data">
                    <span class="kpi-value"><?= $stats['confirmed'] ?? 0 ?></span>
                    <span class="kpi-label">Confirmées</span>
                </div>
            </div>
            <div class="kpi-card kpi-covers">
                <div class="kpi-icon"><i class="fas fa-utensils"></i></div>
                <div class="kpi-data">
                    <span class="kpi-value"><?= $stats['today_covers'] ?? 0 ?></span>
                    <span class="kpi-label">Couverts ce jour</span>
                </div>
            </div>
            <div class="kpi-card kpi-week">
                <div class="kpi-icon"><i class="fas fa-calendar-week"></i></div>
                <div class="kpi-data">
                    <span class="kpi-value"><?= $stats['this_week'] ?? 0 ?></span>
                    <span class="kpi-label">Cette semaine</span>
                </div>
            </div>
        </div>

        <!-- Réservations du jour -->
        <div class="today-section">
            <div class="today-header">
                <div class="today-date-selector">
                    <button class="btn small btn-outline" id="prev-day" title="Jour précédent">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="today-date-display">
                        <input type="date" id="dashboard-date" value="<?= date('Y-m-d') ?>">
                        <h3><i class="fas fa-calendar-day"></i> <span id="dashboard-date-label">Aujourd'hui</span> — <span id="dashboard-date-text"><?= date('d/m/Y') ?></span></h3>
                    </div>
                    <button class="btn small btn-outline" id="next-day" title="Jour suivant">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            
            <!-- Réservations en attente -->
            <div id="pending-reservations-section" style="display: none;">
                <div class="pending-header">
                    <h4><i class="fas fa-clock"></i> Réservations en attente</h4>
                    <button id="btn-validate-all" class="btn small success btn-validate-all" style="display: none;">
                        <i class="fas fa-check-double"></i> Tout valider à ce jour
                    </button>
                </div>
                <div class="pending-reservations-grid">
                    <!-- Contenu rempli par JavaScript -->
                </div>
            </div>
            <?php 
            $nonPendingReservations = array_filter($todayReservations ?? [], function($r) {
                return $r['status'] !== 'pending';
            });
            
            if (empty($nonPendingReservations) && empty($todayReservations ?? [])): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>Aucune réservation pour aujourd'hui.</p>
                </div>
            <?php elseif (empty($nonPendingReservations)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>Aucune réservation confirmée pour aujourd'hui.</p>
                </div>
            <?php else: ?>
                <div class="today-reservations-grid">
                    <?php foreach ($nonPendingReservations as $r): ?>
                        <div class="reservation-card status-<?= $r['status'] ?>" data-id="<?= $r['id'] ?>">
                            <div class="reservation-card-header">
                                <span class="reservation-time"><i class="fas fa-clock"></i> <?= substr($r['reservation_time'], 0, 5) ?></span>
                                <span class="reservation-status badge-<?= $statusColors[$r['status']] ?>"><?= $statusLabels[$r['status']] ?></span>
                            </div>
                            <div class="reservation-card-body">
                                <div class="reservation-info">
                                    <span class="reservation-name"><i class="fas fa-user"></i> <?= htmlspecialchars($r['customer_name']) ?></span>
                                    <span class="reservation-party"><i class="fas fa-users"></i> <?= $r['party_size'] ?> pers.</span>
                                </div>
                                <?php if (!empty($r['table_number'])): ?>
                                    <div class="reservation-table-info">
                                        <span class="reservation-table"><i class="fas fa-chair"></i> <?= htmlspecialchars($r['floor_name']) ?> - Table <?= htmlspecialchars($r['table_number']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="reservation-contact">
                                    <span><i class="fas fa-phone"></i> <?= htmlspecialchars($r['customer_phone']) ?></span>
                                    <?php if (!empty($r['customer_email'])): ?>
                                        <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($r['customer_email']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($r['special_requests'])): ?>
                                    <div class="reservation-notes">
                                        <i class="fas fa-comment-alt"></i> <?= htmlspecialchars($r['special_requests']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($r['status'] === 'pending'): ?>
                                <div class="reservation-card-actions">
                                    <button class="btn small success btn-confirm-reservation" data-id="<?= $r['id'] ?>">
                                        <i class="fas fa-check"></i> Confirmer
                                    </button>
                                    <button class="btn small danger btn-cancel-reservation" data-id="<?= $r['id'] ?>">
                                        <i class="fas fa-times"></i> Refuser
                                    </button>
                                </div>
                            <?php elseif ($r['status'] === 'confirmed'): ?>
                                <div class="reservation-card-actions">
                                    <button class="btn small btn-complete-reservation" data-id="<?= $r['id'] ?>">
                                        <i class="fas fa-flag-checkered"></i> Terminée
                                    </button>
                                    <button class="btn small danger btn-noshow-reservation" data-id="<?= $r['id'] ?>">
                                        <i class="fas fa-user-slash"></i> Absent
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ==================== TAB LIST ==================== -->
    <div class="tab-content" id="tab-list">
        <!-- Filtres -->
        <div class="reservations-filters">
            <form method="GET" action="" class="filters-form">
                <input type="hidden" name="page" value="reservations">
                <div class="filter-group">
                    <label for="filter-status">Statut</label>
                    <select name="status" id="filter-status">
                        <option value="">Tous</option>
                        <?php foreach ($statusLabels as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($filters['status'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filter-date">Date</label>
                    <input type="date" name="date" id="filter-date" value="<?= htmlspecialchars($filters['date'] ?? '') ?>">
                </div>
                <div class="filter-group">
                    <label for="filter-search">Recherche</label>
                    <input type="text" name="search" id="filter-search" placeholder="Nom, email, tél..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn small"><i class="fas fa-search"></i> Filtrer</button>
                    <a href="?page=reservations&tab=list" class="btn small btn-outline"><i class="fas fa-undo"></i> Réinitialiser</a>
                    <div class="filter-divider"></div>
                    <button type="button" id="complete-all-btn" class="btn-icon btn-primary" title="Marquer toutes comme terminées">
                        <i class="fas fa-flag-checkered"></i>
                    </button>
                    <button type="button" id="delete-completed-btn" class="btn-icon btn-warning" title="Supprimer réservations terminées">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <button type="button" id="delete-all-btn" class="btn-icon btn-danger" title="Supprimer toutes les réservations">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Liste des réservations -->
        <?php if (empty($reservations)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>Aucune réservation trouvée.</p>
            </div>
        <?php else: ?>
            <div class="reservations-table-container">
                <table class="reservations-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Nom</th>
                            <th>Téléphone</th>
                            <th>Couverts</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $r): ?>
                            <tr class="row-status-<?= $r['status'] ?>" data-id="<?= $r['id'] ?>">
                                <td data-label="Date"><?= date('d/m/Y', strtotime($r['reservation_date'])) ?></td>
                                <td data-label="Heure"><?= substr($r['reservation_time'], 0, 5) ?></td>
                                <td data-label="Nom">
                                    <strong><?= htmlspecialchars($r['customer_name']) ?></strong>
                                    <?php if (!empty($r['customer_email'])): ?>
                                        <br><small><?= htmlspecialchars($r['customer_email']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Tél"><?= htmlspecialchars($r['customer_phone']) ?></td>
                                <td data-label="Couverts">
                                    <i class="fas fa-users"></i> <?= $r['party_size'] ?>
                                    <?php if (!empty($r['table_number'])): ?>
                                        <br><small style="color: var(--color-primary);"><i class="fas fa-chair"></i> <?= htmlspecialchars($r['floor_name']) ?> - T<?= htmlspecialchars($r['table_number']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Statut">
                                    <span class="status-badge badge-<?= $statusColors[$r['status']] ?>">
                                        <i class="fas <?= $statusIcons[$r['status']] ?>"></i>
                                        <?= $statusLabels[$r['status']] ?>
                                    </span>
                                </td>
                                <td data-label="Actions" class="actions-cell">
                                    <div class="actions-wrapper">
                                        <?php if ($r['status'] === 'pending'): ?>
                                            <button class="btn small success btn-confirm-reservation" data-id="<?= $r['id'] ?>" title="Confirmer"><i class="fas fa-check"></i></button>
                                            <button class="btn small danger btn-cancel-reservation" data-id="<?= $r['id'] ?>" title="Refuser"><i class="fas fa-times"></i></button>
                                        <?php elseif ($r['status'] === 'confirmed'): ?>
                                            <?php if (($settings['booking_assign_table'] ?? false) && !($settings['booking_auto_confirm'] ?? false)): ?>
                                                <button class="btn small primary btn-change-table" data-id="<?= $r['id'] ?>" title="Changer de table"><i class="fas fa-chair"></i></button>
                                            <?php endif; ?>
                                            <button class="btn small btn-complete-reservation" data-id="<?= $r['id'] ?>" title="Terminée"><i class="fas fa-flag-checkered"></i></button>
                                            <button class="btn small danger btn-noshow-reservation" data-id="<?= $r['id'] ?>" title="Absent"><i class="fas fa-user-slash"></i></button>
                                        <?php endif; ?>
                                        <button class="btn small info btn-edit-datetime" data-id="<?= $r['id'] ?>" data-date="<?= $r['reservation_date'] ?>" data-time="<?= substr($r['reservation_time'], 0, 5) ?>" title="Modifier date/heure"><i class="fas fa-calendar-alt"></i></button>
                                        <button class="btn small danger btn-delete-reservation" data-id="<?= $r['id'] ?>" title="Supprimer"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php if (!empty($r['special_requests'])): ?>
                                <tr class="reservation-detail-row row-status-<?= $r['status'] ?>">
                                    <td colspan="7">
                                        <div class="reservation-detail-note">
                                            <i class="fas fa-comment-alt"></i> <?= htmlspecialchars($r['special_requests']) ?>
                                            <?php if (!empty($r['admin_notes'])): ?>
                                                <br><strong>Note admin :</strong> <?= htmlspecialchars($r['admin_notes']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ==================== TAB SETTINGS ==================== -->
    <div class="tab-content" id="tab-settings">
        <div class="settings-section">
            <h3><i class="fas fa-cog"></i> Paramètres des réservations</h3>

            <form id="reservation-settings-form" class="reservation-settings-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

                <!-- Activation -->
                <div class="setting-row" id="setting-booking-enabled">
                    <div class="setting-info">
                        <label>Réservations activées</label>
                        <p class="setting-desc">Activer ou désactiver les réservations sur votre site vitrine.</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle-switch">
                            <input type="checkbox" name="booking_enabled" value="1" <?= ($settings['booking_enabled'] ?? true) ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Validation automatique -->
                <div class="setting-row" id="setting-booking-auto-confirm">
                    <div class="setting-info">
                        <label>Validation automatique</label>
                        <p class="setting-desc">Confirmer automatiquement les nouvelles réservations sans validation manuelle.</p>
                        <p class="setting-warning"><i class="fas fa-exclamation-triangle"></i> Attention : toutes les réservations seront automatiquement confirmées.</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle-switch">
                            <input type="checkbox" name="booking_auto_confirm" value="1" id="booking_auto_confirm" <?= ($settings['booking_auto_confirm'] ?? false) ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Attribution de table lors de la confirmation -->
                <div class="setting-row<?= ($settings['booking_auto_confirm'] ?? false) ? ' disabled' : '' ?>" id="assign-table-setting">
                    <div class="setting-info">
                        <label>Attribution de table lors de la confirmation</label>
                        <p class="setting-desc">Lors de la confirmation d'une réservation, attribuer une table du plan de salle.</p>
                        
                        <?php if ($settings['booking_auto_confirm'] ?? false): ?>
                            <p class="setting-warning" id="assign-table-disabled-message">
                                <i class="fas fa-info-circle"></i> 
                                Cette option est incompatible avec la validation automatique. Désactivez d'abord la validation automatique pour pouvoir l'activer.
                            </p>
                        <?php endif; ?>
                        
                        <?php if (empty($tablesCount)): ?>
                            <p class="setting-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Aucune table configurée. 
                                <a href="?page=floor-plan" target="_blank" class="info-link">Configurer le plan de salle</a>
                            </p>
                        <?php else: ?>
                            <p class="setting-info-extra">
                                <i class="fas fa-info-circle"></i> 
                                <?= $tablesCount ?> table(s) configurée(s) dans le plan de salle
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="setting-control">
                        <label class="toggle-switch">
                            <input type="checkbox" name="booking_assign_table" value="1" <?= ($settings['booking_assign_table'] ?? false) ? 'checked' : '' ?> <?= ($settings['booking_auto_confirm'] ?? false) ? 'disabled' : '' ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Marquage automatique comme terminée -->
                <div class="setting-row" id="setting-booking-auto-complete">
                    <div class="setting-info">
                        <label>Marquage automatique comme terminée</label>
                        <p class="setting-desc">Marquer automatiquement les réservations confirmées comme terminées après la durée du repas.</p>
                    </div>
                    <div class="setting-control setting-inline">
                        <label class="toggle-switch">
                            <input type="checkbox" name="booking_auto_complete" value="1" <?= ($settings['booking_auto_complete'] ?? false) ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <div class="input-group-mini" style="margin-left: 16px;">
                            <label for="booking_meal_duration">Durée (min)</label>
                            <input type="number" name="booking_meal_duration" id="booking_meal_duration" min="5" max="300" step="5" value="<?= $settings['booking_meal_duration'] ?? 90 ?>">
                        </div>
                    </div>
                </div>

                <!-- Nombre de personnes -->
                <div class="setting-row">
                    <div class="setting-info">
                        <label>Nombre de personnes</label>
                        <p class="setting-desc">Nombre minimum et maximum de personnes par réservation.</p>
                    </div>
                    <div class="setting-control setting-inline">
                        <div class="input-group-mini">
                            <label for="booking_min_party">Min</label>
                            <input type="number" name="booking_min_party" id="booking_min_party" min="1" max="50" value="<?= $settings['booking_min_party'] ?? 1 ?>">
                        </div>
                        <div class="input-group-mini">
                            <label for="booking_max_party">Max</label>
                            <input type="number" name="booking_max_party" id="booking_max_party" min="1" max="50" value="<?= $settings['booking_max_party'] ?? 10 ?>">
                        </div>
                    </div>
                </div>

                <!-- Réservation max par créneau -->
                <div class="setting-row">
                    <div class="setting-info">
                        <label>Réservations par créneau</label>
                        <p class="setting-desc">Nombre maximum de réservations acceptées sur un même créneau horaire.</p>
                    </div>
                    <div class="setting-control">
                        <input type="number" name="booking_max_per_slot" min="1" max="50" value="<?= $settings['booking_max_per_slot'] ?? 5 ?>">
                    </div>
                </div>

                <!-- Jours d'avance -->
                <div class="setting-row">
                    <div class="setting-info">
                        <label>Jours d'avance maximum</label>
                        <p class="setting-desc">Combien de jours à l'avance un client peut réserver.</p>
                    </div>
                    <div class="setting-control">
                        <input type="number" name="booking_advance_days" min="1" max="365" value="<?= $settings['booking_advance_days'] ?? 30 ?>">
                    </div>
                </div>

                <!-- Créneaux horaires -->
                <div class="setting-row setting-row-full">
                    <div class="setting-info">
                        <label>Créneaux horaires disponibles</label>
                        <p class="setting-desc">Séparez les créneaux par des virgules (format HH:MM).</p>
                    </div>
                    <div class="setting-control">
                        <textarea name="booking_time_slots" rows="3" placeholder="12:00,12:30,13:00,19:00,19:30,20:00,20:30,21:00"><?= htmlspecialchars($settings['booking_time_slots'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Jours de fermeture -->
                <div class="setting-row setting-row-full">
                    <div class="setting-info">
                        <label>Jours de fermeture hebdomadaire</label>
                        <p class="setting-desc">Sélectionnez les jours où le restaurant est fermé.</p>
                        <?php if (!empty($closureDates)): ?>
                            <p class="setting-info-extra">
                                <i class="fas fa-info-circle"></i>
                                <?= count($closureDates) ?> fermeture(s) exceptionnelle(s) programmée(s)
                                <a href="?page=settings&section=options#closure-dates-section" target="_blank" class="info-link">Voir les dates</a>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="setting-control">
                        <?php
                        $closedDays = array_map('trim', explode(',', $settings['booking_closed_days'] ?? ''));
                        $days = ['1' => 'Lundi', '2' => 'Mardi', '3' => 'Mercredi', '4' => 'Jeudi', '5' => 'Vendredi', '6' => 'Samedi', '0' => 'Dimanche'];
                        ?>
                        <div class="days-checkboxes">
                            <?php foreach ($days as $num => $label): ?>
                                <label class="day-checkbox">
                                    <input type="checkbox" name="closed_days[]" value="<?= $num ?>" <?= in_array($num, $closedDays) ? 'checked' : '' ?>>
                                    <span><?= $label ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Message personnalisé -->
                <div class="setting-row setting-row-full">
                    <div class="setting-info">
                        <label>Message personnalisé</label>
                        <p class="setting-desc">Message affiché aux clients sur le formulaire de réservation (optionnel).</p>
                    </div>
                    <div class="setting-control">
                        <textarea name="booking_message" rows="3" placeholder="Ex: Merci de nous prévenir en cas d'allergie..."><?= htmlspecialchars($settings['booking_message'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="settings-actions">
                    <button type="submit" class="btn primary"><i class="fas fa-save"></i> Enregistrer les paramètres</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Token CSRF pour les appels AJAX -->
<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Navigation entre les jours
    const dateInput = document.getElementById('dashboard-date');
    const dateLabel = document.getElementById('dashboard-date-label');
    const dateText = document.getElementById('dashboard-date-text');
    const prevBtn = document.getElementById('prev-day');
    const nextBtn = document.getElementById('next-day');
    
    function updateDateDisplay() {
        const selectedDate = new Date(dateInput.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        selectedDate.setHours(0, 0, 0, 0);
        
        const diffTime = selectedDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        let label = '';
        if (diffDays === 0) {
            label = 'Aujourd\'hui';
        } else if (diffDays === 1) {
            label = 'Demain';
        } else if (diffDays === -1) {
            label = 'Hier';
        } else if (diffDays > 0) {
            label = `Dans ${diffDays} jours`;
        } else {
            label = `Il y a ${Math.abs(diffDays)} jours`;
        }
        
        dateLabel.textContent = label;
        dateText.textContent = selectedDate.toLocaleDateString('fr-FR');
        
        // Charger les réservations pour cette date
        loadReservationsForDate(dateInput.value);
    }
    
    function changeDate(days) {
        const currentDate = new Date(dateInput.value);
        currentDate.setDate(currentDate.getDate() + days);
        dateInput.value = currentDate.toISOString().split('T')[0];
        updateDateDisplay();
    }
    
    function loadReservationsForDate(date) {
        fetch(`?page=get-day-reservations&date=${date}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const section = document.querySelector('.today-section');
                    
                    // Construire le HTML pour les réservations en attente
                    let pendingHtml = '';
                    if (data.pendingReservations && data.pendingReservations.length > 0) {
                        pendingHtml = '<div id="pending-reservations-section" style="display: block;"><div class="pending-header"><h4><i class="fas fa-clock"></i> Réservations en attente</h4><button id="btn-validate-all" class="btn small success btn-validate-all"><i class="fas fa-check-double"></i> Tout valider à ce jour</button></div><div class="pending-reservations-grid">';
                        const now = new Date();
                        data.pendingReservations.forEach(r => {
                            // Vérifier si la réservation est passée
                            const reservationDateTime = new Date(r.reservation_date + ' ' + r.reservation_time);
                            const isPast = reservationDateTime < now;
                            const pastClass = isPast ? ' past-reservation' : '';
                            
                            pendingHtml += `
                                <div class="reservation-card pending-reservation-card status-pending${pastClass}" data-id="${r.id}">
                                    <div class="reservation-card-header">
                                        <span class="reservation-time"><i class="fas fa-clock"></i> ${r.reservation_time.substring(0, 5)}</span>
                                        <span class="reservation-status badge-warning">En attente</span>
                                    </div>
                                    <div class="reservation-card-body">
                                        <div class="reservation-info">
                                            <span class="reservation-name"><i class="fas fa-user"></i> ${r.customer_name}</span>
                                            <span class="reservation-party"><i class="fas fa-users"></i> ${r.party_size} pers.</span>
                                        </div>
                                        ${r.table_number ? `
                                            <div class="reservation-table-info">
                                                <span class="reservation-table"><i class="fas fa-chair"></i> ${r.floor_name} - Table ${r.table_number}</span>
                                            </div>
                                        ` : ''}
                                        <div class="reservation-contact">
                                            <span><i class="fas fa-phone"></i> ${r.customer_phone}</span>
                                            ${r.customer_email ? `<span><i class="fas fa-envelope"></i> ${r.customer_email}</span>` : ''}
                                        </div>
                                        ${r.special_requests ? `
                                            <div class="reservation-notes">
                                                <i class="fas fa-comment-alt"></i> ${r.special_requests}
                                            </div>
                                        ` : ''}
                                    </div>
                                    <div class="reservation-card-actions">
                                        <button class="btn small success btn-confirm-reservation" data-id="${r.id}">
                                            <i class="fas fa-check"></i> Confirmer
                                        </button>
                                        <button class="btn small danger btn-cancel-reservation" data-id="${r.id}">
                                            <i class="fas fa-times"></i> Annuler
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                        pendingHtml += '</div></div>';
                    }
                    
                    // Construire le HTML pour les autres réservations
                    let otherHtml = '';
                    if (data.reservations.length === 0 && data.pendingReservations.length === 0) {
                        otherHtml = `
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <p>Aucune réservation pour ce jour.</p>
                            </div>
                        `;
                    } else if (data.reservations.length === 0) {
                        otherHtml = `
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <p>Aucune réservation confirmée pour ce jour.</p>
                            </div>
                        `;
                    } else {
                        otherHtml = '<div class="today-reservations-grid">';
                        data.reservations.forEach(r => {
                            otherHtml += `
                                <div class="reservation-card status-${r.status}" data-id="${r.id}">
                                    <div class="reservation-card-header">
                                        <span class="reservation-time"><i class="fas fa-clock"></i> ${r.reservation_time.substring(0, 5)}</span>
                                        <span class="reservation-status badge-${data.statusColors[r.status]}">${data.statusLabels[r.status]}</span>
                                    </div>
                                    <div class="reservation-card-body">
                                        <div class="reservation-info">
                                            <span class="reservation-name"><i class="fas fa-user"></i> ${r.customer_name}</span>
                                            <span class="reservation-party"><i class="fas fa-users"></i> ${r.party_size} pers.</span>
                                        </div>
                                        ${r.table_number ? `
                                            <div class="reservation-table-info">
                                                <span class="reservation-table"><i class="fas fa-chair"></i> ${r.floor_name} - Table ${r.table_number}</span>
                                            </div>
                                        ` : ''}
                                        <div class="reservation-contact">
                                            <span><i class="fas fa-phone"></i> ${r.customer_phone}</span>
                                            ${r.customer_email ? `<span><i class="fas fa-envelope"></i> ${r.customer_email}</span>` : ''}
                                        </div>
                                        ${r.special_requests ? `
                                            <div class="reservation-notes">
                                                <i class="fas fa-comment-alt"></i> ${r.special_requests}
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                        });
                        otherHtml += '</div>';
                    }
                    
                    // Mettre à jour le contenu
                    section.innerHTML = `
                        <div class="today-header">
                            <div class="today-date-selector">
                                <button class="btn small btn-outline" id="prev-day" title="Jour précédent">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <div class="today-date-display">
                                    <input type="date" id="dashboard-date" value="${date}">
                                    <h3><i class="fas fa-calendar-day"></i> <span id="dashboard-date-label">${dateLabel.textContent}</span> — <span id="dashboard-date-text">${dateText.textContent}</span></h3>
                                </div>
                                <button class="btn small btn-outline" id="next-day" title="Jour suivant">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    ` + pendingHtml + otherHtml;
                    
                    // Réattacher les événements
                    attachDayNavigationEvents();
                    attachReservationEvents();
                }
            })
            .catch(error => console.error('Erreur:', error));
    }
    
    function attachDayNavigationEvents() {
        const newDateInput = document.getElementById('dashboard-date');
        const newPrevBtn = document.getElementById('prev-day');
        const newNextBtn = document.getElementById('next-day');
        
        if (newDateInput) {
            newDateInput.addEventListener('change', updateDateDisplay);
        }
        if (newPrevBtn) {
            newPrevBtn.addEventListener('click', () => changeDate(-1));
        }
        if (newNextBtn) {
            newNextBtn.addEventListener('click', () => changeDate(1));
        }
    }
    
    function attachReservationEvents() {
        // Bouton "Valider toutes"
        const btnValidateAll = document.getElementById('btn-validate-all');
        if (btnValidateAll) {
            btnValidateAll.addEventListener('click', validateAllReservations);
        }
        
        document.querySelectorAll('#tab-dashboard .btn-confirm-reservation').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const assignTableEnabled = <?= json_encode(($settings['booking_assign_table'] ?? false) && !($settings['booking_auto_confirm'] ?? false)) ?>;
                
                if (assignTableEnabled) {
                    // Charger les tables disponibles
                    fetch('?page=reservation-get-tables')
                        .then(r => r.json())
                        .then(data => {
                            if (data.success && data.tables && data.tables.length > 0) {
                                // Créer les options du select
                                let tableOptions = '<option value="">Aucune table (confirmation sans attribution)</option>';
                                data.tables.forEach(table => {
                                    let label = `${table.floor_name} - Table ${table.table_number} (${table.capacity_min}-${table.capacity_max} pers.)`;
                                    
                                    // Ajouter les informations de réservations existantes
                                    if (table.reservations && table.reservations.length > 0) {
                                        const times = table.reservations.map(r => r.time).join(', ');
                                        label += ` ⚠️ Déjà réservée : ${times}`;
                                    }
                                    
                                    tableOptions += `<option value="${table.id}">${label}</option>`;
                                });
                                
                                Swal.fire({
                                    title: 'Confirmer cette réservation',
                                    html: `
                                        <div style="text-align: left; margin-bottom: 15px;">
                                            <label for="table-select" style="display: block; margin-bottom: 10px; font-weight: 600; color: #1f2937; font-size: 0.95rem;">Attribuer une table :</label>
                                            <select id="table-select" style="width: 100%; padding: 10px 12px; border: 2px solid #d1d5db; border-radius: 6px; background: #ffffff; color: #1f2937; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; outline: none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                                                ${tableOptions}
                                            </select>
                                        </div>
                                    `,
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#10b981',
                                    confirmButtonText: '<i class="fas fa-check"></i> Confirmer',
                                    cancelButtonText: 'Annuler',
                                    preConfirm: () => {
                                        return document.getElementById('table-select').value;
                                    }
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        updateReservationStatus(id, 'confirmed', result.value || null);
                                    }
                                });
                            } else {
                                // Pas de tables configurées, confirmation simple
                                Swal.fire({
                                    title: 'Confirmer cette réservation ?',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#10b981',
                                    confirmButtonText: '<i class="fas fa-check"></i> Confirmer',
                                    cancelButtonText: 'Annuler'
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        updateReservationStatus(id, 'confirmed');
                                    }
                                });
                            }
                        });
                } else {
                    // Confirmation simple sans attribution de table
                    Swal.fire({
                        title: 'Confirmer cette réservation ?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        confirmButtonText: '<i class="fas fa-check"></i> Confirmer',
                        cancelButtonText: 'Annuler'
                    }).then(result => {
                        if (result.isConfirmed) {
                            updateReservationStatus(id, 'confirmed');
                        }
                    });
                }
            });
        });
        
        document.querySelectorAll('#tab-dashboard .btn-cancel-reservation').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                Swal.fire({
                    title: 'Annuler cette réservation ?',
                    text: 'Le client sera informé de l\'annulation.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: '<i class="fas fa-times"></i> Annuler la réservation',
                    cancelButtonText: 'Retour'
                }).then(result => {
                    if (result.isConfirmed) {
                        updateReservationStatus(id, 'cancelled');
                    }
                });
            });
        });
        
        // Bouton modifier date/heure - Délégation d'événements
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-edit-datetime')) {
                const btn = e.target.closest('.btn-edit-datetime');
                const id = btn.dataset.id;
                const currentDate = btn.dataset.date;
                const currentTime = btn.dataset.time;
                
                Swal.fire({
                    title: 'Modifier la date et l\'heure',
                    html: `
                        <div style="text-align: left; margin-bottom: 15px;">
                            <label for="new-date" style="display: block; margin-bottom: 8px; font-weight: 600; color: #1f2937; font-size: 0.95rem;">Nouvelle date :</label>
                            <input type="date" id="new-date" value="${currentDate}" style="width: 100%; padding: 10px 12px; border: 2px solid #d1d5db; border-radius: 6px; background: #ffffff; color: #1f2937; font-size: 0.9rem; outline: none; cursor: pointer;" onfocus="this.style.borderColor='#3b82f6'; this.showPicker();" onblur="this.style.borderColor='#d1d5db'" onclick="this.showPicker();">
                        </div>
                        <div style="text-align: left;">
                            <label for="new-time" style="display: block; margin-bottom: 8px; font-weight: 600; color: #1f2937; font-size: 0.95rem;">Nouvelle heure :</label>
                            <input type="time" id="new-time" value="${currentTime}" style="width: 100%; padding: 10px 12px; border: 2px solid #d1d5db; border-radius: 6px; background: #ffffff; color: #1f2937; font-size: 0.9rem; outline: none; cursor: pointer;" onfocus="this.style.borderColor='#3b82f6'; this.showPicker();" onblur="this.style.borderColor='#d1d5db'" onclick="this.showPicker();">
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    confirmButtonText: '<i class="fas fa-check"></i> Modifier',
                    cancelButtonText: 'Annuler',
                    didOpen: () => {
                        // Forcer l'ouverture du calendrier au chargement de la modal
                        const dateInput = document.getElementById('new-date');
                        if (dateInput) {
                            setTimeout(() => {
                                try {
                                    dateInput.showPicker();
                                } catch (e) {
                                    // showPicker() peut ne pas être supporté sur certains navigateurs
                                    console.log('showPicker() non supporté');
                                }
                            }, 100);
                        }
                    },
                    preConfirm: () => {
                        const newDate = document.getElementById('new-date').value;
                        const newTime = document.getElementById('new-time').value;
                        
                        if (!newDate || !newTime) {
                            Swal.showValidationMessage('Veuillez renseigner la date et l\'heure');
                            return false;
                        }
                        
                        return { date: newDate, time: newTime };
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        const csrfToken = document.getElementById('csrf-token').value;
                        const formData = new FormData();
                        formData.append('csrf_token', csrfToken);
                        formData.append('id', id);
                        formData.append('reservation_date', result.value.date);
                        formData.append('reservation_time', result.value.time);
                        
                        fetch('?page=reservation-update-datetime', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                if (data.new_csrf_token) {
                                    document.getElementById('csrf-token').value = data.new_csrf_token;
                                }
                                Swal.fire({
                                    title: 'Date et heure modifiées',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                
                                // Rafraîchir la liste
                                if (typeof window.refreshReservationsList === 'function') {
                                    window.refreshReservationsList();
                                } else {
                                    setTimeout(function() {
                                        window.location.href = '?page=reservations&tab=list';
                                    }, 300);
                                }
                            } else {
                                Swal.fire('Erreur', data.message || 'Une erreur est survenue', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Erreur:', error);
                            Swal.fire('Erreur', 'Erreur de communication avec le serveur', 'error');
                        });
                    }
                });
            }
        });
        
        // Bouton changer de table (dashboard uniquement)
        document.querySelectorAll('#tab-dashboard .btn-change-table').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                
                // Charger les tables disponibles
                fetch('?page=reservation-get-tables')
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.tables && data.tables.length > 0) {
                            // Créer les options du select
                            let tableOptions = '<option value="">Aucune table</option>';
                            data.tables.forEach(table => {
                                let label = `${table.floor_name} - Table ${table.table_number} (${table.capacity_min}-${table.capacity_max} pers.)`;
                                
                                // Ajouter les informations de réservations existantes
                                if (table.reservations && table.reservations.length > 0) {
                                    const times = table.reservations.map(r => r.time).join(', ');
                                    label += ` ⚠️ Déjà réservée : ${times}`;
                                }
                                
                                tableOptions += `<option value="${table.id}">${label}</option>`;
                            });
                            
                            Swal.fire({
                                title: 'Changer la table',
                                html: `
                                    <div style="text-align: left; margin-bottom: 15px;">
                                        <label for="table-select-change" style="display: block; margin-bottom: 10px; font-weight: 600; color: #1f2937; font-size: 0.95rem;">Nouvelle table :</label>
                                        <select id="table-select-change" style="width: 100%; padding: 10px 12px; border: 2px solid #d1d5db; border-radius: 6px; background: #ffffff; color: #1f2937; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; outline: none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                                            ${tableOptions}
                                        </select>
                                    </div>
                                `,
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#d97706',
                                confirmButtonText: '<i class="fas fa-check"></i> Changer',
                                cancelButtonText: 'Annuler',
                                preConfirm: () => {
                                    return document.getElementById('table-select-change').value;
                                }
                            }).then(result => {
                                if (result.isConfirmed) {
                                    // Mettre à jour la table sans changer le statut
                                    const csrfToken = document.getElementById('csrf-token').value;
                                    const formData = new FormData();
                                    formData.append('csrf_token', csrfToken);
                                    formData.append('id', id);
                                    formData.append('status', 'confirmed');
                                    formData.append('table_id', result.value || '');
                                    
                                    fetch('?page=reservation-update-status', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.success) {
                                            if (data.new_csrf_token) {
                                                document.getElementById('csrf-token').value = data.new_csrf_token;
                                            }
                                            Swal.fire({
                                                title: 'Table modifiée',
                                                icon: 'success',
                                                timer: 1500,
                                                showConfirmButton: false
                                            });
                                            loadReservationsForDate(dateInput.value);
                                        } else {
                                            Swal.fire('Erreur', data.message || 'Une erreur est survenue', 'error');
                                        }
                                    });
                                }
                            });
                        } else {
                            Swal.fire('Info', 'Aucune table configurée dans le plan de salle', 'info');
                        }
                    });
            });
        });
    }
    
    function validateAllReservations() {
        // Récupérer toutes les réservations en attente
        const pendingCards = document.querySelectorAll('.pending-reservation-card');
        
        if (pendingCards.length === 0) {
            Swal.fire('Info', 'Aucune réservation en attente à valider', 'info');
            return;
        }
        
        const reservationIds = Array.from(pendingCards).map(card => card.dataset.id);
        
        // Récupérer les paramètres de réservation pour vérifier l'attribution de table
        fetch('?page=reservation-get-settings')
            .then(r => r.json())
            .then(settingsData => {
                if (!settingsData.success) {
                    throw new Error('Impossible de récupérer les paramètres');
                }
                
                const settings = settingsData.settings;
                const assignTableEnabled = settings.booking_assign_table && !settings.booking_auto_confirm;
                
                if (assignTableEnabled) {
                    // Charger les tables disponibles
                    return fetch('?page=reservation-get-tables')
                        .then(r => r.json())
                        .then(data => ({ settings, tablesData: data }));
                } else {
                    return { settings, tablesData: null };
                }
            })
            .then(({ settings, tablesData }) => {
                const assignTableEnabled = settings.booking_assign_table && !settings.booking_auto_confirm;
                
                if (assignTableEnabled && tablesData && tablesData.success && tablesData.tables && tablesData.tables.length > 0) {
                    // Demander l'attribution de table pour chaque réservation
                    showTableAssignmentModal(reservationIds, tablesData.tables);
                } else {
                    // Confirmation simple sans attribution de table
                    confirmAllReservations(reservationIds, null);
                }
            })
            .catch(error => {
                console.error('Erreur lors de la validation en masse:', error);
                Swal.fire({
                    title: 'Erreur',
                    text: 'Impossible de charger les paramètres de réservation.',
                    icon: 'error'
                });
            });
    }
    
    function showTableAssignmentModal(reservationIds, tables) {
        // Récupérer les informations des réservations depuis le DOM
        const reservationsData = reservationIds.map(id => {
            const card = document.querySelector(`.pending-reservation-card[data-id="${id}"]`);
            if (!card) return null;
            
            const nameEl = card.querySelector('.reservation-name');
            const timeEl = card.querySelector('.reservation-time');
            const partyEl = card.querySelector('.reservation-party');
            
            return {
                id: id,
                name: nameEl ? nameEl.textContent.replace(/.*?\s/, '').trim() : 'Client',
                time: timeEl ? timeEl.textContent.replace(/.*?\s/, '').trim() : '',
                party: partyEl ? partyEl.textContent.replace(/.*?\s/, '').trim() : ''
            };
        }).filter(r => r !== null);
        
        // Créer les options du select
        let tableOptions = '<option value="">Aucune table</option>';
        tables.forEach(table => {
            let label = `${table.floor_name} - Table ${table.table_number} (${table.capacity_min}-${table.capacity_max} pers.)`;
            
            if (table.reservations && table.reservations.length > 0) {
                const times = table.reservations.map(r => r.time).join(', ');
                label += ` ⚠️ ${times}`;
            }
            
            tableOptions += `<option value="${table.id}">${label}</option>`;
        });
        
        // Créer le HTML pour chaque réservation avec son propre sélecteur
        let reservationsHtml = '';
        reservationsData.forEach((res, index) => {
            reservationsHtml += `
                <div style="margin-bottom: 15px; padding: 12px; background: #f9fafb; border-radius: 8px; border-left: 3px solid #3b82f6;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <strong style="color: #1f2937;">${res.name}</strong>
                        <span style="color: #6b7280; font-size: 0.85rem;">${res.time} • ${res.party}</span>
                    </div>
                    <select id="table-select-${index}" data-reservation-id="${res.id}" class="table-selector" style="width: 100%; padding: 8px 10px; border: 2px solid #d1d5db; border-radius: 6px; background: #ffffff; color: #1f2937; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; outline: none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                        ${tableOptions}
                    </select>
                </div>
            `;
        });
        
        // Récupérer la date affichée
        const dateInput = document.getElementById('dashboard-date');
        const selectedDate = dateInput ? new Date(dateInput.value) : new Date();
        const dateStr = selectedDate.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        
        Swal.fire({
            title: `Attribuer les tables - ${dateStr}`,
            html: `
                <p style="margin-bottom: 15px; color: #6b7280;">Choisissez une table pour chaque réservation :</p>
                <div style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
                    ${reservationsHtml}
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: '<i class="fas fa-check-double"></i> Tout valider',
            cancelButtonText: 'Annuler',
            width: '600px',
            preConfirm: () => {
                // Récupérer les tables sélectionnées pour chaque réservation
                const tableAssignments = {};
                document.querySelectorAll('.table-selector').forEach(select => {
                    const reservationId = select.dataset.reservationId;
                    const tableId = select.value || null;
                    tableAssignments[reservationId] = tableId;
                });
                return tableAssignments;
            }
        }).then(result => {
            if (result.isConfirmed) {
                confirmAllReservationsIndividually(result.value);
            }
        });
    }
    
    async function confirmAllReservationsIndividually(tableAssignments) {
        const reservationIds = Object.keys(tableAssignments);
        
        // Afficher un loader
        Swal.fire({
            title: 'Validation en cours...',
            html: `<div class="swal-progress">0 / ${reservationIds.length}</div>`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Valider toutes les réservations une par une SÉQUENTIELLEMENT avec leur table respective
        let completed = 0;
        let errors = 0;
        let errorMessages = [];
        
        // Traiter les réservations une par une pour éviter les problèmes de CSRF token
        for (const id of reservationIds) {
            try {
                // Récupérer le token CSRF actuel à chaque itération
                const csrfToken = document.getElementById('csrf-token').value;
                
                const data = new FormData();
                data.append('csrf_token', csrfToken);
                data.append('id', id);
                data.append('status', 'confirmed');
                
                const tableId = tableAssignments[id];
                if (tableId) {
                    data.append('table_id', tableId);
                }
                
                const response = await fetch('?page=reservation-update-status', {
                    method: 'POST',
                    body: data
                });
                
                const result = await response.json();
                
                completed++;
                const progressEl = document.querySelector('.swal-progress');
                if (progressEl) {
                    progressEl.textContent = `${completed} / ${reservationIds.length}`;
                }
                
                if (!result.success) {
                    errors++;
                    errorMessages.push(`Réservation #${id}: ${result.message || 'Erreur inconnue'}`);
                } else {
                    // Mettre à jour le token CSRF si le serveur en renvoie un nouveau
                    if (result.new_csrf_token) {
                        const csrfTokenElement = document.getElementById('csrf-token');
                        if (csrfTokenElement) {
                            csrfTokenElement.value = result.new_csrf_token;
                        }
                    }
                }
            } catch (error) {
                completed++;
                errors++;
                const progressEl = document.querySelector('.swal-progress');
                if (progressEl) {
                    progressEl.textContent = `${completed} / ${reservationIds.length}`;
                }
                errorMessages.push(`Réservation #${id}: Erreur réseau`);
                console.error(`Erreur pour réservation ${id}:`, error);
            }
        }
        
        // Afficher le résultat final
        if (errors === 0) {
            Swal.fire({
                title: 'Succès !',
                text: `${reservationIds.length} réservation(s) confirmée(s)`,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                title: 'Terminé avec erreurs',
                html: `
                    <p>${reservationIds.length - errors} réservation(s) confirmée(s), ${errors} erreur(s)</p>
                    <details style="margin-top: 15px; text-align: left;">
                        <summary style="cursor: pointer; font-weight: 600; color: #ef4444;">Voir les erreurs</summary>
                        <ul style="margin-top: 10px; font-size: 0.85rem; color: #6b7280;">
                            ${errorMessages.map(msg => `<li>${msg}</li>`).join('')}
                        </ul>
                    </details>
                `,
                icon: 'warning'
            });
        }
        
        // Rafraîchir l'affichage
        const dateInput = document.getElementById('dashboard-date');
        if (dateInput) {
            loadReservationsForDate(dateInput.value);
        }
    }
    
    function updateReservationStatus(id, status, tableId = null) {
        const csrfToken = document.getElementById('csrf-token').value;
        const data = new FormData();
        data.append('csrf_token', csrfToken);
        data.append('id', id);
        data.append('status', status);
        
        // Ajouter l'ID de la table si fourni
        if (tableId) {
            data.append('table_id', tableId);
        }
        
        fetch('?page=reservation-update-status', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour le token CSRF
                if (data.new_csrf_token) {
                    document.getElementById('csrf-token').value = data.new_csrf_token;
                }
                Swal.fire({
                    title: status === 'confirmed' ? 'Réservation confirmée' : 'Réservation annulée',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                loadReservationsForDate(dateInput.value);
                
                // Rafraîchir le badge de notification
                updateNotificationBadge();
            } else {
                Swal.fire('Erreur', data.message || 'Une erreur est survenue', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            Swal.fire('Erreur', 'Erreur lors de la mise à jour', 'error');
        });
    }
    
    // Fonction pour mettre à jour le badge de notification
    function updateNotificationBadge() {
        fetch('?page=get-pending-reservations')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = data.reservations ? data.reservations.length : 0;
                    let notificationToggle = document.getElementById('notification-toggle');
                    
                    if (count > 0) {
                        // Si le bouton n'existe pas, le créer
                        if (!notificationToggle) {
                            const floatingButtons = document.querySelector('.floating-buttons');
                            if (floatingButtons) {
                                notificationToggle = document.createElement('button');
                                notificationToggle.type = 'button';
                                notificationToggle.className = 'notification-toggle-floating';
                                notificationToggle.id = 'notification-toggle';
                                notificationToggle.title = 'Réservations en attente';
                                notificationToggle.innerHTML = '<i class="fas fa-bell"></i><span class="notification-badge" id="notification-count">' + count + '</span>';
                                floatingButtons.insertBefore(notificationToggle, floatingButtons.firstChild);
                            }
                        } else {
                            // Mettre à jour le compteur et afficher le bouton
                            const notificationCount = document.getElementById('notification-count');
                            if (notificationCount) {
                                notificationCount.textContent = count;
                            }
                            notificationToggle.style.display = '';
                        }
                    } else if (count === 0 && notificationToggle) {
                        // Cacher le bouton si count = 0
                        notificationToggle.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Erreur mise à jour badge:', error);
            });
    }
    
    // Événements initiaux
    if (dateInput) {
        dateInput.addEventListener('change', updateDateDisplay);
    }
    if (prevBtn) {
        prevBtn.addEventListener('click', () => changeDate(-1));
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', () => changeDate(1));
    }
    
    // Charger les réservations pour la date initiale (aujourd'hui)
    loadReservationsForDate(dateInput.value);
    
    // Exposer la fonction globalement pour les appels depuis les notifications
    window.loadReservationsForDate = loadReservationsForDate;
    
    // Polling automatique pour rafraîchir les réservations en attente toutes les 15 secondes
    let previousPendingCount = 0;
    
    function autoRefreshPendingReservations() {
        const dashboardTab = document.querySelector('[data-tab="tab-dashboard"]');
        const isDashboardActive = dashboardTab && dashboardTab.classList.contains('active');
        
        // Récupérer la date actuellement affichée
        const currentDate = dateInput.value;
        
        fetch(`?page=get-day-reservations&date=${currentDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const newPendingCount = data.pendingReservations ? data.pendingReservations.length : 0;
                    const hasChanged = newPendingCount !== previousPendingCount;
                    
                    // Toujours mettre à jour le badge de notification
                    updateNotificationBadge();
                    
                    // Si le nombre de réservations en attente a changé
                    if (hasChanged) {
                        console.log(`[Auto-refresh] Changement détecté: ${previousPendingCount} → ${newPendingCount} réservations en attente`);
                        
                        // Si de nouvelles réservations sont arrivées, afficher une notification
                        if (newPendingCount > previousPendingCount) {
                            const diff = newPendingCount - previousPendingCount;
                            showAutoRefreshNotification(diff);
                        }
                        
                        previousPendingCount = newPendingCount;
                        
                        // Ne rafraîchir l'affichage que si on est sur l'onglet Dashboard
                        if (isDashboardActive) {
                            loadReservationsForDate(currentDate);
                        }
                    }
                }
            })
            .catch(error => {
                console.error('[Auto-refresh] Erreur:', error);
            });
    }
    
    function showAutoRefreshNotification(count) {
        // Afficher une notification discrète
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `${count} nouvelle(s) réservation(s) en attente`,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    }
    
    // Démarrer le polling toutes les 15 secondes
    setInterval(autoRefreshPendingReservations, 15000);
    
    // Initialiser le compteur au chargement et faire un premier check immédiat
    fetch(`?page=get-day-reservations&date=${dateInput.value}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.pendingReservations) {
                previousPendingCount = data.pendingReservations.length;
                
                // Si des réservations en attente existent dès le chargement, afficher la notification
                if (previousPendingCount > 0) {
                    showAutoRefreshNotification(previousPendingCount);
                }
            }
        });
    
    // Faire un premier check après 2 secondes pour détecter les nouvelles réservations
    setTimeout(autoRefreshPendingReservations, 2000);
    
    // Fonction pour rafraîchir instantanément le tableau de l'onglet liste
    window.refreshReservationsList = function() {
        const currentUrl = window.location.href;
        fetch(currentUrl)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(html, 'text/html');
                const currentTableContainer = document.querySelector('.reservations-table-container');
                const newTableContainer = newDoc.querySelector('.reservations-table-container');
                const currentEmptyState = document.querySelector('#tab-list .empty-state');
                const newEmptyState = newDoc.querySelector('#tab-list .empty-state');
                
                // Remplacer le tableau ou l'empty state
                if (newTableContainer && currentTableContainer) {
                    currentTableContainer.innerHTML = newTableContainer.innerHTML;
                } else if (newEmptyState && !currentTableContainer) {
                    // Afficher l'empty state si le tableau n'existe plus
                    const tabList = document.getElementById('tab-list');
                    const filtersDiv = tabList.querySelector('.reservations-filters');
                    if (filtersDiv && filtersDiv.nextElementSibling) {
                        filtersDiv.nextElementSibling.remove();
                    }
                    const emptyDiv = document.createElement('div');
                    emptyDiv.innerHTML = newEmptyState.outerHTML;
                    filtersDiv.after(emptyDiv.firstChild);
                } else if (newTableContainer && currentEmptyState) {
                    // Remplacer l'empty state par le tableau
                    currentEmptyState.remove();
                    const tabList = document.getElementById('tab-list');
                    const filtersDiv = tabList.querySelector('.reservations-filters');
                    const tableDiv = document.createElement('div');
                    tableDiv.innerHTML = newTableContainer.outerHTML;
                    filtersDiv.after(tableDiv.firstChild);
                }
            })
            .catch(error => {
                console.error('[Refresh list] Erreur:', error);
            });
    };
    
    // Polling pour l'onglet Réservations (tab-list) pour détecter les nouvelles réservations
    setInterval(function() {
        const listTab = document.querySelector('[data-tab="tab-list"]');
        const isListTabActive = listTab && listTab.classList.contains('active');
        
        if (isListTabActive && typeof window.refreshReservationsList === 'function') {
            window.refreshReservationsList();
        }
    }, 15000); // Vérifier toutes les 15 secondes
});
</script>

<!-- Définition des étapes du tour guidé pour cette page -->
<script>
const tourSteps = [
    {
        element: '.reservations-header',
        title: 'Gestion des réservations',
        content: 'Bienvenue dans votre système de réservations en ligne ! Gérez toutes vos réservations depuis cette interface centralisée.'
    },
    {
        element: '.reservations-tabs',
        title: 'Navigation par onglets',
        content: '<strong>3 onglets disponibles :</strong><br>• <strong>Tableau de bord</strong> : Vue d\'ensemble et réservations du jour<br>• <strong>Réservations</strong> : Liste complète avec filtres<br>• <strong>Paramètres</strong> : Configuration du système'
    },
    {
        element: '#tab-dashboard',
        title: 'Tableau de bord',
        content: 'Consultez vos <strong>statistiques clés</strong> (en attente, confirmées, terminées) et visualisez les <strong>réservations du jour</strong> avec navigation par date.',
        beforeShow: function() {
            document.querySelector('[data-tab="tab-dashboard"]').click();
        }
    },
    {
        element: '.reservations-kpis',
        title: 'Statistiques en temps réel',
        content: 'Suivez vos <strong>KPIs</strong> : réservations du jour, en attente, confirmées, couverts et cette semaine. Mise à jour automatique.'
    },
    {
        element: '.today-date-selector',
        title: 'Sélecteur de date',
        content: 'Naviguez entre les jours avec les <strong>flèches</strong> ou sélectionnez une <strong>date précise</strong>. Les réservations s\'affichent automatiquement.'
    },
    {
        element: '#tab-list',
        title: 'Liste des réservations',
        content: 'Accédez à <strong>toutes vos réservations</strong> avec des outils de filtrage et de recherche avancés.',
        beforeShow: function() {
            document.querySelector('[data-tab="tab-list"]').click();
        }
    },
    {
        element: '.filters-form',
        title: 'Filtres de recherche',
        content: 'Filtrez par <strong>statut</strong>, <strong>date</strong> ou recherchez par <strong>nom/email/téléphone</strong>. Cliquez sur "Réinitialiser" pour tout effacer.'
    },
    {
        element: '.filter-actions',
        title: 'Actions rapides',
        content: 'Utilisez les <strong>boutons d\'action</strong> :<br>• <i class="fas fa-search"></i> Filtrer / <i class="fas fa-undo"></i> Réinitialiser<br>• <i class="fas fa-flag-checkered"></i> Marquer toutes comme terminées<br>• <i class="fas fa-trash-alt"></i> Supprimer réservations terminées<br>• <i class="fas fa-trash"></i> Supprimer toutes les réservations'
    },
    {
        element: '#tab-settings',
        title: 'Paramètres de réservation',
        content: 'Configurez votre système : <strong>créneaux horaires</strong>, <strong>capacité</strong>, <strong>validation automatique</strong>, <strong>jours de fermeture</strong> et plus encore.',
        beforeShow: function() {
            document.querySelector('[data-tab="tab-settings"]').click();
        }
    },
    {
        element: '#setting-booking-enabled',
        title: 'Activer/Désactiver les réservations',
        content: 'Activez ou désactivez le <strong>formulaire de réservation</strong> sur votre site vitrine en un clic.'
    },
    {
        element: '#setting-booking-auto-confirm',
        title: 'Validation automatique',
        content: 'Si activé, les nouvelles réservations sont <strong>automatiquement confirmées</strong> sans intervention manuelle.'
    },
    {
        element: '#setting-booking-auto-complete',
        title: 'Marquage automatique',
        content: 'Marquez automatiquement les réservations comme <strong>terminées</strong> après la durée du repas (configurable). Nécessite un CRON job.'
    },
    {
        element: 'input[name="booking_min_party"]',
        title: 'Nombre de personnes',
        content: 'Définissez le <strong>nombre minimum et maximum</strong> de personnes par réservation (ex: 1 à 10).'
    },
    {
        element: 'textarea[name="booking_time_slots"]',
        title: 'Créneaux horaires',
        content: 'Configurez vos <strong>créneaux disponibles</strong> au format HH:MM, séparés par des virgules.<br>Ex: <code>12:00,12:30,13:00,19:00,19:30,20:00</code>'
    },
    {
        element: '.days-checkboxes',
        title: 'Jours de fermeture',
        content: 'Sélectionnez les <strong>jours de la semaine</strong> où votre restaurant est fermé. Les clients ne pourront pas réserver ces jours-là.'
    },
    {
        element: '.settings-actions',
        title: 'Enregistrer les modifications',
        content: 'N\'oubliez pas de cliquer sur <strong>"Enregistrer les paramètres"</strong> après chaque modification. Un message de confirmation apparaîtra.'
    },
    {
        element: '#notification-toggle',
        title: '🔔 Notifications en temps réel',
        content: 'Ce bouton affiche le <strong>nombre de réservations en attente</strong>. Il clignote pour attirer votre attention.<br><br>Les notifications sont <strong>automatiques</strong> : vous recevez une alerte <strong>toutes les 10 secondes</strong> maximum quand une nouvelle réservation arrive !<br><br>Cliquez dessus pour voir la liste et <strong>confirmer/refuser</strong> rapidement. Vous pouvez aussi <strong>activer/désactiver le son</strong> des notifications.',
        placement: 'bottom',
        beforeShow: function() {
            // Afficher temporairement le bouton de notification même s'il n'y a pas de réservations
            let notifToggle = document.getElementById('notification-toggle');
            
            // Si le bouton n'existe pas, le créer temporairement
            if (!notifToggle) {
                const floatingButtons = document.querySelector('.floating-buttons');
                if (floatingButtons) {
                    notifToggle = document.createElement('button');
                    notifToggle.type = 'button';
                    notifToggle.className = 'notification-toggle-floating';
                    notifToggle.id = 'notification-toggle';
                    notifToggle.title = 'Réservations en attente';
                    notifToggle.innerHTML = '<i class="fas fa-bell"></i><span class="notification-badge" id="notification-count">1</span>';
                    notifToggle.setAttribute('data-tour-temp', 'true');
                    floatingButtons.insertBefore(notifToggle, floatingButtons.firstChild);
                }
            } else {
                // Le bouton existe, juste s'assurer qu'il est visible
                notifToggle.style.display = '';
                const badge = document.getElementById('notification-count');
                if (badge) {
                    badge.setAttribute('data-original-count', badge.textContent);
                    badge.textContent = '1';
                }
            }
            
            if (notifToggle) {
                // Scroll vers le bouton
                setTimeout(() => {
                    notifToggle.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            }
        },
        afterHide: function() {
            const notifToggle = document.getElementById('notification-toggle');
            
            if (notifToggle) {
                // Si c'était un bouton temporaire, le supprimer
                if (notifToggle.getAttribute('data-tour-temp') === 'true') {
                    notifToggle.remove();
                } else {
                    // Restaurer le compteur original
                    const badge = document.getElementById('notification-count');
                    if (badge && badge.hasAttribute('data-original-count')) {
                        badge.textContent = badge.getAttribute('data-original-count');
                        badge.removeAttribute('data-original-count');
                    }
                    
                    // Masquer le bouton s'il n'y a vraiment pas de réservations
                    const actualCount = parseInt(badge?.textContent || '0');
                    if (actualCount === 0) {
                        notifToggle.style.display = 'none';
                    }
                }
            }
        }
    }
];
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
