<?php 
session_start();
ob_start(); // OBLIGATOIRE : Empêche l'erreur "Headers already sent" avec header()
include('../includes/db.php'); 
include('../config.php'); 

// On déplace le traitement PHP AVANT le chargement du design (header) !
if(isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    $user = mysqli_fetch_assoc($result);

    if($user && password_verify($pass, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        
        // Redirection conditionnelle selon le rôle
        if ($user['role'] === 'admin') {
            header("Location: ../admin_preview.php");
        } else {
            header("Location: ../index.php");
        }
        exit();
    } else {
        $error = "Email ou mot de passe incorrect";
    }
}

include('../includes/header.php'); 
?>

<main class="container" style="max-width: 450px; margin: 80px auto 100px auto;">
    <div style="background: var(--white); padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
        <h2 style="text-align: center; margin-bottom: 25px; color: var(--secondary-color);">
            <i class="fas fa-sign-in-alt" style="color: var(--primary-color);"></i> Connexion
        </h2>
        
        <?php if(isset($error)) echo "<div style='color: #e74c3c; background: rgba(231,76,60,0.1); padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold;'><i class='fas fa-exclamation-circle'></i> $error</div>"; ?>
        <?php if(isset($_GET['success'])) echo "<div style='color: #27ae60; background: rgba(39,174,96,0.1); padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold;'><i class='fas fa-check-circle'></i> ".htmlspecialchars($_GET['success'])."</div>"; ?>
        
        <form method="POST">
            <div style="position: relative; margin-bottom: 20px;">
                <i class="fas fa-envelope" style="position: absolute; top: 16px; left: 15px; color: #aaa;"></i>
                <input type="email" name="email" placeholder="Adresse Email" required style="width:100%; padding:15px 15px 15px 45px; border: 1px solid rgba(128,128,128,0.2); border-radius: 8px; background: var(--bg-color); color: var(--secondary-color); font-size: 1rem; outline: none;">
            </div>
            
            <div style="position: relative; margin-bottom: 30px;">
                <i class="fas fa-lock" style="position: absolute; top: 16px; left: 15px; color: #aaa;"></i>
                <input type="password" name="password" placeholder="Mot de passe" required style="width:100%; padding:15px 15px 15px 45px; border: 1px solid rgba(128,128,128,0.2); border-radius: 8px; background: var(--bg-color); color: var(--secondary-color); font-size: 1rem; outline: none;">
            </div>
            
            <button type="submit" name="login" class="btn-register" style="width:100%; border:none; padding:15px; cursor:pointer; font-size: 1.1em; font-weight: bold; border-radius: 8px; transition: 0.3s; box-shadow: 0 4px 10px rgba(255, 71, 87, 0.3);">
                Se Connecter
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 25px; font-size: 0.95em; color: var(--secondary-color);">
            Vous n'avez pas de compte ? <a href="register.php" style="color: var(--primary-color); text-decoration: none; font-weight: bold;">S'inscrire</a>
        </p>
    </div>
</main>

<?php include('../includes/footer.php'); ?>