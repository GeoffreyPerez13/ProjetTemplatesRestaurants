<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#b45309">
    <title><?= htmlspecialchars($restaurant->name) ?> — Restaurant</title>

    <?php
    // Construire la description SEO dynamique et riche
    $seoDescription = htmlspecialchars($restaurant->name);
    if ($contact && !empty($contact['adresse'])) {
        $seoDescription .= ', ' . htmlspecialchars($contact['adresse']);
    }
    $seoDescription .= ' — Découvrez notre carte, nos plats et nos services.';
    if ($carteMode === 'editable' && !empty($categories)) {
        $catNames = array_slice(array_map(function($c) { return $c['name']; }, $categories), 0, 3);
        $seoDescription .= ' ' . implode(', ', $catNames) . '…';
    }
    if ($contact && !empty($contact['telephone'])) {
        $seoDescription .= ' Réservation : ' . htmlspecialchars($contact['telephone']) . '.';
    }
    // Schéma de protocole réutilisable
    $baseScheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $baseHost = $baseScheme . '://' . $_SERVER['HTTP_HOST'];
    // URL canonique
    $canonicalUrl = $baseHost . $_SERVER['REQUEST_URI'];
    // Image OG : bannière > logo > fallback
    $ogImage = '';
    if ($banner && !empty($banner['url'])) {
        $ogImage = $baseHost . $banner['url'];
    } elseif ($logo && !empty($logo['url'])) {
        $ogImage = $baseHost . $logo['url'];
    }
    ?>

    <!-- SEO Meta -->
    <meta name="description" content="<?= $seoDescription ?>">
    <meta name="robots" content="<?= (!empty($isPreview) || (isset($siteOnline) && !$siteOnline)) ? 'noindex, nofollow' : 'index, follow' ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">

    <!-- Open Graph (Facebook, LinkedIn) -->
    <meta property="og:type" content="restaurant">
    <meta property="og:title" content="<?= htmlspecialchars($restaurant->name) ?>">
    <meta property="og:description" content="<?= $seoDescription ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <?php if ($ogImage): ?>
        <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
    <?php endif; ?>
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="MenuMiam">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($restaurant->name) ?>">
    <meta name="twitter:description" content="<?= $seoDescription ?>">
    <?php if ($ogImage): ?>
        <meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>">
    <?php endif; ?>

    <!-- Schema.org JSON-LD (Données structurées Restaurant enrichies) -->
    <?php
    // Construire le schéma Restaurant
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Restaurant',
        'name' => $restaurant->name,
        'url' => $canonicalUrl,
        'description' => strip_tags($seoDescription),
    ];

    // Images (logo + bannière)
    $schemaImages = [];
    if ($logo && !empty($logo['url'])) {
        $schemaImages[] = $baseHost . $logo['url'];
    }
    if ($banner && !empty($banner['url'])) {
        $schemaImages[] = $baseHost . $banner['url'];
    }
    if (!empty($schemaImages)) {
        $schema['image'] = count($schemaImages) === 1 ? $schemaImages[0] : $schemaImages;
    }

    // Adresse
    if ($contact && !empty($contact['adresse'])) {
        $schema['address'] = [
            '@type' => 'PostalAddress',
            'streetAddress' => $contact['adresse'],
            'addressCountry' => 'FR',
        ];
    }

    // Contact
    if ($contact && !empty($contact['telephone'])) {
        $schema['telephone'] = $contact['telephone'];
    }
    if ($contact && !empty($contact['email'])) {
        $schema['email'] = $contact['email'];
    }
    if ($contact && !empty($contact['horaires'])) {
        $schema['openingHours'] = $contact['horaires'];
    }

    // Moyens de paiement
    $paymentLabels = [];
    if (!empty($payments['payment_visa']) && $payments['payment_visa'] === '1') $paymentLabels[] = 'Visa';
    if (!empty($payments['payment_mastercard']) && $payments['payment_mastercard'] === '1') $paymentLabels[] = 'MasterCard';
    if (!empty($payments['payment_cb']) && $payments['payment_cb'] === '1') $paymentLabels[] = 'Carte bancaire';
    if (!empty($payments['payment_especes']) && $payments['payment_especes'] === '1') $paymentLabels[] = 'Espèces';
    if (!empty($payments['payment_cheques']) && $payments['payment_cheques'] === '1') $paymentLabels[] = 'Chèques';
    if (!empty($paymentLabels)) {
        $schema['paymentAccepted'] = implode(', ', $paymentLabels);
    }

    // Réseaux sociaux (sameAs)
    $sameAs = [];
    if (!empty($socials['social_facebook'])) $sameAs[] = $socials['social_facebook'];
    if (!empty($socials['social_instagram'])) $sameAs[] = $socials['social_instagram'];
    if (!empty($socials['social_x'])) $sameAs[] = $socials['social_x'];
    if (!empty($socials['social_tiktok'])) $sameAs[] = $socials['social_tiktok'];
    if (!empty($sameAs)) {
        $schema['sameAs'] = $sameAs;
    }

    // Services proposés
    $servesCuisine = [];
    if (!empty($services['service_sur_place']) && $services['service_sur_place'] === '1') $servesCuisine[] = 'Sur place';
    if (!empty($services['service_a_emporter']) && $services['service_a_emporter'] === '1') $servesCuisine[] = 'À emporter';
    if (!empty($services['service_livraison_etablissement']) && $services['service_livraison_etablissement'] === '1') $servesCuisine[] = 'Livraison';
    if (!empty($servesCuisine)) {
        $schema['servesCuisine'] = implode(', ', $servesCuisine);
    }

    // Menu structuré (mode éditable)
    if ($carteMode === 'editable' && !empty($categories)) {
        $menuSections = [];
        foreach ($categories as $cat) {
            $section = [
                '@type' => 'MenuSection',
                'name' => $cat['name'],
            ];
            if (!empty($cat['plats'])) {
                $items = [];
                foreach ($cat['plats'] as $plat) {
                    $item = [
                        '@type' => 'MenuItem',
                        'name' => $plat['name'],
                        'offers' => [
                            '@type' => 'Offer',
                            'price' => number_format((float)$plat['price'], 2, '.', ''),
                            'priceCurrency' => 'EUR',
                        ],
                    ];
                    if (!empty($plat['description'])) {
                        $item['description'] = $plat['description'];
                    }
                    $items[] = $item;
                }
                $section['hasMenuItem'] = $items;
            }
            $menuSections[] = $section;
        }
        $schema['hasMenu'] = [
            '@type' => 'Menu',
            'hasMenuSection' => $menuSections,
        ];
    }
    ?>
    <script type="application/ld+json">
    <?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>

    <!-- BreadcrumbList pour la navigation Google -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "MenuMiam",
                "item": <?= json_encode(SITE_URL) ?>
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": <?= json_encode($restaurant->name) ?>,
                "item": <?= json_encode($canonicalUrl) ?>
            }
        ]
    }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (in_array($templateName ?? 'classic', ['elegant', 'rose', 'bistro']) || ($layoutName ?? 'standard') === 'bistro'): ?>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/display/display.css">
    <?php if (($templateName ?? 'classic') !== 'classic'): ?>
        <link rel="stylesheet" href="/assets/css/display/template-<?= htmlspecialchars($templateName) ?>.css">
    <?php endif; ?>
    <?php if (($layoutName ?? 'standard') !== 'standard'): ?>
        <link rel="stylesheet" href="/assets/css/display/layout-<?= htmlspecialchars($layoutName) ?>.css">
    <?php endif; ?>
</head>
