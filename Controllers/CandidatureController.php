<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/Candidature.php';
require_once __DIR__ . '/../Models/Tache.php';

class CandidatureController
{
    // ── POSTULER ──────────────────────────────────────────────────────
    public function postuler($id_projet, $id_freelancer)
    {
        $db = Config::getConnexion();
        try {
            // Vérifier si déjà postulé
            $q = $db->prepare("SELECT id FROM candidature WHERE id_projet=:p AND id_freelancer=:f");
            $q->execute(['p' => $id_projet, 'f' => $id_freelancer]);
            if ($q->fetch()) return ['success' => false, 'message' => 'Vous avez déjà postulé à ce projet.'];

            $q = $db->prepare("INSERT INTO candidature (id_projet, id_freelancer) VALUES (:p, :f)");
            $q->execute(['p' => $id_projet, 'f' => $id_freelancer]);
            return ['success' => true, 'message' => 'Candidature envoyée avec succès !'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    // ── CANDIDATURES D'UN FREELANCER ──────────────────────────────────
    public function getMesCandidatures($id_freelancer)
    {
        $db = Config::getConnexion();
        try {
            $q = $db->prepare("SELECT c.*, p.titre AS titre_projet
                FROM candidature c
                JOIN projet p ON p.id = c.id_projet
                WHERE c.id_freelancer = :f
                ORDER BY c.id DESC");
            $q->execute(['f' => $id_freelancer]);
            $rows = $q->fetchAll();
            $list = [];
            foreach ($rows as $row) {
                $c = new Candidature($row['id_projet'], $row['id_freelancer'], $row['statut']);
                $c->setId($row['id']);
                $c->setCreatedAt($row['created_at']);
                $c->setTitreProjet($row['titre_projet']);
                $list[] = $c;
            }
            return $list;
        } catch (Exception $e) { return []; }
    }

    // ── TOUTES LES CANDIDATURES (backoffice) ──────────────────────────
    public function getAllCandidatures($statut = '')
    {
        $db = Config::getConnexion();
        try {
            $where = $statut ? "WHERE c.statut = :s" : "";
            $q = $db->prepare("SELECT c.*, p.titre AS titre_projet,
                u.nom AS nom_freelancer, u.prenom AS prenom_freelancer
                FROM candidature c
                JOIN projet p ON p.id = c.id_projet
                JOIN user u ON u.id = c.id_freelancer
                $where
                ORDER BY c.id DESC");
            $params = $statut ? ['s' => $statut] : [];
            $q->execute($params);
            $rows = $q->fetchAll();
            $list = [];
            foreach ($rows as $row) {
                $c = new Candidature($row['id_projet'], $row['id_freelancer'], $row['statut']);
                $c->setId($row['id']);
                $c->setCreatedAt($row['created_at']);
                $c->setTitreProjet($row['titre_projet']);
                $c->setNomFreelancer($row['nom_freelancer']);
                $c->setPrenomFreelancer($row['prenom_freelancer']);
                $list[] = $c;
            }
            return $list;
        } catch (Exception $e) { return []; }
    }

    // ── CHANGER STATUT CANDIDATURE ────────────────────────────────────
    public function changerStatut($id, $statut)
    {
        $db = Config::getConnexion();
        try {
            $q = $db->prepare("UPDATE candidature SET statut=:s WHERE id=:id");
            $q->execute(['s' => $statut, 'id' => $id]);

            // Si candidature acceptée → projet passe en_cours
            if ($statut === 'accepte') {
                $q = $db->prepare("SELECT id_projet FROM candidature WHERE id=:id");
                $q->execute(['id' => $id]);
                $row = $q->fetch();
                if ($row) {
                    $q = $db->prepare("UPDATE projet SET statut='en_cours' WHERE id=:p AND statut='en_attente'");
                    $q->execute(['p' => $row['id_projet']]);
                }
            }

            return true;
        } catch (Exception $e) { return false; }
    }

    public function getProjetsAcceptes($id_freelancer)
    {
        $db = Config::getConnexion();
        try {
            $q = $db->prepare("SELECT p.*, u.nom AS nom_client, u.prenom AS prenom_client
                FROM projet p
                JOIN candidature c ON c.id_projet = p.id
                LEFT JOIN user u ON u.id = p.id_client
                WHERE c.id_freelancer = :f AND c.statut = 'accepte'
                ORDER BY p.id DESC");
            $q->execute(['f' => $id_freelancer]);
            return $q->fetchAll();
        } catch (Exception $e) { return []; }
    }

    // ── AJOUTER TACHE ─────────────────────────────────────────────────
    public function ajouterTache(Tache $tache)
    {
        $db = Config::getConnexion();
        try {
            // Vérifier que le freelancer a bien une candidature acceptée
            $q = $db->prepare("SELECT id FROM candidature WHERE id_projet=:p AND id_freelancer=:f AND statut='accepte'");
            $q->execute(['p' => $tache->getIdProjet(), 'f' => $tache->getIdFreelancer()]);
            if (!$q->fetch()) return ['success' => false, 'message' => 'Accès refusé.'];

            // Vérifier que la somme des prix ne dépasse pas le budget du projet
            $q = $db->prepare("SELECT p.budget, COALESCE(SUM(t.prix),0) AS total_taches
                FROM projet p
                LEFT JOIN tache t ON t.id_projet = p.id
                WHERE p.id = :p
                GROUP BY p.budget");
            $q->execute(['p' => $tache->getIdProjet()]);
            $row = $q->fetch();
            if ($row) {
                $budget       = (float)$row['budget'];
                $totalTaches  = (float)$row['total_taches'];
                $prixNouvelle = (float)$tache->getPrix();
                if (($totalTaches + $prixNouvelle) > $budget) {
                    $restant = $budget - $totalTaches;
                    return ['success' => false, 'message' => sprintf(
                        'Budget dépassé. Il reste %.2f TND disponible sur un budget de %.2f TND.',
                        $restant, $budget
                    )];
                }
            }

            $q = $db->prepare("INSERT INTO tache (id_projet, id_freelancer, titre, description, statut, prix)
                VALUES (:p, :f, :t, :d, :s, :prix)");
            $q->execute([
                'p'    => $tache->getIdProjet(),
                'f'    => $tache->getIdFreelancer(),
                't'    => $tache->getTitre(),
                'd'    => $tache->getDescription(),
                's'    => $tache->getStatut(),
                'prix' => $tache->getPrix(),
            ]);
            return ['success' => true, 'message' => 'Tâche ajoutée avec succès.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    // ── METTRE A JOUR AVANCEMENT ──────────────────────────────────────
    public function updateAvancement($id_projet, $id_freelancer, $avancement)
    {
        $db = Config::getConnexion();
        try {
            // Vérifier que le freelancer a bien une candidature acceptée
            $q = $db->prepare("SELECT id FROM candidature WHERE id_projet=:p AND id_freelancer=:f AND statut='accepte'");
            $q->execute(['p' => $id_projet, 'f' => $id_freelancer]);
            if (!$q->fetch()) return ['success' => false, 'message' => 'Accès refusé.'];

            $avancement = max(0, min(100, (int)$avancement));
            $q = $db->prepare("UPDATE projet SET avancement=:a WHERE id=:id");
            $q->execute(['a' => $avancement, 'id' => $id_projet]);

            // Vérifier si le projet doit passer à terminé
            $termine = $this->verifierEtTerminerProjet($id_projet);

            return ['success' => true, 'avancement' => $avancement, 'projet_termine' => $termine];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    public function getTaches($id_projet, $id_freelancer)
    {
        $db = Config::getConnexion();
        try {
            $q = $db->prepare("SELECT * FROM tache WHERE id_projet=:p AND id_freelancer=:f ORDER BY id DESC");
            $q->execute(['p' => $id_projet, 'f' => $id_freelancer]);
            $rows = $q->fetchAll();
            $list = [];
            foreach ($rows as $row) {
                $t = new Tache($row['id_projet'], $row['id_freelancer'], $row['titre'], $row['description'], $row['statut'], $row['prix'] ?? 0);
                $t->setId($row['id']);
                $t->setCreatedAt($row['created_at']);
                $list[] = $t;
            }
            return $list;
        } catch (Exception $e) { return []; }
    }

    // ── AJOUTER TACHE (ADMIN) ─────────────────────────────────────────
    public function ajouterTacheAdmin(Tache $tache)
    {
        $db = Config::getConnexion();
        try {
            // Vérifier budget
            $q = $db->prepare("SELECT p.budget, COALESCE(SUM(t.prix),0) AS total_taches
                FROM projet p LEFT JOIN tache t ON t.id_projet = p.id
                WHERE p.id = :p GROUP BY p.budget");
            $q->execute(['p' => $tache->getIdProjet()]);
            $row = $q->fetch();
            if ($row && (float)$tache->getPrix() > 0) {
                $restant = (float)$row['budget'] - (float)$row['total_taches'];
                if ((float)$tache->getPrix() > $restant) {
                    return ['success' => false, 'message' => sprintf('Budget dépassé. Restant : %.2f TND.', $restant)];
                }
            }
            $q = $db->prepare("INSERT INTO tache (id_projet, id_freelancer, titre, description, statut, prix)
                VALUES (:p, :f, :t, :d, :s, :prix)");
            $q->execute([
                'p'    => $tache->getIdProjet(),
                'f'    => $tache->getIdFreelancer(),
                't'    => $tache->getTitre(),
                'd'    => $tache->getDescription(),
                's'    => $tache->getStatut(),
                'prix' => $tache->getPrix(),
            ]);
            return ['success' => true, 'message' => 'Tâche ajoutée avec succès.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    // ── FREELANCERS ACCEPTÉS SUR UN PROJET ───────────────────────────
    public function getFreelancersAcceptes($id_projet)
    {
        $db = Config::getConnexion();
        try {
            $q = $db->prepare("SELECT u.id, u.nom, u.prenom
                FROM candidature c
                JOIN user u ON u.id = c.id_freelancer
                WHERE c.id_projet = :p AND c.statut = 'accepte'");
            $q->execute(['p' => $id_projet]);
            return $q->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    // ── BUDGET RESTANT D'UN PROJET ────────────────────────────────────
    public function getBudgetRestant($id_projet)
    {
        $db = Config::getConnexion();
        try {
            $q = $db->prepare("SELECT p.budget, COALESCE(SUM(t.prix),0) AS total_taches
                FROM projet p
                LEFT JOIN tache t ON t.id_projet = p.id
                WHERE p.id = :p
                GROUP BY p.budget");
            $q->execute(['p' => $id_projet]);
            $row = $q->fetch();
            if (!$row) return null;
            return [
                'budget'       => (float)$row['budget'],
                'total_taches' => (float)$row['total_taches'],
                'restant'      => (float)$row['budget'] - (float)$row['total_taches'],
            ];
        } catch (Exception $e) { return null; }
    }

    // ── SUPPRIMER TACHE ───────────────────────────────────────────────
    public function supprimerTache($id, $id_freelancer)
    {
        $db = Config::getConnexion();
        try {
            $q = $db->prepare("DELETE FROM tache WHERE id=:id AND id_freelancer=:f");
            $q->execute(['id' => $id, 'f' => $id_freelancer]);
            return true;
        } catch (Exception $e) { return false; }
    }

    // ── PAYER TACHE (CLIENT) ──────────────────────────────────────────
    public function payerTache($id_tache, $id_client)
    {
        $db = Config::getConnexion();
        try {
            // Vérifier que la tâche appartient bien à un projet du client et est terminée
            $q = $db->prepare("SELECT t.id, t.statut, t.payee, t.prix
                FROM tache t
                JOIN projet p ON p.id = t.id_projet
                WHERE t.id = :id AND p.id_client = :c");
            $q->execute(['id' => $id_tache, 'c' => $id_client]);
            $tache = $q->fetch();
            if (!$tache)                    return ['success' => false, 'message' => 'Tâche introuvable.'];
            if ($tache['statut'] !== 'termine') return ['success' => false, 'message' => 'La tâche doit être terminée pour être payée.'];
            if ($tache['payee'])            return ['success' => false, 'message' => 'Cette tâche a déjà été payée.'];

            $q = $db->prepare("UPDATE tache SET payee=1 WHERE id=:id");
            $q->execute(['id' => $id_tache]);
            return ['success' => true, 'message' => 'Paiement effectué avec succès.', 'prix' => (float)$tache['prix']];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    // ── METTRE À JOUR STATUT TACHE ────────────────────────────────────
    public function updateTacheStatut($id, $id_freelancer, $statut)
    {
        $db = Config::getConnexion();
        try {
            // Récupérer l'id_projet avant la mise à jour
            $q = $db->prepare("SELECT id_projet FROM tache WHERE id=:id AND id_freelancer=:f");
            $q->execute(['id' => $id, 'f' => $id_freelancer]);
            $row = $q->fetch();
            if (!$row) return ['success' => false, 'message' => 'Tâche introuvable.'];

            $q = $db->prepare("UPDATE tache SET statut=:s WHERE id=:id AND id_freelancer=:f");
            $q->execute(['s' => $statut, 'id' => $id, 'f' => $id_freelancer]);

            // Vérifier si le projet doit passer à terminé
            $termine = $this->verifierEtTerminerProjet($row['id_projet']);

            return ['success' => true, 'projet_termine' => $termine];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ── VÉRIFIER ET TERMINER PROJET ───────────────────────────────────
    private function verifierEtTerminerProjet($id_projet)
    {
        $db = Config::getConnexion();
        try {
            // Vérifier avancement = 100
            $q = $db->prepare("SELECT avancement FROM projet WHERE id=:p");
            $q->execute(['p' => $id_projet]);
            $projet = $q->fetch();
            if (!$projet || (int)$projet['avancement'] < 100) return false;

            // Vérifier que toutes les tâches sont terminées (et qu'il y en a au moins une)
            $q = $db->prepare("SELECT COUNT(*) AS total, SUM(CASE WHEN statut='termine' THEN 1 ELSE 0 END) AS terminees FROM tache WHERE id_projet=:p");
            $q->execute(['p' => $id_projet]);
            $row = $q->fetch();
            if (!$row || (int)$row['total'] === 0) return false;
            if ((int)$row['total'] !== (int)$row['terminees']) return false;

            // Tout est OK → passer le projet à terminé
            $q = $db->prepare("UPDATE projet SET statut='termine' WHERE id=:p AND statut != 'termine'");
            $q->execute(['p' => $id_projet]);
            return true;
        } catch (Exception $e) { return false; }
    }

    // ── TACHES D'UN PROJET (vue client) ──────────────────────────────
    public function getTachesProjet($id_projet)
    {
        $db = Config::getConnexion();
        try {
            $q = $db->prepare("SELECT t.*, u.nom AS nom_freelancer, u.prenom AS prenom_freelancer
                FROM tache t
                JOIN user u ON u.id = t.id_freelancer
                WHERE t.id_projet = :p
                ORDER BY t.statut ASC, t.id DESC");
            $q->execute(['p' => $id_projet]);
            return $q->fetchAll();
        } catch (Exception $e) { return []; }
    }

    // ── PROJETS D'UN CLIENT ───────────────────────────────────────────
    public function getProjetsClient($id_client)
    {
        $db = Config::getConnexion();
        try {
            $q = $db->prepare("SELECT * FROM projet WHERE id_client = :c ORDER BY id DESC");
            $q->execute(['c' => $id_client]);
            return $q->fetchAll();
        } catch (Exception $e) { return []; }
    }
    public function getAllTaches()
    {
        $db = Config::getConnexion();
        try {
            $q = $db->query("SELECT t.*, p.titre AS titre_projet,
                u.nom AS nom_freelancer, u.prenom AS prenom_freelancer
                FROM tache t
                JOIN projet p ON p.id = t.id_projet
                JOIN user u ON u.id = t.id_freelancer
                ORDER BY t.id DESC");
            return $q->fetchAll();
        } catch (Exception $e) { return []; }
    }

    // ── MODIFIER TACHE (ADMIN) ────────────────────────────────────────
    public function modifierTacheAdmin($id, $titre, $description, $statut, $prix)
    {
        $db = Config::getConnexion();
        try {
            $q = $db->prepare("UPDATE tache SET titre=:t, description=:d, statut=:s, prix=:p WHERE id=:id");
            $q->execute(['t'=>$titre,'d'=>$description,'s'=>$statut,'p'=>$prix,'id'=>$id]);
            return ['success' => true, 'message' => 'Tâche modifiée.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ── SUPPRIMER TACHE (ADMIN) ───────────────────────────────────────
    public function supprimerTacheAdmin($id)
    {
        $db = Config::getConnexion();
        try {
            $q = $db->prepare("DELETE FROM tache WHERE id=:id");
            $q->execute(['id' => $id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
