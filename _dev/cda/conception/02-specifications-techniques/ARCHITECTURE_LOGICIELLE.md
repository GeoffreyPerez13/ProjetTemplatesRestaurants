# ⚙️ Spécifications Techniques - MenuMiam V2
## Architecture Logicielle et Design Patterns

---

## 🏛️ **1. ARCHITECTURE GLOBALE**

### **1.1 Vue d'Ensemble de l'Architecture**

```
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND LAYER                           │
├─────────────────┬─────────────────┬─────────────────────────┤
│  Mobile App     │   Web App       │   Admin Dashboard        │
│  (React Native) │   (React SPA)   │   (React Admin)          │
│                 │                 │                          │
│  • QR Scanner   │  • Menu Viewer  │  • Restaurant Management │
│  • Offline Mode │  • PWA Features │  • Analytics Dashboard   │
│  • Push Notifs  │  • Responsive   │  • User Management      │
└─────────────────┴─────────────────┴─────────────────────────┘
                           │
                    ┌──────▼──────┐
                    │  API Gateway │
                    │  (Node.js)   │
                    │  • Auth      │
                    │  • Routing   │
                    │  • Rate Limit│
                    └──────┬──────┘
                           │
┌──────────────────────────┼─────────────────────────────────┐
│                    BACKEND SERVICES                        │
├─────────────┬────────────┼──────────────┬──────────────────┤
│ Restaurant   │ Payment    │ Analytics     │ Notification     │
│ Service      │ Service    │ Service      │ Service          │
│ (PHP/Symfony)│(Node.js)   │ (Python)     │ (Node.js)        │
│              │            │              │                  │
│ • CRUD Menu  │ • Stripe   │ • Metrics    │ • Email/SMS      │
│ • Templates  │ • Webhooks  │ • Reports    │ • Push Notifs    │
│ • Media      │ • Invoices  │ • ML Models  │ • Real-time      │
└──────────────┴────────────┴──────────────┴──────────────────┘
                           │
                    ┌──────▼──────┐
                    │  Data Layer  │
                    │              │
                    │ ┌──────────┐ │
                    │ │  MySQL   │ │
                    │ │ Primary  │ │
                    │ └──────────┘ │
                    │ ┌──────────┐ │
                    │ │  Redis   │ │
                    │ │ Cache    │ │
                    │ └──────────┘ │
                    │ ┌──────────┐ │
                    │ │  S3      │ │
                    │ │ Files    │ │
                    │ └──────────┘ │
                    └──────────────┘
```

### **1.2 Pattern Architectural : Microservices**

#### **Principes**
- **Single Responsibility** : Chaque service a une mission unique
- **Loose Coupling** : Services communiquent via APIs
- **High Cohesion** : Fonctionnalités liées groupées
- **Database per Service** : Isolation des données

#### **Communication**
- **Synchronous** : REST APIs (HTTP/JSON)
- **Asynchronous** : Message Queue (Redis Pub/Sub)
- **Event-Driven** : Domain Events propagation
- **Circuit Breaker** : Hystrix pattern

---

## 🎯 **2. ARCHITECTURE DÉTAILLÉE PAR SERVICE**

### **2.1 API Gateway**

#### **Responsabilités**
- **Authentication** : JWT validation, 2FA
- **Authorization** : RBAC, permissions
- **Rate Limiting** : 1000 req/min per user
- **Request Routing** : Service discovery
- **Response Aggregation** : GraphQL-like
- **Logging** : Centralized request tracking

#### **Technical Stack**
```javascript
// Node.js + Express + Helmet
const express = require('express');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const jwt = require('jsonwebtoken');

// Rate limiting
const limiter = rateLimit({
  windowMs: 60 * 1000, // 1 minute
  max: 1000 // limit each IP to 1000 requests per windowMs
});

// JWT validation middleware
const validateToken = (req, res, next) => {
  const token = req.headers.authorization?.split(' ')[1];
  if (!token) return res.status(401).json({error: 'No token'});
  
  jwt.verify(token, process.env.JWT_SECRET, (err, decoded) => {
    if (err) return res.status(401).json({error: 'Invalid token'});
    req.user = decoded;
    next();
  });
};
```

