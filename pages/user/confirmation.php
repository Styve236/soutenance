<?php
/**
 * Page de Confirmation de Commande
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

// Sécurité : Utilisateur doit être connecté
Auth::protect();

// Vérifier si les données POST existent
if (!isset($_POST['total_final'])) {
    redirect(BASE_URL . '/?page=panier');
}

$user_id = Auth::user()['id'];
$total = $_POST['total_final'] ?? 0;
$panier = $_SESSION['cart'] ?? [];

// Créer la commande
$sql = "INSERT INTO commandes (client_id, total, statut, date_commande) 
        VALUES ($user_id, " . Database::escape($total) . ", 'en_attente', NOW())";

$result = Database::execute($sql);

if ($result) {
    $commande_id = Database::getLastId();
    
    // Enregistrer les articles de la commande
    if (!empty($panier)) {
        foreach ($panier as $item) {
            $item_sql = "INSERT INTO commande_items (commande_id, menu_id, quantite, prix_unitaire) 
                        VALUES ($commande_id, " . intval($item['menu_id']) . ", " . intval($item['quantite']) . ", " . Database::escape($item['prix']) . ")";
            Database::execute($item_sql);
        }
    }
    
    // Vider le panier
    unset($_SESSION['cart']);
    
    // Enregistrer dans les stats
    StatisticsController::recordVisit('order_completed');
} else {
    redirect(BASE_URL . '/?page=panier');
}

// Charger l'en-tête
require_once APP_PATH . '/views/header.php';
?>

<main class="container" style="text-align: center; padding: 60px 0;">
    <div style="background: white; max-width: 600px; margin: 0 auto; padding: 40px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
        <i class="fas fa-check-circle" style="font-size: 5rem; color: #27ae60;"></i>
        <h2 style="margin-top: 20px;">Commande reçue !</h2>
        <p>Votre numéro de commande est le <strong>#<?php echo htmlspecialchars($commande_id); ?></strong></p>
        <p style="color: #666; margin-top: 10px;">Montant total : <strong><?php echo formatPrice($total); ?></strong></p>
        
        <div style="margin-top: 30px; display: flex; flex-direction: column; gap: 15px;">
            <a href="<?php echo BASE_URL; ?>/?page=commandes" class="btn-login" style="background: #3498db; color: white; padding: 15px; border-radius: 8px; text-decoration: none;">
                <i class="fas fa-list"></i> Mes Commandes
            </a>

            <a href="<?php echo BASE_URL; ?>/?page=accueil" style="color: #777; text-decoration: none; margin-top: 10px;">Retour à l'accueil</a>
        </div>
    </div>
</main>

<?php require_once APP_PATH . '/views/footer.php'; ?>
