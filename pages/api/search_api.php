<?php
/**
 * API : Recherche
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$search_term = '%' . Database::escape($query) . '%';

$restaurants = Database::getAll("
    SELECT id, nom_resto as name, 'restaurant' as type 
    FROM restaurants 
    WHERE nom_resto LIKE '$search_term' 
    LIMIT 10
");

$menus = Database::getAll("
    SELECT id, nom_plat as name, 'menu' as type 
    FROM menus 
    WHERE nom_plat LIKE '$search_term' 
    LIMIT 10
");

$results = array_merge($restaurants, $menus);

echo json_encode($results);
?>
