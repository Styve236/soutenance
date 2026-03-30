<?php
ob_start(); // Empêche l'erreur 'Headers already sent' due aux redirections après le header.php
include('includes/db.php');

// ----- MOTEUR D'EXPORTATION CSV/EXCEL -----
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    // Vide complètement les tampons pour éviter d'exporter le HTML accidentellement
    ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=HonyHub_' . ucfirst($type) . '_' . date('Y-m-d') . '.csv');
    
    // Ajout du BOM UTF-8 pour que Microsoft Excel reconnaisse bien les accents (é, à, etc.)
    echo "\xEF\xBB\xBF"; 
    $output = fopen('php://output', 'w');
    
    if ($type === 'commandes') {
        fputcsv($output, array('ID', 'Client', 'Total (FCFA)', 'Statut', 'Date'), ';');
        $res = mysqli_query($conn, "SELECT c.id, u.nom, c.total, c.statut, c.date_commande FROM commandes c JOIN users u ON c.client_id = u.id ORDER BY c.id DESC");
        while ($row = mysqli_fetch_assoc($res)) {
            fputcsv($output, array($row['id'], $row['nom'], $row['total'], $row['statut'], $row['date_commande']), ';');
        }
    } elseif ($type === 'clients') {
        fputcsv($output, array('ID', 'Nom', 'Email', 'Telephone', 'Role'), ';');
        $res = mysqli_query($conn, "SELECT id, nom, email, telephone, role FROM users ORDER BY id DESC");
        while ($row = mysqli_fetch_assoc($res)) {
            fputcsv($output, array($row['id'], $row['nom'], $row['email'], $row['telephone'], $row['role']), ';');
        }
    }
    
    fclose($output);
    exit();
}
// ------------------------------------------

// Aperçu admin avec code d'accès requis
include('includes/header.php');

// Vérification stricte du rôle Admin
$is_real_admin = false;
if (isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];
    $check_admin = mysqli_query($conn, "SELECT role FROM users WHERE id = '$u_id'");
    $user_admin = mysqli_fetch_assoc($check_admin);
    if ($user_admin && $user_admin['role'] === 'admin') {
        $is_real_admin = true;
    }
}

if (!$is_real_admin) {
    // Redirection immédiate et silencieuse si l'utilisateur n'est pas un VRAI admin
    header("Location: index.php");
    exit();
}

// Rétrocompatibilité : laisse le code existant s'exécuter
$access_granted = true;

