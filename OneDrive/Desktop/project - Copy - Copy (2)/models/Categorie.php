<?php
require_once __DIR__ . '/../config.php';

class Categorie {
    // Attributs privés
    private $id_categorie;
    private $nom_categorie;
    private $description;
    private $icone;
    private $db;

    // Constructeur
    public function __construct($id_categorie = null, $nom_categorie = null, $description = null, $icone = null) {
        $this->id_categorie = $id_categorie;
        $this->nom_categorie = $nom_categorie;
        $this->description = $description;
        $this->icone = $icone;
        $this->db = getDB();
    }

    // Getters
    public function getId() {
        return $this->id_categorie;
    }

    public function getNom() {
        return $this->nom_categorie;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getIcone() {
        return $this->icone;
    }

    // Setters
    public function setId($id_categorie) {
        $this->id_categorie = $id_categorie;
    }

    public function setNom($nom_categorie) {
        $this->nom_categorie = $nom_categorie;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setIcone($icone) {
        $this->icone = $icone;
    }

    public function getAll() {
        $sql = "SELECT c.*, COUNT(s.id_service) as nb_services 
                FROM categorie c 
                LEFT JOIN services s ON c.id_categorie = s.id_categorie AND s.statut = 'actif'
                GROUP BY c.id_categorie 
                ORDER BY c.nom_categorie";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categorie WHERE id_categorie=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO categorie (nom_categorie, description, icone) VALUES (?,?,?)");
        $stmt->bind_param("sss", $data['nom_categorie'], $data['description'], $data['icone']);
        $stmt->execute();
        return $this->db->insert_id;
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE categorie SET nom_categorie=?, description=?, icone=? WHERE id_categorie=?");
        $stmt->bind_param("sssi", $data['nom_categorie'], $data['description'], $data['icone'], $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM categorie WHERE id_categorie=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
