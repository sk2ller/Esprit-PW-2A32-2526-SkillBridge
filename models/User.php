<?php
class User
{
    private $id_user;
    private $nom;
    private $prenom;
    private $email;
    private $mot_de_passe;
    private $niveau;
    private $id_role;
    private $badge_verifie;

    public function __construct(
        $nom = null,
        $prenom = null,
        $email = null,
        $mot_de_passe = null,
        $niveau = 'débutant',
        $id_role = 2,
        $badge_verifie = 0
    ) {
        $this->nom           = $nom;
        $this->prenom        = $prenom;
        $this->email         = $email;
        $this->mot_de_passe  = $mot_de_passe;
        $this->niveau        = $niveau;
        $this->id_role       = $id_role;
        $this->badge_verifie = $badge_verifie;
    }

    public function getIdUser()         { return $this->id_user; }
    public function setIdUser($id)      { $this->id_user = $id; }

    public function getNom()            { return $this->nom; }
    public function setNom($nom)        { $this->nom = $nom; }

    public function getPrenom()         { return $this->prenom; }
    public function setPrenom($p)       { $this->prenom = $p; }

    public function getEmail()          { return $this->email; }
    public function setEmail($e)        { $this->email = $e; }

    public function getMotDePasse()     { return $this->mot_de_passe; }
    public function setMotDePasse($m)   { $this->mot_de_passe = $m; }

    public function getNiveau()         { return $this->niveau; }
    public function setNiveau($n)       { $this->niveau = $n; }

    public function getIdRole()         { return $this->id_role; }
    public function setIdRole($r)       { $this->id_role = $r; }

    public function getBadgeVerifie()   { return $this->badge_verifie; }
    public function setBadgeVerifie($b) { $this->badge_verifie = $b; }
}
