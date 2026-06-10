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

        $this->render('admin/feedback', [
            'csrf_token' => $this->getCsrfToken(),
            'success_message' => $_SESSION['feedback_success'] ?? null
        ]);

        unset($_SESSION['feedback_success']);
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

        $_SESSION['feedback_success'] = 'Merci pour votre retour ! Votre avis nous est précieux pour améliorer MenuCraft.';
        header('Location: ?page=feedback');
        exit;
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
