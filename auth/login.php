<?php 
include('../includes/db.php'); 
include('../includes/header.php'); 

if(isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    $user = mysqli_fetch_assoc($result);

    if($user && password_verify($pass, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        header("Location: ../index.php");
    } else {
        $error = "Email ou mot de passe incorrect";
    }
}
?>

<main class="container" style="max-width: 400px; margin-top: 50px;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; margin-bottom: 20px;">Connexion</h2>
        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required style="width:100%; padding:12px; margin-bottom:15px;">
            <input type="password" name="password" placeholder="Mot de passe" required style="width:100%; padding:12px; margin-bottom:20px;">
            <button type="submit" name="login" class="btn-register" style="width:100%; border:none; padding:15px; cursor:pointer;">Se connecter</button>
        </form>
    </div>
</main>

<?php include('../includes/footer.php'); ?>