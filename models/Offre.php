<?php

class Offre
{
    private $id_offre;
    private $titre;
    private $description;
    private $budget;
    private $delai_publication;
    private $niveau_requis;
    private $competences_requises;
    private $statut;
    private $id_client;
    private $created_at;
    private $updated_at;

    public function __construct(
        $titre = null,
        $description = null,
        $budget = null,
        $delai_publication = 30,
        $niveau_requis = 'intermediaire',
        $competences_requises = null,
        $statut = 'en_attente',
        $id_client = null
    ) {
        $this->titre = $titre;
        $this->description = $description;
        $this->budget = $budget;
        $this->delai_publication = $delai_publication;
        $this->niveau_requis = $niveau_requis;
        $this->competences_requises = $competences_requises;
        $this->statut = $statut;
        $this->id_client = $id_client;
    }

    public function getIdOffre()                  { return $this->id_offre; }
    public function setIdOffre($id)               { $this->id_offre = $id; }

    public function getTitre()                    { return $this->titre; }
    public function setTitre($titre)              { $this->titre = $titre; }

    public function getDescription()              { return $this->description; }
    public function setDescription($description)  { $this->description = $description; }

    public function getBudget()                   { return $this->budget; }
    public function setBudget($budget)            { $this->budget = $budget; }

    public function getDelaiPublication()         { return $this->delai_publication; }
    public function setDelaiPublication($delai)   { $this->delai_publication = $delai; }

    public function getNiveauRequis()             { return $this->niveau_requis; }
    public function setNiveauRequis($niveau)      { $this->niveau_requis = $niveau; }

    public function getCompetencesRequises()      { return $this->competences_requises; }
    public function setCompetencesRequises($comp) { $this->competences_requises = $comp; }

    public function getStatut()                   { return $this->statut; }
    public function setStatut($statut)            { $this->statut = $statut; }

    public function getIdClient()                 { return $this->id_client; }
    public function setIdClient($idClient)        { $this->id_client = $idClient; }

    public function getCreatedAt()                { return $this->created_at; }
    public function setCreatedAt($date)           { $this->created_at = $date; }

    public function getUpdatedAt()                { return $this->updated_at; }
    public function setUpdatedAt($date)           { $this->updated_at = $date; }
}
