<?php
$title = "Votre avis nous intéresse";
$styles = [];
$scripts = [];
$remaining = $remaining ?? 0;
$limit_reached = $limit_reached ?? false;
$feedback_swal = $feedback_swal ?? null;
require __DIR__ . '/../partials/header.php';
?>

<div class="feedback-container" style="max-width: 700px; margin: 30px auto; padding: 0 20px;">
    <div class="template-header" style="text-align: center; margin-bottom: 30px;">
        <h2><i class="fas fa-comment-dots"></i> Donnez-nous votre avis</h2>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Merci d'utiliser MenuCraft ! Vos retours nous aident à améliorer l'application.<br>
            Ce formulaire est <strong>anonyme par défaut</strong>, mais vous pouvez vous identifier si vous le souhaitez.
        </p>
        <p style="color: var(--color-text-muted); font-size: 0.82rem; margin-top: 8px;">
            <i class="fas fa-info-circle"></i> Vous pouvez soumettre jusqu'à <strong>3 avis par mois</strong>. Les champs marqués d'un <span style="color: #ef4444;">*</span> sont obligatoires.<br>
            <span style="margin-top: 4px; display: inline-block;"><?= ($remaining ?? 0) > 0 ? "<strong>{$remaining}</strong> avis restant(s) ce mois-ci." : "<strong>0</strong> avis restant ce mois-ci." ?></span>
        </p>
    </div>

    <?php if (!empty($limit_reached)): ?>
        <div style="background: #fef3c7; border: 1px solid #f59e0b; padding: 16px; border-radius: 10px; margin-bottom: 20px; text-align: center; color: #92400e;">
            <i class="fas fa-exclamation-triangle"></i> <strong>Limite atteinte</strong> — Vous avez déjà envoyé 3 retours ce mois-ci. Vous pourrez en soumettre à nouveau le mois prochain.
        </div>
    <?php endif; ?>

    <form method="post" action="?page=feedback&action=submit" id="feedback-form" style="display: flex; flex-direction: column; gap: 20px; <?= !empty($limit_reached) ? 'opacity: 0.5; pointer-events: none;' : '' ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

        <!-- Identification optionnelle -->
        <div class="feedback-section">
            <h3 style="margin: 0 0 12px; font-size: 1rem;"><i class="fas fa-user feedback-icon"></i> Identification (optionnel)</h3>
            <div class="feedback-identity-row">
                <input type="text" name="name" placeholder="Votre nom ou restaurant" 
                       style="flex: 1; min-width: 0; padding: 10px; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-bg); color: var(--color-text);">
                <input type="email" name="email" placeholder="Votre email (optionnel)" 
                       style="flex: 1; min-width: 0; padding: 10px; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-bg); color: var(--color-text);">
            </div>
        </div>

        <!-- Note globale -->
        <div class="feedback-section">
            <h3 style="margin: 0 0 12px; font-size: 1rem;"><i class="fas fa-star feedback-icon"></i> Note globale <span class="feedback-required">*</span></h3>
            <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 10px;">Comment évaluez-vous MenuCraft dans son ensemble ?</p>
            <div class="feedback-rating" style="display: flex; gap: 8px; flex-wrap: wrap;" id="rating-group">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <label style="cursor: pointer;">
                    <input type="radio" name="rating" value="<?= $i ?>" style="display: none;">
                    <span class="rating-btn" style="display: inline-flex; align-items: center; justify-content: center; width: 50px; height: 50px; border: 2px solid var(--color-border); border-radius: 8px; font-size: 1.2rem; font-weight: 600; transition: all 0.2s;"><?= $i ?></span>
                </label>
                <?php endfor; ?>
            </div>
            <div class="feedback-rating-labels" style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--color-text-muted); margin-top: 6px;">
                <span>Très insatisfait</span>
                <span>Très satisfait</span>
            </div>
        </div>

        <!-- Facilité d'utilisation -->
        <div class="feedback-section">
            <h3 style="margin: 0 0 12px; font-size: 1rem;"><i class="fas fa-hand-pointer feedback-icon"></i> Facilité d'utilisation <span class="feedback-required">*</span></h3>
            <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 10px;">L'application est-elle facile à prendre en main ?</p>
            <select name="ease_of_use" id="ease-of-use" style="width: 100%; padding: 10px; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-bg); color: var(--color-text);">
                <option value="">-- Choisissez --</option>
                <option value="very_easy">Très facile, intuitive</option>
                <option value="easy">Facile, quelques hésitations</option>
                <option value="moderate">Moyen, j'ai dû chercher un peu</option>
                <option value="difficult">Difficile, pas toujours clair</option>
                <option value="very_difficult">Très difficile, je me suis perdu(e)</option>
            </select>
        </div>

        <!-- Fonctionnalités les plus utiles -->
        <div class="feedback-section">
            <h3 style="margin: 0 0 12px; font-size: 1rem;"><i class="fas fa-thumbs-up feedback-icon"></i> Ce qui vous a plu</h3>
            <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 10px;">Quelles fonctionnalités avez-vous le plus appréciées ? (plusieurs choix possibles)</p>
            <div class="feedback-features-grid">
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
                <label class="feedback-feature-label">
                    <input type="checkbox" name="liked_features[]" value="<?= $key ?>">
                    <span><?= $label ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Ce qui peut être amélioré -->
        <div class="feedback-section">
            <h3 style="margin: 0 0 12px; font-size: 1rem;"><i class="fas fa-tools feedback-icon"></i> Ce qui peut être amélioré</h3>
            <textarea name="improvements" rows="4" placeholder="Décrivez ce qui pourrait être mieux, les bugs rencontrés, les fonctionnalités manquantes..."
                      style="width: 100%; padding: 10px; border: 1px solid var(--color-border); border-radius: 6px; resize: vertical; background: var(--color-bg); color: var(--color-text); box-sizing: border-box;"></textarea>
        </div>

        <!-- Recommandation -->
        <div class="feedback-section">
            <h3 style="margin: 0 0 12px; font-size: 1rem;"><i class="fas fa-share-alt feedback-icon"></i> Recommandation <span class="feedback-required">*</span></h3>
            <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 10px;">Recommanderiez-vous MenuCraft à un confrère restaurateur ?</p>
            <select name="would_recommend" id="would-recommend" style="width: 100%; padding: 10px; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-bg); color: var(--color-text);">
                <option value="">-- Choisissez --</option>
                <option value="yes_definitely">Oui, sans hésiter</option>
                <option value="yes_probably">Oui, probablement</option>
                <option value="not_sure">Je ne sais pas encore</option>
                <option value="no_probably">Probablement pas</option>
                <option value="no_definitely">Non</option>
            </select>
        </div>

        <!-- Commentaire libre -->
        <div class="feedback-section">
            <h3 style="margin: 0 0 12px; font-size: 1rem;"><i class="fas fa-pencil-alt feedback-icon"></i> Commentaire libre</h3>
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
/* Sections feedback */
.feedback-section {
    background: var(--color-bg-alt);
    padding: 20px;
    border-radius: 10px;
    border: 1px solid var(--color-border);
}

