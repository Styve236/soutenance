<?php
/**
 * Page d'Inscription
 * Utilise la nouvelle architecture MVC
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

// Traiter l'inscription
$error = null;
$success = null;

if (isset($_POST['register'])) {
    $data = [
        'nom' => $_POST['nom'] ?? '',
        'email' => $_POST['email'] ?? '',
        'telephone' => $_POST['telephone'] ?? '',
        'adresse' => $_POST['adresse'] ?? '',
        'mot_de_passe' => $_POST['password'] ?? '',
    ];
    
    // Valider les données
    if (empty($data['nom']) || empty($data['email']) || empty($data['mot_de_passe'])) {
        $error = "Les champs obligatoires ne peuvent pas être vides";
    } else {
        // Utiliser le contrôleur
        require_once APP_PATH . '/controllers/UserController.php';
        $result = UserController::register($data);
        
        if ($result['success']) {
            $success = "Compte créé avec succès! Vous pouvez maintenant vous connecter.";
            // Redirection après 2 secondes
            header("Refresh: 2; url=" . BASE_URL . "/?page=login");
        } else {
            $error = $result['message'] ?? "Erreur lors de l'inscription";
        }
    }
}

// Charger l'en-tête
require_once APP_PATH . '/views/header.php';
?>

<main class="container" style="max-width: 500px; margin-top: 50px;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; margin-bottom: 20px;">Créer un compte</h2>
        
        <?php if ($error): ?>
            <div style="background-color: #fee; color: #c00; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div style="background-color: #efe; color: #0c0; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="nom" placeholder="Nom complet" required 
                   style="width:100%; padding:12px; margin-bottom:15px; box-sizing:border-box; border: 1px solid #ddd; border-radius: 5px;">
            
            <input type="email" name="email" placeholder="Email" required 
                   style="width:100%; padding:12px; margin-bottom:15px; box-sizing:border-box; border: 1px solid #ddd; border-radius: 5px;">
            
            <input type="text" name="telephone" placeholder="Téléphone (ex: 699...)" 
                   style="width:100%; padding:12px; margin-bottom:15px; box-sizing:border-box; border: 1px solid #ddd; border-radius: 5px;">
            
            <textarea name="adresse" placeholder="Adresse précise (Quartier, point de repère)" 
                      style="width:100%; padding:12px; margin-bottom:15px; box-sizing:border-box; border: 1px solid #ddd; border-radius: 5px; resize: vertical; min-height: 80px;"></textarea>
            
            <input type="password" name="password" placeholder="Mot de passe" required 
                   style="width:100%; padding:12px; margin-bottom:20px; box-sizing:border-box; border: 1px solid #ddd; border-radius: 5px;">
            
            <button type="submit" name="register" 
                    style="width:100%; border:none; padding:15px; cursor:pointer; background: #ff6b35; color: white; border-radius: 5px; font-weight: bold;">
                S'inscrire
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 15px;">
            Vous avez déjà un compte? <a href="<?php echo BASE_URL; ?>/?page=login" style="color: #ff6b35;">Se connecter ici</a>
        </p>
    </div>
</main>

<?php 
require_once APP_PATH . '/views/footer.php'; 
?>
