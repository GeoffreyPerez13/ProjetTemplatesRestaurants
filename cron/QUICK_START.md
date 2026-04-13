# Guide rapide : Tester le CRON en 5 minutes ⚡

## 🎯 Objectif
Vérifier que le marquage automatique des réservations fonctionne correctement.

---

## ✅ Étape 1 : Configuration (2 minutes)

1. Ouvrez votre navigateur → `http://localhost/ProjetTemplatesRestaurants/?page=reservations`
2. Cliquez sur l'onglet **"Paramètres"**
3. Activez **"Marquage automatique comme terminée"** ✅
4. Durée : **5 minutes** (pour les tests)
5. Cliquez sur **"Enregistrer les paramètres"**

---

## ✅ Étape 2 : Créer une réservation test (1 minute)

1. Cliquez sur l'onglet **"Réservations"**
2. Créez une nouvelle réservation :
   - **Nom** : Test CRON
   - **Email** : test@example.com
   - **Téléphone** : 0123456789
   - **Date** : Aujourd'hui
   - **Heure** : **Il y a 10 minutes** ⚠️
     - Ex: Si maintenant = 14:00, mettez **13:50**
   - **Nombre de personnes** : 2
   - **Statut** : **Confirmée** ✅ (très important !)
3. Enregistrez

---

## ✅ Étape 3 : Exécuter le CRON (30 secondes)

Ouvrez **PowerShell** et exécutez :

```powershell
php C:\wamp64\www\ProjetTemplatesRestaurants\cron\auto_complete_reservations.php
```

**Résultat attendu** :
```
Failed loading c:/wamp64/bin/php/php8.5.0/zend_ext/php_xdebug-3.4.7-8.4-ts-vs17-x86_64.dll
```
(C'est normal, c'est juste un avertissement xdebug)

**Pas d'erreur = Succès !** ✅

---

## ✅ Étape 4 : Vérifier le résultat (30 secondes)

1. Retournez dans votre navigateur
2. Rechargez la page des réservations (F5)
3. La réservation **"Test CRON"** devrait maintenant avoir le statut **"Terminée"** 🎉

---

## ✅ Étape 5 : Vérifier les logs (optionnel)

Ouvrez le fichier : `C:\wamp64\logs\php_error.log`

Cherchez les lignes :
```
[2026-04-08 14:00:00] CRON auto_complete_reservations - Démarrage
[2026-04-08 14:00:00] CRON auto_complete_reservations - Admin 1 : 1 réservation(s) marquée(s) comme terminée(s)
[2026-04-08 14:00:00] CRON auto_complete_reservations - Terminé : 1 réservation(s) au total
```

---

## 🎉 Succès !

Votre CRON fonctionne parfaitement ! 

### Prochaines étapes :

1. **Remettez la durée à 90 minutes** (ou selon vos besoins)
2. **Configurez le Planificateur de tâches Windows** pour exécuter automatiquement le CRON toutes les 15 minutes
   - Voir le guide complet : `GUIDE_WINDOWS_CRON.md`

---

## ❌ Problèmes ?

### La réservation n'est pas marquée comme terminée

**Vérifiez** :
- ✅ L'option "Marquage automatique" est bien activée
- ✅ La réservation a le statut **"Confirmée"** (pas "En attente")
- ✅ L'heure de la réservation + 5 minutes < maintenant
- ✅ MySQL est démarré (WAMP)

### Erreur "Failed to open stream"

**Solution** : Vérifiez que le fichier `config.php` existe à la racine du projet

### Erreur "Call to a member function prepare() on null"

**Solution** : Vérifiez que MySQL est démarré et que `config.php` crée bien `$pdo`

---

## 📚 Documentation complète

Pour plus d'informations :
- **Guide Windows complet** : `GUIDE_WINDOWS_CRON.md`
- **Tests avancés** : `TEST_CRON.md`
- **README CRON** : `README_CRON.md`
