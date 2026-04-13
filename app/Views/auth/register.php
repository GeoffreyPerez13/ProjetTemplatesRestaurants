<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - MenuMiam</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin/auth.css">
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

            <form method="POST" action="<?= BASE_URL ?>/public/register" class="auth-form">
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
                    <label for="password">Mot de passe *</label>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Min. 8 caractères, 1 majuscule, 1 chiffre, 1 caractère spécial"
                            required
                        >
                        <button type="button" class="toggle-password" data-target="password">
                            <span class="show-icon">👁️</span>
                            <span class="hide-icon" style="display:none;">👁️‍🗨️</span>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirmer le mot de passe *</label>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            id="password_confirm" 
                            name="password_confirm" 
                            required
                        >
                        <button type="button" class="toggle-password" data-target="password_confirm">
                            <span class="show-icon">👁️</span>
                            <span class="hide-icon" style="display:none;">👁️‍🗨️</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">S'inscrire</button>
            </form>

            <div class="auth-footer">
                <p>Déjà un compte ? <a href="<?= BASE_URL ?>/public/login">Connectez-vous</a></p>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const showIcon = this.querySelector('.show-icon');
                const hideIcon = this.querySelector('.hide-icon');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    showIcon.style.display = 'none';
                    hideIcon.style.display = 'inline';
                } else {
                    input.type = 'password';
                    showIcon.style.display = 'inline';
                    hideIcon.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
