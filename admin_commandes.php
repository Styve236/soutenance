<?php 
// 1. Connexion et démarrage de session
include('includes/db.php'); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** * SÉCURITÉ ADMIN SPÉCIFIQUE
 * L'ID doit être 1 (ou 01) et on vérifie une clé d'accès ou un rôle
 */
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php?error=Connexion requise");
    exit();
}

$user_id = $_SESSION['user_id'];
$access_code = "mamacheri"; // Ton code secret

// On récupère les infos de l'utilisateur connecté
$check_user = mysqli_query($conn, "SELECT id, role FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($check_user);

/**
 * CONDITION D'ACCÈS :
 * 1. L'ID doit être 1
 * 2. Le rôle doit être 'admin'
 * Note : Pour le code "mamacheri", on pourrait l'utiliser comme mot de passe 
 * ou vérifier une variable de session spécifique.
 */
if (!$user_data || $user_data['id'] != 1 || $user_data['role'] !== 'admin') {
    header("Location: index.php?error=Acces interdit : ID ou Code incorrect");
    exit();
}

// 2. LOGIQUE : Mise à jour du statut (le reste du code reste fonctionnel)
if (isset($_POST['update_status'])) {
    $commande_id = mysqli_real_escape_string($conn, $_POST['commande_id']);
    $nouveau_statut = mysqli_real_escape_string($conn, $_POST['nouveau_statut']);
    
    mysqli_query($conn, "UPDATE commandes SET statut = '$nouveau_statut' WHERE id = '$commande_id'");
    $success_msg = "Statut mis à jour !";
}

// 3. RÉCUPÉRATION DES COMMANDES
$query = "SELECT c.*, u.nom, u.telephone, u.adresse 
          FROM commandes c 
          JOIN users u ON c.client_id = u.id 
          ORDER BY c.date_commande DESC";
$result = mysqli_query($conn, $query);

include('includes/header.php'); 
?>

<main class="container" style="padding-top: 20px;">
    <div style="background: #2f3542; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="margin:0;">Session Admin #01</h2>
            <small>Code d'accès : Validé (<?php echo $access_code; ?>)</small>
        </div>
        <a href="admin_commandes.php" style="color: white; text-decoration: none; border: 1px solid white; padding: 5px 10px; border-radius: 5px;">
            <i class="fas fa-sync"></i>
        </a>
    </div>

    <?php if(isset($success_msg)): ?>
        <div style="background: #27ae60; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <div style="background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
            <thead>
                <tr style="background: #f1f2f6; text-align: left;">
                    <th style="padding: 15px;">Commande</th>
                    <th>Client</th>
                    <th>Contact & WhatsApp</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px;"><strong>#<?php echo $row['id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($row['nom']); ?></td>
                    <td>
                        <a href="tel:<?php echo $row['telephone']; ?>" style="color: blue; text-decoration: none;">
                            <i class="fas fa-phone"></i> <?php echo $row['telephone']; ?>
                        </a>
                        <a href="https://wa.me/237<?php echo $row['telephone']; ?>" target="_blank" style="margin-left: 10px; color: #25D366;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </td>
                    <td style="font-weight: bold;"><?php echo number_format($row['total'], 0, ',', ' '); ?> FCFA</td>
                    <td>
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8em; background: #eee;">
                            <?php echo strtoupper($row['statut']); ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" style="display: flex; gap: 5px;">
                            <input type="hidden" name="commande_id" value="<?php echo $row['id']; ?>">
                            <select name="nouveau_statut">
                                <option value="en_attente" <?php if($row['statut'] == 'en_attente') echo 'selected'; ?>>En attente</option>
                                <option value="en_preparation" <?php if($row['statut'] == 'en_preparation') echo 'selected'; ?>>Préparation</option>
                                <option value="en_livraison" <?php if($row['statut'] == 'en_livraison') echo 'selected'; ?>>Livraison</option>
                                <option value="livre" <?php if($row['statut'] == 'livre') echo 'selected'; ?>>Livré</option>
                            </select>
                            <button type="submit" name="update_status" style="cursor:pointer; background:#2f3542; color:white; border:none; padding: 5px;">OK</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include('includes/footer.php'); ?>