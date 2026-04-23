<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../models/CategorieProduit.php');

// Contrôleur pour gérer les opérations CRUD sur les catégories de produits
class CategorieProduitController {

    // Récupérer toutes les catégories avec le nombre de produits
    public function listCategories() {
        $sql = "SELECT c.*, COUNT(p.id_produit) as nb_produits
                FROM categorie_produit c
                LEFT JOIN produit p ON c.id_categorie = p.id_categorie
                GROUP BY c.id_categorie
                ORDER BY c.nom_categorie ASC";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);

            $categories = [];
            foreach ($rows as $row) {
                $cat = new CategorieProduit(
                    $row['nom_categorie'],
                    $row['description'],
                    $row['icone']
                );
                $cat->setId($row['id_categorie']);
                $cat->setCreatedAt($row['created_at']);
                $cat->setNbProduits((int)$row['nb_produits']);
                $categories[] = $cat;
            }
            return $categories;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return [];
        }
    }

    // Récupérer une catégorie par son ID
    public function getCategorieById($id) {
        $sql = "SELECT * FROM categorie_produit WHERE id_categorie = :id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            $row = $query->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $cat = new CategorieProduit(
                    $row['nom_categorie'],
                    $row['description'],
                    $row['icone']
                );
                $cat->setId($row['id_categorie']);
                $cat->setCreatedAt($row['created_at']);
                return $cat;
            }
            return null;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return null;
        }
    }

    // Ajouter une nouvelle catégorie
    public function addCategorie(CategorieProduit $categorie) {
        $sql = "INSERT INTO categorie_produit (nom_categorie, description, icone)
                VALUES (:nom, :description, :icone)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':nom' => $categorie->getNomCategorie(),
                ':description' => $categorie->getDescription(),
                ':icone' => $categorie->getIcone()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return false;
        }
    }

    // Modifier une catégorie existante
    public function updateCategorie(CategorieProduit $categorie) {
        $sql = "UPDATE categorie_produit SET nom_categorie=:nom, description=:description, icone=:icone
                WHERE id_categorie=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':nom' => $categorie->getNomCategorie(),
                ':description' => $categorie->getDescription(),
                ':icone' => $categorie->getIcone(),
                ':id' => $categorie->getId()
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return false;
        }
    }

    // Supprimer une catégorie par son ID
    public function deleteCategorie($id) {
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
