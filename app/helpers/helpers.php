<?php
/**
 * Helpers - Fonctions Utilitaires Globales
 */

/**
 * Redirection sécurisée
 */
function redirect($url)
{
    header("Location: {$url}");
    exit();
}

/**
 * Vérifier si l'utilisateur est connecté
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifier si l'utilisateur est admin
 */
function isAdmin()
{
    if (!isLoggedIn()) return false;
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Récupérer l'ID de l'utilisateur connecté
 */
function getUserId()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Récupérer le nom de l'utilisateur connecté
 */
function getUserName()
{
    return $_SESSION['user_nom'] ?? 'Utilisateur';
}

/**
 * Afficher un message flash
 */
function setFlash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Récupérer et supprimer le message flash
 */
function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Afficher le message flash en HTML
 */
function displayFlash()
{
    $flash = getFlash();
    if (!$flash) return;

    $colors = [
        'success' => '#27ae60',
        'error' => '#e74c3c',
        'warning' => '#f39c12',
        'info' => '#3498db'
    ];

    $color = $colors[$flash['type']] ?? '#3498db';
    echo "<div style='background: {$color}; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
    echo htmlspecialchars($flash['message']);
    echo "</div>";
}

/**
 * Formater une date
 */
function formatDate($date, $format = 'd/m/Y H:i')
{
    return date($format, strtotime($date));
}

/**
 * Vérifier si un email est valide
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Formater le prix en FCFA
 */
function formatPrice($price)
{
    return number_format($price, 0, ',', ' ') . ' FCFA';
}

/**
 * Obtenir l'adresse IP du client
 */
function getClientIp()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * Logger un événement
 */
function logEvent($level, $message, $data = [])
{
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] $message";

    if (!empty($data)) {
        $logMessage .= " | " . json_encode($data);
    }

    if (defined('LOG_FILE')) {
        error_log($logMessage . "\n", 3, LOG_FILE);
    }
}

/**
 * Vérifier le code d'accès admin
 */
function verifyAdminCode($code)
{
    return $code === ADMIN_CODE;
}

/**
 * Générer un token CSRF
 */
function generateCsrfToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier un token CSRF
 */
function verifyCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Obtenir le lien vers une page
 */
function pageUrl($page)
{
    return BASE_URL . "/$page";
}

/**
 * Obtenir le lien vers un asset
 */
function assetUrl($path)
{
    return ASSETS_URL . "/$path";
}

/**
 * Vérifier si une table existe
 */
function tableExists($tableName)
{
    $result = Database::getOne("SHOW TABLES LIKE '$tableName'");
    return $result !== null;
}

/**
 * Vérifier si une colonne existe
 */
function columnExists($table, $column)
{
    $result = Database::getOne("SHOW COLUMNS FROM $table LIKE '$column'");
    return $result !== null;
}
?>