### **2.2 Restaurant Service (Core Business)**

#### **Architecture MVC Enhanced**
```
┌─────────────────────────────────────────────────┐
│                CONTROLLER LAYER                  │
├─────────────────┬───────────────────────────────┤
│ RestaurantController │ MenuController           │
│ TemplateController  │ MediaController           │
│ ReservationController │ AnalyticsController    │
└─────────────────┴───────────────────────────────┘
                           │
                    ┌──────▼──────┐
                    │ SERVICE LAYER│
├─────────────────┬──────────────┼─────────────────┤
│ RestaurantService │ MenuService │ TemplateService │
│ • Business Logic │ • CRUD Ops   │ • Rendering     │
│ • Validation     │ • Pricing    │ • Customization │
│ • Events         │ • Categories │ • Cache Mgmt    │
└─────────────────┴──────────────┴─────────────────┘
                           │
                    ┌──────▼──────┐
│                REPOSITORY LAYER                  │
├─────────────────┬───────────────────────────────┤
│ RestaurantRepo  │ MenuRepo      │ TemplateRepo    │
│ • MySQL Queries │ • CRUD Ops    │ • File Storage  │
│ • Caching       │ • Indexing    │ • Versioning    │
│ • Transactions  │ • Validation  │ • Optimization  │
└─────────────────┴───────────────────────────────┘
```

#### **Design Patterns Implémentés**

##### **Repository Pattern**
```php
<?php
namespace App\Repositories;

interface RestaurantRepositoryInterface {
    public function findById(int $id): ?Restaurant;
    public function findBySlug(string $slug): ?Restaurant;
    public function save(Restaurant $restaurant): Restaurant;
    public function delete(Restaurant $restaurant): bool;
    public function findByOwner(int $ownerId): array;
}

class RestaurantRepository implements RestaurantRepositoryInterface {
    private PDO $db;
    private Redis $cache;
    
    public function findById(int $id): ?Restaurant {
        $cacheKey = "restaurant:$id";
        $cached = $this->cache->get($cacheKey);
        
        if ($cached) {
            return unserialize($cached);
        }
        
        $stmt = $this->db->prepare(
            "SELECT * FROM restaurants WHERE id = ?"
        );
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        if (!$data) return null;
        
        $restaurant = new Restaurant($data);
        $this->cache->setex($cacheKey, 3600, serialize($restaurant));
        
        return $restaurant;
    }
}
```

##### **Factory Pattern**
```php
<?php
namespace App\Factories;

class MenuFactory {
    public function createFromTemplate(int $restaurantId, int $templateId): Menu {
        $template = $this->templateRepository->findById($templateId);
        $menu = new Menu();
        
        $menu->setRestaurantId($restaurantId);
        $menu->setLayout($template->getDefaultLayout());
        $menu->setColorScheme($template->getDefaultColors());
        $menu->setTypography($template->getDefaultFonts());
        
        return $menu;
    }
    
    public function createFromImport(array $data): Menu {
        $menu = new Menu();
        
        // Validation et transformation des données
        $validator = new MenuValidator();
        $validatedData = $validator->validate($data);
        
        $menu->fromArray($validatedData);
        
        return $menu;
    }
}
```

##### **Observer Pattern (Domain Events)**
```php
<?php
namespace App\Events;

class MenuUpdated {
    public function __construct(
        private Menu $menu,
        private array $changes
    ) {}
    
    public function getMenu(): Menu {
        return $this->menu;
    }
    
    public function getChanges(): array {
        return $this->changes;
    }
}

class EventDispatcher {
    private array $listeners = [];
    
    public function dispatch(object $event): void {
        $eventType = get_class($event);
        
        foreach ($this->listeners[$eventType] ?? [] as $listener) {
            $listener($event);
        }
    }
    
    public function addListener(string $eventType, callable $listener): void {
        $this->listeners[$eventType][] = $listener;
    }
}

// Usage
$eventDispatcher->addListener(MenuUpdated::class, function(MenuUpdated $event) {
    // Invalider le cache
    $cache->delete("menu:{$event->getMenu()->getId()}");
    
    // Envoyer notification
    $notificationService->notifyMenuUpdate($event->getMenu());
    
    // Logger l'action
    $logger->info("Menu updated", ['menu_id' => $event->getMenu()->getId()]);
});
```

