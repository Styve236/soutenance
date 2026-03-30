<?php
/**
 * Page du Panier
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

// Charger l'en-tête
require_once APP_PATH . '/views/header.php';

// Récupérer le panier de la session
$panier = $_SESSION['cart'] ?? [];
$total = 0;

// Calculer le total
foreach ($panier as $item) {
    $total += $item['prix'] * $item['quantite'];
}
?>

<main class="container" style="padding: 30px 0;">
    <h2>Mon Panier</h2>
    
    <?php if (empty($panier)): ?>
        <div style="text-align: center; padding: 60px; background: white; border-radius: 10px;">
            <i class="fas fa-shopping-cart" style="font-size: 4rem; color: #ccc;"></i>
            <p style="margin-top: 20px; color: #666;">Votre panier est vide</p>
            <a href="<?php echo BASE_URL; ?>/?page=accueil" style="display: inline-block; margin-top: 15px; background: var(--primary-color); color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none;">
                Retour aux restaurants
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px; max-width: 1200px;">
            <!-- Détails des articles -->
            <div>
                <div style="background: white; border-radius: 10px; overflow: hidden;">
                    <?php foreach ($panier as $index => $item): ?>
                        <div style="padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4><?php echo htmlspecialchars($item['nom']); ?></h4>
                                <p style="color: #666; font-size: 0.9rem;">Quantité: <?php echo intval($item['quantite']); ?></p>
                            </div>
                            <div style="text-align: right;">
                                <p style="font-weight: bold;"><?php echo formatPrice($item['prix'] * $item['quantite']); ?></p>
                                <form method="POST" style="margin-top: 10px;">
                                    <input type="hidden" name="remove_item" value="<?php echo $index; ?>">
                                    <button type="submit" style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 0.8rem;">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Résumé de la commande -->
            <div style="background: white; padding: 20px; border-radius: 10px; height: fit-content;">
                <h3>Résumé</h3>
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Sous-total</span>
                        <span><?php echo formatPrice($total); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Livraison</span>
                        <span>Gratuit</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.2rem; padding-top: 10px; border-top: 1px solid #eee;">
                        <span>Total</span>
                        <span><?php echo formatPrice($total); ?></span>
                    </div>
                </div>
                
                <form method="POST" action="<?php echo BASE_URL; ?>/?page=confirmation" style="margin-top: 20px;">
                    <input type="hidden" name="total_final" value="<?php echo $total; ?>">
                    <button type="submit" style="width: 100%; background: var(--primary-color); color: white; border: none; padding: 15px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1rem;">
                        <i class="fas fa-lock"></i> Confirmer la commande
                    </button>
                </form>
                
                <a href="<?php echo BASE_URL; ?>/?page=accueil" style="display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none;">
                    Continuer les achats
                </a>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once APP_PATH . '/views/footer.php'; ?>
