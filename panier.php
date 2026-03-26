<?php
include('includes/db.php');
include('config.php');
include('includes/header.php');

// Initialisation du panier
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Supprimer un article
if (isset($_GET['remove'])) {
    $id_plat = $_GET['remove'];
    unset($_SESSION['panier'][$id_plat]);
    header("Location: panier.php");
    exit();
}
?>

<main class="container">
    <h2 style="margin: 30px 0;"><i class="fas fa-shopping-cart"></i> Votre Panier</h2>

    <?php if (empty($_SESSION['panier'])): ?>
        <div style="text-align: center; padding: 50px; background: white; border-radius: 10px;">
            <p>Votre panier est vide. Les meilleures saveurs de Douala vous attendent !</p>
            <a href="index.php" class="btn-register" style="display:inline-block; margin-top:20px; text-decoration:none;">Parcourir les menus</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            
            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #eee; text-align: left;">
                            <th style="padding: 10px;">Plat</th>
                            <th>Prix</th>
                            <th>Qté</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sous_total = 0;
                        foreach ($_SESSION['panier'] as $id => $quantite): 
                            $res = mysqli_query($conn, "SELECT * FROM menus WHERE id = '$id'");
                            $plat = mysqli_fetch_assoc($res);
                            $ligne_total = $plat['prix'] * $quantite;
                            $sous_total += $ligne_total;
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px 10px;">
                                <strong><?php echo e($plat['nom_plat']); ?></strong>
                            </td>
                            <td><?php echo number_format($plat['prix'], 0, ',', ' '); ?> F</td>
                            <td><?php echo $quantite; ?></td>
                            <td><?php echo number_format($ligne_total, 0, ',', ' '); ?> F</td>
                            <td>
                                <a href="panier.php?remove=<?php echo $id; ?>" style="color: #e74c3c;"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <aside>
                <div style="background: #2f3542; color: white; padding: 25px; border-radius: 10px; position: sticky; top: 100px;">
                    <h3 style="margin-bottom: 20px; border-bottom: 1px solid #444; padding-bottom: 10px;">Résumé</h3>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Articles :</span>
                        <span><?php echo number_format($sous_total, 0, ',', ' '); ?> FCFA</span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Livraison (<?php echo $frais_livraison; ?>) :</span>
                        <span><?php echo number_format($frais_livraison, 0, ',', ' '); ?> FCFA</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-top: 20px; padding-top: 15px; border-top: 2px solid #555; font-size: 1.3rem; font-weight: bold;">
                        <span>TOTAL :</span>
                        <span style="color: #f1c40f;"><?php echo number_format($sous_total + $frais_livraison, 0, ',', ' '); ?> FCFA</span>
                    </div>

                    <form action="confirmation.php" method="POST" style="margin-top: 30px;">
                        <input type="hidden" name="total_final" value="<?php echo $sous_total + $frais_livraison; ?>">
                        <button type="submit" class="btn-register" style="width: 100%; border: none; padding: 15px; font-size: 1.1rem; cursor: pointer; background: #27ae60;">
                            COMMANDER MAINTENANT
                        </button>
                    </form>
                    
                    <p style="font-size: 0.8rem; text-align: center; margin-top: 15px; color: #ccc;">
                        Paiement à la livraison uniquement.
                    </p>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</main>

<?php include('includes/footer.php'); ?>