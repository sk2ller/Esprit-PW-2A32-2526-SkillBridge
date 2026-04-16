<?php
require_once __DIR__ . '/../config.php';

class Offre {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    // Get all offers with optional filters
    public function getAll($statut = null, $search = null) {
        $sql = "SELECT o.*, COALESCE(u.nom_client, 'Client Anonyme') as nom_client 
                FROM offres o 
                LEFT JOIN clients u ON o.id_client = u.id_client
                WHERE 1=1";
        $params = [];
        $types = "";

        if ($statut) {
            $sql .= " AND o.statut = ?";
            $params[] = $statut;
            $types .= "s";
        }
        if ($search) {
            $sql .= " AND (o.titre LIKE ? OR o.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= "ss";
        }
        $sql .= " ORDER BY o.created_at DESC";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            die("Erreur SQL: " . $this->db->error . "\nRequête: " . $sql);
        }
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get offer by ID
    public function getById($id) {
        $sql = "SELECT o.*, u.nom_client, u.email_client 
                FROM offres o 
                LEFT JOIN clients u ON o.id_client = u.id_client
                WHERE o.id_offre = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Get offers by client
    public function getByClient($id_client) {
        $sql = "SELECT o.* FROM offres o 
                WHERE o.id_client = ?
                ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_client);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Create new offer
    public function create($data) {
        $sql = "INSERT INTO offres (titre, description, budget, delai_publication, niveau_requis, competences_requises, statut, id_client) 
                VALUES (?, ?, ?, ?, ?, ?, 'en_attente', ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssdissi", 
            $data['titre'], 
            $data['description'], 
            $data['budget'], 
            $data['delai_publication'],
            $data['niveau_requis'],
            $data['competences_requises'],
            $data['id_client']
        );
        $stmt->execute();
        return $this->db->insert_id;
    }

    // Update offer
    public function update($id, $data) {
        $sql = "UPDATE offres SET titre=?, description=?, budget=?, delai_publication=?, niveau_requis=?, competences_requises=? WHERE id_offre=?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssdisii", 
            $data['titre'], 
            $data['description'], 
            $data['budget'], 
            $data['delai_publication'],
            $data['niveau_requis'],
            $data['competences_requises'],
            $id
        );
        return $stmt->execute();
    }

    // Delete offer
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM offres WHERE id_offre = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Update offer status (admin)
    public function updateStatut($id, $statut) {
        $stmt = $this->db->prepare("UPDATE offres SET statut = ?, updated_at = CURRENT_TIMESTAMP WHERE id_offre = ?");
        $stmt->bind_param("si", $statut, $id);
        return $stmt->execute();
    }

    // Get statistics
    public function getStats() {
        $total = $this->db->query("SELECT COUNT(*) as count FROM offres")->fetch_assoc()['count'];
        $actif = $this->db->query("SELECT COUNT(*) as count FROM offres WHERE statut = 'actif'")->fetch_assoc()['count'];
        $en_attente = $this->db->query("SELECT COUNT(*) as count FROM offres WHERE statut = 'en_attente'")->fetch_assoc()['count'];
        $suspendu = $this->db->query("SELECT COUNT(*) as count FROM offres WHERE statut = 'suspendu'")->fetch_assoc()['count'];
        
        return [
            'total' => $total,
            'actif' => $actif,
            'en_attente' => $en_attente,
            'suspendu' => $suspendu
        ];
    }

    // Get client stats
    public function getClientStats($id_client) {
        $result = $this->db->query("
            SELECT 
                COUNT(*) as total_offres,
                SUM(CASE WHEN statut = 'actif' THEN 1 ELSE 0 END) as offres_actives,
                SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                SUM(CASE WHEN statut = 'suspendu' THEN 1 ELSE 0 END) as suspendues
            FROM offres 
            WHERE id_client = $id_client
        ")->fetch_assoc();
        return $result;
    }
}
?>
