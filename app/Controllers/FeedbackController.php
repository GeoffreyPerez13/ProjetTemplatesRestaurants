<?php
require_once __DIR__ . '/BaseController.php';

/**
 * Contrôleur du formulaire de feedback beta
 * Permet aux utilisateurs de soumettre leurs retours d'expérience
 */
class FeedbackController extends BaseController
{
    /**
     * Affiche le formulaire de feedback
     */
    public function show()
    {
        $this->requireLogin();

        $limitReached = $this->hasReachedMonthlyLimit($_SESSION['admin_id']);
        $remaining = $this->getRemainingFeedbacks($_SESSION['admin_id']);

        $this->render('admin/feedback', [
            'csrf_token' => $this->getCsrfToken(),
            'feedback_swal' => $_SESSION['feedback_swal'] ?? null,
            'limit_reached' => $limitReached,
            'remaining' => $remaining
        ]);

        unset($_SESSION['feedback_swal']);
    }

    /**
     * Traite la soumission du formulaire de feedback
     */
    public function submit()
    {
        $this->requireLogin();

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verifyCsrfToken($token)) {
            $_SESSION['error_message'] = 'Token CSRF invalide.';
            header('Location: ?page=feedback');
            exit;
        }

        // Limiter à 3 soumissions par mois par restaurant
        if ($this->hasReachedMonthlyLimit($_SESSION['admin_id'])) {
            $_SESSION['feedback_swal'] = [
                'icon' => 'warning',
                'title' => 'Limite atteinte',
                'text' => 'Vous avez déjà envoyé 3 retours ce mois-ci. Merci pour votre implication ! Vous pourrez en soumettre à nouveau le mois prochain.'
            ];
            header('Location: ?page=feedback');
            exit;
        }

        $data = [
            'admin_id' => $_SESSION['admin_id'],
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'rating' => (int)($_POST['rating'] ?? 0),
            'ease_of_use' => $_POST['ease_of_use'] ?? '',
            'liked_features' => implode(', ', $_POST['liked_features'] ?? []),
            'improvements' => trim($_POST['improvements'] ?? ''),
            'would_recommend' => $_POST['would_recommend'] ?? '',
            'comments' => trim($_POST['comments'] ?? ''),
            'submitted_at' => date('Y-m-d H:i:s')
        ];

        // Sauvegarder dans un fichier JSON (simple, pas besoin de table BDD)
        $feedbackDir = __DIR__ . '/../../storage/feedback';
        if (!is_dir($feedbackDir)) {
            mkdir($feedbackDir, 0755, true);
        }

        $filename = $feedbackDir . '/feedback_' . date('Y-m-d_His') . '_' . $data['admin_id'] . '.json';
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Envoyer une notification par mail au SUPER_ADMIN
        $this->sendFeedbackNotification($data);

