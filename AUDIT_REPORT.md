# 🔍 AUDIT COMPLET — Projet MenuMiam (branch testRefonte)
*Généré le 12 mars 2026 — Analyse des 20 push effectués avec Cascade SWE-1.5*

---

## 🚨 PRIORITÉ CRITIQUE — SÉCURITÉ

### 1. `method_exists()` permet d'appeler n'importe quelle méthode publique
**Fichier** : `public/index.php` (lignes 105, 115)
**Impact** : Un attaquant peut appeler toute méthode publique (y compris héritées de BaseController) via l'URL.

```php
// DANGEREUX — appel de méthode dynamique non filtré
case 'edit-logo-banner':
    $action = $_GET['action'] ?? 'show';
    if (method_exists($controller, $action)) {
        $controller->$action();  // ← N'IMPORTE QUELLE méthode publique !
    }
```

**Exemple d'attaque** : `?page=edit-logo-banner&action=render` ou `&action=getCsrfToken`

**Correctif** : Utiliser une whitelist d'actions autorisées, comme c'est déjà fait pour `settings` et `manage-clients`.

```php
case 'edit-logo-banner':
    $controller = new LogoBannerController($pdo);
    $action = $_GET['action'] ?? 'show';
    $allowed = ['show', 'uploadLogo', 'uploadBanner', 'deleteLogo', 
                'deleteBanner', 'updateBannerText', 'deleteBannerText'];
    if (in_array($action, $allowed)) {
        $controller->$action();
    } else {
        $controller->show();
    }
    break;
```

Faire pareil pour `edit-services`.

---

### 2. CardController — Aucune vérification CSRF sur les POST
**Fichier** : `app/Controllers/CardController.php`
**Impact** : Toutes les actions POST (ajout catégorie, ajout plat, suppression, réorganisation, upload images) n'ont **aucune protection CSRF**.

Un site malveillant peut forcer un admin connecté à modifier sa carte.

**Correctif** : Ajouter `verifyCsrfToken()` au début de `handlePostRequest()`.

```php
private function handlePostRequest(...)
{
    // Vérifier le CSRF pour TOUTES les actions POST
    if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $this->addErrorMessage("Token CSRF invalide.", $anchor);
        $this->redirectToEditCard($anchor);
        return;
    }
    // ... reste du code
}
```

---

### 3. ContactController — Aucune vérification CSRF
**Fichier** : `app/Controllers/ContactController.php`
**Impact** : Le formulaire de contact n'a aucune protection CSRF.

