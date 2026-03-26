<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "douala_eats"; // <--- VERIFIE BIEN LE NOM ICI

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("La connexion a échoué : " . mysqli_connect_error());
}
?>