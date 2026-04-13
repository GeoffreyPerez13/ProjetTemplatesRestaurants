<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Dashboard') ?> - MenuMiam</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin/dashboard.css">
</head>
<body>
    <?php require APP_PATH . '/Views/partials/header.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Bienvenue, <?= htmlspecialchars($admin['username']) ?> !</h1>
            <p>Restaurant : <strong><?= htmlspecialchars($admin['restaurant_name']) ?></strong></p>
        </div>

        <?php if (\App\Helpers\Session::hasFlash('success')): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars(\App\Helpers\Session::getFlash('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (\App\Helpers\Session::hasFlash('error')): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars(\App\Helpers\Session::getFlash('error')) ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>📋 Ma Carte</h2>
                <p>Gérez votre carte en ligne</p>
                <a href="<?= BASE_URL ?>/public/carte" class="btn btn-primary">Accéder</a>
            </div>

            <div class="dashboard-card">
                <h2>📞 Contact</h2>
                <p>Informations de contact</p>
                <a href="<?= BASE_URL ?>/public/contact" class="btn btn-primary">Modifier</a>
            </div>

            <div class="dashboard-card">
                <h2>🎨 Apparence</h2>
                <p>Logo, bannière, templates</p>
                <a href="<?= BASE_URL ?>/public/apparence" class="btn btn-primary">Personnaliser</a>
            </div>

            <div class="dashboard-card">
                <h2>⚙️ Paramètres</h2>
                <p>Configuration du compte</p>
                <a href="<?= BASE_URL ?>/public/settings" class="btn btn-primary">Configurer</a>
            </div>
        </div>

        <div class="dashboard-info">
            <h3>Informations du compte</h3>
            <ul>
                <li><strong>Email :</strong> <?= htmlspecialchars($admin['email']) ?></li>
                <li><strong>Slug :</strong> <?= htmlspecialchars($admin['slug']) ?></li>
                <li><strong>Mode carte :</strong> <?= htmlspecialchars($admin['carte_mode']) ?></li>
                <li><strong>Rôle :</strong> <?= htmlspecialchars($admin['role']) ?></li>
                <li><strong>Créé le :</strong> <?= date('d/m/Y', strtotime($admin['created_at'])) ?></li>
            </ul>
        </div>
    </div>

    <?php require APP_PATH . '/Views/partials/footer.php'; ?>
</body>
</html>
