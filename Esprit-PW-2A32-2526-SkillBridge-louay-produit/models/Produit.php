<?php
require_once __DIR__ . '/../config.php';

class Produit {
    private $id_produit;
    private $nom;
    private $description;
    private $prix;
    private $quantite;
    private $statut;
    private $image;
    private $id_categorie;
    private $created_at;
    private $updated_at;

    // Constructeur
    public function __construct($id_produit = null, $nom = null, $description = null, $prix = null,
                                $quantite = null, $statut = null, $image = null, $id_categorie = null,
                                $created_at = null, $updated_at = null) {
        $this->id_produit = $id_produit;
        $this->nom = $nom;
        $this->description = $description;
        $this->prix = $prix;
        $this->quantite = $quantite;
        $this->statut = $statut;
        $this->image = $image;
        $this->id_categorie = $id_categorie;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    // Getters
    public function getId() { return $this->id_produit; }
    public function getNom() { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getPrix() { return $this->prix; }
    public function getQuantite() { return $this->quantite; }
    public function getStatut() { return $this->statut; }
    public function getImage() { return $this->image; }
    public function getIdCategorie() { return $this->id_categorie; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // Setters
    public function setId($id_produit) { $this->id_produit = $id_produit; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setDescription($description) { $this->description = $description; }
    public function setPrix($prix) { $this->prix = $prix; }
    public function setQuantite($quantite) { $this->quantite = $quantite; }
    public function setStatut($statut) { $this->statut = $statut; }
    public function setImage($image) { $this->image = $image; }
    public function setIdCategorie($id_categorie) { $this->id_categorie = $id_categorie; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }

    // Récupérer tous les produits avec filtres optionnels
    public function getAll($statut = null, $id_categorie = null, $search = null) {
        $sql = "SELECT p.*, c.nom_categorie
                FROM produit p
                JOIN categorie_produit c ON p.id_categorie = c.id_categorie
                WHERE 1=1";
        $params = [];

        if ($statut) {
            $sql .= " AND p.statut = :statut";
            $params[':statut'] = $statut;
        }
        if ($id_categorie) {
            $sql .= " AND p.id_categorie = :id_categorie";
            $params[':id_categorie'] = $id_categorie;
        }
        if ($search) {
            $sql .= " AND (p.nom LIKE :search OR p.description LIKE :search2)";
            $params[':search'] = "%$search%";
            $params[':search2'] = "%$search%";
        }
        $sql .= " ORDER BY p.created_at DESC";

        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return [];
        }
    }

    // Récupérer un produit par ID
    public function getById($id) {
        $sql = "SELECT p.*, c.nom_categorie
                FROM produit p
                JOIN categorie_produit c ON p.id_categorie = c.id_categorie
                WHERE p.id_produit = :id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return null;
        }
    }

    // Récupérer les produits par catégorie
    public function getByCategorie($id_categorie) {
        $sql = "SELECT p.*, c.nom_categorie
                FROM produit p
                JOIN categorie_produit c ON p.id_categorie = c.id_categorie
                WHERE p.id_categorie = :id_categorie AND p.statut = 'disponible'
                ORDER BY p.created_at DESC";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id_categorie' => $id_categorie]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return [];
        }
    }

    // Récupérer tous les produits (pour vendeur)
    public function getAllForVendeur() {
        $sql = "SELECT p.*, c.nom_categorie FROM produit p
                JOIN categorie_produit c ON p.id_categorie = c.id_categorie
                ORDER BY p.created_at DESC";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return [];
        }
    }

    // Créer un produit
    public function create($data) {
        $sql = "INSERT INTO produit (nom, description, prix, quantite, statut, id_categorie)
                VALUES (:nom, :description, :prix, :quantite, 'en_attente', :id_categorie)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':nom' => $data['nom'],
                ':description' => $data['description'],
                ':prix' => $data['prix'],
                ':quantite' => $data['quantite'],
                ':id_categorie' => $data['id_categorie']
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return false;
        }
    }

    // Modifier un produit
    public function update($id, $data) {
        $sql = "UPDATE produit SET nom=:nom, description=:description, prix=:prix,
                quantite=:quantite, id_categorie=:id_categorie WHERE id_produit=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':nom' => $data['nom'],
                ':description' => $data['description'],
                ':prix' => $data['prix'],
                ':quantite' => $data['quantite'],
                ':id_categorie' => $data['id_categorie'],
                ':id' => $id
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return false;
        }
    }

    // Supprimer un produit
    public function delete($id) {
        $sql = "DELETE FROM produit WHERE id_produit=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return true;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return false;
        }
    }

    // Mettre à jour le statut
    public function updateStatut($id, $statut) {
        $sql = "UPDATE produit SET statut=:statut WHERE id_produit=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':statut' => $statut, ':id' => $id]);
            return true;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return false;
        }
    }

    // Statistiques pour le dashboard admin
    public function getStats() {
        $stats = ['en_attente' => 0, 'disponible' => 0, 'rupture' => 0, 'total' => 0];
        $db = Config::getConnexion();
        try {
            $query = $db->prepare("SELECT statut, COUNT(*) as count FROM produit GROUP BY statut");
            $query->execute();
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                if (isset($stats[$row['statut']])) {
                    $stats[$row['statut']] = (int)$row['count'];
                }
            }
            $stats['total'] = $stats['en_attente'] + $stats['disponible'] + $stats['rupture'];
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
        return $stats;
    }
}
?>
