# 🏛️ Architecture MVC et Design Patterns - MenuMiam
## De l'Architecture Actuelle vers la V2 Commerciale

---

## 📋 **SOMMAIRE**
1. Architecture MVC existante (V1) - Analyse
2. Patterns déjà implémentés (V1)
3. Architecture MVC cible (V2)
4. Design patterns V2
5. Organisation des packages
6. Conventions de nommage
7. Cycle de vie d'une requête
8. Gestion des erreurs et logging
9. Stratégie de refactoring V1 → V2

---

## 🔍 **1. ARCHITECTURE MVC EXISTANTE (V1) - ANALYSE**

### **1.1 Structure Actuelle du Projet**

```
ProjetTemplatesRestaurants/
├── public/                          # Point d'entrée public (document root)
│   ├── index.php                    # Front Controller + Router
│   ├── .htaccess                    # Rewrite rules Apache
│   └── assets/
│       ├── css/
│       │   ├── admin.css            # Bundle CSS admin (imports)
│       │   ├── admin/
│       │   │   ├── basis/           # Styles de base (base, sidebar, dark-mode)
│       │   │   ├── effects/         # Animations (accordion, lightbox, tour)
│       │   │   ├── forms/           # Formulaires (forms, categories, dishes)
│       │   │   └── sections/        # Sections par page (dashboard, settings...)
│       │   └── display/             # CSS vitrine publique
│       ├── js/
│       │   ├── admin/               # JS global admin (dark-mode, toast, tour)
│       │   ├── effects/             # JS effets (accordion)
│       │   └── sections/            # JS par section (edit-card, settings...)
│       └── uploads/                 # Fichiers uploadés (logos, images)
│
├── app/                             # Code applicatif
│   ├── Controllers/                 # Contrôleurs MVC
│   │   ├── BaseController.php       # Classe abstraite parente
│   │   ├── AdminController.php      # Auth, inscription, profil
│   │   ├── CardController.php       # Gestion de la carte
│   │   ├── ContactController.php    # Gestion des contacts
│   │   ├── DisplayController.php    # Vitrine publique
│   │   ├── LogoBannerController.php # Gestion logo/bannière
│   │   ├── SettingsController.php   # Paramètres et options
│   │   ├── ServicesController.php   # Services du restaurant
│   │   ├── ReservationController.php # Réservations en ligne
│   │   ├── StatsController.php      # Statistiques
│   │   ├── StripeController.php     # Paiements Stripe
│   │   ├── ClientManagementController.php # Gestion clients (SUPER_ADMIN)
│   │   ├── SitemapController.php    # Génération sitemap XML
│   │   └── LegalController.php      # Pages légales
│   │
│   ├── Models/                      # Modèles de données
│   │   ├── Admin.php                # Gestion des admins
│   │   ├── Category.php             # Catégories de carte
│   │   ├── Dish.php                 # Plats
│   │   ├── Allergene.php            # Allergènes
│   │   ├── CardImage.php            # Images de carte
│   │   ├── Contact.php              # Informations de contact
│   │   ├── DailyMenu.php            # Menus du jour
│   │   ├── Restaurant.php           # Restaurants
│   │   ├── Reservation.php          # Réservations
│   │   ├── OptionModel.php          # Options clé-valeur
│   │   ├── ClientSubscription.php   # Abonnements
│   │   ├── PremiumFeature.php       # Fonctionnalités premium
│   │   ├── GoogleReviews.php        # Avis Google
│   │   ├── SiteVisit.php            # Visites du site
│   │   ├── DemoToken.php            # Tokens de démo
│   │   └── BillingCycle.php         # Cycle de facturation
│   │
│   ├── Views/                       # Vues (templates PHP)
│   │   ├── admin/                   # Vues back-office
│   │   ├── display/                 # Vues vitrine publique
│   │   ├── errors/                  # Pages d'erreur
│   │   ├── partials/                # Éléments réutilisables (header, footer)
│   │   ├── display.php              # Layout vitrine
│   │   └── landing.php              # Page d'accueil
│   │
│   ├── Helpers/                     # Utilitaires
│   │   ├── Mailer.php               # Envoi d'emails
│   │   ├── Validator.php            # Validation de données
│   │   ├── FormHelper.php           # Helpers de formulaires
│   │   ├── CategoryIconHelper.php   # Icônes catégories
│   │   └── old.php                  # Fonction old() pour formulaires
│   │
│   ├── Services/                    # Couche service
│   │   └── NotificationService.php  # Service de notifications
│   │
│   ├── Migrations/                  # Scripts de migration SQL
│   └── Seeds/                       # Données de test
│
├── config.php                       # Configuration (DB, constantes)
├── cron/                            # Tâches planifiées
└── tools/                           # Outils divers
```

