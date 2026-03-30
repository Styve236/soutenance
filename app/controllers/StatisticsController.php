<?php
/**
 * StatisticsController - Gère les statistiques et le trafic
 */

class StatisticsController
{
    /**
     * Enregistrer une visite
     */
    public static function recordVisit($page = null)
    {
        $userId = Auth::check() ? Auth::user()['id'] : null;
        $ipAddress = getClientIp();
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);
        $page = $page ?? basename($_SERVER['PHP_SELF']);

        $sql = "INSERT INTO visitor_tracking (user_id, ip_address, user_agent, page) 
                VALUES (" . ($userId ? $userId : "NULL") . ", '$ipAddress', '$userAgent', '$page')";

        return Database::execute($sql);
    }

    /**
     * Obtenir les statistiques en temps réel
     */
    public static function getLiveStats()
    {
        // Visiteurs actuels (dernière 30 minutes)
        $current = Database::getOne("
            SELECT COUNT(DISTINCT ip_address) as count 
            FROM visitor_tracking 
            WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        ");

        // Visites aujourd'hui
        $today = Database::getOne("
            SELECT COUNT(*) as count 
            FROM visitor_tracking 
            WHERE DATE(visit_date) = CURDATE()
        ");

        // Comptes créés aujourd'hui
        $registrations = Database::getOne("
            SELECT COUNT(*) as count 
            FROM user_registrations 
            WHERE DATE(registration_date) = CURDATE()
        ");

        return [
            'current_visitors' => $current['count'] ?? 0,
            'today_visits' => $today['count'] ?? 0,
            'today_registrations' => $registrations['count'] ?? 0
        ];
    }

    /**
     * Obtenir les statistiques d'une période
     */
    public static function getStats($period = 'daily')
    {
        if ($period === 'daily') {
            return self::getDailyStats();
        } elseif ($period === 'weekly') {
            return self::getWeeklyStats();
        } elseif ($period === 'monthly') {
            return self::getMonthlyStats();
        }
        return [];
    }

    /**
     * Statistiques journalières
     */
    private static function getDailyStats()
    {
        return Database::getAll("
            SELECT 
                DATE(visit_date) as date,
                COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id END) as unique_visitors,
                COUNT(*) as total_visits
            FROM visitor_tracking
            WHERE visit_date >= CURDATE()
            GROUP BY DATE(visit_date)
            ORDER BY DATE(visit_date) DESC
        ");
    }

    /**
     * Statistiques hebdomadaires
     */
    private static function getWeeklyStats()
    {
        return Database::getAll("
            SELECT 
                WEEK(visit_date) as week,
                YEAR(visit_date) as year,
                COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id END) as unique_visitors,
                COUNT(*) as total_visits
            FROM visitor_tracking
            WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY WEEK(visit_date), YEAR(visit_date)
            ORDER BY year DESC, week DESC
        ");
    }

    /**
     * Statistiques mensuelles
     */
    private static function getMonthlyStats()
    {
        return Database::getAll("
            SELECT 
                MONTH(visit_date) as month,
                YEAR(visit_date) as year,
                COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id END) as unique_visitors,
                COUNT(*) as total_visits
            FROM visitor_tracking
            WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY MONTH(visit_date), YEAR(visit_date)
            ORDER BY year DESC, month DESC
        ");
    }

    /**
     * Obtenir les pages populaires
     */
    public static function getPopularPages($limit = 10, $days = 7)
    {
        return Database::getAll("
            SELECT page, COUNT(*) as count
            FROM visitor_tracking
            WHERE visit_date >= DATE_SUB(NOW(), INTERVAL $days DAY)
            GROUP BY page
            ORDER BY count DESC
            LIMIT $limit
        ");
    }

    /**
     * Obtenir les visites par heure
     */
    public static function getVisitsPerHour($days = 1)
    {
        $data = [];
        for ($i = 0; $i < 24; $i++) {
            $data[$i] = 0;
        }

        $results = Database::getAll("
            SELECT HOUR(visit_date) as hour, COUNT(*) as count
            FROM visitor_tracking
            WHERE visit_date >= DATE_SUB(NOW(), INTERVAL $days DAY)
            GROUP BY HOUR(visit_date)
        ");

        foreach ($results as $row) {
            $data[$row['hour']] = $row['count'];
        }

        return array_values($data);
    }

    /**
     * Exporter les statistiques en CSV
     */
    public static function exportCsv($period = 'daily', $filename = null)
    {
        if (!$filename) {
            $filename = "rapport_" . date('Y-m-d_H-i-s') . ".csv";
        }

        header('Content-Type: application/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // En-tête
        fputcsv($output, ['Rapport Statistique - ' . $period], ';');
        fputcsv($output, ['Généré le ' . date('d/m/Y H:i')], ';');
        fputcsv($output, [], ';');

        // Données
        $stats = self::getStats($period);
        
        if ($period === 'daily') {
            fputcsv($output, ['Date', 'Visiteurs Uniques', 'Total Visites'], ';');
        } else {
            fputcsv($output, ['Période', 'Visiteurs Uniques', 'Total Visites'], ';');
        }

        foreach ($stats as $row) {
            fputcsv($output, $row, ';');
        }

        fclose($output);
    }
}
?>
