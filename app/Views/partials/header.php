<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'Administration') ?></title> <!-- Titre de la page, avec fallback sur "Administration" si $title n'est pas défini -->
    <link rel="stylesheet" href="/assets/css/admin.css"> <!-- CSS principal de l'administration -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery (utilisé pour les scripts front et interactions) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert2 pour les alertes stylisées -->

    <!-- Inclusion de scripts additionnels dynamiques si fournis -->
    <?php if (!empty($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="/assets/<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
<!-- Conteneur principal de toutes les pages admin -->
<div class="container">