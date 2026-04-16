<?php
require_once __DIR__ . '/../config.php';

class Service {
    // Attributs privés
    private $id_service;
    private $titre;
    private $description;
    private $prix;
    private $delai_livraison;
    private $statut;
    private $id_categorie;
    private $created_at;
    private $db;

    // Constructeur
    public function __construct($id_service = null, $titre = null, $description = null, $prix = null, 
                                $delai_livraison = null, $statut = null, $id_categorie = null, $created_at = null) {
        $this->id_service = $id_service;
        $this->titre = $titre;
        $this->description = $description;
        $this->prix = $prix;
        $this->delai_livraison = $delai_livraison;
        $this->statut = $statut;
        $this->id_categorie = $id_categorie;
        $this->created_at = $created_at;
        $this->db = getDB();
    }

    // Getters
    public function getId() {
        return $this->id_service;
    }

    public function getTitre() {
        return $this->titre;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getPrix() {
        return $this->prix;
    }

    public function getDelaiLivraison() {
        return $this->delai_livraison;
    }

    public function getStatut() {
        return $this->statut;
    }

    public function getIdCategorie() {
        return $this->id_categorie;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    // Setters
    public function setId($id_service) {
        $this->id_service = $id_service;
    }

    public function setTitre($titre) {
        $this->titre = $titre;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setPrix($prix) {
        $this->prix = $prix;
    }

    public function setDelaiLivraison($delai_livraison) {
        $this->delai_livraison = $delai_livraison;
    }

    public function setStatut($statut) {
        $this->statut = $statut;
    }

    public function setIdCategorie($id_categorie) {
        $this->id_categorie = $id_categorie;
    }

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }

    public function getAll($statut = null, $id_categorie = null, $search = null) {
        $sql = "SELECT s.*, c.nom_categorie
                FROM services s 
                JOIN categorie c ON s.id_categorie = c.id_categorie 
                WHERE 1=1";
        $params = [];
        $types = "";

        if ($statut) {
            $sql .= " AND s.statut = ?";
            $params[] = $statut;
            $types .= "s";
        }
        if ($id_categorie) {
            $sql .= " AND s.id_categorie = ?";
            $params[] = $id_categorie;
            $types .= "i";
        }
        if ($search) {
            $sql .= " AND (s.titre LIKE ? OR s.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= "ss";
        }
        $sql .= " ORDER BY s.created_at DESC";

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT s.*, c.nom_categorie
                FROM services s 
                JOIN categorie c ON s.id_categorie = c.id_categorie 
                WHERE s.id_service = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getByFreelancer($id_freelance = null) {
        // Get all services for display (freelancer ID not needed anymore)
        $sql = "SELECT s.*, c.nom_categorie FROM services s 
                JOIN categorie c ON s.id_categorie = c.id_categorie 
                ORDER BY s.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getByCategory($id_categorie) {
        $sql = "SELECT s.*, c.nom_categorie 
                FROM services s 
                JOIN categorie c ON s.id_categorie = c.id_categorie 
                WHERE s.id_categorie = ? AND s.statut = 'actif'
                ORDER BY s.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_categorie);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO services (titre, description, prix, delai_livraison, statut, id_categorie) 
                VALUES (?, ?, ?, ?, 'en_attente', ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssdii", 
            $data['titre'], $data['description'], 
            $data['prix'], $data['delai_livraison'],
            $data['id_categorie']
        );
        $stmt->execute();
        return $this->db->insert_id;
    }

    public function update($id, $data) {
        $sql = "UPDATE services SET titre=?, description=?, prix=?, delai_livraison=?, id_categorie=? WHERE id_service=?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssdiii", 
            $data['titre'], $data['description'], 
            $data['prix'], $data['delai_livraison'],
            $data['id_categorie'], $id
        );
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM services WHERE id_service=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function updateStatut($id, $statut) {
        $stmt = $this->db->prepare("UPDATE services SET statut=? WHERE id_service=?");
        $stmt->bind_param("si", $statut, $id);
        return $stmt->execute();
    }

    public function getStats() {
        $stats = ['en_attente' => 0, 'confirmee' => 0, 'rejetee' => 0, 'total' => 0];
        $result = $this->db->query("SELECT statut, COUNT(*) as count FROM services GROUP BY statut");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if ($row && isset($row['statut']) && isset($row['count'])) {
                    $stats[$row['statut']] = $row['count'];
                }
            }
        }
        $stats['total'] = array_sum(array_filter($stats, function($k) { return $k !== 'total'; }, ARRAY_FILTER_USE_KEY));
        return $stats;
    }
}
?>
