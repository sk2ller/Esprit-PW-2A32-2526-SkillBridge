<?php
class Tache
{
    private $id;
    private $id_projet;
    private $id_freelancer;
    private $titre;
    private $description;
    private $statut;
    private $created_at;
    private $prix;

    public function __construct($id_projet = null, $id_freelancer = null, $titre = null, $description = null, $statut = 'a_faire', $prix = 0)
    {
        $this->id_projet     = $id_projet;
        $this->id_freelancer = $id_freelancer;
        $this->titre         = $titre;
        $this->description   = $description;
        $this->statut        = $statut;
        $this->prix          = $prix;
    }

    public function getId()           { return $this->id; }
    public function setId($v)         { $this->id = $v; }
    public function getIdProjet()     { return $this->id_projet; }
    public function setIdProjet($v)   { $this->id_projet = $v; }
    public function getIdFreelancer() { return $this->id_freelancer; }
    public function setIdFreelancer($v){ $this->id_freelancer = $v; }
    public function getTitre()        { return $this->titre; }
    public function setTitre($v)      { $this->titre = $v; }
    public function getDescription()  { return $this->description; }
    public function setDescription($v){ $this->description = $v; }
    public function getStatut()       { return $this->statut; }
    public function setStatut($v)     { $this->statut = $v; }
    public function getPrix()         { return $this->prix; }
    public function setPrix($v)       { $this->prix = (float)$v; }
    public function getCreatedAt()    { return $this->created_at; }
    public function setCreatedAt($v)  { $this->created_at = $v; }
}
