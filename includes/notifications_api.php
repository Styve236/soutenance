<?php
// Système de notifications
include('includes/db.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non authentifié']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Créer une notification
if ($action == 'create') {
    $user_id_target = (int)$_POST['user_id'];
    $type = $_POST['type']; // 'order', 'delivery', 'review'
    $message = $_POST['message'];
    $data = json_encode($_POST['data'] ?? []);
    
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message, data, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iss", $user_id_target, $type, $message, $data);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
}

// Récupérer les notifications non lues
else if ($action == 'unread') {
    $stmt = $conn->prepare("
        SELECT id, type, message, created_at 
        FROM notifications 
        WHERE user_id = ? AND is_read = 0 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = array();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    echo json_encode($notifications);
    $stmt->close();
}

// Marquer comme lue
else if ($action == 'read') {
    $notification_id = (int)$_POST['notification_id'];
    
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    }
    $stmt->close();
}

// Récupérer toutes les notifications
else if ($action == 'all') {
    $stmt = $conn->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 20
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = array();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    echo json_encode($notifications);
    $stmt->close();
}
?>
