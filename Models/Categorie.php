<?php
class Categorie
{
    private $id;
    private $nom;
    private $description;
    private $icone;

    public function __construct($nom = '', $description = '', $icone = 'fas fa-folder')
    {
        $this->nom = $nom;
        $this->description = $description;
        $this->icone = $icone;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getNom() { return $this->nom; }
    public function setNom($nom) { $this->nom = $nom; }
    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }
    public function getIcone() { return $this->icone; }
    public function setIcone($icone) { $this->icone = $icone; }
}
?>
