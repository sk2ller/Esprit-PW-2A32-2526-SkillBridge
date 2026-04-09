<?php
declare(strict_types=1);

namespace App\Modele;

final class Commande
{
    public function __construct(
        private ?int $id,
        private string $statut,
        private float $prix_total,
        private string $date_creation,
        private string $date_livraison,
        private int $id_service,
        private int $id_client
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getPrixTotal(): float
    {
        return $this->prix_total;
    }

    public function getDateCreation(): string
    {
        return $this->date_creation;
    }

    public function getDateLivraison(): string
    {
        return $this->date_livraison;
    }

    public function getIdService(): int
    {
        return $this->id_service;
    }

    public function getIdClient(): int
    {
        return $this->id_client;
    }
}