### **1.2 Rôle de Chaque Couche V1**

#### **Front Controller (index.php)**
```
Responsabilités :
├── Chargement de la configuration (config.php)
├── Autoloading manuel des contrôleurs et helpers (require_once)
├── Routage via switch/case sur $_GET['page']
├── Instanciation du contrôleur correspondant
├── Délégation à la méthode appropriée
└── Gestion des cas spéciaux (démo, Stripe, sitemap)
```

#### **BaseController**
```
Responsabilités :
├── Injection de la connexion PDO
├── Envoi des headers de sécurité (X-Frame, CSP, XSS Protection)
├── Authentification (isLogged, requireLogin)
├── Mode démo (isDemoMode, checkDemoExpiry, blockIfDemo)
├── Abonnements (isReadOnly, requireActiveSubscription)
├── Rendu de vue (render) avec protection path traversal
├── Sécurité CSRF (getCsrfToken, verifyCsrfToken)
├── Messages flash (addSuccessMessage, addErrorMessage, getFlashMessages)
└── Détection AJAX (isAjaxRequest)
```

#### **Contrôleurs Enfants**
```
Pattern commun :
├── Héritage de BaseController
├── Méthode show() pour l'affichage (GET)
├── Méthodes d'action pour les traitements (POST)
├── Validation CSRF pour chaque POST
├── Support AJAX : retour JSON ou redirect+flash selon le type de requête
├── Appel requireLogin() en début de chaque méthode protégée
└── Appel render() pour afficher la vue avec les données
```

#### **Modèles**
```
Pattern commun :
├── Injection PDO dans le constructeur
├── Requêtes préparées (pas de SQL brut)
├── Méthodes CRUD : findById, findAll, create, update, delete
├── Méthodes métier spécifiques (ex: getActiveByAdmin)
├── Gestion des exceptions PDO
└── Pas d'héritage commun (pas de BaseModel)
```

#### **Vues**
```
Pattern commun :
├── Fichiers PHP avec HTML + balises PHP
├── Variables injectées via extract() dans render()
├── Header/Footer partagés via partials (include)
├── Scripts JS chargés dynamiquement via $scripts[]
├── Pas de moteur de template (Twig, Blade) : PHP natif
└── Echappement manuel via htmlspecialchars()
```

---

## 🧩 **2. DESIGN PATTERNS DÉJÀ IMPLÉMENTÉS (V1)**

### **2.1 Front Controller**
Le fichier `public/index.php` centralise toutes les requêtes HTTP.
```
Requête HTTP → index.php → switch($page) → Controller → View
```
**Forces** : Point d'entrée unique, sécurité centralisée
**Faiblesses** : Routage par switch/case, pas de support regex/paramètres

### **2.2 Template Method (BaseController)**
`BaseController` définit le squelette commun à tous les contrôleurs :
- `requireLogin()` → Vérification d'authentification
- `verifyCsrfToken()` → Validation CSRF
- `render()` → Rendu de vue
- `getFlashMessages()` → Récupération des messages

Les contrôleurs enfants implémentent leurs propres méthodes d'action tout en réutilisant le squelette du parent.

### **2.3 Active Record (Modèles)**
Chaque modèle encapsule la logique SQL pour sa table :
```php
// Exemple : DailyMenu (Active Record simplifié)
$dailyMenu = new DailyMenu($pdo);
$menus = $dailyMenu->getAllByAdmin($adminId);       // SELECT
$dailyMenu->create($adminId, $title, $price, ...);  // INSERT
$dailyMenu->update($id, $title, $price, ...);       // UPDATE
$dailyMenu->delete($id);                            // DELETE
```

### **2.4 Strategy Pattern (carte_mode)**
Le mode de carte (`editable` ou `images`) détermine dynamiquement le comportement d'affichage et d'édition dans CardController et les vues associées.

### **2.5 Observer Pattern Implicite (Flash Messages)**
Le système de messages flash utilise la session comme canal de communication entre les actions POST (producteur) et les vues suivantes (consommateur), avec nettoyage automatique après lecture.

### **2.6 Facade Pattern (Helpers)**
Les helpers (`Mailer`, `Validator`, `FormHelper`) simplifient l'accès à des sous-systèmes complexes derrière des interfaces simples.

---

## 🎯 **3. ARCHITECTURE MVC CIBLE (V2)**

### **3.1 Vue d'Ensemble V2**

