<?php

require_once __DIR__ . '/BaseController.php';

/**
 * Contrôleur Stripe : gère le paiement d'activation de l'abonnement Basique
 * Utilise l'API Stripe via cURL (pas de bibliothèque externe requise)
 *
 * TEST : carte fictive Stripe → 4242 4242 4242 4242 / exp 12/26 / CVV 123
 * Clés de test : https://dashboard.stripe.com/test/apikeys
 */
class StripeController extends BaseController
{
    /**
     * Point d'entrée : redirige vers le checkout Basique (GET) ou premium (POST)
     */
    public function createCheckout()
    {
        $this->requireLogin();
        $this->blockIfDemo("Le paiement n'est pas disponible en mode démonstration.");

        if (STRIPE_SECRET_KEY === 'sk_test_VOTRE_CLE_SECRETE_ICI') {
            $this->addErrorMessage(
                'Stripe n\'est pas encore configuré. Ajoutez vos clés API dans config.php.'
            );
            header('Location: ?page=settings&section=premium');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['features'])) {
            $this->createPremiumFeaturesCheckout();
        } else {
            $this->createBasiqueCheckout();
        }
    }

    /**
     * Checkout pour l'abonnement Basique (9€)
     */
    private function createBasiqueCheckout()
    {
        $stmt = $this->pdo->prepare("SELECT email FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $adminEmail = $stmt->fetchColumn() ?: '';

        $successUrl = SITE_URL . '/?page=stripe-success&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = SITE_URL . '/?page=settings&section=premium';

        $postData = http_build_query([
            'payment_method_types[0]'                              => 'card',
            'line_items[0][price_data][currency]'                  => 'eur',
            'line_items[0][price_data][product_data][name]'        => 'Abonnement Basique MenuMiam',
            'line_items[0][price_data][product_data][description]' => 'Site vitrine restaurant – accès complet',
            'line_items[0][price_data][unit_amount]'               => 900,
            'line_items[0][quantity]'                              => 1,
            'mode'                                                 => 'payment',
            'customer_email'                                       => $adminEmail,
            'success_url'                                          => $successUrl,
            'cancel_url'                                           => $cancelUrl,
            'metadata[admin_id]'                                   => $_SESSION['admin_id'],
            'metadata[type]'                                       => 'basique',
        ]);

        $this->redirectToStripe($postData);
    }

    /**
     * Checkout pour une ou plusieurs options premium à la carte
     */
    private function createPremiumFeaturesCheckout()
    {
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->addErrorMessage('Token CSRF invalide.');
            header('Location: ?page=settings&section=premium');
            exit;
        }

        $allowed = [
            'google_reviews'       => ['name' => 'Avis Google',              'amount' => 500],
            'advanced_analytics'   => ['name' => 'Statistiques avancées',    'amount' => 500],
            'online_booking'       => ['name' => 'Réservations en ligne',     'amount' => 800],
            'delivery_integration' => ['name' => 'Intégration livraison',     'amount' => 700],
        ];

        $selected = array_filter((array)$_POST['features'], fn($f) => isset($allowed[$f]));
        $selected = array_values($selected);

        if (empty($selected)) {
            $this->addErrorMessage('Veuillez sélectionner au moins une option premium.');
            header('Location: ?page=settings&section=premium');
            exit;
        }

        $stmt = $this->pdo->prepare("SELECT email FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $adminEmail = $stmt->fetchColumn() ?: '';

        $successUrl = SITE_URL . '/?page=stripe-success&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = SITE_URL . '/?page=settings&section=premium';

        $params = [
            'payment_method_types[0]' => 'card',
            'mode'                    => 'payment',
            'customer_email'          => $adminEmail,
            'success_url'             => $successUrl,
            'cancel_url'              => $cancelUrl,
            'metadata[admin_id]'      => $_SESSION['admin_id'],
            'metadata[type]'          => 'premium',
            'metadata[features]'      => json_encode($selected),
        ];

        foreach ($selected as $i => $key) {
            $params["line_items[{$i}][price_data][currency]"]                  = 'eur';
            $params["line_items[{$i}][price_data][product_data][name]"]        = 'MenuMiam — ' . $allowed[$key]['name'];
            $params["line_items[{$i}][price_data][unit_amount]"]               = $allowed[$key]['amount'];
            $params["line_items[{$i}][quantity]"]                              = 1;
        }

        $monthlyTotal = 9 + (int)array_sum(array_map(fn($k) => $allowed[$k]['amount'] / 100, $selected));
        $params['custom_text[submit][message]'] =
            'Ces options s\'ajoutent à votre Abonnement Basique MenuMiam (9€/mois, déjà actif). '
            . 'Total mensuel estimé : ' . $monthlyTotal . '€/mois.';

        $this->redirectToStripe(http_build_query($params));
    }

    /**
     * Résilie un abonnement basique ou une option premium
     */
    public function cancelSubscription()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=settings&section=premium');
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->addErrorMessage('Token CSRF invalide.');
            header('Location: ?page=settings&section=premium');
            exit;
        }

        $type = $_POST['type'] ?? '';

        if ($type === 'basique') {
            try {
                $stmt = $this->pdo->prepare(
                    "UPDATE client_subscriptions SET status = 'cancelled' WHERE admin_id = ?"
                );
                $stmt->execute([$_SESSION['admin_id']]);

                require_once __DIR__ . '/../Models/PremiumFeature.php';
                $pf = new PremiumFeature($this->pdo);
                foreach (array_keys($pf->getAvailableFeatures()) as $key) {
                    $pf->disable($_SESSION['admin_id'], $key);
                }
                $this->addSuccessMessage(
                    'Votre abonnement Basique et toutes les options premium ont été résiliés.'
                );
            } catch (Exception $e) {
                error_log('[Cancel] Basique: ' . $e->getMessage());
                $this->addErrorMessage('Erreur lors de la résiliation. Contactez le support.');
            }

        } elseif ($type === 'premium') {
            $allowed = ['google_reviews', 'advanced_analytics', 'online_booking', 'delivery_integration'];
            $featureKey = $_POST['feature'] ?? '';

            if (!in_array($featureKey, $allowed)) {
                $this->addErrorMessage('Option invalide.');
                header('Location: ?page=settings&section=premium');
                exit;
            }

            try {
                require_once __DIR__ . '/../Models/PremiumFeature.php';
                $pf = new PremiumFeature($this->pdo);
                $pf->disable($_SESSION['admin_id'], $featureKey);
                $name = $pf->getAvailableFeatures()[$featureKey]['name'] ?? $featureKey;
                $this->addSuccessMessage('L\'option « ' . $name . ' » a été désactivée.');
            } catch (Exception $e) {
                error_log('[Cancel] Premium feature: ' . $e->getMessage());
                $this->addErrorMessage('Erreur lors de la résiliation. Contactez le support.');
            }
        }

        header('Location: ?page=settings&section=premium');
        exit;
    }

    /**
     * Envoie la requête Stripe et redirige vers l'URL de paiement
     */
    private function redirectToStripe($postData)
    {
        $session = $this->stripeRequest('POST', '/v1/checkout/sessions', $postData);

        if (!$session || empty($session['url'])) {
            $errMsg = $session['error']['message'] ?? ($session['_curl_error'] ?? 'Erreur inconnue');
            error_log('[Stripe] createCheckout failed: ' . json_encode($session));
            $this->addErrorMessage('Impossible de créer la session de paiement : ' . $errMsg);
            header('Location: ?page=settings&section=premium');
            exit;
        }

        header('Location: ' . $session['url']);
        exit;
    }

    /**
     * Traite le retour de Stripe après paiement réussi
     */
    public function handleSuccess()
    {
        $this->requireLogin();

        $sessionId = trim($_GET['session_id'] ?? '');

        if (empty($sessionId)) {
            header('Location: ?page=dashboard');
            exit;
        }

        $session = $this->stripeRequest('GET', '/v1/checkout/sessions/' . urlencode($sessionId));

        if (!$session || ($session['payment_status'] ?? '') !== 'paid') {
            $this->addErrorMessage(
                'Paiement non confirmé. Si vous avez été débité, contactez contact@menumiam.fr.'
            );
            header('Location: ?page=settings&section=premium');
            exit;
        }

        $metaAdminId = (int)($session['metadata']['admin_id'] ?? 0);
        if ($metaAdminId !== (int)$_SESSION['admin_id']) {
            $this->addErrorMessage('Session de paiement invalide.');
            header('Location: ?page=settings&section=premium');
            exit;
        }

        $type = $session['metadata']['type'] ?? 'basique';

        if ($type === 'premium') {
            $features = json_decode($session['metadata']['features'] ?? '[]', true);
            $this->activatePremiumFeatures($_SESSION['admin_id'], $features);
            $names = implode(', ', array_map(fn($f) => ucfirst(str_replace('_', ' ', $f)), $features));
            $this->addSuccessMessage(
                'Paiement confirmé ! Option(s) activée(s) : ' . $names . '.'
            );
            header('Location: ?page=settings&section=premium');
        } else {
            $this->activateSubscription($_SESSION['admin_id'], $sessionId);
            $this->addSuccessMessage(
                'Paiement confirmé ! Votre abonnement Basique est maintenant actif. Bienvenue sur MenuMiam !'
            );
            header('Location: ?page=dashboard');
        }
        exit;
    }

    // ------------------------------------------------------------------
    // Helpers privés
    // ------------------------------------------------------------------

    /**
     * Effectue une requête vers l'API Stripe via cURL
     *
     * @param string $method   GET ou POST
     * @param string $endpoint Ex : /v1/checkout/sessions
     * @param string $body     Données encodées pour POST (http_build_query)
     * @return array|null      Réponse décodée ou null en cas d'erreur cURL
     */
    private function stripeRequest($method, $endpoint, $body = '')
    {
        $url = 'https://api.stripe.com' . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        // Certificat CA — pointe vers le bundle WAMP si disponible, sinon désactive la vérif en dev
        $caBundlePaths = [
            'C:/wamp64/bin/php/php8.5.0/extras/ssl/cacert.pem',
            'C:/wamp64/bin/php/php8.4.0/extras/ssl/cacert.pem',
            'C:/wamp64/bin/php/php8.3.0/extras/ssl/cacert.pem',
            'C:/wamp64/bin/php/php8.2.0/extras/ssl/cacert.pem',
        ];
        $caFound = false;
        foreach ($caBundlePaths as $caPath) {
            if (file_exists($caPath)) {
                curl_setopt($ch, CURLOPT_CAINFO, $caPath);
                $caFound = true;
                break;
            }
        }
        if (!$caFound) {
            // En dev local uniquement — ne jamais faire ça en production
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            error_log('[Stripe] cURL error: ' . $curlErr);
            return ['_curl_error' => $curlErr];
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            error_log('[Stripe] API error ' . $httpCode . ': ' . $response);
        }

        return $decoded;
    }

    /**
     * Active (ou réactive) l'abonnement basique d'un admin
     */
    private function activateSubscription($adminId, $stripeSessionId)
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE client_subscriptions
                 SET status = 'active', started_at = NOW(), notes = ?
                 WHERE admin_id = ?"
            );
            $note = 'Stripe session: ' . $stripeSessionId;
            $stmt->execute([$note, $adminId]);

            // Si aucune ligne existait, on l'insère
            if ($stmt->rowCount() === 0) {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO client_subscriptions
                        (admin_id, plan_type, status, price_per_month, started_at, notes)
                     VALUES (?, 'basique', 'active', 9.00, NOW(), ?)"
                );
                $stmt->execute([$adminId, $note]);
            }
        } catch (Exception $e) {
            error_log('[Stripe] Erreur activation abonnement: ' . $e->getMessage());
        }
    }

    /**
     * Active les options premium achetées via Stripe
     */
    private function activatePremiumFeatures($adminId, array $features)
    {
        if (empty($features)) {
            return;
        }
        try {
            require_once __DIR__ . '/../Models/PremiumFeature.php';
            $premiumFeature = new PremiumFeature($this->pdo);
            foreach ($features as $featureKey) {
                $premiumFeature->enable($adminId, $featureKey);
            }
        } catch (Exception $e) {
            error_log('[Stripe] Erreur activation options premium: ' . $e->getMessage());
        }
    }
}
