<?php
class Service
{
    private $id;
    private $titre;
    private $description;
    private $prix;
    private $delaiLivraison;
    private $statut;
    private $categorieId;
    private $freelancerId;
    private $thumbnail;

    public function __construct($titre = '', $description = '', $prix = 0, $delaiLivraison = 1, $categorieId = 0, $freelancerId = 0, $statut = 'en_attente', $thumbnail = null)
    {
        $this->titre = $titre;
        $this->description = $description;
        $this->prix = $prix;
        $this->delaiLivraison = $delaiLivraison;
        $this->categorieId = $categorieId;
        $this->freelancerId = $freelancerId;
        $this->statut = $statut;
        $this->thumbnail = $thumbnail;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getTitre() { return $this->titre; }
    public function setTitre($titre) { $this->titre = $titre; }
    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }
    public function getPrix() { return $this->prix; }
    public function setPrix($prix) { $this->prix = $prix; }
    public function getDelaiLivraison() { return $this->delaiLivraison; }
    public function setDelaiLivraison($delaiLivraison) { $this->delaiLivraison = $delaiLivraison; }
    public function getStatut() { return $this->statut; }
    public function setStatut($statut) { $this->statut = $statut; }
    public function getCategorieId() { return $this->categorieId; }
    public function setCategorieId($categorieId) { $this->categorieId = $categorieId; }
    public function getFreelancerId() { return $this->freelancerId; }
    public function setFreelancerId($freelancerId) { $this->freelancerId = $freelancerId; }
    public function getThumbnail() { return $this->thumbnail; }
    public function setThumbnail($thumbnail) { $this->thumbnail = $thumbnail; }
}
?>
