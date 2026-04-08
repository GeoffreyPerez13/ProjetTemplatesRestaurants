# Guide complet : CRON sur Windows et haute disponibilité

## 📌 Table des matières
1. [CRON sur Windows : Pas de problème !](#1-cron-sur-windows--pas-de-problème-)
2. [Comment tester votre CRON](#2-comment-tester-votre-cron)
3. [Haute disponibilité et sécurité](#3-haute-disponibilité-et-sécurité)
4. [Monitoring et alertes](#4-monitoring-et-alertes)

---

## 1. CRON sur Windows : Pas de problème ! ✅

### ❌ Mythe : "CRON = Linux uniquement"
**Réalité** : Windows a son propre système de tâches planifiées tout aussi puissant !

### ✅ Solution : Planificateur de tâches Windows

Windows dispose du **Planificateur de tâches** (Task Scheduler) qui est l'équivalent de CRON.

---

## 🔧 Configuration du Planificateur de tâches Windows

### Méthode 1 : Interface graphique (recommandée pour débuter)

#### Étape 1 : Ouvrir le Planificateur de tâches
1. Appuyez sur `Windows + R`
2. Tapez `taskschd.msc`
3. Appuyez sur `Entrée`

#### Étape 2 : Créer une nouvelle tâche
1. Clic droit sur **"Bibliothèque du Planificateur de tâches"**
2. Sélectionnez **"Créer une tâche..."** (pas "Créer une tâche de base")

#### Étape 3 : Onglet "Général"
- **Nom** : `Auto-complete Reservations`
- **Description** : `Marque automatiquement les réservations comme terminées`
- ✅ Cochez **"Exécuter même si l'utilisateur n'est pas connecté"**
- ✅ Cochez **"Exécuter avec les autorisations maximales"**
- **Configurer pour** : Windows 10/11

#### Étape 4 : Onglet "Déclencheurs"
1. Cliquez sur **"Nouveau..."**
2. **Lancer la tâche** : `Selon une planification`
3. **Paramètres** : `Quotidien`
4. **Démarrer le** : Date du jour
5. **Heure** : `00:00:00`
6. ✅ Cochez **"Répéter la tâche toutes les"** : `15 minutes`
7. **Pendant** : `1 jour`
8. ✅ Cochez **"Activé"**
9. Cliquez sur **"OK"**

#### Étape 5 : Onglet "Actions"
1. Cliquez sur **"Nouveau..."**
2. **Action** : `Démarrer un programme`
3. **Programme/script** : `C:\wamp64\bin\php\php8.2.0\php.exe` (ajustez selon votre version PHP)
4. **Ajouter des arguments** : `C:\wamp64\www\ProjetTemplatesRestaurants\cron\auto_complete_reservations.php`
5. **Commencer dans** : `C:\wamp64\www\ProjetTemplatesRestaurants\cron`
6. Cliquez sur **"OK"**

#### Étape 6 : Onglet "Conditions"
- ❌ Décochez **"Démarrer la tâche uniquement si l'ordinateur est relié au secteur"**
- ✅ Cochez **"Réveiller l'ordinateur pour exécuter cette tâche"** (si serveur local)

#### Étape 7 : Onglet "Paramètres"
- ✅ Cochez **"Autoriser l'exécution de la tâche à la demande"**
- ✅ Cochez **"Exécuter la tâche dès que possible si un démarrage planifié est manqué"**
- **Si la tâche échoue, redémarrer toutes les** : `1 minute`
- **Tenter de redémarrer jusqu'à** : `3 fois`

#### Étape 8 : Valider
1. Cliquez sur **"OK"**
2. Entrez votre mot de passe Windows si demandé

---

### Méthode 2 : Ligne de commande (PowerShell)

```powershell
# Créer la tâche planifiée
$action = New-ScheduledTaskAction -Execute "C:\wamp64\bin\php\php8.2.0\php.exe" -Argument "C:\wamp64\www\ProjetTemplatesRestaurants\cron\auto_complete_reservations.php" -WorkingDirectory "C:\wamp64\www\ProjetTemplatesRestaurants\cron"

$trigger = New-ScheduledTaskTrigger -Daily -At "00:00" -RepetitionInterval (New-TimeSpan -Minutes 15) -RepetitionDuration (New-TimeSpan -Days 1)

$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RestartCount 3 -RestartInterval (New-TimeSpan -Minutes 1)

Register-ScheduledTask -TaskName "Auto-complete Reservations" -Action $action -Trigger $trigger -Settings $settings -Description "Marque automatiquement les réservations comme terminées" -RunLevel Highest
```

---

## 2. Comment tester votre CRON 🧪

### Test 1 : Exécution manuelle (immédiate)

#### Via PowerShell
```powershell
php C:\wamp64\www\ProjetTemplatesRestaurants\cron\auto_complete_reservations.php
```

**Résultat attendu** :
- Exit code : `0` (succès)
- Aucune erreur affichée

#### Via Planificateur de tâches
1. Ouvrez le Planificateur de tâches
2. Trouvez votre tâche **"Auto-complete Reservations"**
3. Clic droit → **"Exécuter"**
4. Vérifiez l'onglet **"Historique"** pour voir le résultat

---

### Test 2 : Vérification des logs

#### Logs PHP (erreurs)
Fichier : `C:\wamp64\logs\php_error.log`

**Recherchez** :
```
[2026-04-08 14:00:00] CRON auto_complete_reservations - Démarrage
[2026-04-08 14:00:00] CRON auto_complete_reservations - Admin 1 : 1 réservation(s) marquée(s) comme terminée(s)
[2026-04-08 14:00:00] CRON auto_complete_reservations - Terminé : 1 réservation(s) au total
```

#### Logs Planificateur de tâches
1. Planificateur de tâches → Votre tâche
2. Onglet **"Historique"**
3. Vérifiez les événements :
   - **ID 100** : Tâche démarrée
   - **ID 102** : Tâche terminée avec succès

---

### Test 3 : Scénario complet de bout en bout

#### Préparation (5 minutes)
1. Allez dans **Réservations > Paramètres**
2. Activez **"Marquage automatique comme terminée"**
3. Durée : **5 minutes**
4. Enregistrez

#### Création de la réservation test
1. Allez dans **Réservations > Réservations**
2. Créez une réservation :
   - **Date** : Aujourd'hui
   - **Heure** : Il y a 10 minutes (ex: 13:50 si maintenant = 14:00)
   - **Statut** : **Confirmée** ✅
   - **Client** : Test CRON

#### Exécution
```powershell
php C:\wamp64\www\ProjetTemplatesRestaurants\cron\auto_complete_reservations.php
```

#### Vérification
1. Retournez dans **Réservations > Réservations**
2. La réservation "Test CRON" devrait avoir le statut **"Terminée"** ✅
3. Vérifiez les logs PHP pour confirmation

---

## 3. Haute disponibilité et sécurité 🛡️

### Problème : "Si mon serveur est HS, mon CRON ne s'exécutera plus"

**Vous avez raison !** C'est un problème réel. Voici les solutions :

---

### Solution 1 : Serveur dédié / VPS (Recommandé pour production)

#### Avantages
- ✅ Disponibilité 24/7
- ✅ Pas de dépendance à votre ordinateur local
- ✅ Backup automatique
- ✅ Monitoring inclus

#### Hébergeurs recommandés
1. **OVH** (France) - À partir de 3€/mois
2. **Scaleway** (France) - À partir de 3€/mois
3. **DigitalOcean** - À partir de 6$/mois
4. **AWS Lightsail** - À partir de 5$/mois

#### Configuration CRON sur serveur Linux
```bash
# Éditer le crontab
crontab -e

# Ajouter cette ligne (exécution toutes les 15 minutes)
*/15 * * * * /usr/bin/php /var/www/ProjetTemplatesRestaurants/cron/auto_complete_reservations.php >> /var/log/cron_reservations.log 2>&1
```

---

### Solution 2 : Services CRON externes (Cloud CRON)

#### Principe
Un service externe appelle votre script via HTTP toutes les 15 minutes.

#### Services recommandés
1. **EasyCron** - Gratuit jusqu'à 1 tâche
2. **Cron-job.org** - Gratuit
3. **SetCronJob** - Gratuit

#### Étape 1 : Créer un endpoint HTTP pour le CRON
Créez `public/cron-endpoint.php` :

```php
<?php
// Sécurité : Token secret pour éviter les exécutions non autorisées
$secretToken = 'VOTRE_TOKEN_SECRET_UNIQUE_ICI'; // Changez-moi !

if (!isset($_GET['token']) || $_GET['token'] !== $secretToken) {
    http_response_code(403);
    die('Unauthorized');
}

// Exécuter le script CRON
require_once __DIR__ . '/../cron/auto_complete_reservations.php';
```

#### Étape 2 : Configurer le service externe
- **URL** : `https://votre-domaine.com/cron-endpoint.php?token=VOTRE_TOKEN_SECRET_UNIQUE_ICI`
- **Fréquence** : Toutes les 15 minutes
- **Méthode** : GET

---

### Solution 3 : Backup et redondance

#### Stratégie de backup automatique

##### 1. Backup de la base de données (quotidien)
Créez `cron/backup_database.php` :

```php
<?php
require_once __DIR__ . '/../config.php';

$backupDir = __DIR__ . '/../backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$filename = $backupDir . '/backup_' . date('Y-m-d_H-i-s') . '.sql';

// Récupérer les infos de connexion depuis $pdo
$host = $pdo->query("SELECT @@hostname")->fetchColumn();
$dbname = $pdo->query("SELECT DATABASE()")->fetchColumn();

// Commande mysqldump
$command = sprintf(
    'mysqldump -h %s -u %s -p%s %s > %s',
    escapeshellarg($host),
    escapeshellarg('votre_user'),
    escapeshellarg('votre_password'),
    escapeshellarg($dbname),
    escapeshellarg($filename)
);

exec($command, $output, $return);

if ($return === 0) {
    error_log("[" . date('Y-m-d H:i:s') . "] Backup réussi : $filename");
    
    // Supprimer les backups de plus de 7 jours
    $files = glob($backupDir . '/backup_*.sql');
    foreach ($files as $file) {
        if (filemtime($file) < strtotime('-7 days')) {
            unlink($file);
        }
    }
} else {
    error_log("[" . date('Y-m-d H:i:s') . "] Erreur backup : " . implode("\n", $output));
}
```

##### 2. Planifier le backup (Windows)
```powershell
$action = New-ScheduledTaskAction -Execute "C:\wamp64\bin\php\php8.2.0\php.exe" -Argument "C:\wamp64\www\ProjetTemplatesRestaurants\cron\backup_database.php"
$trigger = New-ScheduledTaskTrigger -Daily -At "03:00"
Register-ScheduledTask -TaskName "Backup Database" -Action $action -Trigger $trigger -RunLevel Highest
```

---

## 4. Monitoring et alertes 📊

### Solution 1 : Logs avec rotation

#### Créer un système de logs dédié
Modifiez `auto_complete_reservations.php` pour écrire dans un fichier dédié :

```php
// Au début du script
$logFile = __DIR__ . '/../logs/cron_auto_complete.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

function logCron($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Remplacer tous les error_log() par logCron()
```

---

### Solution 2 : Alertes par email en cas d'erreur

Ajoutez à la fin de `auto_complete_reservations.php` :

```php
// Envoyer un email en cas d'erreur
function sendErrorAlert($error) {
    $to = 'admin@votre-domaine.com';
    $subject = '[ALERTE] Erreur CRON Auto-complete Reservations';
    $message = "Une erreur s'est produite lors de l'exécution du CRON :\n\n" . $error;
    $headers = 'From: cron@votre-domaine.com';
    
    mail($to, $subject, $message, $headers);
}

// Utiliser dans les blocs catch
try {
    // ... code existant ...
} catch (Exception $e) {
    $errorMsg = $e->getMessage();
    error_log("CRON auto_complete_reservations - ERREUR : $errorMsg");
    sendErrorAlert($errorMsg);
    exit(1);
}
```

---

### Solution 3 : Monitoring externe (Uptime monitoring)

#### Services recommandés
1. **UptimeRobot** - Gratuit (50 moniteurs)
2. **Pingdom** - Payant mais très complet
3. **StatusCake** - Gratuit (10 moniteurs)

#### Configuration
1. Créez un endpoint de santé `public/health-check.php` :

```php
<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$health = [
    'status' => 'ok',
    'timestamp' => date('c'),
    'checks' => []
];

// Vérifier la connexion DB
try {
    $pdo->query('SELECT 1');
    $health['checks']['database'] = 'ok';
} catch (Exception $e) {
    $health['status'] = 'error';
    $health['checks']['database'] = 'error';
}

// Vérifier le dernier run du CRON
$logFile = __DIR__ . '/../logs/cron_auto_complete.log';
if (file_exists($logFile)) {
    $lastModified = filemtime($logFile);
    $minutesSinceLastRun = (time() - $lastModified) / 60;
    
    if ($minutesSinceLastRun > 30) { // Alerte si pas exécuté depuis 30 min
        $health['status'] = 'warning';
        $health['checks']['cron'] = 'warning - last run: ' . round($minutesSinceLastRun) . ' minutes ago';
    } else {
        $health['checks']['cron'] = 'ok';
    }
} else {
    $health['checks']['cron'] = 'unknown';
}

http_response_code($health['status'] === 'ok' ? 200 : 503);
echo json_encode($health, JSON_PRETTY_PRINT);
```

2. Configurez UptimeRobot pour vérifier `https://votre-domaine.com/health-check.php` toutes les 5 minutes

---

## 📋 Checklist de mise en production

### Avant le déploiement
- [ ] Tester le CRON manuellement (exit code 0)
- [ ] Vérifier les logs PHP
- [ ] Créer une réservation de test et vérifier le marquage automatique
- [ ] Remettre la durée à 90 minutes (ou selon vos besoins)

### Configuration serveur
- [ ] Configurer le Planificateur de tâches Windows (toutes les 15 min)
- [ ] OU Configurer le crontab Linux (si VPS)
- [ ] OU Configurer un service CRON externe

### Sécurité et backup
- [ ] Configurer le backup automatique de la base de données (quotidien)
- [ ] Tester la restauration d'un backup
- [ ] Configurer les alertes email en cas d'erreur

### Monitoring
- [ ] Créer le endpoint health-check
- [ ] Configurer UptimeRobot ou équivalent
- [ ] Tester les alertes

---

## 🎯 Recommandation finale

### Pour un environnement de développement (WAMP local)
✅ **Planificateur de tâches Windows** suffit largement

### Pour un environnement de production
✅ **VPS Linux** + **Backup automatique** + **Monitoring externe**

**Pourquoi ?**
- Disponibilité 24/7 garantie
- Pas de dépendance à votre ordinateur
- Backup automatique
- Monitoring et alertes
- Coût : ~5€/mois

---

## 💡 Besoin d'aide ?

Si vous avez des questions ou rencontrez des problèmes :
1. Vérifiez les logs : `C:\wamp64\logs\php_error.log`
2. Vérifiez l'historique du Planificateur de tâches
3. Testez manuellement le script PHP
4. Vérifiez que MySQL est démarré
5. Vérifiez les permissions du dossier `logs/`
