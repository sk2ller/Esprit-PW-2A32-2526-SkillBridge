<?php

class Service
{
    private $id_service;
    private $titre;
    private $description;
    private $prix;
    private $delai_livraison;
    private $statut;
    private $id_categorie;
    private $freelancer_name;
    private $nom_categorie;
    private $created_at;
    private $cv;
    private $portfolio;
    private $thumbnail;


    public function __construct(
        $titre = null,
        $description = null,
        $prix = null,
        $delai_livraison = null,
        $id_categorie = null,
        $statut = 'en_attente',
        $freelancer_name = 'Freelancer Demo',
        $cv = null,
        $portfolio = null,
        $thumbnail = null
    ) {
        $this->titre = $titre;
        $this->description = $description;
        $this->prix = $prix;
        $this->delai_livraison = $delai_livraison;
        $this->id_categorie = $id_categorie;
        $this->statut = $statut;
        $this->freelancer_name = $freelancer_name;
        $this->cv = $cv;
        $this->portfolio = $portfolio;
        $this->thumbnail = $thumbnail;
    }

    public function getId()                      { return $this->id_service; }
    public function setId($id)                   { $this->id_service = $id; }

    public function getTitre()                   { return $this->titre; }
    public function setTitre($titre)             { $this->titre = $titre; }

    public function getDescription()             { return $this->description; }
    public function setDescription($d)           { $this->description = $d; }

    public function getPrix()                    { return $this->prix; }
    public function setPrix($prix)               { $this->prix = $prix; }

    public function getDelaiLivraison()          { return $this->delai_livraison; }
    public function setDelaiLivraison($d)        { $this->delai_livraison = $d; }

    public function getStatut()                  { return $this->statut; }
    public function setStatut($statut)           { $this->statut = $statut; }

    public function getIdCategorie()             { return $this->id_categorie; }
    public function setIdCategorie($id)          { $this->id_categorie = $id; }

    public function getFreelancerName()          { return $this->freelancer_name; }
    public function setFreelancerName($name)     { $this->freelancer_name = $name; }

    public function getNomCategorie()            { return $this->nom_categorie; }
    public function setNomCategorie($nom)        { $this->nom_categorie = $nom; }

    public function getCreatedAt()               { return $this->created_at; }
    public function setCreatedAt($date)          { $this->created_at = $date; }

    public function getCv()                      { return $this->cv; }
    public function setCv($cv)                   { $this->cv = $cv; }

    public function getPortfolio()               { return $this->portfolio; }
    public function setPortfolio($portfolio)     { $this->portfolio = $portfolio; }

    public function getThumbnail()               { return $this->thumbnail; }
    public function setThumbnail($thumbnail)     { $this->thumbnail = $thumbnail; }
}
