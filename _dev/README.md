# 📁 Dossier _dev - Fichiers de développement

Ce dossier contient tous les fichiers qui ne sont **pas nécessaires pour la production** et peuvent être supprimés avant la mise en ligne.

## 🗂️ Structure

### 📄 `docs/` - Documentation et rapports
- `AUDIT_REPORT.md` - Rapport d'audit de sécurité et performance
- `README_SECURITY.md` - Documentation sur les mesures de sécurité
- `project_tasks_organized.md` - Suivi des tâches de développement
- `testRefonte_commits_list.txt` - Historique des commits pour la refonte

### 🛠️ `tools/` - Outils de développement
- `mailhog.bat` - Script pour démarrer MailHog (test emails)

### ⚙️ Fichiers de configuration exemples
- `.env.example` - Fichier d'environnement modèle
- `config.example.php` - Fichier de configuration modèle

## 🚀 Avant la mise en production

**Supprimez entièrement le dossier `_dev/`** avant de déployer l'application en production.

Ces fichiers contiennent :
- Informations sensibles de développement
- Documentation interne
- Outils de test
- Fichiers de configuration exemples

## ⚠️ Important

- **Ne jamais** uploader le dossier `_dev/` sur un serveur de production
- **Ne jamais** partager les fichiers de développement avec des clients
- **Conserver** ces fichiers uniquement pour l'environnement de développement local

---
*Généré automatiquement - Dossier de développement MenuMiam*
