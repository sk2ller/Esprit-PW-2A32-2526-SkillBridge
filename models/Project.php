<?php
class Project
{
    private $id;
    private $titre;
    private $description;
    private $budget;
    private $date_creation;
    private $statut;

    public function __construct(
        $titre = null,
        $description = null,
        $budget = 0,
        $date_creation = null,
        $statut = 'en_attente'
    ) {
        $this->titre = $titre;
        $this->description = $description;
        $this->budget = (float)$budget;
        $this->date_creation = $date_creation ?: date('Y-m-d');
        $this->statut = $statut;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = (int)$id; }

    public function getTitre() { return $this->titre; }
    public function setTitre($titre) { $this->titre = $titre; }

    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }

    public function getBudget() { return $this->budget; }
    public function setBudget($budget) { $this->budget = (float)$budget; }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($date_creation) { $this->date_creation = $date_creation; }

    public function getStatut() { return $this->statut; }
    public function setStatut($statut) { $this->statut = $statut; }
}
?>