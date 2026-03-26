<?php
// Gestion du panier persistant
include('includes/db.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non authentifié']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Ajouter au panier
if ($action == 'add') {
    $menu_id = (int)$_POST['menu_id'];
    $quantity = (int)$_POST['quantity'];
    
    $stmt = $conn->prepare("
        INSERT INTO panier (user_id, menu_id, quantity) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE quantity = quantity + ?
    ");
    $stmt->bind_param("iiii", $user_id, $menu_id, $quantity, $quantity);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur']);
    }
    $stmt->close();
}

// Récupérer le panier
else if ($action == 'get') {
    $stmt = $conn->prepare("
        SELECT p.*, m.nom_plat, m.prix, r.nom_resto 
        FROM panier p 
        JOIN menus m ON p.menu_id = m.id 
        JOIN restaurants r ON m.restaurant_id = r.id 
        WHERE p.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = array();
    $total = 0;
    
    while ($row = $result->fetch_assoc()) {
        $row['subtotal'] = $row['prix'] * $row['quantity'];
        $total += $row['subtotal'];
        $items[] = $row;
    }
    
    echo json_encode([
        'items' => $items,
        'total' => $total
    ]);
    $stmt->close();
}

// Supprimer du panier
else if ($action == 'remove') {
    $panier_id = (int)$_POST['panier_id'];
    
    $stmt = $conn->prepare("DELETE FROM panier WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $panier_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    }
    $stmt->close();
}

// Vider le panier
else if ($action == 'clear') {
    $stmt = $conn->prepare("DELETE FROM panier WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    }
    $stmt->close();
}
?>
