<?php
/**
 * display.php — Vue principale de la page vitrine (assemblage de partials)
 *
 * Variables disponibles (passées par DisplayController::show()) :
 *   $restaurant, $logo, $banner, $contact, $carteMode, $categories,
 *   $cardImages, $lastUpdated, $services, $payments, $socials,
 *   $siteOnline, $isPreview, $templateName, $layoutName
 */
$displayDir = __DIR__ . '/display/';
?>
<?php include $displayDir . 'head.php'; ?>

<body class="template-<?= htmlspecialchars($templateName ?? 'classic') ?> layout-<?= htmlspecialchars($layoutName ?? 'standard') ?>">
    <?php if (isset($siteOnline) && !$siteOnline): ?>
        <!-- Page de maintenance -->
        <div class="maintenance-container">
            <div class="maintenance-box">
                <i class="fas fa-tools"></i>
                <h1><?= htmlspecialchars($restaurant->name) ?></h1>
                <p>Le site est actuellement en maintenance.<br>Veuillez revenir plus tard.</p>
            </div>
        </div>
    <?php else: ?>
        <!-- Bandeau de fermeture exceptionnelle -->
        <?php if (!empty($todayClosureDate)): ?>
            <link rel="stylesheet" href="/assets/css/display/closure-banner.css">
            <div class="closure-banner">
                <div class="closure-banner-text">
                    <i class="fas fa-calendar-times"></i>
                    <span>Fermeture exceptionnelle aujourd'hui</span>
                    <span class="closure-banner-date"><?= date('d/m/Y', strtotime($todayClosureDate)) ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <?php include $displayDir . 'cookies.php'; ?>
        <?php include $displayDir . 'header.php'; ?>
        <?php include $displayDir . 'banner.php'; ?>
        <?php include $displayDir . 'carte.php'; ?>
        <?php include $displayDir . 'services.php'; ?>
        <?php include $displayDir . 'reviews.php'; ?>
        <?php include $displayDir . 'footer.php'; ?>
        <?php include $displayDir . 'lightbox.php'; ?>

        <!-- Flèches de navigation scroll -->
        <div class="page-navigation-buttons">
            <button type="button" id="scroll-to-bottom" class="btn-navigation scroll-to-bottom" title="Aller en bas de la page">
                <i class="fas fa-arrow-down"></i>
            </button>
            <button type="button" id="scroll-to-top" class="btn-navigation scroll-to-top" title="Aller en haut de la page">
                <i class="fas fa-arrow-up"></i>
            </button>
        </div>

        <script src="/assets/js/display/navigation.js"></script>
        <script src="/assets/js/display/lightbox.js"></script>
        <script src="/assets/js/display/cookies.js"></script>
        <script src="/assets/js/display/scroll-arrows.js"></script>
    <?php endif; ?>
</body>

</html>