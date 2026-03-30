<?php
/**
 * Point d'entrée principal de l'application
 * Routeur central qui redirige vers les bonnes pages
 */

require_once __DIR__ . '/../bootstrap.php';

// Récupérer la page demandée
$page = $_GET['page'] ?? 'accueil';
$action = $_GET['action'] ?? null;

// Nettoyer la page
$page = preg_replace('/[^a-z0-9_-]/', '', strtolower($page));

// Logs
logEvent('PAGE_VIEW', "Page: $page, Action: $action");

// Routes disponibles
$routes = [
    // Pages publiques
    'accueil' => PAGES_PATH . '/accueil.php',
    'index' => PAGES_PATH . '/accueil.php',
    'restaurants' => PAGES_PATH . '/restaurants.php',
    'menu' => PAGES_PATH . '/menu.php',
    'docs' => PAGES_PATH . '/docs.php',
    
    // Pages authentification
    'login' => PAGES_PATH . '/auth/login.php',
    'register' => PAGES_PATH . '/auth/register.php',
    'logout' => PAGES_PATH . '/auth/logout.php',
    
    // Pages utilisateur (connecté)
    'panier' => PAGES_PATH . '/user/panier.php',
    'commandes' => PAGES_PATH . '/user/mes_commandes.php',
    'profil' => PAGES_PATH . '/user/profil.php',
    'confirmation' => PAGES_PATH . '/user/confirmation.php',
    
    // Pages restaurant
    'dashboard-resto' => PAGES_PATH . '/restaurant/dashboard.php',
    
    // Pages admin
    'admin' => PAGES_PATH . '/admin/dashboard.php',
    'admin-dashboard' => PAGES_PATH . '/admin/dashboard.php',
    'admin-commandes' => PAGES_PATH . '/admin/commandes.php',
    'admin-preview' => PAGES_PATH . '/admin/preview.php',
    'traffic-monitor' => PAGES_PATH . '/admin/traffic_monitor.php',
    
    // APIs
    'ajouter-panier' => PAGES_PATH . '/api/ajouter_panier.php',
    'panier-api' => PAGES_PATH . '/api/panier_api.php',
    'search-api' => PAGES_PATH . '/api/search_api.php',
    'traffic-api' => PAGES_PATH . '/api/traffic_api.php',
    'notifications-api' => PAGES_PATH . '/api/notifications_api.php',
];

// Vérifier si la page existe
if (isset($routes[$page])) {
    $file = $routes[$page];
    
    if (file_exists($file)) {
        // Charger la page
        include $file;
    } else {
        http_response_code(404);
        echo "Page non trouvée: $file";
        logEvent('ERROR', "Page non trouvée: $page ($file)");
    }
} else {
    // Page par défaut (accueil)
    include PAGES_PATH . '/accueil.php';
}
?>
