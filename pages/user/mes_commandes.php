<?php
/**
 * Page des Commandes de l'Utilisateur
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

// Protéger la route
Auth::protect();

// Récupérer les commandes de l'utilisateur
$user_id = Auth::user()['id'];
$commandes = Database::getAll("
    SELECT c.*, COUNT(ci.id) as nb_items 
    FROM commandes c 
    LEFT JOIN commande_items ci ON c.id = ci.commande_id 
    WHERE c.client_id = $user_id 
    GROUP BY c.id 
    ORDER BY c.date_commande DESC
");

// Charger l'en-tête
require_once APP_PATH . '/views/header.php';
?>

<main class="container" style="padding: 30px 0;">
    <h2>Mes Commandes</h2>
    
    <?php if (empty($commandes)): ?>
        <div style="text-align: center; padding: 60px; background: white; border-radius: 10px;">
            <i class="fas fa-history" style="font-size: 4rem; color: #ccc;"></i>
            <p style="margin-top: 20px; color: #666;">Vous n'avez pas encore de commande</p>
            <a href="<?php echo BASE_URL; ?>/?page=accueil" style="display: inline-block; margin-top: 15px; background: var(--primary-color); color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none;">
                Commencer une commande
            </a>
        </div>
    <?php else: ?>
        <div style="background: white; border-radius: 10px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                        <th style="padding: 15px; text-align: left;">Commande #</th>
                        <th style="padding: 15px; text-align: left;">Date</th>
                        <th style="padding: 15px; text-align: left;">Articles</th>
                        <th style="padding: 15px; text-align: left;">Total</th>
                        <th style="padding: 15px; text-align: left;">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $commande): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px;">#<?php echo htmlspecialchars($commande['id']); ?></td>
                            <td style="padding: 15px;"><?php echo formatDate($commande['date_commande']); ?></td>
                            <td style="padding: 15px;"><?php echo intval($commande['nb_items']); ?> article(s)</td>
                            <td style="padding: 15px; font-weight: bold;"><?php echo formatPrice($commande['total']); ?></td>
                            <td style="padding: 15px;">
                                <span style="display: inline-block; padding: 5px 10px; border-radius: 5px; font-size: 0.85rem; font-weight: bold;
                                    background: <?php echo $commande['statut'] === 'livree' ? '#d4edda' : ($commande['statut'] === 'annulee' ? '#f8d7da' : '#cfe2ff'); ?>;
                                    color: <?php echo $commande['statut'] === 'livree' ? '#155724' : ($commande['statut'] === 'annulee' ? '#721c24' : '#004085'); ?>;">
                                    <?php echo ucfirst(htmlspecialchars($commande['statut'])); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php require_once APP_PATH . '/views/footer.php'; ?>
