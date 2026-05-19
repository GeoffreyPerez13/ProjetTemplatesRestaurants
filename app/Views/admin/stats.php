<?php
$title = "Statistiques avancées";
$scripts = ["js/sections/stats/stats.js"];
require __DIR__ . '/../partials/header.php';
?>
<link rel="stylesheet" href="/assets/css/admin/sections/stats/stats.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

<a class="btn-back" href="?page=dashboard">Retour</a>

<div class="stats-page">
    <div class="stats-header">
        <div class="stats-header-left">
            <div>
                <h1><i class="fas fa-chart-line"></i> Statistiques avancées</h1>
                <p class="stats-subtitle">
                    <?= htmlspecialchars($restaurant_name ?? '') ?>
                </p>
                <?php if (!empty($slug)): ?>
                    <a href="?page=display&slug=<?= htmlspecialchars($slug) ?>" target="_blank" class="stats-site-link">
                        Voir le site
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="stats-header-right">
            <div class="stats-period-selector">
                <button type="button" class="period-btn" data-days="7">7j</button>
                <button type="button" class="period-btn active" data-days="30">30j</button>
                <button type="button" class="period-btn" data-days="90">90j</button>
                <button type="button" class="period-btn" data-days="365">1an</button>
            </div>
        </div>
    </div>

    <?php if (!empty($success_message)): ?>
        <p class="message-success"><?= $success_message ?></p>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <p class="message-error"><?= $error_message ?></p>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="stats-kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-eye"></i></div>
            <div class="kpi-content">
                <span class="kpi-value" id="kpi-total-visits">—</span>
                <span class="kpi-label">Visites totales</span>
            </div>
            <div class="kpi-trend" id="kpi-trend-visits"></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon kpi-icon-unique"><i class="fas fa-users"></i></div>
            <div class="kpi-content">
                <span class="kpi-value" id="kpi-unique-visitors">—</span>
                <span class="kpi-label">Visiteurs uniques</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon kpi-icon-avg"><i class="fas fa-chart-bar"></i></div>
            <div class="kpi-content">
                <span class="kpi-value" id="kpi-avg-daily">—</span>
                <span class="kpi-label">Moy. / jour</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon kpi-icon-device"><i class="fas fa-mobile-alt"></i></div>
            <div class="kpi-content">
                <span class="kpi-value" id="kpi-mobile-pct">—</span>
                <span class="kpi-label">Mobile</span>
            </div>
        </div>
    </div>

    <!-- Graphique principal : visites par jour -->
    <div class="stats-chart-card stats-chart-main">
        <div class="chart-card-header">
            <h3><i class="fas fa-chart-area"></i> Visites par jour</h3>
        </div>
        <div class="chart-container chart-container-main">
            <canvas id="chart-visits-per-day"></canvas>
        </div>
    </div>

    <!-- Ligne 2 : Appareils + Navigateurs -->
    <div class="stats-charts-row">
        <div class="stats-chart-card">
            <div class="chart-card-header">
                <h3><i class="fas fa-laptop"></i> Appareils</h3>
            </div>
            <div class="chart-container chart-container-sm">
                <canvas id="chart-devices"></canvas>
            </div>
        </div>
        <div class="stats-chart-card">
            <div class="chart-card-header">
                <h3><i class="fas fa-globe"></i> Navigateurs</h3>
            </div>
            <div class="chart-container chart-container-sm">
                <canvas id="chart-browsers"></canvas>
            </div>
        </div>
    </div>

    <!-- Ligne 3 : Heures de pointe + Jours de la semaine -->
    <div class="stats-charts-row">
        <div class="stats-chart-card">
            <div class="chart-card-header">
                <h3><i class="fas fa-clock"></i> Heures de pointe</h3>
            </div>
            <div class="chart-container chart-container-sm">
                <canvas id="chart-hours"></canvas>
            </div>
        </div>
        <div class="stats-chart-card">
            <div class="chart-card-header">
                <h3><i class="fas fa-calendar-week"></i> Jours de la semaine</h3>
            </div>
            <div class="chart-container chart-container-sm">
                <canvas id="chart-weekdays"></canvas>
            </div>
        </div>
    </div>

    <!-- Top référents -->
    <div class="stats-chart-card">
        <div class="chart-card-header">
            <h3><i class="fas fa-link"></i> Sources de trafic</h3>
        </div>
        <div class="stats-referrers-table" id="referrers-table">
            <div class="stats-loading"><i class="fas fa-spinner fa-spin"></i> Chargement…</div>
        </div>
    </div>

    <!-- Loader global -->
    <div class="stats-global-loader" id="stats-loader">
        <div class="stats-loader-spinner">
            <i class="fas fa-chart-line fa-spin"></i>
            <p>Chargement des statistiques…</p>
        </div>
    </div>
</div>

<!-- Tour guidé -->
<script>
// Définir les étapes du tour pour cette page
const tourSteps = [
    {
        element: '.stats-header',
        title: 'Statistiques avancées',
        content: 'Bienvenue dans votre tableau de bord statistiques ! Analysez le trafic de votre site vitrine en détail.',
        position: 'bottom'
    },
    {
        element: '.stats-period-selector',
        title: 'Période d\'analyse',
        content: 'Sélectionnez la période à analyser : 7 jours, 30 jours, 90 jours ou 1 an. Les graphiques se mettent à jour automatiquement.',
        position: 'bottom'
    },
    {
        element: '.stats-kpi-grid',
        title: 'Indicateurs clés',
        content: 'Vue d\'ensemble rapide : visites totales, visiteurs uniques, moyenne par jour et pourcentage de visiteurs mobiles.',
        position: 'bottom'
    },
    {
        element: '.stats-chart-card.stats-chart-main',
        title: 'Visites par jour',
        content: 'Graphique principal montrant l\'évolution des visites totales et des visiteurs uniques sur la période sélectionnée.',
        position: 'top'
    },
    {
        element: '#chart-devices',
        title: 'Appareils',
        content: 'Découvrez quels appareils (mobile, desktop, tablette) utilisent vos visiteurs.',
        position: 'top'
    },
    {
        element: '#chart-hours',
        title: 'Heures de pointe',
        content: 'Identifiez les heures de pointe pour optimiser votre communication.',
        position: 'top'
    },
    {
        element: '.stats-referrers-table',
        title: 'Sources de trafic',
        content: 'Tableau des sites référents : découvrez d\'où viennent vos visiteurs (Google, réseaux sociaux, liens directs...).',
        position: 'top'
    },
    {
        element: null,
        title: 'Analysez votre audience !',
        content: 'Les statistiques sont mises à jour en temps réel. Revenez régulièrement pour suivre l\'évolution de votre trafic.',
        position: 'center'
    }
];
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