// Gestion CRUD Restaurants
if ($access_granted) {
    // Ajouter un restaurant
    if (isset($_POST['add_restaurant'])) {
        $nom_resto = $_POST['nom_resto'];
        $quartier = $_POST['quartier'];
        $description = $_POST['description'];
        $image_logo = '';
        
        // Gestion de l'image du logo
        if (isset($_FILES['image_logo']) && $_FILES['image_logo']['error'] == 0) {
            $target_dir = "assets/images/restos/";
            $file_extension = pathinfo($_FILES['image_logo']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image_logo']['tmp_name'], $target_file)) {
                $image_logo = $file_name;
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO restaurants (nom_resto, quartier, description, image_logo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nom_resto, $quartier, $description, $image_logo);
        
        if ($stmt->execute()) {
            $success_msg = "Boutique ajoutée avec succès !";
        } else {
            $error_msg = "Erreur lors de l'ajout de la boutique.";
        }
        $stmt->close();
    }

    // Modifier un restaurant
    if (isset($_POST['edit_restaurant'])) {
        $id = (int)$_POST['id'];
        $nom_resto = $_POST['nom_resto'];
        $quartier = $_POST['quartier'];
        $description = $_POST['description'];
        $image_logo = '';
        
        // Gestion de l'image du logo
        if (isset($_FILES['image_logo']) && $_FILES['image_logo']['error'] == 0) {
            $target_dir = "assets/images/restos/";
            $file_extension = pathinfo($_FILES['image_logo']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image_logo']['tmp_name'], $target_file)) {
                $image_logo = $file_name;
            }
        }
        
        if ($image_logo != '') {
            $stmt = $conn->prepare("UPDATE restaurants SET nom_resto=?, quartier=?, description=?, image_logo=? WHERE id=?");
            $stmt->bind_param("ssssi", $nom_resto, $quartier, $description, $image_logo, $id);
        } else {
            $stmt = $conn->prepare("UPDATE restaurants SET nom_resto=?, quartier=?, description=? WHERE id=?");
            $stmt->bind_param("sssi", $nom_resto, $quartier, $description, $id);
        }
        
        if ($stmt->execute()) {
            $success_msg = "Boutique modifiée avec succès !";
        } else {
            $error_msg = "Erreur lors de la modification de la boutique.";
        }
        $stmt->close();
    }

    // Supprimer un restaurant
    if (isset($_GET['delete_restaurant'])) {
        $id = (int)$_GET['delete_restaurant'];
        try {
            // On tente de supprimer les menus associés d'abord
            mysqli_query($conn, "DELETE FROM menus WHERE restaurant_id = $id");

            $stmt = $conn->prepare("DELETE FROM restaurants WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $success_msg = "Boutique supprimée avec succès !";
            } else {
                $error_msg = "Erreur lors de la suppression de la boutique.";
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $error_msg = "Impossible de supprimer cette boutique : elle est liée à des commandes passées (Historique).";
        }
    }

    // Gestion CRUD Menus
    // Ajouter un menu
    if (isset($_POST['add_menu'])) {
        $nom_plat = $_POST['nom_plat'];
        $restaurant_id = (int)$_POST['restaurant_id'];
        $prix = (float)$_POST['prix'];
        $categorie = $_POST['categorie'];
        $description_plat = $_POST['description_plat'];
        $image_plat = '';
        
        if (isset($_FILES['image_plat']) && $_FILES['image_plat']['error'] == 0) {
            $target_dir = "assets/images/plats/";
            $file_extension = pathinfo($_FILES['image_plat']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES['image_plat']['tmp_name'], $target_file)) {
                $image_plat = $file_name;
            }
        }

        $stmt = $conn->prepare("INSERT INTO menus (nom_plat, restaurant_id, prix, categorie, description_plat, image_plat) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidsss", $nom_plat, $restaurant_id, $prix, $categorie, $description_plat, $image_plat);
        
        if ($stmt->execute()) {
            $success_msg = "Miel ajouté avec succès !";
            // Notification aux utilisateurs
            /*
            $notif_msg = "Nouveau produit disponible : " . $nom_plat . " !";
            $stmt_notif = $conn->prepare("INSERT INTO notifications (message) VALUES (?)");
            $stmt_notif->bind_param("s", $notif_msg);
            $stmt_notif->execute();
            */
        } 
       else {
            $error_msg = "Erreur lors de l'ajout du miel.";
        }
        $stmt->close();
    }

    // Modifier un menu
    if (isset($_POST['edit_menu'])) {
        $id = (int)$_POST['menu_id'];
        $nom_plat = $_POST['nom_plat'];
        $restaurant_id = (int)$_POST['restaurant_id'];
        $prix = (float)$_POST['prix'];
        $categorie = $_POST['categorie'];
        $description_plat = $_POST['description_plat'];
        $image_plat = '';

        if (isset($_FILES['image_plat']) && $_FILES['image_plat']['error'] == 0) {
            $target_dir = "assets/images/plats/";
            $file_extension = pathinfo($_FILES['image_plat']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES['image_plat']['tmp_name'], $target_file)) {
                $image_plat = $file_name;
            }
        }

        if ($image_plat != '') {
            $stmt = $conn->prepare("UPDATE menus SET nom_plat=?, restaurant_id=?, prix=?, categorie=?, description_plat=?, image_plat=? WHERE id=?");
            $stmt->bind_param("sidsssi", $nom_plat, $restaurant_id, $prix, $categorie, $description_plat, $image_plat, $id);
        } else {
            $stmt = $conn->prepare("UPDATE menus SET nom_plat=?, restaurant_id=?, prix=?, categorie=?, description_plat=? WHERE id=?");
            $stmt->bind_param("sidssi", $nom_plat, $restaurant_id, $prix, $categorie, $description_plat, $id);
        }
        
        if ($stmt->execute()) {
            $success_msg = "Miel modifié avec succès !";
        } else {
            $error_msg = "Erreur lors de la modification du miel.";
        }
        $stmt->close();
    }

    // Supprimer un menu
    if (isset($_GET['delete_menu'])) {
        $id = (int)$_GET['delete_menu'];
        try {
            $stmt = $conn->prepare("DELETE FROM menus WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $success_msg = "Miel supprimé avec succès !";
            } else {
                $error_msg = "Erreur lors de la suppression du miel.";
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $error_msg = "Impossible de supprimer ce miel : il apparaît dans des commandes passées.";
        }
    }

    // Supprimer un utilisateur (Client)
    if (isset($_GET['delete_user'])) {
        $id_to_delete = (int)$_GET['delete_user'];
        
        // Sécurité : On vérifie que l'on ne supprime pas un admin
        $check = mysqli_query($conn, "SELECT role FROM users WHERE id = $id_to_delete");
        $user_data = mysqli_fetch_assoc($check);
        
        if ($user_data && $user_data['role'] !== 'admin') {
            // Étape 1 : Retirer les éléments liés pour éviter l'erreur "Foreign Key Constraint"
            // Trouver toutes les commandes passées par ce client
            $cmd_query = mysqli_query($conn, "SELECT id FROM commandes WHERE client_id = $id_to_delete");
            if ($cmd_query) {
                // On vérifie d'abord si la table commande_items existe vraiment dans la DB !
                $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'commande_items'");
                $has_items_table = mysqli_num_rows($check_table) > 0;

                while($cmd = mysqli_fetch_assoc($cmd_query)) {
                    $c_id = $cmd['id'];
                    if ($has_items_table) {
                        mysqli_query($conn, "DELETE FROM commande_items WHERE commande_id = $c_id");
                    }
                }
            }
            // Étape 2 : Supprimer les commandes "parents" elles-mêmes
            mysqli_query($conn, "DELETE FROM commandes WHERE client_id = $id_to_delete");
            
            // Étape 3 (Bonus) : Supprimer les avis potentiels de ce client s'il y a une telle relation
            // mysqli_query($conn, "DELETE FROM avis WHERE user_id = $id_to_delete");

            // Étape Finale : Supprimer le client !
            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param("i", $id_to_delete);
            
            if ($stmt->execute()) {
                $success_msg = "Le client a été supprimé avec succès !";
                // Réinitialise le compteur d'ID auto-increment au plus bas possible pour la prochaine inscription
                mysqli_query($conn, "ALTER TABLE users AUTO_INCREMENT = 1");
            } else {
                $error_msg = "Erreur lors de la suppression du client.";
            }
            $stmt->close();
        } else {
            $error_msg = "Action refusée : Impossible de supprimer un compte Administrateur.";
        }
    }

    // Modifier le statut d'une commande
    if (isset($_POST['update_order_id']) && isset($_POST['new_status'])) {
        $order_id = (int)$_POST['update_order_id'];
        $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
        
        $stmt = $conn->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        if ($stmt->execute()) {
            $success_msg = "Le statut de la commande #$order_id est passé à : " . strtoupper($new_status);
        } else {
            $error_msg = "Erreur lors de la mise à jour du statut de la commande.";
        }
        $stmt->close();
    }
}
?>

<main class="container" style="padding-top: 20px;">
        <div style="background: var(--secondary-color); color: white; padding: 25px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <div>
                <h2 style="margin:0;"><i class="fas fa-user-shield" style="color: var(--primary-color);"></i> Tableau de bord Administration</h2>
                <small style="color: #ccc;">Session Admin : <?php echo htmlspecialchars($_SESSION['user_nom']); ?> (Accès Sécurisé)</small>
            </div>
            <a href="auth/logout.php" style="color: white; text-decoration: none; background: #e74c3c; padding: 10px 15px; border-radius: 5px; font-weight: bold; transition: 0.3s;">
                <i class="fas fa-power-off"></i> Déconnexion
            </a>
        </div>

        <?php if(isset($success_msg)): ?>
            <div style="background: #27ae60; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <span><?php echo $success_msg; ?></span>
                <button onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: white; cursor: pointer; font-size: 20px;">&times;</button>
            </div>
        <?php endif; ?>

        <?php
        // Statistiques de ventes pour les graphiques
        $salesDaily = [];
        $resD = mysqli_query($conn, "SELECT DATE_FORMAT(date_commande, '%d/%m') as label, SUM(total) as revenue, COUNT(*) as orders FROM commandes WHERE date_commande >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(date_commande) ORDER BY DATE(date_commande) ASC");
        if ($resD) while($r = mysqli_fetch_assoc($resD)) $salesDaily[] = $r;

        $salesWeekly = [];
        $resW = mysqli_query($conn, "SELECT CONCAT('Sem.', WEEK(date_commande)) as label, SUM(total) as revenue, COUNT(*) as orders FROM commandes WHERE date_commande >= DATE_SUB(NOW(), INTERVAL 4 WEEK) GROUP BY WEEK(date_commande) ORDER BY WEEK(date_commande) ASC");
        if ($resW) while($r = mysqli_fetch_assoc($resW)) $salesWeekly[] = $r;

        $salesMonthly = [];
        $resM = mysqli_query($conn, "SELECT DATE_FORMAT(date_commande, '%M %Y') as label, SUM(total) as revenue, COUNT(*) as orders FROM commandes WHERE date_commande >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY MONTH(date_commande), YEAR(date_commande) ORDER BY YEAR(date_commande) ASC, MONTH(date_commande) ASC");
        if ($resM) while($r = mysqli_fetch_assoc($resM)) $salesMonthly[] = $r;
        ?>



    <!-- Restaurants -->
    <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="shop-list-title"><i class="fas fa-store"></i> Boutiques / Apiculteurs</h3>
            <button onclick="toggleRestaurantForm()" style="background: var(--primary-color); color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: 600;">
                <i class="fas fa-plus"></i> Ajouter
            </button>
        </div>

        <!-- Formulaire Restaurant -->
        <div id="restaurant-form" style="display: none; background: var(--bg-color); padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #dee2e6;">
            <h4 id="shop-form-title"><i class="fas fa-plus"></i> Ajouter une Boutique</h4>
            <form method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; align-items: end;">
                <input type="hidden" id="restaurant-id" name="id">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Nom de la Boutique</label>
                    <input type="text" id="nom_resto" name="nom_resto" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Quartier</label>
                    <input type="text" id="quartier" name="quartier" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Logo de la Boutique (optionnel)</label>
                    <input type="file" id="image_logo" name="image_logo" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Catégorie</label>
                    <select id="categorie_resto" name="categorie" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="Apiculteur Local">Apiculteur Local</option>
                        <option value="Coopérative">Coopérative</option>
                        <option value="Grossiste">Grossiste</option>
                        <option value="Artisan">Artisan</option>
                    </select>
                </div>
                <div style="grid-column: span 2;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Description</label>
                    <textarea id="description" name="description" rows="3" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                </div>
                <div style="grid-column: span 2; display: flex; gap: 10px;">
                    <button type="submit" id="add-shop-btn" name="add_restaurant" style="background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-plus"></i> Ajouter
                    </button>
                    <button type="submit" id="edit-shop-btn" name="edit_restaurant" style="background: var(--accent-color); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; display: none; font-weight: 600;">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                    <button type="button" onclick="toggleRestaurantForm()" style="background: #95a5a6; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                </div>
            </form>
        </div>
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-color); text-align: left;">
                    <th style="padding: 10px;">ID</th>
                    <th>Nom</th>
                    <th>Note</th>
                    <th>Quartier</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res_restos = mysqli_query($conn, "SELECT r.*, (SELECT AVG(rating) FROM avis WHERE restaurant_id = r.id) as avg_rating FROM restaurants r ORDER BY id DESC");
                while($resto = mysqli_fetch_assoc($res_restos)):
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;"><?php echo $resto['id']; ?></td>
                    <td><?php echo htmlspecialchars($resto['nom_resto']); ?></td>
                    <td>
                        <span style="color: #f1c40f;">
                            <?php 
                            $rating = round($resto['avg_rating']);
                            echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                            ?>
                        </span>
                        <small>(<?php echo number_format($resto['avg_rating'], 1); ?>)</small>
                    </td>
                    <td><?php echo htmlspecialchars($resto['quartier']); ?></td>
                    <td><?php echo htmlspecialchars(substr($resto['description'], 0, 50)) . '...'; ?></td>
                    <td>
                        <?php
                        $args_resto = sprintf("%d, %s, %s, %s", 
                            $resto['id'], 
                            json_encode($resto['nom_resto'], JSON_HEX_APOS | JSON_HEX_QUOT), 
                            json_encode($resto['quartier'], JSON_HEX_APOS | JSON_HEX_QUOT), 
                            json_encode($resto['description'], JSON_HEX_APOS | JSON_HEX_QUOT)
                        );
                        ?>
                        <button type="button" onclick='editRestaurant(<?php echo $args_resto; ?>)' 
                            style="background: var(--accent-color); color:white; border:none; padding:5px 10px; border-radius:3px; cursor:pointer; margin-right:5px; font-weight: 600;">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        <a href="admin_preview.php?delete_restaurant=<?php echo $resto['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette boutique ?')" 
                            style="background: #e74c3c; color: white; padding: 5px 10px; border-radius: 3px; text-decoration: none; font-weight: 600;">
                            <i class="fas fa-trash"></i> Supprimer
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Menus -->
    <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3><i class="fas fa-list"></i> Produits / Miels</h3>
            <button onclick="toggleMenuForm()" style="background: var(--primary-color); color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: 600;">
                <i class="fas fa-plus"></i> Ajouter un Miel
            </button>
        </div>

        <!-- Formulaire Menu -->
        <div id="menu-form" style="display: none; background: var(--bg-color); padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #dee2e6;">
            <h4 id="menu-form-title"><i class="fas fa-plus"></i> Ajouter un Miel</h4>
            <form method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; align-items: end;">
                <input type="hidden" id="menu-id" name="menu_id">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Nom du Miel</label>
                    <input type="text" id="nom_plat" name="nom_plat" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Boutique</label>
                    <select id="restaurant_id" name="restaurant_id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">Choisir une boutique</option>
                        <?php
                        $res_restos_select = mysqli_query($conn, "SELECT id, nom_resto FROM restaurants ORDER BY nom_resto");
                        while($resto_select = mysqli_fetch_assoc($res_restos_select)):
                        ?>
                        <option value="<?php echo $resto_select['id']; ?>"><?php echo htmlspecialchars($resto_select['nom_resto']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Prix (FCFA)</label>
                    <input type="number" id="prix" name="prix" min="0" step="100" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Catégorie</label>
                    <select id="categorie" name="categorie" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="Miel Pur">Miel Pur</option>
                        <option value="Miel Blanc">Miel Blanc</option>
                        <option value="Propolis">Propolis</option>
                        <option value="Gelée Royale">Gelée Royale</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Image du Miel (optionnel)</label>
                    <input type="file" id="image_plat" name="image_plat" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Description du Miel</label>
                    <textarea id="description_plat" name="description_plat" rows="3" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                </div>
                <div style="grid-column: span 2; display: flex; gap: 10px;">
                    <button type="submit" id="add-menu-btn" name="add_menu" style="background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-plus"></i> Ajouter
                    </button>
                    <button type="submit" id="edit-menu-btn" name="edit_menu" style="background: var(--accent-color); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; display: none; font-weight: 600;">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                    <button type="button" onclick="toggleMenuForm()" style="background: #95a5a6; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                </div>
            </form>
        </div>

        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-color); text-align: left;">
                    <th style="padding: 10px;">ID</th>
                    <th>Miel</th>
                    <th>Boutique</th>
                    <th>Prix</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res_menus = mysqli_query($conn, "SELECT m.*, r.nom_resto FROM menus m JOIN restaurants r ON m.restaurant_id = r.id ORDER BY m.id DESC");
                while($menu = mysqli_fetch_assoc($res_menus)):
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;"><?php echo $menu['id']; ?></td>
                    <td><?php echo htmlspecialchars($menu['nom_plat']); ?></td>
                    <td><?php echo htmlspecialchars($menu['nom_resto']); ?></td>
                    <td><?php echo number_format($menu['prix'], 0, ',', ' '); ?> FCFA</td>
                    <td><?php echo htmlspecialchars(substr($menu['description_plat'], 0, 50)) . '...'; ?></td>
                    <td>
                        <?php
                        $args_menu = sprintf("%d, %s, %d, %d, %s, %s", 
                            $menu['id'], 
                            json_encode($menu['nom_plat'], JSON_HEX_APOS | JSON_HEX_QUOT), 
                            $menu['restaurant_id'], 
                            $menu['prix'], 
                            json_encode($menu['categorie'], JSON_HEX_APOS | JSON_HEX_QUOT), 
                            json_encode($menu['description_plat'], JSON_HEX_APOS | JSON_HEX_QUOT)
                        );
                        ?>
                        <button type="button" onclick='editMenu(<?php echo $args_menu; ?>)'
                            style="background: var(--accent-color); color:white; border:none; padding:5px 10px; border-radius:3px; cursor:pointer; margin-right:5px; font-weight: 600;">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        <a href="admin_preview.php?delete_menu=<?php echo $menu['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce miel ?')" style="background: #e74c3c; color: white; padding: 5px 10px; border-radius: 3px; text-decoration: none; font-weight: 600;">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Utilisateurs -->
    <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin:0;"><i class="fas fa-users"></i> Utilisateurs</h3>
            <a href="admin_preview.php?export=clients" style="background: #27ae60; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 0.9em;">
                <i class="fas fa-file-excel"></i> Exporter Liste Clients (CSV)
            </a>
        </div>
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-color); text-align: left;">
                    <th style="padding: 10px;">ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res_users = mysqli_query($conn, "SELECT id, nom, email, telephone, role FROM users ORDER BY id DESC");
                while($user = mysqli_fetch_assoc($res_users)):
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;"><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['nom']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['telephone']); ?></td>
                    <td><span style="font-weight: bold; color: <?php echo $user['role'] == 'admin' ? '#e74c3c' : '#2c3e50'; ?>;"><?php echo htmlspecialchars($user['role']); ?></span></td>
                    <td>
                        <?php if($user['role'] !== 'admin'): ?>
                            <a href="admin_preview.php?delete_user=<?php echo $user['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer définitivement le client <?php echo htmlspecialchars(addslashes($user['nom'])); ?> ?')" 
                                style="background: #e74c3c; color: white; padding: 5px 10px; border-radius: 3px; text-decoration: none; font-weight: 600; font-size: 0.9em;">
                                <i class="fas fa-trash"></i> Supprimer
                            </a>
                        <?php else: ?>
                            <span style="color: #999; font-size: 0.9em; font-style: italic;">Intouchable</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Commandes -->
    <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin:0;"><i class="fas fa-shopping-cart"></i> Commandes</h3>
            <a href="admin_preview.php?export=commandes" style="background: #27ae60; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 0.9em;">
                <i class="fas fa-file-excel"></i> Exporter Historique (CSV)
            </a>
        </div>
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-color); text-align: left;">
                    <th style="padding: 10px;">ID</th>
                    <th>Client</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res_commandes = mysqli_query($conn, "SELECT c.id, u.nom, c.total, c.statut, c.date_commande FROM commandes c JOIN users u ON c.client_id = u.id ORDER BY c.id DESC");
                while($commande = mysqli_fetch_assoc($res_commandes)):
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;"><?php echo $commande['id']; ?></td>
                    <td><?php echo htmlspecialchars($commande['nom']); ?></td>
                    <td><?php echo number_format($commande['total'], 0, ',', ' '); ?> FCFA</td>
                    <td>
                        <form method="POST" action="admin_preview.php" style="margin: 0;">
                            <input type="hidden" name="update_order_id" value="<?php echo $commande['id']; ?>">
                            <select name="new_status" onchange="this.form.submit()" 
                                style="padding: 6px; border-radius: 5px; border: 1px solid #ccc; font-weight: bold; cursor: pointer; outline: none;
                                background: <?php echo $commande['statut'] == 'livree' ? '#27ae60' : ($commande['statut'] == 'en_attente' ? '#f39c12' : ($commande['statut'] == 'annulee' ? '#e74c3c' : '#3498db')); ?>; 
                                color: white;">
                                <option style="background: white; color: black;" value="en_attente" <?php if($commande['statut'] == 'en_attente') echo 'selected'; ?>>En attente</option>
                                <option style="background: white; color: black;" value="en_cours" <?php if($commande['statut'] == 'en_cours') echo 'selected'; ?>>En cours</option>
                                <option style="background: white; color: black;" value="livree" <?php if($commande['statut'] == 'livree') echo 'selected'; ?>>Livrée</option>
                                <option style="background: white; color: black;" value="annulee" <?php if($commande['statut'] == 'annulee') echo 'selected'; ?>>Annulée</option>
                            </select>
                        </form>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Graphiques de Ventes -->
    <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 20px;">
            <h3 style="margin: 0; color: #2c3e50;"><i class="fas fa-chart-line"></i> Statistiques Financières (Achats)</h3>
            <div style="display: flex; gap: 10px;">
                <button onclick="updateChart('daily')" id="btn-daily" style="padding: 8px 15px; border: none; background: var(--primary-color); color: white; border-radius: 5px; cursor: pointer; font-weight: bold;">Jour</button>
                <button onclick="updateChart('weekly')" id="btn-weekly" style="padding: 8px 15px; border: none; background: #e0e0e0; color: #333; border-radius: 5px; cursor: pointer; font-weight: bold;">Semaine</button>
                <button onclick="updateChart('monthly')" id="btn-monthly" style="padding: 8px 15px; border: none; background: #e0e0e0; color: #333; border-radius: 5px; cursor: pointer; font-weight: bold;">Mois</button>
            </div>
        </div>
        <div style="position: relative; height: 350px; width: 100%;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
