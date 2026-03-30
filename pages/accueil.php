<?php
/**
 * Page d'Accueil
 * Utilise la nouvelle architecture MVC
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once APP_PATH . '/controllers/StatisticsController.php';

// Enregistrer la visite
StatisticsController::recordVisit();

// Récupérer les filtres
$cat_filtre = $_GET['cat'] ?? '';
$search = $_GET['search'] ?? '';

// Récupérer les restaurants
$sql = "SELECT DISTINCT r.* FROM restaurants r WHERE 1";

if (!empty($cat_filtre)) {
    $sql .= " AND categorie = " . Database::escape($cat_filtre);
}

if (!empty($search)) {
    $search_escaped = '%' . Database::escape($search) . '%';
    $sql .= " AND (nom_resto LIKE '$search_escaped' OR quartier LIKE '$search_escaped' OR description LIKE '$search_escaped')";
}

$sql .= " ORDER BY id DESC";
$restaurants = Database::getAll($sql);

// Charger l'en-tête
require_once APP_PATH . '/views/header.php';
?>

<main class="container">
    <?php if (!Auth::check()): ?>
        <!-- PAGE D'ACCUEIL POUR LES UTILISATEURS NON CONNECTÉS -->
        <section style="padding: 60px 0; text-align: center; background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo ASSETS_URL; ?>/images/hero-douala.jpg'); background-size: cover; border-radius: 15px; color: white; margin-top: 20px;">
            <h2 style="font-size: 3rem; margin-bottom: 20px; font-weight: bold;">Bienvenue sur HonneyHub</h2>
            <p style="font-size: 1.3rem; margin-bottom: 30px; max-width: 700px; margin-left: auto; margin-right: auto;">La plateforme de livraison de repas la plus rapide et la plus savoureuse de Douala</p>
        </section>

        <section style="margin-top: 50px; margin-bottom: 50px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; margin-bottom: 50px;">
                <!-- Carte 1 -->
                <div style="background: var(--white); padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center;">
                    <i class="fas fa-map-marker-alt" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 15px; display: block;"></i>
                    <h3 style="color: var(--secondary-color); margin-bottom: 10px;">Restaurants Variés</h3>
                    <p style="color: #666;">Découvrez des centaines de restaurants à Douala, des braisés traditionnels aux fast-foods modernes.</p>
                </div>

                <!-- Carte 2 -->
                <div style="background: var(--white); padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center;">
                    <i class="fas fa-bicycle" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 15px; display: block;"></i>
                    <h3 style="color: var(--secondary-color); margin-bottom: 10px;">Livraison Rapide</h3>
                    <p style="color: #666;">Vos repas arrivent frais et chauds en 25-40 minutes, directement à votre porte.</p>
                </div>

                <!-- Carte 3 -->
                <div style="background: var(--white); padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center;">
                    <i class="fas fa-credit-card" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 15px; display: block;"></i>
                    <h3 style="color: var(--secondary-color); margin-bottom: 10px;">Paiement Sécurisé</h3>
                    <p style="color: #666;">Payez en toute sécurité avec plusieurs options de paiement disponibles.</p>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, var(--primary-color), #ff6b7a); color: white; padding: 50px 30px; border-radius: 15px; text-align: center;">
                <h2 style="margin-bottom: 20px; font-size: 2rem;">Prêt à Commander?</h2>
                <p style="font-size: 1.1rem; margin-bottom: 30px;">Créez votre compte maintenant pour accéder à tous nos restaurants et menus délicieux.</p>
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="<?php echo BASE_URL; ?>/?page=login" style="background: var(--white); color: var(--primary-color); padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 1rem;">
                        <i class="fas fa-sign-in-alt"></i> Se Connecter
                    </a>
                    <a href="<?php echo BASE_URL; ?>/?page=register" style="background: var(--secondary-color); color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 1rem;">
                        <i class="fas fa-user-plus"></i> Créer un Compte
                    </a>
                </div>
            </div>

            <div style="background: var(--bg-color); padding: 40px 30px; border-radius: 15px; margin-top: 40px;">
                <h3 style="color: var(--secondary-color); margin-bottom: 20px; text-align: center;">Pourquoi Choisir HonneyHub?</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div>
                        <h4 style="color: var(--primary-color); margin-bottom: 10px;"><i class="fas fa-check"></i> Large Sélection</h4>
                        <p style="color: #666;">Des braisés à Akwa aux pâtisseries de Bonapriso</p>
                    </div>
                    <div>
                        <h4 style="color: var(--primary-color); margin-bottom: 10px;"><i class="fas fa-check"></i> Menus Variés</h4>
                        <p style="color: #666;">Plats traditionnels, fast-food, desserts et bien plus</p>
                    </div>
                    <div>
                        <h4 style="color: var(--primary-color); margin-bottom: 10px;"><i class="fas fa-check"></i> Suivi Commande</h4>
                        <p style="color: #666;">Suivez votre commande en temps réel</p>
                    </div>
                    <div>
                        <h4 style="color: var(--primary-color); margin-bottom: 10px;"><i class="fas fa-check"></i> Support 24/7</h4>
                        <p style="color: #666;">Notre équipe est toujours disponible</p>
                    </div>
                </div>
            </div>
        </section>

    <?php else: ?>
        <!-- PAGE D'ACCUEIL POUR LES UTILISATEURS CONNECTÉS -->
        <section style="padding: 40px 0; text-align: center; background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo ASSETS_URL; ?>/images/hero-douala.jpg'); background-size: cover; border-radius: 15px; color: white; margin-top: 20px;">
            <h2 style="font-size: 2.5rem; margin-bottom: 10px;">Le meilleur de Douala à domicile</h2>
            <p style="font-size: 1.2rem;">Commandez dans vos restaurants préférés à Akwa, Bonapriso et partout ailleurs.</p>
        </section>

        <section style="margin-top: 30px;">
            <h3 style="margin-bottom: 15px;">Parcourir par envie</h3>
            <div style="display: flex; gap: 12px; overflow-x: auto; padding-bottom: 15px; scrollbar-width: none;">
                <a href="<?php echo BASE_URL; ?>/?page=accueil" class="btn-cat <?php echo (empty($cat_filtre) && empty($search)) ? 'active' : ''; ?>">
                    <i class="fas fa-border-all"></i> Tous
                </a>
                <a href="<?php echo BASE_URL; ?>/?page=accueil&cat=Braisés" class="btn-cat <?php echo $cat_filtre === 'Braisés' ? 'active' : ''; ?>">
                    <i class="fas fa-fire"></i> Braisés
                </a>
                <a href="<?php echo BASE_URL; ?>/?page=accueil&cat=Traditionnel" class="btn-cat <?php echo $cat_filtre === 'Traditionnel' ? 'active' : ''; ?>">
                    <i class="fas fa-leaf"></i> Traditionnel
                </a>
                <a href="<?php echo BASE_URL; ?>/?page=accueil&cat=Fast-food" class="btn-cat <?php echo $cat_filtre === 'Fast-food' ? 'active' : ''; ?>">
                    <i class="fas fa-hamburger"></i> Fast-food
                </a>
                <a href="<?php echo BASE_URL; ?>/?page=accueil&cat=Pâtisserie" class="btn-cat <?php echo $cat_filtre === 'Pâtisserie' ? 'active' : ''; ?>">
                    <i class="fas fa-birthday-cake"></i> Pâtisserie
                </a>
            </div>
        </section>

        <section style="margin-bottom: 50px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>
                    <?php 
                    if (!empty($search)) echo "Résultats pour : " . htmlspecialchars($search);
                    elseif (!empty($cat_filtre)) echo "Spécialités : " . htmlspecialchars($cat_filtre);
                    else echo "Restaurants à la une";
                    ?>
                </h3>
                <span style="color: #777; font-size: 0.9em;"><?php echo count($restaurants) ?? 0; ?> restaurants trouvés</span>
            </div>

            <div class="grid">
                <?php 
                if (!empty($restaurants) && count($restaurants) > 0):
                    foreach ($restaurants as $resto): 
                ?>
                    <a href="<?php echo BASE_URL; ?>/?page=menu&id=<?php echo $resto['id']; ?>" style="text-decoration: none; color: inherit;">
                        <article class="card">
                            <div style="position: relative;">
                                <img src="<?php echo ASSETS_URL; ?>/images/restos/<?php echo !empty($resto['image_logo']) ? htmlspecialchars($resto['image_logo']) : 'default-resto.jpg'; ?>" alt="<?php echo htmlspecialchars($resto['nom_resto']); ?>">
                                <span style="position: absolute; top: 10px; right: 10px; background: var(--primary-color); color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.8em; font-weight: bold;">
                                    <?php echo htmlspecialchars($resto['categorie']); ?>
                                </span>
                            </div>
                        
                        <div class="card-body">
                            <h4 style="font-size: 1.2rem; margin-bottom: 5px;"><?php echo htmlspecialchars($resto['nom_resto']); ?></h4>
                            <p style="color: #666; font-size: 0.9rem; height: 40px; overflow: hidden;">
                                <?php echo htmlspecialchars($resto['description']); ?>
                            </p>
                            
                            <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 10px;">
                                <span class="badge-quartier">
                                    <i class="fas fa-map-marker-alt" style="color: var(--primary-color);"></i> <?php echo htmlspecialchars($resto['quartier']); ?>
                                </span>
                                <span style="font-size: 0.8em; color: #27ae60; font-weight: bold;">
                                    <i class="fas fa-bicycle"></i> 25-40 min
                                </span>
                            </div>
                        </div>
                    </article>
                </a>
            <?php 
                endforeach; 
            else:
            ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background: white; border-radius: 10px;">
                    <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                    <p>Désolé, nous n'avons trouvé aucun restaurant correspondant à votre recherche.</p>
                    <a href="<?php echo BASE_URL; ?>/?page=accueil" style="color: var(--primary-color); font-weight: bold;">Voir tous les restaurants</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php require_once APP_PATH . '/views/footer.php'; ?>