### **2.3 Payment Service**

#### **Architecture**
```javascript
// Node.js + Stripe + Webhooks
class PaymentService {
    constructor(stripeClient, webhookService, notificationService) {
        this.stripe = stripeClient;
        this.webhooks = webhookService;
        this.notifications = notificationService;
    }
    
    async createSubscription(userId, planId, paymentMethodId) {
        try {
            // Créer customer Stripe
            const customer = await this.stripe.customers.create({
                email: userId,
                payment_method: paymentMethodId,
                invoice_settings: {
                    default_payment_method: paymentMethodId,
                },
            });
            
            // Créer abonnement
            const subscription = await this.stripe.subscriptions.create({
                customer: customer.id,
                items: [{ price: planId }],
                payment_behavior: 'default_incomplete',
                expand: ['latest_invoice.payment_intent'],
            });
            
            // Sauvegarder en base
            await this.saveSubscription(userId, subscription);
            
            // Envoyer confirmation
            await this.notifications.sendSubscriptionConfirmation(userId, subscription);
            
            return subscription;
        } catch (error) {
            throw new PaymentError('Failed to create subscription', error);
        }
    }
    
    async handleWebhook(event) {
        switch (event.type) {
            case 'invoice.payment_succeeded':
                await this.handleSuccessfulPayment(event.data.object);
                break;
            case 'invoice.payment_failed':
                await this.handleFailedPayment(event.data.object);
                break;
            case 'customer.subscription.deleted':
                await this.handleSubscriptionCancellation(event.data.object);
                break;
        }
    }
}
```

### **2.4 Analytics Service**

#### **Python + Flask + TimescaleDB**
```python
from flask import Flask, request
from timescaledb import TimescaleDB
from dataclasses import dataclass
from datetime import datetime
from typing import Dict, List

@dataclass
class MenuViewEvent:
    restaurant_id: int
    menu_id: int
    user_agent: str
    ip_address: str
    timestamp: datetime
    session_id: str
    referrer: str

class AnalyticsService:
    def __init__(self, db: TimescaleDB):
        self.db = db
    
    def track_menu_view(self, event: MenuViewEvent):
        """Track menu view events"""
        query = """
        INSERT INTO menu_views (
            restaurant_id, menu_id, user_agent, 
            ip_address, timestamp, session_id, referrer
        ) VALUES (%s, %s, %s, %s, %s, %s, %s)
        """
        
        self.db.execute(query, (
            event.restaurant_id, event.menu_id, event.user_agent,
            event.ip_address, event.timestamp, event.session_id, event.referrer
        ))
    
    def get_restaurant_analytics(self, restaurant_id: int, 
                                start_date: datetime, 
                                end_date: datetime) -> Dict:
        """Get comprehensive analytics for a restaurant"""
        
        # Menu views over time
        views_query = """
        SELECT 
            time_bucket('1 hour', timestamp) as hour,
            COUNT(*) as view_count,
            COUNT(DISTINCT session_id) as unique_sessions
        FROM menu_views 
        WHERE restaurant_id = %s 
        AND timestamp BETWEEN %s AND %s
        GROUP BY hour
        ORDER BY hour
        """
        
        # Popular items
        popular_items_query = """
        SELECT 
            mi.name,
            COUNT(mv.id) as view_count
        FROM menu_views mv
        JOIN menu_items mi ON mv.menu_item_id = mi.id
        WHERE mv.restaurant_id = %s
        AND mv.timestamp BETWEEN %s AND %s
        GROUP BY mi.id, mi.name
        ORDER BY view_count DESC
        LIMIT 10
        """
        
        return {
            'views_over_time': self.db.fetch_all(views_query, (restaurant_id, start_date, end_date)),
            'popular_items': self.db.fetch_all(popular_items_query, (restaurant_id, start_date, end_date))
        }
```

