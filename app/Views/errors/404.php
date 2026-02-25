<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page introuvable — MenuMiam</title>
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        :root {
            --color-primary: #b45309;
            --color-primary-dark: #92400e;
            --color-text: #1c1917;
            --color-text-light: #57534e;
            --color-bg: #ffffff;
            --color-bg-warm: #fef7ed;
            --font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-family);
            background: var(--color-bg-warm);
            color: var(--color-text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
        }

        .error-container {
            text-align: center;
            max-width: 460px;
            width: 100%;
            padding: 48px 32px;
            background: var(--color-bg);
            border-radius: 20px;
            box-shadow: 0 20px 48px rgba(28, 25, 23, 0.12);
        }

        .error-code {
            font-size: clamp(5rem, 15vw, 8rem);
            font-weight: 700;
            color: var(--color-primary);
            line-height: 1;
            margin-bottom: 8px;
            letter-spacing: -0.04em;
        }

        .error-icon {
            font-size: 3rem;
            color: var(--color-text-light);
            margin-bottom: 20px;
        }

        .error-container h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .error-container p {
            color: var(--color-text-light);
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .error-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 32px;
            background: var(--color-primary);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .error-link:hover {
            background: var(--color-primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(180, 83, 9, 0.3);
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <div class="error-icon"><i class="fas fa-map-signs"></i></div>
        <h1>Page introuvable</h1>
        <p>La page que vous recherchez n'existe pas ou a été déplacée.</p>
        <a href="/" class="error-link"><i class="fas fa-home"></i> Retour à l'accueil</a>
    </div>
</body>

</html>
