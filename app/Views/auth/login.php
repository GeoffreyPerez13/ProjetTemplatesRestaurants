<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - MenuMiam</title>
    <link rel="stylesheet" href="/public/assets/css/admin/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>MenuMiam</h1>
                <p>Connectez-vous à votre espace</p>
            </div>

            <?php if (\App\Helpers\Session::hasFlash('error')): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars(\App\Helpers\Session::getFlash('error')) ?>
                </div>
            <?php endif; ?>

            <?php if (\App\Helpers\Session::hasFlash('success')): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars(\App\Helpers\Session::getFlash('success')) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="form-group">
                    <label for="identifier">Email ou nom d'utilisateur</label>
                    <input 
                        type="text" 
                        id="identifier" 
                        name="identifier" 
                        value="<?= htmlspecialchars(\App\Helpers\Session::get('old')['identifier'] ?? '') ?>"
                        required 
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>

            <div class="auth-footer">
                <p>Pas encore de compte ? <a href="/register">Inscrivez-vous</a></p>
            </div>
        </div>
    </div>
</body>
</html>