---

## 🗄️ **3. MODÉLISATION BASE DE DONNÉES (MERISE)**

### **3.1 Modèle Conceptuel de Données (MCD)**

```
┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│    RESTAURANT   │◄────────┤      USER       │◄────────│    ADMIN        │
│─────────────────│ 1,N     │─────────────────│ 1,N     │─────────────────│
│ • id_restaurant │         │ • id_user      │         │ • id_admin      │
│ • nom           │         │ • email        │         │ • email         │
│ • slug          │         │ • password     │         │ • role          │
│ • adresse       │         │ • created_at   │         │ • created_at    │
│ • telephone     │         │ • last_login   │         └─────────────────┘
│ • email         │         └─────────────────┘
│ • created_at    │                 │
│ • updated_at    │                 │
└─────────────────┘                 │
         │                         │
         │ 1,N                     │ 1,N
         ▼                         ▼
┌─────────────────┐         ┌─────────────────┐
│      MENU       │◄────────┤   SUBSCRIPTION  │
│─────────────────│ 1,1     │─────────────────│
│ • id_menu       │         │ • id_subscription│
│ • id_restaurant  │         │ • id_user      │
│ • nom           │         │ • plan_type     │
│ • template_id   │         │ • status       │
│ • color_scheme  │         │ • start_date   │
│ • typography    │         │ • end_date     │
│ • layout        │         │ • amount       │
│ • created_at    │         │ • created_at    │
│ • updated_at    │         └─────────────────┘
└─────────────────┘
         │
         │ 1,N
         ▼
┌─────────────────┐         ┌─────────────────┐
│     CATEGORY    │         │      ITEM       │
│─────────────────│◄────────│─────────────────│
│ • id_category   │ 1,N     │─────────────────│
│ • id_menu       │         │ • id_item      │
│ • nom           │         │ • id_category  │
│ • description   │         │ • nom          │
│ • image         │         │ • description  │
│ • display_order │         │ • price        │
│ • created_at    │         │ • image        │
└─────────────────┘         │ • allergenes   │
                            │ • display_order│
                            │ • created_at   │
                            └─────────────────┘
```

### **3.2 Modèle Logique de Données (MLD)**

#### **Tables Principales**
```sql
-- Table restaurants
CREATE TABLE restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    adresse TEXT,
    telephone VARCHAR(20),
    email VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug),
    INDEX idx_location (latitude, longitude),
    INDEX idx_created (created_at)
);

-- Table users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    email_verified BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_created (created_at)
);

-- Table restaurant_users (junction table)
CREATE TABLE restaurant_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('owner', 'manager', 'employee') DEFAULT 'employee',
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_restaurant_user (restaurant_id, user_id),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_user (user_id)
);

-- Table menus
CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    template_id INT,
    color_scheme JSON,
    typography JSON,
    layout JSON,
    is_active BOOLEAN DEFAULT TRUE,
    version INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES templates(id),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_active (is_active),
    INDEX idx_created (created_at)
);

-- Table categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    display_order INT DEFAULT 0,
    is_hidden BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    INDEX idx_menu_order (menu_id, display_order),
    INDEX idx_hidden (is_hidden)
);

-- Table items
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2),
    image VARCHAR(255),
    display_order INT DEFAULT 0,
    is_available BOOLEAN DEFAULT TRUE,
    allergenes JSON,
    nutritional_info JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category_order (category_id, display_order),
    INDEX idx_available (is_available),
    INDEX idx_price (price)
);
```