```
┌──────────────────────────────────────────────────────────────────┐
│                        HTTP REQUEST                              │
└──────────────────────────┬───────────────────────────────────────┘
                           ▼
┌──────────────────────────────────────────────────────────────────┐
│                     MIDDLEWARE PIPELINE                           │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────────┐ │
│  │ Security │→ │   Auth   │→ │   CSRF   │→ │   Rate Limit     │ │
│  │ Headers  │  │ Check    │  │ Verify   │  │   Throttle       │ │
│  └──────────┘  └──────────┘  └──────────┘  └──────────────────┘ │
└──────────────────────────┬───────────────────────────────────────┘
                           ▼
┌──────────────────────────────────────────────────────────────────┐
│                         ROUTER                                    │
│  ┌──────────────────────────────────────────────────────────────┐ │
│  │  Route::get('/api/restaurants/{id}/menu', [MenuCtrl, 'show'])│ │
│  │  Route::post('/api/restaurants/{id}/menu', [MenuCtrl, 'store'])│
│  │  Route::put('/api/items/{id}', [ItemCtrl, 'update'])        │ │
│  │  Route::delete('/api/items/{id}', [ItemCtrl, 'destroy'])    │ │
│  └──────────────────────────────────────────────────────────────┘ │
└──────────────────────────┬───────────────────────────────────────┘
                           ▼
┌──────────────────────────────────────────────────────────────────┐
│                      CONTROLLER LAYER                            │
│  ┌──────────────────────────────────────────────────────────────┐ │
│  │  • Valide les entrées (Request Validation)                  │ │
│  │  • Délègue au Service Layer                                 │ │
│  │  • Retourne une Response (JSON ou View)                     │ │
│  └──────────────────────────────────────────────────────────────┘ │
└──────────────────────────┬───────────────────────────────────────┘
                           ▼
┌──────────────────────────────────────────────────────────────────┐
│                       SERVICE LAYER                              │
│  ┌──────────────────────────────────────────────────────────────┐ │
│  │  • Logique métier (Business Rules)                          │ │
│  │  • Orchestration entre Repositories                         │ │
│  │  • Dispatch d'événements (Domain Events)                    │ │
│  │  • Transactions et cohérence                                │ │
│  └──────────────────────────────────────────────────────────────┘ │
└──────────────────────────┬───────────────────────────────────────┘
                           ▼
┌──────────────────────────────────────────────────────────────────┐
│                     REPOSITORY LAYER                             │
│  ┌──────────────────────────────────────────────────────────────┐ │
│  │  • Abstraction d'accès aux données                          │ │
│  │  • Requêtes SQL via Query Builder ou ORM                    │ │
│  │  • Gestion du cache (Redis)                                 │ │
│  │  • Interface pour tests (mocks)                             │ │
│  └──────────────────────────────────────────────────────────────┘ │
└──────────────────────────┬───────────────────────────────────────┘
                           ▼
┌──────────────────────────────────────────────────────────────────┐
│                       DATA LAYER                                 │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐                 │
│  │   MySQL    │  │   Redis    │  │  Storage   │                 │
│  │  (Primary) │  │  (Cache)   │  │  (Files)   │                 │
│  └────────────┘  └────────────┘  └────────────┘                 │
└──────────────────────────────────────────────────────────────────┘
```

### **3.2 Structure des Dossiers V2**

