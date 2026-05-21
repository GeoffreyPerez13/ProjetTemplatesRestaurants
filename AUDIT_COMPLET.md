# Audit Complet — MenuMiam (ProjetTemplatesRestaurants)

**Date** : 20/05/2026  
**Version** : V1Projet  
**Stack** : PHP 8.x (vanilla MVC), MySQL 8.4.7, HTML/CSS/JS, Stripe, Chart.js

---

## 1. SÉCURITÉ

### ✅ Points positifs

| Aspect | Implémentation |
|--------|---------------|
| **CSRF** | Token 256-bit (`random_bytes(32)` + `bin2hex`), vérifié via `hash_equals`, rotation après validation non-AJAX |
| **SQL Injection** | Requêtes préparées (PDO `prepare/execute`) systématiques dans tous les contrôleurs et modèles |
| **XSS** | `htmlspecialchars()` utilisé massivement (248 occurrences dans 27 fichiers de vues) |
| **Upload** | Double validation : MIME type (`finfo_file`) + extension whitelist, taille max 5Mo, nom de fichier sécurisé (timestamp + uniqid) |
| **Headers sécurité** | `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `X-XSS-Protection`, `Referrer-Policy` |
| **CSP** | Content-Security-Policy complète (script-src, style-src, font-src, img-src, frame-src, connect-src) |
| **Auth** | `session_regenerate_id(true)` après login (anti-fixation), rate limiting login (5 tentatives / 15 min) |
| **Password** | Bcrypt ($2y$), validation stricte (8+ chars, maj, min, chiffre, caractère spécial) |
| **Path traversal** | Protection dans `render()` avec `realpath()` + vérification du préfixe |
| **Accès fichiers** | `.htaccess` bloque `config.php`, `Options -Indexes`, `.gitignore` exclut les secrets |
| **Démo** | Mode isolé avec expiration, `blockIfDemo()` pour empêcher les modifications |
| **Abonnement** | `isReadOnly()` + `requireActiveSubscription()` pour bloquer les modifications sans abonnement |

### ⚠️ Points à corriger (CRITIQUE)

| # | Problème | Risque | Recommandation |
|---|----------|--------|----------------|
| 1 | **Session sans cookie sécurisé** | Vol de session via HTTP | Ajouter avant `session_start()` :<br>`ini_set('session.cookie_httponly', 1);`<br>`ini_set('session.cookie_secure', 1);`<br>`ini_set('session.cookie_samesite', 'Strict');`<br>`ini_set('session.use_strict_mode', 1);` |
| 2 | **Pas de rate limiting sur `publicBook()`** | Spam de réservations / DoS | Implémenter un rate limit (ex: max 3 réservations / IP / heure) ou un honeypot / reCAPTCHA |
| 3 | **Rate limiting login basé sur SESSION** | Contournable en supprimant les cookies | Stocker les tentatives en BDD ou fichier, par IP |
| 4 | **Pas de header `Strict-Transport-Security`** | Downgrade HTTPS → HTTP | Ajouter `header('Strict-Transport-Security: max-age=31536000; includeSubDomains');` en production |
| 5 | **`error_log` expose des détails en dev** | Fuite d'info si logs accessibles | S'assurer que `display_errors = Off` en production, vérifier les chemins des logs |
| 6 | **CSP inclut `'unsafe-inline'`** | Réduit l'efficacité anti-XSS | À terme : remplacer par des nonces ou hashes pour les scripts inline |

### ℹ️ Points mineurs

| # | Problème | Recommandation |
|---|----------|----------------|
| 7 | `seed-reviews` accessible sans auth CSRF | Protéger ou supprimer avant production |
| 8 | Messages d'erreur exposent parfois `$e->getMessage()` | En production, ne jamais montrer les exceptions brutes |
| 9 | Pas de protection contre le clickjacking via CSP `frame-ancestors` | Ajouter `frame-ancestors 'none'` à la CSP |

---

## 2. RESPONSIVE DESIGN

### ✅ Points positifs
- **Breakpoints cohérents** : 1024px (tablette), 768px (mobile), 600px, 480px, 400px
- **Grilles adaptatives** : `grid-template-columns` et `repeat(auto-fill, minmax(...))` utilisés
- **Dark mode complet** : Variables CSS overridées, pas de couleurs hardcodées dans le HTML
- **Mobile-first pour la vitrine** (display)

### ⚠️ Points à vérifier / améliorer

| # | Page | Problème | Priorité |
|---|------|----------|----------|
| 1 | **Landing** (`landing.php`) | Page de 32Ko — tester sur petits écrans (< 375px) | Moyenne |
| 2 | **Floor-plan** | Canvas a `min-width: 600-700px` — sur mobile le plan est difficilement utilisable | Basse (fonctionnel mais UX limitée) |
| 3 | **Edit-card** | Fichier de 55Ko côté contrôleur — vérifier les formulaires batch add sur petit écran | Moyenne |
| 4 | **Legal pages** | Vérifier que le texte long ne déborde pas (18Ko de contrôleur) | Basse |
| 5 | **Toutes pages** | Les `max-width: 850px` du base.css s'appliquent aux forms — vérifier qu'aucune page ne sort | Basse |

---

## 3. TYPOGRAPHIE & TAILLES

### ✅ Points positifs
- **Variables centralisées** : `--font-family: 'Inter', 'Segoe UI', system-ui, sans-serif`
- **Hiérarchie claire** : Variables d'espacement (`--spacing-xs` à `--spacing-3xl`)
- **Font-weight cohérent** : 400 (body), 600 (labels, boutons), 700 (titres)

### ⚠️ Recommandations

| # | Problème | Recommandation |
|---|----------|----------------|
| 1 | Pas de tailles de police variables dans `:root` | Créer des variables `--font-size-xs: 0.75rem`, `--font-size-sm: 0.85rem`, `--font-size-base: 1rem`, `--font-size-lg: 1.1rem`, `--font-size-xl: 1.25rem` pour uniformiser |
| 2 | Certaines tailles hardcodées (`0.78rem`, `0.82rem`, `0.85rem`, `0.9rem`) | Standardiser sur 3-4 paliers maximum |
| 3 | Pas de `clamp()` pour les titres | Utiliser `font-size: clamp(1rem, 2vw + 0.5rem, 1.5rem)` pour un scaling fluide |
| 4 | `line-height: 1.6` peut être trop aéré pour les petits textes | Considérer `line-height: 1.4` pour les éléments < 0.85rem |

---

## 4. FONCTIONNALITÉS

### ✅ Features implémentées et fonctionnelles
- Auth (login, register via invitation, auto-register, reset password, email verify)
- Dashboard admin avec KPI
- Carte (mode éditable + mode images) avec catégories, plats, allergènes
- Menus du jour / Formules
- Contact, Logo, Bannière, Services, Templates
- Réservations en ligne (dashboard, liste, paramètres, API publique)
- Plan de salle (étages, tables, éléments, canvas)
- Statistiques avancées (Chart.js, visites, appareils, référents)
- Paramètres (options, Google Reviews, fermetures, livraison, premium, abonnements)
- Stripe (checkout, webhook, abonnements, résiliation)
- Notifications en temps réel (SSE)
- Mode démo (clones isolés, expiration, tokens)
- Dark mode
- Tour guidé
- Sitemap dynamique
- Pages légales
- Gestion clients (SUPER_ADMIN)
- CRON auto-complete reservations

### ⚠️ Edge cases à traiter

| # | Problème | Impact | Recommandation |
|---|----------|--------|----------------|
| 1 | **Pas de CAPTCHA** sur `publicBook()` et `auto-register` | Spam | Ajouter reCAPTCHA v3 ou honeypot |
| 2 | **Pas de confirmation email** pour les réservations | Réservations fantômes | Envoyer un email de confirmation au client |
| 3 | **Suppression en cascade** | Supprimer un admin supprime tout (catégories, plats, logos…) | C'est voulu (FK CASCADE), mais ajouter une confirmation forte + soft-delete ? |
| 4 | **admin_options et card_images sont MyISAM** | Pas de FK constraints, pas de transactions | Migrer en InnoDB si possible |
| 5 | **Pas de gestion des images orphelines** | Fichiers qui restent sur le disque si erreur | Ajouter un script de nettoyage périodique |
| 6 | **SSE (notification-stream)** | Long polling bloque un worker PHP par connexion | Acceptable pour un petit nombre d'admins connectés simultanément |
| 7 | **Timezone** | `date_default_timezone_set('Europe/Paris')` en dur dans `publicBook()` | Centraliser dans `config.php` ou respecter le fuseau du restaurant |

---

## 5. PERFORMANCE

### ✅ Points positifs
- PDO avec connexion unique (passée par injection)
- CSS séparé par section (chargement conditionnel possible)
- Images uploadées avec noms uniques (cache-friendly)
- `defer` sur les scripts JS

### ⚠️ Recommandations

| # | Problème | Impact | Recommandation |
|---|----------|--------|----------------|
| 1 | **Pas de minification CSS/JS** | Taille des assets en production | Mettre en place un build step (ou `.htaccess` mod_deflate) |
| 2 | **Pas de cache-busting** (query string `?v=`) | Changements CSS/JS non pris en compte après déploiement | Ajouter `?v=<?= filemtime($file) ?>` aux includes |
| 3 | **Beaucoup de `require_once` au top-level** | 16 contrôleurs chargés pour CHAQUE requête | Utiliser un autoloader PSR-4 (Composer) |
| 4 | **Font Awesome chargé entièrement** | ~80Ko CSS pour quelques icônes | Pas critique, mais considérer un subset ou des SVG inline |
| 5 | **Pas de compression gzip/brotli** dans `.htaccess` | Transfert plus lourd | Ajouter `mod_deflate` dans le `.htaccess` public |
| 6 | **Chart.js chargé depuis CDN** | Dépendance externe | Acceptable — le CDN est rapide et cacheable |

---

## 6. ACCESSIBILITÉ

### ⚠️ Points à améliorer

| # | Problème | Impact | Recommandation |
|---|----------|--------|----------------|
| 1 | **Pas d'attributs `aria-label`** sur les boutons icon-only (`.btn-icon`) | Lecteurs d'écran ne peuvent pas les identifier | Ajouter `aria-label="Supprimer"` etc. |
| 2 | **Pas de `role` sur les onglets** | Navigation onglets non sémantique | Ajouter `role="tablist"`, `role="tab"`, `role="tabpanel"`, `aria-selected` |
| 3 | **`title` utilisé mais pas `aria-label`** | `title` n'est pas lu par tous les lecteurs d'écran | Doubler avec `aria-label` |
| 4 | **Focus visible** | Pas de style `:focus-visible` personnalisé | Ajouter un outline visible pour la navigation clavier |
| 5 | **Contraste** | `--color-text-muted: #a8a29e` sur fond blanc = ratio ~2.8:1 | Passer à `#78716c` minimum (ratio 4.5:1) pour le texte informatif important |
| 6 | **Formulaires** | Certains `<label>` ne sont pas associés via `for=` | Vérifier tous les formulaires |
| 7 | **Images** | Vérifier que toutes les `<img>` ont un `alt` significatif | Audit image par image |