#### **Tables Analytics**
```sql
-- TimescaleDB hypertable pour les analytics
CREATE TABLE menu_views (
    time TIMESTAMP NOT NULL,
    restaurant_id INT NOT NULL,
    menu_id INT NOT NULL,
    session_id VARCHAR(255),
    user_agent TEXT,
    ip_address INET,
    referrer TEXT,
    country_code CHAR(2),
    device_type VARCHAR(50)
);

SELECT create_hypertable('menu_views', 'time', chunk_time_interval => INTERVAL '1 day');

-- Table pour les réservations
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255),
    customer_phone VARCHAR(20),
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    party_size INT NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    INDEX idx_restaurant_date (restaurant_id, reservation_date),
    INDEX idx_status (status),
    INDEX idx_datetime (reservation_date, reservation_time)
);
```

### **3.3 Optimisations de Performance**

#### **Indexation Stratégique**
```sql
-- Index composites pour les requêtes fréquentes
CREATE INDEX idx_menu_items_category_order ON items(category_id, display_order, is_available);
CREATE INDEX idx_restaurant_menus_active ON menus(restaurant_id, is_active);
CREATE INDEX idx_reservations_datetime_status ON reservations(reservation_date, reservation_time, status);

-- Index全文 pour la recherche
CREATE FULLTEXT INDEX idx_items_search ON items(nom, description);

-- Index géospatiaux pour la localisation
CREATE INDEX idx_restaurants_location ON restaurants(latitude, longitude) USING SPATIAL INDEX;
```

#### **Partitionnement pour la scalabilité**
```sql
-- Partitionnement par restaurant_id pour les grandes tables
CREATE TABLE items_partitioned (
    LIKE items INCLUDING ALL
) PARTITION BY HASH (restaurant_id);

CREATE TABLE items_0 PARTITION OF items_partitioned FOR VALUES WITH (modulus 4, remainder 0);
CREATE TABLE items_1 PARTITION OF items_partitioned FOR VALUES WITH (modulus 4, remainder 1);
CREATE TABLE items_2 PARTITION OF items_partitioned FOR VALUES WITH (modulus 4, remainder 2);
CREATE TABLE items_3 PARTITION OF items_partitioned FOR VALUES WITH (modulus 4, remainder 3);
```

---

## 🎨 **4. ARCHITECTURE FRONTEND**

### **4.1 React Native Mobile App**

#### **Structure des Composants**
```
src/
├── components/
│   ├── common/
│   │   ├── Button/
│   │   ├── Input/
│   │   ├── Card/
│   │   └── Loading/
│   ├── menu/
│   │   ├── MenuViewer/
│   │   ├── MenuItem/
│   │   └── CategorySection/
│   └── qr/
│       ├── QRScanner/
│       └── QRDisplay/
├── screens/
│   ├── HomeScreen/
│   ├── MenuScreen/
│   ├── QRScannerScreen/
│   └── SettingsScreen/
├── navigation/
│   ├── AppNavigator.tsx
│   └── TabNavigator.tsx
├── services/
│   ├── api/
│   ├── storage/
│   └── analytics/
├── hooks/
│   ├── useMenu.ts
│   ├── useQR.ts
│   └── useOffline.ts
└── utils/
    ├── constants/
    ├── helpers/
    └── types/
```

#### **Hooks Personnalisés**
```typescript
// hooks/useMenu.ts
import { useState, useEffect } from 'react';
import { getMenu } from '../services/api';
import { storeMenu, getCachedMenu } from '../services/storage';

interface MenuItem {
  id: number;
  name: string;
  description: string;
  price: number;
  image: string;
  allergens: string[];
}

export const useMenu = (restaurantId: string) => {
  const [menu, setMenu] = useState<MenuItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadMenu = async () => {
      try {
        setLoading(true);
        
        // Essayer le cache d'abord
        const cachedMenu = await getCachedMenu(restaurantId);
        if (cachedMenu) {
          setMenu(cachedMenu);
        }
        
        // Charger les données fraîches
        const freshMenu = await getMenu(restaurantId);
        setMenu(freshMenu);
        await storeMenu(restaurantId, freshMenu);
        
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error');
      } finally {
        setLoading(false);
      }
    };

    loadMenu();
  }, [restaurantId]);

  return { menu, loading, error, refetch: () => loadMenu() };
};
```

