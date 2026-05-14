<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../models/Produit.php');
require_once(__DIR__ . '/../models/CategorieProduit.php');

// Contrôleur pour gérer les opérations CRUD sur les produits
class ProduitController {

    // Récupérer tous les produits avec filtres optionnels
    public function listProduits($statut = null, $id_categorie = null, $search = null) {
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
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);

            $produits = [];
            foreach ($rows as $row) {
                $produit = new Produit();
                $produit->setId($row['id_produit']);
                $produit->setNom($row['nom']);
                $produit->setDescription($row['description']);
                $produit->setPrix($row['prix']);
                $produit->setQuantite($row['quantite']);
                $produit->setStatut($row['statut']);
                $produit->setImage($row['image']);
                $produit->setIdCategorie($row['id_categorie']);
                $produit->setIdVendeur($row['id_vendeur'] ?? null);
                $produit->setCreatedAt($row['created_at']);
                $produit->setUpdatedAt($row['updated_at']);
                $produit->setNomCategorie($row['nom_categorie']);
                $produits[] = $produit;
            }
            return $produits;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return [];
        }
    }

    // Récupérer un produit par son ID (avec catégorie)
    public function getProduitById($id) {
        $sql = "SELECT p.*, c.nom_categorie
                FROM produit p
                JOIN categorie_produit c ON p.id_categorie = c.id_categorie
                WHERE p.id_produit = :id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            $row = $query->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $produit = new Produit();
                $produit->setId($row['id_produit']);
                $produit->setNom($row['nom']);
                $produit->setDescription($row['description']);
                $produit->setPrix($row['prix']);
                $produit->setQuantite($row['quantite']);
                $produit->setStatut($row['statut']);
                $produit->setImage($row['image']);
                $produit->setIdCategorie($row['id_categorie']);
                $produit->setIdVendeur($row['id_vendeur'] ?? null);
                $produit->setCreatedAt($row['created_at']);
                $produit->setUpdatedAt($row['updated_at']);
                $produit->setNomCategorie($row['nom_categorie']);
                return $produit;
            }
            return null;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return null;
        }
    }

    // Récupérer tous les produits (pour vendeur)
    public function listProduitsVendeur($vendeurId = null) {
        $sql = "SELECT p.*, c.nom_categorie FROM produit p
                JOIN categorie_produit c ON p.id_categorie = c.id_categorie";
        $params = [];
        if ($vendeurId) {
            $sql .= " WHERE p.id_vendeur = :vendeur_id";
            $params[':vendeur_id'] = $vendeurId;
        }
        $sql .= " ORDER BY p.created_at DESC";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);

            $produits = [];
            foreach ($rows as $row) {
                $produit = new Produit();
                $produit->setId($row['id_produit']);
                $produit->setNom($row['nom']);
                $produit->setDescription($row['description']);
                $produit->setPrix($row['prix']);
                $produit->setQuantite($row['quantite']);
                $produit->setStatut($row['statut']);
                $produit->setImage($row['image']);
                $produit->setIdCategorie($row['id_categorie']);
                $produit->setIdVendeur($row['id_vendeur'] ?? null);
                $produit->setCreatedAt($row['created_at']);
                $produit->setUpdatedAt($row['updated_at']);
                $produit->setNomCategorie($row['nom_categorie']);
                $produits[] = $produit;
            }
            return $produits;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return [];
        }
    }

    // Ajouter un nouveau produit
    public function addProduit(Produit $produit) {
        $sql = "INSERT INTO produit (nom, description, prix, quantite, statut, id_categorie, id_vendeur)
                VALUES (:nom, :description, :prix, :quantite, 'en_attente', :id_categorie, :id_vendeur)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':nom' => $produit->getNom(),
                ':description' => $produit->getDescription(),
                ':prix' => $produit->getPrix(),
                ':quantite' => $produit->getQuantite(),
                ':id_categorie' => $produit->getIdCategorie(),
                ':id_vendeur' => $produit->getIdVendeur()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return false;
        }
    }

    // Modifier un produit existant
    public function updateProduit(Produit $produit) {
        $sql = "UPDATE produit SET nom=:nom, description=:description, prix=:prix,
                quantite=:quantite, id_categorie=:id_categorie, image=:image WHERE id_produit=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':nom' => $produit->getNom(),
                ':description' => $produit->getDescription(),
                ':prix' => $produit->getPrix(),
                ':quantite' => $produit->getQuantite(),
                ':id_categorie' => $produit->getIdCategorie(),
                ':image' => $produit->getImage(),
                ':id' => $produit->getId()
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return false;
        }
    }

    // Supprimer un produit par son ID
    public function deleteProduit($id) {
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

    // Mettre à jour le statut d'un produit
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

    // Gérer l'upload d'image pour un produit
    public function handleImageUpload($file) {
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExts)) {
                $uploadDir = __DIR__ . '/../uploads/produits/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = uniqid() . '.' . $ext;
                $destPath = $uploadDir . $fileName;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    return 'uploads/produits/' . $fileName;
                }
            }
        }
        return null;
    }
}
?>