```
app/
├── Config/
│   ├── app.php                  # Configuration générale
│   ├── database.php             # Configuration BDD
│   ├── routes.php               # Définition des routes
│   └── middleware.php           # Pipeline middleware
│
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php       # BaseController V2
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   ├── RegisterController.php
│   │   │   └── PasswordController.php
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── MenuController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ItemController.php
│   │   │   ├── SettingsController.php
│   │   │   ├── ReservationController.php
│   │   │   └── AnalyticsController.php
│   │   ├── Api/
│   │   │   ├── MenuApiController.php
│   │   │   ├── ReservationApiController.php
│   │   │   └── AnalyticsApiController.php
│   │   └── Public/
│   │       ├── DisplayController.php
│   │       ├── LandingController.php
│   │       └── SitemapController.php
│   │
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── CsrfMiddleware.php
│   │   ├── RateLimitMiddleware.php
│   │   ├── SecurityHeadersMiddleware.php
│   │   ├── SubscriptionMiddleware.php
│   │   └── DemoModeMiddleware.php
│   │
│   ├── Requests/                 # Validation des requêtes
│   │   ├── StoreItemRequest.php
│   │   ├── UpdateItemRequest.php
│   │   ├── StoreReservationRequest.php
│   │   └── UpdateSettingsRequest.php
│   │
│   └── Responses/
│       ├── JsonResponse.php
│       └── ViewResponse.php
│
├── Domain/                       # Logique métier pure
│   ├── Models/                   # Entités du domaine
│   │   ├── User.php
│   │   ├── Restaurant.php
│   │   ├── Category.php
│   │   ├── Item.php
│   │   ├── Reservation.php
│   │   └── Subscription.php
│   │
│   ├── Services/                 # Services métier
│   │   ├── MenuService.php
│   │   ├── ReservationService.php
│   │   ├── SubscriptionService.php
│   │   ├── AnalyticsService.php
│   │   ├── NotificationService.php
│   │   └── MediaService.php
│   │
│   ├── Events/                   # Domain Events
│   │   ├── EventDispatcher.php
│   │   ├── MenuUpdated.php
│   │   ├── ReservationCreated.php
│   │   └── SubscriptionChanged.php
│   │
│   └── Exceptions/               # Exceptions métier
│       ├── DomainException.php
│       ├── ValidationException.php
│       ├── NotFoundException.php
│       └── UnauthorizedException.php
│
├── Infrastructure/               # Implémentation technique
│   ├── Repositories/
│   │   ├── Contracts/            # Interfaces
│   │   │   ├── UserRepositoryInterface.php
│   │   │   ├── RestaurantRepositoryInterface.php
│   │   │   ├── CategoryRepositoryInterface.php
│   │   │   └── ItemRepositoryInterface.php
│   │   ├── MySQL/                # Implémentations MySQL
│   │   │   ├── UserRepository.php
│   │   │   ├── RestaurantRepository.php
│   │   │   ├── CategoryRepository.php
│   │   │   └── ItemRepository.php
│   │   └── Cache/                # Décorateurs cache
│   │       └── CachedRestaurantRepository.php
│   │
│   ├── Persistence/
│   │   ├── DatabaseConnection.php
│   │   ├── QueryBuilder.php
│   │   └── Migration.php
│   │
│   ├── External/                 # APIs externes
│   │   ├── StripeGateway.php
│   │   ├── GoogleReviewsClient.php
│   │   └── MailProvider.php
│   │
│   └── Storage/
│       ├── FileStorage.php
│       └── ImageProcessor.php
│
├── Views/                        # Templates
│   ├── layouts/
│   │   ├── admin.php
│   │   ├── public.php
│   │   └── auth.php
│   ├── admin/
│   ├── public/
│   ├── auth/
│   ├── errors/
│   └── components/               # Composants réutilisables
│       ├── form-input.php
│       ├── toast.php
│       └── pagination.php
│
└── Support/                      # Utilitaires transverses
    ├── Helpers/
    │   ├── Validator.php
    │   ├── Sanitizer.php
    │   └── Formatter.php
    ├── Container.php             # Injection de dépendances
    ├── Router.php                # Système de routage
    └── Logger.php                # Logging centralisé
```

---

## 🧩 **4. DESIGN PATTERNS V2**

### **4.1 Repository Pattern**

Sépare la logique métier de l'accès aux données. Permet de changer de source de données (MySQL, Redis, API) sans toucher à la logique métier.

```php
// Contrat (Interface)
namespace App\Infrastructure\Repositories\Contracts;

interface ItemRepositoryInterface
{
    public function findById(int $id): ?Item;
    public function findByCategory(int $categoryId): array;
    public function save(Item $item): Item;
    public function delete(int $id): bool;
    public function search(string $query, int $restaurantId): array;
}

// Implémentation MySQL
namespace App\Infrastructure\Repositories\MySQL;

class ItemRepository implements ItemRepositoryInterface
{
    public function __construct(
        private PDO $db
    ) {}

    public function findById(int $id): ?Item
    {
        $stmt = $this->db->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? Item::fromArray($row) : null;
    }

    public function findByCategory(int $categoryId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM items 
             WHERE category_id = ? AND is_available = 1 
             ORDER BY display_order"
        );
        $stmt->execute([$categoryId]);
        return array_map(
            fn($row) => Item::fromArray($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function save(Item $item): Item
    {
        if ($item->id) {
            $stmt = $this->db->prepare(
                "UPDATE items SET name=?, description=?, price=?, 
                 image=?, display_order=?, is_available=?, updated_at=NOW() 
                 WHERE id=?"
            );
            $stmt->execute([
                $item->name, $item->description, $item->price,
                $item->image, $item->displayOrder, $item->isAvailable, $item->id
            ]);
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO items (category_id, name, description, price, 
                 image, display_order, is_available) VALUES (?,?,?,?,?,?,?)"
            );
            $stmt->execute([
                $item->categoryId, $item->name, $item->description,
                $item->price, $item->image, $item->displayOrder, $item->isAvailable
            ]);
            $item->id = (int)$this->db->lastInsertId();
        }
        return $item;
    }
}

// Décorateur Cache
namespace App\Infrastructure\Repositories\Cache;

class CachedItemRepository implements ItemRepositoryInterface
{
    public function __construct(
        private ItemRepositoryInterface $inner,
        private Redis $cache
    ) {}

    public function findByCategory(int $categoryId): array
    {
        $key = "items:category:$categoryId";
        $cached = $this->cache->get($key);

        if ($cached) {
            return unserialize($cached);
        }

        $items = $this->inner->findByCategory($categoryId);
        $this->cache->setex($key, 1800, serialize($items)); // 30 min
        return $items;
    }

    public function save(Item $item): Item
    {
        $result = $this->inner->save($item);
        // Invalidation du cache
        $this->cache->del("items:category:{$item->categoryId}");
        return $result;
    }
}
```

