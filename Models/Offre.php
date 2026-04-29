<?php
require_once __DIR__ . '/../config.php';

class Offre
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
        $this->ensureSchema();
    }

    private function ensureSchema()
    {
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS offre_job (
                id_offre INT(11) NOT NULL AUTO_INCREMENT,
                titre VARCHAR(200) NOT NULL,
                description TEXT NOT NULL,
                budget DECIMAL(10,2) NOT NULL,
                delai_jours INT(11) NOT NULL DEFAULT 7,
                execution_mode ENUM('full_project','milestone') DEFAULT 'full_project',
                milestone_plan TEXT DEFAULT NULL,
                niveau_requis ENUM('debutant','intermediaire','expert') DEFAULT 'intermediaire',
                competences_requises TEXT DEFAULT NULL,
                statut ENUM('actif','suspendu','en_attente','fermee') DEFAULT 'en_attente',
                id_client INT(11) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id_offre),
                KEY idx_offre_client (id_client),
                CONSTRAINT fk_offre_client_user FOREIGN KEY (id_client) REFERENCES User(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
        );

        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS candidature_offre (
                id_candidature INT(11) NOT NULL AUTO_INCREMENT,
                id_offre INT(11) NOT NULL,
                id_freelancer INT(11) NOT NULL,
                message TEXT NOT NULL,
                budget_propose DECIMAL(10,2) NOT NULL,
                disponibilite_jours INT(11) NOT NULL,
                execution_mode ENUM('full_project','milestone') DEFAULT 'full_project',
                milestone_plan TEXT DEFAULT NULL,
                cv_url VARCHAR(255) DEFAULT NULL,
                portfolio_url VARCHAR(255) DEFAULT NULL,
                statut ENUM('en_attente','acceptee','refusee') DEFAULT 'en_attente',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id_candidature),
                UNIQUE KEY uniq_offre_freelancer (id_offre, id_freelancer),
                KEY idx_candidature_freelancer (id_freelancer),
                CONSTRAINT fk_candidature_offre_job FOREIGN KEY (id_offre) REFERENCES offre_job(id_offre) ON DELETE CASCADE,
                CONSTRAINT fk_candidature_freelancer_user FOREIGN KEY (id_freelancer) REFERENCES User(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
        );

        $this->ensureOfferColumn('execution_mode', "ALTER TABLE offre_job ADD COLUMN execution_mode ENUM('full_project','milestone') DEFAULT 'full_project' AFTER delai_jours");
        $this->ensureOfferColumn('milestone_plan', "ALTER TABLE offre_job ADD COLUMN milestone_plan TEXT DEFAULT NULL AFTER execution_mode");
        $this->ensureApplicationColumn('execution_mode', "ALTER TABLE candidature_offre ADD COLUMN execution_mode ENUM('full_project','milestone') DEFAULT 'full_project' AFTER disponibilite_jours");
        $this->ensureApplicationColumn('milestone_plan', "ALTER TABLE candidature_offre ADD COLUMN milestone_plan TEXT DEFAULT NULL AFTER execution_mode");
        $this->ensureApplicationColumn('cv_url', "ALTER TABLE candidature_offre ADD COLUMN cv_url VARCHAR(255) DEFAULT NULL AFTER disponibilite_jours");
        $this->ensureApplicationColumn('portfolio_url', "ALTER TABLE candidature_offre ADD COLUMN portfolio_url VARCHAR(255) DEFAULT NULL AFTER cv_url");
    }

    private function ensureOfferColumn($column, $sql)
    {
        $stmt = $this->db->prepare("SHOW COLUMNS FROM offre_job LIKE ?");
        $stmt->execute([$column]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->db->exec($sql);
        }
    }

    private function ensureApplicationColumn($column, $sql)
    {
        $stmt = $this->db->prepare("SHOW COLUMNS FROM candidature_offre LIKE ?");
        $stmt->execute([$column]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->db->exec($sql);
        }
    }

    public function listActive($search = '')
    {
        $sql = "SELECT o.*, u.nom, u.prenom,
                       (SELECT COUNT(*) FROM candidature_offre c WHERE c.id_offre = o.id_offre) AS candidature_count
                FROM offre_job o
                JOIN User u ON u.id = o.id_client
                WHERE o.statut = 'actif'";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (o.titre LIKE :search OR o.description LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listByClient($idClient)
    {
        $stmt = $this->db->prepare(
            "SELECT o.*,
                    (SELECT COUNT(*) FROM candidature_offre c WHERE c.id_offre = o.id_offre) AS candidature_count
             FROM offre_job o
             WHERE o.id_client = :id_client
             ORDER BY o.created_at DESC"
        );
        $stmt->execute([':id_client' => $idClient]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listAll($search = '', $status = '')
    {
        $sql = "SELECT o.*, u.nom, u.prenom,
                       (SELECT COUNT(*) FROM candidature_offre c WHERE c.id_offre = o.id_offre) AS candidature_count
                FROM offre_job o
                JOIN User u ON u.id = o.id_client
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (o.titre LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if ($status !== '' && $status !== 'all') {
            $sql .= " AND o.statut = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($idOffre)
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.nom, u.prenom, u.email
             FROM offre_job o
             JOIN User u ON u.id = o.id_client
             WHERE o.id_offre = :id_offre"
        );
        $stmt->execute([':id_offre' => $idOffre]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findOwnedByClient($idOffre, $idClient)
    {
        $stmt = $this->db->prepare("SELECT * FROM offre_job WHERE id_offre = :id_offre AND id_client = :id_client");
        $stmt->execute([
            ':id_offre' => $idOffre,
            ':id_client' => $idClient
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO offre_job (titre, description, budget, delai_jours, execution_mode, milestone_plan, niveau_requis, competences_requises, statut, id_client)
             VALUES (:titre, :description, :budget, :delai_jours, :execution_mode, :milestone_plan, :niveau_requis, :competences_requises, 'en_attente', :id_client)"
        );
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update($idOffre, array $data)
    {
        $data[':id_offre'] = $idOffre;
        $stmt = $this->db->prepare(
            "UPDATE offre_job
             SET titre = :titre,
                 description = :description,
                 budget = :budget,
                 delai_jours = :delai_jours,
                 execution_mode = :execution_mode,
                 milestone_plan = :milestone_plan,
                 niveau_requis = :niveau_requis,
                 competences_requises = :competences_requises,
                 statut = 'en_attente',
                 updated_at = CURRENT_TIMESTAMP
             WHERE id_offre = :id_offre"
        );
        return $stmt->execute($data);
    }

    public function delete($idOffre)
    {
        $stmt = $this->db->prepare("DELETE FROM offre_job WHERE id_offre = :id_offre");
        return $stmt->execute([':id_offre' => $idOffre]);
    }

    public function updateStatus($idOffre, $status)
    {
        $stmt = $this->db->prepare("UPDATE offre_job SET statut = :status, updated_at = CURRENT_TIMESTAMP WHERE id_offre = :id_offre");
        return $stmt->execute([
            ':status' => $status,
            ':id_offre' => $idOffre
        ]);
    }

    public function createApplication(array $data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO candidature_offre (id_offre, id_freelancer, message, budget_propose, disponibilite_jours, execution_mode, milestone_plan, cv_url, portfolio_url, statut)
             VALUES (:id_offre, :id_freelancer, :message, :budget_propose, :disponibilite_jours, :execution_mode, :milestone_plan, :cv_url, :portfolio_url, 'en_attente')"
        );
        return $stmt->execute($data);
    }

    public function hasApplied($idOffre, $idFreelancer)
    {
        $stmt = $this->db->prepare(
            "SELECT id_candidature
             FROM candidature_offre
             WHERE id_offre = :id_offre AND id_freelancer = :id_freelancer"
        );
        $stmt->execute([
            ':id_offre' => $idOffre,
            ':id_freelancer' => $idFreelancer
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function listApplicationsByOffer($idOffre)
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.nom, u.prenom, u.email, u.niveau
             FROM candidature_offre c
             JOIN User u ON u.id = c.id_freelancer
             WHERE c.id_offre = :id_offre
             ORDER BY c.created_at DESC"
        );
        $stmt->execute([':id_offre' => $idOffre]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listApplicationsByFreelancer($idFreelancer)
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, o.titre, o.budget, o.niveau_requis, o.statut AS offre_statut, u.nom, u.prenom
             FROM candidature_offre c
             JOIN offre_job o ON o.id_offre = c.id_offre
             JOIN User u ON u.id = o.id_client
             WHERE c.id_freelancer = :id_freelancer
             ORDER BY c.created_at DESC"
        );
        $stmt->execute([':id_freelancer' => $idFreelancer]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listAllApplications($search = '', $status = '')
    {
        $sql = "SELECT c.*, o.titre, client.nom AS client_nom, client.prenom AS client_prenom,
                       freelancer.nom AS freelancer_nom, freelancer.prenom AS freelancer_prenom
                FROM candidature_offre c
                JOIN offre_job o ON o.id_offre = c.id_offre
                JOIN User client ON client.id = o.id_client
                JOIN User freelancer ON freelancer.id = c.id_freelancer
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (o.titre LIKE :search OR client.nom LIKE :search OR freelancer.nom LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if ($status !== '' && $status !== 'all') {
            $sql .= " AND c.statut = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function listFreelancers()
    {
        $stmt = $this->db->prepare(
            "SELECT id, nom, prenom, email
             FROM User
             WHERE id_role = 3
             ORDER BY prenom ASC, nom ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findFreelancer($idFreelancer)
    {
        $stmt = $this->db->prepare(
            "SELECT id, nom, prenom, email
             FROM User
             WHERE id = :id AND id_role = 3"
        );
        $stmt->execute([':id' => $idFreelancer]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }



    public function updateApplicationStatus($idCandidature, $status)
    {
        $stmt = $this->db->prepare("UPDATE candidature_offre SET statut = :status WHERE id_candidature = :id_candidature");
        return $stmt->execute([
            ':status' => $status,
            ':id_candidature' => $idCandidature
        ]);
    }

    public function getClientStats($idClient)
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total_offres,
                SUM(CASE WHEN statut = 'actif' THEN 1 ELSE 0 END) AS offres_actives,
                SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) AS offres_attente,
                (
                    SELECT COUNT(*)
                    FROM candidature_offre c
                    JOIN offre_job o2 ON o2.id_offre = c.id_offre
                    WHERE o2.id_client = :id_client
                ) AS total_candidatures
             FROM offre_job
             WHERE id_client = :id_client"
        );
        $stmt->execute([':id_client' => $idClient]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFreelancerStats($idFreelancer)
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total_candidatures,
                SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) AS candidatures_attente,
                SUM(CASE WHEN statut = 'acceptee' THEN 1 ELSE 0 END) AS candidatures_acceptees
             FROM candidature_offre
             WHERE id_freelancer = :id_freelancer"
        );
        $stmt->execute([':id_freelancer' => $idFreelancer]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAdminStats()
    {
        return [
            'total_offres' => (int) $this->db->query("SELECT COUNT(*) FROM offre_job")->fetchColumn(),
            'offres_actives' => (int) $this->db->query("SELECT COUNT(*) FROM offre_job WHERE statut = 'actif'")->fetchColumn(),
            'offres_attente' => (int) $this->db->query("SELECT COUNT(*) FROM offre_job WHERE statut = 'en_attente'")->fetchColumn(),
            'total_candidatures' => (int) $this->db->query("SELECT COUNT(*) FROM candidature_offre")->fetchColumn(),
            'candidatures_attente' => (int) $this->db->query("SELECT COUNT(*) FROM candidature_offre WHERE statut = 'en_attente'")->fetchColumn(),
            'candidatures_acceptees' => (int) $this->db->query("SELECT COUNT(*) FROM candidature_offre WHERE statut = 'acceptee'")->fetchColumn()
        ];
    }
}
?>