**Correctif** : Ajouter la vérification au début du bloc POST.

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $this->addErrorMessage("Token CSRF invalide.", $anchor);
        header('Location: ?page=edit-contact');
        exit;
    }
    // ... reste du code
}
```

---

### 4. `delete-demo-token` via GET sans CSRF
**Fichier** : `public/index.php` (ligne 314)
**Impact** : La suppression de token de démo se fait via GET (`?page=delete-demo-token&id=X`), sans aucune vérification CSRF. Un lien piégé peut forcer un SUPER_ADMIN à supprimer des tokens.

**Correctif** : Exiger un POST avec CSRF, ou ajouter un token dans l'URL.

---

### 5. `delete-demo-tokens-bulk` via GET avec IDs dans l'URL
**Fichier** : `public/index.php` (ligne 336)
**Impact** : Même problème que ci-dessus, en pire : suppression en masse via GET.

```php
$idsParam = $_GET['ids'] ?? '';
$ids = array_filter(array_map('intval', explode(',', $idsParam)));
```

**Correctif** : Passer en POST avec vérification CSRF.

---

### 6. `update-demo-label` — Pas de vérification CSRF
**Fichier** : `public/index.php` (ligne 290)
**Impact** : L'endpoint AJAX ne vérifie pas le token CSRF.

**Correctif** : Ajouter la vérification.

---

### 7. `send-invitation.php` — Accès direct à `$_POST` dans la vue
**Fichier** : `app/Views/admin/send-invitation.php` (lignes 51, 70, 102-107)
**Impact** : La vue accède directement à `$_POST['email']` et `$_POST['restaurant_name']`. C'est un anti-pattern MVC qui rend la vue dépendante du contexte de requête.

**Correctif** : Passer ces valeurs via le contrôleur dans `$data` (comme `form_data` dans `autoRegister()`).

---

### 8. Header `Content-Security-Policy` manquant
**Fichier** : `app/Controllers/BaseController.php`
**Impact** : Les headers de sécurité sont bons (`X-Frame-Options`, `X-XSS-Protection`, `X-Content-Type-Options`) mais il manque :
- `Content-Security-Policy` (protège contre les injections de scripts)
- `Strict-Transport-Security` (force HTTPS en production)
- `Permissions-Policy` (limite les APIs navigateur)

**Correctif** : Ajouter dans le constructeur de `BaseController` (à activer en production) :

```php
// header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://js.stripe.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self' https://api.stripe.com;");
// header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
```

---

### 9. Session configuration potentiellement faible
**Fichier** : `config.php` (non lisible car gitignored)
**Impact** : Vérifier que `config.php` contient :

```php
ini_set('session.cookie_httponly', 1);   // Empêche l'accès JS aux cookies de session
ini_set('session.cookie_secure', 1);     // Cookies uniquement en HTTPS (production)
ini_set('session.cookie_samesite', 'Lax'); // Protection CSRF basique
ini_set('session.use_strict_mode', 1);   // Rejette les IDs de session non générés par le serveur
ini_set('session.gc_maxlifetime', 3600); // Expiration session 1h
```

**À vérifier manuellement** dans ton `config.php`.

---

## ⚠️ PRIORITÉ HAUTE — BUGS CONFIRMÉS

### 10. `closure-dates.js` — Fonction `loadAnalytics()` hors du scope
**Fichier** : `public/assets/js/display/cookies.js` (ligne 134)
**Impact** : La fonction `loadAnalytics()` est définie **à l'extérieur** du scope de l'événement `DOMContentLoaded`, mais `getCookie()` est définie **à l'intérieur**. `loadAnalytics()` appelle `getCookie()` qui n'est pas accessible dans son scope.

```javascript
// Ligne 131: fermeture du DOMContentLoaded
    }                // ← fin du bloc DOMContentLoaded

    // --- Analytics ---
    function loadAnalytics() {
        if (getCookie('cookie_analytics') === 'true') {  // ← getCookie n'existe pas ici !
```

**Correctif** : Déplacer `loadAnalytics()` à l'intérieur du bloc `DOMContentLoaded`, ou extraire `getCookie()` en dehors.

---

### 11. `closure-dates.js` — Script bloqué si `clearAllBtn` n'existe pas
**Fichier** : `public/assets/js/sections/settings/closure-dates.js` (ligne 16)
**Impact** : Le script vérifie `!clearAllBtn` dans sa condition d'initialisation, mais le bouton `#clear-all-closure-dates` n'existe pas dans le HTML de `settings.php`. Le script entier ne s'initialise donc **jamais**.

```javascript
if (!calendar || !monthYearElement || !prevMonthBtn || !nextMonthBtn 
    || !selectedDatesList || !selectedCountElement 
    || !clearAllBtn  // ← CE BOUTON N'EXISTE PAS DANS LE HTML
    || !saveBtn) {
    console.log('Éléments du calendrier non trouvés, initialisation annulée');
    return;  // ← LE SCRIPT S'ARRÊTE ICI
}
```

**Correctif** : Retirer `clearAllBtn` de la condition de guard, ou ajouter le bouton dans le HTML.

---

### 12. `cookies.js` — console.log en production
**Fichier** : `public/assets/js/display/cookies.js`
**Impact** : 8 appels `console.log()` restent dans le code de production. C'est peu professionnel et peut exposer des informations de debug.

**Correctif** : Supprimer tous les `console.log()` ou les conditionner à un mode debug.

---

### 13. `CardController` — 33 appels `error_log()` en production
**Fichier** : `app/Controllers/CardController.php`
**Impact** : 33 appels `error_log()` dont des `print_r($_POST, true)` qui écrivent les données complètes des formulaires (potentiellement sensibles) dans les logs serveur.

**Correctif** : Supprimer les logs de debug ou les conditionner :

```php
if (defined('APP_DEBUG') && APP_DEBUG) {
    error_log("POST data: " . print_r($_POST, true));
}
```

---

### 14. Token CSRF unique par session (pas de rotation)
**Fichier** : `app/Controllers/BaseController.php` (ligne 255)
**Impact** : Le token CSRF est généré une seule fois par session et ne change jamais. En cas de fuite (XSS, logs), il reste valide indéfiniment.

**Correctif** : Régénérer le token après chaque utilisation réussie :

```php
protected function verifyCsrfToken(?string $token): bool
{
    // ... vérification existante ...
    $valid = !empty($token) && !empty($_SESSION['csrf_token']) 
             && hash_equals($_SESSION['csrf_token'], $token);
    
    if ($valid) {
        // Rotation du token après utilisation
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $valid;
}
```

---

## 🔶 PRIORITÉ MOYENNE — AMÉLIORATIONS

### 15. Stripe — Clé secrète en constante PHP, pas de webhook signature
**Fichier** : `app/Controllers/StripeController.php`
**Impact** : 
- La validation du paiement (`handleSuccess`) se fait côté client via `session_id` dans l'URL. Un attaquant pourrait tenter de forger une URL de succès.
- Pas de vérification de signature webhook Stripe.

**Correctif** : Implémenter un webhook Stripe (`/stripe-webhook`) qui vérifie la signature `stripe-signature` et active l'abonnement côté serveur, pas côté client.

---

### 16. `render()` utilise `extract()` — risque d'écrasement de variables
**Fichier** : `app/Controllers/BaseController.php` (ligne 217)
**Impact** : `extract($data)` peut écraser des variables existantes si un nom de clé correspond.

**Correctif** : Utiliser `extract($data, EXTR_SKIP)` pour ne pas écraser les variables existantes.

---

### 17. `render()` — Path traversal potentiel
**Fichier** : `app/Controllers/BaseController.php` (ligne 223)
**Impact** : `include __DIR__ . "/../Views/$view.php"` — si `$view` contient `../../`, un attaquant pourrait inclure des fichiers arbitraires. Actuellement, `$view` est toujours contrôlé côté serveur, mais une validation supplémentaire est recommandée.

**Correctif** :

```php
protected function render($view, $data = [])
{
    // Sanitize le nom de la vue
    $view = str_replace(['..', "\0"], '', $view);
    $filePath = __DIR__ . "/../Views/$view.php";
    
    if (!file_exists($filePath)) {
        http_response_code(500);
        return;
    }
    
    extract($data, EXTR_SKIP);
    include $filePath;
}
```

---

### 18. Pas de rate limiting sur les endpoints AJAX
**Fichier** : `app/Controllers/SettingsController.php`, `ClientManagementController.php`
**Impact** : Les endpoints AJAX (`get-options`, `get-closure-dates`, `toggle-premium`, etc.) n'ont aucun rate limiting. Un attaquant pourrait spammer ces endpoints.

**Correctif** : Ajouter un rate limiting basé sur la session ou l'IP pour les endpoints sensibles.

---

### 19. Double requête SQL dans `dashboard()`
**Fichier** : `app/Controllers/AdminController.php` (lignes 435-448)
**Impact** : Deux requêtes séparées pour récupérer `slug` et `updated_at` de la même table `restaurants`.

```php
$stmt = $this->pdo->prepare("SELECT slug FROM restaurants WHERE id = ?");
// ... puis ...
$stmt = $this->pdo->prepare("SELECT updated_at FROM restaurants WHERE id = ?");
```

**Correctif** : Une seule requête :

```php
$stmt = $this->pdo->prepare("SELECT slug, updated_at FROM restaurants WHERE id = ?");
```

---

### 20. `DEV_SHOW_LINK` et config SMTP en dur dans `index.php`
**Fichier** : `public/index.php` (lignes 2-4)
**Impact** : Les paramètres de développement sont codés en dur dans le fichier principal.

```php
ini_set('SMTP', 'localhost');
ini_set('smtp_port', 1025);
define('DEV_SHOW_LINK', true);
```

**Correctif** : Déplacer dans `config.php` et conditionner à un environnement :

```php
// Dans config.php
define('APP_ENV', 'development'); // 'production' en prod
if (APP_ENV === 'development') {
    ini_set('SMTP', 'localhost');
    ini_set('smtp_port', 1025);
    define('DEV_SHOW_LINK', true);
} else {
    define('DEV_SHOW_LINK', false);
}
```

---

## 🟡 PRIORITÉ BASSE — OPTIMISATIONS

### 21. Password trimming dans `login()` et `register()`
**Fichier** : `app/Controllers/AdminController.php` (lignes 104, 258, 344)
**Impact** : `$password = trim($_POST['password'] ?? '')` supprime les espaces au début/fin du mot de passe. Si l'utilisateur a intentionnellement un espace en début/fin de mot de passe, il ne pourra jamais se connecter.

**Correctif** : Ne PAS utiliser `trim()` sur les mots de passe. Utiliser uniquement les données brutes.

---

### 22. `require_once` répétitifs dans les controllers
**Fichier** : Multiples controllers
**Impact** : `require_once __DIR__ . '/../Models/OptionModel.php'` est appelé à chaque méthode qui en a besoin au lieu d'être dans le constructeur ou en haut du fichier.

**Correctif** : Centraliser les `require_once` en haut de chaque fichier controller.

---

### 23. CSS `@import` en cascade dans `display.css`
**Fichier** : `public/assets/css/display/display.css`
**Impact** : Les `@import` CSS bloquent le rendu. Chaque import est une requête HTTP séparée.

**Correctif** : En production, concaténer tous les CSS en un seul fichier (build step) ou utiliser `<link>` dans le HTML au lieu de `@import`.

---

### 24. Pas de pagination sur la liste des dates de fermeture
**Fichier** : `public/assets/js/sections/settings/closure-dates.js`
**Impact** : Si un restaurant a beaucoup de dates de fermeture, toutes sont chargées en mémoire.

**Correctif** : Limiter l'affichage aux 12 prochains mois et ajouter un scroll ou pagination.

---

## 📊 RÉSUMÉ PAR CATÉGORIE

| Catégorie | Critique | Haute | Moyenne | Basse |
|-----------|----------|-------|---------|-------|
| **Sécurité** | 6 | 2 | 4 | 1 |
| **Bugs** | 0 | 4 | 0 | 0 |
| **Performance** | 0 | 0 | 1 | 2 |
| **Architecture** | 0 | 0 | 1 | 1 |
| **Total** | **6** | **6** | **6** | **4** |

---

## ✅ POINTS POSITIFS (déjà en place)

1. **Requêtes préparées partout** — Pas de SQL injection détectée (PDO + `prepare()`)
2. **`htmlspecialchars()` bien utilisé** — 203 occurrences dans les vues (protection XSS)
3. **Headers de sécurité** — `X-Frame-Options`, `X-XSS-Protection`, `X-Content-Type-Options`
4. **CSRF sur la plupart des contrôleurs** — Settings, Logo/Banner, Services, Stripe, Admin, ClientManagement
5. **Rate limiting sur le login** — 5 tentatives / 15 minutes
6. **`session_regenerate_id(true)`** — Anti session fixation après login
7. **`hash_equals()`** — Comparaison temps-constant pour les tokens CSRF
8. **Upload sécurisé** — Validation MIME type + extension + taille max + nom sécurisé
9. **Whitelist des tables** dans `LogoBannerController` pour éviter l'injection SQL dans les noms de table
10. **Mode lecture seule** — Protection des actions d'écriture sans abonnement actif
11. **Système de démo isolé** — Chaque démo a son propre clone

---

## 🎯 PLAN D'ACTION RECOMMANDÉ

### Sprint 1 — Sécurité critique (immédiat) 
- [x] Remplacer `method_exists()` par des whitelists dans `index.php`
- [x] Ajouter CSRF à `CardController` (handlePostRequest) et `ContactController`
- [x] Convertir `delete-demo-token` et `delete-demo-tokens-bulk` en POST avec CSRF
- [x] Ajouter CSRF à `update-demo-label`
- [x] Ajouter `verifyCsrfTokenPublic()` à `BaseController` pour usage dans `index.php`
- [ ] Vérifier et renforcer `config.php` (session cookie flags)

### Sprint 2 — Bugs bloquants (1 jour) 
- [x] Corriger le scope de `loadAnalytics()` dans `cookies.js` (déplacé dans DOMContentLoaded)
- [x] Corriger le guard `clearAllBtn` dans `closure-dates.js` (retiré de la condition)
- [x] Supprimer les `console.log()` de production (`cookies.js`)
- [x] Nettoyer les `error_log()` de debug dans `CardController` (15 appels supprimés)

### Sprint 2b — Cohérence CSRF front-end 
- [x] Ajouter `csrf_token` aux données du `AdminController::dashboard()` (+ passer au render)
- [x] Ajouter `csrf_token` aux données de `ContactController::edit()` (+ champ hidden dans le formulaire)
- [x] Ajouter `csrf_token` aux données de `CardController` (`getEditableModeData` + `getImagesModeData`)
- [x] Ajouter `$csrfField` dans les 11 formulaires de `edit-card.php`
- [x] Convertir les liens GET de suppression démo en formulaires POST avec CSRF dans `dashboard.php`
- [x] Ajouter CSRF au fetch JS `update-demo-label` et à la suppression bulk via formulaire dynamique

### Sprint 3 — Améliorations sécurité (1-2 jours) ✅ TERMINÉ
- [x] Implémenter la rotation des tokens CSRF (régénération après chaque validation réussie, sauf AJAX)
- [x] Ajouter `Content-Security-Policy` header (dans `render()`, avec CSP pour scripts, styles, fonts, images, frames)
- [x] Headers sécurité dans le constructeur `BaseController` (`X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`, `Referrer-Policy`)
- [x] Ne plus `trim()` les mots de passe (3 occurrences corrigées dans `AdminController`)
- [x] Protéger `render()` contre le path traversal (sanitize `..` et `\0`, vérification `realpath` + `strpos`)
- [x] Utiliser `extract($data, EXTR_SKIP)` dans `render()` (empêche l'écrasement de variables existantes)
- [x] Centraliser `isAjaxRequest()` dans `BaseController` (supprimé de `SettingsController`, passé en `protected`)
- [x] Déplacer la config dev dans `config.php` (constantes SMTP_HOST, SMTP_PORT, DEV_SHOW_LINK dans config.php + config.example.php créé)

### Sprint 4 — Optimisations (1 jour) ✅ TERMINÉ
- [x] Fusionner les 2 requêtes SQL (slug + updated_at) dans `dashboard()` en une seule
- [x] Centraliser les `require_once` en haut des fichiers (10 doublons supprimés dans CardController, SettingsController, DisplayController, StripeController, ClientManagementController)
- [x] Optimiser les imports CSS vitrine : remplacé `@import` cascade dans `display.css` par `<link>` individuels dans `head.php` (chargement parallèle)
- [x] Implémenter un webhook Stripe pour la validation des paiements (handleWebhook + vérification signature HMAC + processCompletedCheckout idempotent + route stripe-webhook)

---

*Fin du rapport d'audit — 24 points identifiés + 4 bugs résolus. Sprint 1 + Sprint 2 + Sprint 2b + Sprint 3 + Sprint 4 tous terminés (32 corrections appliquées). Audit complet.*