### **4.2 Service Layer Pattern**

Orchestre la logique métier en utilisant les repositories. Le contrôleur ne contient que la coordination entre requête et réponse.

```php
namespace App\Domain\Services;

class MenuService
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private ItemRepositoryInterface $items,
        private EventDispatcher $events
    ) {}

    public function getFullMenu(int $restaurantId): array
    {
        $categories = $this->categories->findByRestaurant($restaurantId);
        $menu = [];

        foreach ($categories as $category) {
            $menu[] = [
                'category' => $category,
                'items' => $this->items->findByCategory($category->id),
            ];
        }

        return $menu;
    }

    public function addItem(int $categoryId, array $data): Item
    {
        // Validation métier
        $category = $this->categories->findById($categoryId);
        if (!$category) {
            throw new NotFoundException("Catégorie introuvable");
        }

        // Création
        $item = new Item();
        $item->categoryId = $categoryId;
        $item->name = $data['name'];
        $item->description = $data['description'] ?? '';
        $item->price = $data['price'] ?? null;
        $item->isAvailable = true;
        $item->displayOrder = $this->items->getNextOrder($categoryId);

        $saved = $this->items->save($item);

        // Événement
        $this->events->dispatch(new MenuUpdated($category->restaurantId, 'item_added', $saved));

        return $saved;
    }

    public function deleteItem(int $itemId, int $restaurantId): bool
    {
        $item = $this->items->findById($itemId);
        if (!$item) {
            throw new NotFoundException("Plat introuvable");
        }

        // Vérifier que l'item appartient au bon restaurant
        $category = $this->categories->findById($item->categoryId);
        if ($category->restaurantId !== $restaurantId) {
            throw new UnauthorizedException("Accès refusé");
        }

        $result = $this->items->delete($itemId);
        $this->events->dispatch(new MenuUpdated($restaurantId, 'item_deleted', $item));

        return $result;
    }
}
```

### **4.3 Middleware Pattern (Chain of Responsibility)**

Pipeline de middlewares qui traitent la requête séquentiellement avant qu'elle n'atteigne le contrôleur.

```php
namespace App\Http\Middleware;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
}

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            return new RedirectResponse('?page=login');
        }
        return $next($request);
    }
}

class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        if ($request->isPost()) {
            $token = $request->get('csrf_token');
            if (!$this->isValidToken($token)) {
                return new JsonResponse(['error' => 'Token CSRF invalide'], 403);
            }
        }
        return $next($request);
    }
}

class SubscriptionMiddleware implements MiddlewareInterface
{
    public function __construct(private PDO $db) {}

    public function handle(Request $request, callable $next): Response
    {
        if ($request->isPost() && $this->isReadOnly()) {
            return new RedirectResponse('?page=settings&section=premium');
        }
        return $next($request);
    }
}

// Pipeline
class MiddlewarePipeline
{
    private array $middlewares = [];

    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function process(Request $request, callable $handler): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            fn($next, $middleware) => fn($request) => $middleware->handle($request, $next),
            $handler
        );
        return $pipeline($request);
    }
}
```

### **4.4 Observer Pattern (Domain Events)**

Découple les effets de bord (cache, notifications, logs) de la logique métier.

```php
namespace App\Domain\Events;

class EventDispatcher
{
    private array $listeners = [];

    public function listen(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }

    public function dispatch(object $event): void
    {
        $class = get_class($event);
        foreach ($this->listeners[$class] ?? [] as $listener) {
            $listener($event);
        }
    }
}

// Événement
class MenuUpdated
{
    public function __construct(
        public readonly int $restaurantId,
        public readonly string $action,
        public readonly mixed $data
    ) {}
}

// Listeners enregistrés au boot de l'application
$events->listen(MenuUpdated::class, function (MenuUpdated $e) use ($cache) {
    $cache->del("menu:{$e->restaurantId}");
    $cache->del("categories:{$e->restaurantId}");
});

$events->listen(MenuUpdated::class, function (MenuUpdated $e) use ($logger) {
    $logger->info("Menu updated", [
        'restaurant' => $e->restaurantId,
        'action' => $e->action,
    ]);
});

$events->listen(ReservationCreated::class, function (ReservationCreated $e) use ($notifier) {
    $notifier->sendReservationConfirmation($e->reservation);
    $notifier->notifyRestaurantOwner($e->reservation);
});
```

### **4.5 Dependency Injection Container**

Gère l'instanciation et les dépendances de tous les services.

