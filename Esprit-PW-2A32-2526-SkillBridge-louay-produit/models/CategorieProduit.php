<?php
// Modèle CategorieProduit - représente une catégorie de produits
class CategorieProduit {
    private $id_categorie;
    private $nom_categorie;
    private $description;
    private $icone;
    private $created_at;
    private $nb_produits; // utilisé pour afficher le nombre de produits (jointure)


    // -- Getters --
    public function getId() { return $this->id_categorie; }
    public function getNomCategorie() { return $this->nom_categorie; }
    public function getDescription() { return $this->description; }
    public function getIcone() { return $this->icone; }
    public function getCreatedAt() { return $this->created_at; }
    public function getNbProduits() { return $this->nb_produits; }

    // -- Setters --
    public function setId($id_categorie) { $this->id_categorie = $id_categorie; }
    public function setNomCategorie($nom_categorie) { $this->nom_categorie = $nom_categorie; }
    public function setDescription($description) { $this->description = $description; }
    public function setIcone($icone) { $this->icone = $icone; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setNbProduits($nb_produits) { $this->nb_produits = $nb_produits; }
}
?>
