<?php
include('includes/db.php');
include('config.php');
include('includes/header.php');

// Sécurité : Si l'utilisateur n'est pas connecté ou si pas de données POST
if (!isset($_SESSION['user_id']) || !isset($_POST['total_final'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$total = mysqli_real_escape_string($conn, $_POST['total_final']);
$statut = 'en_attente';

// 1. Enregistrement de la commande dans la base de données
$sql = "INSERT INTO commandes (client_id, total, statut, date_commande) 
        VALUES ('$user_id', '$total', '$statut', NOW())";

if (mysqli_query($conn, $sql)) {
    $id_commande = mysqli_insert_id($conn);

    // 2. Préparation du message WhatsApp pour HonyHub (Le Livreur / L'Admin)
    $telephone_livreur = "237655052258"; // NUMÉRO DE RÉCEPTION DES COMMANDES
    $texte = "🐝 *Nouvelle Commande HonyHub* 🐝\n\n"
           . "Salut ! Je viens de passer la commande *#$id_commande* sur la plateforme.\n"
           . "👤 *Client* : " . $_SESSION['user_nom'] . "\n"
           . "💰 *Montant Total* : " . number_format($total, 0, ',', ' ') . " FCFA.\n\n"
           . "Merci d'organiser ma livraison de miel ! 🍯";
    $url_whatsapp = "https://wa.me/$telephone_livreur?text=" . urlencode($texte);

    // 3. On vide le panier après le succès
    unset($_SESSION['panier']);
} else {
    die("Erreur : " . mysqli_error($conn));
}
?>

<main class="container" style="text-align: center; padding: 60px 0;">
    <div style="background: white; max-width: 600px; margin: 0 auto; padding: 40px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
        <i class="fas fa-check-circle" style="font-size: 5rem; color: #27ae60;"></i>
        <h2 style="margin-top: 20px;">Commande reçue !</h2>
        <p>Votre numéro de commande est le <strong>#<?php echo $id_commande; ?></strong></p>
        
        <div style="margin-top: 30px; display: flex; flex-direction: column; gap: 15px;">
            <a href="generer_facture.php?id=<?php echo $id_commande; ?>" target="_blank" class="btn-login" style="background: #e74c3c; color: white; padding: 15px; border-radius: 8px; text-decoration: none;">
                <i class="fas fa-file-pdf"></i> Télécharger ma facture (PDF)
            </a>

            <a href="<?php echo $url_whatsapp; ?>" target="_blank" class="btn-whatsapp" style="background: #25D366; color: white; padding: 15px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 1.1em; transition: 0.3s; box-shadow: 0 4px 10px rgba(37, 211, 102, 0.4);">
                <i class="fab fa-whatsapp" style="font-size: 1.2em; margin-right: 5px;"></i> Finaliser ma commande par WhatsApp
            </a>

            <a href="index.php" style="color: #777; text-decoration: none; margin-top: 10px;">Retour à l'accueil</a>
        </div>
    </div>
</main>

<?php include('includes/footer.php'); ?>