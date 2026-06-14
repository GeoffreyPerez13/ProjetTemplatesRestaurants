<?php
$title = "Dashboard Feedbacks Beta";
$styles = [];
$scripts = [];
require __DIR__ . '/../partials/header.php';
?>

<a class="btn-back" href="?page=dashboard">Retour</a>

<?php
// Labels lisibles
$easeLabels = [
    'very_easy' => 'Très facile, intuitive',
    'easy' => 'Facile, quelques hésitations',
    'moderate' => 'Moyen, j\'ai dû chercher',
    'difficult' => 'Difficile, pas toujours clair',
    'very_difficult' => 'Très difficile',
];

$recommendLabels = [
    'yes_definitely' => 'Oui, sans hésiter',
    'yes_probably' => 'Oui, probablement',
    'not_sure' => 'Je ne sais pas encore',
    'no_probably' => 'Probablement pas',
    'no_definitely' => 'Non',
];

$featureLabels = [
    'carte' => 'Gestion de la carte',
    'templates' => 'Choix du template',
    'reservations' => 'Réservations en ligne',
    'menus' => 'Menus du jour',
    'stats' => 'Statistiques',
    'contact' => 'Page contact',
    'services' => 'Services & paiements',
    'mobile' => 'Version mobile',
];
?>

<div class="fb-dash" style="max-width: 900px; margin: 20px auto; padding: 0 20px;">
    <h2 style="margin-bottom: 6px;"><i class="fas fa-chart-bar"></i> Dashboard Feedbacks Beta</h2>
    <p style="color: var(--color-text-muted); margin-bottom: 24px;"><?= $stats['total'] ?> retour(s) reçu(s) au total</p>

    <?php if ($stats['total'] === 0): ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--color-text-muted);">
            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 16px; opacity: 0.4;"></i>
            <p>Aucun feedback reçu pour le moment.</p>
        </div>
    <?php else: ?>

    <!-- ===== STATISTIQUES GLOBALES ===== -->
    <div class="fb-stats-grid">
        <!-- Note moyenne -->
        <div class="fb-stat-card">
            <div class="fb-stat-icon"><i class="fas fa-star"></i></div>
            <div class="fb-stat-value"><?= $stats['avg_rating'] ?>/5</div>
            <div class="fb-stat-label">Note moyenne</div>
        </div>

        <!-- Facilité d'utilisation la plus choisie -->
        <div class="fb-stat-card">
            <div class="fb-stat-icon"><i class="fas fa-hand-pointer"></i></div>
            <div class="fb-stat-value"><?= $easeLabels[array_key_first($stats['ease_of_use'])] ?? '-' ?></div>
            <div class="fb-stat-label">Facilité la plus citée</div>
        </div>

        <!-- Recommandation la plus choisie -->
        <div class="fb-stat-card">
            <div class="fb-stat-icon"><i class="fas fa-share-alt"></i></div>
            <div class="fb-stat-value"><?= $recommendLabels[array_key_first($stats['recommendations'])] ?? '-' ?></div>
            <div class="fb-stat-label">Recommandation majoritaire</div>
        </div>
    </div>

    <!-- ===== DÉTAIL STATISTIQUES ===== -->
    <div class="fb-details-grid">
        <!-- Facilité d'utilisation -->
        <div class="fb-detail-card">
            <h3><i class="fas fa-hand-pointer"></i> Facilité d'utilisation</h3>
            <?php foreach ($stats['ease_of_use'] as $key => $count): ?>
                <div class="fb-bar-row">
                    <span class="fb-bar-label"><?= $easeLabels[$key] ?? $key ?></span>
                    <div class="fb-bar-track">
                        <div class="fb-bar-fill" style="width: <?= round($count / $stats['total'] * 100) ?>%;"></div>
                    </div>
                    <span class="fb-bar-count"><?= $count ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Features appréciées -->
        <div class="fb-detail-card">
            <h3><i class="fas fa-thumbs-up"></i> Ce qui a le plus plu</h3>
            <?php foreach ($stats['liked_features'] as $key => $count): ?>
                <div class="fb-bar-row">
                    <span class="fb-bar-label"><?= $featureLabels[$key] ?? $key ?></span>
                    <div class="fb-bar-track">
                        <div class="fb-bar-fill fb-bar-fill-green" style="width: <?= round($count / $stats['total'] * 100) ?>%;"></div>
                    </div>
                    <span class="fb-bar-count"><?= $count ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Recommandations -->
        <div class="fb-detail-card">
            <h3><i class="fas fa-share-alt"></i> Recommandations</h3>
            <?php foreach ($stats['recommendations'] as $key => $count): ?>
                <div class="fb-bar-row">
                    <span class="fb-bar-label"><?= $recommendLabels[$key] ?? $key ?></span>
                    <div class="fb-bar-track">
                        <div class="fb-bar-fill fb-bar-fill-blue" style="width: <?= round($count / $stats['total'] * 100) ?>%;"></div>
                    </div>
                    <span class="fb-bar-count"><?= $count ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ===== TABLEAU DES FEEDBACKS ===== -->
    <h3 style="margin: 30px 0 12px;"><i class="fas fa-table"></i> Tous les retours</h3>
    <div class="fb-table-wrap">
        <table class="fb-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Identité</th>
                    <th>Note</th>
                    <th>Facilité</th>
                    <th>Recommande</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedbacks as $fb): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($fb['submitted_at'] ?? '')) ?></td>
                    <td><?= htmlspecialchars($fb['name'] ?: 'Anonyme') ?></td>
                    <td><strong><?= (int)$fb['rating'] ?>/5</strong></td>
                    <td><?= $easeLabels[$fb['ease_of_use'] ?? ''] ?? '-' ?></td>
                    <td><?= $recommendLabels[$fb['would_recommend'] ?? ''] ?? '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ===== COMMENTAIRES & AMÉLIORATIONS ===== -->
    <h3 style="margin: 30px 0 12px;"><i class="fas fa-comments"></i> Commentaires & Améliorations</h3>
    <div class="fb-comments-list">
        <?php foreach ($feedbacks as $fb): ?>
            <?php if (!empty($fb['improvements']) || !empty($fb['comments'])): ?>
            <div class="fb-comment-card">
                <div class="fb-comment-header">
                    <span class="fb-comment-author"><?= htmlspecialchars($fb['name'] ?: 'Anonyme') ?></span>
                    <span class="fb-comment-date"><?= date('d/m/Y', strtotime($fb['submitted_at'] ?? '')) ?></span>
                </div>
                <?php if (!empty($fb['improvements'])): ?>
                    <div class="fb-comment-section">
                        <strong><i class="fas fa-tools"></i> Améliorations :</strong>
                        <p><?= nl2br(htmlspecialchars($fb['improvements'])) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($fb['comments'])): ?>
                    <div class="fb-comment-section">
                        <strong><i class="fas fa-pencil-alt"></i> Commentaire :</strong>
                        <p><?= nl2br(htmlspecialchars($fb['comments'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>

<style>
/* Stats grid */
.fb-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.fb-stat-card {
    background: var(--color-bg-alt);
    border: 1px solid var(--color-border);
    border-radius: 10px;
    padding: 20px;
    text-align: center;
}

.fb-stat-icon {
    font-size: 1.4rem;
    color: var(--color-primary);
    margin-bottom: 8px;
}

.fb-stat-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--color-text);
    margin-bottom: 4px;
}

.fb-stat-label {
    font-size: 0.8rem;
    color: var(--color-text-muted);
}

/* Details grid */
.fb-details-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 24px;
}

