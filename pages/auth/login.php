<?php
/**
 * Page de Connexion
 * Utilise la nouvelle architecture MVC
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

// Traiter la connexion
$error = null;
if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Email et mot de passe requis";
    } else {
        // Utiliser le contrôleur
        require_once APP_PATH . '/controllers/UserController.php';
        $result = UserController::login($email, $password);
        
        if ($result['success']) {
            // Connecter l'utilisateur
            Auth::login($result['user']['id'], $result['user']['nom'], $result['user']['email'], $result['user']['id']);
            redirect(BASE_URL . '/?page=accueil');
        } else {
            $error = $result['message'] ?? "Email ou mot de passe incorrect";
        }
    }
}

// Charger l'en-tête
require_once APP_PATH . '/views/header.php';
?>

<main class="container" style="max-width: 400px; margin-top: 50px;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; margin-bottom: 20px;">Connexion</h2>
        
        <?php if ($error): ?>
            <div style="background-color: #fee; color: #c00; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required 
                   style="width:100%; padding:12px; margin-bottom:15px; box-sizing:border-box; border: 1px solid #ddd; border-radius: 5px;">
            <input type="password" name="password" placeholder="Mot de passe" required 
                   style="width:100%; padding:12px; margin-bottom:20px; box-sizing:border-box; border: 1px solid #ddd; border-radius: 5px;">
            <button type="submit" name="login" class="btn-register" 
                    style="width:100%; border:none; padding:15px; cursor:pointer; background: #ff6b35; color: white; border-radius: 5px; font-weight: bold;">
                Se connecter
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 15px;">
            Pas de compte? <a href="<?php echo BASE_URL; ?>/?page=register" style="color: #ff6b35;">S'inscrire ici</a>
        </p>
    </div>
</main>

<?php 
require_once APP_PATH . '/views/footer.php'; 
?>
