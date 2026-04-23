<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../models/Commande.php');

// Contrôleur pour gérer les opérations CRUD sur les commandes
class CommandeController {

    // Récupérer toutes les commandes avec le nom du produit
    public function listCommandes($statut = null) {
        $sql = "SELECT c.*, p.nom as nom_produit
                FROM commande c
                JOIN produit p ON c.id_produit = p.id_produit
                WHERE 1=1";
        $params = [];

        if ($statut) {
            $sql .= " AND c.statut = :statut";
            $params[':statut'] = $statut;
        }
        $sql .= " ORDER BY c.created_at DESC";

        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);

            $commandes = [];
            foreach ($rows as $row) {
                $cmd = new Commande(
                    $row['nom_client'],
                    $row['email_client'],
                    $row['telephone'],
                    $row['adresse'],
                    $row['id_produit'],
                    $row['quantite'],
                    $row['prix_total'],
                    $row['note'],
                    $row['rating'],
                    $row['review']
                );
                $cmd->setId($row['id_commande']);
                $cmd->setStatut($row['statut']);
                $cmd->setCreatedAt($row['created_at']);
                $cmd->setUpdatedAt($row['updated_at']);
                $cmd->setNomProduit($row['nom_produit']);
                $commandes[] = $cmd;
            }
            return $commandes;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return [];
        }
    }

    // Récupérer une commande par son ID
    public function getCommandeById($id) {
        $sql = "SELECT c.*, p.nom as nom_produit
                FROM commande c
                JOIN produit p ON c.id_produit = p.id_produit
                WHERE c.id_commande = :id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            $row = $query->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $cmd = new Commande(
                    $row['nom_client'],
                    $row['email_client'],
                    $row['telephone'],
                    $row['adresse'],
                    $row['id_produit'],
                    $row['quantite'],
                    $row['prix_total'],
                    $row['note'],
                    $row['rating'],
                    $row['review']
                );
                $cmd->setId($row['id_commande']);
                $cmd->setStatut($row['statut']);
                $cmd->setCreatedAt($row['created_at']);
                $cmd->setUpdatedAt($row['updated_at']);
                $cmd->setNomProduit($row['nom_produit']);
                return $cmd;
            }
            return null;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return null;
        }
    }

    // Ajouter une nouvelle commande
    public function addCommande(Commande $commande) {
        $sql = "INSERT INTO commande (nom_client, email_client, telephone, adresse, id_produit, quantite, prix_total, statut, note)
                VALUES (:nom_client, :email_client, :telephone, :adresse, :id_produit, :quantite, :prix_total, 'en_attente', :note)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':nom_client' => $commande->getNomClient(),
                ':email_client' => $commande->getEmailClient(),
                ':telephone' => $commande->getTelephone(),
                ':adresse' => $commande->getAdresse(),
                ':id_produit' => $commande->getIdProduit(),
                ':quantite' => $commande->getQuantite(),
                ':prix_total' => $commande->getPrixTotal(),
                ':note' => $commande->getNote()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return false;
        }
    }

    // Modifier une commande existante
    public function updateCommande(Commande $commande) {
        $sql = "UPDATE commande SET nom_client=:nom_client, email_client=:email_client,
                telephone=:telephone, adresse=:adresse, id_produit=:id_produit,
                quantite=:quantite, prix_total=:prix_total, note=:note
                WHERE id_commande=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':nom_client' => $commande->getNomClient(),
                ':email_client' => $commande->getEmailClient(),
                ':telephone' => $commande->getTelephone(),
                ':adresse' => $commande->getAdresse(),
                ':id_produit' => $commande->getIdProduit(),
                ':quantite' => $commande->getQuantite(),
                ':prix_total' => $commande->getPrixTotal(),
                ':note' => $commande->getNote(),
                ':id' => $commande->getId()
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return false;
        }
    }

    // Supprimer une commande par son ID
    public function deleteCommande($id) {
        $sql = "DELETE FROM commande WHERE id_commande=:id";
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

    // Mettre à jour le statut d'une commande
    public function updateStatut($id, $statut) {
        $sql = "UPDATE commande SET statut=:statut WHERE id_commande=:id";
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

    // Ajouter une note et un avis
    public function submitReview($id, $rating, $review) {
        $sql = "UPDATE commande SET rating=:rating, review=:review WHERE id_commande=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':rating' => $rating, ':review' => $review, ':id' => $id]);
            return true;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
            return false;
        }
    }

    // Statistiques pour le dashboard admin
    public function getStats() {
        $stats = ['en_attente' => 0, 'confirmee' => 0, 'expediee' => 0, 'livree' => 0, 'annulee' => 0, 'total' => 0];
        $db = Config::getConnexion();
        try {
            $query = $db->prepare("SELECT statut, COUNT(*) as count FROM commande GROUP BY statut");
            $query->execute();
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                if (isset($stats[$row['statut']])) {
                    $stats[$row['statut']] = (int)$row['count'];
                }
            }
            $stats['total'] = array_sum(array_diff_key($stats, ['total' => 0]));
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
        return $stats;
    }
}
?>
