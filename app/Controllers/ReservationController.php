<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Admin.php';
require_once __DIR__ . '/../Models/Reservation.php';
require_once __DIR__ . '/../Models/OptionModel.php';
require_once __DIR__ . '/../Models/PremiumFeature.php';
require_once __DIR__ . '/../Models/Restaurant.php';
require_once __DIR__ . '/../Helpers/Mailer.php';

/**
 * Contrôleur pour les réservations en ligne (option premium)
 */
class ReservationController extends BaseController
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Vérifie l'accès premium à la fonctionnalité
     */
    private function checkAccess()
    {
        $this->requireLogin();

        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findById($_SESSION['admin_id']);

        if (!$admin) {
            $this->addErrorMessage('Administrateur non trouvé.');
            header('Location: ?page=login');
            exit;
        }

        $isSuperAdmin = ($admin->role === 'SUPER_ADMIN');
        $premiumFeature = new PremiumFeature($this->pdo);
        $hasAccess = $isSuperAdmin || $premiumFeature->isEnabled($_SESSION['admin_id'], 'online_booking');

        if (!$hasAccess) {
            $this->addErrorMessage('Cette fonctionnalité nécessite l\'option Réservations en ligne. Activez-la dans les fonctionnalités premium.');
            header('Location: ?page=settings&section=premium');
            exit;
        }

        return $admin;
    }

    /**
     * Envoie un email au restaurant pour l'informer d'une nouvelle réservation
     */
    private function sendNewReservationNotificationToRestaurant($reservation, $admin)
    {
        $restaurantEmail = $admin->email;
        $restaurantName = htmlspecialchars($admin->restaurant_name ?? 'Restaurant');
        
        $customerName = htmlspecialchars($reservation['customer_name']);
        $date = date('d/m/Y', strtotime($reservation['reservation_date']));
        $time = substr($reservation['reservation_time'], 0, 5);
        $party = (int)$reservation['party_size'];
        $phone = htmlspecialchars($reservation['customer_phone']);
        $email = !empty($reservation['customer_email']) ? htmlspecialchars($reservation['customer_email']) : 'Non renseigné';
        $specialReqs = !empty($reservation['special_requests']) ? htmlspecialchars($reservation['special_requests']) : 'Aucune';

        $subject = "🔔 Nouvelle réservation - $restaurantName";
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #b45309 0%, #ea580c 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { background: #ffffff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; }
                .alert { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; margin: 20px 0; border-radius: 4px; }
                .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .details-table td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
                .details-table td:first-child { font-weight: 600; color: #6b7280; width: 40%; }
                .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 0.9rem; color: #6b7280; border-radius: 0 0 8px 8px; }
                .btn { display: inline-block; padding: 12px 24px; background: #b45309; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔔 Nouvelle réservation</h1>
                </div>
                <div class='content'>
                    <div class='alert'>
                        <strong>⏰ Action requise :</strong> Une nouvelle demande de réservation vient d'être effectuée sur votre site.
                    </div>
                    
                    <h2 style='color: #b45309; margin-top: 0;'>Détails de la réservation</h2>
                    
                    <table class='details-table'>
                        <tr>
                            <td>👤 Client</td>
                            <td><strong>$customerName</strong></td>
                        </tr>
                        <tr>
                            <td>📅 Date</td>
                            <td><strong>$date</strong></td>
                        </tr>
                        <tr>
                            <td>🕐 Heure</td>
                            <td><strong>$time</strong></td>
                        </tr>
                        <tr>
                            <td>👥 Nombre de personnes</td>
                            <td><strong>$party personne" . ($party > 1 ? 's' : '') . "</strong></td>
                        </tr>
                        <tr>
                            <td>📞 Téléphone</td>
                            <td>$phone</td>
                        </tr>
                        <tr>
                            <td>📧 Email</td>
                            <td>$email</td>
                        </tr>
                        <tr>
                            <td>💬 Demandes spéciales</td>
                            <td>$specialReqs</td>
                        </tr>
                    </table>
                    
                    <p style='text-align: center;'>
                        <a href='" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "?page=reservations' class='btn'>
                            Voir dans le panel admin
                        </a>
                    </p>
                    
                    <p style='color: #6b7280; font-size: 0.9rem; margin-top: 30px;'>
                        <strong>💡 Conseil :</strong> Pensez à confirmer ou refuser cette réservation rapidement pour offrir la meilleure expérience à vos clients.
                    </p>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement par MenuMiam</p>
                    <p style='margin: 5px 0;'>Vous recevez cet email car vous gérez $restaurantName</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mailer = new Mailer();
        return $mailer->send($restaurantEmail, $subject, $message);
    }

    /**
     * Envoie un email au client concernant sa réservation
     */
    private function sendReservationEmail($reservation, $restaurantName, $type, $extra = [])
    {
        $email = $reservation['customer_email'] ?? null;
        if (empty($email)) return false;

        $name = htmlspecialchars($reservation['customer_name']);
        $date = date('d/m/Y', strtotime($reservation['reservation_date']));
        $time = substr($reservation['reservation_time'], 0, 5);
        $party = (int)$reservation['party_size'];
        $restaurant = htmlspecialchars($restaurantName);

        $detailsHtml = "
            <table cellpadding='8' cellspacing='0' style='border-collapse:collapse; width:100%; margin:20px 0; border:1px solid #e5e7eb; border-radius:8px;'>
                <tr style='background:#f9fafb;'>
                    <td style='font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb; padding:10px 16px;'>Restaurant</td>
                    <td style='border-bottom:1px solid #e5e7eb; padding:10px 16px;'>$restaurant</td>
                </tr>
                <tr>
                    <td style='font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb; padding:10px 16px;'>Date</td>
                    <td style='border-bottom:1px solid #e5e7eb; padding:10px 16px;'>$date</td>
                </tr>
                <tr style='background:#f9fafb;'>
                    <td style='font-weight:600; color:#6b7280; border-bottom:1px solid #e5e7eb; padding:10px 16px;'>Heure</td>
                    <td style='border-bottom:1px solid #e5e7eb; padding:10px 16px;'>$time</td>
                </tr>
                <tr>
                    <td style='font-weight:600; color:#6b7280; padding:10px 16px;'>Couverts</td>
                    <td style='padding:10px 16px;'>$party personne" . ($party > 1 ? 's' : '') . "</td>
                </tr>
            </table>";

        switch ($type) {
            case 'received':
                $subject = "Réservation reçue — $restaurant";
                $color = '#f59e0b';
                $icon = '📋';
                $title = 'Votre demande de réservation a bien été reçue';
                $message = "Bonjour <strong>$name</strong>,<br><br>
                    Nous avons bien reçu votre demande de réservation. Elle est actuellement <strong>en attente de confirmation</strong> par le restaurant.
                    Vous recevrez un email dès qu'elle sera traitée.";
                break;

            case 'confirmed':
                $subject = "Réservation confirmée ✓ — $restaurant";
                $color = '#10b981';
                $icon = '✅';
                $title = 'Votre réservation est confirmée !';
                $message = "Bonjour <strong>$name</strong>,<br><br>
                    Bonne nouvelle ! Votre réservation a été <strong style='color:#10b981;'>confirmée</strong> par le restaurant.
                    Nous vous attendons avec plaisir.";
                break;

            case 'cancelled':
                $subject = "Réservation refusée — $restaurant";
                $color = '#ef4444';
                $icon = '❌';
                $title = 'Votre réservation n\'a pas pu être confirmée';
                $reasonHtml = '';
                if (!empty($extra['cancelled_reason'])) {
                    $reason = htmlspecialchars($extra['cancelled_reason']);
                    $reasonHtml = "<div style='background:#fef2f2; border-left:4px solid #ef4444; padding:12px 16px; margin:16px 0; border-radius:4px;'>
                        <strong>Raison :</strong> $reason
                    </div>";
                }
                $message = "Bonjour <strong>$name</strong>,<br><br>
                    Nous sommes désolés, votre réservation a été <strong style='color:#ef4444;'>refusée</strong> par le restaurant.
                    $reasonHtml
                    N'hésitez pas à réessayer pour une autre date ou un autre créneau.";
                break;

            default:
                return false;
        }

        $body = "
        <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; max-width:600px; margin:0 auto; background:#ffffff;'>
            <div style='background:$color; padding:24px; text-align:center;'>
                <span style='font-size:2rem;'>$icon</span>
                <h1 style='color:#ffffff; margin:12px 0 0; font-size:1.3rem;'>$title</h1>
            </div>
            <div style='padding:24px 32px; color:#374151; font-size:0.95rem; line-height:1.6;'>
                $message
                <h3 style='margin:24px 0 8px; color:#374151; font-size:1rem;'>Détails de la réservation</h3>
                $detailsHtml
            </div>
            <div style='background:#f9fafb; padding:16px 32px; text-align:center; font-size:0.8rem; color:#9ca3af; border-top:1px solid #e5e7eb;'>
                Cet email a été envoyé automatiquement par <strong>$restaurant</strong> via MenuMiam.
            </div>
        </div>";

        $mailer = new Mailer();
        return $mailer->send($email, $subject, $body);
    }

    /**
     * Récupère les paramètres de réservation d'un admin
     */
    private function getSettings($adminId)
    {
        $optionModel = new OptionModel($this->pdo);
        $options = $optionModel->getAll($adminId);

        return [
            'booking_enabled'       => ($options['booking_enabled'] ?? '1') === '1',
            'booking_min_party'     => max(1, (int)($options['booking_min_party'] ?? 1)),
            'booking_max_party'     => max(1, (int)($options['booking_max_party'] ?? 10)),
            'booking_advance_days'  => max(1, (int)($options['booking_advance_days'] ?? 30)),
            'booking_max_per_slot'  => max(1, (int)($options['booking_max_per_slot'] ?? 5)),
            'booking_time_slots'    => $options['booking_time_slots'] ?? '12:00,12:30,13:00,13:30,19:00,19:30,20:00,20:30,21:00',
            'booking_closed_days'   => $options['booking_closed_days'] ?? '',
            'booking_message'       => $options['booking_message'] ?? '',
            'booking_auto_confirm'  => ($options['booking_auto_confirm'] ?? '0') === '1',
            'booking_auto_complete' => ($options['booking_auto_complete'] ?? '0') === '1',
            'booking_meal_duration' => max(30, (int)($options['booking_meal_duration'] ?? 90)),
        ];
    }

    /**
     * Page admin : liste des réservations
     */
    public function show()
    {
        $admin = $this->checkAccess();
        $adminId = $_SESSION['admin_id'];

        $reservationModel = new Reservation($this->pdo);

        // Auto-compléter les réservations passées
        $reservationModel->autoComplete($adminId);

        // Filtres
        $filters = [];
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['date'])) {
            $filters['date'] = $_GET['date'];
        }
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }

        // Par défaut : réservations à partir d'aujourd'hui
        if (empty($filters['date']) && empty($filters['status']) && empty($filters['search'])) {
            $filters['date_from'] = date('Y-m-d');
        }

        $reservations = $reservationModel->getAll($adminId, $filters);
        $stats = $reservationModel->getStats($adminId);
        $todayReservations = $reservationModel->getToday($adminId);
        $settings = $this->getSettings($adminId);

        // Récupérer les dates de fermeture exceptionnelles pour le badge et l'info
        $closureDates = [];
        try {
            $stmt = $this->pdo->prepare("
                SELECT option_value 
                FROM admin_options 
                WHERE admin_id = ? AND option_name = 'closure_dates'
            ");
            $stmt->execute([$adminId]);
            $closureDatesValue = $stmt->fetchColumn();
            if ($closureDatesValue) {
                $closureDates = json_decode($closureDatesValue, true) ?: [];
            }
        } catch (Exception $e) {
            error_log("Erreur récupération dates fermeture: " . $e->getMessage());
            $closureDates = [];
        }

        $messages = $this->getFlashMessages();

        $this->render('admin/reservations', [
            'success_message'    => $messages['success_message'],
            'error_message'      => $messages['error_message'],
            'csrf_token'         => $this->getCsrfToken(),
            'reservations'       => $reservations,
            'todayReservations'  => $todayReservations,
            'stats'              => $stats,
            'settings'           => $settings,
            'filters'            => $filters,
            'closureDates'       => $closureDates,
            'restaurant_name'    => $admin->restaurant_name ?? '',
        ]);
    }

    /**
     * Mettre à jour le statut d'une réservation (AJAX)
     */
    public function updateStatus()
    {
        $this->checkAccess();
        $adminId = $_SESSION['admin_id'];

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $allowedStatuses = ['confirmed', 'cancelled', 'completed', 'no_show'];

        if (!$id || !in_array($status, $allowedStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
            exit;
        }

        $extra = [];
        if (!empty($_POST['cancelled_reason'])) {
            $extra['cancelled_reason'] = trim($_POST['cancelled_reason']);
        }
        if (isset($_POST['admin_notes'])) {
            $extra['admin_notes'] = trim($_POST['admin_notes']);
        }

        $reservationModel = new Reservation($this->pdo);
        $result = $reservationModel->updateStatus($id, $adminId, $status, $extra);

        if ($result) {
            // Envoyer un email au client pour confirmation ou refus
            if (in_array($status, ['confirmed', 'cancelled'])) {
                $reservation = $reservationModel->findById($id);
                if ($reservation && !empty($reservation['customer_email'])) {
                    $adminModel = new Admin($this->pdo);
                    $admin = $adminModel->findById($adminId);
                    $restaurantName = $admin->restaurant_name ?? '';
                    $this->sendReservationEmail($reservation, $restaurantName, $status, $extra);
                }
            }

            $statusLabels = [
                'confirmed' => 'confirmée',
                'cancelled' => 'annulée',
                'completed' => 'terminée',
                'no_show'   => 'marquée comme absent',
            ];
            
            // Renvoyer le token CSRF actuel (il reste valide pour les prochaines requêtes)
            echo json_encode([
                'success' => true,
                'message' => 'Réservation ' . ($statusLabels[$status] ?? 'mise à jour') . '.',
                'new_csrf_token' => $_SESSION['csrf_token'] ?? ''
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour.']);
        }
        exit;
    }

    /**
     * Supprimer une réservation (AJAX)
     */
    public function deleteReservation()
    {
        $this->checkAccess();
        $adminId = $_SESSION['admin_id'];

        header('Content-Type: application/json');

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            exit;
        }

        $reservationModel = new Reservation($this->pdo);
        $result = $reservationModel->delete($id, $adminId);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Réservation supprimée.' : 'Erreur lors de la suppression.',
            'new_csrf_token' => $_SESSION['csrf_token'] ?? ''
        ]);
        exit;
    }

    /**
     * Supprimer toutes les réservations (AJAX)
     */
    public function deleteAllReservations()
    {
        $this->checkAccess();
        $adminId = $_SESSION['admin_id'];

        header('Content-Type: application/json');

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $reservationModel = new Reservation($this->pdo);
        $count = $reservationModel->deleteAll($adminId);

        echo json_encode([
            'success' => true,
            'message' => "$count réservation(s) supprimée(s).",
            'count' => $count,
            'new_csrf_token' => $_SESSION['csrf_token'] ?? ''
        ]);
        exit;
    }

    /**
     * Supprimer toutes les réservations terminées (AJAX)
     */
    public function deleteCompletedReservations()
    {
        $this->checkAccess();
        $adminId = $_SESSION['admin_id'];

        header('Content-Type: application/json');

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $reservationModel = new Reservation($this->pdo);
        $count = $reservationModel->deleteByStatus($adminId, 'completed');

        echo json_encode([
            'success' => true,
            'message' => "$count réservation(s) terminée(s) supprimée(s).",
            'count' => $count,
            'new_csrf_token' => $_SESSION['csrf_token'] ?? ''
        ]);
        exit;
    }

    /**
     * Marquer toutes les réservations comme terminées (AJAX)
     */
    public function completeAllReservations()
    {
        $this->checkAccess();
        $adminId = $_SESSION['admin_id'];

        header('Content-Type: application/json');

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $reservationModel = new Reservation($this->pdo);
        $count = $reservationModel->completeAll($adminId);

        echo json_encode([
            'success' => true,
            'message' => "$count réservation(s) marquée(s) comme terminée(s).",
            'count' => $count,
            'new_csrf_token' => $_SESSION['csrf_token'] ?? ''
        ]);
        exit;
    }

    /**
     * Sauvegarder les paramètres de réservation (AJAX)
     */
    public function saveSettings()
    {
        $this->checkAccess();
        $adminId = $_SESSION['admin_id'];

        header('Content-Type: application/json');

        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
            exit;
        }

        $optionModel = new OptionModel($this->pdo);

        $settingsKeys = [
            'booking_enabled',
            'booking_min_party',
            'booking_max_party',
            'booking_advance_days',
            'booking_max_per_slot',
            'booking_time_slots',
            'booking_closed_days',
            'booking_message',
            'booking_auto_confirm',
            'booking_auto_complete',
            'booking_meal_duration',
        ];

        $checkboxKeys = ['booking_enabled', 'booking_auto_confirm', 'booking_auto_complete'];
        
        foreach ($settingsKeys as $key) {
            // Pour les checkboxes, on gère le cas où elles ne sont pas présentes dans $_POST
            if (in_array($key, $checkboxKeys)) {
                $value = isset($_POST[$key]) && $_POST[$key] === '1' ? '1' : '0';
                $optionModel->set($adminId, $key, $value);
            } elseif (isset($_POST[$key])) {
                $value = trim($_POST[$key]);
                // Validation basique
                if (in_array($key, ['booking_min_party', 'booking_max_party', 'booking_advance_days', 'booking_max_per_slot', 'booking_meal_duration'])) {
                    $value = max(1, (int)$value);
                }
                $optionModel->set($adminId, $key, (string)$value);
            }
        }

        echo json_encode([
            'success' => true, 
            'message' => 'Paramètres de réservation enregistrés.',
            'new_csrf_token' => $_SESSION['csrf_token'] ?? ''
        ]);
        exit;
    }

    /**
     * API publique : créer une réservation (côté vitrine)
     */
    public function publicBook()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        date_default_timezone_set('Europe/Paris');

        $adminId = (int)($_POST['admin_id'] ?? 0);
        if (!$adminId) {
            echo json_encode(['success' => false, 'message' => 'Restaurant non trouvé']);
            exit;
        }

        // Vérifier que la fonctionnalité est active
        $premiumFeature = new PremiumFeature($this->pdo);
        $adminModel = new Admin($this->pdo);
        $admin = $adminModel->findById($adminId);

        if (!$admin) {
            echo json_encode(['success' => false, 'message' => 'Restaurant non trouvé']);
            exit;
        }

        $isSuperAdmin = ($admin->role === 'SUPER_ADMIN');
        if (!$isSuperAdmin && !$premiumFeature->isEnabled($adminId, 'online_booking')) {
            echo json_encode(['success' => false, 'message' => 'Les réservations en ligne ne sont pas disponibles.']);
            exit;
        }

        // Vérifier que les réservations sont activées
        $settings = $this->getSettings($adminId);
        if (!$settings['booking_enabled']) {
            echo json_encode(['success' => false, 'message' => 'Les réservations sont temporairement fermées.']);
            exit;
        }

        // Validation des données
        $customerName  = trim($_POST['customer_name'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $date          = trim($_POST['reservation_date'] ?? '');
        $time          = trim($_POST['reservation_time'] ?? '');
        $partySize     = (int)($_POST['party_size'] ?? 0);
        $specialReqs   = trim($_POST['special_requests'] ?? '');

        $errors = [];

        if (empty($customerName) || mb_strlen($customerName) < 2) {
            $errors[] = 'Veuillez indiquer votre nom.';
        }
        if (empty($customerPhone) || mb_strlen($customerPhone) < 8) {
            $errors[] = 'Veuillez indiquer un numéro de téléphone valide.';
        }
        if (!empty($customerEmail) && !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse email n\'est pas valide.';
        }
        if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors[] = 'Veuillez choisir une date valide.';
        }
        if (empty($time) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
            $errors[] = 'Veuillez choisir un créneau horaire.';
        }
        if ($partySize < $settings['booking_min_party'] || $partySize > $settings['booking_max_party']) {
            $errors[] = 'Nombre de personnes entre ' . $settings['booking_min_party'] . ' et ' . $settings['booking_max_party'] . '.';
        }

        // Vérifier que la date est dans le futur
        if (!empty($date) && $date < date('Y-m-d')) {
            $errors[] = 'La date ne peut pas être dans le passé.';
        }

        // Vérifier que le créneau n'est pas dépassé pour aujourd'hui
        if (!empty($date) && !empty($time) && $date === date('Y-m-d') && $time <= date('H:i')) {
            $errors[] = 'Ce créneau horaire est déjà passé.';
        }

        // Vérifier que la date n'est pas trop loin
        $maxDate = date('Y-m-d', strtotime('+' . $settings['booking_advance_days'] . ' days'));
        if (!empty($date) && $date > $maxDate) {
            $errors[] = 'Les réservations ne sont pas ouvertes au-delà de ' . $settings['booking_advance_days'] . ' jours.';
        }

        // Vérifier le jour de fermeture
        if (!empty($date) && !empty($settings['booking_closed_days'])) {
            $closedDays = array_map('trim', explode(',', $settings['booking_closed_days']));
            $dayOfWeek = (int)date('w', strtotime($date)); // 0=dimanche, 6=samedi
            $dayNames = ['0' => 'dimanche', '1' => 'lundi', '2' => 'mardi', '3' => 'mercredi', '4' => 'jeudi', '5' => 'vendredi', '6' => 'samedi'];
            if (in_array((string)$dayOfWeek, $closedDays)) {
                $errors[] = 'Le restaurant est fermé le ' . ($dayNames[$dayOfWeek] ?? '') . '.';
            }
        }

        // Vérifier le créneau horaire
        if (!empty($time) && !empty($settings['booking_time_slots'])) {
            $allowedSlots = array_map('trim', explode(',', $settings['booking_time_slots']));
            if (!in_array($time, $allowedSlots)) {
                $errors[] = 'Ce créneau horaire n\'est pas disponible.';
            }
        }

        // Vérifier la disponibilité du créneau
        if (empty($errors) && !empty($date) && !empty($time)) {
            $reservationModel = new Reservation($this->pdo);
            $slotCount = $reservationModel->countForSlot($adminId, $date, $time . ':00');
            if ($slotCount >= $settings['booking_max_per_slot']) {
                $errors[] = 'Ce créneau est complet. Veuillez en choisir un autre.';
            }
        }

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]);
            exit;
        }

        // Créer la réservation
        $reservationModel = new Reservation($this->pdo);
        
        // Vérifier si la validation automatique est activée
        $autoConfirm = ($settings['booking_auto_confirm'] ?? false);
        $initialStatus = $autoConfirm ? 'confirmed' : 'pending';
        
        $reservationId = $reservationModel->create($adminId, [
            'customer_name'    => $customerName,
            'customer_email'   => $customerEmail ?: null,
            'customer_phone'   => $customerPhone,
            'reservation_date' => $date,
            'reservation_time' => $time . ':00',
            'party_size'       => $partySize,
            'special_requests' => $specialReqs ?: null,
            'status'           => $initialStatus,
        ]);

        if ($reservationId) {
            $reservation = $reservationModel->findById($reservationId);
            
            // Envoyer le mail approprié au client selon le statut
            if ($reservation && !empty($customerEmail)) {
                $emailType = $autoConfirm ? 'confirmed' : 'received';
                $this->sendReservationEmail($reservation, $admin->restaurant_name ?? '', $emailType);
            }
            
            // Envoyer un email au restaurant pour l'informer de la nouvelle réservation
            if ($reservation && !empty($admin->email)) {
                $this->sendNewReservationNotificationToRestaurant($reservation, $admin);
            }

            $message = $autoConfirm 
                ? 'Votre réservation a été confirmée ! Nous vous attendons avec plaisir.'
                : 'Votre réservation a bien été enregistrée ! Vous recevrez une confirmation prochainement.';

            echo json_encode([
                'success' => true,
                'message' => $message,
                'reservation_id' => $reservationId,
                'auto_confirmed' => $autoConfirm
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création de la réservation.']);
        }
        exit;
    }

    /**
     * API publique : récupérer les créneaux disponibles pour une date (AJAX)
     */
    public function publicGetSlots()
    {
        header('Content-Type: application/json');
        date_default_timezone_set('Europe/Paris');

        $adminId = (int)($_GET['admin_id'] ?? 0);
        $date    = trim($_GET['date'] ?? '');

        if (!$adminId || !$date) {
            echo json_encode(['success' => false, 'slots' => []]);
            exit;
        }

        $settings = $this->getSettings($adminId);
        $allSlots = array_map('trim', explode(',', $settings['booking_time_slots']));

        // Vérifier jour de fermeture
        if (!empty($settings['booking_closed_days'])) {
            $closedDays = array_map('trim', explode(',', $settings['booking_closed_days']));
            $dayOfWeek = (string)date('w', strtotime($date));
            if (in_array($dayOfWeek, $closedDays)) {
                echo json_encode(['success' => true, 'slots' => [], 'closed' => true]);
                exit;
            }
        }

        // Récupérer les créneaux complets
        $reservationModel = new Reservation($this->pdo);
        $bookedSlots = $reservationModel->getBookedSlots($adminId, $date, $settings['booking_max_per_slot']);

        // Filtrer les créneaux : si la date est aujourd'hui, ne pas montrer les créneaux passés
        $now = date('H:i');
        $availableSlots = [];
        foreach ($allSlots as $slot) {
            $isBooked = in_array($slot, $bookedSlots);
            $isPast = ($date === date('Y-m-d') && $slot <= $now);
            $availableSlots[] = [
                'time'      => $slot,
                'available' => !$isBooked && !$isPast,
            ];
        }

        echo json_encode([
            'success' => true,
            'slots'   => $availableSlots,
            'closed'  => false,
        ]);
        exit;
    }

    /**
     * API AJAX : récupérer les réservations en attente pour le dropdown de notifications
     */
    public function getPendingReservations()
    {
        header('Content-Type: application/json');
        $this->checkAccess();
        
        $adminId = $_SESSION['admin_id'];
        $reservationModel = new Reservation($this->pdo);
        
        // Récupérer toutes les réservations en attente, triées par date et heure
        $stmt = $this->pdo->prepare("
            SELECT * FROM reservations 
            WHERE admin_id = ? AND status = 'pending'
            ORDER BY reservation_date ASC, reservation_time ASC
            LIMIT 50
        ");
        $stmt->execute([$adminId]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'reservations' => $reservations,
            'count' => count($reservations)
        ]);
        exit;
    }

    /**
     * API AJAX : récupérer les réservations pour une date spécifique
     */
    public function getDayReservations()
    {
        header('Content-Type: application/json');
        $this->checkAccess();
        
        $adminId = $_SESSION['admin_id'];
        $date = $_GET['date'] ?? date('Y-m-d');
        
        // Valider la date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            echo json_encode(['success' => false, 'message' => 'Date invalide']);
            exit;
        }
        
        $reservationModel = new Reservation($this->pdo);
        $reservations = $reservationModel->getByDate($adminId, $date);
        
        // Séparer les réservations en attente des autres
        $pendingReservations = array_values(array_filter($reservations, function($r) {
            return $r['status'] === 'pending';
        }));
        
        $otherReservations = array_values(array_filter($reservations, function($r) {
            return $r['status'] !== 'pending';
        }));
        
        // Statuts et couleurs
        $statusLabels = [
            'pending'   => 'En attente',
            'confirmed' => 'Confirmée',
            'cancelled' => 'Annulée',
            'completed' => 'Terminée',
            'no_show'   => 'Absent',
        ];
        
        $statusColors = [
            'pending'   => 'warning',
            'confirmed' => 'success',
            'cancelled' => 'danger',
            'completed' => 'muted',
            'no_show'   => 'danger',
        ];
        
        echo json_encode([
            'success' => true,
            'reservations' => $otherReservations,
            'pendingReservations' => $pendingReservations,
            'statusLabels' => $statusLabels,
            'statusColors' => $statusColors
        ]);
        exit;
    }
}
