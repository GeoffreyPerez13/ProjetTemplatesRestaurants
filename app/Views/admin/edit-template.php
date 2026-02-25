<?php
$title = "Choisir un template";
$scripts = ["js/sections/edit-template/edit-template.js"];
require __DIR__ . '/../partials/header.php';
?>

<a href="?page=dashboard" class="btn-back">Retour</a>

<div class="template-selector">
    <div class="template-header">
        <h2><i class="fas fa-palette"></i> Choisir le template du site vitrine</h2>
        <p class="template-subtitle">Sélectionnez le design qui correspond le mieux à votre restaurant. Le changement est instantané.</p>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="message-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="message-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <div class="templates-grid">
        <!-- Template Classique -->
        <div class="template-card <?= $currentTemplate === 'classic' ? 'active' : '' ?>">
            <div class="template-preview template-preview-classic">
                <div class="preview-header-bar">
                    <div class="preview-logo-dot"></div>
                    <div class="preview-nav-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
                <div class="preview-banner-area classic-banner">
                    <div class="preview-banner-text">
                        <div class="preview-title-line"></div>
                        <div class="preview-subtitle-line"></div>
                    </div>
                </div>
                <div class="preview-content-area">
                    <div class="preview-section-title"></div>
                    <div class="preview-cards-row">
                        <div class="preview-card"></div>
                        <div class="preview-card"></div>
                        <div class="preview-card"></div>
                    </div>
                </div>
                <div class="preview-footer-bar classic-footer"></div>
            </div>
            <div class="template-info">
                <h3>Classique</h3>
                <p>Design chaleureux aux tons cuivrés. Navigation traditionnelle avec bannière pleine largeur. Idéal pour les brasseries et restaurants familiaux.</p>
                <div class="template-colors">
                    <span class="color-dot" style="background: #b45309;"></span>
                    <span class="color-dot" style="background: #fef7ed;"></span>
                    <span class="color-dot" style="background: #1c1917;"></span>
                    <span class="color-dot" style="background: #ffffff;"></span>
                </div>
            </div>
            <div class="template-actions">
                <?php if ($currentTemplate === 'classic'): ?>
                    <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                <?php else: ?>
                    <form method="POST" action="?page=edit-template&action=save-template">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="template" value="classic">
                        <button type="submit" class="btn">Appliquer</button>
                    </form>
                <?php endif; ?>
                <?php if (!empty($slug)): ?>
                    <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_template=classic" target="_blank" class="btn btn-outline">
                        <i class="fas fa-eye"></i> Aperçu
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Template Moderne -->
        <div class="template-card <?= $currentTemplate === 'modern' ? 'active' : '' ?>">
            <div class="template-preview template-preview-modern">
                <div class="preview-header-bar modern-header">
                    <div class="preview-logo-dot modern-dot"></div>
                    <div class="preview-nav-dots modern-nav">
                        <span></span><span></span><span></span>
                    </div>
                </div>
                <div class="preview-banner-area modern-banner">
                    <div class="preview-banner-text">
                        <div class="preview-title-line modern-line"></div>
                    </div>
                </div>
                <div class="preview-content-area modern-content">
                    <div class="preview-section-title modern-title"></div>
                    <div class="preview-cards-row modern-cards">
                        <div class="preview-card modern-card"></div>
                        <div class="preview-card modern-card"></div>
                    </div>
                </div>
                <div class="preview-footer-bar modern-footer"></div>
            </div>
            <div class="template-info">
                <h3>Moderne</h3>
                <p>Design épuré et minimaliste aux tons bleu nuit. Grandes images, typographie aérée. Parfait pour les restaurants branchés et bistrots modernes.</p>
                <div class="template-colors">
                    <span class="color-dot" style="background: #2563eb;"></span>
                    <span class="color-dot" style="background: #f0f9ff;"></span>
                    <span class="color-dot" style="background: #0f172a;"></span>
                    <span class="color-dot" style="background: #ffffff;"></span>
                </div>
            </div>
            <div class="template-actions">
                <?php if ($currentTemplate === 'modern'): ?>
                    <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                <?php else: ?>
                    <form method="POST" action="?page=edit-template&action=save-template">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="template" value="modern">
                        <button type="submit" class="btn">Appliquer</button>
                    </form>
                <?php endif; ?>
                <?php if (!empty($slug)): ?>
                    <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_template=modern" target="_blank" class="btn btn-outline">
                        <i class="fas fa-eye"></i> Aperçu
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Template Élégant -->
        <div class="template-card <?= $currentTemplate === 'elegant' ? 'active' : '' ?>">
            <div class="template-preview template-preview-elegant">
                <div class="preview-header-bar elegant-header">
                    <div class="preview-logo-dot elegant-dot"></div>
                    <div class="preview-nav-dots elegant-nav">
                        <span></span><span></span><span></span>
                    </div>
                </div>
                <div class="preview-banner-area elegant-banner">
                    <div class="preview-banner-text">
                        <div class="preview-title-line elegant-line"></div>
                        <div class="preview-subtitle-line elegant-sub"></div>
                    </div>
                </div>
                <div class="preview-content-area elegant-content">
                    <div class="preview-section-title elegant-title"></div>
                    <div class="preview-cards-row">
                        <div class="preview-card elegant-card"></div>
                        <div class="preview-card elegant-card"></div>
                        <div class="preview-card elegant-card"></div>
                    </div>
                </div>
                <div class="preview-footer-bar elegant-footer"></div>
            </div>
            <div class="template-info">
                <h3>Élégant</h3>
                <p>Design sombre et raffiné avec accents dorés. Ambiance haut de gamme. Idéal pour la gastronomie, les restaurants étoilés et tables d'exception.</p>
                <div class="template-colors">
                    <span class="color-dot" style="background: #d4a853;"></span>
                    <span class="color-dot" style="background: #1a1a2e;"></span>
                    <span class="color-dot" style="background: #f5f0e8;"></span>
                    <span class="color-dot" style="background: #0d0d1a;"></span>
                </div>
            </div>
            <div class="template-actions">
                <?php if ($currentTemplate === 'elegant'): ?>
                    <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                <?php else: ?>
                    <form method="POST" action="?page=edit-template&action=save-template">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="template" value="elegant">
                        <button type="submit" class="btn">Appliquer</button>
                    </form>
                <?php endif; ?>
                <?php if (!empty($slug)): ?>
                    <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_template=elegant" target="_blank" class="btn btn-outline">
                        <i class="fas fa-eye"></i> Aperçu
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Template Nature -->
        <div class="template-card <?= $currentTemplate === 'nature' ? 'active' : '' ?>">
            <div class="template-preview template-preview-nature">
                <div class="preview-header-bar nature-header">
                    <div class="preview-logo-dot nature-dot"></div>
                    <div class="preview-nav-dots nature-nav">
                        <span></span><span></span><span></span>
                    </div>
                </div>
                <div class="preview-banner-area nature-banner">
                    <div class="preview-banner-text">
                        <div class="preview-title-line"></div>
                        <div class="preview-subtitle-line"></div>
                    </div>
                </div>
                <div class="preview-content-area nature-content">
                    <div class="preview-section-title nature-title"></div>
                    <div class="preview-cards-row">
                        <div class="preview-card nature-card"></div>
                        <div class="preview-card nature-card"></div>
                        <div class="preview-card nature-card"></div>
                    </div>
                </div>
                <div class="preview-footer-bar nature-footer"></div>
            </div>
            <div class="template-info">
                <h3>Nature</h3>
                <p>Design frais et organique aux tons verts. Ambiance naturelle et apaisante. Idéal pour les restaurants bio, végétariens et cuisine du terroir.</p>
                <div class="template-colors">
                    <span class="color-dot" style="background: #16a34a;"></span>
                    <span class="color-dot" style="background: #f0fdf4;"></span>
                    <span class="color-dot" style="background: #14532d;"></span>
                    <span class="color-dot" style="background: #ffffff;"></span>
                </div>
            </div>
            <div class="template-actions">
                <?php if ($currentTemplate === 'nature'): ?>
                    <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                <?php else: ?>
                    <form method="POST" action="?page=edit-template&action=save-template">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="template" value="nature">
                        <button type="submit" class="btn">Appliquer</button>
                    </form>
                <?php endif; ?>
                <?php if (!empty($slug)): ?>
                    <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_template=nature" target="_blank" class="btn btn-outline">
                        <i class="fas fa-eye"></i> Aperçu
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Template Rosé -->
        <div class="template-card <?= $currentTemplate === 'rose' ? 'active' : '' ?>">
            <div class="template-preview template-preview-rose">
                <div class="preview-header-bar rose-header">
                    <div class="preview-logo-dot rose-dot"></div>
                    <div class="preview-nav-dots rose-nav">
                        <span></span><span></span><span></span>
                    </div>
                </div>
                <div class="preview-banner-area rose-banner">
                    <div class="preview-banner-text">
                        <div class="preview-title-line rose-line"></div>
                        <div class="preview-subtitle-line rose-sub"></div>
                    </div>
                </div>
                <div class="preview-content-area rose-content">
                    <div class="preview-section-title rose-title"></div>
                    <div class="preview-cards-row">
                        <div class="preview-card rose-card"></div>
                        <div class="preview-card rose-card"></div>
                        <div class="preview-card rose-card"></div>
                    </div>
                </div>
                <div class="preview-footer-bar rose-footer"></div>
            </div>
            <div class="template-info">
                <h3>Rosé</h3>
                <p>Design doux et raffiné aux tons roses et terracotta. Typographie serif élégante. Parfait pour les pâtisseries, salons de thé et brunchs.</p>
                <div class="template-colors">
                    <span class="color-dot" style="background: #be185d;"></span>
                    <span class="color-dot" style="background: #fce7f3;"></span>
                    <span class="color-dot" style="background: #500724;"></span>
                    <span class="color-dot" style="background: #fffbfc;"></span>
                </div>
            </div>
            <div class="template-actions">
                <?php if ($currentTemplate === 'rose'): ?>
                    <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                <?php else: ?>
                    <form method="POST" action="?page=edit-template&action=save-template">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="template" value="rose">
                        <button type="submit" class="btn">Appliquer</button>
                    </form>
                <?php endif; ?>
                <?php if (!empty($slug)): ?>
                    <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_template=rose" target="_blank" class="btn btn-outline">
                        <i class="fas fa-eye"></i> Aperçu
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
