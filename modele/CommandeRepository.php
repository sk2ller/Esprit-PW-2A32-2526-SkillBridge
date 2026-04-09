<?php
declare(strict_types=1);

namespace App\Modele;

use PDO;

final class CommandeRepository
{
    public function __construct(private Database $database)
    {
    }

    /**
     * Récupère toutes les commandes
     * @return Commande[]
     */
    public function findAll(): array
    {
        $pdo = $this->database->getPdo();
        $stmt = $pdo->prepare('SELECT * FROM commandes ORDER BY id DESC');
        $stmt->execute();

        $commandes = [];
        while ($row = $stmt->fetch()) {
            $commandes[] = $this->hydrate($row);
        }

        return $commandes;
    }

    /**
     * Récupère une commande par ID
     */
    public function findById(int $id): ?Commande
    {
        $pdo = $this->database->getPdo();
        $stmt = $pdo->prepare('SELECT * FROM commandes WHERE id = ?');
        $stmt->execute([$id]);

        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /**
     * Crée une nouvelle commande
     */
    public function create(Commande $commande): int
    {
        $pdo = $this->database->getPdo();
        $stmt = $pdo->prepare(
            'INSERT INTO commandes (statut, prix_total, date_creation, date_livraison, id_service, id_client)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $commande->getStatut(),
            $commande->getPrixTotal(),
            $commande->getDateCreation(),
            $commande->getDateLivraison(),
            $commande->getIdService(),
            $commande->getIdClient(),
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Met à jour une commande existante
     */
    public function update(Commande $commande): void
    {
        $pdo = $this->database->getPdo();
        $stmt = $pdo->prepare(
            'UPDATE commandes 
             SET statut = ?, prix_total = ?, date_creation = ?, date_livraison = ?, id_service = ?, id_client = ?
             WHERE id = ?'
        );

        $stmt->execute([
            $commande->getStatut(),
            $commande->getPrixTotal(),
            $commande->getDateCreation(),
            $commande->getDateLivraison(),
            $commande->getIdService(),
            $commande->getIdClient(),
            $commande->getId(),
        ]);
    }

    /**
     * Supprime une commande
     */
    public function delete(int $id): void
    {
        $pdo = $this->database->getPdo();
        $stmt = $pdo->prepare('DELETE FROM commandes WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * Convertit une ligne DB en objet Commande
     */
    private function hydrate(array $row): Commande
    {
        return new Commande(
            (int)$row['id'],
            $row['statut'],
            (float)$row['prix_total'],
            $row['date_creation'],
            $row['date_livraison'],
            (int)$row['id_service'],
            (int)$row['id_client']
        );
    }
}
