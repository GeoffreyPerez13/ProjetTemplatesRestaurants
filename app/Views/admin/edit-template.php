<?php
$title = "Personnaliser le site vitrine";
$scripts = ["js/effects/accordion.js", "js/sections/edit-template/edit-template.js"];

// Déterminer quel accordéon ouvrir après un enregistrement
$openAccordion = $_SESSION['open_template_accordion'] ?? '';
unset($_SESSION['open_template_accordion']);

require __DIR__ . '/../partials/header.php';
?>

<a href="?page=dashboard" class="btn-back">Retour</a>

<div class="template-selector">
    <div class="template-header">
        <h2><i class="fas fa-paint-brush"></i> Personnaliser le site vitrine</h2>
        <p class="template-subtitle">Choisissez la palette de couleurs et le design de votre site. Combinez-les librement !</p>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="message-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="message-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <!-- Boutons de contrôle généraux pour tous les accordéons -->
    <div class="global-accordion-controls">
        <button type="button" id="expand-all-accordions" class="btn"><i class="fas fa-expand-alt"></i> Tout ouvrir</button>
        <button type="button" id="collapse-all-accordions" class="btn"><i class="fas fa-compress-alt"></i> Tout fermer</button>
    </div>

    <!-- ==================== SECTION 1 : PALETTES DE COULEURS ==================== -->
    <div class="accordion-section template-section">
        <div class="accordion-header">
            <h2><i class="fas fa-palette"></i> Palette de couleurs</h2>
            <button type="button" class="accordion-toggle" data-target="palette-content">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>

        <div id="palette-content" class="accordion-content <?= $openAccordion === 'palette' ? 'expanded prevent-auto-close' : 'collapsed' ?>">
            <p class="template-section-desc">Les couleurs, les tons et l'ambiance de votre site.</p>

            <div class="templates-grid">
            <!-- Palette Classique -->
            <div class="template-card <?= $currentPalette === 'classic' ? 'active' : '' ?>">
                <div class="template-preview template-preview-classic">
                    <div class="preview-header-bar">
                        <div class="preview-logo-dot"></div>
                        <div class="preview-nav-dots"><span></span><span></span><span></span></div>
                    </div>
                    <div class="preview-banner-area classic-banner">
                        <div class="preview-banner-text">
                            <div class="preview-title-line"></div>
                            <div class="preview-subtitle-line"></div>
                        </div>
                    </div>
                    <div class="preview-content-area">
                        <div class="preview-section-title"></div>
                        <div class="preview-cards-row"><div class="preview-card"></div><div class="preview-card"></div><div class="preview-card"></div></div>
                    </div>
                    <div class="preview-footer-bar classic-footer"></div>
                </div>
                <div class="template-info">
                    <h3>Classique</h3>
                    <p>Tons cuivrés chaleureux. Idéal pour les brasseries et restaurants familiaux.</p>
                    <div class="template-colors">
                        <span class="color-dot" style="background: #b45309;"></span>
                        <span class="color-dot" style="background: #fef7ed;"></span>
                        <span class="color-dot" style="background: #1c1917;"></span>
                        <span class="color-dot" style="background: #ffffff;"></span>
                    </div>
                </div>
                <div class="template-actions">
                    <?php if ($currentPalette === 'classic'): ?>
                        <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                    <?php else: ?>
                        <form method="POST" action="?page=edit-template&action=save-palette">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="palette" value="classic">
                            <button type="submit" class="btn">Appliquer</button>
                        </form>
                    <?php endif; ?>
                    <?php if (!empty($slug)): ?>
                        <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_palette=classic" target="_blank" class="btn btn-outline"><i class="fas fa-eye"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Palette Moderne -->
            <div class="template-card <?= $currentPalette === 'modern' ? 'active' : '' ?>">
                <div class="template-preview template-preview-modern">
                    <div class="preview-header-bar modern-header">
                        <div class="preview-logo-dot modern-dot"></div>
                        <div class="preview-nav-dots modern-nav"><span></span><span></span><span></span></div>
                    </div>
                    <div class="preview-banner-area modern-banner">
                        <div class="preview-banner-text"><div class="preview-title-line modern-line"></div></div>
                    </div>
                    <div class="preview-content-area modern-content">
                        <div class="preview-section-title modern-title"></div>
                        <div class="preview-cards-row modern-cards"><div class="preview-card modern-card"></div><div class="preview-card modern-card"></div></div>
                    </div>
                    <div class="preview-footer-bar modern-footer"></div>
                </div>
                <div class="template-info">
                    <h3>Moderne</h3>
                    <p>Tons bleu nuit minimalistes. Parfait pour les restaurants branchés.</p>
                    <div class="template-colors">
                        <span class="color-dot" style="background: #2563eb;"></span>
                        <span class="color-dot" style="background: #f0f9ff;"></span>
                        <span class="color-dot" style="background: #0f172a;"></span>
                        <span class="color-dot" style="background: #ffffff;"></span>
                    </div>
                </div>
                <div class="template-actions">
                    <?php if ($currentPalette === 'modern'): ?>
                        <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                    <?php else: ?>
                        <form method="POST" action="?page=edit-template&action=save-palette">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="palette" value="modern">
                            <button type="submit" class="btn">Appliquer</button>
                        </form>
                    <?php endif; ?>
                    <?php if (!empty($slug)): ?>
                        <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_palette=modern" target="_blank" class="btn btn-outline"><i class="fas fa-eye"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Palette Élégant -->
            <div class="template-card <?= $currentPalette === 'elegant' ? 'active' : '' ?>">
                <div class="template-preview template-preview-elegant">
                    <div class="preview-header-bar elegant-header">
                        <div class="preview-logo-dot elegant-dot"></div>
                        <div class="preview-nav-dots elegant-nav"><span></span><span></span><span></span></div>
                    </div>
                    <div class="preview-banner-area elegant-banner">
                        <div class="preview-banner-text">
                            <div class="preview-title-line elegant-line"></div>
                            <div class="preview-subtitle-line elegant-sub"></div>
                        </div>
                    </div>
                    <div class="preview-content-area elegant-content">
                        <div class="preview-section-title elegant-title"></div>
                        <div class="preview-cards-row"><div class="preview-card elegant-card"></div><div class="preview-card elegant-card"></div><div class="preview-card elegant-card"></div></div>
                    </div>
                    <div class="preview-footer-bar elegant-footer"></div>
                </div>
                <div class="template-info">
                    <h3>Élégant</h3>
                    <p>Fond sombre avec accents dorés. Idéal pour la gastronomie haut de gamme.</p>
                    <div class="template-colors">
                        <span class="color-dot" style="background: #d4a853;"></span>
                        <span class="color-dot" style="background: #1a1a2e;"></span>
                        <span class="color-dot" style="background: #f5f0e8;"></span>
                        <span class="color-dot" style="background: #0d0d1a;"></span>
                    </div>
                </div>
                <div class="template-actions">
                    <?php if ($currentPalette === 'elegant'): ?>
                        <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                    <?php else: ?>
                        <form method="POST" action="?page=edit-template&action=save-palette">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="palette" value="elegant">
                            <button type="submit" class="btn">Appliquer</button>
                        </form>
                    <?php endif; ?>
                    <?php if (!empty($slug)): ?>
                        <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_palette=elegant" target="_blank" class="btn btn-outline"><i class="fas fa-eye"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Palette Nature -->
            <div class="template-card <?= $currentPalette === 'nature' ? 'active' : '' ?>">
                <div class="template-preview template-preview-nature">
                    <div class="preview-header-bar nature-header">
                        <div class="preview-logo-dot nature-dot"></div>
                        <div class="preview-nav-dots nature-nav"><span></span><span></span><span></span></div>
                    </div>
                    <div class="preview-banner-area nature-banner">
                        <div class="preview-banner-text"><div class="preview-title-line"></div><div class="preview-subtitle-line"></div></div>
                    </div>
                    <div class="preview-content-area nature-content">
                        <div class="preview-section-title nature-title"></div>
                        <div class="preview-cards-row"><div class="preview-card nature-card"></div><div class="preview-card nature-card"></div><div class="preview-card nature-card"></div></div>
                    </div>
                    <div class="preview-footer-bar nature-footer"></div>
                </div>
                <div class="template-info">
                    <h3>Nature</h3>
                    <p>Tons verts frais et organiques. Pour les restaurants bio et végétariens.</p>
                    <div class="template-colors">
                        <span class="color-dot" style="background: #16a34a;"></span>
                        <span class="color-dot" style="background: #f0fdf4;"></span>
                        <span class="color-dot" style="background: #14532d;"></span>
                        <span class="color-dot" style="background: #ffffff;"></span>
                    </div>
                </div>
                <div class="template-actions">
                    <?php if ($currentPalette === 'nature'): ?>
                        <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                    <?php else: ?>
                        <form method="POST" action="?page=edit-template&action=save-palette">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="palette" value="nature">
                            <button type="submit" class="btn">Appliquer</button>
                        </form>
                    <?php endif; ?>
                    <?php if (!empty($slug)): ?>
                        <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_palette=nature" target="_blank" class="btn btn-outline"><i class="fas fa-eye"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Palette Rosé -->
            <div class="template-card <?= $currentPalette === 'rose' ? 'active' : '' ?>">
                <div class="template-preview template-preview-rose">
                    <div class="preview-header-bar rose-header">
                        <div class="preview-logo-dot rose-dot"></div>
                        <div class="preview-nav-dots rose-nav"><span></span><span></span><span></span></div>
                    </div>
                    <div class="preview-banner-area rose-banner">
                        <div class="preview-banner-text"><div class="preview-title-line rose-line"></div><div class="preview-subtitle-line rose-sub"></div></div>
                    </div>
                    <div class="preview-content-area rose-content">
                        <div class="preview-section-title rose-title"></div>
                        <div class="preview-cards-row"><div class="preview-card rose-card"></div><div class="preview-card rose-card"></div><div class="preview-card rose-card"></div></div>
                    </div>
                    <div class="preview-footer-bar rose-footer"></div>
                </div>
                <div class="template-info">
                    <h3>Rosé</h3>
                    <p>Tons roses doux et raffinés. Parfait pour pâtisseries et salons de thé.</p>
                    <div class="template-colors">
                        <span class="color-dot" style="background: #be185d;"></span>
                        <span class="color-dot" style="background: #fce7f3;"></span>
                        <span class="color-dot" style="background: #500724;"></span>
                        <span class="color-dot" style="background: #fffbfc;"></span>
                    </div>
                </div>
                <div class="template-actions">
                    <?php if ($currentPalette === 'rose'): ?>
                        <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                    <?php else: ?>
                        <form method="POST" action="?page=edit-template&action=save-palette">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="palette" value="rose">
                            <button type="submit" class="btn">Appliquer</button>
                        </form>
                    <?php endif; ?>
                    <?php if (!empty($slug)): ?>
                        <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_palette=rose" target="_blank" class="btn btn-outline"><i class="fas fa-eye"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Palette Bistro -->
            <div class="template-card <?= $currentPalette === 'bistro' ? 'active' : '' ?>">
                <div class="template-preview template-preview-bistro">
                    <div class="preview-header-bar bistro-header">
                        <div class="preview-logo-dot bistro-dot"></div>
                        <div class="preview-nav-dots bistro-nav"><span></span><span></span><span></span></div>
                    </div>
                    <div class="preview-banner-area bistro-banner">
                        <div class="preview-banner-text"><div class="preview-title-line bistro-line"></div><div class="preview-subtitle-line bistro-sub"></div></div>
                    </div>
                    <div class="preview-content-area bistro-content">
                        <div class="preview-section-title bistro-title"></div>
                        <div class="preview-cards-row bistro-cards"><div class="preview-card bistro-card"></div><div class="preview-card bistro-card"></div><div class="preview-card bistro-card"></div></div>
                    </div>
                    <div class="preview-footer-bar bistro-footer"></div>
                </div>
                <div class="template-info">
                    <h3>Bistro</h3>
                    <p>Tons bordeaux et or. Ambiance cosy pour caves à vin et tables d'hôte.</p>
                    <div class="template-colors">
                        <span class="color-dot" style="background: #7f1d1d;"></span>
                        <span class="color-dot" style="background: #c9a96e;"></span>
                        <span class="color-dot" style="background: #fffbf7;"></span>
                        <span class="color-dot" style="background: #1a0f0f;"></span>
                    </div>
                </div>
                <div class="template-actions">
                    <?php if ($currentPalette === 'bistro'): ?>
                        <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                    <?php else: ?>
                        <form method="POST" action="?page=edit-template&action=save-palette">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="palette" value="bistro">
                            <button type="submit" class="btn">Appliquer</button>
                        </form>
                    <?php endif; ?>
                    <?php if (!empty($slug)): ?>
                        <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_palette=bistro" target="_blank" class="btn btn-outline"><i class="fas fa-eye"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Palette Océan -->
            <div class="template-card <?= $currentPalette === 'ocean' ? 'active' : '' ?>">
                <div class="template-preview template-preview-ocean">
                    <div class="preview-header-bar ocean-header">
                        <div class="preview-logo-dot ocean-dot"></div>
                        <div class="preview-nav-dots ocean-nav"><span></span><span></span><span></span></div>
                    </div>
                    <div class="preview-banner-area ocean-banner">
                        <div class="preview-banner-text"><div class="preview-title-line ocean-line"></div></div>
                    </div>
                    <div class="preview-content-area ocean-content">
                        <div class="preview-section-title ocean-title"></div>
                        <div class="preview-cards-row"><div class="preview-card ocean-card"></div><div class="preview-card ocean-card"></div></div>
                    </div>
                    <div class="preview-footer-bar ocean-footer"></div>
                </div>
                <div class="template-info">
                    <h3>Océan</h3>
                    <p>Tons bleu-vert frais. Pour les restaurants de fruits de mer et beach bars.</p>
                    <div class="template-colors">
                        <span class="color-dot" style="background: #0d9488;"></span>
                        <span class="color-dot" style="background: #14b8a6;"></span>
                        <span class="color-dot" style="background: #f0fdfa;"></span>
                        <span class="color-dot" style="background: #042f2e;"></span>
                    </div>
                </div>
                <div class="template-actions">
                    <?php if ($currentPalette === 'ocean'): ?>
                        <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                    <?php else: ?>
                        <form method="POST" action="?page=edit-template&action=save-palette">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="palette" value="ocean">
                            <button type="submit" class="btn">Appliquer</button>
                        </form>
                    <?php endif; ?>
                    <?php if (!empty($slug)): ?>
                        <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_palette=ocean" target="_blank" class="btn btn-outline"><i class="fas fa-eye"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- ==================== SECTION 2 : DESIGN DU SITE ==================== -->
    <div class="accordion-section template-section">
        <div class="accordion-header">
            <h2><i class="fas fa-layer-group"></i> Design du site</h2>
            <button type="button" class="accordion-toggle" data-target="layout-content">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>

        <div id="layout-content" class="accordion-content <?= $openAccordion === 'layout' ? 'expanded prevent-auto-close' : 'collapsed' ?>">
            <p class="template-section-desc">L'agencement des sections, la disposition des plats et l'organisation du contenu.</p>

            <div class="templates-grid layouts-grid">
            <!-- Layout Standard -->
            <div class="template-card <?= $currentLayout === 'standard' ? 'active' : '' ?>">
                <div class="template-preview template-preview-classic">
                    <div class="preview-header-bar">
                        <div class="preview-logo-dot"></div>
                        <div class="preview-nav-dots"><span></span><span></span><span></span></div>
                    </div>
                    <div class="preview-banner-area classic-banner" style="height: 70px;">
                        <div class="preview-banner-text"><div class="preview-title-line"></div></div>
                    </div>
                    <div class="preview-content-area">
                        <div class="preview-section-title"></div>
                        <div class="preview-cards-row"><div class="preview-card"></div><div class="preview-card"></div><div class="preview-card"></div></div>
                    </div>
                    <div class="preview-footer-bar classic-footer"></div>
                </div>
                <div class="template-info">
                    <h3>Standard</h3>
                    <p>Header horizontal, plats en grille, bannière classique. Le design par défaut, adapté à tous les types de restaurants.</p>
                </div>
                <div class="template-actions">
                    <?php if ($currentLayout === 'standard'): ?>
                        <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                    <?php else: ?>
                        <form method="POST" action="?page=edit-template&action=save-layout">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="layout" value="standard">
                            <button type="submit" class="btn">Appliquer</button>
                        </form>
                    <?php endif; ?>
                    <?php if (!empty($slug)): ?>
                        <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_layout=standard" target="_blank" class="btn btn-outline"><i class="fas fa-eye"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Layout Bistro -->
            <div class="template-card <?= $currentLayout === 'bistro' ? 'active' : '' ?>">
                <div class="template-preview template-preview-bistro">
                    <div class="preview-header-bar bistro-header" style="flex-direction: column; height: 30px; justify-content: center; gap: 2px;">
                        <div class="preview-logo-dot bistro-dot" style="width: 8px; height: 8px;"></div>
                        <div class="preview-nav-dots bistro-nav" style="gap: 3px;"><span style="width: 10px;"></span><span style="width: 10px;"></span><span style="width: 10px;"></span></div>
                    </div>
                    <div class="preview-banner-area bistro-banner" style="height: 55px;">
                        <div class="preview-banner-text"><div class="preview-title-line bistro-line"></div></div>
                    </div>
                    <div class="preview-content-area bistro-content">
                        <div class="preview-section-title bistro-title"></div>
                        <div class="preview-cards-row" style="flex-direction: column; gap: 3px;">
                            <div class="preview-card bistro-card" style="height: 14px;"></div>
                            <div class="preview-card bistro-card" style="height: 14px;"></div>
                            <div class="preview-card bistro-card" style="height: 14px;"></div>
                        </div>
                    </div>
                    <div class="preview-footer-bar bistro-footer"></div>
                </div>
                <div class="template-info">
                    <h3>Bistro</h3>
                    <p>Header centré (logo au-dessus de la nav), plats en liste verticale avec images rondes, images en grille 2 colonnes. Un look intimiste et élégant.</p>
                </div>
                <div class="template-actions">
                    <?php if ($currentLayout === 'bistro'): ?>
                        <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                    <?php else: ?>
                        <form method="POST" action="?page=edit-template&action=save-layout">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="layout" value="bistro">
                            <button type="submit" class="btn">Appliquer</button>
                        </form>
                    <?php endif; ?>
                    <?php if (!empty($slug)): ?>
                        <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_layout=bistro" target="_blank" class="btn btn-outline"><i class="fas fa-eye"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Layout Océan -->
            <div class="template-card <?= $currentLayout === 'ocean' ? 'active' : '' ?>">
                <div class="template-preview template-preview-ocean">
                    <div class="preview-header-bar ocean-header">
                        <div class="preview-logo-dot ocean-dot"></div>
                        <div class="preview-nav-dots ocean-nav"><span></span><span></span><span></span></div>
                    </div>
                    <div class="preview-banner-area ocean-banner" style="height: 95px;">
                        <div class="preview-banner-text"><div class="preview-title-line ocean-line" style="height: 5px;"></div></div>
                    </div>
                    <div class="preview-content-area ocean-content">
                        <div class="preview-section-title ocean-title"></div>
                        <div class="preview-cards-row" style="flex-direction: column; gap: 4px;">
                            <div class="preview-card ocean-card" style="height: 18px;"></div>
                            <div class="preview-card ocean-card" style="height: 18px;"></div>
                        </div>
                    </div>
                    <div class="preview-footer-bar ocean-footer"></div>
                </div>
                <div class="template-info">
                    <h3>Océan</h3>
                    <p>Bannière pleine hauteur, plats en cartes larges alternées (gauche/droite), services en badges horizontaux. Un design immersif et aéré.</p>
                </div>
                <div class="template-actions">
                    <?php if ($currentLayout === 'ocean'): ?>
                        <span class="template-badge active-badge"><i class="fas fa-check-circle"></i> Actif</span>
                    <?php else: ?>
                        <form method="POST" action="?page=edit-template&action=save-layout">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="layout" value="ocean">
                            <button type="submit" class="btn">Appliquer</button>
                        </form>
                    <?php endif; ?>
                    <?php if (!empty($slug)): ?>
                        <a href="?page=display&slug=<?= urlencode($slug) ?>&preview_layout=ocean" target="_blank" class="btn btn-outline"><i class="fas fa-eye"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
