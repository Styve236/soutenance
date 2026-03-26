<?php
// Configuration générale de Douala Eats
define('SITE_NAME', 'Douala Eats');
define('CURRENCY', 'FCFA');

// Frais de livraison par défaut (Peut être rendu dynamique plus tard)
$frais_livraison = 1000; 

// Liste des quartiers pour les formulaires
$quartiers_douala = ['Akwa', 'Bonapriso', 'Bali', 'Logpom', 'Kotto', 'Bonamoussadi', 'Deido', 'Makepe'];

// Fonction pour sécuriser les affichages (XSS protection)
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>