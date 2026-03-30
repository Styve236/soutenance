<?php
/**
 * Bootstrap - Fichier d'Initialisation de l'Application
 * À inclure au début de chaque fichier
 */

// Déterminer le chemin racine peu importe le niveau du fichier
$rootPath = dirname(__DIR__);
if (basename($rootPath) === 'Soutenance') {
    $rootPath = dirname($rootPath) . '/Soutenance';
} else {
    $rootPath = __DIR__;
}

// Définir BOOTSTRAP_ROOT si pas encore défini
if (!defined('BOOTSTRAP_ROOT')) {
    define('BOOTSTRAP_ROOT', $rootPath);
}

// Charger la configuration
require_once BOOTSTRAP_ROOT . '/config/config.php';

// Charger la classe Database
require_once BOOTSTRAP_ROOT . '/app/models/Database.php';

// Charger les helpers
require_once BOOTSTRAP_ROOT . '/app/helpers/helpers.php';

// Charger le middleware Auth
require_once BOOTSTRAP_ROOT . '/app/middleware/Auth.php';

// Initialiser la session
Auth::start();

// Vérifier que les tables existent (optionnel)
function checkTables()
{
    $requiredTables = [
        'users',
        'restaurants',
        'menus',
        'commandes',
        'visitor_tracking',
        'user_registrations'
    ];

    foreach ($requiredTables as $table) {
        if (!tableExists($table)) {
            logEvent('WARNING', "Table manquante: $table");
        }
    }
}

// Appeler la vérification des tables
if (APP_ENV === 'development') {
    // checkTables(); // Dé-commenter pour vérifier
}
?>
