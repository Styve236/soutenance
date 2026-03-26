<?php
// On démarre la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connexion à la base de données (nécessaire pour vérifier le rôle admin ici)
include('db.php');

// Vérification du rôle admin pour l'affichage du menu
$is_admin = false;
if (isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];
    $res_role = mysqli_query($conn, "SELECT role FROM users WHERE id = '$u_id'");
    $user_row = mysqli_fetch_assoc($res_role);
    if ($user_row && $user_row['role'] === 'admin') {
        $is_admin = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Douala Eats | Livraison de repas à Douala</title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Petit ajout de style pour le menu déroulant (dropdown) */
        .nav-links li { position: relative; }
        .dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            list-style: none;
            border-radius: 5px;
            min-width: 180px;
            z-index: 1001;
        }
        .user-menu:hover .dropdown { display: block; }
        .dropdown li { margin: 0; border-bottom: 1px solid #eee; }
        .dropdown li a { padding: 12px 15px; display: block; font-size: 0.9em; }
        .dropdown li a:hover { background: #f8f9fa; color: var(--primary-color); }
        .badge-admin { background: var(--primary-color); color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.7em; margin-left: 5px; vertical-align: middle; }
    </style>
</head>
<body>

<header class="main-header">
    <nav class="container">
        <div class="logo">
            <a href="index.php">
                <h1>Douala<span>Eats</span></h1>
            </a>
        </div>

        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="search-container">
                <input type="text" id="search-input" class="search-input" placeholder="Chercher un restaurant, un plat...">
                <div id="search-results"></div>
            </div>
        <?php endif; ?>

        <ul class="nav-links">
            <li><a href="index.php"><i class="fas fa-utensils"></i> Restaurants</a></li>

            <!-- Case Admin Preview -->
            <li style="display: flex; align-items: center;">
                <label style="display: flex; align-items: center; cursor: pointer; font-size: 0.9em;">
                    <input type="checkbox" id="admin-preview-toggle" style="margin-right: 5px;">
                    <i class="fas fa-eye"></i> Aperçu Admin
                </label>
            </li>

            <?php if(isset($_SESSION['user_id'])): ?>
                <li style="position: relative;">
                    <a href="#" id="notification-icon" style="position: relative;">
                        <i class="fas fa-bell"></i>
                    </a>
                </li>
                
                <li><a href="panier.php"><i class="fas fa-shopping-basket"></i> Panier</a></li>
                
                <li class="user-menu">
                    <a href="#" style="cursor: pointer;">
                        <i class="fas fa-user-circle"></i> 
                        <?php echo explode(' ', $_SESSION['user_nom'])[0]; // Affiche le premier nom ?>
                        <?php if($is_admin) echo '<span class="badge-admin">Admin</span>'; ?>
                        <i class="fas fa-caret-down"></i>
                    </a>
                    <ul class="dropdown">
                        <?php if($is_admin): ?>
                            <li><a href="admin_commandes.php"><i class="fas fa-tasks"></i> Gestion Commandes</a></li>
                            <li><a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                            <li><a href="admin_preview.php"><i class="fas fa-eye"></i> Aperçu Admin</a></li>
                        <?php endif; ?>
                        <li><a href="mes_commandes.php"><i class="fas fa-list"></i> Mes achats</a></li>
                        <li><a href="auth/logout.php" style="color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="auth/login.php" class="btn-login">Connexion</a></li>
                <li><a href="auth/register.php" class="btn-register">S'inscrire</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div class="header-spacer"></div>