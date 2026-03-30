<?php
/**
 * Page de Déconnexion
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

// Déconnecter l'utilisateur
Auth::logout();

// Rediriger vers l'accueil
redirect(BASE_URL . '/?page=accueil');
exit();
?>
