<?php
$title = "Gestion des clients Premium";
$scripts = [
    "js/admin/manage-clients.js"
];

require __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../Models/ClientSubscription.php';

$subscriptionModel = new ClientSubscription($pdo);
$clients = $subscriptionModel->getAllClients();
$stats = $subscriptionModel->getSubscriptionStats();
?>

<a class="btn-back" href="?page=dashboard">Retour</a>

<div class="manage-clients-container">
    <div class="page-header">
        <h1><i class="fas fa-crown"></i> Gestion des clients Premium</h1>
        <p>Activez et gérez les abonnements premium de vos clients</p>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3><?= count($clients) ?></h3>
                <p>Total clients</p>
            </div>
        </div>
        <div class="stat-card premium">
            <div class="stat-icon">
                <i class="fas fa-crown"></i>
            </div>
            <div class="stat-info">
                <h3><?= array_sum(array_column(array_filter($stats, fn($s) => $s['status'] === 'active' && $s['plan_type'] !== 'free'), 'active_count')) ?></h3>
                <p>Clients Premium actifs</p>
            </div>
        </div>
        <div class="stat-card revenue">
            <div class="stat-icon">
                <i class="fas fa-euro-sign"></i>
            </div>
            <div class="stat-info">
                <h3><?= number_format(array_sum(array_column(array_filter($stats, fn($s) => $s['status'] === 'active' && $s['plan_type'] !== 'free'), 'active_count')) * 19, 0, ',', ' ') ?></h3>
                <p>Revenue mensuel estimé</p>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="filters-section">
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">Tous (<?= count($clients) ?>)</button>
            <button class="filter-tab" data-filter="free">Free (<?= count(array_filter($clients, fn($c) => $c['plan_type'] === 'free')) ?>)</button>
            <button class="filter-tab" data-filter="premium">Premium (<?= count(array_filter($clients, fn($c) => $c['plan_type'] === 'premium')) ?>)</button>
            <button class="filter-tab" data-filter="pro">Pro (<?= count(array_filter($clients, fn($c) => $c['plan_type'] === 'pro')) ?>)</button>
        </div>
    </div>

    <!-- Liste des clients -->
    <div class="clients-table-container">
        <table class="clients-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Restaurant</th>
                    <th>Plan</th>
                    <th>Statut</th>
                    <th>Fonctionnalités</th>
                    <th>Début</th>
                    <th>Expiration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr class="client-row" data-plan="<?= $client['plan_type'] ?>" data-status="<?= $client['status'] ?>">
                        <td>
                            <div class="client-info">
                                <div class="client-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="client-name"><?= htmlspecialchars($client['username']) ?></div>
                                    <div class="client-email"><?= htmlspecialchars($client['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($client['restaurant_name'] ?? 'Non défini') ?></td>
                        <td>
                            <span class="plan-badge plan-<?= $client['plan_type'] ?>">
                                <?= strtoupper($client['plan_type']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?= $client['status'] ?>">
                                <?= ucfirst($client['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $features = json_decode($client['features_enabled'] ?? '[]', true);
                            if (!empty($features)):
                            ?>
                                <div class="features-list">
                                    <?php foreach ($features as $feature): ?>
                                        <span class="feature-tag"><?= $feature ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="no-features">Aucune</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $client['started_at'] ? date('d/m/Y', strtotime($client['started_at'])) : '-' ?></td>
                        <td>
                            <?php if ($client['expires_at']): ?>
                                <span class="expiry-date <?= strtotime($client['expires_at']) < time() ? 'expired' : '' ?>">
                                    <?= date('d/m/Y', strtotime($client['expires_at'])) ?>
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions-dropdown">
                                <button class="actions-btn" data-client="<?= $client['admin_id'] ?>">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu" id="dropdown-<?= $client['admin_id'] ?>">
                                    <?php if ($client['plan_type'] === 'free'): ?>
                                        <button class="dropdown-item activate-premium" data-client="<?= $client['admin_id'] ?>" data-plan="premium">
                                            <i class="fas fa-crown"></i>
                                            Activer Premium
                                        </button>
                                        <button class="dropdown-item activate-pro" data-client="<?= $client['admin_id'] ?>" data-plan="pro">
                                            <i class="fas fa-star"></i>
                                            Activer Pro
                                        </button>
                                    <?php else: ?>
                                        <button class="dropdown-item view-details" data-client="<?= $client['admin_id'] ?>">
                                            <i class="fas fa-eye"></i>
                                            Voir détails
                                        </button>
                                        <button class="dropdown-item extend-subscription" data-client="<?= $client['admin_id'] ?>">
                                            <i class="fas fa-calendar-plus"></i>
                                            Prolonger
                                        </button>
                                        <button class="dropdown-item danger cancel-subscription" data-client="<?= $client['admin_id'] ?>">
                                            <i class="fas fa-times"></i>
                                            Annuler
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal d'activation Premium -->
<div id="activateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Activer l'abonnement Premium</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="client-preview">
                <div class="client-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="client-details">
                    <h4 id="modal-client-name"></h4>
                    <p id="modal-client-restaurant"></p>
                </div>
            </div>
            
            <div class="plan-selection">
                <h4>Choisir un plan</h4>
                <div class="plan-options">
                    <div class="plan-option" data-plan="premium">
                        <div class="plan-header">
                            <h5>Premium</h5>
                            <span class="plan-price">19€/mois</span>
                        </div>
                        <ul class="plan-features">
                            <li><i class="fas fa-check"></i> Avis Google</li>
                            <li><i class="fas fa-check"></i> Support prioritaire</li>
                            <li><i class="fas fa-check"></i> Analytics de base</li>
                        </ul>
                    </div>
                    <div class="plan-option" data-plan="pro">
                        <div class="plan-header">
                            <h5>Pro</h5>
                            <span class="plan-price">39€/mois</span>
                        </div>
                        <ul class="plan-features">
                            <li><i class="fas fa-check"></i> Tout le plan Premium</li>
                            <li><i class="fas fa-check"></i> Statistiques avancées</li>
                            <li><i class="fas fa-check"></i> Réservations en ligne</li>
                            <li><i class="fas fa-check"></i> Intégration livraison</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="subscription-duration">
                <label for="duration">Durée de l'abonnement</label>
                <select id="duration" name="duration">
                    <option value="1">1 mois</option>
                    <option value="3">3 mois (-10%)</option>
                    <option value="6">6 mois (-15%)</option>
                    <option value="12">12 mois (-20%)</option>
                </select>
            </div>
            
            <div class="price-summary">
                <div class="price-row">
                    <span>Prix de base:</span>
                    <span id="base-price">19€</span>
                </div>
                <div class="price-row discount">
                    <span>Réduction:</span>
                    <span id="discount">0€</span>
                </div>
                <div class="price-row total">
                    <span>Total:</span>
                    <span id="total-price">19€</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn secondary modal-close">Annuler</button>
            <button class="btn premium-btn" id="confirm-activation">
                <i class="fas fa-crown"></i>
                Activer l'abonnement
            </button>
        </div>
    </div>
</div>

<style>
.manage-clients-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-xl);
}

.page-header {
    text-align: center;
    margin-bottom: var(--spacing-xl);
}

.page-header h1 {
    color: var(--color-text);
    margin-bottom: var(--spacing-sm);
    font-size: 2rem;
}

.page-header p {
    color: var(--color-text-light);
    font-size: 1.1rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.stat-card {
    background: var(--color-bg-alt);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.stat-card.premium {
    border-color: var(--color-primary);
    background: linear-gradient(135deg, var(--color-bg-alt), rgba(212, 168, 83, 0.1));
}

.stat-card.revenue {
    border-color: #10b981;
    background: linear-gradient(135deg, var(--color-bg-alt), rgba(16, 185, 129, 0.1));
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: var(--color-bg-warm);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text);
    font-size: 1.5rem;
}

.stat-card.premium .stat-icon {
    background: linear-gradient(135deg, var(--color-primary), #d4a853);
    color: white;
}

.stat-card.revenue .stat-icon {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.stat-info h3 {
    margin: 0;
    color: var(--color-text);
    font-size: 2rem;
    font-weight: 700;
}

.stat-info p {
    margin: 0;
    color: var(--color-text-light);
    font-size: 0.9rem;
}

.filters-section {
    margin-bottom: var(--spacing-lg);
}

.filter-tabs {
    display: flex;
    gap: var(--spacing-sm);
    border-bottom: 1px solid var(--color-border);
    padding-bottom: var(--spacing-sm);
}

.filter-tab {
    background: none;
    border: none;
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: var(--radius-md) var(--radius-md) 0 0;
    color: var(--color-text-light);
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-tab:hover {
    background: var(--color-bg-alt);
}

.filter-tab.active {
    background: var(--color-primary);
    color: white;
}

.clients-table-container {
    background: var(--color-bg-alt);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.clients-table {
    width: 100%;
    border-collapse: collapse;
}

.clients-table th {
    background: var(--color-bg-warm);
    padding: var(--spacing-md);
    text-align: left;
    font-weight: 600;
    color: var(--color-text);
    border-bottom: 1px solid var(--color-border);
}

.clients-table td {
    padding: var(--spacing-md);
    border-bottom: 1px solid var(--color-border);
}

.client-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.client-avatar {
    width: 40px;
    height: 40px;
    background: var(--color-bg-warm);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-light);
}

.client-name {
    font-weight: 600;
    color: var(--color-text);
}

.client-email {
    font-size: 0.85rem;
    color: var(--color-text-light);
}

.plan-badge {
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-full);
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.plan-free {
    background: var(--color-bg-warm);
    color: var(--color-text-light);
}

.plan-premium {
    background: linear-gradient(135deg, var(--color-primary), #d4a853);
    color: white;
}

.plan-pro {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.status-badge {
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-full);
    font-size: 0.8rem;
    font-weight: 500;
}

.status-active {
    background: var(--color-success);
    color: white;
}

.status-inactive {
    background: var(--color-bg-warm);
    color: var(--color-text-light);
}

.status-cancelled {
    background: var(--color-danger);
    color: white;
}

.features-list {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-xs);
}

.feature-tag {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    padding: 2px var(--spacing-xs);
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    color: var(--color-text);
}

.no-features {
    color: var(--color-text-muted);
    font-style: italic;
    font-size: 0.85rem;
}

.expiry-date.expired {
    color: var(--color-danger);
    font-weight: 600;
}

.actions-dropdown {
    position: relative;
}

.actions-btn {
    background: none;
    border: none;
    padding: var(--spacing-xs);
    cursor: pointer;
    color: var(--color-text-light);
}

.dropdown-menu {
    position: absolute;
    right: 0;
    top: 100%;
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    min-width: 180px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    display: none;
}

.dropdown-menu.show {
    display: block;
}

.dropdown-item {
    width: 100%;
    background: none;
    border: none;
    padding: var(--spacing-sm) var(--spacing-md);
    text-align: left;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    color: var(--color-text);
    transition: background 0.2s ease;
}

.dropdown-item:hover {
    background: var(--color-bg-alt);
}

.dropdown-item.danger {
    color: var(--color-danger);
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: var(--color-bg);
    border-radius: var(--radius-lg);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--color-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: var(--color-text);
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--color-text-light);
}

.modal-body {
    padding: var(--spacing-lg);
}

.client-preview {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background: var(--color-bg-alt);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-lg);
}

.client-preview .client-avatar {
    width: 50px;
    height: 50px;
    font-size: 1.2rem;
}

.client-details h4 {
    margin: 0;
    color: var(--color-text);
}

.client-details p {
    margin: 0;
    color: var(--color-text-light);
}

.plan-selection h4 {
    margin: 0 0 var(--spacing-md) 0;
    color: var(--color-text);
}

.plan-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

.plan-option {
    border: 2px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--spacing-md);
    cursor: pointer;
    transition: all 0.3s ease;
}

.plan-option:hover {
    border-color: var(--color-primary);
}

.plan-option.selected {
    border-color: var(--color-primary);
    background: rgba(212, 168, 83, 0.1);
}

.plan-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-md);
}

.plan-header h5 {
    margin: 0;
    color: var(--color-text);
}

.plan-price {
    font-weight: 700;
    color: var(--color-primary);
}

.plan-features {
    list-style: none;
    padding: 0;
    margin: 0;
}

.plan-features li {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-xs);
    color: var(--color-text-light);
    font-size: 0.9rem;
}

.plan-features i {
    color: var(--color-success);
}

.subscription-duration {
    margin-bottom: var(--spacing-lg);
}

.subscription-duration label {
    display: block;
    margin-bottom: var(--spacing-sm);
    color: var(--color-text);
    font-weight: 500;
}

.subscription-duration select {
    width: 100%;
    padding: var(--spacing-sm);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    background: var(--color-bg);
    color: var(--color-text);
}

.price-summary {
    background: var(--color-bg-alt);
    border-radius: var(--radius-md);
    padding: var(--spacing-md);
}

.price-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: var(--spacing-sm);
}

.price-row.discount {
    color: var(--color-success);
}

.price-row.total {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--color-text);
    padding-top: var(--spacing-sm);
    border-top: 1px solid var(--color-border);
}

.modal-footer {
    padding: var(--spacing-lg);
    border-top: 1px solid var(--color-border);
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-sm);
}

.premium-btn {
    background: linear-gradient(135deg, var(--color-primary), #d4a853);
    color: white;
    border: none;
}

@media (max-width: 768px) {
    .manage-clients-container {
        padding: var(--spacing-md);
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .plan-options {
        grid-template-columns: 1fr;
    }
    
    .clients-table {
        font-size: 0.85rem;
    }
    
    .clients-table th,
    .clients-table td {
        padding: var(--spacing-sm);
    }
}
</style>

<script src="assets/js/admin/manage-clients.js"></script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
