<?php
class Candidature
{
    private $id;
    private $id_projet;
    private $id_freelancer;
    private $statut;
    private $created_at;
    // extras (JOIN)
    private $titre_projet;
    private $nom_freelancer;
    private $prenom_freelancer;

    public function __construct($id_projet = null, $id_freelancer = null, $statut = 'en_attente')
    {
        $this->id_projet     = $id_projet;
        $this->id_freelancer = $id_freelancer;
        $this->statut        = $statut;
    }

    public function getId()               { return $this->id; }
    public function setId($v)             { $this->id = $v; }
    public function getIdProjet()         { return $this->id_projet; }
    public function setIdProjet($v)       { $this->id_projet = $v; }
    public function getIdFreelancer()     { return $this->id_freelancer; }
    public function setIdFreelancer($v)   { $this->id_freelancer = $v; }
    public function getStatut()           { return $this->statut; }
    public function setStatut($v)         { $this->statut = $v; }
    public function getCreatedAt()        { return $this->created_at; }
    public function setCreatedAt($v)      { $this->created_at = $v; }
    public function getTitreProjet()      { return $this->titre_projet; }
    public function setTitreProjet($v)    { $this->titre_projet = $v; }
    public function getNomFreelancer()    { return $this->nom_freelancer; }
    public function setNomFreelancer($v)  { $this->nom_freelancer = $v; }
    public function getPrenomFreelancer() { return $this->prenom_freelancer; }
    public function setPrenomFreelancer($v){ $this->prenom_freelancer = $v; }
}
