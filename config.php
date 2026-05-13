<?php
<<<<<<< HEAD

// Base URL for assets - adjust if running in a subdirectory
define('BASE_URL', (function() {
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    return rtrim($script, '/');
})());

=======
>>>>>>> 70c0bf137c21f2ed3b54e66ff17647b5ff291330
class Config
{
    private static $connexion = null;
    private static $openRouterApiKey = '';
    private static $emailJsServiceId = 'service_saxy6dj';
    private static $emailJsTemplateId = 'template_1ndnibx';
    private static $emailJsSecurityTemplateId = '';
    private static $emailJsPublicKey = 'kOZhnkpxmJa5vxl1f';
    private static $emailJsAccessToken = 'FDkvPiSWgNuNGiuyVmMwj';
    
    public static function getConnexion()
    {
        if (self::$connexion === null) {
            try {
                self::$connexion = new PDO(
<<<<<<< HEAD
                    'mysql:host=localhost;port=3307;dbname=skillbridge',
=======
                    'mysql:host=localhost;dbname=skillbridge',
>>>>>>> 70c0bf137c21f2ed3b54e66ff17647b5ff291330
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

    public static function getEmailJsConfig()
    {
        return [
            'service_id' => self::envOrDefault('EMAILJS_SERVICE_ID', self::$emailJsServiceId),
            'template_id' => self::envOrDefault('EMAILJS_TEMPLATE_ID', self::$emailJsTemplateId),
            'security_template_id' => self::envOrDefault('EMAILJS_SECURITY_TEMPLATE_ID', self::$emailJsSecurityTemplateId),
            'public_key' => self::envOrDefault('EMAILJS_PUBLIC_KEY', self::$emailJsPublicKey),
            'access_token' => self::envOrDefault('EMAILJS_ACCESS_TOKEN', self::$emailJsAccessToken),
        ];
    }

    private static function envOrDefault($name, $default)
    {
        $value = getenv($name);
        if ($value !== false && trim($value) !== '') {
            return trim($value);
        }

        return $default;
    }
}
?>
