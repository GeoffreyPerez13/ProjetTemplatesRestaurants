<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/Models/Category.php';
require_once __DIR__ . '/../app/Models/Dish.php';

$title = "Restaurant Templates";
require __DIR__ . '/../partials/header.php';

// --- Récupération des catégories et plats ---
$categoryModel = new Category($pdo);
$dishModel = new Dish($pdo);

// Récupère toutes les catégories
$categories = $categoryModel->getAll();
foreach ($categories as &$cat) {
    // Ajoute les plats associés à chaque catégorie
    $cat['plats'] = $dishModel->getAllByCategory($cat['id']);
}
?>

<!-- ==================== ACCUEIL ==================== -->
<section class="accueil" id="accueil">
    <div class="accueil-content">
        <h1>Bienvenue au Restaurant Templates</h1>
        <p>Découvrez nos plats savoureux préparés avec amour.</p>
        <button onclick="document.querySelector('#carte').scrollIntoView({behavior:'smooth'});">Voir la carte</button>
    </div>
</section>

<!-- ==================== CARTE ==================== -->
<section class="carte" id="carte">
    <?php foreach ($categories as $cat): ?>
        <div class="bloc">
            <div class="texte">
                <h2><?= htmlspecialchars($cat['name']) ?></h2>

                <!-- Liste des plats de la catégorie -->
                <?php if (!empty($cat['plats'])): ?>
                    <ul>
                        <?php foreach ($cat['plats'] as $plat): ?>
                            <li><?= htmlspecialchars($plat['name']) ?> - <?= number_format($plat['price'], 2, ',', '') ?>€</li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucun plat disponible pour cette catégorie.</p>
                <?php endif; ?>
            </div>

            <!-- Image placeholder pour la catégorie -->
            <div class="image">
                <img src="assets/placeholder.jpg" alt="<?= htmlspecialchars($cat['name']) ?>">
            </div>
        </div>
    <?php endforeach; ?>
</section>

<!-- ==================== CONTACT ==================== -->
<section class="contact" id="contact">
    <h2>Contact</h2>
    <div class="contact-container">
        <div>
            <p><strong>Téléphone :</strong> 01 23 45 67 89</p>
            <p><strong>Email :</strong> contact@restaurant.com</p>
            <p><strong>Adresse :</strong> 123 Rue du Restaurant, Paris</p>
            <p><strong>Horaires :</strong> Lun-Dim 12h - 22h</p>
        </div>
        <div>
            <!-- Intégration Google Maps -->
            <iframe src="https://www.google.com/maps/embed?pb=!1m18..."></iframe>
        </div>
    </div>
</section>

<!-- ==================== FOOTER ==================== -->
<footer>
    <p>&copy; <?= date('Y') ?> Restaurant Templates. Tous droits réservés.</p>
    <div class="footer-links">
        <a href="#accueil">Accueil</a> |
        <a href="#carte">Carte</a> |
        <a href="#contact">Contact</a>
    </div>
</footer>

<?php require __DIR__ . '/../partials/footer.php'; ?>