        $_SESSION['feedback_swal'] = [
            'icon' => 'success',
            'title' => 'Merci !',
            'text' => 'Votre avis a bien été envoyé. Merci pour votre retour, il nous est précieux pour améliorer MenuCraft.'
        ];
        header('Location: ?page=feedback');
        exit;
    }

    /**
     * Dashboard SUPER_ADMIN : affiche les stats et la liste de tous les feedbacks
     */
    public function dashboard()
    {
        $this->requireLogin();

        // Vérifier que c'est le SUPER_ADMIN
        require_once __DIR__ . '/../Models/Admin.php';
        $adminModel = new Admin($this->pdo);
        $currentAdmin = $adminModel->findById($_SESSION['admin_id']);
        if (!$currentAdmin || $currentAdmin->role !== 'SUPER_ADMIN') {
            $_SESSION['error_message'] = "Accès refusé.";
            header('Location: ?page=dashboard');
            exit;
        }

        // Charger tous les feedbacks
        $feedbacks = $this->loadAllFeedbacks();

        // Calculer les statistiques
        $stats = $this->computeStats($feedbacks);

        $this->render('admin/feedback-dashboard', [
            'feedbacks' => $feedbacks,
            'stats' => $stats,
            'csrf_token' => $this->getCsrfToken()
        ]);
    }

    /**
     * Charge tous les fichiers JSON de feedback
     */
    private function loadAllFeedbacks()
    {
        $feedbackDir = __DIR__ . '/../../storage/feedback';
        if (!is_dir($feedbackDir)) {
            return [];
        }

        $files = glob($feedbackDir . '/feedback_*.json');
        if (!$files) {
            return [];
        }

        $feedbacks = [];
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $feedbacks[] = $data;
            }
        }

        // Trier par date décroissante
        usort($feedbacks, function ($a, $b) {
            return strtotime($b['submitted_at'] ?? '0') - strtotime($a['submitted_at'] ?? '0');
        });

        return $feedbacks;
    }

    /**
     * Calcule les statistiques globales à partir de tous les feedbacks
     */
    private function computeStats($feedbacks)
    {
        $stats = [
            'total' => count($feedbacks),
            'avg_rating' => 0,
            'ease_of_use' => [],
            'liked_features' => [],
            'recommendations' => [],
        ];

        if (empty($feedbacks)) {
            return $stats;
        }

        $totalRating = 0;
        foreach ($feedbacks as $fb) {
            // Note
            $totalRating += (int)($fb['rating'] ?? 0);

            // Facilité d'utilisation
            $ease = $fb['ease_of_use'] ?? '';
            if ($ease) {
                $stats['ease_of_use'][$ease] = ($stats['ease_of_use'][$ease] ?? 0) + 1;
            }

            // Features appréciées
            $features = $fb['liked_features'] ?? '';
            if ($features) {
                $featureList = array_map('trim', explode(',', $features));
                foreach ($featureList as $f) {
                    if ($f) {
                        $stats['liked_features'][$f] = ($stats['liked_features'][$f] ?? 0) + 1;
                    }
                }
            }

            // Recommandations
            $rec = $fb['would_recommend'] ?? '';
            if ($rec) {
                $stats['recommendations'][$rec] = ($stats['recommendations'][$rec] ?? 0) + 1;
            }
        }

        $stats['avg_rating'] = round($totalRating / $stats['total'], 1);

        // Trier par fréquence décroissante
        arsort($stats['ease_of_use']);
        arsort($stats['liked_features']);
        arsort($stats['recommendations']);

        return $stats;
    }

    /**
     * Retourne le nombre de feedbacks restants pour le mois en cours
     */
    private function getRemainingFeedbacks($adminId)
    {
        $feedbackDir = __DIR__ . '/../../storage/feedback';
        if (!is_dir($feedbackDir)) {
            return 3;
        }

        $currentMonth = date('Y-m');
        $files = glob($feedbackDir . '/feedback_' . $currentMonth . '*_' . $adminId . '.json');
        $count = is_array($files) ? count($files) : 0;

        return max(0, 3 - $count);
    }

    /**
     * Vérifie si un admin a atteint la limite de 3 feedbacks par mois
     */
    private function hasReachedMonthlyLimit($adminId)
    {
        return $this->getRemainingFeedbacks($adminId) === 0;
    }

    /**
     * Envoie un email de notification au SUPER_ADMIN quand un feedback est soumis
     */
    private function sendFeedbackNotification($data)
    {
        try {
            $to = 'contact.menucraft@gmail.com';
            $subject = '[MenuCraft Beta] Nouveau feedback reçu';

            $ratingStars = str_repeat('★', $data['rating']) . str_repeat('☆', 5 - $data['rating']);
            $body = "Nouveau feedback beta reçu :\n\n";
            $body .= "Identité : " . ($data['name'] ?: 'Anonyme') . "\n";
            $body .= "Email : " . ($data['email'] ?: 'Non renseigné') . "\n";
            $body .= "Note : {$ratingStars} ({$data['rating']}/5)\n";
            $body .= "Facilité : {$data['ease_of_use']}\n";
            $body .= "Features appréciées : {$data['liked_features']}\n";
            $body .= "Recommanderait : {$data['would_recommend']}\n\n";
            $body .= "Améliorations :\n{$data['improvements']}\n\n";
            $body .= "Commentaires :\n{$data['comments']}\n";

            $headers = "From: no-reply@menucraft.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            @mail($to, $subject, $body, $headers);
        } catch (Exception $e) {
            error_log("Erreur envoi notification feedback: " . $e->getMessage());
        }
    }
}
