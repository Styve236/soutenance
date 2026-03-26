<?php
// Indispensable pour que le serveur se souvienne du panier
session_start();

if (isset($_GET['id'])) {
    $id_plat = $_GET['id'];

    // Si le panier n'existe pas encore, on l'initialise
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = array();
    }

    // Si le plat est déjà dans le panier, on augmente la quantité
    if (isset($_SESSION['panier'][$id_plat])) {
        $_SESSION['panier'][$id_plat]++;
    } else {
        // Sinon, on l'ajoute avec une quantité de 1
        $_SESSION['panier'][$id_plat] = 1;
    }
}

// Redirige l'utilisateur vers le panier pour lui montrer que ça a marché
header("Location: panier.php");
exit();