<?php
/**
 * Configuration Centrale de l'Application
 * Point unique pour toutes les configurations
 */

// Configuration de la Base de Données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'douala_eats');

// Configuration de l'Application
define('APP_NAME', 'HonneyHub');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // production ou development

// Codes d'Accès
define('ADMIN_CODE', 'mention');

// Chemins de l'Application
define('ROOT_PATH', (defined('BOOTSTRAP_ROOT') ? BOOTSTRAP_ROOT : dirname(__DIR__)));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('PAGES_PATH', ROOT_PATH . '/pages');
define('DOCS_PATH', ROOT_PATH . '/documentation');
define('AUTH_PATH', PAGES_PATH . '/auth');

// URLs
define('BASE_URL', 'http://localhost/Soutenance');
define('ASSETS_URL', BASE_URL . '/assets');
define('ADMIN_URL', BASE_URL . '/admin');

// Paramètres de Session
define('SESSION_TIMEOUT', 3600); // 1 heure
define('SESSION_NAME', 'HONNEYHUB_SESSION');

// Paramètres de Sécurité
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 10);

// Logs
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
define('LOG_FILE', STORAGE_PATH . '/logs/app.log');

// Pagination
define('ITEMS_PER_PAGE', 10);

// Délais (en secondes)
define('REFRESH_RATE', 5); // Rafraîchissement monitoring

// Configuration d'Erreur (développement)
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
?>
