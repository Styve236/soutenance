<?php
/**
 * Dashboard Admin
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

// Protéger - Vérifier le code admin
if (isset($_POST['admin_code'])) {
    if ($_POST['admin_code'] === ADMIN_CODE) {
        $_SESSION['admin_access'] = true;
    } else {
        $error = "Code d'accès incorrect";
    }
}

if (!isset($_SESSION['admin_access']) || !$_SESSION['admin_access']) {
    // Formulaire d'accès admin
    require_once APP_PATH . '/views/header.php';
    ?>
    <main class="container" style="max-width: 400px; margin-top: 50px;">
        <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <h2 style="text-align: center; margin-bottom: 20px;">Accès Admin</h2>
            <?php if (isset($error)): ?>
                <div style="background-color: #fee; color: #c00; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <input type="password" name="admin_code" placeholder="Code d'accès" required 
                       style="width:100%; padding:12px; margin-bottom:20px; box-sizing:border-box; border: 1px solid #ddd; border-radius: 5px;">
                <button type="submit" style="width:100%; border:none; padding:15px; cursor:pointer; background: #ff6b35; color: white; border-radius: 5px; font-weight: bold;">
                    Accéder
                </button>
            </form>
        </div>
    </main>
    <?php
    require_once APP_PATH . '/views/footer.php';
    exit;
}

// Charger l'en-tête
require_once APP_PATH . '/views/header.php';
require_once APP_PATH . '/controllers/StatisticsController.php';

// Récupérer les statistiques
$today = StatisticsController::getStats('today');
$week = StatisticsController::getStats('week');
$month = StatisticsController::getStats('month');
$live = StatisticsController::getLiveStats();
?>

<main class="container" style="padding: 30px 0;">
    <h2>Dashboard Admin</h2>
    
    <!-- Cartes de statistiques -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Visites d'aujourd'hui -->
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h4 style="color: #666; margin-bottom: 10px;">Visites Aujourd'hui</h4>
            <p style="font-size: 2rem; font-weight: bold; color: var(--primary-color);">
                <?php echo $today['visits'] ?? 0; ?>
            </p>
        </div>
        
        <!-- Utilisateurs cette semaine -->
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h4 style="color: #666; margin-bottom: 10px;">Nouveaux Utilisateurs (7 jours)</h4>
            <p style="font-size: 2rem; font-weight: bold; color: #27ae60;">
                <?php echo $week['new_users'] ?? 0; ?>
            </p>
        </div>
        
        <!-- Commandes ce mois -->
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h4 style="color: #666; margin-bottom: 10px;">Commandes (30 jours)</h4>
            <p style="font-size: 2rem; font-weight: bold; color: #3498db;">
                <?php echo $month['orders'] ?? 0; ?>
            </p>
        </div>
        
        <!-- Utilisateurs en ligne -->
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h4 style="color: #666; margin-bottom: 10px;">Actuellement en ligne</h4>
            <p style="font-size: 2rem; font-weight: bold; color: #e74c3c;">
                <?php echo $live['current_visitors'] ?? 0; ?>
            </p>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
        <a href="<?php echo BASE_URL; ?>/?page=admin-commandes" style="background: var(--primary-color); color: white; padding: 12px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;">
            <i class="fas fa-list"></i> Gérer Commandes
        </a>
        <a href="<?php echo BASE_URL; ?>/?page=traffic-monitor" style="background: #27ae60; color: white; padding: 12px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;">
            <i class="fas fa-chart-line"></i> Traffic Monitor
        </a>
        <a href="<?php echo AUTH_PATH; ?>/logout.php" style="background: #e74c3c; color: white; padding: 12px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>

    <!-- Graphiques -->
    <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
        <h3>Statistiques Détaillées</h3>
        <div id="chart"></div>
    </div>
</main>

<?php require_once APP_PATH . '/views/footer.php'; ?>
