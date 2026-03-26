<?php
// Dashboard Admin avec Statistiques
include('includes/db.php');
include('includes/header.php');

// Vérification du code d'accès
$access_granted = false;
$access_code = "mention"; // Code secret pour accéder à l'aperçu admin

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_preview_access']);
    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_POST['admin_code'])) {
    if ($_POST['admin_code'] === $access_code) {
        $access_granted = true;
        $_SESSION['admin_preview_access'] = true;
    } else {
        $error_msg = "Code d'accès incorrect.";
    }
} elseif (isset($_SESSION['admin_preview_access']) && $_SESSION['admin_preview_access'] === true) {
    $access_granted = true;
}

// Récupérer les statistiques
$stats = array();

if ($access_granted) {
    // Nombre total de restaurants
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM restaurants");
    $stats['restaurants'] = mysqli_fetch_assoc($result)['total'];
    
    // Nombre total de menus
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM menus");
    $stats['menus'] = mysqli_fetch_assoc($result)['total'];
    
    // Nombre total d'utilisateurs
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
    $stats['users'] = mysqli_fetch_assoc($result)['total'];
    
    // Nombre total de commandes
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM commandes");
    $stats['commandes'] = mysqli_fetch_assoc($result)['total'];
    
    // Revenu total
    $result = mysqli_query($conn, "SELECT SUM(total) as revenue FROM commandes");
    $rev = mysqli_fetch_assoc($result);
    $stats['revenue'] = $rev['revenue'] ? $rev['revenue'] : 0;
    
    // Commandes du jour
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM commandes WHERE DATE(date_commande) = CURDATE()");
    $stats['today_orders'] = mysqli_fetch_assoc($result)['total'];
    
    // Restaurant le plus populaire
    $result = mysqli_query($conn, "
        SELECT r.nom_resto, COUNT(c.id) as order_count 
        FROM restaurants r 
        LEFT JOIN menus m ON r.id = m.restaurant_id 
        LEFT JOIN commandes c ON m.id = c.menu_id 
        GROUP BY r.id 
        ORDER BY order_count DESC 
        LIMIT 1
    ");
    $popular = mysqli_fetch_assoc($result);
    $stats['popular_restaurant'] = $popular ? $popular['nom_resto'] : 'N/A';
    $stats['popular_count'] = $popular ? $popular['order_count'] : 0;
    
    // Dernier menu commandé
    $result = mysqli_query($conn, "SELECT m.nom_plat, r.nom_resto FROM commandes c JOIN menus m ON c.menu_id = m.id JOIN restaurants r ON m.restaurant_id = r.id ORDER BY c.date_commande DESC LIMIT 1");
    $last = mysqli_fetch_assoc($result);
    $stats['last_order'] = $last ? $last['nom_plat'] . ' de ' . $last['nom_resto'] : 'Aucune';
}
?>

<main class="container" style="padding-top: 20px;">
    <?php if (!$access_granted && !isset($_SESSION['user_id'])): ?>
        <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 40px; text-align: center; max-width: 500px; margin: 0 auto;">
            <h2 style="margin-bottom: 20px; color: var(--secondary-color);"><i class="fas fa-lock"></i> Accès Admin</h2>
            <p style="margin-bottom: 30px; color: #666;">Entrez le code d'accès pour accéder au tableau de bord administrateur.</p>

            <?php if(isset($error_msg)): ?>
                <div style="background: #e74c3c; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                <input type="password" name="admin_code" placeholder="Code d'accès" required
                       style="padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px; text-align: center;">
                <button type="submit" style="padding: 12px; background: var(--primary-color); color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-key"></i> Accéder
                </button>
            </form>
        </div>
    <?php elseif (isset($_SESSION['user_id']) && !$access_granted): ?>
        <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 40px; text-align: center; max-width: 500px; margin: 0 auto;">
            <h2 style="margin-bottom: 20px; color: #e74c3c;"><i class="fas fa-lock"></i> Accès Refusé</h2>
            <p style="margin-bottom: 30px; color: #666;">Vous êtes connecté, mais vous n'avez pas accès au tableau de bord administrateur.</p>
            <a href="index.php" style="color: white; background: var(--primary-color); padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; font-weight: 600;">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
        </div>
    <?php else: ?>
        <div style="background: var(--secondary-color); color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin:0;"><i class="fas fa-chart-line"></i> Tableau de Bord Admin</h2>
                <small>Vue d'ensemble des statistiques</small>
            </div>
            <a href="admin_dashboard.php?logout=1" style="color: white; text-decoration: none; border: 1px solid white; padding: 5px 10px; border-radius: 5px; font-weight: 600;">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>

        <!-- Statistiques -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <!-- Card 1: Restaurants -->
            <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 20px; text-align: center; border-top: 4px solid var(--primary-color);">
                <i class="fas fa-utensils" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 10px; display: block;"></i>
                <h3 style="color: var(--secondary-color); margin: 0;"><?php echo $stats['restaurants']; ?></h3>
                <p style="color: #666; margin: 5px 0;">Restaurants</p>
            </div>

            <!-- Card 2: Menus -->
            <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 20px; text-align: center; border-top: 4px solid var(--accent-color);">
                <i class="fas fa-list" style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 10px; display: block;"></i>
                <h3 style="color: var(--secondary-color); margin: 0;"><?php echo $stats['menus']; ?></h3>
                <p style="color: #666; margin: 5px 0;">Menus</p>
            </div>

            <!-- Card 3: Utilisateurs -->
            <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 20px; text-align: center; border-top: 4px solid #3498db;">
                <i class="fas fa-users" style="font-size: 2.5rem; color: #3498db; margin-bottom: 10px; display: block;"></i>
                <h3 style="color: var(--secondary-color); margin: 0;"><?php echo $stats['users']; ?></h3>
                <p style="color: #666; margin: 5px 0;">Utilisateurs</p>
            </div>

            <!-- Card 4: Commandes -->
            <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 20px; text-align: center; border-top: 4px solid #27ae60;">
                <i class="fas fa-shopping-cart" style="font-size: 2.5rem; color: #27ae60; margin-bottom: 10px; display: block;"></i>
                <h3 style="color: var(--secondary-color); margin: 0;"><?php echo $stats['commandes']; ?></h3>
                <p style="color: #666; margin: 5px 0;">Commandes Totales</p>
            </div>

            <!-- Card 5: Revenu -->
            <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 20px; text-align: center; border-top: 4px solid #9b59b6;">
                <i class="fas fa-money-bill-wave" style="font-size: 2.5rem; color: #9b59b6; margin-bottom: 10px; display: block;"></i>
                <h3 style="color: var(--secondary-color); margin: 0;"><?php echo number_format($stats['revenue'], 0, ',', ' '); ?> FCFA</h3>
                <p style="color: #666; margin: 5px 0;">Revenu Total</p>
            </div>

            <!-- Card 6: Commandes Aujourd'hui -->
            <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 20px; text-align: center; border-top: 4px solid #e67e22;">
                <i class="fas fa-calendar-day" style="font-size: 2.5rem; color: #e67e22; margin-bottom: 10px; display: block;"></i>
                <h3 style="color: var(--secondary-color); margin: 0;"><?php echo $stats['today_orders']; ?></h3>
                <p style="color: #666; margin: 5px 0;">Commandes Aujourd'hui</p>
            </div>
        </div>

        <!-- Informations supplémentaires -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <!-- Restaurant Populaire -->
            <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 20px;">
                <h3 style="color: var(--secondary-color); margin-bottom: 15px;"><i class="fas fa-crown" style="color: #f39c12;"></i> Restaurant Populaire</h3>
                <p style="font-size: 1.3rem; font-weight: bold; color: var(--primary-color); margin-bottom: 5px;"><?php echo $stats['popular_restaurant']; ?></p>
                <p style="color: #666;">Commandes: <strong><?php echo $stats['popular_count']; ?></strong></p>
            </div>

            <!-- Dernier Ordre -->
            <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 20px;">
                <h3 style="color: var(--secondary-color); margin-bottom: 15px;"><i class="fas fa-clock" style="color: #3498db;"></i> Dernier Ordre</h3>
                <p style="font-size: 1.1rem; font-weight: bold; color: var(--secondary-color);"><?php echo $stats['last_order']; ?></p>
            </div>

            <!-- Actions Rapides -->
            <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 20px;">
                <h3 style="color: var(--secondary-color); margin-bottom: 15px;"><i class="fas fa-link"></i> Actions Rapides</h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="admin_preview.php" style="background: var(--primary-color); color: white; padding: 8px 12px; border-radius: 5px; text-decoration: none; text-align: center; font-weight: 600;">
                        <i class="fas fa-cog"></i> Gérer les Données
                    </a>
                    <a href="index.php" style="background: #95a5a6; color: white; padding: 8px 12px; border-radius: 5px; text-decoration: none; text-align: center; font-weight: 600;">
                        <i class="fas fa-home"></i> Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>

    <?php endif; ?>
</main>

<?php include('includes/footer.php'); ?>
