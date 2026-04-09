<?php
declare(strict_types=1);

namespace App\Modele;

use PDO;
use PDOException;

final class Database
{
    private static ?self $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $dbPath = __DIR__ . '/commandes.db';
        
        try {
            $this->pdo = new PDO(
                'sqlite:' . $dbPath,
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
            
            // Créer la table si elle n'existe pas
            $this->initializeSchema();
        } catch (PDOException $e) {
            throw new \RuntimeException('Erreur de connexion à la base de données: ' . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    private function initializeSchema(): void
    {
        $schema = <<<'SQL'
CREATE TABLE IF NOT EXISTS commandes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    statut TEXT NOT NULL CHECK(statut IN ('en_attente', 'en_cours', 'livree', 'annulee')),
    prix_total REAL NOT NULL,
    date_creation TEXT NOT NULL,
    date_livraison TEXT NOT NULL,
    id_service INTEGER NOT NULL,
    id_client INTEGER NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
SQL;

        $this->pdo->exec($schema);
    }
}
