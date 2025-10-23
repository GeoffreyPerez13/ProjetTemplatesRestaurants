<?php
// Titre et scripts spécifiques à cette page
$title = "Modifier la carte";
$scripts = ["js/edit-carte.js"];

// Inclusion du header commun
require __DIR__ . '/../partials/header.php';
?>

<!-- Bouton retour vers le dashboard -->
<a class="btn-back" href="?page=dashboard">← Retour au dashboard</a>

<h2>Modifier la carte</h2>

<!-- Message de succès éventuel après ajout/modification -->
<?php if (!empty($message)): ?>
    <p class="success"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<!-- Formulaire pour ajouter une nouvelle catégorie -->
<h3>Ajouter une catégorie</h3>
<form method="post">
    <input type="text" name="new_category" placeholder="Nom de la catégorie" required>
    <button type="submit" class="btn">Ajouter</button>
</form>

<!-- Liste des catégories existantes si présentes -->
<?php if (!empty($categories)): ?>
    <h3>Catégories existantes</h3>
<?php endif; ?>

<?php foreach ($categories as $cat): ?>
    <div class="category-block">
        <!-- Nom de la catégorie -->
        <strong><?= htmlspecialchars($cat['name']) ?></strong>

        <!-- Formulaire pour supprimer la catégorie -->
        <form method="post" class="inline-form">
            <input type="hidden" name="delete_category" value="<?= $cat['id'] ?>">
            <button type="submit" class="btn danger">Supprimer catégorie</button>
        </form>

        <!-- Formulaire pour ajouter un plat dans cette catégorie -->
        <form method="post" style="margin-top:10px;">
            <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
            <input type="text" name="dish_name" placeholder="Nom du plat" required>
            <input type="number" step="0.01" name="dish_price" placeholder="Prix" required>
            <button type="submit" name="new_dish" class="btn">Ajouter plat</button>
        </form>

        <?php $plats = $cat['plats'] ?? []; ?>

        <!-- Liste des plats existants pour cette catégorie -->
        <?php if ($plats): ?>
            <ul>
                <?php foreach ($plats as $plat): ?>
                    <li>
                        <!-- Formulaire pour modifier un plat -->
                        <form method="post" class="inline-form">
                            <input type="hidden" name="dish_id" value="<?= $plat['id'] ?>">
                            <input type="text" name="dish_name" value="<?= htmlspecialchars($plat['name']) ?>" required>
                            <input type="text" name="dish_price" value="<?= htmlspecialchars($plat['price']) ?>" required>
                            <button type="submit" name="edit_dish" class="btn">Modifier</button>
                        </form>

                        <!-- Formulaire pour supprimer un plat -->
                        <form method="post" class="inline-form">
                            <input type="hidden" name="delete_dish" value="<?= $plat['id'] ?>">
                            <button type="submit" class="btn danger">Supprimer</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucun plat pour cette catégorie.</p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php
// Inclusion du footer commun
require __DIR__ . '/../partials/footer.php';
?>