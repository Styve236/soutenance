<?php
/**
 * Page Menu d'un Restaurant
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

// Récupérer l'ID du restaurant
$restaurant_id = $_GET['id'] ?? null;

if (!$restaurant_id) {
    redirect(BASE_URL . '/?page=accueil');
}

// Récupérer le restaurant
$restaurant = Database::getOne("SELECT * FROM restaurants WHERE id = " . intval($restaurant_id));

if (!$restaurant) {
    redirect(BASE_URL . '/?page=accueil');
}

// Récupérer les menus du restaurant
$menus = Database::getAll("SELECT * FROM menus WHERE restaurant_id = " . intval($restaurant_id) . " ORDER BY id DESC");

// Charger l'en-tête
require_once APP_PATH . '/views/header.php';
?>

<main class="container" style="padding: 30px 0;">
    <!-- En-tête restaurant -->
    <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; display: flex; gap: 20px; align-items: center;">
        <img src="<?php echo ASSETS_URL; ?>/images/restos/<?php echo htmlspecialchars($restaurant['image_logo']); ?>" 
             alt="<?php echo htmlspecialchars($restaurant['nom_resto']); ?>" 
             style="width: 150px; height: 150px; object-fit: cover; border-radius: 10px;">
        
        <div style="flex: 1;">
            <h1><?php echo htmlspecialchars($restaurant['nom_resto']); ?></h1>
            <p style="color: #666; margin: 10px 0;">
                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($restaurant['quartier']); ?>
            </p>
            <p style="color: #666;">
                <?php echo htmlspecialchars($restaurant['description']); ?>
            </p>
            <p style="margin-top: 10px;">
                <strong>Catégorie :</strong> <?php echo htmlspecialchars($restaurant['categorie']); ?>
            </p>
        </div>
    </div>

    <!-- Menu items -->
    <h2>Menu</h2>
    
    <?php if (!empty($menus)): ?>
        <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));">
            <?php foreach ($menus as $menu): ?>
                <div style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <img src="<?php echo ASSETS_URL; ?>/images/plats/<?php echo htmlspecialchars($menu['image']); ?>" 
                         alt="<?php echo htmlspecialchars($menu['nom_plat']); ?>" 
                         style="width: 100%; height: 200px; object-fit: cover;">
                    
                    <div style="padding: 15px;">
                        <h4><?php echo htmlspecialchars($menu['nom_plat']); ?></h4>
                        <p style="color: #666; font-size: 0.9rem; margin: 10px 0;">
                            <?php echo htmlspecialchars(substr($menu['description'], 0, 100)); ?>...
                        </p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                            <span style="font-weight: bold; font-size: 1.2rem; color: var(--primary-color);">
                                <?php echo formatPrice($menu['prix']); ?>
                            </span>
                            <button onclick="addToCart(<?php echo $menu['id']; ?>, '<?php echo htmlspecialchars($menu['nom_plat']); ?>', <?php echo $menu['prix']; ?>)" 
                                    style="background: var(--primary-color); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;">
                                <i class="fas fa-plus"></i> Ajouter
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 50px; background: white; border-radius: 10px;">
            <p style="color: #666;">Aucun menu disponible pour ce restaurant</p>
        </div>
    <?php endif; ?>
</main>

<script>
function addToCart(menuId, name, price) {
    fetch('<?php echo BASE_URL; ?>/?page=ajouter-panier', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            menu_id: menuId,
            nom: name,
            prix: price,
            quantite: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Article ajouté au panier!');
        }
    });
}
</script>

<?php require_once APP_PATH . '/views/footer.php'; ?>
