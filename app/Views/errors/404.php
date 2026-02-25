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
            --color-primary: #2563eb;
            --color-text: #1f2937;
            --color-text-light: #6b7280;
            --color-bg: #ffffff;
            --color-bg-alt: #f9fafb;
            --font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-family);
            background: var(--color-bg-alt);
            color: var(--color-text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .error-container {
            text-align: center;
            max-width: 500px;
            padding: 40px 20px;
        }

        .error-code {
            font-size: 8rem;
            font-weight: 700;
            color: var(--color-primary);
            line-height: 1;
            margin-bottom: 10px;
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
            display: inline-block;
            padding: 12px 28px;
            background: var(--color-primary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: filter 0.2s;
        }

        .error-link:hover {
            filter: brightness(0.9);
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
