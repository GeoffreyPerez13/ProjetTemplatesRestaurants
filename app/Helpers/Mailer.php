<?php

class Mailer
{
    private $fromEmail;
    private $fromName;

    /**
     * @param string $fromEmail Adresse d'expédition par défaut
     * @param string $fromName  Nom d'expéditeur par défaut
     */
    public function __construct($fromEmail = 'no-reply@menumiam.com', $fromName = 'Menumiam')
    {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    /**
     * Envoie un email HTML
     * @param string $to      Destinataire
     * @param string $subject Sujet
     * @param string $body    Corps HTML
     * @param array  $options Options supplémentaires (texte brut, pièces jointes...)
     * @return bool
     */
    public function send($to, $subject, $body, $options = [])
    {
        // Version texte brut si non fournie
        $textBody = $options['text'] ?? strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $body));

        // En-têtes
        $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: {$this->fromEmail}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        // Envoi
        $success = mail($to, $subject, $body, $headers);

        // Log
        $this->log($to, $subject, $success);

        return $success;
    }

    /**
     * Enregistre un log d'envoi
     */
    private function log($to, $subject, $success)
    {
        $logFile = __DIR__ . '/../../cron/logs/mail.log';
        $timestamp = date('Y-m-d H:i:s');
        $status = $success ? 'SUCCÈS' : 'ÉCHEC';
        $message = "[$timestamp] $status - À: $to - Sujet: $subject\n";
        file_put_contents($logFile, $message, FILE_APPEND);
    }
}