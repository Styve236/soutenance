<?php
/**
 * Classe Database - Gestion de la connexion à la Base de Données
 * Permet une meilleure gestion des connexions et requêtes
 */

class Database
{
    private static $conn = null;
    private $lastError = null;

    /**
     * Établir une connexion à la base de données
     */
    public static function connect()
    {
        if (self::$conn === null) {
            self::$conn = mysqli_connect(
                DB_HOST,
                DB_USER,
                DB_PASS,
                DB_NAME
            );

            if (!self::$conn) {
                die('Erreur de connexion: ' . mysqli_connect_error());
            }

            // Définir le charset UTF-8
            mysqli_set_charset(self::$conn, 'utf8mb4');
        }

        return self::$conn;
    }

    /**
     * Exécuter une requête SELECT
     */
    public static function query($sql)
    {
        $conn = self::connect();
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            self::$lastError = mysqli_error($conn);
            return false;
        }

        return $result;
    }

    /**
     * Exécuter une requête SELECT et retourner tous les résultats
     */
    public static function getAll($sql)
    {
        $result = self::query($sql);
        if (!$result) return [];

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Exécuter une requête SELECT et retourner une seule ligne
     */
    public static function getOne($sql)
    {
        $result = self::query($sql);
        if (!$result) return null;

        return mysqli_fetch_assoc($result);
    }

    /**
     * Exécuter une requête INSERT/UPDATE/DELETE
     */
    public static function execute($sql)
    {
        $conn = self::connect();
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            self::$lastError = mysqli_error($conn);
            return false;
        }

        return true;
    }

    /**
     * Obtenir l'ID de la dernière insertion
     */
    public static function getLastId()
    {
        $conn = self::connect();
        return mysqli_insert_id($conn);
    }

    /**
     * Compter le nombre de lignes affectées
     */
    public static function affectedRows()
    {
        $conn = self::connect();
        return mysqli_affected_rows($conn);
    }

    /**
     * Échapper une chaîne de caractères
     */
    public static function escape($str)
    {
        $conn = self::connect();
        return mysqli_real_escape_string($conn, $str);
    }

    /**
     * Obtenir la dernière erreur
     */
    public static function getError()
    {
        return self::$lastError;
    }

    /**
     * Fermer la connexion
     */
    public static function close()
    {
        if (self::$conn) {
            mysqli_close(self::$conn);
            self::$conn = null;
        }
    }
}

// Connexion automatique
Database::connect();
?>
