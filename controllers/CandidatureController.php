<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../models/Candidature.php');

class CandidatureController
{
    public function addCandidature(Candidature $candidature)
    {
        $sql = "INSERT INTO candidatures (id_offre, id_freelancer, message, proposition_budget, delai_propose, statut)
                VALUES (?, ?, ?, ?, ?, ?)";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $idOffre = $candidature->getIdOffre();
            $idFreelancer = $candidature->getIdFreelancer();
            $message = $candidature->getMessage();
            $budget = $candidature->getPropositionBudget();
            $delai = $candidature->getDelaiPropose();
            $statut = $candidature->getStatut();

            $query->bind_param("iisdis", $idOffre, $idFreelancer, $message, $budget, $delai, $statut);
            return $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function listAll($statut = null)
    {
        $sql = "SELECT c.*, o.titre as titre_offre
                FROM candidatures c
                LEFT JOIN offres o ON c.id_offre = o.id_offre
                WHERE 1=1";
        $params = [];
        $types = "";

        if ($statut !== null && $statut !== '') {
            $sql .= " AND c.statut = ?";
            $params[] = $statut;
            $types .= "s";
        }

        $sql .= " ORDER BY c.created_at DESC";
        $db = getDB();

        try {
            $query = $db->prepare($sql);

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

    public function getCandidatureById($id)
    {
        $sql = "SELECT * FROM candidatures WHERE id_candidature = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->bind_param("i", $id);
            $query->execute();

            $result = $query->get_result()->fetch_assoc();
            if ($result) {
                $candidature = new Candidature(
                    $result['id_offre'],
                    $result['id_freelancer'],
                    $result['message'],
                    $result['proposition_budget'],
                    $result['delai_propose'],
                    $result['statut']
                );

                $candidature->setIdCandidature($result['id_candidature']);
                $candidature->setCreatedAt($result['created_at']);
                $candidature->setUpdatedAt($result['updated_at']);

                return $candidature;
            }

            return null;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return null;
        }
    }

    public function listByOffre($idOffre)
    {
        $sql = "SELECT * FROM candidatures WHERE id_offre = ? ORDER BY created_at DESC";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->bind_param("i", $idOffre);
            $query->execute();
            return $query->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function listByFreelancer($idFreelancer)
    {
        $sql = "SELECT * FROM candidatures WHERE id_freelancer = ? ORDER BY created_at DESC";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->bind_param("i", $idFreelancer);
            $query->execute();
            return $query->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function updateCandidature(Candidature $candidature)
    {
        $sql = "UPDATE candidatures
                SET message = ?, proposition_budget = ?, delai_propose = ?, statut = ?
                WHERE id_candidature = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $id = $candidature->getIdCandidature();
            $message = $candidature->getMessage();
            $budget = $candidature->getPropositionBudget();
            $delai = $candidature->getDelaiPropose();
            $statut = $candidature->getStatut();

            $query->bind_param("sdisi", $message, $budget, $delai, $statut, $id);
            return $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function deleteCandidature($id)
    {
        $sql = "DELETE FROM candidatures WHERE id_candidature = ?";
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
        $sql = "UPDATE candidatures SET statut = ?, updated_at = CURRENT_TIMESTAMP WHERE id_candidature = ?";
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
        return [
            'total' => $db->query("SELECT COUNT(*) as count FROM candidatures")->fetch_assoc()['count'] ?? 0,
            'en_attente' => $db->query("SELECT COUNT(*) as count FROM candidatures WHERE statut = 'en_attente'")->fetch_assoc()['count'] ?? 0,
            'acceptee' => $db->query("SELECT COUNT(*) as count FROM candidatures WHERE statut = 'acceptee'")->fetch_assoc()['count'] ?? 0,
            'refusee' => $db->query("SELECT COUNT(*) as count FROM candidatures WHERE statut = 'refusee'")->fetch_assoc()['count'] ?? 0,
        ];
    }

    public function countAdminList($filter = 'all', $search = null): int
    {
        $db  = getDB();
        $sql = "SELECT COUNT(*) as total
                FROM candidatures c
                LEFT JOIN offres o ON c.id_offre = o.id_offre
                WHERE 1=1";
        $params = [];
        $types  = "";

        if ($filter && $filter !== 'all') {
            $sql .= " AND c.statut = ?";
            $params[] = $filter;
            $types   .= "s";
        }
        if (!empty($search)) {
            $sql .= " AND (c.nom_freelancer LIKE ? OR c.email_freelancer LIKE ? OR o.titre LIKE ?)";
            $like      = "%{$search}%";
            $params[]  = $like;
            $params[]  = $like;
            $params[]  = $like;
            $types    .= "sss";
        }

        $stmt = $db->prepare($sql);
        if ($stmt === false) return 0;
        if (!empty($params)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    }

    public function getAdminList($filter = 'all', $search = null, $sort = 'recent', $perPage = 10, $page = 1)
    {
        $db = getDB();
        $sql = "SELECT c.*, o.titre as titre_offre
                FROM candidatures c
                LEFT JOIN offres o ON c.id_offre = o.id_offre
                WHERE 1=1";
        $params = [];
        $types = "";

        if ($filter && $filter !== 'all') {
            $sql .= " AND c.statut = ?";
            $params[] = $filter;
            $types .= "s";
        }
        if (!empty($search)) {
            $sql .= " AND (c.nom_freelancer LIKE ? OR c.email_freelancer LIKE ? OR o.titre LIKE ?)";
            $searchLike = "%{$search}%";
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
            $types .= "sss";
        }

        $allowedSorts = [
            'recent' => 'c.created_at DESC',
            'ancien' => 'c.created_at ASC',
            'tarif_asc' => 'c.tarif_propose ASC',
            'tarif_desc' => 'c.tarif_propose DESC'
        ];
        $orderBy = $allowedSorts[$sort] ?? $allowedSorts['recent'];
        $sql .= " ORDER BY {$orderBy}";

        $offset    = ($page - 1) * $perPage;
        $sql      .= " LIMIT ? OFFSET ?";
        $params[]  = $perPage;
        $params[]  = $offset;
        $types    .= "ii";

        $stmt = $db->prepare($sql);
        if ($stmt === false) return [];
        if (!empty($params)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAdminInsights()
    {
        $db = getDB();
        $insights = [
            'average_tarif' => 0,
            'highest_tarif' => 0,
            'with_documents' => 0,
            'by_status' => [],
            'by_offer' => []
        ];

        try {
            $tarifStats = $db->query("SELECT AVG(tarif_propose) as avg_tarif, MAX(tarif_propose) as max_tarif FROM candidatures")->fetch_assoc();
            $insights['average_tarif'] = round((float) ($tarifStats['avg_tarif'] ?? 0), 2);
            $insights['highest_tarif'] = round((float) ($tarifStats['max_tarif'] ?? 0), 2);

            $docsQuery = $db->query("SELECT COUNT(*) as count FROM candidatures WHERE (cv_path IS NOT NULL AND cv_path <> '') OR (portfolio_path IS NOT NULL AND portfolio_path <> '')");
            $insights['with_documents'] = $docsQuery->fetch_assoc()['count'] ?? 0;

            $status = $this->getStats();
            $insights['by_status'] = [
                ['label' => 'En attente', 'value' => (int) ($status['en_attente'] ?? 0)],
                ['label' => 'Acceptee', 'value' => (int) ($status['acceptee'] ?? 0)],
                ['label' => 'Refusee', 'value' => (int) ($status['refusee'] ?? 0)]
            ];

            $byOfferSql = "SELECT COALESCE(o.titre, 'Offre supprimee') as titre_offre, COUNT(*) as total
                           FROM candidatures c
                           LEFT JOIN offres o ON c.id_offre = o.id_offre
                           GROUP BY c.id_offre, o.titre
                           ORDER BY total DESC
                           LIMIT 6";
            $byOfferQuery = $db->query($byOfferSql);
            $insights['by_offer'] = $byOfferQuery ? $byOfferQuery->fetch_all(MYSQLI_ASSOC) : [];
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }

        return $insights;
    }

    public function create($id_offre)
    {
        if (!$id_offre) {
            header("Location: index.php?page=offres");
            exit;
        }

        $db = getDB();
        $offreStmt = $db->prepare("SELECT * FROM offres WHERE id_offre = ?");
        if ($offreStmt === false) {
            echo 'Error: ' . $db->error;
            return;
        }
        $offreStmt->bind_param("i", $id_offre);
        $offreStmt->execute();
        $offre = $offreStmt->get_result()->fetch_assoc();

        if (!$offre) {
            header("Location: index.php?page=offres");
            exit;
        }

        $error = null;
        $id_freelancer = (int)($_GET['id_freelancer'] ?? 1);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom_freelancer'] ?? '');
            $email = trim($_POST['email_freelancer'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $tarifRaw = trim($_POST['tarif_propose'] ?? '');

            if ($nom === '' || $email === '' || $message === '') {
                $error = "Tous les champs obligatoires doivent etre remplis.";
            } elseif (mb_strlen($nom) < 3 || mb_strlen($nom) > 80) {
                $error = "Le nom doit contenir entre 3 et 80 caracteres.";
            } elseif (!preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/u", $nom)) {
                $error = "Le nom contient des caracteres non autorises.";
            } elseif (mb_strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strpos($email, '..') !== false) {
                $error = "Email invalide.";
            } elseif (mb_strlen($message) < 20 || mb_strlen($message) > 2000) {
                $error = "Le message doit contenir entre 20 et 2000 caracteres.";
            } elseif ($tarifRaw !== '' && !preg_match('/^\d+(\.\d{1,2})?$/', $tarifRaw)) {
                $error = "Le tarif doit etre un nombre valide avec au maximum 2 decimales.";
            } elseif ($tarifRaw !== '' && ((float) $tarifRaw < 0 || (float) $tarifRaw > 10000000)) {
                $error = "Le tarif propose est hors limite autorisee.";
            } else {
                $nom = preg_replace('/\s+/', ' ', $nom);
                $message = preg_replace('/\s+/', ' ', $message);
                $tarif = $tarifRaw === '' ? 0 : (float) $tarifRaw;
                $cvPath = null;
                $portfolioPath = null;

                if (!empty($_FILES['cv_file']['name'])) {
                    $cvUpload = $this->uploadDocument($_FILES['cv_file'], ['pdf', 'doc', 'docx'], 5 * 1024 * 1024);
                    if (!$cvUpload['ok']) {
                        $error = $cvUpload['error'];
                    } else {
                        $cvPath = $cvUpload['path'];
                    }
                }

                if (!$error && !empty($_FILES['portfolio_file']['name'])) {
                    $portfolioUpload = $this->uploadDocument($_FILES['portfolio_file'], ['pdf', 'doc', 'docx', 'zip'], 10 * 1024 * 1024);
                    if (!$portfolioUpload['ok']) {
                        $error = $portfolioUpload['error'];
                    } else {
                        $portfolioPath = $portfolioUpload['path'];
                    }
                }

                if (!$error) {
                    $sql = "INSERT INTO candidatures (id_offre, id_freelancer, nom_freelancer, email_freelancer, message, tarif_propose, cv_path, portfolio_path)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        $error = "Erreur SQL: " . $db->error;
                    } else {
                        $safeNom = htmlspecialchars($nom);
                        $safeEmail = htmlspecialchars($email);
                        $safeMessage = htmlspecialchars($message);
                        $stmt->bind_param("iisssdss", $id_offre, $id_freelancer, $safeNom, $safeEmail, $safeMessage, $tarif, $cvPath, $portfolioPath);
                        $stmt->execute();
                        header("Location: index.php?page=offre_detail&id={$id_offre}&success_candidature=1");
                        exit;
                    }
                }
            }
        }

        require_once(__DIR__ . '/../views/FrontOffice/candidature_form.php');
    }

    private function uploadDocument($file, $allowedExtensions, $maxSize)
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Erreur lors du telechargement du fichier.'];
        }
        if (($file['size'] ?? 0) > $maxSize) {
            return ['ok' => false, 'error' => 'Fichier trop volumineux.'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            return ['ok' => false, 'error' => 'Format de fichier non autorise.'];
        }

        $uploadDir = __DIR__ . '/../views/assets/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $safeName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $targetPath = $uploadDir . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['ok' => false, 'error' => 'Impossible de sauvegarder le fichier.'];
        }

        return ['ok' => true, 'path' => 'views/assets/uploads/' . $safeName];
    }

    public function adminIndex()
    {
        $filter  = $_GET['filter'] ?? 'all';
        $search  = $_GET['search'] ?? null;
        $sort    = $_GET['sort'] ?? 'recent';
        $perPage = 10;

        $totalItems  = $this->countAdminList($filter, $search);
        $totalPages  = (int) ceil($totalItems / $perPage);
        $currentPage = max(1, min((int)($_GET['p'] ?? 1), max(1, $totalPages)));

        $candidatures = $this->getAdminList($filter, $search, $sort, $perPage, $currentPage);
        $stats        = $this->getStats();
        $insights     = $this->getAdminInsights();

        require_once(__DIR__ . '/../views/BackOffice/candidatures.php');
    }

    public function adminUpdateStatut($id, $statut)
    {
        $statutsValides = ['en_attente', 'acceptee', 'refusee'];
        if (!in_array($statut, $statutsValides, true)) {
            header("Location: index.php?page=admin_candidatures&error=1");
            exit;
        }

        $this->updateStatut((int)$id, $statut);
        header("Location: index.php?page=admin_candidatures&success=1");
        exit;
    }

    public function clientIndex()
    {
        $id_client = (int)($_GET['id_client'] ?? 1);
        $filter    = $_GET['filter'] ?? 'all';
        $search    = $_GET['search'] ?? null;
        $perPage   = 8;
        $db        = getDB();

        // ── Total count for pagination ────────────────────────────────────
        $countSql    = "SELECT COUNT(*) as total
                        FROM candidatures c
                        INNER JOIN offres o ON c.id_offre = o.id_offre
                        WHERE o.id_client = ?";
        $countParams = [$id_client];
        $countTypes  = "i";

        if ($filter && $filter !== 'all') {
            $countSql    .= " AND c.statut = ?";
            $countParams[] = $filter;
            $countTypes  .= "s";
        }
        if (!empty($search)) {
            $countSql    .= " AND (c.nom_freelancer LIKE ? OR c.email_freelancer LIKE ? OR o.titre LIKE ?)";
            $like         = "%{$search}%";
            $countParams[] = $like;
            $countParams[] = $like;
            $countParams[] = $like;
            $countTypes  .= "sss";
        }

        $countStmt = $db->prepare($countSql);
        $totalItems = 0;
        if ($countStmt !== false) {
            $countStmt->bind_param($countTypes, ...$countParams);
            $countStmt->execute();
            $totalItems = (int)($countStmt->get_result()->fetch_assoc()['total'] ?? 0);
        }

        $totalPages  = (int)ceil($totalItems / $perPage);
        $currentPage = max(1, min((int)($_GET['p'] ?? 1), max(1, $totalPages)));
        $offset      = ($currentPage - 1) * $perPage;

        // ── Paginated list ────────────────────────────────────────────────
        $sql    = "SELECT c.*, o.titre as titre_offre
                   FROM candidatures c
                   INNER JOIN offres o ON c.id_offre = o.id_offre
                   WHERE o.id_client = ?";
        $params = [$id_client];
        $types  = "i";

        if ($filter && $filter !== 'all') {
            $sql    .= " AND c.statut = ?";
            $params[] = $filter;
            $types  .= "s";
        }
        if (!empty($search)) {
            $sql    .= " AND (c.nom_freelancer LIKE ? OR c.email_freelancer LIKE ? OR o.titre LIKE ?)";
            $like    = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $types  .= "sss";
        }

        $sql    .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        $types  .= "ii";

        $stmt         = $db->prepare($sql);
        $candidatures = [];
        if ($stmt !== false) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $candidatures = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        // ── Stats (all, not paginated) ────────────────────────────────────
        $statsSql  = "SELECT
                        COUNT(*) as total,
                        SUM(CASE WHEN c.statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                        SUM(CASE WHEN c.statut = 'acceptee'   THEN 1 ELSE 0 END) as acceptee,
                        SUM(CASE WHEN c.statut = 'refusee'    THEN 1 ELSE 0 END) as refusee
                      FROM candidatures c
                      INNER JOIN offres o ON c.id_offre = o.id_offre
                      WHERE o.id_client = ?";
        $statsStmt = $db->prepare($statsSql);
        $stats     = ['total' => 0, 'en_attente' => 0, 'acceptee' => 0, 'refusee' => 0];
        if ($statsStmt !== false) {
            $statsStmt->bind_param("i", $id_client);
            $statsStmt->execute();
            $stats = $statsStmt->get_result()->fetch_assoc() ?: $stats;
        }

        require_once(__DIR__ . '/../views/FrontOffice/candidatures_recues.php');
    }

    public function clientUpdateStatut($id, $statut)
    {
        $statutsValides = ['acceptee', 'refusee', 'en_attente'];
        if (!in_array($statut, $statutsValides, true)) {
            header("Location: index.php?page=client_candidatures&id_client=1&error=1");
            exit;
        }

        $id_client = (int)($_GET['id_client'] ?? 1);
        $db = getDB();
        $sql = "UPDATE candidatures c
                INNER JOIN offres o ON c.id_offre = o.id_offre
                SET c.statut = ?, c.updated_at = CURRENT_TIMESTAMP
                WHERE c.id_candidature = ? AND o.id_client = ?";
        $stmt = $db->prepare($sql);

        if ($stmt === false) {
            header("Location: index.php?page=client_candidatures&id_client={$id_client}&error=1");
            exit;
        }

        $id = (int)$id;
        $stmt->bind_param("sii", $statut, $id, $id_client);
        $stmt->execute();

        if ($stmt->affected_rows <= 0) {
            header("Location: index.php?page=client_candidatures&id_client={$id_client}&error=1");
            exit;
        }

        header("Location: index.php?page=client_candidatures&id_client={$id_client}&success=1");
        exit;
    }
}
