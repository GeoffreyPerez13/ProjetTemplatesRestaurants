<?php
$title = $title ?? "Mentions légales";
require __DIR__ . '/../partials/header.php';
?>

<a class="btn-back" href="?page=dashboard">Retour</a>

<div class="legal-container">
    <div class="legal-sidebar">
        <h3>Documentation</h3>
        <ul class="legal-menu">
            <?php foreach ($sections as $section): ?>
                <li>
                    <a href="?page=legal&section=<?= $section ?>" 
                       class="<?= $section === $current_section ? 'active' : '' ?>">
                        <?php 
                        $titles = [
                            'cgu' => 'CGU',
                            'privacy' => 'Confidentialité',
                            'cookies' => 'Cookies',
                            'legal' => 'Mentions légales'
                        ];
                        echo $titles[$section] ?? ucfirst($section);
                        ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <div class="legal-content">
        <h1><?= htmlspecialchars($title) ?></h1>
        <div class="legal-text">
            <?= $content ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>