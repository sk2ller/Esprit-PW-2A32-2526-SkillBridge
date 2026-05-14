<?php
// Modèle Commande - représente une commande sur SkillBridge
class Commande {
    private $id_commande;
    private $nom_client;
    private $email_client;
    private $telephone;
    private $adresse;
    private $id_produit;
    private $quantite;
    private $prix_total;
    private $statut;
    private $note;
    private $rating;
    private $review;
    private $created_at;
    private $updated_at;
    private $nom_produit; // utilisé pour afficher le nom du produit (jointure)



    // -- Getters --
    public function getId() { return $this->id_commande; }
    public function getNomClient() { return $this->nom_client; }
    public function getEmailClient() { return $this->email_client; }
    public function getTelephone() { return $this->telephone; }
    public function getAdresse() { return $this->adresse; }
    public function getIdProduit() { return $this->id_produit; }
    public function getQuantite() { return $this->quantite; }
    public function getPrixTotal() { return $this->prix_total; }
    public function getStatut() { return $this->statut; }
    public function getNote() { return $this->note; }
    public function getRating() { return $this->rating; }
    public function getReview() { return $this->review; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getNomProduit() { return $this->nom_produit; }

    // -- Setters --
    public function setId($id_commande) { $this->id_commande = $id_commande; }
    public function setNomClient($nom_client) { $this->nom_client = $nom_client; }
    public function setEmailClient($email_client) { $this->email_client = $email_client; }
    public function setTelephone($telephone) { $this->telephone = $telephone; }
    public function setAdresse($adresse) { $this->adresse = $adresse; }
    public function setIdProduit($id_produit) { $this->id_produit = $id_produit; }
    public function setQuantite($quantite) { $this->quantite = $quantite; }
    public function setPrixTotal($prix_total) { $this->prix_total = $prix_total; }
    public function setStatut($statut) { $this->statut = $statut; }
    public function setNote($note) { $this->note = $note; }
    public function setRating($rating) { $this->rating = $rating; }
    public function setReview($review) { $this->review = $review; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    public function setNomProduit($nom_produit) { $this->nom_produit = $nom_produit; }
}
?>
