<?php 
include('includes/db.php'); 
include('includes/header.php'); 

// 1. On récupère l'ID passé dans l'URL
if(isset($_GET['id'])) {
    $resto_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // 2. On récupère les infos du restaurant
    $res_resto = mysqli_query($conn, "SELECT * FROM restaurants WHERE id = '$resto_id'");
    $resto = mysqli_fetch_assoc($res_resto);
    
    // 3. On récupère les plats de ce restaurant
    $res_menus = mysqli_query($conn, "SELECT * FROM menus WHERE restaurant_id = '$resto_id'");
} else {
    header("Location: index.php"); // Si pas d'ID, retour à l'accueil
    exit();
}
?>

<main class="container">
    <section class="resto-hero" style="background: #fff; padding: 20px; border-radius: 10px; margin: 20px 0;">
        <h1><?php echo $resto['nom_resto']; ?></h1>
        <p><i class="fas fa-map-marker-alt"></i> <?php echo $resto['quartier']; ?></p>
        <p><?php echo $resto['description']; ?></p>
    </section>

    <h3>La Carte / Menu</h3>
    <div class="grid">
        <?php while($plat = mysqli_fetch_assoc($res_menus)): ?>
            <article class="card">
                <img src="assets/img/plats/<?php echo $plat['image_plat']; ?>" alt="<?php echo $plat['nom_plat']; ?>">
                <div class="card-body">
                    <h4><?php echo $plat['nom_plat']; ?></h4>
                    <p><?php echo $plat['description_plat']; ?></p>
                    <div style="display:flex; justify-content: space-between; align-items: center; margin-top:15px;">
                        <span style="font-weight: bold; color: var(--primary-color); font-size: 1.2em;">
                            <?php echo number_format($plat['prix'], 0, ',', ' '); ?> FCFA
                        </span>
                        <form action="ajouter_panier.php" method="GET">
                            <input type="hidden" name="id" value="<?php echo $plat['id']; ?>">
                            <button type="submit" class="btn-register" style="border:none; cursor:pointer;">
                                <i class="fas fa-plus"></i> Ajouter
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </div>

    <!-- Section des Avis -->
    <section style="margin-top: 50px; background: var(--white); padding: 30px; border-radius: 10px;">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-star"></i> Avis des clients</h3>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <!-- Formulaire d'ajout d'avis -->
            <div style="background: var(--bg-color); padding: 20px; border-radius: 10px; margin-bottom: 30px;">
                <h4>Partagez votre expérience</h4>
                <form id="review-form" method="POST">
                    <div style="margin-bottom: 15px;">
                        <label>Note (de 1 à 5 étoiles)</label>
                        <div style="display: flex; gap: 10px; margin-top: 10px;">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <label style="cursor: pointer; font-size: 24px;">
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" style="display: none;">
                                    <i class="fas fa-star" style="color: #ddd; transition: 0.2s;" onclick="this.style.color = 'var(--accent-color)'"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label>Votre avis</label>
                        <textarea name="comment" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 100px; font-family: inherit;" placeholder="Partagez votre expérience..."></textarea>
                    </div>

                    <button type="submit" style="background: var(--primary-color); color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                        <i class="fas fa-paper-plane"></i> Envoyer l'avis
                    </button>
                </form>
            </div>
        <?php else: ?>
            <p style="background: var(--bg-color); padding: 15px; border-radius: 5px;">
                <a href="auth/login.php" style="color: var(--primary-color); font-weight: bold;">Connectez-vous</a> pour laisser un avis
            </p>
        <?php endif; ?>

        <!-- Affichage des avis -->
        <div id="reviews-list"></div>
    </section>

    <script>
        const restaurantId = <?php echo $resto_id; ?>;

        // Charger les avis au chargement
        function loadReviews() {
            fetch(`includes/avis.php?restaurant_id=${restaurantId}`)
                .then(response => response.json())
                .then(data => {
                    const reviewsList = document.getElementById('reviews-list');
                    if (data.length === 0) {
                        reviewsList.innerHTML = '<p style="color: #999;">Aucun avis pour le moment.</p>';
                    } else {
                        reviewsList.innerHTML = data.map(review => `
                            <div style="background: var(--bg-color); padding: 15px; border-radius: 5px; margin-bottom: 10px;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                    <strong>${review.user_nom}</strong>
                                    <span style="color: var(--accent-color);">
                                        ${'★'.repeat(review.rating)}${'☆'.repeat(5-review.rating)}
                                    </span>
                                </div>
                                <p>${review.comment}</p>
                                <small style="color: #999;">
                                    ${new Date(review.created_at).toLocaleDateString('fr-FR')}
                                </small>
                            </div>
                        `).join('');
                    }
                })
                .catch(error => console.error('Erreur:', error));
        }

        loadReviews();

        // Envoyer un avis
        <?php if(isset($_SESSION['user_id'])): ?>
            document.getElementById('review-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('restaurant_id', restaurantId);

                fetch('includes/avis.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Avis envoyé avec succès!');
                        this.reset();
                        loadReviews();
                    } else {
                        alert('Erreur: ' + data.error);
                    }
                })
                .catch(error => console.error('Erreur:', error));
            });
        <?php endif; ?>
    </script>
</main>

<?php include('includes/footer.php'); ?>