```php
namespace App\Support;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $abstract, callable $factory): void
    {
        $this->bindings[$abstract] = $factory;
    }

    public function singleton(string $abstract, callable $factory): void
    {
        $this->bindings[$abstract] = function () use ($abstract, $factory) {
            if (!isset($this->instances[$abstract])) {
                $this->instances[$abstract] = $factory($this);
            }
            return $this->instances[$abstract];
        };
    }

    public function make(string $abstract): mixed
    {
        if (isset($this->bindings[$abstract])) {
            return ($this->bindings[$abstract])($this);
        }
        throw new \RuntimeException("No binding for: $abstract");
    }
}

// Enregistrement des services (bootstrap)
$container = new Container();

$container->singleton(PDO::class, fn() => new PDO($dsn, $user, $pass));

$container->singleton(
    ItemRepositoryInterface::class,
    fn($c) => new CachedItemRepository(
        new ItemRepository($c->make(PDO::class)),
        $c->make(Redis::class)
    )
);

$container->singleton(
    MenuService::class,
    fn($c) => new MenuService(
        $c->make(CategoryRepositoryInterface::class),
        $c->make(ItemRepositoryInterface::class),
        $c->make(EventDispatcher::class)
    )
);
```

### **4.6 Router Pattern**

Remplace le switch/case par un système de routage déclaratif.

```php
namespace App\Support;

class Router
{
    private array $routes = [];

    public function get(string $pattern, array $handler): void
    {
        $this->addRoute('GET', $pattern, $handler);
    }

    public function post(string $pattern, array $handler): void
    {
        $this->addRoute('POST', $pattern, $handler);
    }

    public function put(string $pattern, array $handler): void
    {
        $this->addRoute('PUT', $pattern, $handler);
    }

    public function delete(string $pattern, array $handler): void
    {
        $this->addRoute('DELETE', $pattern, $handler);
    }

    private function addRoute(string $method, string $pattern, array $handler): void
    {
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $this->routes[] = [
            'method'  => $method,
            'pattern' => "#^{$regex}$#",
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): mixed
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                [$controllerClass, $action] = $route['handler'];
                $controller = $this->container->make($controllerClass);
                return $controller->$action(...$params);
            }
        }
        throw new NotFoundException("Route not found: $method $uri");
    }
}
```

---

## 📐 **5. CONVENTIONS DE NOMMAGE**

### **5.1 PHP**

| Élément               | Convention        | Exemple                              |
|-----------------------|-------------------|--------------------------------------|
| Classe                | PascalCase        | `MenuService`, `ItemRepository`      |
| Interface             | PascalCase + I/If | `ItemRepositoryInterface`            |
| Méthode               | camelCase         | `findById()`, `getFullMenu()`        |
| Variable              | camelCase         | `$restaurantId`, `$menuItems`        |
| Constante             | UPPER_SNAKE_CASE  | `MAX_UPLOAD_SIZE`, `DEFAULT_LOCALE`  |
| Propriété             | camelCase         | `$this->categoryRepo`               |
| Namespace             | PascalCase        | `App\Domain\Services`               |
| Fichier               | PascalCase.php    | `MenuService.php`                   |
| Table BDD             | snake_case        | `restaurant_users`, `daily_menus`   |
| Colonne BDD           | snake_case        | `created_at`, `display_order`       |

### **5.2 JavaScript**

| Élément               | Convention        | Exemple                              |
|-----------------------|-------------------|--------------------------------------|
| Variable / Fonction   | camelCase         | `menuItems`, `handleSubmit()`        |
| Classe                | PascalCase        | `MenuViewer`, `ToastNotification`    |
| Constante             | UPPER_SNAKE_CASE  | `API_BASE_URL`, `MAX_FILE_SIZE`      |
| Fichier               | kebab-case.js     | `menu-viewer.js`, `toast.js`         |
| CSS classe            | kebab-case        | `.menu-item`, `.btn--primary`        |
| CSS variable          | --kebab-case      | `--color-primary`, `--font-size-lg`  |
| data-attribute        | kebab-case        | `data-restaurant-id`, `data-target`  |

### **5.3 API REST**

| Élément               | Convention                | Exemple                        |
|-----------------------|---------------------------|--------------------------------|
| Endpoint              | kebab-case, pluriel       | `/api/restaurants`, `/api/items` |
| Paramètre URL         | camelCase                 | `?sortBy=price&pageSize=20`   |
| Champ JSON response   | camelCase                 | `{ "restaurantName": "..." }` |
| Verbe HTTP            | Standard REST             | GET, POST, PUT, DELETE         |
| Status codes          | Standard HTTP             | 200, 201, 400, 401, 404, 500  |

---

## 🔄 **6. CYCLE DE VIE D'UNE REQUÊTE (V2)**

