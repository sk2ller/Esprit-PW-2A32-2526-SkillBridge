<?php
require_once __DIR__ . '/../config.php';

class Offre {
    // Attributs privés de l'entité Offre
    private $id_offre;
    private $titre;
    private $description;
    private $budget;
    private $delai_publication;
    private $niveau_requis;
    private $competences_requises;
    private $statut;
    private $id_client;
    private $created_at;
    private $updated_at;
    
    // Connexion à la base de données
    private $db;

    /**
     * Constructeur du modèle Offre
     * @param int $id_offre
     * @param string $titre
     * @param string $description
     * @param float $budget
     * @param int $delai_publication
     * @param string $niveau_requis
     * @param string $competences_requises
     * @param string $statut
     * @param int $id_client
     * @param string $created_at
     * @param string $updated_at
     */
    public function __construct($id_offre = null, $titre = null, $description = null, $budget = null, 
                                 $delai_publication = null, $niveau_requis = null, 
                                 $competences_requises = null, $statut = 'en_attente', 
                                 $id_client = null, $created_at = null, $updated_at = null) {
        $this->db = getDB();
        $this->id_offre = $id_offre;
        $this->titre = $titre;
        $this->description = $description;
        $this->budget = $budget;
        $this->delai_publication = $delai_publication ?? 30;
        $this->niveau_requis = $niveau_requis ?? 'intermediaire';
        $this->competences_requises = $competences_requises;
        $this->statut = $statut;
        $this->id_client = $id_client;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    // ========== GETTERS ==========
    
    public function getIdOffre() {
        return $this->id_offre;
    }

    public function getTitre() {
        return $this->titre;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getBudget() {
        return $this->budget;
    }

    public function getDelaiPublication() {
        return $this->delai_publication;
    }

    public function getNiveauRequis() {
        return $this->niveau_requis;
    }

    public function getCompetencesRequises() {
        return $this->competences_requises;
    }

    public function getStatut() {
        return $this->statut;
    }

    public function getIdClient() {
        return $this->id_client;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    // ========== SETTERS ==========
    
    public function setIdOffre($id_offre) {
        $this->id_offre = $id_offre;
        return $this;
    }

    public function setTitre($titre) {
        $this->titre = $titre;
        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function setBudget($budget) {
        $this->budget = (float)$budget;
        return $this;
    }

    public function setDelaiPublication($delai_publication) {
        $this->delai_publication = (int)$delai_publication;
        return $this;
    }

    public function setNiveauRequis($niveau_requis) {
        $this->niveau_requis = $niveau_requis;
        return $this;
    }

    public function setCompetencesRequises($competences_requises) {
        $this->competences_requises = $competences_requises;
        return $this;
    }

    public function setStatut($statut) {
        $this->statut = $statut;
        return $this;
    }

    public function setIdClient($id_client) {
        $this->id_client = $id_client;
        return $this;
    }

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
        return $this;
    }

    public function setUpdatedAt($updated_at) {
        $this->updated_at = $updated_at;
        return $this;
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
