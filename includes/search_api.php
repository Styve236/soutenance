<?php
// API de recherche avec autocomplete
include('includes/db.php');

header('Content-Type: application/json');

$search = $_GET['q'] ?? '';
$type = $_GET['type'] ?? 'restaurants'; // restaurants, menus, plats

if (strlen($search) < 2) {
    echo json_encode([]);
    exit();
}

$search = '%' . $search . '%';

if ($type == 'restaurants') {
    $stmt = $conn->prepare("
        SELECT id, nom_resto as name, quartier, image_logo 
        FROM restaurants 
        WHERE nom_resto LIKE ? OR quartier LIKE ?
        LIMIT 10
    ");
    $stmt->bind_param("ss", $search, $search);
}
else if ($type == 'menus') {
    $stmt = $conn->prepare("
        SELECT m.id, m.nom_plat as name, m.prix, r.nom_resto as restaurant, m.image_plat 
        FROM menus m 
        JOIN restaurants r ON m.restaurant_id = r.id 
        WHERE m.nom_plat LIKE ? OR m.description_plat LIKE ?
        LIMIT 10
    ");
    $stmt->bind_param("ss", $search, $search);
}
else if ($type == 'plats') {
    $stmt = $conn->prepare("
        SELECT m.id, m.nom_plat as name, m.prix, m.categorie, r.nom_resto as restaurant
        FROM menus m 
        JOIN restaurants r ON m.restaurant_id = r.id 
        WHERE m.nom_plat LIKE ? OR m.categorie LIKE ?
        LIMIT 10
    ");
    $stmt->bind_param("ss", $search, $search);
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    
    $results = array();
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    
    echo json_encode($results);
    $stmt->close();
}
?>
