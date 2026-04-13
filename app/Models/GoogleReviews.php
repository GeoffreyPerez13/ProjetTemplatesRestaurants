<?php

/**
 * Modèle GoogleReviews : récupère et met en cache les avis Google Places
 * Utilise la nouvelle Places API (New) de Google
 */

class GoogleReviews
{
    private $pdo;
    private $apiKey;
    private $cacheDuration = 3600; // 1 heure en secondes

    public function __construct($pdo, $apiKey = null)
    {
        $this->pdo = $pdo;
        $this->apiKey = $apiKey ?: $this->getApiKeyFromConfig();
    }

    /**
     * Récupère les avis pour un Place ID
     */
    public function getReviews($placeId, $limit = 5)
    {
        // Vérifier le cache
        $cached = $this->getCachedReviews($placeId);
        if ($cached && (time() - strtotime($cached['cached_at'])) < $this->cacheDuration) {
            return json_decode($cached['data'], true);
        }

        // Appeler l'API
        $data = $this->fetchFromApi($placeId);
        if ($data) {
            $this->cacheReviews($placeId, json_encode($data));
            return $data;
        }

        return null;
    }

    /**
     * Appel à la nouvelle Places API (New)
     */
    private function fetchFromApi($placeId)
    {
        if (!$this->apiKey) {
            error_log('[GoogleReviews] Pas de clé API configurée');
            return null;
        }

        $url = "https://places.googleapis.com/v1/places/{$placeId}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Goog-Api-Key: ' . $this->apiKey,
            'X-Goog-FieldMask: displayName,rating,userRatingCount,reviews'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('[GoogleReviews] Erreur cURL: ' . $curlError);
            return null;
        }

        if ($httpCode !== 200) {
            error_log('[GoogleReviews] API HTTP ' . $httpCode . ': ' . $response);
            return null;
        }

        $data = json_decode($response, true);
        if (!$data) {
            return null;
        }

        // Formater la réponse de la nouvelle API
        $result = [
            'name' => $data['displayName']['text'] ?? '',
            'rating' => $data['rating'] ?? 0,
            'total_reviews' => $data['userRatingCount'] ?? 0,
            'reviews' => []
        ];

        if (isset($data['reviews'])) {
            $result['reviews'] = array_slice(array_map(function($review) {
                return [
                    'author_name' => $review['authorAttribution']['displayName'] ?? 'Anonyme',
                    'rating' => $review['rating'] ?? 0,
                    'text' => $review['text']['text'] ?? '',
                    'relative_time_description' => $this->formatRelativeTime($review['publishTime'] ?? ''),
                    'profile_photo_url' => $review['authorAttribution']['photoUri'] ?? null
                ];
            }, $data['reviews']), 0, 5);
        }

        return $result;
    }

    /**
     * Récupère depuis le cache
     */
    private function getCachedReviews($placeId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM google_reviews_cache WHERE place_id = ? ORDER BY cached_at DESC LIMIT 1");
        $stmt->execute([$placeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Met en cache les avis
     */
    private function cacheReviews($placeId, $data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO google_reviews_cache (place_id, data, cached_at) 
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE data = VALUES(data), cached_at = VALUES(cached_at)
        ");
        $stmt->execute([$placeId, $data]);
    }

    /**
     * Formate le temps relatif
     */
    private function formatRelativeTime($publishTime)
    {
        if (!$publishTime) return '';
        
        $date = new DateTime($publishTime);
        $now = new DateTime();
        $diff = $now->diff($date);

        if ($diff->y > 0) return $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
        if ($diff->m > 0) return $diff->m . ' mois';
        if ($diff->d > 0) return $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
        if ($diff->h > 0) return $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        return 'à l\'instant';
    }

    /**
     * Récupère la clé API depuis la config
     */
    private function getApiKeyFromConfig()
    {
        // Pour l'instant, on peut la stocker dans admin_options
        // Ou dans un fichier .env plus tard
        $stmt = $this->pdo->prepare("SELECT option_value FROM admin_options WHERE option_name = 'google_api_key' LIMIT 1");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Nettoie le cache expiré
     */
    public function cleanExpiredCache()
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM google_reviews_cache 
            WHERE cached_at < DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$this->cacheDuration]);
    }
}
