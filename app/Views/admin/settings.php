<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Paramètres') ?> - MenuMiam</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/shared/toast.css">
    <style>
        .settings-section {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }
        
        .settings-section h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input {
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-primary {
            padding: 12px 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
    </style>
</head>
<body>
    <?php require APP_PATH . '/Views/partials/header.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Paramètres</h1>
            <p>Gérez les informations de votre compte</p>
        </div>

        <div class="settings-section">
            <h2>Informations du profil</h2>
            
            <form method="POST" action="<?= BASE_URL ?>/public/settings/update-profile" class="ajax-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur *</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="<?= htmlspecialchars($admin['username']) ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?= htmlspecialchars($admin['email']) ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="restaurant_name">Nom du restaurant *</label>
                        <input 
                            type="text" 
                            id="restaurant_name" 
                            name="restaurant_name" 
                            value="<?= htmlspecialchars($admin['restaurant_name']) ?>"
                            required
                        >
                    </div>
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </div>

        <div class="settings-section">
            <h2>Informations du compte</h2>
            <ul>
                <li><strong>Slug :</strong> <?= htmlspecialchars($admin['slug']) ?></li>
                <li><strong>Mode carte :</strong> <?= htmlspecialchars($admin['carte_mode']) ?></li>
                <li><strong>Rôle :</strong> <?= htmlspecialchars($admin['role']) ?></li>
                <li><strong>Créé le :</strong> <?= date('d/m/Y', strtotime($admin['created_at'])) ?></li>
            </ul>
        </div>
    </div>

    <?php require APP_PATH . '/Views/partials/footer.php'; ?>
    
    <script src="<?= BASE_URL ?>/public/assets/js/app.js"></script>
</body>
</html>
