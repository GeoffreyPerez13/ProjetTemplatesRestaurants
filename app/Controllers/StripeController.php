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
            // Vérifier le type de checkout
            if (!empty($_POST['pack_full'])) {
                $this->createPackFullCheckout();
            } elseif (isset($_POST['include_basique']) && $_POST['include_basique'] === '1') {
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
     * Checkout pour l'abonnement Basique (11,99€/mois)
     */
    private function createBasiqueCheckout()
    {
        $stmt = $this->pdo->prepare("SELECT email FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $adminEmail = $stmt->fetchColumn() ?: '';

        $successUrl = SITE_URL . '/?page=stripe-success&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = SITE_URL . '/?page=settings&section=premium';

        // Mode prorata réservé au SUPER_ADMIN
        $useProrata = isset($_SESSION['role']) && $_SESSION['role'] === 'SUPER_ADMIN' && !empty($_POST['use_prorata']);
        $amount = $useProrata ? $this->calculateProrataAmountCents(11.99) : 1199; // 11,99€
        $productName = $useProrata ? 'Abonnement Basique MenuCraft (prorata)' : 'Abonnement Basique MenuCraft — 1 mois';
        $productDesc = $useProrata ? 'Site vitrine restaurant – prorata jusqu\'au 15 du mois' : 'Site vitrine restaurant – abonnement mensuel';

        $postData = http_build_query([
            'payment_method_types[0]'                              => 'card',
            'line_items[0][price_data][currency]'                  => 'eur',
            'line_items[0][price_data][product_data][name]'        => $productName,
            'line_items[0][price_data][product_data][description]' => $productDesc,
            'line_items[0][price_data][unit_amount]'               => $amount,
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
            'google_reviews'       => ['name' => 'Avis Google',              'amount' => 399],
            'advanced_analytics'   => ['name' => 'Statistiques avancées',    'amount' => 399],
            'online_booking'       => ['name' => 'Réservations en ligne',     'amount' => 1099],
            'delivery_integration' => ['name' => 'Intégration livraison',     'amount' => 399],
        ];

        $selected = array_filter((array)$_POST['features'], fn($f) => isset($allowed[$f]));
        $selected = array_values($selected);

        $stmt = $this->pdo->prepare("SELECT email FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $adminEmail = $stmt->fetchColumn() ?: '';

        $successUrl = SITE_URL . '/?page=stripe-success&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = SITE_URL . '/?page=settings&section=premium';

        // Mode prorata réservé au SUPER_ADMIN
        $useProrata = isset($_SESSION['role']) && $_SESSION['role'] === 'SUPER_ADMIN' && !empty($_POST['use_prorata']);

        // Calculer le montant total (basique + premium)
        $totalAmount = 1199; // 11,99€ pour l'abonnement basique
        foreach ($selected as $feature) {
            $totalAmount += $allowed[$feature]['amount'];
        }

        // Créer les line items
        $basiqueAmount = $useProrata ? $this->calculateProrataAmountCents(11.99) : 1199;
        $lineItems = [
            [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $basiqueAmount,
                    'product_data' => [
                        'name' => 'Abonnement Basique MenuCraft — 1 mois',
                        'description' => 'Site vitrine restaurant – 11,99€/mois',
                    ],
                ],
                'quantity' => 1,
            ]
        ];

        // Ajouter les options premium sélectionnées
        foreach ($selected as $feature) {
            $priceEuros = $allowed[$feature]['amount'] / 100;
            $featureAmount = $useProrata ? $this->calculateProrataAmountCents($priceEuros) : $allowed[$feature]['amount'];
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $featureAmount,
                    'product_data' => [
                        'name' => $allowed[$feature]['name'] . ' — 1 mois',
                        'description' => number_format($priceEuros, 2, ',', '') . '€/mois',
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
            'google_reviews'       => ['name' => 'Avis Google',              'amount' => 399],
            'advanced_analytics'   => ['name' => 'Statistiques avancées',    'amount' => 399],
            'online_booking'       => ['name' => 'Réservations en ligne',     'amount' => 1099],
            'delivery_integration' => ['name' => 'Intégration livraison',     'amount' => 399],
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

        // Mode prorata réservé au SUPER_ADMIN
        $useProrata = isset($_SESSION['role']) && $_SESSION['role'] === 'SUPER_ADMIN' && !empty($_POST['use_prorata']);

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

        $monthlyTotal = 0;
        foreach ($selected as $i => $key) {
            $priceEuros = $allowed[$key]['amount'] / 100;
            $featureAmount = $useProrata ? $this->calculateProrataAmountCents($priceEuros) : $allowed[$key]['amount'];
            $monthlyTotal += $priceEuros;
            $params["line_items[{$i}][price_data][currency]"]                  = 'eur';
            $params["line_items[{$i}][price_data][product_data][name]"]        = 'MenuCraft — ' . $allowed[$key]['name'] . ' — 1 mois';
            $params["line_items[{$i}][price_data][product_data][description]"] = number_format($priceEuros, 2, ',', '') . '€/mois';
            $params["line_items[{$i}][price_data][unit_amount]"]               = $featureAmount;
            $params["line_items[{$i}][quantity]"]                              = 1;
        }

        $params['custom_text[submit][message]'] = 'Total : ' . number_format($monthlyTotal, 2, ',', '') . '€ pour 1 mois.';

        $this->redirectToStripe(http_build_query($params));
    }

    /**
     * Checkout pour le Pack Full (Basique + toutes les options premium)
     * Durées : 1 mois, 3 mois, 1 an
     */
    private function createPackFullCheckout()
    {
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->addErrorMessage('Token CSRF invalide.');
            header('Location: ?page=settings&section=premium');
            exit;
        }

        $premiumFeature = new PremiumFeature($this->pdo);
        $packFull = $premiumFeature->getPackFull();

        // Durée sélectionnée
        $duration = $_POST['pack_duration'] ?? '1_month';
        if (!isset($packFull['prices'][$duration])) {
            $duration = '1_month';
        }

        $priceInfo = $packFull['prices'][$duration];
        $pricePerMonth = $priceInfo['price'];
        $durationMonths = $priceInfo['duration_months'];
        $totalAmount = isset($priceInfo['total']) ? (int)round($priceInfo['total'] * 100) : (int)round($pricePerMonth * 100);
        $durationLabel = $priceInfo['label'];

        $stmt = $this->pdo->prepare("SELECT email FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $adminEmail = $stmt->fetchColumn() ?: '';

        $successUrl = SITE_URL . '/?page=stripe-success&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = SITE_URL . '/?page=settings&section=premium';

        // Description du pack
        $savingsPercent = round((1 - ($pricePerMonth / $packFull['individual_total'])) * 100);
        $productDesc = 'Basique + Avis Google + Stats + Réservations + Livraison — '
            . number_format($pricePerMonth, 2, ',', '') . '€/mois'
            . ($savingsPercent > 0 ? ' (-' . $savingsPercent . '%)' : '');

        $params = [
            'payment_method_types[0]' => 'card',
            'mode'                    => 'payment',
            'customer_email'          => $adminEmail,
            'success_url'             => $successUrl,
            'cancel_url'              => $cancelUrl,
            'metadata[admin_id]'      => $_SESSION['admin_id'],
            'metadata[type]'          => 'pack_full',
            'metadata[duration]'      => $duration,
            'metadata[features]'      => json_encode($packFull['includes']),
            'line_items[0][price_data][currency]'                  => 'eur',
            'line_items[0][price_data][unit_amount]'               => $totalAmount,
            'line_items[0][price_data][product_data][name]'        => 'MenuCraft — Pack Full — ' . $durationLabel,
            'line_items[0][price_data][product_data][description]' => $productDesc,
            'line_items[0][quantity]'                              => 1,
            'custom_text[submit][message]' => 'Pack Full ' . $durationLabel . ' : '
                . number_format($totalAmount / 100, 2, ',', '') . '€'
                . ($durationMonths > 1 ? ' (' . number_format($pricePerMonth, 2, ',', '') . '€/mois)' : ''),
        ];

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
                // Vérifier le plan actuel et la date d'expiration
                $stmtCheck = $this->pdo->prepare("SELECT plan_type, expires_at FROM client_subscriptions WHERE admin_id = ? LIMIT 1");
                $stmtCheck->execute([$_SESSION['admin_id']]);
                $sub = $stmtCheck->fetch(\PDO::FETCH_ASSOC);
                $currentPlan = $sub['plan_type'] ?? 'basique';
                $expiresAt = $sub['expires_at'] ?? null;

                // Marquer comme résilié mais garder l'accès jusqu'à expires_at
                $stmt = $this->pdo->prepare(
                    "UPDATE client_subscriptions SET status = 'cancelled', cancelled_at = NOW() WHERE admin_id = ?"
                );
                $stmt->execute([$_SESSION['admin_id']]);

                // Formater la date de fin d'accès
                $endDate = $expiresAt ? (new \DateTime($expiresAt))->format('d/m/Y') : null;
                $message = ($currentPlan === 'pack_full')
                    ? 'Votre Pack Full a été résilié.'
                    : 'Votre abonnement Basique a été résilié.';
                if ($endDate) {
                    $message .= " Vous conservez l'accès jusqu'au $endDate.";
                }

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
                // Marquer comme résilié mais garder l'accès jusqu'à expires_at
                $pf->markCancelled($_SESSION['admin_id'], $featureKey);
                $name = $pf->getAvailableFeatures()[$featureKey]['name'] ?? $featureKey;

                // Chercher la date d'expiration de la feature
                $stmtExp = $this->pdo->prepare("SELECT expires_at FROM premium_features WHERE admin_id = ? AND feature_name = ?");
                $stmtExp->execute([$_SESSION['admin_id'], $featureKey]);
                $featureExpires = $stmtExp->fetchColumn();
                $endDate = $featureExpires ? (new \DateTime($featureExpires))->format('d/m/Y') : null;

                $message = 'L\'option « ' . $name . ' » a été résiliée.';
                if ($endDate) {
                    $message .= " Accès maintenu jusqu'au $endDate.";
                }
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
     * Réactive un abonnement résilié (annule la résiliation pendant la période de grâce)
     */
    public function reactivateSubscription()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?page=settings&section=subscriptions');
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->addErrorMessage('Token CSRF invalide.');
            header('Location: ?page=settings&section=subscriptions');
            exit;
        }

        try {
            // Vérifier que l'abonnement est bien résilié et encore en période de grâce
            $stmt = $this->pdo->prepare("
                SELECT id, plan_type FROM client_subscriptions 
                WHERE admin_id = ? AND status = 'cancelled' AND expires_at > NOW()
            ");
            $stmt->execute([$_SESSION['admin_id']]);
            $sub = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$sub) {
                $this->addErrorMessage('Aucun abonnement résilié à réactiver.');
                header('Location: ?page=settings&section=subscriptions');
                exit;
            }

            // Réactiver : remettre status = active, supprimer cancelled_at
            $update = $this->pdo->prepare("
                UPDATE client_subscriptions SET status = 'active', cancelled_at = NULL WHERE admin_id = ?
            ");
            $update->execute([$_SESSION['admin_id']]);

            // Supprimer cancelled_at sur les premium_features aussi
            $updatePf = $this->pdo->prepare("
                UPDATE premium_features SET cancelled_at = NULL WHERE admin_id = ? AND is_active = 1
            ");
            $updatePf->execute([$_SESSION['admin_id']]);

            $planLabel = $sub['plan_type'] === 'pack_full' ? 'Pack Full' : 'Abonnement Basique';
            $_SESSION['pendingToast'] = json_encode([
                'message' => $planLabel . ' réactivé avec succès !',
                'type' => 'success'
            ]);
        } catch (Exception $e) {
            error_log('[Reactivate] Erreur: ' . $e->getMessage());
            $this->addErrorMessage('Erreur lors de la réactivation. Contactez le support.');
        }

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
                'Paiement non confirmé. Si vous avez été débité, contactez contact.menucraft@gmail.com.'
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

        if ($type === 'pack_full') {
            $features = json_decode($session['metadata']['features'] ?? '[]', true);
            $duration = $session['metadata']['duration'] ?? '1_month';
            $durationMonths = $this->getDurationMonths($duration);
            $packPricePerMonth = $this->getPackFullPricePerMonth($duration);
            $totalAmount = $packPricePerMonth * $durationMonths;
            $this->activateSubscription($_SESSION['admin_id'], $sessionId, $durationMonths, 'pack_full', $packPricePerMonth);
            $this->activatePremiumFeatures($_SESSION['admin_id'], $features, $durationMonths);
            $durationLabel = ['1_month' => '1 mois', '3_months' => '3 mois', '1_year' => '1 an'][$duration] ?? '1 mois';
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$durationMonths} months"));
            $this->recordPurchase($_SESSION['admin_id'], 'pack_full', 'Pack Full ' . $durationLabel, $totalAmount, $packPricePerMonth, $durationMonths, $sessionId, $features, $expiresAt);
            $message = 'Paiement confirmé ! Pack Full activé pour ' . $durationLabel . '. Toutes les fonctionnalités sont débloquées !';
            $_SESSION['pendingToast'] = json_encode([
                'message' => $message,
                'type' => 'success'
            ]);
            header('Location: ?page=settings&section=subscriptions#subscription-total');
        } elseif ($type === 'premium') {
            $features = json_decode($session['metadata']['features'] ?? '[]', true);
            $this->activatePremiumFeatures($_SESSION['admin_id'], $features);
            $names = implode(', ', array_map([$this, 'translateFeatureName'], $features));
            $premiumFeatureModel = new PremiumFeature($this->pdo);
            $availableFeatures = $premiumFeatureModel->getAvailableFeatures();
            $totalAmount = 0;
            foreach ($features as $f) {
                $totalAmount += $availableFeatures[$f]['price_monthly'] ?? 0;
            }
            $this->recordPurchase($_SESSION['admin_id'], 'premium', 'Options : ' . $names, $totalAmount, $totalAmount, 1, $sessionId, $features);
            $message = 'Paiement confirmé ! Option(s) activée(s) : ' . $names . '.';
            $_SESSION['pendingToast'] = json_encode([
                'message' => $message,
                'type' => 'success'
            ]);
            header('Location: ?page=settings&section=subscriptions#subscription-total');
        } elseif ($type === 'basique_premium') {
            $features = json_decode($session['metadata']['features'] ?? '[]', true);
            $this->activateSubscription($_SESSION['admin_id'], $sessionId);
            $this->activatePremiumFeatures($_SESSION['admin_id'], $features);
            $names = implode(', ', array_map([$this, 'translateFeatureName'], $features));
            $premiumFeatureModel = new PremiumFeature($this->pdo);
            $availableFeatures = $premiumFeatureModel->getAvailableFeatures();
            $totalAmount = 11.99;
            foreach ($features as $f) {
                $totalAmount += $availableFeatures[$f]['price_monthly'] ?? 0;
            }
            $this->recordPurchase($_SESSION['admin_id'], 'basique', 'Abonnement Basique + ' . $names, $totalAmount, $totalAmount, 1, $sessionId, $features);
            $message = 'Paiement confirmé ! Votre abonnement Basique est actif' . 
                ($names ? ' et option(s) premium activée(s) : ' . $names . '.' : ' !');
            $_SESSION['pendingToast'] = json_encode([
                'message' => $message,
                'type' => 'success'
            ]);
            header('Location: ?page=settings&section=subscriptions#subscription-total');
        } else {
            $this->activateSubscription($_SESSION['admin_id'], $sessionId);
            $this->recordPurchase($_SESSION['admin_id'], 'basique', 'Abonnement Basique', 11.99, 11.99, 1, $sessionId);
            $message = 'Paiement confirmé ! Votre abonnement Basique est maintenant actif. Bienvenue sur MenuCraft !';
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

        if ($type === 'pack_full') {
            $features = json_decode($session['metadata']['features'] ?? '[]', true);
            $duration = $session['metadata']['duration'] ?? '1_month';
            $durationMonths = $this->getDurationMonths($duration);
            $packPricePerMonth = $this->getPackFullPricePerMonth($duration);
            $this->activateSubscription($adminId, $sessionId, $durationMonths, 'pack_full', $packPricePerMonth);
            if (!empty($features)) {
                $this->activatePremiumFeatures($adminId, $features, $durationMonths);
            }
            error_log('[Stripe Webhook] Pack Full activé pour admin #' . $adminId . ' (durée: ' . $duration . ')');
        } elseif ($type === 'premium' || $type === 'basique_premium') {
            $features = json_decode($session['metadata']['features'] ?? '[]', true);
            if (!empty($features)) {
                $this->activatePremiumFeatures($adminId, $features);
                error_log('[Stripe Webhook] Options premium activées pour admin #' . $adminId . ': ' . implode(', ', $features));
            }
        }
    }

    /**
     * Convertit une clé de durée en nombre de mois
     */
    private function getDurationMonths($duration)
    {
        $map = ['1_month' => 1, '3_months' => 3, '1_year' => 12];
        return $map[$duration] ?? 1;
    }

    /**
     * Retourne le prix mensuel du Pack Full selon la durée
     */
    private function getPackFullPricePerMonth($duration)
    {
        $map = ['1_month' => 29.99, '3_months' => 26.99, '1_year' => 22.99];
        return $map[$duration] ?? 29.99;
    }

    /**
     * Active (ou réactive) l'abonnement d'un admin
     * @param int $durationMonths Nombre de mois (1, 3 ou 12)
     * @param string $planType 'basique' ou 'pack_full'
     * @param float $pricePerMonth Prix mensuel effectif
     */
    private function activateSubscription($adminId, $stripeSessionId, $durationMonths = 1, $planType = 'basique', $pricePerMonth = 11.99)
    {
        try {
            $note = 'Stripe session: ' . $stripeSessionId;
            $stmt = $this->pdo->prepare(
                "UPDATE client_subscriptions
                 SET plan_type = ?, status = 'active', price_per_month = ?, started_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL ? MONTH), notes = ?
                 WHERE admin_id = ?"
            );
            $stmt->execute([$planType, $pricePerMonth, $durationMonths, $note, $adminId]);

            // Si aucune ligne existait, on l'insère
            if ($stmt->rowCount() === 0) {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO client_subscriptions
                        (admin_id, plan_type, status, price_per_month, started_at, expires_at, notes)
                     VALUES (?, ?, 'active', ?, NOW(), DATE_ADD(NOW(), INTERVAL ? MONTH), ?)"
                );
                $stmt->execute([$adminId, $planType, $pricePerMonth, $durationMonths, $note]);
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
     * @param int $durationMonths Nombre de mois (1, 3 ou 12)
     */
    private function activatePremiumFeatures($adminId, array $features, $durationMonths = 1)
    {
        if (empty($features)) {
            return;
        }
        try {
            $premiumFeature = new PremiumFeature($this->pdo);
            
            foreach ($features as $featureKey) {
                $premiumFeature->enable($adminId, $featureKey, $durationMonths);
            }
        } catch (Exception $e) {
            error_log('[Stripe] Erreur activation options premium: ' . $e->getMessage());
        }
    }

    /**
     * Enregistre un achat dans l'historique pour traçabilité
     */
    private function recordPurchase($adminId, $type, $label, $amount, $pricePerMonth, $durationMonths, $stripeSessionId, $features = null, $expiresAt = null)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO purchase_history 
                    (admin_id, type, label, features, amount, price_per_month, duration_months, stripe_session_id, status, purchased_at, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed', NOW(), ?)
            ");
            $stmt->execute([
                $adminId,
                $type,
                $label,
                $features ? json_encode($features) : null,
                $amount,
                $pricePerMonth,
                $durationMonths,
                $stripeSessionId,
                $expiresAt
            ]);
        } catch (Exception $e) {
            error_log('[Stripe] Erreur enregistrement historique achat: ' . $e->getMessage());
        }
    }
}
