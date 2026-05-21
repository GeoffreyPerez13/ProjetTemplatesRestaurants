<?php

/**
 * Rate Limiter basé sur fichiers — indépendant de la session
 * Limite les actions par adresse IP sur une fenêtre de temps donnée
 */
class RateLimiter
{
    /** @var string Dossier de stockage des fichiers de rate limit */
    private $storageDir;

    public function __construct()
    {
        $this->storageDir = __DIR__ . '/../../storage/rate_limits/';
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * Vérifie si une action est autorisée pour une IP donnée
     *
     * @param string $action   Identifiant de l'action (ex: 'booking', 'login')
     * @param int    $maxAttempts Nombre max de tentatives
     * @param int    $windowSeconds Fenêtre de temps en secondes
     * @return bool true si l'action est autorisée
     */
    public function attempt(string $action, int $maxAttempts, int $windowSeconds): bool
    {
        $ip = $this->getClientIp();
        $key = $this->getKey($action, $ip);
        $file = $this->storageDir . $key . '.json';

        $data = $this->loadData($file);

        // Nettoyer les entrées expirées
        $now = time();
        $data = array_filter($data, function ($timestamp) use ($now, $windowSeconds) {
            return ($now - $timestamp) < $windowSeconds;
        });

        // Vérifier la limite
        if (count($data) >= $maxAttempts) {
            return false;
        }

        // Enregistrer la tentative
        $data[] = $now;
        $this->saveData($file, $data);

        return true;
    }

    /**
     * Retourne le nombre de secondes restantes avant la prochaine tentative autorisée
     *
     * @param string $action
     * @param int    $windowSeconds
     * @return int Secondes restantes (0 si déjà autorisé)
     */
    public function retryAfter(string $action, int $windowSeconds): int
    {
        $ip = $this->getClientIp();
        $key = $this->getKey($action, $ip);
        $file = $this->storageDir . $key . '.json';

        $data = $this->loadData($file);
        if (empty($data)) {
            return 0;
        }

        $oldest = min($data);
        $expiresAt = $oldest + $windowSeconds;
        $remaining = $expiresAt - time();

        return max(0, $remaining);
    }

    /**
     * Réinitialise le compteur pour une action/IP
     *
     * @param string $action
     */
    public function reset(string $action): void
    {
        $ip = $this->getClientIp();
        $key = $this->getKey($action, $ip);
        $file = $this->storageDir . $key . '.json';

        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Nettoie les fichiers de rate limit expirés (à appeler périodiquement)
     *
     * @param int $maxAge Age max en secondes (par défaut 1h)
     */
    public function cleanup(int $maxAge = 3600): void
    {
        $files = glob($this->storageDir . '*.json');
        $now = time();

        foreach ($files as $file) {
            if (($now - filemtime($file)) > $maxAge) {
                unlink($file);
            }
        }
    }

    /**
     * Récupère l'IP du client (gère les proxies)
     */
    private function getClientIp(): string
    {
        // En production derrière un reverse proxy, utiliser X-Forwarded-For
        // Attention : ne faire confiance qu'au premier proxy de confiance
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        return preg_replace('/[^a-fA-F0-9.:_]/', '', $ip);
    }

    /**
     * Génère une clé de fichier sécurisée
     */
    private function getKey(string $action, string $ip): string
    {
        return $action . '_' . md5($ip);
    }

    /**
     * Charge les données depuis le fichier
     */
    private function loadData(string $file): array
    {
        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Sauvegarde les données dans le fichier
     */
    private function saveData(string $file, array $data): void
    {
        file_put_contents($file, json_encode(array_values($data)), LOCK_EX);
    }
}
