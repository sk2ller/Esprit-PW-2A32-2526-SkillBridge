<?php
// Modèle Produit - représente un produit numérique sur SkillBridge
class Produit {
    private $id_produit;
    private $nom;
    private $description;
    private $prix;
    private $quantite;
    private $statut;
    private $image;
    private $id_categorie;
    private $id_vendeur;
    private $created_at;
    private $updated_at;
    private $nom_categorie; // utilisé pour afficher le nom de la catégorie (jointure)


    // -- Getters --
    public function getId() { return $this->id_produit; }
    public function getNom() { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getPrix() { return $this->prix; }
    public function getQuantite() { return $this->quantite; }
    public function getStatut() { return $this->statut; }
    public function getImage() { return $this->image; }
    public function getIdCategorie() { return $this->id_categorie; }
    public function getIdVendeur() { return $this->id_vendeur; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getNomCategorie() { return $this->nom_categorie; }

    // -- Setters --
    public function setId($id_produit) { $this->id_produit = $id_produit; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setDescription($description) { $this->description = $description; }
    public function setPrix($prix) { $this->prix = $prix; }
    public function setQuantite($quantite) { $this->quantite = $quantite; }
    public function setStatut($statut) { $this->statut = $statut; }
    public function setImage($image) { $this->image = $image; }
    public function setIdCategorie($id_categorie) { $this->id_categorie = $id_categorie; }
    public function setIdVendeur($id_vendeur) { $this->id_vendeur = $id_vendeur; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    public function setNomCategorie($nom_categorie) { $this->nom_categorie = $nom_categorie; }
}
?>
