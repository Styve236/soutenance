<?php
// Aperçu admin avec code d'accès requis
include('includes/db.php');
include('includes/header.php');

// Vérification du code d'accès
$access_granted = false;
$access_code = "mention"; // Code secret pour accéder à l'aperçu admin

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_preview_access']);
    header("Location: admin_preview.php");
    exit();
}

if (isset($_POST['admin_code'])) {
    if ($_POST['admin_code'] === $access_code) {
        $access_granted = true;
        $_SESSION['admin_preview_access'] = true;
    } else {
        $error_msg = "Code d'accès incorrect.";
    }
} elseif (isset($_SESSION['admin_preview_access']) && $_SESSION['admin_preview_access'] === true) {
    $access_granted = true;
}

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
            $success_msg = "Restaurant ajouté avec succès !";
        } else {
            $error_msg = "Erreur lors de l'ajout du restaurant.";
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
            $success_msg = "Restaurant modifié avec succès !";
        } else {
            $error_msg = "Erreur lors de la modification du restaurant.";
        }
        $stmt->close();
    }

    // Supprimer un restaurant
    if (isset($_GET['delete_restaurant'])) {
        $id = (int)$_GET['delete_restaurant'];
        $stmt = $conn->prepare("DELETE FROM restaurants WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $success_msg = "Restaurant supprimé avec succès !";
        } else {
            $error_msg = "Erreur lors de la suppression du restaurant.";
        }
        $stmt->close();
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
        $stmt->bind_param("sidss", $nom_plat, $restaurant_id, $prix, $categorie, $description_plat);
        
        if ($stmt->execute()) {
            $success_msg = "Menu ajouté avec succès !";
        } else {
            $error_msg = "Erreur lors de l'ajout du menu.";
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
            $stmt->bind_param("sidss", $nom_plat, $restaurant_id, $prix, $categorie, $description_plat);
        }
        
        if ($stmt->execute()) {
            $success_msg = "Menu modifié avec succès !";
        } else {
            $error_msg = "Erreur lors de la modification du menu.";
        }
        $stmt->close();
    }

    // Supprimer un menu
    if (isset($_GET['delete_menu'])) {
        $id = (int)$_GET['delete_menu'];
        $stmt = $conn->prepare("DELETE FROM menus WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $success_msg = "Menu supprimé avec succès !";
        } else {
            $error_msg = "Erreur lors de la suppression du menu.";
        }
        $stmt->close();
    }
}
?>

