<?php
class Config
{
    private static $connexion = null;
    
    public static function getConnexion()
    {
        if (self::$connexion === null) {
            try {
                self::$connexion = new PDO(
                    'mysql:host=localhost;dbname=skillbridge',
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
