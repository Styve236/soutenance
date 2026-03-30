<?php
/**
 * API : Traffic Monitor (Données en direct)
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once APP_PATH . '/controllers/StatisticsController.php';

header('Content-Type: application/json');

$stats = StatisticsController::getLiveStats();

echo json_encode($stats);
?>