### **4.2 React Admin Dashboard**

#### **Configuration des Resources**
```typescript
// resources/RestaurantResource.tsx
import { List, Datagrid, TextField, EmailField, EditButton, DeleteButton } from 'react-admin';
import { RestaurantFilter } from './RestaurantFilter';

export const RestaurantList = () => (
  <List filters={<RestaurantFilter />}>
    <Datagrid rowClick="edit">
      <TextField source="id" />
      <TextField source="name" />
      <EmailField source="email" />
      <TextField source="phone" />
      <TextField source="address.city" />
      <EditButton />
      <DeleteButton />
    </Datagrid>
  </List>
);

// resources/MenuResource.tsx
export const MenuEdit = () => (
  <Edit>
    <SimpleForm>
      <TextInput source="name" validate={[required()]} />
      <SelectInput 
        source="template_id" 
        choices={templateChoices}
        validate={[required()]}
      />
      <ColorInput source="primary_color" />
      <ColorInput source="secondary_color" />
      <ArrayInput source="categories">
        <SimpleFormIterator>
          <TextInput source="name" />
          <TextInput source="description" />
          <ImageInput source="image" />
          <NumberInput source="display_order" />
        </SimpleFormIterator>
      </ArrayInput>
    </SimpleForm>
  </Edit>
);
```

---

## 🔧 **5. DÉPLOIEMENT ET INFRASTRUCTURE**

### **5.1 Docker Compose pour le Développement**
```yaml
# docker-compose.dev.yml
version: '3.8'

services:
  # API Gateway
  api-gateway:
    build: ./services/api-gateway
    ports:
      - "3000:3000"
    environment:
      - NODE_ENV=development
      - JWT_SECRET=${JWT_SECRET}
      - REDIS_URL=redis://redis:6379
    depends_on:
      - redis
      - restaurant-service

  # Restaurant Service
  restaurant-service:
    build: ./services/restaurant-service
    ports:
      - "3001:3001"
    environment:
      - NODE_ENV=development
      - DB_HOST=mysql
      - DB_USER=${DB_USER}
      - DB_PASSWORD=${DB_PASSWORD}
      - DB_NAME=${DB_NAME}
    depends_on:
      - mysql
      - redis

  # Payment Service
  payment-service:
    build: ./services/payment-service
    ports:
      - "3002:3002"
    environment:
      - NODE_ENV=development
      - STRIPE_SECRET_KEY=${STRIPE_SECRET_KEY}
      - WEBHOOK_SECRET=${WEBHOOK_SECRET}

  # Analytics Service
  analytics-service:
    build: ./services/analytics-service
    ports:
      - "3003:3003"
    environment:
      - NODE_ENV=development
      - DB_HOST=timescaledb
      - DB_USER=${DB_USER}
      - DB_PASSWORD=${DB_PASSWORD}

  # Databases
  mysql:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
      - MYSQL_DATABASE=${DB_NAME}
      - MYSQL_USER=${DB_USER}
      - MYSQL_PASSWORD=${DB_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
      - ./migrations:/docker-entrypoint-initdb.d

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

  timescaledb:
    image: timescale/timescaledb:latest-pg14
    environment:
      - POSTGRES_DB=${DB_NAME}
      - POSTGRES_USER=${DB_USER}
      - POSTGRES_PASSWORD=${DB_PASSWORD}
    volumes:
      - timescaledb_data:/var/lib/postgresql/data

volumes:
  mysql_data:
  timescaledb_data:
```

