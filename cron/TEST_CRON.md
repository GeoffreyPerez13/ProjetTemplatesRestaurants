# Guide de test du CRON auto_complete_reservations

## Étape 1 : Configuration des paramètres

1. Allez dans **Réservations > Paramètres**
2. Activez **"Marquage automatique comme terminée"**
3. Définissez la **durée à 5 minutes** (pour les tests)
4. Cliquez sur **"Enregistrer les paramètres"**

## Étape 2 : Créer une réservation de test

### Option A : Via l'interface admin
1. Allez dans **Réservations > Réservations**
2. Créez une nouvelle réservation avec :
   - Date : Aujourd'hui
   - Heure : Il y a 10 minutes (ex: si maintenant = 14:00, mettez 13:50)
   - Statut : **Confirmée** (important !)

### Option B : Via SQL (plus rapide)
```sql
INSERT INTO reservations (admin_id, customer_name, customer_email, customer_phone, reservation_date, reservation_time, party_size, status, created_at)
VALUES (
    1, -- Remplacer par votre admin_id
    'Test CRON',
    'test@example.com',
    '0123456789',
    CURDATE(),
    DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 10 MINUTE), '%H:%i:00'),
    2,
    'confirmed',
    NOW()
);
```

## Étape 3 : Exécuter le script CRON manuellement

### Windows (PowerShell)
```powershell
cd C:\wamp64\bin\php\php8.2.0
.\php.exe C:\wamp64\www\ProjetTemplatesRestaurants\cron\auto_complete_reservations.php
```

### Linux/Mac
```bash
php /path/to/ProjetTemplatesRestaurants/cron/auto_complete_reservations.php
```

## Étape 4 : Vérifier le résultat

1. Retournez dans **Réservations > Réservations**
2. La réservation de test devrait maintenant avoir le statut **"Terminée"**
3. Vérifiez les logs d'erreur PHP pour voir les messages du CRON

### Vérifier les logs (Windows avec WAMP)
```
C:\wamp64\logs\php_error.log
```

Vous devriez voir des lignes comme :
```
[2026-04-08 13:51:23] CRON auto_complete_reservations - Démarrage
[2026-04-08 13:51:23] CRON auto_complete_reservations - Admin 1 : 1 réservation(s) marquée(s) comme terminée(s)
[2026-04-08 13:51:23] CRON auto_complete_reservations - Terminé : 1 réservation(s) au total
```

## Étape 5 : Test avec différentes durées

1. Changez la durée à **10 minutes**
2. Créez une réservation il y a **15 minutes** → Devrait être marquée terminée
3. Créez une réservation il y a **5 minutes** → Ne devrait PAS être marquée terminée
4. Exécutez le CRON et vérifiez

## Étape 6 : Planifier le CRON en production

Une fois les tests validés, remettez la durée à **90 minutes** et planifiez le CRON :

### Windows (Planificateur de tâches)
- Fréquence : Toutes les 15 minutes
- Action : `C:\wamp64\bin\php\php8.2.0\php.exe`
- Arguments : `C:\wamp64\www\ProjetTemplatesRestaurants\cron\auto_complete_reservations.php`

### Linux (crontab)
```bash
*/15 * * * * /usr/bin/php /path/to/ProjetTemplatesRestaurants/cron/auto_complete_reservations.php
```

## Dépannage

### Le CRON ne marque rien
- Vérifiez que l'option `booking_auto_complete` est bien à `1` dans `admin_options`
- Vérifiez que la réservation a le statut `confirmed` (pas `pending`)
- Vérifiez que l'heure de la réservation + durée < maintenant

### Erreur de connexion DB
- Vérifiez que `app/config.php` contient les bonnes informations de connexion
- Vérifiez que le serveur MySQL est démarré

### Pas de logs
- Vérifiez que `error_log` est activé dans `php.ini`
- Vérifiez les permissions d'écriture sur le fichier de log
