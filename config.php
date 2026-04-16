<?php
class Config
{
    private static $connexion = null;
    
    public static function getConnexion()
    {
        if (self::$connexion === null) {
            $dsnCandidates = [
                'mysql:host=127.0.0.1;port=3307;dbname=skillbridge;charset=utf8mb4',
                'mysql:host=localhost;port=3307;dbname=skillbridge;charset=utf8mb4',
                'mysql:host=127.0.0.1;port=3306;dbname=skillbridge;charset=utf8mb4',
                'mysql:host=localhost;port=3306;dbname=skillbridge;charset=utf8mb4'
            ];

            $lastError = null;
            foreach ($dsnCandidates as $dsn) {
                try {
                    self::$connexion = new PDO(
                        $dsn,
                        'root',
                        '',
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    break;
                } catch (PDOException $e) {
                    $lastError = $e->getMessage();
                }
            }

            if (self::$connexion === null) {
                die('Connection Error: ' . $lastError);
            }
        }
        return self::$connexion;
    }
}
?>
