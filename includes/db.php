<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "douala_eats"; // <--- VERIFIE BIEN LE NOM ICI

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("La connexion a échoué : " . mysqli_connect_error());
}

// Auto-création de la table notifications pour le système d'alertes HonyHub
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(255) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Patch de sécurité : si la table 'notifications' existait déjà avant dans la BDD sans la colonne 'date_creation'
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM notifications LIKE 'date_creation'");
if ($check_col && mysqli_num_rows($check_col) == 0) {
    mysqli_query($conn, "ALTER TABLE notifications ADD COLUMN date_creation DATETIME DEFAULT CURRENT_TIMESTAMP");
}
// Auto-création de la table avis pour les notations
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");
?>