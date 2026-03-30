<?php 
include('../includes/db.php'); 
include('../config.php'); 
include('../includes/header.php'); 

if(isset($_POST['register'])) {
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $tel = mysqli_real_escape_string($conn, $_POST['telephone']);
    $adresse = mysqli_real_escape_string($conn, $_POST['adresse']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (nom, email, telephone, adresse, mot_de_passe) 
            VALUES ('$nom', '$email', '$tel', '$adresse', '$pass')";
    
    if(mysqli_query($conn, $sql)) {
        header("Location: login.php?success=Compte créé");
    }
}
?>

<main class="container" style="max-width: 500px; margin-top: 50px;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; margin-bottom: 20px;">Créer un compte</h2>
        <form method="POST">
            <input type="text" name="nom" placeholder="Nom complet" required style="width:100%; padding:12px; margin-bottom:15px;">
            <input type="email" name="email" placeholder="Email" required style="width:100%; padding:12px; margin-bottom:15px;">
            <input type="text" name="telephone" placeholder="Téléphone (ex: 699...)" required style="width:100%; padding:12px; margin-bottom:15px;">
            <textarea name="adresse" placeholder="Adresse précise (Quartier, point de repère)" required style="width:100%; padding:12px; margin-bottom:15px;"></textarea>
            <input type="password" name="password" placeholder="Mot de passe" required style="width:100%; padding:12px; margin-bottom:20px;">
            <button type="submit" name="register" class="btn-register" style="width:100%; border:none; padding:15px; cursor:pointer;">S'inscrire</button>
        </form>
    </div>
</main>

<?php include('../includes/footer.php'); ?>