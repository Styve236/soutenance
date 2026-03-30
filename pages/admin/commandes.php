<?php
/**
 * Gestion des Commandes Admin
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

// Protéger - Vérifier accès admin
if (!isset($_SESSION['admin_access']) || !$_SESSION['admin_access']) {
    redirect(BASE_URL . '/?page=admin');
}

// Récupérer toutes les commandes
$commandes = Database::getAll("
    SELECT c.*, u.nom, u.telephone, COUNT(ci.id) as nb_items 
    FROM commandes c 
    JOIN users u ON c.client_id = u.id 
    LEFT JOIN commande_items ci ON c.id = ci.commande_id 
    GROUP BY c.id 
    ORDER BY c.date_commande DESC
");

// Charger l'en-tête
require_once APP_PATH . '/views/header.php';
?>

<main class="container" style="padding: 30px 0;">
    <h2>Gestion des Commandes</h2>
    
    <div style="background: white; border-radius: 10px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                    <th style="padding: 15px; text-align: left;">Commande #</th>
                    <th style="padding: 15px; text-align: left;">Client</th>
                    <th style="padding: 15px; text-align: left;">Téléphone</th>
                    <th style="padding: 15px; text-align: left;">Articles</th>
                    <th style="padding: 15px; text-align: left;">Total</th>
                    <th style="padding: 15px; text-align: left;">Statut</th>
                    <th style="padding: 15px; text-align: left;">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $cmd): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px;"><strong>#<?php echo htmlspecialchars($cmd['id']); ?></strong></td>
                        <td style="padding: 15px;"><?php echo htmlspecialchars($cmd['nom']); ?></td>
                        <td style="padding: 15px;"><?php echo htmlspecialchars($cmd['telephone']); ?></td>
                        <td style="padding: 15px;"><?php echo intval($cmd['nb_items']); ?></td>
                        <td style="padding: 15px; font-weight: bold;"><?php echo formatPrice($cmd['total']); ?></td>
                        <td style="padding: 15px;">
                            <span style="display: inline-block; padding: 5px 10px; border-radius: 5px; font-size: 0.85rem; font-weight: bold;
                                background: <?php echo $cmd['statut'] === 'livree' ? '#d4edda' : (#f8d7da' ? '#cfe2ff'); ?>;
                                color: <?php echo $cmd['statut'] === 'livree' ? '#155724' : ($cmd['statut'] === 'annulee' ? '#721c24' : '#004085'); ?>;">
                                <?php echo ucfirst(htmlspecialchars($cmd['statut'])); ?>
                            </span>
                        </td>
                        <td style="padding: 15px;"><?php echo formatDate($cmd['date_commande']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once APP_PATH . '/views/footer.php'; ?>
