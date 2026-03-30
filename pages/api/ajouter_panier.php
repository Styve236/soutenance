<?php
/**
 * API : Ajouter au Panier
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['menu_id']) || !isset($data['prix'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

// Initialiser le panier si vide
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Ajouter au panier
$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ($item['menu_id'] == $data['menu_id']) {
        $item['quantite']++;
        $found = true;
        break;
    }
}

if (!$found) {
    $_SESSION['cart'][] = [
        'menu_id' => $data['menu_id'],
        'nom' => $data['nom'] ?? 'Article',
        'prix' => $data['prix'],
        'quantite' => $data['quantite'] ?? 1
    ];
}

echo json_encode([
    'success' => true,
    'message' => 'Article ajouté',
    'cart_items' => count($_SESSION['cart'])
]);
?>
