# Solution : Problème de mot de passe Windows pour le Planificateur de tâches

## 🔐 Pourquoi le mot de passe est demandé ?

Le Planificateur de tâches demande votre mot de passe Windows lorsque vous cochez :
- ✅ **"Exécuter même si l'utilisateur n'est pas connecté"**

Cette option est nécessaire pour que le CRON s'exécute automatiquement, même si vous n'êtes pas connecté à Windows.

---

## ✅ Solutions (par ordre de facilité)

### Solution 1 : Exécuter uniquement quand vous êtes connecté (SIMPLE)

**Avantages** :
- ✅ Pas besoin de mot de passe
- ✅ Configuration immédiate

**Inconvénients** :
- ❌ Le CRON ne s'exécute que si vous êtes connecté à Windows
- ❌ Pas idéal pour un serveur de production

#### Configuration

**Étape 1 : Onglet "Général"**
- **Nom** : `Auto-complete Reservations`
- ❌ **NE PAS cocher** "Exécuter même si l'utilisateur n'est pas connecté"
- ✅ Cochez **"Exécuter uniquement si l'utilisateur est connecté"**
- ❌ **NE PAS cocher** "Exécuter avec les autorisations maximales"

**Étape 2 : Continuer normalement**
- Configurez les déclencheurs (toutes les 15 minutes)
- Configurez l'action (php.exe + script)
- Cliquez sur **"OK"**
- ✅ **Aucun mot de passe ne sera demandé !**

---

### Solution 2 : Réinitialiser votre mot de passe Windows (RECOMMANDÉ)

Si vous ne connaissez plus votre mot de passe Windows, vous pouvez le réinitialiser.

#### Option A : Compte Microsoft

Si votre compte Windows est lié à un compte Microsoft :

1. Allez sur : https://account.live.com/password/reset
2. Suivez les instructions pour réinitialiser votre mot de passe
3. Redémarrez Windows
4. Connectez-vous avec le nouveau mot de passe
5. Retournez dans le Planificateur de tâches et utilisez ce nouveau mot de passe

#### Option B : Compte local

Si vous avez un compte local Windows :

**Méthode 1 : Via un autre compte administrateur**
1. Connectez-vous avec un autre compte administrateur
2. Allez dans **Paramètres > Comptes > Famille et autres utilisateurs**
3. Sélectionnez votre compte
4. Cliquez sur **"Modifier le mot de passe"**

**Méthode 2 : Via l'invite de commandes (mode sans échec)**
1. Redémarrez Windows en mode sans échec
2. Ouvrez l'invite de commandes en tant qu'administrateur
3. Tapez : `net user VotreNomUtilisateur NouveauMotDePasse`
4. Redémarrez normalement

---

### Solution 3 : Créer un compte de service dédié (AVANCÉ)

Créez un compte Windows spécifique pour exécuter les tâches planifiées.

#### Étape 1 : Créer le compte

1. Ouvrez **Gestion de l'ordinateur** (`compmgmt.msc`)
2. Allez dans **Utilisateurs et groupes locaux > Utilisateurs**
3. Clic droit → **Nouvel utilisateur**
4. **Nom d'utilisateur** : `ServiceCRON`
5. **Mot de passe** : Créez un mot de passe fort (notez-le !)
6. ✅ Cochez **"Le mot de passe n'expire jamais"**
7. ❌ Décochez **"L'utilisateur doit changer le mot de passe à la prochaine ouverture de session"**
8. Cliquez sur **"Créer"**

#### Étape 2 : Donner les permissions

1. Clic droit sur le compte **ServiceCRON** → **Propriétés**
2. Onglet **"Membre de"**
3. Cliquez sur **"Ajouter..."**
4. Tapez : `Utilisateurs`
5. Cliquez sur **"OK"**

#### Étape 3 : Donner les permissions sur les dossiers

Ouvrez PowerShell en tant qu'administrateur :

```powershell
# Donner les permissions de lecture sur le projet
icacls "C:\wamp64\www\ProjetTemplatesRestaurants" /grant ServiceCRON:(OI)(CI)RX /T

# Donner les permissions d'écriture sur les logs
icacls "C:\wamp64\logs" /grant ServiceCRON:(OI)(CI)M /T
```

#### Étape 4 : Configurer la tâche planifiée

1. Dans le Planificateur de tâches, onglet **"Général"**
2. Cliquez sur **"Modifier l'utilisateur ou le groupe..."**
3. Tapez : `ServiceCRON`
4. Cliquez sur **"OK"**
5. Entrez le mot de passe du compte **ServiceCRON**
6. ✅ La tâche s'exécutera avec ce compte !

---

### Solution 4 : Utiliser PowerShell avec le compte actuel (SIMPLE)

Créez une tâche qui s'exécute uniquement quand vous êtes connecté, via PowerShell.

#### Étape 1 : Créer la tâche

Ouvrez PowerShell **en tant qu'administrateur** :

