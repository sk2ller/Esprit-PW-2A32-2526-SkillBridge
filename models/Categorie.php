<?php
require_once __DIR__ . '/../config.php';

class Categorie {
    private $db;

    public function __construct() {
        $this->db = getDB();
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
