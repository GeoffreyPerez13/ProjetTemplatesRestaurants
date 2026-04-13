<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - MenuMiam</title>
    <link rel="stylesheet" href="/public/assets/css/admin/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>MenuMiam</h1>
                <p>Créez votre compte restaurateur</p>
            </div>

            <?php if (\App\Helpers\Session::hasFlash('error')): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars(\App\Helpers\Session::getFlash('error')) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/register" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="form-group">
                    <label for="restaurant_name">Nom du restaurant *</label>
                    <input 
                        type="text" 
                        id="restaurant_name" 
                        name="restaurant_name" 
                        value="<?= htmlspecialchars(\App\Helpers\Session::get('old')['restaurant_name'] ?? '') ?>"
                        required 
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="username">Nom d'utilisateur *</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?= htmlspecialchars(\App\Helpers\Session::get('old')['username'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?= htmlspecialchars(\App\Helpers\Session::get('old')['email'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe * (min. 8 caractères)</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirmer le mot de passe *</label>
                    <input 
                        type="password" 
                        id="password_confirm" 
                        name="password_confirm" 
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary">S'inscrire</button>
            </form>

            <div class="auth-footer">
                <p>Déjà un compte ? <a href="/login">Connectez-vous</a></p>
            </div>
        </div>
    </div>
</body>
</html>