### **6.1 Requête Admin : Modifier un plat**
```
1. POST /admin/items/42
   ├── Body: { name: "Pizza", price: 14.50, csrf_token: "abc..." }
   └── Headers: X-Requested-With: XMLHttpRequest

2. MIDDLEWARE PIPELINE
   ├── SecurityHeadersMiddleware → Ajoute headers sécurité ✅
   ├── AuthMiddleware → Vérifie session admin ✅
   ├── CsrfMiddleware → Valide CSRF token ✅
   ├── SubscriptionMiddleware → Vérifie abonnement actif ✅
   └── DemoModeMiddleware → Vérifie restrictions démo ✅

3. ROUTER
   └── Match: Route::post('/admin/items/{id}', [ItemController, 'update'])
       └── Paramètres extraits: { id: 42 }

4. CONTROLLER (ItemController::update)
   ├── Validation de la requête (UpdateItemRequest)
   │   ├── name: required, string, max:255 ✅
   │   └── price: numeric, min:0 ✅
   └── Délègue à MenuService::updateItem(42, $data)

5. SERVICE (MenuService::updateItem)
   ├── Charge l'item via ItemRepository::findById(42)
   ├── Vérifie ownership via CategoryRepository
   ├── Met à jour les données
   ├── Sauvegarde via ItemRepository::save($item)
   └── Dispatch événement MenuUpdated

6. EVENT LISTENERS
   ├── CacheInvalidator → Supprime cache menu ✅
   ├── AuditLogger → Enregistre modification ✅
   └── (Pas de notification pour simple update)

7. RESPONSE
   └── JsonResponse: { success: true, message: "Plat mis à jour" }
```

### **6.2 Requête Publique : Afficher une carte**
```
1. GET /restaurant/le-bistrot-parisien

2. MIDDLEWARE PIPELINE
   ├── SecurityHeadersMiddleware ✅
   └── (Pas d'auth requise pour page publique)

3. ROUTER
   └── Match: Route::get('/restaurant/{slug}', [DisplayController, 'show'])

4. CONTROLLER (DisplayController::show)
   └── Délègue à MenuService::getFullMenu($slug)

5. SERVICE
   ├── RestaurantRepository::findBySlug('le-bistrot-parisien')
   │   └── Cache HIT (Redis) → Retour immédiat
   ├── MenuService::getFullMenu($restaurant->id)
   │   └── Cache HIT (Redis) → Retour immédiat
   └── AnalyticsService::trackView($restaurant->id, $request)
       └── Async (non-bloquant)

6. RESPONSE
   └── ViewResponse: render('public/display', $data)
       ├── Layout: public.php
       ├── Template: selon restaurant->template
       └── Cache-Control: max-age=300 (5 min)
```

---

## 🚨 **7. GESTION DES ERREURS ET LOGGING**

### **7.1 Hiérarchie d'Exceptions**

```
Exception (PHP natif)
└── DomainException (MenuMiam)
    ├── ValidationException     → 422 Unprocessable Entity
    ├── NotFoundException       → 404 Not Found
    ├── UnauthorizedException   → 401/403 Unauthorized/Forbidden
    ├── SubscriptionException   → 402 Payment Required
    └── RateLimitException      → 429 Too Many Requests
```

### **7.2 Error Handler Global**

```php
class ErrorHandler
{
    public function handle(\Throwable $e): Response
    {
        // Log l'erreur
        $this->logger->error($e->getMessage(), [
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => $e->getTraceAsString(),
        ]);

        // Réponse selon le type d'erreur
        if ($e instanceof ValidationException) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->getErrors(),
            ], 422);
        }

        if ($e instanceof NotFoundException) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }

        // Erreur interne : ne pas exposer les détails en production
        $message = $this->isProduction
            ? 'Erreur interne du serveur'
            : $e->getMessage();

        return new JsonResponse([
            'success' => false,
            'message' => $message,
        ], 500);
    }
}
```

### **7.3 Logger Structuré**

```php
class Logger
{
    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    private function write(string $level, string $message, array $context): void
    {
        $entry = [
            'timestamp'  => date('c'),
            'level'      => $level,
            'message'    => $message,
            'context'    => $context,
            'request_id' => $_SERVER['HTTP_X_REQUEST_ID'] ?? uniqid(),
            'user_id'    => $_SESSION['admin_id'] ?? null,
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];

        $line = json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
```

---

## 🔄 **8. STRATÉGIE DE REFACTORING V1 → V2**

### **8.1 Principes du Refactoring**

- **Progressif** : Pas de réécriture totale, migration incrémentale
- **Rétrocompatible** : Les routes V1 continuent de fonctionner
- **Testable** : Chaque module refactoré est couvert par des tests
- **Isolé** : Chaque refactoring est un commit/PR indépendant

### **8.2 Plan de Refactoring par Étapes**

