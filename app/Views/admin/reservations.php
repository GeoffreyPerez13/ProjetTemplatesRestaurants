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
                <h4><i class="fas fa-clock"></i> Réservations en attente</h4>
                <div class="pending-reservations-grid">
                    <!-- Contenu rempli par JavaScript -->
                </div>
            </div>
            <?php 
            $nonPendingReservations = array_filter($todayReservations, function($r) {
                return $r['status'] !== 'pending';
            });
            
            if (empty($nonPendingReservations) && empty($todayReservations)): ?>
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
                    <a href="?page=reservations" class="btn small btn-outline"><i class="fas fa-undo"></i> Réinitialiser</a>
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
                                <td data-label="Couverts"><i class="fas fa-users"></i> <?= $r['party_size'] ?></td>
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
                                            <button class="btn small btn-complete-reservation" data-id="<?= $r['id'] ?>" title="Terminée"><i class="fas fa-flag-checkered"></i></button>
                                            <button class="btn small danger btn-noshow-reservation" data-id="<?= $r['id'] ?>" title="Absent"><i class="fas fa-user-slash"></i></button>
                                        <?php endif; ?>
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
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <!-- Activation -->
                <div class="setting-row">
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
<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrf_token) ?>">

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
                        pendingHtml = '<div id="pending-reservations-section" style="display: block;"><h4><i class="fas fa-clock"></i> Réservations en attente</h4><div class="pending-reservations-grid">';
                        data.pendingReservations.forEach(r => {
                            pendingHtml += `
                                <div class="reservation-card status-pending" data-id="${r.id}">
                                    <div class="reservation-card-header">
                                        <span class="reservation-time"><i class="fas fa-clock"></i> ${r.reservation_time.substring(0, 5)}</span>
                                        <span class="reservation-status badge-warning">En attente</span>
                                    </div>
                                    <div class="reservation-card-body">
                                        <div class="reservation-info">
                                            <span class="reservation-name"><i class="fas fa-user"></i> ${r.customer_name}</span>
                                            <span class="reservation-party"><i class="fas fa-users"></i> ${r.party_size} pers.</span>
                                        </div>
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
        document.querySelectorAll('.btn-confirm-reservation').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
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
            });
        });
        
        document.querySelectorAll('.btn-cancel-reservation').forEach(btn => {
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
    }
    
    function updateReservationStatus(id, status) {
        const csrfToken = document.getElementById('csrf-token').value;
        const data = new FormData();
        data.append('csrf_token', csrfToken);
        data.append('id', id);
        data.append('status', status);
        
        fetch('?page=reservation-update-status', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: status === 'confirmed' ? 'Réservation confirmée' : 'Réservation annulée',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                loadReservationsForDate(dateInput.value);
            } else {
                Swal.fire('Erreur', data.message || 'Une erreur est survenue', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            Swal.fire('Erreur', 'Erreur lors de la mise à jour', 'error');
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
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