### **5.2 Kubernetes pour la Production**
```yaml
# k8s/restaurant-service-deployment.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: restaurant-service
  labels:
    app: restaurant-service
spec:
  replicas: 3
  selector:
    matchLabels:
      app: restaurant-service
  template:
    metadata:
      labels:
        app: restaurant-service
    spec:
      containers:
      - name: restaurant-service
        image: menumiam/restaurant-service:latest
        ports:
        - containerPort: 3001
        env:
        - name: DB_HOST
          value: "mysql-service"
        - name: REDIS_URL
          value: "redis-service"
        resources:
          requests:
            memory: "256Mi"
            cpu: "250m"
          limits:
            memory: "512Mi"
            cpu: "500m"
        livenessProbe:
          httpGet:
            path: /health
            port: 3001
          initialDelaySeconds: 30
          periodSeconds: 10
        readinessProbe:
          httpGet:
            path: /ready
            port: 3001
          initialDelaySeconds: 5
          periodSeconds: 5
---
apiVersion: v1
kind: Service
metadata:
  name: restaurant-service
spec:
  selector:
    app: restaurant-service
  ports:
  - protocol: TCP
    port: 3001
    targetPort: 3001
  type: ClusterIP
```

---

## 🧪 **6. STRATÉGIE DE TESTS**

### **6.1 Tests Unitaires - Backend**
```php
// tests/Unit/Services/MenuServiceTest.php
namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\MenuService;
use App\Repositories\MenuRepository;
use App\Factories\MenuFactory;
use App\Events\EventDispatcher;

class MenuServiceTest extends TestCase {
    private MenuService $menuService;
    private MenuRepository $menuRepository;
    private MenuFactory $menuFactory;
    private EventDispatcher $eventDispatcher;

    protected function setUp(): void {
        $this->menuRepository = $this->createMock(MenuRepository::class);
        $this->menuFactory = $this->createMock(MenuFactory::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        
        $this->menuService = new MenuService(
            $this->menuRepository,
            $this->menuFactory,
            $this->eventDispatcher
        );
    }

    public function testCreateMenuFromTemplate(): void {
        // Arrange
        $restaurantId = 1;
        $templateId = 2;
        $expectedMenu = new Menu(['restaurant_id' => $restaurantId]);
        
        $this->menuFactory
            ->expects($this->once())
            ->method('createFromTemplate')
            ->with($restaurantId, $templateId)
            ->willReturn($expectedMenu);
            
        $this->menuRepository
            ->expects($this->once())
            ->method('save')
            ->with($expectedMenu)
            ->willReturn($expectedMenu);
            
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch');

        // Act
        $result = $this->menuService->createFromTemplate($restaurantId, $templateId);

        // Assert
        $this->assertSame($expectedMenu, $result);
    }

    public function testUpdateMenuInvalidData(): void {
        // Arrange
        $menu = new Menu(['id' => 1]);
        $invalidData = ['name' => '']; // Empty name should be invalid

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->menuService->updateMenu($menu, $invalidData);
    }
}
```

### **6.2 Tests d'Intégration - Frontend**
```typescript
// tests/integration/MenuViewer.test.tsx
import React from 'react';
import { render, screen, waitFor, fireEvent } from '@testing-library/react';
import { MenuViewer } from '../../src/components/menu/MenuViewer';
import { useMenu } from '../../src/hooks/useMenu';

// Mock du hook
jest.mock('../../src/hooks/useMenu');
const mockUseMenu = useMenu as jest.MockedFunction<typeof useMenu>;

describe('MenuViewer Integration', () => {
  beforeEach(() => {
    mockUseMenu.mockClear();
  });

  test('displays menu items when loaded', async () => {
    // Arrange
    const mockMenu = [
      { id: 1, name: 'Pizza Margherita', price: 12.50, description: 'Classic pizza' },
      { id: 2, name: 'Pasta Carbonara', price: 10.00, description: 'Creamy pasta' }
    ];

    mockUseMenu.mockReturnValue({
      menu: mockMenu,
      loading: false,
      error: null,
      refetch: jest.fn()
    });

    // Act
    render(<MenuViewer restaurantId="123" />);

    // Assert
    await waitFor(() => {
      expect(screen.getByText('Pizza Margherita')).toBeInTheDocument();
      expect(screen.getByText('12.50€')).toBeInTheDocument();
      expect(screen.getByText('Pasta Carbonara')).toBeInTheDocument();
    });
  });

  test('shows loading state initially', () => {
    // Arrange
    mockUseMenu.mockReturnValue({
      menu: [],
      loading: true,
      error: null,
      refetch: jest.fn()
    });

    // Act
    render(<MenuViewer restaurantId="123" />);

    // Assert
    expect(screen.getByTestId('loading-spinner')).toBeInTheDocument();
  });

  test('displays error message when API fails', async () => {
    // Arrange
    mockUseMenu.mockReturnValue({
      menu: [],
      loading: false,
      error: 'Failed to load menu',
      refetch: jest.fn()
    });

    // Act
    render(<MenuViewer restaurantId="123" />);

    // Assert
    await waitFor(() => {
      expect(screen.getByText(/Failed to load menu/)).toBeInTheDocument();
    });
  });
});
```

