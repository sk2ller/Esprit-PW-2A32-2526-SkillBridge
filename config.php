<?php
class Config
{
    private static $connexion = null;
    private static $openRouterApiKey = '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';
    
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

    public static function getOpenRouterApiKey()
    {
        $envKey = getenv('OPENROUTER_API_KEY');
        if ($envKey !== false && trim($envKey) !== '') {
            return trim($envKey);
        }

        return self::$openRouterApiKey;
    }
}
?>