---

## 7. QUALITÉ DE CODE

### ✅ Points positifs
- Architecture MVC claire (Controllers / Models / Views)
- Séparation des responsabilités (BaseController, Helpers, Services)
- Nommage cohérent (snake_case BDD, camelCase PHP)
- Commentaires PHPDoc sur les méthodes principales
- Gestion des erreurs avec try/catch et error_log

### ⚠️ Recommandations

| # | Problème | Recommandation |
|---|----------|----------------|
| 1 | **Pas d'autoloader** | Utiliser Composer + PSR-4 |
| 2 | **Controllers très volumineux** | `SettingsController` = 60Ko, `CardController` = 55Ko — découper en sous-contrôleurs ou traits |
| 3 | **Duplication de code** | La vérification SUPER_ADMIN + auth est répétée dans `index.php` — extraire en middleware |
| 4 | **Pas de tests automatisés** | `tests/` est vide — ajouter au minimum des tests unitaires pour les modèles |
| 5 | **`error_log` avec `print_r`** | Retirer les `error_log("POST data: " . print_r(...))` avant production |
| 6 | **Pas de `.env`** | Les secrets sont dans `config.php` (gitignored) — acceptable, mais `.env` + `vlucas/phpdotenv` serait plus standard |

---

## 8. SEO (Pages publiques)