</main>

<!-- Librairie Simple-DataTables -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
<style>
/* Adaptation Simple-DataTables au design HonyHub & Dark Mode */
.dataTable-wrapper { font-size: 0.95em; }
.dataTable-table th a { text-decoration: none; color: inherit; }
body.dark-mode .dataTable-wrapper { color: var(--secondary-color); }
body.dark-mode .dataTable-input { background: var(--bg-color); color: var(--secondary-color); border: 1px solid #444; }
body.dark-mode .dataTable-selector { background: var(--bg-color); color: var(--secondary-color); border: 1px solid #444; }
body.dark-mode .dataTable-info { color: #999; }
body.dark-mode .dataTable-pagination a { color: var(--secondary-color); border-color: #444; }
body.dark-mode .dataTable-pagination a:hover { background: var(--primary-color); color: white; border-color: var(--primary-color); }
body.dark-mode .dataTable-pagination .active a { background: var(--primary-color); color: white; border-color: var(--primary-color); }
body.dark-mode .dataTable-table th { background: var(--bg-color); border-bottom: 2px solid #444; }
body.dark-mode .dataTable-table td { border-bottom: 1px solid rgba(255,255,255,0.1); }
</style>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const dataTablesElements = document.querySelectorAll('.data-table');
    dataTablesElements.forEach(table => {
        new simpleDatatables.DataTable(table, {
            searchable: true,
            fixedHeight: false,
            perPageSelect: [5, 10, 15, 20],
            labels: {
                placeholder: "Rechercher...",
                perPage: "lignes par page",
                noRows: "Aucun résultat trouvé",
                info: "Affichage {start} à {end} sur {rows} total"
            }
        });
    });
});
</script>

<script>
function toggleRestaurantForm() {
    const form = document.getElementById('restaurant-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    if (form.style.display === 'block') {
        document.getElementById('add-btn').style.display = 'block';
        document.getElementById('edit-btn').style.display = 'none';
        document.getElementById('nom_resto').value = '';
        document.getElementById('quartier').value = '';
        document.getElementById('description').value = '';
        document.getElementById('restaurant-id').value = '';
    }
}

function editRestaurant(id, nom, quartier, description) {
    document.getElementById('restaurant-form').style.display = 'block';
    document.getElementById('restaurant-id').value = id;
    document.getElementById('nom_resto').value = nom;
    document.getElementById('quartier').value = quartier;
    document.getElementById('description').value = description;
    document.getElementById('add-btn').style.display = 'none';
    document.getElementById('edit-btn').style.display = 'block';
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function toggleMenuForm() {
    const form = document.getElementById('menu-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    if (form.style.display === 'block') {
        document.getElementById('add-menu-btn').style.display = 'block';
        document.getElementById('edit-menu-btn').style.display = 'none';
        document.getElementById('nom_plat').value = '';
        document.getElementById('restaurant_id').value = '';
        document.getElementById('prix').value = '';
        document.getElementById('categorie').value = '';
        document.getElementById('description_plat').value = '';
        document.getElementById('menu-id').value = '';
    }
}

function editMenu(id, nom, resto_id, prix, categorie, description) {
    document.getElementById('menu-form').style.display = 'block';
    document.getElementById('menu-id').value = id;
    document.getElementById('nom_plat').value = nom;
    document.getElementById('restaurant_id').value = resto_id;
    document.getElementById('prix').value = prix;
    document.getElementById('categorie').value = categorie;
    document.getElementById('description_plat').value = description;
    document.getElementById('edit-menu-btn').style.display = 'block';
    window.scrollTo({top: 0, behavior: 'smooth'});
}

// ========= CHARTS LOGIC =========
// Données PHP injectées en JS
const dataDaily = <?php echo json_encode($salesDaily ?? []); ?>;
const dataWeekly = <?php echo json_encode($salesWeekly ?? []); ?>;
const dataMonthly = <?php echo json_encode($salesMonthly ?? []); ?>;

let salesChart;

function renderChart(labels, revenues, orders, title) {
    const ctx = document.getElementById('salesChart');
    if(!ctx) return;
    
    if (salesChart) {
        salesChart.destroy();
    }

    salesChart = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenus (FCFA)',
                    data: revenues,
                    backgroundColor: 'rgba(52, 152, 219, 0.6)',
                    borderColor: '#2980b9',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Nombre de Commandes',
                    data: orders,
                    type: 'line',
                    borderColor: '#e74c3c',
                    backgroundColor: '#e74c3c',
                    borderWidth: 2,
                    tension: 0.3,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: title, font: { size: 16 } },
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    type: 'linear', display: true, position: 'left',
                    title: { display: true, text: 'Revenus (FCFA)' },
                    beginAtZero: true
                },
                y1: {
                    type: 'linear', display: true, position: 'right',
                    title: { display: true, text: 'Commandes' },
                    grid: { drawOnChartArea: false },
                    beginAtZero: true
                }
            }
        }
    });
}

