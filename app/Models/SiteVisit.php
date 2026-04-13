<?php
/**
 * Modèle pour le tracking et l'agrégation des visites du site vitrine
 */
class SiteVisit
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Enregistrer une visite
     */
    public function track($adminId, $visitorIp, $userAgent, $referrer, $pagePath)
    {
        // Hash de IP+UA pour identifier les visiteurs uniques sans stocker l'IP
        $visitorHash = hash('sha256', $visitorIp . '|' . $userAgent);
        $deviceType = $this->detectDeviceType($userAgent);
        $browser = $this->detectBrowser($userAgent);

        // Anti-spam : ne pas enregistrer plus d'une visite par visiteur par minute
        $stmt = $this->pdo->prepare("
            SELECT id FROM site_visits 
            WHERE admin_id = ? AND visitor_hash = ? AND visited_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
            LIMIT 1
        ");
        $stmt->execute([$adminId, $visitorHash]);
        if ($stmt->fetch()) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO site_visits (admin_id, visitor_hash, user_agent, referrer, device_type, browser, page_path, visited_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $adminId,
            $visitorHash,
            mb_substr($userAgent ?? '', 0, 512),
            mb_substr($referrer ?? '', 0, 1024),
            $deviceType,
            $browser,
            $pagePath
        ]);
    }

    /**
     * Nombre total de visites sur une période
     */
    public function getTotalVisits($adminId, $days = 30)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM site_visits 
            WHERE admin_id = ? AND visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$adminId, $days]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Nombre de visiteurs uniques sur une période
     */
    public function getUniqueVisitors($adminId, $days = 30)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT visitor_hash) FROM site_visits 
            WHERE admin_id = ? AND visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$adminId, $days]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Visites par jour sur une période (pour le graphique ligne)
     */
    public function getVisitsPerDay($adminId, $days = 30)
    {
        $stmt = $this->pdo->prepare("
            SELECT DATE(visited_at) as visit_date, 
                   COUNT(*) as total_visits,
                   COUNT(DISTINCT visitor_hash) as unique_visitors
            FROM site_visits 
            WHERE admin_id = ? AND visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(visited_at)
            ORDER BY visit_date ASC
        ");
        $stmt->execute([$adminId, $days]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Remplir les jours sans visites avec des zéros
        $filled = [];
        $startDate = new DateTime("-{$days} days");
        $endDate = new DateTime();
        $dataMap = [];
        foreach ($results as $row) {
            $dataMap[$row['visit_date']] = $row;
        }

        $current = clone $startDate;
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $filled[] = [
                'date' => $dateStr,
                'total' => (int)($dataMap[$dateStr]['total_visits'] ?? 0),
                'unique' => (int)($dataMap[$dateStr]['unique_visitors'] ?? 0),
            ];
            $current->modify('+1 day');
        }

        return $filled;
    }

    /**
     * Répartition par type d'appareil
     */
    public function getDeviceBreakdown($adminId, $days = 30)
    {
        $stmt = $this->pdo->prepare("
            SELECT device_type, COUNT(*) as count
            FROM site_visits 
            WHERE admin_id = ? AND visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY device_type
            ORDER BY count DESC
        ");
        $stmt->execute([$adminId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Répartition par navigateur
     */
    public function getBrowserBreakdown($adminId, $days = 30)
    {
        $stmt = $this->pdo->prepare("
            SELECT browser, COUNT(*) as count
            FROM site_visits 
            WHERE admin_id = ? AND visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY browser
            ORDER BY count DESC
            LIMIT 10
        ");
        $stmt->execute([$adminId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Top référents (d'où viennent les visiteurs)
     */
    public function getTopReferrers($adminId, $days = 30)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                CASE 
                    WHEN referrer IS NULL OR referrer = '' THEN 'Direct'
                    ELSE SUBSTRING_INDEX(SUBSTRING_INDEX(REPLACE(REPLACE(referrer, 'https://', ''), 'http://', ''), '/', 1), '?', 1)
                END as source,
                COUNT(*) as count
            FROM site_visits 
            WHERE admin_id = ? AND visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY source
            ORDER BY count DESC
            LIMIT 10
        ");
        $stmt->execute([$adminId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Visites par heure de la journée (heures de pointe)
     */
    public function getVisitsByHour($adminId, $days = 30)
    {
        $stmt = $this->pdo->prepare("
            SELECT HOUR(visited_at) as hour, COUNT(*) as count
            FROM site_visits 
            WHERE admin_id = ? AND visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY HOUR(visited_at)
            ORDER BY hour ASC
        ");
        $stmt->execute([$adminId, $days]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Remplir les 24h
        $hourMap = array_column($results, 'count', 'hour');
        $filled = [];
        for ($h = 0; $h < 24; $h++) {
            $filled[] = [
                'hour' => $h,
                'count' => (int)($hourMap[$h] ?? 0),
            ];
        }
        return $filled;
    }

    /**
     * Visites par jour de la semaine
     */
    public function getVisitsByDayOfWeek($adminId, $days = 30)
    {
        $stmt = $this->pdo->prepare("
            SELECT DAYOFWEEK(visited_at) as dow, COUNT(*) as count
            FROM site_visits 
            WHERE admin_id = ? AND visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DAYOFWEEK(visited_at)
            ORDER BY dow ASC
        ");
        $stmt->execute([$adminId, $days]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dayNames = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $dowMap = array_column($results, 'count', 'dow');
        $filled = [];
        for ($d = 1; $d <= 7; $d++) {
            $filled[] = [
                'day' => $dayNames[$d - 1],
                'count' => (int)($dowMap[$d] ?? 0),
            ];
        }
        return $filled;
    }

    /**
     * Tendance : comparaison période actuelle vs précédente
     */
    public function getTrend($adminId, $days = 30)
    {
        $current = $this->getTotalVisits($adminId, $days);
        $previous = 0;

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM site_visits 
            WHERE admin_id = ? 
              AND visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY) 
              AND visited_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$adminId, $days * 2, $days]);
        $previous = (int)$stmt->fetchColumn();

        if ($previous === 0) {
            $percentChange = $current > 0 ? 100 : 0;
        } else {
            $percentChange = round((($current - $previous) / $previous) * 100, 1);
        }

        return [
            'current' => $current,
            'previous' => $previous,
            'change' => $percentChange,
        ];
    }

    /**
     * Détecter le type d'appareil depuis le User-Agent
     */
    private function detectDeviceType($ua)
    {
        $ua = strtolower($ua ?? '');
        if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) return 'tablet';
        if (preg_match('/mobile|android|iphone|ipod|opera mini|opera mobi|webos|blackberry|windows phone/i', $ua)) return 'mobile';
        return 'desktop';
    }

    /**
     * Détecter le navigateur depuis le User-Agent
     */
    private function detectBrowser($ua)
    {
        $ua = $ua ?? '';
        if (preg_match('/Edg\//i', $ua)) return 'Edge';
        if (preg_match('/OPR\//i', $ua) || preg_match('/Opera/i', $ua)) return 'Opera';
        if (preg_match('/Chrome\//i', $ua) && !preg_match('/Edg\//i', $ua)) return 'Chrome';
        if (preg_match('/Firefox\//i', $ua)) return 'Firefox';
        if (preg_match('/Safari\//i', $ua) && !preg_match('/Chrome\//i', $ua)) return 'Safari';
        if (preg_match('/MSIE|Trident/i', $ua)) return 'IE';
        return 'Autre';
    }
}