```powershell
# Créer la tâche planifiée SANS mot de passe
$action = New-ScheduledTaskAction -Execute "C:\wamp64\bin\php\php8.2.0\php.exe" -Argument "C:\wamp64\www\ProjetTemplatesRestaurants\cron\auto_complete_reservations.php" -WorkingDirectory "C:\wamp64\www\ProjetTemplatesRestaurants\cron"

$trigger = New-ScheduledTaskTrigger -Daily -At "00:00" -RepetitionInterval (New-TimeSpan -Minutes 15) -RepetitionDuration (New-TimeSpan -Days 1)

$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable

# Enregistrer la tâche pour l'utilisateur actuel (pas besoin de mot de passe)
Register-ScheduledTask -TaskName "Auto-complete Reservations" -Action $action -Trigger $trigger -Settings $settings -Description "Marque automatiquement les réservations comme terminées"
```

**Important** : Cette tâche ne s'exécutera que si vous êtes connecté.

---

### Solution 5 : Utiliser un service CRON externe (SANS SERVEUR LOCAL)

Si vous ne voulez pas gérer le Planificateur de tâches Windows, utilisez un service externe.

#### Étape 1 : Créer un endpoint HTTP

Créez le fichier `public/cron-endpoint.php` :

```php
<?php
/**
 * Endpoint HTTP pour exécution CRON externe
 * Sécurisé par token secret
 */

// Token secret unique (changez-moi !)
$secretToken = bin2hex(random_bytes(32)); // Générez un token unique
// Exemple : 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6'

// Vérification du token
if (!isset($_GET['token']) || $_GET['token'] !== $secretToken) {
    http_response_code(403);
    die('Unauthorized');
}

// Exécuter le script CRON
require_once __DIR__ . '/../cron/auto_complete_reservations.php';

echo "CRON executed successfully";
```

#### Étape 2 : Notez votre token

Ouvrez le fichier et copiez la valeur de `$secretToken`.

**Exemple** : `a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6`

#### Étape 3 : Tester localement

```
http://localhost/ProjetTemplatesRestaurants/cron-endpoint.php?token=VOTRE_TOKEN_ICI
```

#### Étape 4 : Configurer un service CRON externe

**Services gratuits** :
1. **EasyCron** : https://www.easycron.com/
2. **Cron-job.org** : https://cron-job.org/
3. **SetCronJob** : https://www.setcronjob.com/

**Configuration** :
- **URL** : `http://localhost/ProjetTemplatesRestaurants/cron-endpoint.php?token=VOTRE_TOKEN`
- **Fréquence** : Toutes les 15 minutes
- **Méthode** : GET

**⚠️ Important** : Cette solution nécessite que votre serveur soit accessible depuis Internet (pas idéal pour localhost).

---

## 🎯 Quelle solution choisir ?

### Pour un environnement de développement local (WAMP)

✅ **Solution 1** (Exécuter uniquement quand connecté)
- Simple et rapide
- Pas besoin de mot de passe
- Suffisant pour le développement

### Pour un environnement de production

✅ **Solution 2** (Réinitialiser le mot de passe) + Configuration complète
- Meilleure solution
- Exécution 24/7
- Professionnel

**OU**

✅ **VPS Linux** (recommandé)
- Pas de problème de mot de passe Windows
- Disponibilité garantie
- CRON natif Linux

---

## 📋 Récapitulatif des commandes PowerShell

### Créer une tâche SANS mot de passe (utilisateur connecté uniquement)

```powershell
$action = New-ScheduledTaskAction -Execute "C:\wamp64\bin\php\php8.2.0\php.exe" -Argument "C:\wamp64\www\ProjetTemplatesRestaurants\cron\auto_complete_reservations.php" -WorkingDirectory "C:\wamp64\www\ProjetTemplatesRestaurants\cron"

$trigger = New-ScheduledTaskTrigger -Daily -At "00:00" -RepetitionInterval (New-TimeSpan -Minutes 15) -RepetitionDuration (New-TimeSpan -Days 1)

$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable

Register-ScheduledTask -TaskName "Auto-complete Reservations" -Action $action -Trigger $trigger -Settings $settings -Description "Marque automatiquement les réservations comme terminées"
```

### Vérifier que la tâche est créée

```powershell
Get-ScheduledTask -TaskName "Auto-complete Reservations"
```

### Exécuter la tâche manuellement

```powershell
Start-ScheduledTask -TaskName "Auto-complete Reservations"
```

### Supprimer la tâche (si besoin)

```powershell
Unregister-ScheduledTask -TaskName "Auto-complete Reservations" -Confirm:$false
```

---

## ✅ Test rapide

Une fois la tâche créée (avec n'importe quelle solution) :

1. Ouvrez le Planificateur de tâches
2. Trouvez votre tâche **"Auto-complete Reservations"**
3. Clic droit → **"Exécuter"**
4. Vérifiez les logs : `C:\wamp64\logs\php_error.log`
5. Vérifiez qu'une réservation test est marquée comme terminée

---

## 💡 Recommandation finale

**Pour le développement** :
✅ Utilisez la **Solution 1** (exécution uniquement quand connecté) ou la **Solution 4** (PowerShell)

**Pour la production** :
✅ Migrez vers un **VPS Linux** (~5€/mois) pour éviter tous ces problèmes Windows

---

## 🆘 Besoin d'aide ?

Si aucune solution ne fonctionne :
1. Vérifiez que vous êtes administrateur de votre PC
2. Vérifiez que le Planificateur de tâches est activé
3. Testez d'abord le script manuellement : `php C:\wamp64\www\ProjetTemplatesRestaurants\cron\auto_complete_reservations.php`
4. Vérifiez les logs d'erreur PHP
