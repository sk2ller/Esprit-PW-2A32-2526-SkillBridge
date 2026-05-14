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
    // =========================================================
    // ANALYTICS & STATS (VENDEUR)
    // =========================================================

    public function getVendorRevenueStats($vendeurId, $days = 7) {
        $db = Config::getConnexion();
        $stats = [];
        // Prepare default dates with 0 revenue
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $stats[$date] = ['date' => date('d/m', strtotime($date)), 'revenue' => 0];
        }

        try {
            // Only confirmed, shipped, or delivered orders count towards revenue
            $sql = "SELECT DATE(c.created_at) as order_date, SUM(c.prix_total) as daily_revenue 
                    FROM commande c
                    JOIN produit p ON c.id_produit = p.id_produit
                    WHERE p.id_vendeur = :vendeur_id
                    AND c.statut IN ('confirmee', 'expediee', 'livree')
                    AND c.created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                    GROUP BY DATE(c.created_at)";
            $query = $db->prepare($sql);
            $query->bindValue(':vendeur_id', $vendeurId, PDO::PARAM_INT);
            $query->bindValue(':days', $days - 1, PDO::PARAM_INT);
            $query->execute();
            
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $date = $row['order_date'];
                if (isset($stats[$date])) {
                    $stats[$date]['revenue'] = $row['daily_revenue'];
                }
            }
        } catch (Exception $e) { }
        return array_values($stats);
    }

    public function getTopProducts($vendeurId, $limit = 5) {
        $db = Config::getConnexion();
        try {
            $sql = "SELECT p.nom, SUM(c.quantite) as total_vendus
                    FROM commande c
                    JOIN produit p ON c.id_produit = p.id_produit
                    WHERE p.id_vendeur = :vendeur_id
                    AND c.statut IN ('confirmee', 'expediee', 'livree')
                    GROUP BY p.id_produit, p.nom
                    ORDER BY total_vendus DESC
                    LIMIT :limit";
            $query = $db->prepare($sql);
            $query->bindValue(':vendeur_id', $vendeurId, PDO::PARAM_INT);
            $query->bindValue(':limit', $limit, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // =========================================================
    // ANALYTICS & STATS (ADMIN — PLATFORM-WIDE)
    // =========================================================

    /**
     * Revenue across the ENTIRE platform over the last N days
     */
    public function getPlatformRevenueStats($days = 7) {
        $db = Config::getConnexion();
        $stats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $stats[$date] = ['date' => date('d/m', strtotime($date)), 'revenue' => 0];
        }
        try {
            $sql = "SELECT DATE(c.created_at) as order_date, SUM(c.prix_total) as daily_revenue 
                    FROM commande c
                    WHERE c.statut IN ('confirmee', 'expediee', 'livree')
                    AND c.created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                    GROUP BY DATE(c.created_at)";
            $query = $db->prepare($sql);
            $query->bindValue(':days', $days, PDO::PARAM_INT);
            $query->execute();
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $d = $row['order_date'];
                if (isset($stats[$d])) {
                    $stats[$d]['revenue'] = (float)$row['daily_revenue'];
                }
            }
        } catch (Exception $e) { }
        return array_values($stats);
    }

    /**
     * Top-selling products across the ENTIRE platform
     */
    public function getPlatformTopProducts($limit = 5) {
        $db = Config::getConnexion();
        try {
            $sql = "SELECT p.nom, SUM(c.quantite) as total_vendus
                    FROM commande c
                    JOIN produit p ON c.id_produit = p.id_produit
                    WHERE c.statut IN ('confirmee', 'expediee', 'livree')
                    GROUP BY p.id_produit, p.nom
                    ORDER BY total_vendus DESC
                    LIMIT :lim";
            $query = $db->prepare($sql);
            $query->bindValue(':lim', $limit, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Total platform revenue (all confirmed/shipped/delivered orders)
     */
    public function getTotalRevenue() {
        $db = Config::getConnexion();
        try {
            $sql = "SELECT COALESCE(SUM(prix_total), 0) as total FROM commande WHERE statut IN ('confirmee', 'expediee', 'livree')";
            $query = $db->prepare($sql);
            $query->execute();
            return (float)$query->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Orders grouped by day for the last N days
     */
    public function getOrdersPerDay($days = 7) {
        $db = Config::getConnexion();
        $stats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $stats[$date] = ['date' => date('d/m', strtotime($date)), 'count' => 0];
        }
        try {
            $sql = "SELECT DATE(created_at) as order_date, COUNT(*) as order_count 
                    FROM commande
                    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                    GROUP BY DATE(created_at)";
            $query = $db->prepare($sql);
            $query->bindValue(':days', $days, PDO::PARAM_INT);
            $query->execute();
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $d = $row['order_date'];
                if (isset($stats[$d])) {
                    $stats[$d]['count'] = (int)$row['order_count'];
                }
            }
        } catch (Exception $e) { }
        return array_values($stats);
    }
}
?>
