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
    private $is_approved;
    private $is_banned;
    private $availability;
    private $rating;
    private $created_at;
    private $updated_at;

    public function __construct(
        $nom = null,
        $prenom = null,
        $email = null,
        $mot_de_passe = null,
        $niveau = 'débutant',
        $id_role = 2,
        $badge_verifie = 0,
        $is_approved = 0,
        $is_banned = 0,
        $availability = 'available',
        $rating = 0.00
    ) {
        $this->nom           = $nom;
        $this->prenom        = $prenom;
        $this->email         = $email;
        $this->mot_de_passe  = $mot_de_passe;
        $this->niveau        = $niveau;
        $this->id_role       = $id_role;
        $this->badge_verifie = $badge_verifie;
        $this->is_approved   = $is_approved;
        $this->is_banned     = $is_banned;
        $this->availability  = $availability;
        $this->rating        = $rating;
    }

    // Getters and Setters
    public function getIdUser()                { return $this->id_user; }
    public function setIdUser($id)             { $this->id_user = $id; }

    public function getNom()                   { return $this->nom; }
    public function setNom($nom)               { $this->nom = $nom; }

    public function getPrenom()                { return $this->prenom; }
    public function setPrenom($p)              { $this->prenom = $p; }

    public function getEmail()                 { return $this->email; }
    public function setEmail($e)               { $this->email = $e; }

    public function getMotDePasse()            { return $this->mot_de_passe; }
    public function setMotDePasse($m)          { $this->mot_de_passe = $m; }

    public function getNiveau()                { return $this->niveau; }
    public function setNiveau($n)              { $this->niveau = $n; }

    public function getIdRole()                { return $this->id_role; }
    public function setIdRole($r)              { $this->id_role = $r; }

    public function getBadgeVerifie()          { return $this->badge_verifie; }
    public function setBadgeVerifie($b)        { $this->badge_verifie = $b; }

    public function getIsApproved()            { return $this->is_approved; }
    public function setIsApproved($a)          { $this->is_approved = $a; }

    public function getIsBanned()              { return $this->is_banned; }
    public function setIsBanned($b)            { $this->is_banned = $b; }

    public function getAvailability()          { return $this->availability; }
    public function setAvailability($av)       { $this->availability = $av; }

    public function getRating()                { return $this->rating; }
    public function setRating($r)              { $this->rating = $r; }

    public function getCreatedAt()             { return $this->created_at; }
    public function setCreatedAt($c)           { $this->created_at = $c; }

    public function getUpdatedAt()             { return $this->updated_at; }
    public function setUpdatedAt($u)           { $this->updated_at = $u; }
}
