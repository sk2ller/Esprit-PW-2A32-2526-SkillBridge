<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../models/Offre.php');

class OffreController
{
    public function addOffre(Offre $offre)
    {
        $sql = "INSERT INTO offres (titre, description, budget, delai_publication, niveau_requis, competences_requises, statut, id_client)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $titre = $offre->getTitre();
            $description = $offre->getDescription();
            $budget = $offre->getBudget();
            $delai = $offre->getDelaiPublication();
            $niveau = $offre->getNiveauRequis();
            $competences = $offre->getCompetencesRequises();
            $statut = $offre->getStatut();
            $idClient = $offre->getIdClient();

            $query->bind_param("ssdisssi", $titre, $description, $budget, $delai, $niveau, $competences, $statut, $idClient);
            return $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function countAll($statut = null, $search = null): int
    {
        $sql    = "SELECT COUNT(*) as total FROM offres o WHERE 1=1";
        $params = [];
        $types  = "";

        if ($statut !== null && $statut !== '') {
            $sql     .= " AND o.statut = ?";
            $params[] = $statut;
            $types   .= "s";
        }
        if ($search !== null && $search !== '') {
            $sql     .= " AND (o.titre LIKE ? OR o.description LIKE ?)";
            $like     = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
            $types   .= "ss";
        }

        $db   = getDB();
        $stmt = $db->prepare($sql);
        if ($stmt === false) return 0;
        if (!empty($params)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    }

    public function listAll($statut = null, $search = null, $sort = 'recent', $perPage = null, $page = 1)
    {
        $sql = "SELECT o.*, COALESCE(o.nom_client, 'Client Anonyme') as nom_client
                FROM offres o
                WHERE 1=1";
        $params = [];
        $types = "";

        if ($statut !== null && $statut !== '') {
            $sql .= " AND o.statut = ?";
            $params[] = $statut;
            $types .= "s";
        }

        if ($search !== null && $search !== '') {
            $sql .= " AND (o.titre LIKE ? OR o.description LIKE ?)";
            $searchValue = "%" . $search . "%";
            $params[] = $searchValue;
            $params[] = $searchValue;
            $types .= "ss";
        }

        $allowedSorts = [
            'recent' => 'o.created_at DESC',
            'ancien' => 'o.created_at ASC',
            'budget_asc' => 'o.budget ASC',
            'budget_desc' => 'o.budget DESC'
        ];
        $orderBy = $allowedSorts[$sort] ?? $allowedSorts['recent'];
        $sql .= " ORDER BY {$orderBy}";

        if ($perPage !== null) {
            $offset    = ($page - 1) * $perPage;
            $sql      .= " LIMIT ? OFFSET ?";
            $params[]  = $perPage;
            $params[]  = $offset;
            $types    .= "ii";
        }

        $db = getDB();

        try {
            $query = $db->prepare($sql);
            if ($query === false) {
                throw new Exception("SQL prepare failed: " . $db->error);
            }

            if (!empty($params)) {
                $query->bind_param($types, ...$params);
            }

            $query->execute();
            return $query->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function getOffreById($id)
    {
        $sql = "SELECT o.*,
                       COALESCE(o.nom_client, 'Client Anonyme') as nom_client,
                       o.email_client
                FROM offres o
                WHERE o.id_offre = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            if ($query === false) {
                throw new Exception("SQL prepare failed: " . $db->error);
            }
            $query->bind_param("i", $id);
            $query->execute();

            $result = $query->get_result()->fetch_assoc();
            if ($result) {
                $offre = new Offre(
                    $result['titre'],
                    $result['description'],
                    $result['budget'],
                    $result['delai_publication'],
                    $result['niveau_requis'],
                    $result['competences_requises'],
                    $result['statut'],
                    $result['id_client']
                );

                $offre->setIdOffre($result['id_offre']);
                $offre->setCreatedAt($result['created_at']);
                $offre->setUpdatedAt($result['updated_at']);

                return [
                    'entity' => $offre,
                    'data' => $result
                ];
            }

            return null;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return null;
        }
    }

    public function listByClient($idClient, $sort = 'recent')
    {
        $allowedSorts = [
            'recent' => 'created_at DESC',
            'ancien' => 'created_at ASC',
            'budget_asc' => 'budget ASC',
            'budget_desc' => 'budget DESC'
        ];
        $orderBy = $allowedSorts[$sort] ?? $allowedSorts['recent'];
        $sql = "SELECT * FROM offres WHERE id_client = ? ORDER BY {$orderBy}";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->bind_param("i", $idClient);
            $query->execute();
            return $query->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function updateOffre(Offre $offre)
    {
        $sql = "UPDATE offres
                SET titre = ?, description = ?, budget = ?, delai_publication = ?, niveau_requis = ?, competences_requises = ?, statut = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id_offre = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $id = $offre->getIdOffre();
            $titre = $offre->getTitre();
            $description = $offre->getDescription();
            $budget = $offre->getBudget();
            $delai = $offre->getDelaiPublication();
            $niveau = $offre->getNiveauRequis();
            $competences = $offre->getCompetencesRequises();
            $statut = $offre->getStatut();

            $query->bind_param("ssdisssi", $titre, $description, $budget, $delai, $niveau, $competences, $statut, $id);
            return $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function deleteOffre($id)
    {
        $sql = "DELETE FROM offres WHERE id_offre = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->bind_param("i", $id);
            return $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function updateStatut($id, $statut)
    {
        $sql = "UPDATE offres SET statut = ?, updated_at = CURRENT_TIMESTAMP WHERE id_offre = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->bind_param("si", $statut, $id);
            return $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function getStats()
    {
        $db = getDB();
        $stats = [
            'total' => 0,
            'actif' => 0,
            'en_attente' => 0,
            'suspendu' => 0
        ];

        try {
            $stats['total'] = $db->query("SELECT COUNT(*) as count FROM offres")->fetch_assoc()['count'] ?? 0;
            $stats['actif'] = $db->query("SELECT COUNT(*) as count FROM offres WHERE statut = 'actif'")->fetch_assoc()['count'] ?? 0;
            $stats['en_attente'] = $db->query("SELECT COUNT(*) as count FROM offres WHERE statut = 'en_attente'")->fetch_assoc()['count'] ?? 0;
            $stats['suspendu'] = $db->query("SELECT COUNT(*) as count FROM offres WHERE statut = 'suspendu'")->fetch_assoc()['count'] ?? 0;
            return $stats;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return $stats;
        }
    }

    public function getAdminInsights()
    {
        $db = getDB();
        $insights = [
            'average_budget' => 0,
            'highest_budget' => 0,
            'lowest_budget' => 0,
            'by_level' => [],
            'recent_count' => 0,
            'status_chart' => []
        ];

        try {
            $budgetStats = $db->query("SELECT AVG(budget) as avg_budget, MAX(budget) as max_budget, MIN(budget) as min_budget FROM offres")->fetch_assoc();
            $insights['average_budget'] = round((float) ($budgetStats['avg_budget'] ?? 0), 2);
            $insights['highest_budget'] = round((float) ($budgetStats['max_budget'] ?? 0), 2);
            $insights['lowest_budget'] = round((float) ($budgetStats['min_budget'] ?? 0), 2);

            $levelQuery = $db->query("SELECT niveau_requis, COUNT(*) as total, AVG(budget) as avg_budget FROM offres GROUP BY niveau_requis ORDER BY total DESC");
            $insights['by_level'] = $levelQuery ? $levelQuery->fetch_all(MYSQLI_ASSOC) : [];

            $recentQuery = $db->query("SELECT COUNT(*) as count FROM offres WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $insights['recent_count'] = $recentQuery->fetch_assoc()['count'] ?? 0;

            $stats = $this->getStats();
            $insights['status_chart'] = [
                ['label' => 'Actif', 'value' => (int) ($stats['actif'] ?? 0)],
                ['label' => 'En attente', 'value' => (int) ($stats['en_attente'] ?? 0)],
                ['label' => 'Suspendu', 'value' => (int) ($stats['suspendu'] ?? 0)]
            ];
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }

        return $insights;
    }

    public function getClientStats($idClient)
    {
        $sql = "SELECT
                    COUNT(*) as total_offres,
                    SUM(CASE WHEN statut = 'actif' THEN 1 ELSE 0 END) as offres_actives,
                    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                    SUM(CASE WHEN statut = 'suspendu' THEN 1 ELSE 0 END) as suspendues
                FROM offres
                WHERE id_client = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->bind_param("i", $idClient);
            $query->execute();
            return $query->get_result()->fetch_assoc();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [
                'total_offres' => 0,
                'offres_actives' => 0,
                'en_attente' => 0,
                'suspendues' => 0
            ];
        }
    }

    public function index()
    {
        $search  = $_GET['search'] ?? null;
        $sort    = $_GET['sort'] ?? 'recent';
        $perPage = 9;

        $totalItems  = $this->countAll('actif', $search);
        $totalPages  = (int)ceil($totalItems / $perPage);
        $currentPage = max(1, min((int)($_GET['p'] ?? 1), max(1, $totalPages)));

        $offres = $this->listAll('actif', $search, $sort, $perPage, $currentPage);
        require_once(__DIR__ . '/../views/FrontOffice/offres_list.php');
    }

    public function show($id)
    {
        $result = $this->getOffreById($id);

        if (!$result) {
            header("Location: index.php?page=offres");
            exit;
        }

        $offre = $result['data'];
        require_once(__DIR__ . '/../views/FrontOffice/offre_detail.php');
    }

    public function myOffres()
    {
        $idClient = $_GET['id_client'] ?? 1;
        $sort = $_GET['sort'] ?? 'recent';
        $offres = $this->listByClient($idClient, $sort);
        $stats = $this->getClientStats($idClient);

        require_once(__DIR__ . '/../views/FrontOffice/mes_offres.php');
    }

    public function create()
    {
        $error = null;
        $idClient = $_GET['id_client'] ?? 1;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['titre']) || empty($_POST['description']) || empty($_POST['budget'])) {
                $error = "Tous les champs obligatoires doivent être remplis.";
            } elseif ($_POST['budget'] <= 0) {
                $error = "Le budget doit être supérieur à 0.";
            } elseif (strlen($_POST['titre']) < 5 || strlen($_POST['titre']) > 200) {
                $error = "Le titre doit contenir entre 5 et 200 caractères.";
            } elseif (strlen($_POST['description']) < 20) {
                $error = "La description doit contenir au moins 20 caractères.";
            } else {
                $offre = new Offre(
                    htmlspecialchars($_POST['titre']),
                    htmlspecialchars($_POST['description']),
                    (float) $_POST['budget'],
                    (int) ($_POST['delai_publication'] ?? 30),
                    htmlspecialchars($_POST['niveau_requis'] ?? 'intermediaire'),
                    htmlspecialchars($_POST['competences_requises'] ?? ''),
                    'en_attente',
                    $idClient
                );

                $this->addOffre($offre);
                header("Location: index.php?page=mes_offres&id_client=$idClient&success=1");
                exit;
            }
        }

        require_once(__DIR__ . '/../views/FrontOffice/offre_form.php');
    }

    public function edit($id)
    {
        $result = $this->getOffreById($id);

        if (!$result) {
            header("Location: index.php?page=mes_offres");
            exit;
        }

        $offreData = $result['data'];
        $offreEntity = $result['entity'];
        $offre = $offreData;
        $idClient = $offreData['id_client'];
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['titre']) || empty($_POST['description']) || empty($_POST['budget'])) {
                $error = "Tous les champs obligatoires doivent être remplis.";
            } elseif ($_POST['budget'] <= 0) {
                $error = "Le budget doit être supérieur à 0.";
            } else {
                $updatedOffre = new Offre(
                    htmlspecialchars($_POST['titre']),
                    htmlspecialchars($_POST['description']),
                    (float) $_POST['budget'],
                    (int) ($_POST['delai_publication'] ?? 30),
                    htmlspecialchars($_POST['niveau_requis'] ?? 'intermediaire'),
                    htmlspecialchars($_POST['competences_requises'] ?? ''),
                    'en_attente',
                    $offreEntity->getIdClient()
                );

                $updatedOffre->setIdOffre($id);
                $this->updateOffre($updatedOffre);

                header("Location: index.php?page=mes_offres&id_client=$idClient&success=2");
                exit;
            }
        }

        require_once(__DIR__ . '/../views/FrontOffice/offre_form.php');
    }

    public function delete($id)
    {
        $result = $this->getOffreById($id);

        if (!$result) {
            header("Location: index.php?page=mes_offres");
            exit;
        }

        $idClient = $result['data']['id_client'];
        $this->deleteOffre($id);

        header("Location: index.php?page=mes_offres&id_client=$idClient&success=3");
        exit;
    }

    public function adminIndex()
    {
        $filter  = $_GET['filter'] ?? 'all';
        $search  = $_GET['search'] ?? null;
        $sort    = $_GET['sort'] ?? 'recent';
        $perPage = 10;

        $statut      = ($filter === 'all') ? null : $filter;
        $totalItems  = $this->countAll($statut, $search);
        $totalPages  = (int)ceil($totalItems / $perPage);
        $currentPage = max(1, min((int)($_GET['p'] ?? 1), max(1, $totalPages)));

        $offres   = $this->listAll($statut, $search, $sort, $perPage, $currentPage);
        $stats    = $this->getStats();
        $insights = $this->getAdminInsights();
        require_once(__DIR__ . '/../views/BackOffice/offres.php');
    }

    public function adminUpdateStatut($id, $statut)
    {
        $statutsValides = ['actif', 'suspendu', 'en_attente'];

        if (!in_array($statut, $statutsValides)) {
            header("Location: index.php?page=admin_offres&error=1");
            exit;
        }

        $this->updateStatut($id, $statut);
        header("Location: index.php?page=admin_offres&success=1");
        exit;
    }
}
