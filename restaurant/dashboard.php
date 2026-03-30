<?php
include('../includes/db.php');
include('../config.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// SÉCURITÉ : On vérifie si l'utilisateur est connecté et s'il est un RESTAURATEUR
// On suppose que dans ta table 'users', tu as un rôle 'restaurant'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'restaurant') {
    header("Location: ../auth/login.php?error=Acces réservé aux restaurateurs");
    exit();
}

$user_id = $_SESSION['user_id'];

// On récupère les infos du restaurant lié à cet utilisateur
$query_resto = mysqli_query($conn, "SELECT * FROM restaurants WHERE proprietaire_id = '$user_id'");
$mon_resto = mysqli_fetch_assoc($query_resto);
$resto_id = $mon_resto['id'];

// --- LOGIQUE : AJOUT D'UN PLAT ---
if (isset($_POST['ajouter_plat'])) {
    $nom = mysqli_real_escape_string($conn, $_POST['nom_plat']);
    $prix = mysqli_real_escape_string($conn, $_POST['prix']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Gestion de l'image du plat
    $image = $_FILES['image']['name'];
    $target = "../assets/images/plats/" . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    mysqli_query($conn, "INSERT INTO plats (restaurant_id, nom_plat, prix, description, image_plat) 
                        VALUES ('$resto_id', '$nom', '$prix', '$desc', '$image')");
    $msg = "Le plat a été ajouté avec succès !";
}

// --- RÉCUPÉRATION DES DONNÉES ---
// 1. Les plats du restaurant
$plats = mysqli_query($conn, "SELECT * FROM plats WHERE restaurant_id = '$resto_id'");

// 2. Les commandes récentes pour ce restaurant uniquement
$commandes = mysqli_query($conn, "SELECT c.*, u.nom as client_nom 
                                 FROM commandes c 
                                 JOIN users u ON c.client_id = u.id 
                                 WHERE c.restaurant_id = '$resto_id' 
                                 ORDER BY c.date_commande DESC LIMIT 10");

include('../includes/header.php'); 
?>

<main class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Tableau de bord : <?php echo e($mon_resto['nom_resto']); ?></h1>
        <span style="background: #27ae60; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9em;">Ouvert</span>
    </div>

    <?php if(isset($msg)): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        
        <section>
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin:0;">Mes Plats au Menu</h3>
                    <button onclick="document.getElementById('modalPlat').style.display='block'" style="background: var(--primary-color); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">
                        + Ajouter un plat
                    </button>
                </div>

                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid #eee;">
                            <th style="padding: 10px;">Image</th>
                            <th>Nom</th>
                            <th>Prix</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($p = mysqli_fetch_assoc($plats)): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;">
                                <img src="../assets/images/plats/<?php echo $p['image_plat']; ?>" style="width: 50px; height: 50px; border-radius: 5px; object-fit: cover;">
                            </td>
                            <td><strong><?php echo e($p['nom_plat']); ?></strong></td>
                            <td><?php echo number_format($p['prix'], 0, ',', ' '); ?> FCFA</td>
                            <td>
                                <a href="#" style="color: #e74c3c; text-decoration: none;"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <div style="background: #2f3542; color: white; padding: 20px; border-radius: 12px;">
                <h3 style="margin-bottom: 20px;">Dernières Commandes</h3>
                <?php if(mysqli_num_rows($commandes) > 0): ?>
                    <?php while($c = mysqli_fetch_assoc($commandes)): ?>
                    <div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 8px; margin-bottom: 10px; font-size: 0.9em;">
                        <div style="display: flex; justify-content: space-between;">
                            <strong>#<?php echo $c['id']; ?> - <?php echo e($c['client_nom']); ?></strong>
                            <span style="color: #f1c40f;"><?php echo $c['statut']; ?></span>
                        </div>
                        <div style="margin-top: 5px; color: #ccc;">Total: <?php echo $c['total']; ?> FCFA</div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #ccc; font-style: italic;">Aucune commande pour le moment.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div id="modalPlat" style="display:none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6);">
        <div style="background: white; width: 400px; margin: 100px auto; padding: 30px; border-radius: 12px; position: relative;">
            <span onclick="this.parentElement.parentElement.style.display='none'" style="position: absolute; right: 20px; top: 10px; cursor: pointer; font-size: 1.5rem;">&times;</span>
            <h3>Nouveau Plat</h3>
            <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
                <input type="text" name="nom_plat" placeholder="Nom du plat (ex: Ndolé Royal)" required style="padding: 10px;">
                <input type="number" name="prix" placeholder="Prix en FCFA" required style="padding: 10px;">
                <textarea name="description" placeholder="Description du plat..." style="padding: 10px; height: 80px;"></textarea>
                <label>Image du plat :</label>
                <input type="file" name="image" required>
                <button type="submit" name="ajouter_plat" style="background: var(--primary-color); color: white; border: none; padding: 12px; border-radius: 5px; font-weight: bold; cursor: pointer;">
                    ENREGISTRER LE PLAT
                </button>
            </form>
        </div>
    </div>
</main>

<?php include('../includes/footer.php'); ?>