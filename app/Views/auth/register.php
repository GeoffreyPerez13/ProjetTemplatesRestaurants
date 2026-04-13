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
                            required
                        >
                        <button type="button" class="toggle-password" data-target="password">
                            <span class="show-icon">👁</span>
                            <span class="hide-icon" style="display:none;">⦻</span>
                        </button>
                    </div>
                    <div class="password-requirements">
                        <div class="requirement" data-requirement="length">
                            <span class="requirement-icon"></span>
                            <span class="requirement-text">Au moins 8 caractères</span>
                        </div>
                        <div class="requirement" data-requirement="lowercase">
                            <span class="requirement-icon"></span>
                            <span class="requirement-text">Une minuscule</span>
                        </div>
                        <div class="requirement" data-requirement="uppercase">
                            <span class="requirement-icon"></span>
                            <span class="requirement-text">Une majuscule</span>
                        </div>
                        <div class="requirement" data-requirement="number">
                            <span class="requirement-icon"></span>
                            <span class="requirement-text">Un chiffre</span>
                        </div>
                        <div class="requirement" data-requirement="special">
                            <span class="requirement-icon"></span>
                            <span class="requirement-text">Un caractère spécial</span>
                        </div>
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
                            <span class="show-icon">👁</span>
                            <span class="hide-icon" style="display:none;">⦻</span>
                        </button>
                    </div>
                    <div class="password-match-feedback" id="password-match-feedback" style="display:none;">
                        <span class="match-icon"></span>
                        <span class="match-text">Les mots de passe correspondent</span>
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

        // Password validation in real-time
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirm');
        const matchFeedback = document.getElementById('password-match-feedback');

        // Validation rules
        const requirements = {
            length: (password) => password.length >= 8,
            lowercase: (password) => /[a-z]/.test(password),
            uppercase: (password) => /[A-Z]/.test(password),
            number: (password) => /[0-9]/.test(password),
            special: (password) => /[^a-zA-Z0-9]/.test(password)
        };

        // Update requirement indicators
        function updateRequirements(password) {
            Object.keys(requirements).forEach(key => {
                const requirementElement = document.querySelector(`[data-requirement="${key}"]`);
                if (requirements[key](password)) {
                    requirementElement.classList.add('valid');
                } else {
                    requirementElement.classList.remove('valid');
                }
            });
        }

        // Check password match
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const passwordConfirm = passwordConfirmInput.value;

            if (passwordConfirm.length === 0) {
                matchFeedback.style.display = 'none';
                return;
            }

            matchFeedback.style.display = 'flex';

            if (password === passwordConfirm) {
                matchFeedback.classList.remove('no-match');
                matchFeedback.classList.add('match');
                matchFeedback.querySelector('.match-text').textContent = 'Les mots de passe correspondent';
            } else {
                matchFeedback.classList.remove('match');
                matchFeedback.classList.add('no-match');
                matchFeedback.querySelector('.match-text').textContent = 'Les mots de passe ne correspondent pas';
            }
        }

        // Event listeners
        passwordInput.addEventListener('input', function() {
            updateRequirements(this.value);
            checkPasswordMatch();
        });

        passwordConfirmInput.addEventListener('input', checkPasswordMatch);
    </script>
</body>
</html>
