<?php
/**
 * Aperçu Admin / Informations Système
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

// Protéger - Vérifier accès admin
if (!isset($_SESSION['admin_access']) || !$_SESSION['admin_access']) {
    redirect(BASE_URL . '/?page=admin');
}

// Récupérer les stats système
$users = Database::getOne("SELECT COUNT(*) as count FROM users");
$restaurants = Database::getOne("SELECT COUNT(*) as count FROM restaurants");
$commandes = Database::getOne("SELECT COUNT(*) as count FROM commandes");
$menus = Database::getOne("SELECT COUNT(*) as count FROM menus");

// Charger l'en-tête
require_once APP_PATH . '/views/header.php';
?>

<main class="container" style="padding: 30px 0;">
    <h2>Aperçu Système</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h4 style="color: #666; margin-bottom: 10px;">Utilisateurs</h4>
            <p style="font-size: 2rem; font-weight: bold; color: #3498db;">
                <?php echo intval($users['count']); ?>
            </p>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h4 style="color: #666; margin-bottom: 10px;">Restaurants</h4>
            <p style="font-size: 2rem; font-weight: bold; color: #27ae60;">
                <?php echo intval($restaurants['count']); ?>
            </p>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h4 style="color: #666; margin-bottom: 10px;">Commandes Totales</h4>
            <p style="font-size: 2rem; font-weight: bold; color: #e74c3c;">
                <?php echo intval($commandes['count']); ?>
            </p>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h4 style="color: #666; margin-bottom: 10px;">Menus Disponibles</h4>
            <p style="font-size: 2rem; font-weight: bold; color: #f39c12;">
                <?php echo intval($menus['count']); ?>
            </p>
        </div>
    </div>

    <!-- Informations Système -->
    <div style="background: white; padding: 20px; border-radius: 10px;">
        <h3>Informations Système</h3>
        <div style="margin-top: 20px;">
            <p><strong>Version Application :</strong> <?php echo APP_VERSION; ?></p>
            <p><strong>Environnement :</strong> <?php echo APP_ENV; ?></p>
            <p><strong>PHP Version :</strong> <?php echo PHP_VERSION; ?></p>
            <p><strong>Base de Données :</strong> <?php echo DB_NAME; ?> @ <?php echo DB_HOST; ?></p>
            <p><strong>URL de Base :</strong> <?php echo BASE_URL; ?></p>
        </div>
    </div>
</main>

<?php require_once APP_PATH . '/views/footer.php'; ?>
