<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Admin.php';
require_once __DIR__ . '/../Models/Restaurant.php';
require_once __DIR__ . '/../Helpers/Validator.php';
require_once __DIR__ . '/../Models/PremiumFeature.php';
require_once __DIR__ . '/../Models/BillingCycle.php';

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
     * Calcule le montant prorata en centimes pour Stripe
     */
    private function calculateProrataAmountCents($priceMonthlyEuros)
    {
        $now = new DateTime('today');
        $daysInMonth = (int)$now->format('t');
        $currentDay = (int)$now->format('j');
        $targetMonth = $currentDay > 15 ? (int)$now->format('m') + 1 : (int)$now->format('m');
        $targetDate = new DateTime($now->format('Y') . '-' . $targetMonth . '-15');
        $daysRemaining = (int)$targetDate->diff($now)->days;
        $prorata = round(($daysRemaining / $daysInMonth) * $priceMonthlyEuros, 2);
        return (int)round($prorata * 100);
    }

    /**
     * Point d'entrée principal pour créer une session Stripe Checkout
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifier si c'est un checkout basique + premium ou juste premium
            if (isset($_POST['include_basique']) && $_POST['include_basique'] === '1') {
                $this->createBasiqueWithPremiumCheckout();
            } elseif (!empty($_POST['features'])) {
                $this->createPremiumFeaturesCheckout();
            } else {
                $this->createBasiqueCheckout();
            }
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
            'line_items[0][price_data][product_data][name]'        => 'Abonnement Basique MenuMiam (prorata)',
            'line_items[0][price_data][product_data][description]' => 'Site vitrine restaurant – prorata jusqu\'au 15 du mois',
            'line_items[0][price_data][unit_amount]'               => $this->calculateProrataAmountCents(9),
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
     * Checkout combiné : Abonnement Basique + Options Premium
     */
    private function createBasiqueWithPremiumCheckout()
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

        $stmt = $this->pdo->prepare("SELECT email FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $adminEmail = $stmt->fetchColumn() ?: '';

        $successUrl = SITE_URL . '/?page=stripe-success&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = SITE_URL . '/?page=settings&section=premium';

        // Calculer le montant total (basique + premium)
        $totalAmount = 900; // 9€ pour l'abonnement basique
        foreach ($selected as $feature) {
            $totalAmount += $allowed[$feature]['amount'];
        }

        // Créer les line items avec prorata
        $basiqueProrataAmount = $this->calculateProrataAmountCents(9);
        $lineItems = [
            [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $basiqueProrataAmount,
                    'product_data' => [
                        'name' => 'Abonnement Basique MenuMiam (prorata)',
                        'description' => 'Prorata jusqu\'au 15 du mois – puis 9€/mois',
                    ],
                ],
                'quantity' => 1,
            ]
        ];

        // Ajouter les options premium sélectionnées au prorata
        foreach ($selected as $feature) {
            $priceEuros = $allowed[$feature]['amount'] / 100;
            $prorataAmount = $this->calculateProrataAmountCents($priceEuros);
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $prorataAmount,
                    'product_data' => [
                        'name' => $allowed[$feature]['name'] . ' (prorata)',
                        'description' => 'Prorata jusqu\'au 15 du mois – puis ' . $priceEuros . '€/mois',
                    ],
                ],
                'quantity' => 1,
            ];
        }

        // Construire les données pour Stripe avec le bon format
        $postData = [
            'payment_method_types[0]' => 'card',
            'mode' => 'payment',
            'customer_email' => $adminEmail,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata[type]' => 'basique_premium',
            'metadata[admin_id]' => $_SESSION['admin_id'],
            'metadata[features]' => json_encode($selected),
        ];

        // Ajouter les line items au bon format pour http_build_query
        foreach ($lineItems as $index => $item) {
            $postData["line_items[$index][price_data][currency]"] = $item['price_data']['currency'];
            $postData["line_items[$index][price_data][unit_amount]"] = $item['price_data']['unit_amount'];
            $postData["line_items[$index][price_data][product_data][name]"] = $item['price_data']['product_data']['name'];
            $postData["line_items[$index][price_data][product_data][description]"] = $item['price_data']['product_data']['description'];
            $postData["line_items[$index][quantity]"] = $item['quantity'];
        }

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

        $prorataTotal = 0;
        foreach ($selected as $i => $key) {
            $priceEuros = $allowed[$key]['amount'] / 100;
            $prorataAmount = $this->calculateProrataAmountCents($priceEuros);
            $prorataTotal += $prorataAmount;
            $params["line_items[{$i}][price_data][currency]"]                  = 'eur';
            $params["line_items[{$i}][price_data][product_data][name]"]        = 'MenuMiam — ' . $allowed[$key]['name'] . ' (prorata)';
            $params["line_items[{$i}][price_data][product_data][description]"] = 'Prorata jusqu\'au 15 du mois – puis ' . $priceEuros . '€/mois';
            $params["line_items[{$i}][price_data][unit_amount]"]               = $prorataAmount;
            $params["line_items[{$i}][quantity]"]                              = 1;
        }

        $monthlyTotal = 9 + (int)array_sum(array_map(fn($k) => $allowed[$k]['amount'] / 100, $selected));
        $params['custom_text[submit][message]'] =
            'Prorata ce mois : ' . number_format($prorataTotal / 100, 2) . '€. '
            . 'À partir du mois prochain : ' . $monthlyTotal . '€/mois (basique inclus).';

        $this->redirectToStripe(http_build_query($params));
    }

    /**
     * Résilie un abonnement basique ou une option premium
     */
    public function cancelSubscription()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Pour les requêtes AJAX, retourner JSON
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
                exit;
            }
            header('Location: ?page=settings&section=premium');
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            // Pour les requêtes AJAX, retourner JSON
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
                exit;
            }
            $this->addErrorMessage('Token CSRF invalide.');
            header('Location: ?page=settings&section=premium');
            exit;
        }

        // Gérer la résiliation groupée
        if (isset($_POST['bulk_cancel']) && $_POST['bulk_cancel'] === '1') {
            return $this->cancelBulkSubscriptions();
        }

        $type = $_POST['type'] ?? '';

        if ($type === 'basique') {
            try {
                $stmt = $this->pdo->prepare(
                    "UPDATE client_subscriptions SET status = 'cancelled' WHERE admin_id = ?"
                );
                $stmt->execute([$_SESSION['admin_id']]);

                $pf = new PremiumFeature($this->pdo);
                foreach (array_keys($pf->getAvailableFeatures()) as $key) {
                    $pf->disable($_SESSION['admin_id'], $key);
                }
                $message = 'Votre abonnement Basique et toutes les options premium ont été résiliés.';
                $_SESSION['pendingToast'] = json_encode([
                    'message' => $message,
                    'type' => 'success'
                ]);
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
                $pf = new PremiumFeature($this->pdo);
                $pf->disable($_SESSION['admin_id'], $featureKey);
                $name = $pf->getAvailableFeatures()[$featureKey]['name'] ?? $featureKey;
                $message = 'L\'option « ' . $name . ' » a été désactivée.';
                $_SESSION['pendingToast'] = json_encode([
                    'message' => $message,
                    'type' => 'success'
                ]);
            } catch (Exception $e) {
                error_log('[Cancel] Premium feature: ' . $e->getMessage());
                $this->addErrorMessage('Erreur lors de la résiliation. Contactez le support.');
            }
        }

        // Redirection vers le total de l'abonnement
        header('Location: ?page=settings&section=subscriptions#subscription-total');
        exit;
    }

    /**
     * Gère la résiliation groupée d'abonnements
     */
    private function cancelBulkSubscriptions()
    {
        try {
            $this->pdo->beginTransaction();

            $cancelledBasique = false;
            $cancelledPremium = [];

            // Vérifier si l'abonnement basique doit être résilié
            if (isset($_POST['basique_cancel']) && $_POST['basique_cancel'] === '1') {
                $stmt = $this->pdo->prepare(
                    "UPDATE client_subscriptions SET status = 'cancelled' WHERE admin_id = ?"
                );
                $stmt->execute([$_SESSION['admin_id']]);
                $cancelledBasique = true;

                // Résilier automatiquement toutes les options premium
                $pf = new PremiumFeature($this->pdo);
                foreach (array_keys($pf->getAvailableFeatures()) as $key) {
                    $pf->disable($_SESSION['admin_id'], $key);
                    $feature = $pf->getAvailableFeatures()[$key];
                    $cancelledPremium[] = $feature['name'] ?? $key;
                }
            }

            // Résilier les options premium individuelles
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'premium_cancel_') === 0 && $value) {
                    $featureName = $value;
                    
                    // Trouver la clé de la fonctionnalité à partir du nom
                    $pf = new PremiumFeature($this->pdo);
                    $availableFeatures = $pf->getAvailableFeatures();
                    
                    $featureKey = null;
                    foreach ($availableFeatures as $key => $feature) {
                        if ($feature['name'] === $featureName) {
                            $featureKey = $key;
                            break;
                        }
                    }
                    
                    if ($featureKey) {
                        $pf->disable($_SESSION['admin_id'], $featureKey);
                        $cancelledPremium[] = $this->translateFeatureName($featureKey);
                    }
                }
            }

            $this->pdo->commit();

            // Préparer le message de succès
            $message = '';
            if ($cancelledBasique) {
                $message = 'Votre abonnement Basique';
                if (!empty($cancelledPremium)) {
                    $message .= ' et les options premium suivantes ont été résiliés : ' . implode(', ', $cancelledPremium);
                } else {
                    $message .= ' a été résilié.';
                }
            } elseif (!empty($cancelledPremium)) {
                $message = 'Les options premium suivantes ont été résiliées : ' . implode(', ', $cancelledPremium);
            } else {
                $message = 'Aucun abonnement n\'a été résilié.';
            }

            // Pour AJAX, retourner le succès JSON
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => $message
                ]);
                exit;
            }
            
            // Pour les requêtes normales, utiliser le système de messages du site
            $this->addSuccessMessage($message);
            header('Location: ?page=settings&section=subscriptions#subscription-total');
            exit;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log('[Cancel Bulk] Error: ' . $e->getMessage());
            
            // Pour AJAX, retourner l'erreur JSON
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la résiliation groupée. Veuillez réessayer.'
                ]);
                exit;
            }
            
            // Pour les requêtes normales, utiliser le système de messages du site
            $this->addErrorMessage('Erreur lors de la résiliation groupée. Veuillez réessayer.');
            header('Location: ?page=settings&section=subscriptions#subscription-total');
            exit;
        }
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
            $names = implode(', ', array_map([$this, 'translateFeatureName'], $features));
            $message = 'Paiement confirmé ! Option(s) activée(s) : ' . $names . '.';
            // Stocker dans sessionStorage pour SweetAlert2
            $_SESSION['pendingToast'] = json_encode([
                'message' => $message,
                'type' => 'success'
            ]);
            header('Location: ?page=settings&section=subscriptions#subscription-total');
        } elseif ($type === 'basique_premium') {
            // Activer l'abonnement basique ET les options premium
            $features = json_decode($session['metadata']['features'] ?? '[]', true);
            $this->activateSubscription($_SESSION['admin_id'], $sessionId);
            $this->activatePremiumFeatures($_SESSION['admin_id'], $features);
            
            $names = implode(', ', array_map([$this, 'translateFeatureName'], $features));
            $message = 'Paiement confirmé ! Votre abonnement Basique est actif' . 
                ($names ? ' et option(s) premium activée(s) : ' . $names . '.' : ' !');
            // Stocker dans sessionStorage pour SweetAlert2
            $_SESSION['pendingToast'] = json_encode([
                'message' => $message,
                'type' => 'success'
            ]);
            header('Location: ?page=settings&section=subscriptions#subscription-total');
        } else {
            $this->activateSubscription($_SESSION['admin_id'], $sessionId);
            $message = 'Paiement confirmé ! Votre abonnement Basique est maintenant actif. Bienvenue sur MenuMiam !';
            // Stocker dans sessionStorage pour SweetAlert2
            $_SESSION['pendingToast'] = json_encode([
                'message' => $message,
                'type' => 'success'
            ]);
            header('Location: ?page=settings&section=subscriptions#subscription-total');
        }
        exit;
    }

    /**
     * Requête générique à l'API Stripe
     * @param string $method   GET ou POST
     * @param string $endpoint Ex : /v1/checkout/sessions
     * @param string|array $body     Données pour POST (array pour JSON, string pour form-urlencoded)
     * @return array|null      Réponse décodée ou null en cas d'erreur cURL
     */
    private function stripeRequest($method, $endpoint, $body = '')
    {
        $url = 'https://api.stripe.com' . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':');
        
        // Forcer application/x-www-form-urlencoded pour Stripe
        $headers = ['Content-Type: application/x-www-form-urlencoded'];
        if (is_array($body)) {
            // Convertir array en form-urlencoded
            $body = http_build_query($body);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

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
     * Webhook Stripe : reçoit les notifications de paiement serveur-à-serveur
     * 
     * Stripe envoie un POST à cette URL quand un événement se produit (ex: paiement réussi).
     * Contrairement à handleSuccess() qui dépend du retour du navigateur,
     * le webhook est appelé directement par les serveurs Stripe, même si l'utilisateur
     * ferme son navigateur après le paiement.
     * 
     * Configuration requise :
     * 1. Créer un webhook dans le Dashboard Stripe → Developers → Webhooks
     * 2. URL : https://votre-domaine.com/?page=stripe-webhook
     * 3. Événement à écouter : checkout.session.completed
     * 4. Copier le "Signing secret" (whsec_...) dans config.php → STRIPE_WEBHOOK_SECRET
     */
    public function handleWebhook()
    {
        // 1. Lire le corps brut de la requête (pas $_POST, Stripe envoie du JSON)
        $payload = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        // 2. Vérifier la signature pour s'assurer que c'est bien Stripe qui envoie
        if (defined('STRIPE_WEBHOOK_SECRET') && STRIPE_WEBHOOK_SECRET !== '') {
            $event = $this->verifyWebhookSignature($payload, $sigHeader, STRIPE_WEBHOOK_SECRET);
            if (!$event) {
                http_response_code(400);
                echo json_encode(['error' => 'Signature invalide']);
                exit;
            }
        } else {
            // Sans secret configuré, on parse quand même (dev uniquement)
            $event = json_decode($payload, true);
            if (!$event) {
                http_response_code(400);
                echo json_encode(['error' => 'Payload JSON invalide']);
                exit;
            }
            error_log('[Stripe Webhook] ATTENTION : STRIPE_WEBHOOK_SECRET non configuré, signature non vérifiée');
        }

        // 3. Traiter l'événement selon son type
        $type = $event['type'] ?? '';

        if ($type === 'checkout.session.completed') {
            $session = $event['data']['object'] ?? [];
            $this->processCompletedCheckout($session);
        }
        // Autres événements possibles à gérer plus tard :
        // - payment_intent.payment_failed → notifier l'admin
        // - customer.subscription.deleted → désactiver l'abonnement

        // 4. Toujours répondre 200 à Stripe (sinon il réessaie)
        http_response_code(200);
        echo json_encode(['received' => true]);
        exit;
    }

    /**
     * Vérifie la signature du webhook Stripe (HMAC SHA-256)
     * 
     * Stripe signe chaque webhook avec un secret partagé. Cela garantit que
     * la requête vient bien de Stripe et n'a pas été falsifiée.
     *
     * @param string $payload    Corps brut de la requête
     * @param string $sigHeader  Contenu du header Stripe-Signature
     * @param string $secret     Clé secrète du webhook (whsec_...)
     * @return array|null        Événement décodé ou null si signature invalide
     */
    private function verifyWebhookSignature(string $payload, string $sigHeader, string $secret): ?array
    {
        // Le header contient : t=timestamp,v1=signature
        $parts = [];
        foreach (explode(',', $sigHeader) as $part) {
            [$key, $value] = explode('=', $part, 2);
            $parts[$key] = $value;
        }

        $timestamp = $parts['t'] ?? '';
        $signature = $parts['v1'] ?? '';

        if (empty($timestamp) || empty($signature)) {
            error_log('[Stripe Webhook] Header Stripe-Signature malformé');
            return null;
        }

        // Protection contre les attaques par rejeu (tolérance : 5 minutes)
        if (abs(time() - (int)$timestamp) > 300) {
            error_log('[Stripe Webhook] Timestamp trop ancien (possible replay attack)');
            return null;
        }

        // Recalculer la signature attendue
        $signedPayload = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

        // Comparaison en temps constant (anti timing attack)
        if (!hash_equals($expectedSignature, $signature)) {
            error_log('[Stripe Webhook] Signature invalide');
            return null;
        }

        return json_decode($payload, true);
    }

    /**
     * Traite un checkout.session.completed reçu via webhook
     * 
     * C'est ici que l'activation réelle de l'abonnement se fait.
     * La méthode est idempotente : appeler 2 fois avec la même session
     * n'active pas l'abonnement 2 fois.
     *
     * @param array $session Objet session Stripe
     */
    private function processCompletedCheckout(array $session)
    {
        $paymentStatus = $session['payment_status'] ?? '';
        if ($paymentStatus !== 'paid') {
            error_log('[Stripe Webhook] Session non payée, status: ' . $paymentStatus);
            return;
        }

        $adminId = (int)($session['metadata']['admin_id'] ?? 0);
        $type = $session['metadata']['type'] ?? 'basique';
        $sessionId = $session['id'] ?? '';

        if ($adminId <= 0) {
            error_log('[Stripe Webhook] admin_id invalide dans metadata');
            return;
        }

        // Vérifier si cette session a déjà été traitée (idempotence)
        try {
            $stmt = $this->pdo->prepare(
                "SELECT status, notes FROM client_subscriptions WHERE admin_id = ?"
            );
            $stmt->execute([$adminId]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing && str_contains($existing['notes'] ?? '', $sessionId)) {
                error_log('[Stripe Webhook] Session déjà traitée: ' . $sessionId);
                return;
            }
        } catch (Exception $e) {
            // Table peut ne pas exister en dev
        }

        // Activer selon le type de paiement
        if ($type === 'basique' || $type === 'basique_premium') {
            $this->activateSubscription($adminId, $sessionId);
            error_log('[Stripe Webhook] Abonnement basique activé pour admin #' . $adminId);
        }

        if ($type === 'premium' || $type === 'basique_premium') {
            $features = json_decode($session['metadata']['features'] ?? '[]', true);
            if (!empty($features)) {
                $this->activatePremiumFeatures($adminId, $features);
                error_log('[Stripe Webhook] Options premium activées pour admin #' . $adminId . ': ' . implode(', ', $features));
            }
        }
    }

    /**
     * Active (ou réactive) l'abonnement basique d'un admin
     */
    private function activateSubscription($adminId, $stripeSessionId)
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE client_subscriptions
                 SET status = 'active', started_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL 1 MONTH), notes = ?
                 WHERE admin_id = ?"
            );
            $note = 'Stripe session: ' . $stripeSessionId;
            $stmt->execute([$note, $adminId]);

            // Si aucune ligne existait, on l'insère
            if ($stmt->rowCount() === 0) {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO client_subscriptions
                        (admin_id, plan_type, status, price_per_month, started_at, expires_at, notes)
                     VALUES (?, 'basique', 'active', 9.00, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), ?)"
                );
                $stmt->execute([$adminId, $note]);
            }
        } catch (Exception $e) {
            error_log('[Stripe] Erreur activation abonnement: ' . $e->getMessage());
        }
    }

    /**
     * Traduit les clés de fonctionnalités en noms français
     */
    private function translateFeatureName($featureKey)
    {
        $translations = [
            'google_reviews' => 'Avis Google',
            'advanced_analytics' => 'Statistiques avancées',
            'online_booking' => 'Réservations en ligne',
            'delivery_integration' => 'Intégration livraison'
        ];
        
        return $translations[$featureKey] ?? ucfirst(str_replace('_', ' ', $featureKey));
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
            $premiumFeature = new PremiumFeature($this->pdo);
            $billingCycle = new BillingCycle($this->pdo);
            
            foreach ($features as $featureKey) {
                $premiumFeature->enable($adminId, $featureKey);
                
                // Récupérer le prix mensuel de la feature
                $availableFeatures = $premiumFeature->getAvailableFeatures();
                $priceMonthly = $availableFeatures[$featureKey]['price_monthly'] ?? 0;
                
                // Mettre à jour le cycle de facturation avec prorata
                $billingCycle->updateBillingForPremiumFeature($adminId, $featureKey, $priceMonthly);
            }
        } catch (Exception $e) {
            error_log('[Stripe] Erreur activation options premium: ' . $e->getMessage());
        }
    }
}