.fb-detail-card {
    background: var(--color-bg-alt);
    border: 1px solid var(--color-border);
    border-radius: 10px;
    padding: 16px 20px;
}

.fb-detail-card h3 {
    margin: 0 0 12px;
    font-size: 0.95rem;
}

.fb-bar-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.fb-bar-label {
    flex: 0 0 180px;
    font-size: 0.82rem;
    color: var(--color-text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.fb-bar-track {
    flex: 1;
    height: 8px;
    background: var(--color-border);
    border-radius: 4px;
    overflow: hidden;
}

.fb-bar-fill {
    height: 100%;
    background: var(--color-primary);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.fb-bar-fill-green {
    background: #059669;
}

.fb-bar-fill-blue {
    background: #3b82f6;
}

.fb-bar-count {
    flex: 0 0 24px;
    font-size: 0.8rem;
    font-weight: 600;
    text-align: right;
}

/* Table */
.fb-table-wrap {
    overflow-x: auto;
    border: 1px solid var(--color-border);
    border-radius: 10px;
}

.fb-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}

.fb-table th,
.fb-table td {
    padding: 10px 14px;
    text-align: left;
    border-bottom: 1px solid var(--color-border);
}

.fb-table th {
    background: var(--color-bg-alt);
    font-weight: 600;
    font-size: 0.8rem;
    color: var(--color-text-muted);
    white-space: nowrap;
}

.fb-table tbody tr:hover {
    background: var(--color-bg-alt);
}

.fb-table tbody tr:last-child td {
    border-bottom: none;
}

/* Comments */
.fb-comments-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.fb-comment-card {
    background: var(--color-bg-alt);
    border: 1px solid var(--color-border);
    border-radius: 10px;
    padding: 16px 20px;
}

.fb-comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--color-border);
}

.fb-comment-author {
    font-weight: 600;
    font-size: 0.9rem;
}

.fb-comment-date {
    font-size: 0.78rem;
    color: var(--color-text-muted);
}

.fb-comment-section {
    margin-top: 8px;
}

.fb-comment-section strong {
    font-size: 0.82rem;
    color: var(--color-text-muted);
}

.fb-comment-section p {
    margin: 4px 0 0;
    font-size: 0.9rem;
    line-height: 1.5;
    color: var(--color-text);
}

/* Mobile */
@media (max-width: 768px) {
    .fb-dash {
        padding: 0 10px !important;
    }

    .fb-stats-grid {
        grid-template-columns: 1fr;
    }

    .fb-bar-label {
        flex: 0 0 120px;
        font-size: 0.75rem;
    }

    .fb-table {
        font-size: 0.78rem;
    }

    .fb-table th,
    .fb-table td {
        padding: 8px 10px;
    }
}
</style>

<?php require __DIR__ . '/../partials/footer.php'; ?>
