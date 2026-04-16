<?php
require_once __DIR__ . '/../config.php';

class CategorieProduit {
    private $id_categorie;
    private $nom_categorie;
    private $description;
    private $icone;
    private $created_at;

    // Constructeur
    public function __construct($id_categorie = null, $nom_categorie = null, $description = null,
                                $icone = null, $created_at = null) {
        $this->id_categorie = $id_categorie;
        $this->nom_categorie = $nom_categorie;
        $this->description = $description;
        $this->icone = $icone;
        $this->created_at = $created_at;
    }

    // Getters
    public function getId() { return $this->id_categorie; }
    public function getNomCategorie() { return $this->nom_categorie; }
    public function getDescription() { return $this->description; }
    public function getIcone() { return $this->icone; }
    public function getCreatedAt() { return $this->created_at; }

    // Setters
    public function setId($id_categorie) { $this->id_categorie = $id_categorie; }
    public function setNomCategorie($nom_categorie) { $this->nom_categorie = $nom_categorie; }
    public function setDescription($description) { $this->description = $description; }
    public function setIcone($icone) { $this->icone = $icone; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }

    // Récupérer toutes les catégories avec le nombre de produits
    public function getAll() {
        $sql = "SELECT c.*, COUNT(p.id_produit) as nb_produits
                FROM categorie_produit c
                LEFT JOIN produit p ON c.id_categorie = p.id_categorie
                GROUP BY c.id_categorie
                ORDER BY c.nom_categorie ASC";
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

    // Récupérer une catégorie par ID
    public function getById($id) {
        $sql = "SELECT * FROM categorie_produit WHERE id_categorie = :id";
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

    // Créer une catégorie
    public function create($data) {
        $sql = "INSERT INTO categorie_produit (nom_categorie, description, icone)
                VALUES (:nom, :description, :icone)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':nom' => $data['nom_categorie'],
                ':description' => $data['description'],
                ':icone' => $data['icone']
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return false;
        }
    }

    // Modifier une catégorie
    public function update($id, $data) {
        $sql = "UPDATE categorie_produit SET nom_categorie=:nom, description=:description, icone=:icone
                WHERE id_categorie=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':nom' => $data['nom_categorie'],
                ':description' => $data['description'],
                ':icone' => $data['icone'],
                ':id' => $id
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return false;
        }
    }

    // Supprimer une catégorie
    public function delete($id) {
        $sql = "DELETE FROM categorie_produit WHERE id_categorie=:id";
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
}
?>
