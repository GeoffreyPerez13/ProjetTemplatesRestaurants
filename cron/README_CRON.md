# Configuration du CRON Job pour le marquage automatique des réservations

## Description

Le script `auto_complete_reservations.php` marque automatiquement les réservations confirmées comme "terminées" après la durée du repas configurée (par défaut 90 minutes).

## Prérequis

- PHP CLI installé
- Accès au planificateur de tâches (Windows) ou CRON (Linux)

---

## Configuration sur Windows (WAMP)

### 1. Localiser PHP CLI

Le fichier PHP CLI se trouve généralement ici :
```
C:\wamp64\bin\php\php8.x.x\php.exe
```

Remplacez `php8.x.x` par votre version (ex: `php8.2.0`)

### 2. Ouvrir le Planificateur de tâches

1. Appuyez sur `Win + R`
2. Tapez `taskschd.msc` et validez
3. Cliquez sur "Créer une tâche..." dans le panneau de droite

### 3. Configurer la tâche

**Onglet Général :**
- Nom : `MenuMiam - Auto Complete Reservations`
- Description : `Marque automatiquement les réservations comme terminées`
- Cochez "Exécuter même si l'utilisateur n'est pas connecté"

**Onglet Déclencheurs :**
- Cliquez sur "Nouveau..."
- Répéter la tâche toutes les : `15 minutes`
- Pendant : `Indéfiniment`

**Onglet Actions :**
- Action : `Démarrer un programme`
- Programme/script : `C:\wamp64\bin\php\php8.2.0\php.exe`
- Ajouter des arguments : `C:\wamp64\www\ProjetTemplatesRestaurants\cron\auto_complete_reservations.php`

**Onglet Conditions :**
- Décochez "Démarrer la tâche uniquement si l'ordinateur est relié au secteur"

**Onglet Paramètres :**
- Cochez "Autoriser l'exécution de la tâche à la demande"

### 4. Tester manuellement

Ouvrez un terminal (CMD) et exécutez :
```cmd
C:\wamp64\bin\php\php8.2.0\php.exe C:\wamp64\www\ProjetTemplatesRestaurants\cron\auto_complete_reservations.php
```

Vérifiez les logs dans le fichier d'erreurs PHP ou dans l'Event Viewer de Windows.

---

## Configuration sur Linux

### 1. Éditer le CRON

```bash
crontab -e
```

### 2. Ajouter la ligne suivante

Toutes les 15 minutes :
```
*/15 * * * * /usr/bin/php /path/to/ProjetTemplatesRestaurants/cron/auto_complete_reservations.php
```

Remplacez `/path/to/` par le chemin absolu de votre projet.

### 3. Vérifier les logs

Les logs sont écrits dans le fichier d'erreurs PHP configuré dans `php.ini`.

Vous pouvez aussi rediriger les logs vers un fichier :
```
*/15 * * * * /usr/bin/php /path/to/ProjetTemplatesRestaurants/cron/auto_complete_reservations.php >> /var/log/menumiam_cron.log 2>&1
```

---

## Fonctionnement

Le script :
1. Vérifie quels admins ont activé le marquage automatique (`booking_auto_complete = 1`)
2. Pour chaque admin, récupère la durée du repas configurée (`booking_meal_duration`)
3. Trouve toutes les réservations confirmées dont l'heure + durée est dépassée
4. Les marque comme `completed`
5. Écrit les résultats dans les logs

## Fréquence recommandée

- **15 minutes** : Bon compromis entre réactivité et charge serveur
- **30 minutes** : Si vous avez peu de réservations
- **5 minutes** : Si vous voulez une mise à jour très fréquente (plus de charge)

## Désactivation

Pour désactiver le marquage automatique :
1. Allez dans l'onglet "Paramètres" de la page Réservations
2. Désactivez le toggle "Marquage automatique comme terminée"
3. Le CRON continuera de tourner mais ne fera rien pour cet admin

## Dépannage

### Le script ne s'exécute pas

- Vérifiez que PHP CLI est bien installé : `php -v`
- Vérifiez les permissions du fichier (Linux) : `chmod +x auto_complete_reservations.php`
- Vérifiez les logs d'erreur PHP

### Aucune réservation n'est marquée

- Vérifiez que l'option est bien activée dans les paramètres
- Vérifiez que la durée du repas est correcte
- Vérifiez qu'il y a bien des réservations confirmées dépassées
- Consultez les logs du CRON

### Erreur de connexion DB

- Vérifiez que `app/config.php` existe et contient les bonnes informations
- Vérifiez que MySQL est démarré
