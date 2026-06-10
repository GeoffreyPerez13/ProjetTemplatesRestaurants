<?php
$title = "Votre avis nous intéresse";
$styles = [];
$scripts = [];
require __DIR__ . '/../partials/header.php';
?>

<div class="feedback-container" style="max-width: 700px; margin: 30px auto; padding: 0 20px;">
    <div class="template-header" style="text-align: center; margin-bottom: 30px;">
        <h2><i class="fas fa-comment-dots"></i> Donnez-nous votre avis</h2>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Merci d'utiliser MenuCraft ! Vos retours nous aident à améliorer l'application.<br>
            Ce formulaire est <strong>anonyme par défaut</strong>, mais vous pouvez vous identifier si vous le souhaitez.
        </p>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success" style="background: #d1fae5; border: 1px solid #6ee7b7; padding: 15px; border-radius: 8px; margin-bottom: 20px; color: #065f46;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="?page=feedback&action=submit" style="display: flex; flex-direction: column; gap: 20px;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

        <!-- Identification optionnelle -->
        <div style="background: var(--color-bg-alt); padding: 20px; border-radius: 10px; border: 1px solid var(--color-border);">
            <h3 style="margin: 0 0 12px; font-size: 1rem;"><i class="fas fa-user"></i> Identification (optionnel)</h3>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <input type="text" name="name" placeholder="Votre nom ou restaurant" 
                       style="flex: 1; min-width: 200px; padding: 10px; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-bg); color: var(--color-text);">
                <input type="email" name="email" placeholder="Votre email (optionnel)" 
                       style="flex: 1; min-width: 200px; padding: 10px; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-bg); color: var(--color-text);">
            </div>
        </div>

        <!-- Note globale -->
        <div style="background: var(--color-bg-alt); padding: 20px; border-radius: 10px; border: 1px solid var(--color-border);">
            <h3 style="margin: 0 0 12px; font-size: 1rem;"><i class="fas fa-star"></i> Note globale</h3>
            <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 10px;">Comment évaluez-vous MenuCraft dans son ensemble ?</p>
            <div class="feedback-rating" style="display: flex; gap: 8px; flex-wrap: wrap;">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <label style="cursor: pointer;">
                    <input type="radio" name="rating" value="<?= $i ?>" style="display: none;" required>
                    <span class="rating-btn" style="display: inline-flex; align-items: center; justify-content: center; width: 50px; height: 50px; border: 2px solid var(--color-border); border-radius: 8px; font-size: 1.2rem; font-weight: 600; transition: all 0.2s;"><?= $i ?></span>
                </label>
                <?php endfor; ?>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--color-text-muted); margin-top: 6px;">
                <span>Très insatisfait</span>
                <span>Très satisfait</span>
            </div>
        </div>

        <!-- Facilité d'utilisation -->
        <div style="background: var(--color-bg-alt); padding: 20px; border-radius: 10px; border: 1px solid var(--color-border);">
            <h3 style="margin: 0 0 12px; font-size: 1rem;"><i class="fas fa-hand-pointer"></i> Facilité d'utilisation</h3>
            <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 10px;">L'application est-elle facile à prendre en main ?</p>
            <select name="ease_of_use" required style="width: 100%; padding: 10px; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-bg); color: var(--color-text);">
                <option value="">-- Choisissez --</option>
                <option value="very_easy">Très facile, intuitive</option>
                <option value="easy">Facile, quelques hésitations</option>
                <option value="moderate">Moyen, j'ai dû chercher un peu</option>
                <option value="difficult">Difficile, pas toujours clair</option>
                <option value="very_difficult">Très difficile, je me suis perdu(e)</option>
            </select>
        </div>

        <!-- Fonctionnalités les plus utiles -->
        <div style="background: var(--color-bg-alt); padding: 20px; border-radius: 10px; border: 1px solid var(--color-border);">
            <h3 style="margin: 0 0 12px; font-size: 1rem;"><i class="fas fa-thumbs-up"></i> Ce qui vous a plu</h3>
            <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 10px;">Quelles fonctionnalités avez-vous le plus appréciées ? (plusieurs choix possibles)</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px;">
                <?php
                $features = [
                    'carte' => 'Gestion de la carte',
                    'templates' => 'Choix du template',
                    'reservations' => 'Réservations en ligne',
                    'menus' => 'Menus du jour',
                    'stats' => 'Statistiques',
                    'contact' => 'Page contact',
                    'services' => 'Services & paiements',
                    'mobile' => 'Version mobile',
                ];
                foreach ($features as $key => $label): ?>
                <label style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid var(--color-border); border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="liked_features[]" value="<?= $key ?>">
                    <span style="font-size: 0.85rem;"><?= $label ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Ce qui peut être amélioré -->
        <div style="background: var(--color-bg-alt); padding: 20px; border-radius: 10px; border: 1px solid var(--color-border);">
            <h3 style="margin: 0 0 12px; font-size: 1rem;"><i class="fas fa-tools"></i> Ce qui peut être amélioré</h3>
            <textarea name="improvements" rows="4" placeholder="Décrivez ce qui pourrait être mieux, les bugs rencontrés, les fonctionnalités manquantes..."
                      style="width: 100%; padding: 10px; border: 1px solid var(--color-border); border-radius: 6px; resize: vertical; background: var(--color-bg); color: var(--color-text); box-sizing: border-box;"></textarea>
        </div>

        <!-- Recommandation -->
        <div style="background: var(--color-bg-alt); padding: 20px; border-radius: 10px; border: 1px solid var(--color-border);">
            <h3 style="margin: 0 0 12px; font-size: 1rem;"><i class="fas fa-share-alt"></i> Recommandation</h3>
            <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 10px;">Recommanderiez-vous MenuCraft à un confrère restaurateur ?</p>
            <select name="would_recommend" required style="width: 100%; padding: 10px; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-bg); color: var(--color-text);">
                <option value="">-- Choisissez --</option>
                <option value="yes_definitely">Oui, sans hésiter</option>
                <option value="yes_probably">Oui, probablement</option>
                <option value="not_sure">Je ne sais pas encore</option>
                <option value="no_probably">Probablement pas</option>
                <option value="no_definitely">Non</option>
            </select>
        </div>

        <!-- Commentaire libre -->
        <div style="background: var(--color-bg-alt); padding: 20px; border-radius: 10px; border: 1px solid var(--color-border);">
            <h3 style="margin: 0 0 12px; font-size: 1rem;"><i class="fas fa-pencil-alt"></i> Commentaire libre</h3>
            <textarea name="comments" rows="4" placeholder="Tout autre commentaire, suggestion ou remarque..."
                      style="width: 100%; padding: 10px; border: 1px solid var(--color-border); border-radius: 6px; resize: vertical; background: var(--color-bg); color: var(--color-text); box-sizing: border-box;"></textarea>
        </div>

        <!-- Soumettre -->
        <button type="submit" class="btn primary" style="padding: 14px 30px; font-size: 1rem; border-radius: 8px; cursor: pointer;">
            <i class="fas fa-paper-plane"></i> Envoyer mon avis
        </button>
    </form>
</div>

<style>
.feedback-rating input:checked + .rating-btn {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: white;
}
.feedback-rating .rating-btn:hover {
    border-color: var(--color-primary);
    background: rgba(var(--color-primary-rgb, 232, 160, 56), 0.1);
}
</style>

<?php require __DIR__ . '/../partials/footer.php'; ?>
