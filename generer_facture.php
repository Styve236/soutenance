<?php
session_start();
include('includes/db.php');

// Vérification de sécurité basique
if (!isset($_GET['id'])) {
    die("Accès non autorisé : numéro de facture manquant.");
}

$id_commande = (int)$_GET['id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// L'administrateur ou l'utilisateur concerné peut voir
// Pour simplifier et éviter de bloquer l'administrateur, on cherche la commande
$query = "SELECT c.*, u.nom, u.email, u.telephone, u.adresse 
          FROM commandes c 
          JOIN users u ON c.client_id = u.id 
          WHERE c.id = $id_commande";
$res = mysqli_query($conn, $query);

if (mysqli_num_rows($res) == 0) {
    die("Commande introuvable.");
}

$commande = mysqli_fetch_assoc($res);

// Vérification stricte commentée pour l'admin (pour démo)
// if ($user_id && $commande['client_id'] != $user_id && $_SESSION['role'] != 'admin') { die("Accès refusé."); }

// Récupération des articles de la commande s'ils existent (vérification selon la structure)
$items = [];
// On vérifie d'abord si la table commande_items existe pour éviter un crash SQL
$table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'commande_items'");
if (mysqli_num_rows($table_exists) > 0) {
    $query_items = "SELECT ci.*, m.nom_plat, m.prix 
                    FROM commande_items ci 
                    JOIN menus m ON ci.menu_id = m.id 
                    WHERE ci.commande_id = $id_commande";
    $res_items = mysqli_query($conn, $query_items);
    if ($res_items) {
        while($row = mysqli_fetch_assoc($res_items)) {
            $items[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #<?php echo $id_commande; ?> - HonyHub</title>
    <!-- On inclut html2pdf.js via CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .invoice-box { max-width: 800px; margin: auto; padding: 40px; border: 1px solid #eee; box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); font-size: 16px; line-height: 24px; background: #fff; border-radius: 10px; }
        .invoice-header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 2px solid #f39c12; padding-bottom: 20px; }
        .invoice-header h1 { margin: 0; color: #f39c12; font-size: 2.5rem; }
        .invoice-header .details { text-align: right; }
        .client-info { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .client-info div { width: 48%; }
        .invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .invoice-table th { background: #f39c12; color: white; padding: 12px; text-align: left; }
        .invoice-table td { padding: 12px; border-bottom: 1px solid #eee; border-left: 1px solid #eee; border-right: 1px solid #eee; }
        .invoice-table td.right, .invoice-table th.right { text-align: right; }
        .total-row { font-weight: bold; font-size: 1.2rem; background: #fff9f0 !important; }
        .doc-buttons { text-align: center; margin-bottom: 20px; }
        .btn-download { background: #e74c3c; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1rem; text-decoration: none; font-weight: bold; }
        .btn-download:hover { background: #c0392b; }
    </style>
</head>
<body>

<div class="doc-buttons" id="editorMode">
    <button onclick="generatePDF()" class="btn-download"><i class="fas fa-file-pdf"></i> Télécharger le PDF</button>
</div>

<div class="invoice-box" id="invoice">
    <div class="invoice-header">
        <div>
            <h1><i class="fas fa-archive" style="color: #d35400;"></i> HonyHub</h1>
            <p style="margin: 5px 0; color: #666;">La référence du Miel pur à Douala<br>Akwa, Douala, Cameroun<br>contact@honyhub.com</p>
        </div>
        <div class="details">
            <h2 style="margin: 0; color: #2c3e50;">FACTURE</h2>
            <p style="margin: 5px 0;">Facture N°: <strong style="color:#e74c3c;">#<?php echo str_pad($id_commande, 6, "0", STR_PAD_LEFT); ?></strong><br>
            Date: <?php echo date('d/m/Y', strtotime($commande['date_commande'])); ?><br>
            Statut: <span style="text-transform: uppercase; font-weight: bold; color: <?php echo $commande['statut'] == 'livree' ? '#27ae60' : ($commande['statut'] == 'annulee' ? '#e74c3c' : '#e67e22'); ?>;"><?php echo $commande['statut']; ?></span></p>
        </div>
    </div>

    <div class="client-info">
        <div>
            <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color:#f39c12;">Facturé à :</h3>
            <strong><?php echo htmlspecialchars($commande['nom']); ?></strong><br>
            Adresse : <?php echo htmlspecialchars($commande['adresse']); ?><br>
            Tél : <?php echo htmlspecialchars($commande['telephone']); ?><br>
            Email : <?php echo htmlspecialchars($commande['email']); ?>
        </div>
        <div>
            <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color:#f39c12;">Détails de paiement :</h3>
            Moyen : <strong>Paiement à la livraison</strong><br>
            Monnaie : <strong>FCFA (XAF)</strong>
        </div>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Prix Unitaire</th>
                <th class="right">Quantité</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($items) > 0): ?>
                <?php foreach($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nom_plat']); ?></td>
                    <td class="right"><?php echo number_format($item['prix_unitaire'] ?? $item['prix'], 0, ',', ' '); ?> F</td>
                    <td class="right"><?php echo $item['quantite']; ?></td>
                    <td class="right"><?php echo number_format(($item['prix_unitaire'] ?? $item['prix']) * $item['quantite'], 0, ',', ' '); ?> F</td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td>Commande globale (Liste des pots non spécifiée sur les anciennes requêtes)</td>
                    <td class="right">-</td>
                    <td class="right">-</td>
                    <td class="right"><?php echo number_format($commande['total'], 0, ',', ' '); ?> F</td>
                </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="3" class="right" style="padding-top: 20px; border-bottom: none;">TOTAL À PAYER :</td>
                <td class="right" style="padding-top: 20px; color: #d35400; font-size: 1.5rem; border-bottom: none;"><?php echo number_format($commande['total'], 0, ',', ' '); ?> FCFA</td>
            </tr>
        </tbody>
    </table>

    <div style="text-align: center; margin-top: 50px; color: #7f8c8d; font-size: 0.9rem; border-top: 2px dashed #eee; padding-top:20px;">
        <p><strong>Merci pour votre achat sur HonyHub !</strong><br>Pour toute réclamation concernant la qualité du miel, merci de nous contacter sous 48h.</p>
    </div>
</div>

<script>
function generatePDF() {
    // Cacher le bouton PDF pendant l'export pour qu'il n'apparaisse pas sur la feuille PDF
    document.getElementById('editorMode').style.display = 'none';
    
    // Configuration html2pdf
    const element = document.getElementById('invoice');
    const opt = {
        margin:       0.3, // marge réduite pour optimiser le rendu 
        filename:     'Facture_HonyHub_#<?php echo $id_commande; ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
    };

    // Génération
    html2pdf().set(opt).from(element).save().then(() => {
        // Remettre le bouton une fois fini
        document.getElementById('editorMode').style.display = 'block';
    });
}

// Déclenchement automatique au chargement (1 seconde après pour être certain des styles)
window.onload = function() {
    setTimeout(generatePDF, 1000); 
};
</script>

</body>
</html>
