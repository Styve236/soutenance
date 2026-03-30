<?php
session_start();
// Gestion des avis
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $restaurant_id = (int)$_POST['restaurant_id'];
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $rating = (int)$_POST['rating'];
    $comment = $_POST['comment'];
    
    if ($user_id > 0 && $rating >= 1 && $rating <= 5) {
        try {
            $stmt = $conn->prepare("INSERT INTO avis (restaurant_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("iis", $restaurant_id, $user_id, $rating, $comment);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Avis enregistré avec succès!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur BDD: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Données invalides ou utilisateur non connecté']);
    }
    exit();
}

// Récupérer les avis
if (isset($_GET['restaurant_id'])) {
    $restaurant_id = (int)$_GET['restaurant_id'];
    
    try {
        $stmt = $conn->prepare("
            SELECT a.*, u.nom AS user_nom, u.email AS user_email 
            FROM avis a 
            JOIN users u ON a.user_id = u.id 
            WHERE a.restaurant_id = ? 
            ORDER BY a.created_at DESC
        ");
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param("i", $restaurant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $avis = array();
        while ($row = $result->fetch_assoc()) {
            $avis[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode($avis);
        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}
?>
