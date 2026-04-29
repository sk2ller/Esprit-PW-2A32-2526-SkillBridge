<?php
// Base URL — détecté automatiquement selon le dossier du projet
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));

class Config
{
    private static $connexion = null;
    
    public static function getConnexion()
    {
        if (self::$connexion === null) {
            try {
                self::$connexion = new PDO(
                    'mysql:host=127.0.0.1;port=3307;dbname=skillbridge',
                    'root',
                    '',
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $e) {
                die('Connection Error: ' . $e->getMessage());
            }
        }
        return self::$connexion;
    }
}
?>