/* Required indicator */
.feedback-required {
    color: #ef4444;
    font-weight: 700;
}

/* Error state */
.feedback-error {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
}

.feedback-error .rating-btn {
    border-color: #ef4444 !important;
}

/* Identité row */
.feedback-identity-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

/* Select fix overflow */
.feedback-section select {
    width: 100%;
    max-width: 100%;
    padding: 10px;
    border: 1px solid var(--color-border);
    border-radius: 6px;
    background: var(--color-bg);
    color: var(--color-text);
    box-sizing: border-box;
    text-overflow: ellipsis;
    overflow: hidden;
}

/* Grid features */
.feedback-features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 8px;
}

.feedback-feature-label {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border: 1px solid var(--color-border);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    overflow: hidden;
}

.feedback-feature-label input[type="checkbox"] {
    flex-shrink: 0;
    width: 16px;
    height: 16px;
    margin: 0;
}

.feedback-feature-label span {
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Rating */
.feedback-rating input:checked + .rating-btn {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: white;
}
.feedback-rating .rating-btn:hover {
    border-color: var(--color-primary);
    background: rgba(var(--color-primary-rgb, 232, 160, 56), 0.1);
}

/* Mobile */
@media (max-width: 768px) {
    .feedback-section {
        padding: 10px;
    }

    .feedback-section h3,
    .feedback-section p {
        text-align: center;
    }

    .feedback-icon {
        display: none;
    }

    .feedback-identity-row {
        flex-direction: column;
    }

    .feedback-identity-row input {
        min-width: 0 !important;
        width: 100%;
    }

    .feedback-features-grid {
        grid-template-columns: 1fr;
    }

    .feedback-container {
        padding: 0 10px !important;
    }

    .feedback-feature-label span {
        white-space: normal;
    }

    .feedback-rating {
        justify-content: center;
    }

    .feedback-rating-labels {
        display: none;
    }

    .feedback-section select {
        font-size: 0.85rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert toast pour les messages de succès/erreur
    <?php if (!empty($feedback_swal)): ?>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: '<?= $feedback_swal['icon'] ?>',
        title: '<?= addslashes($feedback_swal['title']) ?>',
        text: '<?= addslashes($feedback_swal['text']) ?>',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
    });
    <?php endif; ?>

    // Validation du formulaire avant soumission
    const form = document.getElementById('feedback-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            let hasError = false;

            // Reset erreurs
            document.querySelectorAll('.feedback-error').forEach(el => el.classList.remove('feedback-error'));

            // Vérifier la note
            const ratingGroup = document.getElementById('rating-group');
            const ratingChecked = form.querySelector('input[name="rating"]:checked');
            if (!ratingChecked) {
                ratingGroup.classList.add('feedback-error');
                hasError = true;
            }

            // Vérifier facilité d'utilisation
            const easeSelect = document.getElementById('ease-of-use');
            if (!easeSelect.value) {
                easeSelect.classList.add('feedback-error');
                hasError = true;
            }

            // Vérifier recommandation
            const recommendSelect = document.getElementById('would-recommend');
            if (!recommendSelect.value) {
                recommendSelect.classList.add('feedback-error');
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Champs obligatoires',
                    text: 'Veuillez remplir tous les champs marqués d\'un *.',
                    confirmButtonColor: '#059669'
                });
            }
        });

        // Retirer l'erreur au changement
        document.querySelectorAll('#ease-of-use, #would-recommend').forEach(el => {
            el.addEventListener('change', function() {
                this.classList.remove('feedback-error');
            });
        });

        document.querySelectorAll('input[name="rating"]').forEach(input => {
            input.addEventListener('change', function() {
                document.getElementById('rating-group').classList.remove('feedback-error');
            });
        });
    }
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
