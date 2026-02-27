<?php
$title = "Tableau de bord";
$scripts = ["js/sections/dashboard/dashboard.js", "js/effects/accordion.js"];

// Formater la date si elle existe
$formatted_date = null;
if (!empty($last_updated)) {
    $date = new DateTime($last_updated);
    $formatted_date = $date->format('d/m/Y à H:i');
}

require __DIR__ . '/../partials/header.php';
?>
<div class="dashboard">
    <!-- En-tête avec titre et bouton paramètres -->
    <div class="dashboard-header">
        <h2>Tableau de bord</h2>
        <a href="?page=settings" class="settings-icon-btn" title="Paramètres">
            <span class="settings-icon">⚙️</span>
        </a>
    </div>

    <!-- Messages flash (HTML autorisé pour les liens de démo) -->
    <?php if (!empty($success_message)): ?>
        <div class="message-success"><?= $success_message ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="message-error"><?= $error_message ?></div>
    <?php endif; ?>

    <!-- Affichage du message de bienvenue personnalisé -->
    <div class="welcome-message">
        <p>Bienvenue <strong><?= htmlspecialchars($username) ?></strong>.</p>
        <p>Vous gérez le restaurant : <strong><?= htmlspecialchars($restaurant_name) ?></strong>.</p>

        <?php if (!empty($slug)): ?>
            <p class="visit-site">
                <a href="?page=display&slug=<?= urlencode($slug) ?>" target="_blank" class="btn btn-outline">
                    <i class="fas fa-external-link-alt"></i> Aller sur le site
                </a>
            </p>
        <?php endif; ?>

        <?php if (!empty($last_updated)): ?>
            <p class="last-updated">Dernière modification de la carte le : <em><strong><?= htmlspecialchars($formatted_date) ?></strong></em>.</p>
        <?php else: ?>
            <p class="last-updated">La carte n'a pas encore été modifiée.</p>
        <?php endif; ?>
    </div>

    <!-- Menu déroulant pour mobile -->
    <div class="dashboard-mobile-menu">
        <button class="mobile-menu-toggle" aria-expanded="false" aria-controls="mobile-menu-content">
            <span class="menu-icon">☰</span>
            <span class="menu-text">Menu</span>
        </button>

        <div class="mobile-menu-content" id="mobile-menu-content">
            <div class="mobile-menu-items">
                <a href="?page=edit-card" class="mobile-menu-item">
                    <div class="menu-item-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="menu-item-content">
                        <span class="menu-item-title">Modifier la carte</span>
                        <span class="menu-item-desc">Gérer les catégories et plats</span>
                    </div>
                    <div class="menu-item-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <a href="?page=edit-contact" class="mobile-menu-item">
                    <div class="menu-item-icon">
                        <i class="fas fa-address-book"></i>
                    </div>
                    <div class="menu-item-content">
                        <span class="menu-item-title">Modifier le contact</span>
                        <span class="menu-item-desc">Coordonnées et horaires</span>
                    </div>
                    <div class="menu-item-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <a href="?page=edit-logo-banner" class="mobile-menu-item">
                    <div class="menu-item-icon">
                        <i class="fas fa-image"></i>
                    </div>
                    <div class="menu-item-content">
                        <span class="menu-item-title">Modifier logo/bannière</span>
                        <span class="menu-item-desc">Logo du restaurant</span>
                    </div>
                    <div class="menu-item-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <!-- Services & Options -->
                <a href="?page=edit-services" class="mobile-menu-item">
                    <div class="menu-item-icon">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <div class="menu-item-content">
                        <span class="menu-item-title">Services, paiements & réseaux</span>
                        <span class="menu-item-desc">Gérer les services et contacts</span>
                    </div>
                    <div class="menu-item-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <a href="?page=edit-template" class="mobile-menu-item">
                    <div class="menu-item-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="menu-item-content">
                        <span class="menu-item-title">Choisir un template</span>
                        <span class="menu-item-desc">Design du site vitrine</span>
                    </div>
                    <div class="menu-item-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>

                <a href="?page=view-card" class="mobile-menu-item success">
                    <div class="menu-item-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="menu-item-content">
                        <span class="menu-item-title">Aperçu de la carte</span>
                        <span class="menu-item-desc">Voir comme vos clients</span>
                    </div>
                    <div class="menu-item-arrow">
                        <i class="fas fa-external-link-alt"></i>
                    </div>
                </a>

                <?php if ($role === 'SUPER_ADMIN'): ?>
                    <a href="?page=send-invitation" class="mobile-menu-item admin">
                        <div class="menu-item-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="menu-item-content">
                            <span class="menu-item-title">Inviter un utilisateur</span>
                            <span class="menu-item-desc">Création de compte</span>
                        </div>
                        <div class="menu-item-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>

                    <a href="?page=manage-clients" class="mobile-menu-item premium">
                        <div class="menu-item-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="menu-item-content">
                            <span class="menu-item-title">Gérer les clients Premium</span>
                            <span class="menu-item-desc">Abonnements et fonctionnalités</span>
                        </div>
                        <div class="menu-item-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                <?php endif; ?>
            </div>

            <div class="mobile-menu-footer">
                <a href="?page=settings" class="settings-menu-link">
                    <i class="fas fa-cog"></i> Paramètres du compte
                </a>
            </div>
        </div>
    </div>

    <!-- Boutons normaux pour desktop -->
    <div class="dashboard-desktop">
        <div class="dashboard-top-buttons">
            <a href="?page=edit-card" class="btn">Modifier la carte</a>
            <a href="?page=edit-contact" class="btn">Modifier le contact</a>
            <a href="?page=edit-logo-banner" class="btn">Modifier logo/bannière</a>
            <a href="?page=edit-services" class="btn">Services, paiements & réseaux</a>
            <a href="?page=edit-template" class="btn">Choisir un template</a>
        </div>

        <!-- Zone du bas pour les boutons d'action desktop -->
        <div class="dashboard-bottom desktop-bottom">
            <div class="bottom-left">
                <?php if ($role === 'SUPER_ADMIN'): ?>
                    <a href="?page=send-invitation" class="btn">Envoyer un lien de création de compte</a>
                    <a href="?page=manage-clients" class="btn premium-btn">
                        <i class="fas fa-crown"></i> Gérer les clients Premium
                    </a>
                <?php endif; ?>
                <a href="?page=view-card" class="btn success">Aperçu de la carte</a>
            </div>
            <div class="bottom-right">
                <a href="?page=<?= !empty($is_demo) ? 'demo-logout' : 'logout' ?>" class="btn danger">Se déconnecter</a>
            </div>
        </div>
    </div>

    <!-- Section gestion démo (SUPER_ADMIN uniquement, hors mode démo) -->
    <?php if ($role === 'SUPER_ADMIN' && empty($is_demo)): ?>
        <div class="demo-management-section">
            <h3><i class="fas fa-flask"></i> Gestion de la démo client</h3>

            <?php if (!$demoExists): ?>
                <p class="demo-info">Le restaurant de démo n'existe pas encore.</p>
                <a href="?page=seed-demo" class="btn">Créer le restaurant de démo</a>
            <?php else: ?>
                <div class="demo-actions">
                    <a href="?page=generate-demo" class="btn"><i class="fas fa-link"></i> Générer un lien de démo (3 jours)</a>
                    <a href="?page=demo" target="_blank" class="btn btn-outline"><i class="fas fa-eye"></i> Voir la vitrine démo</a>
                    <a href="?page=seed-demo&action=clean" class="btn danger" onclick="return confirm('Supprimer le restaurant de démo et toutes ses données ?')"><i class="fas fa-trash"></i> Supprimer la démo</a>
                </div>

                <?php if (!empty($demoTokens)): ?>
                    <div class="demo-tokens-list">
                        <!-- Accordion pour les liens actifs -->
                        <div class="accordion-section demo-tokens-accordion">
                            <div class="accordion-header">
                                <h4><i class="fas fa-link"></i> Liens actifs (<?= count($demoTokens) ?>)</h4>
                                <button type="button" class="accordion-toggle" data-target="demo-tokens-content"><i class="fas fa-chevron-up"></i></button>
                            </div>
                            <div id="demo-tokens-content" class="accordion-content expanded prevent-auto-close">
                                <!-- Boutons d'actions en masse -->
                                <div class="demo-bulk-actions">
                                    <button type="button" id="demo-copy-all" class="btn btn-sm" title="Copier tous les liens"><i class="fas fa-copy"></i> Copier tous les liens</button>
                                    <button type="button" id="demo-delete-selected" class="btn danger btn-sm" style="display:none;" title="Supprimer la sélection"><i class="fas fa-trash"></i> Supprimer la sélection (<span id="demo-selected-count">0</span>)</button>
                                </div>
                                <table class="demo-tokens-table">
                                    <thead>
                                        <tr>
                                            <th class="th-checkbox"><input type="checkbox" id="demo-select-all" title="Tout sélectionner"></th>
                                            <th>Utilisateur</th>
                                            <th>Lien</th>
                                            <th>Expire le</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($demoTokens as $dt): ?>
                                            <tr data-token-id="<?= $dt['id'] ?>">
                                                <td class="td-checkbox"><input type="checkbox" class="demo-row-check" value="<?= $dt['id'] ?>"></td>
                                                <td>
                                                    <input type="text"
                                                           class="demo-label-input"
                                                           data-id="<?= $dt['id'] ?>"
                                                           value="<?= htmlspecialchars($dt['label'] ?? '') ?>"
                                                           placeholder="Nom du client..."
                                                           maxlength="100">
                                                </td>
                                                <td>
                                                    <code class="demo-token-link" title="Cliquer pour copier" onclick="navigator.clipboard.writeText(this.textContent.trim()).then(()=>this.classList.add('copied'))"><?= htmlspecialchars(SITE_URL . '/index.php?page=demo-access&token=' . $dt['token']) ?></code>
                                                </td>
                                                <td><?= (new DateTime($dt['expires_at']))->format('d/m/Y H:i') ?></td>
                                                <td>
                                                    <a href="?page=delete-demo-token&id=<?= $dt['id'] ?>" class="btn danger btn-sm" title="Révoquer" onclick="return confirm('Révoquer ce lien ?')"><i class="fas fa-times"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <script>
                    (function() {
                        // --- Label auto-save ---
                        document.querySelectorAll('.demo-label-input').forEach(function(input) {
                            var saved = input.value;
                            function saveLabel() {
                                var val = input.value.trim();
                                if (val === saved) return;
                                saved = val;
                                input.classList.remove('saved', 'error');
                                fetch('?page=update-demo-label', {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                    body: 'id=' + encodeURIComponent(input.dataset.id) + '&label=' + encodeURIComponent(val)
                                })
                                .then(function(r) { return r.json(); })
                                .then(function(d) { input.classList.add(d.success ? 'saved' : 'error'); })
                                .catch(function() { input.classList.add('error'); });
                            }
                            input.addEventListener('blur', saveLabel);
                            input.addEventListener('keydown', function(e) {
                                if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
                            });
                        });

                        // --- Checkbox / bulk selection ---
                        var selectAll = document.getElementById('demo-select-all');
                        var rowChecks = document.querySelectorAll('.demo-row-check');
                        var deleteBtn = document.getElementById('demo-delete-selected');
                        var countSpan = document.getElementById('demo-selected-count');
                        var copyAllBtn = document.getElementById('demo-copy-all');

                        function updateBulkUI() {
                            var checked = document.querySelectorAll('.demo-row-check:checked');
                            countSpan.textContent = checked.length;
                            deleteBtn.style.display = checked.length > 0 ? 'inline-flex' : 'none';
                            selectAll.checked = checked.length === rowChecks.length && rowChecks.length > 0;
                            selectAll.indeterminate = checked.length > 0 && checked.length < rowChecks.length;
                        }

                        if (selectAll) {
                            selectAll.addEventListener('change', function() {
                                rowChecks.forEach(function(cb) { cb.checked = selectAll.checked; });
                                updateBulkUI();
                            });
                        }

                        rowChecks.forEach(function(cb) {
                            cb.addEventListener('change', updateBulkUI);
                        });

                        // Suppression en masse
                        if (deleteBtn) {
                            deleteBtn.addEventListener('click', function() {
                                var ids = [];
                                document.querySelectorAll('.demo-row-check:checked').forEach(function(cb) {
                                    ids.push(cb.value);
                                });
                                if (ids.length === 0) return;
                                if (!confirm('Supprimer ' + ids.length + ' lien(s) de d\u00e9mo ?')) return;
                                window.location.href = '?page=delete-demo-tokens-bulk&ids=' + ids.join(',');
                            });
                        }

                        // Copier tous les liens
                        if (copyAllBtn) {
                            copyAllBtn.addEventListener('click', function() {
                                var links = [];
                                document.querySelectorAll('.demo-token-link').forEach(function(el) {
                                    links.push(el.textContent.trim());
                                });
                                navigator.clipboard.writeText(links.join('\n')).then(function() {
                                    copyAllBtn.innerHTML = '<i class="fas fa-check"></i> Copi\u00e9 !';
                                    copyAllBtn.classList.add('copied');
                                    setTimeout(function() {
                                        copyAllBtn.innerHTML = '<i class="fas fa-copy"></i> Copier tous les liens';
                                        copyAllBtn.classList.remove('copied');
                                    }, 2000);
                                });
                            });
                        }
                    })();
                    </script>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Bouton déconnexion toujours visible sur mobile -->
    <div class="mobile-logout-container">
        <a href="?page=<?= !empty($is_demo) ? 'demo-logout' : 'logout' ?>" class="btn danger mobile-logout">Se déconnecter</a>
    </div>
</div>

<?php
require __DIR__ . '/../partials/footer.php';
?>