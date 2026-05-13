<?php
class Project
{
    private $id;
    private $titre;
    private $description;
    private $budget;
    private $date_creation;
    private $statut;
    private $etat;
    private $id_client;
    private $avancement = 0;
    private $nom_client = '';

    public function __construct(
        $titre = null,
        $description = null,
        $budget = 0,
        $date_creation = null,
        $statut = 'en_attente',
        $etat = 'en_attente_validation',
        $id_client = null
    ) {
        $this->titre = $titre;
        $this->description = $description;
        $this->budget = $budget;
        $this->date_creation = $date_creation ?? date('Y-m-d');
        $this->statut = $statut;
        $this->etat = $etat;
        $this->id_client = $id_client;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getTitre() { return $this->titre; }
    public function setTitre($titre) { $this->titre = $titre; }

    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }

    public function getBudget() { return $this->budget; }
    public function setBudget($budget) { $this->budget = $budget; }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($date_creation) { $this->date_creation = $date_creation; }

    public function getStatut() { return $this->statut; }
    public function setStatut($statut) { $this->statut = $statut; }

    public function getEtat() { return $this->etat; }
    public function setEtat($etat) { $this->etat = $etat; }

    public function getIdClient() { return $this->id_client; }
    public function setIdClient($id_client) { $this->id_client = $id_client; }

    public function getAvancement() { return $this->avancement; }
    public function setAvancement($a) { $this->avancement = (int)$a; }

    public function getNomClient() { return $this->nom_client; }
    public function setNomClient($v) { $this->nom_client = trim($v); }
}