function updateChart(period) {
    let labels = [], revenues = [], orders = [], title = '';
    
    document.getElementById('btn-daily').style.background = '#e0e0e0';
    document.getElementById('btn-daily').style.color = '#333';
    document.getElementById('btn-weekly').style.background = '#e0e0e0';
    document.getElementById('btn-weekly').style.color = '#333';
    document.getElementById('btn-monthly').style.background = '#e0e0e0';
    document.getElementById('btn-monthly').style.color = '#333';

    document.getElementById('btn-' + period).style.background = 'var(--primary-color)';
    document.getElementById('btn-' + period).style.color = 'white';

    if (period === 'daily') {
        labels = dataDaily.map(d => d.label);
        revenues = dataDaily.map(d => d.revenue);
        orders = dataDaily.map(d => d.orders);
        title = 'Ventes des 7 derniers jours';
    } else if (period === 'weekly') {
        labels = dataWeekly.map(d => d.label);
        revenues = dataWeekly.map(d => d.revenue);
        orders = dataWeekly.map(d => d.orders);
        title = 'Ventes des 4 dernières semaines';
    } else if (period === 'monthly') {
        labels = dataMonthly.map(d => d.label);
        revenues = dataMonthly.map(d => d.revenue);
        orders = dataMonthly.map(d => d.orders);
        title = 'Ventes des 12 derniers mois';
    }

    renderChart(labels, revenues, orders, title);
}

