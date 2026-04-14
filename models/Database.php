<?php
class Database
{
    private static $connection = null;

    public static function getConnection()
    {
        if (self::$connection === null) {
            $host = '127.0.0.1';
            $port = '3307';
            $dbname = 'projet';
            $user = 'root';
            $password = '';

            try {
                $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
                self::$connection = new PDO($dsn, $user, $password);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                die('Erreur de connexion a la base de donnees: ' . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