---

## 📊 **7. MONITORING ET OBSERVABILITÉ**

### **7.1 Prometheus + Grafana**
```yaml
# monitoring/prometheus.yml
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'restaurant-service'
    static_configs:
      - targets: ['restaurant-service:3001']
    metrics_path: '/metrics'
    scrape_interval: 5s

  - job_name: 'api-gateway'
    static_configs:
      - targets: ['api-gateway:3000']
    metrics_path: '/metrics'

rule_files:
  - "alert_rules.yml"

alerting:
  alertmanagers:
    - static_configs:
        - targets:
          - alertmanager:9093
```

### **7.2 Health Checks**
```php
// src/Controllers/HealthController.php
<?php
namespace App\Controllers;

class HealthController {
    public function check(): array {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'filesystem' => $this->checkFilesystem(),
            'memory' => $this->checkMemory(),
            'disk_space' => $this->checkDiskSpace()
        ];

        $healthy = array_reduce($checks, fn($status, $check) => 
            $status && $check['status'] === 'ok', true);

        return [
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => date('c'),
            'checks' => $checks
        ];
    }

    private function checkDatabase(): array {
        try {
            $this->db->query('SELECT 1')->execute();
            return ['status' => 'ok', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkRedis(): array {
        try {
            $this->redis->ping();
            return ['status' => 'ok', 'message' => 'Redis connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
```

---

## 🎯 **8. SÉCURITÉ AVANCÉE**

### **8.1 OWASP Top 10 Compliance**
```php
// src/Middleware/SecurityMiddleware.php
<?php
namespace App\Middleware;

class SecurityMiddleware {
    public function handle(Request $request, callable $next): Response {
        // XSS Protection
        $this->sanitizeInput($request);
        
        // SQL Injection Protection
        $this->validateSQL($request);
        
        // CSRF Protection
        $this->validateCSRF($request);
        
        // Rate Limiting
        $this->checkRateLimit($request);
        
        return $next($request);
    }

    private function sanitizeInput(Request $request): void {
        $input = $request->all();
        
        array_walk_recursive($input, function(&$value) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        });
        
        $request->replace($input);
    }

    private function validateCSRF(Request $request): void {
        if ($request->isMethod('POST') && !$this->isValidCSRFToken($request)) {
            throw new CSRFTokenException('Invalid CSRF token');
        }
    }

    private function checkRateLimit(Request $request): void {
        $key = 'rate_limit:' . $request->ip();
        $count = $this->redis->incr($key);
        
        if ($count === 1) {
            $this->redis->expire($key, 60); // 1 minute window
        }
        
        if ($count > 1000) { // 1000 requests per minute
            throw new RateLimitException('Too many requests');
        }
    }
}
```

---

## 🎯 **CONCLUSION**

Cette architecture technique est conçue pour :

✅ **Scalabilité horizontale** : Microservices + load balancing  
✅ **Haute disponibilité** : Redondance + health checks  
✅ **Performance** : Cache + optimisations DB + CDN  
✅ **Sécurité** : OWASP compliance + monitoring  
✅ **Maintenabilité** : Tests + documentation + conventions  
✅ **Déploiement continu** : CI/CD + Docker + Kubernetes  

**MenuMiam V2 sera une solution enterprise-ready** capable de supporter des milliers de restaurants avec une qualité industrielle.

---
*Prochaine étape : Maquettage et structure responsive* 🎨