// Charger Chart.js dynamiquement puis initier
const script = document.createElement('script');
script.src = "https://cdn.jsdelivr.net/npm/chart.js";
script.onload = () => updateChart('daily');
document.head.appendChild(script);

</script>

    <!-- Section des Avis -->
    <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 20px;">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-comments"></i> Derniers Avis Clients</h3>
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-color); text-align: left;">
                    <th style="padding: 10px;">Boutique</th>
                    <th>Client</th>
                    <th>Note</th>
                    <th>Commentaire</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res_avis = mysqli_query($conn, "SELECT a.*, r.nom_resto, u.nom as user_nom FROM avis a JOIN restaurants r ON a.restaurant_id = r.id JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 20");
                while($avis = mysqli_fetch_assoc($res_avis)):
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;"><?php echo htmlspecialchars($avis['nom_resto']); ?></td>
                    <td><?php echo htmlspecialchars($avis['user_nom']); ?></td>
                    <td>
                        <span style="color: #f1c40f;">
                            <?php echo str_repeat('★', $avis['rating']) . str_repeat('☆', 5 - $avis['rating']); ?>
                        </span>
                    </td>
                    <td><small><?php echo htmlspecialchars($avis['comment']); ?></small></td>
                    <td><small><?php echo date('d/m/Y', strtotime($avis['created_at'])); ?></small></td>
                    <td>
                        <a href="admin_preview.php?delete_review=<?php echo $avis['id']; ?>" onclick="return confirm('Supprimer cet avis ?')" style="color: #e74c3c;"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if(mysqli_num_rows($res_avis) == 0): ?>
                    <tr><td colspan="6" style="padding: 20px; text-align: center; color: #999;">Aucun avis enregistré.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php include('includes/footer.php'); ?>
