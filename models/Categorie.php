<?php

class Categorie
{
    private $id_categorie;
    private $nom_categorie;
    private $description;
    private $icone;

    public function __construct(
        $nom_categorie = null,
        $description = null,
        $icone = 'fas fa-folder'
    ) {
        $this->nom_categorie = $nom_categorie;
        $this->description = $description;
        $this->icone = $icone;
    }

    public function getId()               { return $this->id_categorie; }
    public function setId($id)            { $this->id_categorie = $id; }
    public function getNom()              { return $this->nom_categorie; }
    public function setNom($nom)          { $this->nom_categorie = $nom; }
    public function getDescription()      { return $this->description; }
    public function setDescription($d)    { $this->description = $d; }
    public function getIcone()            { return $this->icone; }
    public function setIcone($icone)      { $this->icone = $icone; }
}
