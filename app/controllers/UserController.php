<?php
/**
 * User Controller - Gère les utilisateurs
 */

class UserController
{
    /**
     * Enregistrer un nouvel utilisateur
     */
    public static function register($data)
    {
        // Valider les données
        if (empty($data['nom']) || empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'error' => 'Tous les champs sont requis.'];
        }

        // Vérifier si l'email existe déjà
        $existing = Database::getOne("SELECT id FROM users WHERE email = '" . Database::escape($data['email']) . "'");
        if ($existing) {
            return ['success' => false, 'error' => 'Cet email est déjà utilisé.'];
        }

        // Préparer les données
        $nom = Database::escape($data['nom']);
        $email = Database::escape($data['email']);
        $telephone = Database::escape($data['telephone'] ?? '');
        $adresse = Database::escape($data['adresse'] ?? '');
        $password = password_hash($data['password'], PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);

        // Insérer l'utilisateur
        $sql = "INSERT INTO users (nom, email, telephone, adresse, mot_de_passe) 
                VALUES ('$nom', '$email', '$telephone', '$adresse', '$password')";

        if (!Database::execute($sql)) {
            return ['success' => false, 'error' => 'Erreur lors de l\'enregistrement.'];
        }

        $userId = Database::getLastId();

        // Enregistrer dans la table d'enregistrement
        Database::execute("INSERT INTO user_registrations (user_id) VALUES ($userId)");

        logEvent('INFO', "Nouvel utilisateur enregistré", ['user_id' => $userId, 'email' => $email]);

        return ['success' => true, 'user_id' => $userId, 'message' => 'Compte créé avec succès.'];
    }

    /**
     * Connecter un utilisateur
     */
    public static function login($email, $password)
    {
        // Valider les données
        if (empty($email) || empty($password)) {
            return ['success' => false, 'error' => 'Email et mot de passe requis.'];
        }

        // Récupérer l'utilisateur
        $user = Database::getOne("SELECT * FROM users WHERE email = '" . Database::escape($email) . "'");

        if (!$user) {
            return ['success' => false, 'error' => 'Identifiants invalides.'];
        }

        // Vérifier le mot de passe
        if (!password_verify($password, $user['mot_de_passe'])) {
            return ['success' => false, 'error' => 'Identifiants invalides.'];
        }

        // Connecter l'utilisateur
        Auth::login($user['id'], $user['nom'], $user['email'], $user['role'] ?? 'user');

        logEvent('INFO', "Utilisateur connecté", ['user_id' => $user['id'], 'email' => $email]);

        return ['success' => true, 'user_id' => $user['id'], 'message' => 'Connexion réussie.'];
    }

    /**
     * Obtenir les informations d'un utilisateur
     */
    public static function getUser($userId)
    {
        return Database::getOne("SELECT id, nom, email, telephone, adresse, role FROM users WHERE id = $userId");
    }

    /**
     * Mettre à jour les informations d'un utilisateur
     */
    public static function updateUser($userId, $data)
    {
        $updates = [];

        if (isset($data['nom'])) {
            $updates[] = "nom = '" . Database::escape($data['nom']) . "'";
        }
        if (isset($data['email'])) {
            $updates[] = "email = '" . Database::escape($data['email']) . "'";
        }
        if (isset($data['telephone'])) {
            $updates[] = "telephone = '" . Database::escape($data['telephone']) . "'";
        }
        if (isset($data['adresse'])) {
            $updates[] = "adresse = '" . Database::escape($data['adresse']) . "'";
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $updates[] = "mot_de_passe = '" . password_hash($data['password'], PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]) . "'";
        }

        if (empty($updates)) {
            return ['success' => false, 'error' => 'Aucune donnée à mettre à jour.'];
        }

        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = $userId";

        if (!Database::execute($sql)) {
            return ['success' => false, 'error' => 'Erreur lors de la mise à jour.'];
        }

        logEvent('INFO', "Utilisateur mis à jour", ['user_id' => $userId]);

        return ['success' => true, 'message' => 'Profil mis à jour avec succès.'];
    }

    /**
     * Lister tous les utilisateurs (admin)
     */
    public static function listUsers($limit = 10, $offset = 0)
    {
        $users = Database::getAll("SELECT id, nom, email, role, DATE(created_at) as date_inscription FROM users LIMIT $offset, $limit");
        $total = Database::getOne("SELECT COUNT(*) as count FROM users");

        return [
            'users' => $users,
            'total' => $total['count'] ?? 0,
            'limit' => $limit,
            'offset' => $offset
        ];
    }
}
?>
