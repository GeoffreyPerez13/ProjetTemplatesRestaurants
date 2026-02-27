<?php
/**
 * Section des avis Google sur la page vitrine
 */

// Vérifier si les avis Google sont activés et si la fonctionnalité premium est disponible
if (!($googleReviewsEnabled ?? false)) {
    return;
}

// Vérifier si la fonctionnalité premium Google Reviews est activée
require_once __DIR__ . '/../../Models/PremiumFeature.php';
$premiumFeature = new PremiumFeature($pdo);
$adminId = $_SESSION['admin_id'] ?? null;

if (!$adminId || !$premiumFeature->isEnabled($adminId, 'google_reviews')) {
    // Afficher la carte premium upgrade au lieu des vrais avis
    include 'reviews-premium-upgrade.php';
    return;
}

// Récupérer les avis
$reviews = null;
$restaurantInfo = null;

if ($googlePlaceId) {
    require_once __DIR__ . '/../../Models/GoogleReviews.php';
    $googleReviews = new GoogleReviews($pdo, $googleApiKey);
    $data = $googleReviews->getReviews($googlePlaceId, 5);
    
    if ($data) {
        $restaurantInfo = [
            'name' => $data['name'] ?? '',
            'rating' => $data['rating'] ?? 0,
            'total_reviews' => count($data['reviews'] ?? [])
        ];
        $reviews = $data['reviews'] ?? [];
    }
}
?>

<?php if ($reviews): ?>
<section id="reviews" class="reviews-section">
    <div class="container">
        <h2>Avis Google</h2>
        
        <!-- En-tête avec note globale -->
        <div class="reviews-header">
            <div class="reviews-summary">
                <div class="rating-large">
                    <span class="rating-number"><?= number_format($restaurantInfo['rating'], 1) ?></span>
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= $restaurantInfo['rating'] ? 'filled' : 'empty' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="total-reviews"><?= $restaurantInfo['total_reviews'] ?> avis</span>
                </div>
                <div class="restaurant-name">
                    <h3><?= htmlspecialchars($restaurantInfo['name']) ?></h3>
                    <a href="https://search.google.com/local/reviews?placeid=<?= urlencode($placeId) ?>" 
                       target="_blank" rel="noopener" class="see-all-reviews">
                        Voir tous les avis <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Avis -->
        <div class="reviews-grid">
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <?php if ($review['profile_photo_url']): ?>
                                <img src="<?= htmlspecialchars($review['profile_photo_url']) ?>" 
                                     alt="<?= htmlspecialchars($review['author_name']) ?>" 
                                     class="reviewer-avatar">
                            <?php else: ?>
                                <div class="reviewer-avatar-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                            <div class="reviewer-details">
                                <div class="reviewer-name"><?= htmlspecialchars($review['author_name']) ?></div>
                                <div class="review-date"><?= htmlspecialchars($review['relative_time_description']) ?></div>
                            </div>
                        </div>
                        <div class="review-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $review['rating'] ? 'filled' : 'empty' ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php if (!empty($review['text'])): ?>
                        <div class="review-text">
                            <p><?= htmlspecialchars($review['text']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Lien vers Google -->
        <div class="reviews-footer">
            <a href="https://search.google.com/local/reviews?placeid=<?= urlencode($placeId) ?>" 
               target="_blank" rel="noopener" class="btn btn-outline">
                <i class="fab fa-google"></i> Laisser un avis sur Google
            </a>
        </div>
    </div>
</section>
<?php endif; ?>
