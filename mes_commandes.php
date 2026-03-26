<?php 
include('includes/db.php'); 
include('includes/header.php'); 

// 1. SÉCURITÉ : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php?error=Veuillez vous connecter pour voir vos commandes");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. RÉCUPÉRATION : On récupère les commandes de cet utilisateur spécifique
$query = "SELECT * FROM commandes WHERE client_id = '$user_id' ORDER BY date_commande DESC";
$result = mysqli_query($conn, $query);
?>

<main class="container">
    <div style="margin: 30px 0;">
        <h2><i class="fas fa-shopping-bag"></i> Mes Commandes</h2>
        <p>Suivez l'état de vos plats en temps réel.</p>
    </div>

    <?php if(mysqli_num_rows($result) > 0): ?>
        <div style="display: grid; gap: 20px;">
            <?php while($commande = mysqli_fetch_assoc($result)): ?>
                <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 5px solid var(--primary-color);">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                        <div>
                            <h3 style="margin: 0;">Commande #<?php echo $commande['id']; ?></h3>
                            <small style="color: #777;"><?php echo date('d/m/Y à H:i', strtotime($commande['date_commande'])); ?></small>
                        </div>
                        
                        <div style="text-align: right;">
                            <?php 
                                // Gestion des couleurs et textes des statuts
                                $status_text = "Reçue";
                                $status_color = "#ffeaa7"; // Jaune
                                $icon = "fa-clock";

                                switch($commande['statut']) {
                                    case 'en_preparation':
                                        $status_text = "En préparation";
                                        $status_color = "#fab1a0"; // Orange
                                        $icon = "fa-fire-burner";
                                        break;
                                    case 'en_livraison':
                                        $status_text = "En cours de livraison";
                                        $status_color = "#81ecec"; // Bleu
                                        $icon = "fa-motorcycle";
                                        break;
                                    case 'livre':
                                        $status_text = "Livrée";
                                        $status_color = "#55efc4"; // Vert
                                        $icon = "fa-check-double";
                                        break;
                                }
                            ?>
                            <span style="display: inline-block; padding: 8px 15px; border-radius: 20px; background: <?php echo $status_color; ?>; font-weight: bold; font-size: 0.9em;">
                                <i class="fas <?php echo $icon; ?>"></i> <?php echo strtoupper($status_text); ?>
                            </span>
                        </div>
                    </div>

                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #eee; display: flex; justify-content: space-between;">
                        <span>Montant total :</span>
                        <span style="font-weight: bold;"><?php echo number_format($commande['total'], 0, ',', ' '); ?> FCFA</span>
                    </div>

                    <?php if($commande['statut'] == 'en_livraison'): ?>
                        <div style="margin-top: 15px; background: #f1f2f6; padding: 10px; border-radius: 5px; font-size: 0.9em;">
                            <i class="fas fa-info-circle" style="color: var(--primary-color);"></i> Notre livreur est en route ! Gardez votre téléphone à proximité.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; background: white; padding: 50px; border-radius: 10px;">
            <i class="fas fa-box-open" style="font-size: 4em; color: #ccc; margin-bottom: 20px;"></i>
            <p>Vous n'avez pas encore passé de commande.</p>
            <a href="index.php" class="btn-register" style="display: inline-block; margin-top: 15px; text-decoration: none;">Commander mon premier repas</a>
        </div>
    <?php endif; ?>
</main>

<?php include('includes/footer.php'); ?>