<main class="container" style="padding-top: 20px;">
    <?php if (!$access_granted && !isset($_SESSION['user_id'])): ?>
        <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 40px; text-align: center; max-width: 500px; margin: 0 auto;">
            <h2 style="margin-bottom: 20px; color: var(--secondary-color);"><i class="fas fa-lock"></i> Accès Admin</h2>
            <p style="margin-bottom: 30px; color: #666;">Entrez le code d'accès pour voir l'aperçu administrateur.</p>

            <?php if(isset($error_msg)): ?>
                <div style="background: #e74c3c; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                <input type="password" name="admin_code" placeholder="Code d'accès" required
                       style="padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px; text-align: center;">
                <button type="submit" style="padding: 12px; background: var(--primary-color); color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-key"></i> Accéder
                </button>
            </form>
        </div>
    <?php elseif (isset($_SESSION['user_id']) && !$access_granted): ?>
        <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 40px; text-align: center; max-width: 500px; margin: 0 auto;">
            <h2 style="margin-bottom: 20px; color: #e74c3c;"><i class="fas fa-lock"></i> Accès Refusé</h2>
            <p style="margin-bottom: 30px; color: #666;">Vous êtes connecté, mais vous n'avez pas accès au panneau administrateur.</p>
            <a href="index.php" style="color: white; background: var(--primary-color); padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; font-weight: 600;">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
        </div>
    <?php else: ?>
        <div style="background: var(--secondary-color); color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin:0;"><i class="fas fa-eye"></i> Aperçu Admin - Accès Autorisé</h2>
                <small>Code d'accès validé - Gestion des données</small>
            </div>
            <a href="admin_preview.php?logout=1" style="color: white; text-decoration: none; border: 1px solid white; padding: 5px 10px; border-radius: 5px; font-weight: 600;">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>

        <?php if(isset($success_msg)): ?>
            <div style="background: #27ae60; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <span><?php echo $success_msg; ?></span>
                <button onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: white; cursor: pointer; font-size: 20px;">&times;</button>
            </div>
        <?php endif; ?>

    <!-- Restaurants -->
    <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="form-title"><i class="fas fa-utensils"></i> Restaurants</h3>
            <button onclick="toggleRestaurantForm()" style="background: var(--primary-color); color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: 600;">
                <i class="fas fa-plus"></i> Ajouter
            </button>
        </div>

        <!-- Formulaire Restaurant -->
        <div id="restaurant-form" style="display: none; background: var(--bg-color); padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #dee2e6;">
            <h4 id="form-title"><i class="fas fa-plus"></i> Ajouter un Restaurant</h4>
            <form method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; align-items: end;">
                <input type="hidden" id="restaurant-id" name="id">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Nom du Restaurant</label>
                    <input type="text" id="nom_resto" name="nom_resto" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Quartier</label>
                    <input type="text" id="quartier" name="quartier" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Logo du Restaurant (optionnel)</label>
                    <input type="file" id="image_logo" name="image_logo" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Catégorie</label>
                    <select id="categorie_resto" name="categorie" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="Braisés">Braisés</option>
                        <option value="Traditionnel">Traditionnel</option>
                        <option value="Fast-food">Fast-food</option>
                        <option value="Pâtisserie">Pâtisserie</option>
                    </select>
                </div>
                <div style="grid-column: span 2;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Description</label>
                    <textarea id="description" name="description" rows="3" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                </div>
                <div style="grid-column: span 2; display: flex; gap: 10px;">
                    <button type="submit" id="add-btn" name="add_restaurant" style="background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-plus"></i> Ajouter
                    </button>
                    <button type="submit" id="edit-btn" name="edit_restaurant" style="background: var(--accent-color); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; display: none; font-weight: 600;">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                    <button type="button" onclick="toggleRestaurantForm()" style="background: #95a5a6; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                </div>
            </form>
        </div>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-color); text-align: left;">
                    <th style="padding: 10px;">ID</th>
                    <th>Nom</th>
                    <th>Quartier</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res_restos = mysqli_query($conn, "SELECT * FROM restaurants ORDER BY id DESC");
                while($resto = mysqli_fetch_assoc($res_restos)):
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;"><?php echo $resto['id']; ?></td>
                    <td><?php echo htmlspecialchars($resto['nom_resto']); ?></td>
                    <td><?php echo htmlspecialchars($resto['quartier']); ?></td>
                    <td><?php echo htmlspecialchars(substr($resto['description'], 0, 50)) . '...'; ?></td>
                    <td>
                        <button onclick="editRestaurant(<?= $resto['id']; ?>, <?= json_encode($resto['nom_resto']); ?>, <?= json_encode($resto['quartier']); ?>, <?= json_encode($resto['description']); ?>)" 
                            style="background: var(--accent-color); color:white; border:none; padding:5px 10px; border-radius:3px; cursor:pointer; margin-right:5px; font-weight: 600;">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        <a href="admin_preview.php?delete_restaurant=<?php echo $resto['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce restaurant ?')" 
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
            <h3><i class="fas fa-list"></i> Menus</h3>
            <button onclick="toggleMenuForm()" style="background: var(--primary-color); color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: 600;">
                <i class="fas fa-plus"></i> Ajouter un Menu
            </button>
        </div>

        <!-- Formulaire Menu -->
        <div id="menu-form" style="display: none; background: var(--bg-color); padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #dee2e6;">
            <h4 id="menu-form-title"><i class="fas fa-plus"></i> Ajouter un Menu</h4>
            <form method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; align-items: end;">
                <input type="hidden" id="menu-id" name="menu_id">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Nom du Plat</label>
                    <input type="text" id="nom_plat" name="nom_plat" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Restaurant</label>
                    <select id="restaurant_id" name="restaurant_id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">Choisir un restaurant</option>
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
                        <option value="Braisés">Braisés</option>
                        <option value="Traditionnel">Traditionnel</option>
                        <option value="Fast-food">Fast-food</option>
                        <option value="Pâtisserie">Pâtisserie</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Image du Plat (optionnel)</label>
                    <input type="file" id="image_plat" name="image_plat" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Description du Plat</label>
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

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-color); text-align: left;">
                    <th style="padding: 10px;">ID</th>
                    <th>Plat</th>
                    <th>Restaurant</th>
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
<button
onclick="editMenu(
    <?= $menu['id']; ?>,
    <?= json_encode($menu['nom_plat']); ?>,
    <?= $menu['restaurant_id']; ?>,
    <?= $menu['prix']; ?>,
    <?= json_encode($menu['categorie']); ?>,
    <?= json_encode($menu['description_plat']); ?>
)"
style="background: var(--accent-color); color:white; border:none; padding:5px 10px; border-radius:3px; cursor:pointer; margin-right:5px; font-weight: 600;">
    Modifier
</button>
                        <a href="admin_preview.php?delete_menu=<?php echo $menu['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce menu ?')" style="background: #e74c3c; color: white; padding: 5px 10px; border-radius: 3px; text-decoration: none; font-weight: 600;">
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
        <h3><i class="fas fa-users"></i> Utilisateurs</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-color); text-align: left;">
                    <th style="padding: 10px;">ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Rôle</th>
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
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Commandes -->
    <div style="background: var(--white); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 20px;">
        <h3><i class="fas fa-shopping-cart"></i> Commandes</h3>
        <table style="width: 100%; border-collapse: collapse;">
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
                    <td><?php echo htmlspecialchars($commande['statut']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>

<?php include('includes/footer.php'); ?>

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
    document.getElementById('add-menu-btn').style.display = 'none';
    document.getElementById('edit-menu-btn').style.display = 'block';
    window.scrollTo({top: 0, behavior: 'smooth'});
}
</script>
