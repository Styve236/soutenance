<?php
/**
 * Header Layout - Nouvelle version structurée
 */

// Charger bootstrap
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../bootstrap.php';
}

// Charger les contrôleurs
require_once APP_PATH . '/controllers/UserController.php';
require_once APP_PATH . '/controllers/StatisticsController.php';

// Vérifier la session
Auth::verifySession();

// Enregistrer la visite
if (APP_ENV === 'development' || true) { // Toujours enregistrer les visites
    StatisticsController::recordVisit();
}

// Vérifier le rôle de l'utilisateur
$isAdmin = Auth::isAdmin();
$user = Auth::check() ? Auth::user() : null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> | Livraison à Douala</title>

    <!-- Styles CSS -->
    <link rel="stylesheet" href="<?php echo assetUrl('css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo assetUrl('css/forms.css'); ?>">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f39c12;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .main-header {
            background: var(--white);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .main-header nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo h1 {
            color: var(--primary-color);
            font-size: 1.8em;
        }

        .logo span {
            color: var(--secondary-color);
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 20px;
            align-items: center;
        }

        .nav-links a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .btn-login,
        .btn-register {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-login {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-register {
            background: var(--primary-color);
            color: white;
        }

        .header-spacer {
            height: 80px;
        }
    </style>
</head>
<body>

<header class="main-header">
    <nav class="container">
        <div class="logo">
            <a href="<?php echo pageUrl('index.php'); ?>">
                <h1><?php echo APP_NAME; ?><span>Hub</span></h1>
            </a>
        </div>

        <ul class="nav-links">
            <li><a href="<?php echo pageUrl('index.php'); ?>"><i class="fas fa-utensils"></i> Miels</a></li>

            <?php if (Auth::check()): ?>
                <li><a href="<?php echo pageUrl('panier.php'); ?>"><i class="fas fa-shopping-basket"></i> Panier</a></li>
                <li><a href="<?php echo pageUrl('mes_commandes.php'); ?>"><i class="fas fa-list"></i> Mes Achats</a></li>

                <?php if ($isAdmin): ?>
                    <li><a href="<?php echo pageUrl('admin_dashboard.php'); ?>"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                    <li><a href="<?php echo pageUrl('traffic_monitor.php'); ?>"><i class="fas fa-wifi"></i> Monitoring</a></li>
                <?php endif; ?>

                <li style="position: relative;">
                    <a href="#" onclick="toggleMenu()">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars(substr($user['nom'], 0, 20)); ?>
                        <i class="fas fa-caret-down"></i>
                    </a>
                    <ul id="user-menu" style="display: none; position: absolute; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); list-style: none; min-width: 180px; top: 100%; right: 0; border-radius: 5px; overflow: hidden;">
                        <li><a href="<?php echo pageUrl('mes_commandes.php'); ?>" style="padding: 12px 15px; display: block; text-decoration: none;"><i class="fas fa-list"></i> Mes Achats</a></li>
                        <li><a href="<?php echo pageUrl('auth/logout.php'); ?>" style="padding: 12px 15px; display: block; text-decoration: none; color: var(--danger-color);"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="<?php echo pageUrl('auth/login.php'); ?>" class="btn-login">Connexion</a></li>
                <li><a href="<?php echo pageUrl('auth/register.php'); ?>" class="btn-register">S'inscrire</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div class="header-spacer"></div>

<script>
function toggleMenu() {
    const menu = document.getElementById('user-menu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}
</script>
