<!-- Section Premium Upgrade pour les avis Google -->
<section class="reviews-section">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-star"></i> Avis Google</h2>
            <p>Découvrez ce que nos clients pensent de nous</p>
        </div>
        
        <div class="premium-upgrade-card">
            <div class="premium-upgrade-content">
                <div class="premium-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <h3>Fonctionnalité Premium</h3>
                <p>Affichez les avis Google de votre restaurant directement sur votre site vitrine.</p>
                
                <div class="premium-features-list">
                    <ul>
                        <li><i class="fas fa-check"></i> Avis Google en temps réel</li>
                        <li><i class="fas fa-check"></i> Note moyenne et nombre d'avis</li>
                        <li><i class="fas fa-check"></i> Design intégré et responsive</li>
                        <li><i class="fas fa-check"></i> Mise à jour automatique</li>
                    </ul>
                </div>
                
                <div class="premium-upgrade-actions">
                    <button class="btn premium-btn" disabled>
                        <i class="fas fa-lock"></i>
                        Activer Premium
                    </button>
                    <p class="premium-note">
                        Contactez-nous à <a href="mailto:premium@menumiam.fr">premium@menumiam.fr</a> pour activer cette fonctionnalité
                    </p>
                </div>
            </div>
            
            <div class="premium-preview">
                <div class="review-preview-card">
                    <div class="review-header">
                        <div class="review-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="review-info">
                            <div class="review-name">Client Satisfait</div>
                            <div class="review-date">il y a 2 semaines</div>
                        </div>
                        <div class="review-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <div class="review-text">
                        Excellent restaurant ! Service impeccable et plats délicieux. Je recommande vivement !
                    </div>
                </div>
                
                <div class="review-preview-card">
                    <div class="review-header">
                        <div class="review-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="review-info">
                            <div class="review-name">Client Ravi</div>
                            <div class="review-date">il y a 1 mois</div>
                        </div>
                        <div class="review-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                    <div class="review-text">
                        Une expérience culinaire exceptionnelle. L'ambiance est chaleureuse et les plats sont savoureux.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.premium-upgrade-card {
    background: linear-gradient(135deg, var(--color-bg-alt), rgba(212, 168, 83, 0.1));
    border: 2px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-xl);
    align-items: center;
    margin: var(--spacing-lg) 0;
}

.premium-upgrade-content {
    text-align: center;
}

.premium-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--color-primary), #d4a853);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--spacing-lg);
    color: white;
    font-size: 2rem;
}

.premium-upgrade-content h3 {
    color: var(--color-text);
    margin: 0 0 var(--spacing-md) 0;
    font-size: 1.5rem;
}

.premium-upgrade-content p {
    color: var(--color-text-light);
    margin: 0 0 var(--spacing-lg) 0;
    font-size: 1.1rem;
}

.premium-features-list ul {
    list-style: none;
    padding: 0;
    margin: 0 0 var(--spacing-lg) 0;
}

.premium-features-list li {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    color: var(--color-text);
    margin-bottom: var(--spacing-sm);
}

.premium-features-list i {
    color: var(--color-primary);
}

.premium-upgrade-actions {
    text-align: center;
}

.premium-btn {
    background: linear-gradient(135deg, var(--color-primary), #d4a853);
    color: white;
    border: none;
    padding: var(--spacing-sm) var(--spacing-lg);
    border-radius: var(--radius-md);
    font-weight: 600;
    cursor: not-allowed;
    opacity: 0.7;
    margin-bottom: var(--spacing-md);
}

.premium-note {
    color: var(--color-text-light);
    font-size: 0.9rem;
    margin: 0;
}

.premium-note a {
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 500;
}

.premium-note a:hover {
    text-decoration: underline;
}

.premium-preview {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.review-preview-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--spacing-md);
    opacity: 0.7;
}

.review-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-sm);
}

.review-avatar {
    width: 40px;
    height: 40px;
    background: var(--color-bg-warm);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-light);
}

.review-info {
    flex: 1;
}

.review-name {
    font-weight: 600;
    color: var(--color-text);
}

.review-date {
    font-size: 0.85rem;
    color: var(--color-text-light);
}

.review-rating {
    color: #d4a853;
}

.review-text {
    color: var(--color-text-light);
    font-style: italic;
    line-height: 1.4;
}

@media (max-width: 768px) {
    .premium-upgrade-card {
        grid-template-columns: 1fr;
        gap: var(--spacing-lg);
        padding: var(--spacing-lg);
    }
    
    .premium-preview {
        flex-direction: row;
        overflow-x: auto;
        gap: var(--spacing-sm);
    }
    
    .review-preview-card {
        min-width: 280px;
    }
}
</style>