#### **Étape 1 : Infrastructure de base (Semaine 1-2)**
```
Créer :
├── Container.php (injection de dépendances)
├── Router.php (routage déclaratif, coexiste avec switch/case)
├── Logger.php (logging structuré)
└── ErrorHandler.php (gestion d'erreurs centralisée)

Impact : Aucun sur le code existant
```

#### **Étape 2 : Extraction des Repositories (Semaine 3-4)**
```
Extraire :
├── ItemRepositoryInterface + ItemRepository (depuis Dish.php)
├── CategoryRepositoryInterface + CategoryRepository (depuis Category.php)
├── RestaurantRepositoryInterface + RestaurantRepository
└── UserRepositoryInterface + UserRepository (depuis Admin.php)

Impact : Les modèles V1 délèguent aux repositories
```

#### **Étape 3 : Création de la couche Service (Semaine 5-6)**
```
Créer :
├── MenuService (logique extraite de CardController)
├── ReservationService (logique extraite de ReservationController)
├── SubscriptionService (logique extraite de StripeController)
└── MediaService (logique d'upload extraite)

Impact : Les contrôleurs V1 délèguent aux services
```

#### **Étape 4 : Middlewares (Semaine 7)**
```
Extraire de BaseController :
├── AuthMiddleware (depuis requireLogin)
├── CsrfMiddleware (depuis verifyCsrfToken)
├── SubscriptionMiddleware (depuis requireActiveSubscription)
├── DemoModeMiddleware (depuis blockIfDemo)
└── SecurityHeadersMiddleware (depuis le constructeur)

Impact : BaseController simplifié, middleware réutilisable
```

#### **Étape 5 : Migration des routes (Semaine 8)**
```
Migrer progressivement :
├── switch/case → Router déclaratif
├── Contrôleurs monolithiques → Contrôleurs par ressource
├── Réponses manuelles → JsonResponse/ViewResponse
└── Validation inline → Request classes

Impact : Routes V1 et V2 coexistent, migration progressive
```

#### **Étape 6 : Domain Events (Semaine 9-10)**
```
Découpler :
├── Cache invalidation (depuis les contrôleurs)
├── Notifications (depuis les actions)
├── Audit logging (depuis partout)
└── Analytics tracking (depuis les vues)

Impact : Code plus propre, effets de bord isolés
```

### **8.3 Mapping V1 → V2**

| Composant V1                   | Composant V2                              |
|--------------------------------|-------------------------------------------|
| `index.php` (switch/case)     | `Router.php` + `routes.php`               |
| `BaseController` (tout-en-un) | `Controller` + Middlewares                |
| `Admin.php` (modèle)          | `UserRepository` + `User` (entité)        |
| `Dish.php` (modèle)           | `ItemRepository` + `Item` (entité)        |
| `CardController` (gros)       | `MenuController` + `MenuService`          |
| `SettingsController` (gros)   | `SettingsController` + sous-controllers   |
| Flash messages (session)       | `JsonResponse` + Toast JS                |
| `require_once` (manuel)       | `Container` + autoloading PSR-4           |
| `config.php` (global)         | `Config/` (séparé par domaine)            |
| `Helpers/` (statique)         | `Support/` (injectable)                   |

---

## 🎯 **CONCLUSION**

Cette documentation d'architecture MVC et design patterns définit :

✅ **Analyse complète** de l'architecture V1 existante avec ses forces et faiblesses  
✅ **Architecture V2** en couches (Controller → Service → Repository → Data)  
✅ **6 design patterns** détaillés : Repository, Service Layer, Middleware, Observer, DI Container, Router  
✅ **Conventions de nommage** exhaustives (PHP, JS, CSS, API, BDD)  
✅ **Cycle de vie** complet d'une requête (admin et publique)  
✅ **Gestion d'erreurs** avec hiérarchie d'exceptions et logging structuré  
✅ **Plan de refactoring** progressif en 6 étapes sur 10 semaines  

L'architecture V2 est conçue pour être **maintenable, testable et scalable**, tout en permettant une **migration progressive** depuis la V1 sans rupture de service.

---

## 📊 **RÉCAPITULATIF DE LA PHASE DE CONCEPTION CDA**

| Phase | Document | Statut |
|-------|----------|--------|
| 1 | Analyse état actuel | ✅ Terminé |
| 1 | Cahier des charges | ✅ Terminé |
| 2 | Spécifications techniques et architecture | ✅ Terminé |
| 3 | Maquettage et structure responsive | ✅ Terminé |
| 4 | Modélisation BDD (MERISE) | ✅ Terminé |
| 5 | Architecture MVC et design patterns | ✅ Terminé |

**La phase de conception CDA est maintenant complète.** 🎉

*Prochaine grande phase : Développement (implémentation)* 💻
