<?php
/**
 * Section des avis Google sur la page vitrine
 * Les données sont préparées par DisplayController et passées dans $googleReviewsData
 */

// Si aucune donnée d'avis, ne rien afficher
if (!($googleReviewsData ?? null)) {
    return;
}

$restaurantInfo = $googleReviewsData['restaurant_info'] ?? null;
$reviews = $googleReviewsData['reviews'] ?? [];

if (!$reviews || !$restaurantInfo) {
    return;
}
?>

<?php if ($reviews): ?>
<section id="reviews" class="reviews-section">
    <div class="container">
        <h2>Avis Google</h2>
        
        <!-- En-tête avec note globale -->
        <div class="reviews-header">
            <div class="reviews-hero-card">
                <div class="reviews-hero-icon">
                    <i class="fab fa-google"></i>
                </div>
                <div class="reviews-hero-rating">
                    <span class="rating-number"><?= number_format($restaurantInfo['rating'], 1) ?></span>
                    <div class="reviews-hero-stars-block">
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= round($restaurantInfo['rating']) ? 'filled' : 'empty' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="total-reviews"><?= $restaurantInfo['total_reviews'] ?> avis Google</span>
                    </div>
                </div>
                <div class="reviews-hero-sep"></div>
                <div class="reviews-hero-info">
                    <span class="reviews-hero-name"><?= htmlspecialchars($restaurantInfo['name']) ?></span>
                    <a href="https://search.google.com/local/reviews?placeid=<?= urlencode($googlePlaceId) ?>" 
                       target="_blank" rel="noopener" class="see-all-reviews">
                        Voir tous les avis <i class="fas fa-arrow-right"></i>
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
            <a href="https://search.google.com/local/reviews?placeid=<?= urlencode($googlePlaceId) ?>" 
               target="_blank" rel="noopener" class="btn btn-outline">
                <i class="fab fa-google"></i> Laisser un avis sur Google
            </a>
        </div>
    </div>
</section>
<?php endif; ?>
