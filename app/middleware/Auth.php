<?php
/**
 * Middleware d'Authentification
 * Gère les vérifications de sécurité et d'accès
 */

class Auth
{
    /**
     * Démarrer la session
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
    }

    /**
     * Vérifier si l'utilisateur est connecté
     */
    public static function check()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    public static function isAdmin()
    {
        return self::check() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Obtenir l'utilisateur connecté
     */
    public static function user()
    {
        if (!self::check()) return null;

        return [
            'id' => $_SESSION['user_id'],
            'nom' => $_SESSION['user_nom'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'role' => $_SESSION['user_role'] ?? 'user'
        ];
    }

    /**
     * Connecter un utilisateur
     */
    public static function login($userId, $userName, $userEmail, $userRole = 'user')
    {
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_nom'] = $userName;
        $_SESSION['user_email'] = $userEmail;
        $_SESSION['user_role'] = $userRole;
        $_SESSION['login_time'] = time();
    }

    /**
     * Déconnecter l'utilisateur
     */
    public static function logout()
    {
        session_destroy();
        redirect(BASE_URL . '/auth/login.php');
    }

    /**
     * Protéger une route (requiert connexion)
     */
    public static function protect($redirect = null)
    {
        if (!self::check()) {
            setFlash('error', 'Vous devez être connecté.');
            redirect($redirect ?? BASE_URL . '/auth/login.php');
        }
    }

    /**
     * Protéger une route admin (requiert admin)
     */
    public static function protectAdmin($redirect = null)
    {
        if (!self::isAdmin()) {
            setFlash('error', 'Accès refusé. Droits admin requis.');
            redirect($redirect ?? BASE_URL . '/index.php');
        }
    }

    /**
     * Vérifier le code d'accès admin
     */
    public static function checkAdminCode($code)
    {
        return $code === ADMIN_CODE;
    }

    /**
     * Vérifier la session
     */
    public static function verifySession()
    {
        if (!self::check()) return false;

        // Vérifier le timeout de session
        if (SESSION_TIMEOUT > 0) {
            if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
                self::logout();
                return false;
            }
            $_SESSION['login_time'] = time();
        }

        return true;
    }
}

// Démarrer la session automatiquement
Auth::start();
?>