### ✅ Points positifs
- `robots.txt` bien configuré (pages admin bloquées, sitemap déclarée)
- Sitemap dynamique (`SitemapController`)
- Balises `<meta>` dans les pages display

### ⚠️ Recommandations
- Ajouter des balises Open Graph (`og:title`, `og:image`, `og:description`) pour le partage social
- Vérifier la balise `<meta name="description">` sur chaque page vitrine
- Ajouter `<link rel="canonical">` pour éviter le duplicate content

---

## 9. DÉPLOIEMENT / PRODUCTION

### Checklist pré-lancement

| # | Action | Statut |
|---|--------|--------|
| 1 | `display_errors = Off` dans php.ini production | ❓ À vérifier |
| 2 | `session.cookie_secure = 1` (HTTPS obligatoire) | ❌ À ajouter |
| 3 | Certificat SSL valide (Let's Encrypt ou autre) | ❓ À vérifier |
| 4 | Supprimer/protéger `seed-reviews`, `seed-demo` | ❌ À faire |
| 5 | Retirer les `error_log(print_r(...))` de debug | ❌ À faire |
| 6 | Configurer un backup automatique de la BDD | ❓ À vérifier |
| 7 | Tester le webhook Stripe en mode live | ❓ À vérifier |
| 8 | Configurer le CRON en production | ❓ À vérifier |
| 9 | Vérifier les permissions des dossiers `uploads/`, `logos/`, `banners/` | ❓ À vérifier |
| 10 | Minifier CSS/JS ou activer gzip | ❌ À faire |
| 11 | Ajouter compression gzip dans `.htaccess` | ❌ À faire |
| 12 | Tester toutes les pages sur mobile réel (iPhone SE, Galaxy S20) | ❓ À faire |

---

## RÉSUMÉ DES PRIORITÉS

### 🔴 Critique (à faire avant lancement)
1. Sécuriser les cookies de session (`httponly`, `secure`, `samesite`)
2. Rate limiting sur `publicBook()` (anti-spam réservations)
3. Rate limiting login basé sur IP (pas session)
4. HSTS header en production
5. Supprimer/protéger les routes de seed

### 🟠 Important (à faire rapidement après lancement)
6. CAPTCHA ou honeypot sur formulaires publics
7. Confirmation email pour les réservations
8. Cache-busting sur les assets CSS/JS
9. Compression gzip
10. Attributs `aria-label` sur les boutons icon-only

### 🟡 Moyen terme
11. Autoloader PSR-4 (Composer)
12. Tests unitaires
13. Standardiser les tailles de police (variables)
14. Migrer `admin_options` et `card_images` en InnoDB
15. Open Graph pour les vitrines

---

*Audit réalisé par analyse statique du code. Un pen-test et des tests manuels sur mobile sont recommandés avant la mise en production.*
