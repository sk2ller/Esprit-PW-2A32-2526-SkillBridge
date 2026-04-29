<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../models/Service.php');
require_once(__DIR__ . '/../models/Categorie.php');
require_once(__DIR__ . '/CategorieController.php');

class ServiceController
{
    public function __construct()
    {
        $this->ensureServiceSchema();
    }

    private function ensureServiceSchema()
    {
        $db = getDB();
        $columnCheck = $db->query("SHOW COLUMNS FROM services LIKE 'freelancer_name'");
        if ($columnCheck && $columnCheck->num_rows === 0) {
            $db->query("ALTER TABLE services ADD freelancer_name VARCHAR(120) NOT NULL DEFAULT 'Freelancer Demo' AFTER id_categorie");
        }
    }

    private function getCurrentFreelancerName()
    {
        $name = trim($_SESSION['profile_name'] ?? '');
        if ($name !== '') {
            return mb_substr($name, 0, 120);
        }

        return 'Freelancer Demo';
    }

    private function isAllowedExtension($fileName, $allowedExtensions)
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        return in_array($extension, $allowedExtensions, true);
    }

    private function uploadFile($file, $prefix, $allowedExtensions)
    {
        if (empty($file['name'])) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if (!$this->isAllowedExtension($file['name'], $allowedExtensions)) {
            return false;
        }

        $uploadDir = __DIR__ . '/../views/assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . $prefix . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
        $destination = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $fileName;
        }

        return false;
    }

    private function validateService()
    {
        $errors = [];

        if (empty($_POST['titre'] ?? '')) {
            $errors['titre'] = 'Le titre du service est obligatoire.';
        } elseif (strlen($_POST['titre']) < 5) {
            $errors['titre'] = 'Le titre doit contenir au moins 5 caracteres.';
        } elseif (strlen($_POST['titre']) > 200) {
            $errors['titre'] = 'Le titre ne doit pas depasser 200 caracteres.';
        }

        if (empty($_POST['id_categorie'] ?? '')) {
            $errors['id_categorie'] = 'Veuillez selectionner une categorie.';
        }

        if (empty($_POST['description'] ?? '')) {
            $errors['description'] = 'La description est obligatoire.';
        } elseif (strlen($_POST['description']) < 20) {
            $errors['description'] = 'La description doit contenir au moins 20 caracteres.';
        }

        if (empty($_POST['prix'] ?? '')) {
            $errors['prix'] = 'Le prix est obligatoire.';
        } elseif (!is_numeric($_POST['prix']) || $_POST['prix'] <= 0) {
            $errors['prix'] = 'Le prix doit etre un nombre positif.';
        }

        if (empty($_POST['delai_livraison'] ?? '')) {
            $errors['delai_livraison'] = 'Le delai de livraison est obligatoire.';
        } elseif (!is_numeric($_POST['delai_livraison']) || $_POST['delai_livraison'] <= 0) {
            $errors['delai_livraison'] = 'Le delai doit etre un nombre positif (en jours).';
        }

        if (!empty($_FILES['thumbnail']['name'] ?? '')) {
            if (!$this->isAllowedExtension($_FILES['thumbnail']['name'], ['jpg', 'jpeg', 'png', 'webp'])) {
                $errors['thumbnail'] = 'Le fichier miniature doit etre en format JPG, JPEG, PNG ou WEBP.';
            }
        }

        return $errors;
    }

    public function addService(Service $service)
    {
        $sql = "INSERT INTO services (titre, description, prix, delai_livraison, statut, id_categorie, freelancer_name, thumbnail)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $db = getDB();

        try {
            $query = $db->prepare($sql);

            if ($query === false) {
                echo 'Prepare Error: ' . $db->error;
                return false;
            }

            $titre = $service->getTitre();
            $description = $service->getDescription();
            $prix = $service->getPrix();
            $delai = $service->getDelaiLivraison();
            $statut = $service->getStatut();
            $idCategorie = $service->getIdCategorie();
            $freelancerName = $service->getFreelancerName();
            $thumbnail = $service->getThumbnail();

            $query->bind_param('ssdisiss', $titre, $description, $prix, $delai, $statut, $idCategorie, $freelancerName, $thumbnail);

            if (!$query->execute()) {
                echo 'Execute Error: ' . $query->error;
                return false;
            }

            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function listAll($statut = null, $idCategorie = null, $search = null, $sort = null)
    {
        $sql = "SELECT s.*, COALESCE(NULLIF(s.thumbnail, ''), NULLIF(s.image, '')) AS thumbnail, c.nom_categorie
                FROM services s
                JOIN categorie c ON s.id_categorie = c.id_categorie
                WHERE 1=1";

        $params = [];
        $types = '';

        if ($statut !== null && $statut !== '') {
            $sql .= ' AND s.statut = ?';
            $params[] = $statut;
            $types .= 's';
        }

        if ($idCategorie !== null && $idCategorie !== '') {
            $sql .= ' AND s.id_categorie = ?';
            $params[] = (int) $idCategorie;
            $types .= 'i';
        }

        if ($search !== null && $search !== '') {
            $sql .= ' AND (s.titre LIKE ? OR s.description LIKE ?)';
            $searchValue = '%' . $search . '%';
            $params[] = $searchValue;
            $params[] = $searchValue;
            $types .= 'ss';
        }

        if ($sort === 'prix_asc') {
            $sql .= ' ORDER BY s.prix ASC';
        } elseif ($sort === 'prix_desc') {
            $sql .= ' ORDER BY s.prix DESC';
        } elseif ($sort === 'statut_asc') {
            $sql .= ' ORDER BY s.statut ASC';
        } elseif ($sort === 'statut_desc') {
            $sql .= ' ORDER BY s.statut DESC';
        } elseif ($sort === 'recent') {
            $sql .= ' ORDER BY s.created_at DESC';
        } elseif ($sort === 'ancien') {
            $sql .= ' ORDER BY s.created_at ASC';
        } else {
            $sql .= ' ORDER BY s.created_at DESC';
        }

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

    public function getServiceById($id)
    {
        $sql = "SELECT s.*, COALESCE(NULLIF(s.thumbnail, ''), NULLIF(s.image, '')) AS thumbnail, c.nom_categorie
                FROM services s
                JOIN categorie c ON s.id_categorie = c.id_categorie
                WHERE s.id_service = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->bind_param('i', $id);
            $query->execute();

            $result = $query->get_result()->fetch_assoc();
            if ($result) {
                $service = new Service(
                    $result['titre'],
                    $result['description'],
                    $result['prix'],
                    $result['delai_livraison'],
                    $result['id_categorie'],
                    $result['statut'],
                    $result['freelancer_name'] ?? 'Freelancer Demo',
                    null,
                    null,
                    $result['thumbnail'] ?? null
                );

                $service->setId($result['id_service']);
                $service->setNomCategorie($result['nom_categorie']);
                $service->setCreatedAt($result['created_at']);

                return $service;
            }

            return null;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return null;
        }
    }

    public function listByFreelancer($idFreelance = null)
    {
        $sql = "SELECT s.*, COALESCE(NULLIF(s.thumbnail, ''), NULLIF(s.image, '')) AS thumbnail, c.nom_categorie
                FROM services s
                JOIN categorie c ON s.id_categorie = c.id_categorie
                ORDER BY s.created_at DESC";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function updateService(Service $service)
    {
        $sql = "UPDATE services
                SET titre = ?, description = ?, prix = ?, delai_livraison = ?, statut = ?, id_categorie = ?, freelancer_name = ?, thumbnail = ?
                WHERE id_service = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $id = $service->getId();
            $titre = $service->getTitre();
            $description = $service->getDescription();
            $prix = $service->getPrix();
            $delai = $service->getDelaiLivraison();
            $statut = $service->getStatut();
            $idCategorie = $service->getIdCategorie();
            $freelancerName = $service->getFreelancerName();
            $thumbnail = $service->getThumbnail();

            $query->bind_param('ssdisissi', $titre, $description, $prix, $delai, $statut, $idCategorie, $freelancerName, $thumbnail, $id);
            return $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function updateStatut($id, $statut)
    {
        $sql = "UPDATE services SET statut = ? WHERE id_service = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->bind_param('si', $statut, $id);
            return $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function deleteService($id)
    {
        $sql = "DELETE FROM services WHERE id_service = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->bind_param('i', $id);
            return $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function getStats()
    {
        $sql = "SELECT statut, COUNT(*) as count FROM services GROUP BY statut";
        $db = getDB();
        $stats = [
            'en_attente' => 0,
            'actif' => 0,
            'rejete' => 0,
            'rejetee' => 0,
            'confirmee' => 0,
            'suspendu' => 0,
            'total' => 0
        ];

        try {
            $query = $db->prepare($sql);
            $query->execute();
            $result = $query->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($result as $row) {
                $stats[$row['statut']] = $row['count'];
            }

            foreach ($stats as $key => $value) {
                if ($key !== 'total') {
                    $stats['total'] += $value;
                }
            }

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
            'average_price' => 0,
            'average_delay' => 0,
            'with_thumbnail' => 0,
            'without_thumbnail' => 0,
            'top_category' => 'Aucune',
            'top_category_count' => 0,
            'top_freelancer' => 'Aucun',
            'top_freelancer_count' => 0
        ];

        try {
            $summarySql = "SELECT 
                              AVG(prix) AS average_price,
                              AVG(delai_livraison) AS average_delay,
                              SUM(CASE WHEN COALESCE(NULLIF(thumbnail, ''), NULLIF(image, '')) IS NOT NULL THEN 1 ELSE 0 END) AS with_thumbnail,
                              SUM(CASE WHEN COALESCE(NULLIF(thumbnail, ''), NULLIF(image, '')) IS NULL THEN 1 ELSE 0 END) AS without_thumbnail
                           FROM services";
            $summaryQuery = $db->prepare($summarySql);
            $summaryQuery->execute();
            $summary = $summaryQuery->get_result()->fetch_assoc();

            if ($summary) {
                $insights['average_price'] = (float) ($summary['average_price'] ?? 0);
                $insights['average_delay'] = (float) ($summary['average_delay'] ?? 0);
                $insights['with_thumbnail'] = (int) ($summary['with_thumbnail'] ?? 0);
                $insights['without_thumbnail'] = (int) ($summary['without_thumbnail'] ?? 0);
            }

            $categorySql = "SELECT c.nom_categorie, COUNT(*) AS total
                            FROM services s
                            JOIN categorie c ON s.id_categorie = c.id_categorie
                            GROUP BY s.id_categorie, c.nom_categorie
                            ORDER BY total DESC, c.nom_categorie ASC
                            LIMIT 1";
            $categoryQuery = $db->prepare($categorySql);
            $categoryQuery->execute();
            $topCategory = $categoryQuery->get_result()->fetch_assoc();

            if ($topCategory) {
                $insights['top_category'] = $topCategory['nom_categorie'];
                $insights['top_category_count'] = (int) $topCategory['total'];
            }

            $freelancerSql = "SELECT freelancer_name, COUNT(*) AS total
                              FROM services
                              GROUP BY freelancer_name
                              ORDER BY total DESC, freelancer_name ASC
                              LIMIT 1";
            $freelancerQuery = $db->prepare($freelancerSql);
            $freelancerQuery->execute();
            $topFreelancer = $freelancerQuery->get_result()->fetch_assoc();

            if ($topFreelancer) {
                $insights['top_freelancer'] = $topFreelancer['freelancer_name'];
                $insights['top_freelancer_count'] = (int) $topFreelancer['total'];
            }

            return $insights;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return $insights;
        }
    }

    public function getCategoryPerformance()
    {
        $sql = "SELECT c.nom_categorie,
                       COUNT(s.id_service) AS total_services,
                       AVG(s.prix) AS average_price,
                       SUM(CASE WHEN s.statut = 'actif' THEN 1 ELSE 0 END) AS actifs,
                       SUM(CASE WHEN s.statut = 'en_attente' THEN 1 ELSE 0 END) AS en_attente
                FROM categorie c
                LEFT JOIN services s ON s.id_categorie = c.id_categorie
                GROUP BY c.id_categorie, c.nom_categorie
                ORDER BY total_services DESC, c.nom_categorie ASC";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function index()
    {
        $search = $_GET['search'] ?? null;
        $idCategorie = $_GET['categorie'] ?? null;

        $services = $this->listAll('actif', $idCategorie, $search);

        $categorieController = new CategorieController();
        $categories = $categorieController->listCategories();

        require_once(__DIR__ . '/../views/FrontOffice/services_list.php');
    }

    public function show($id)
    {
        $serviceObject = $this->getServiceById($id);

        if (!$serviceObject) {
            header("Location: index.php?page=services");
            exit;
        }

        $service = [
            'id_service' => $serviceObject->getId(),
            'titre' => $serviceObject->getTitre(),
            'description' => $serviceObject->getDescription(),
            'prix' => $serviceObject->getPrix(),
            'delai_livraison' => $serviceObject->getDelaiLivraison(),
            'statut' => $serviceObject->getStatut(),
            'id_categorie' => $serviceObject->getIdCategorie(),
            'freelancer_name' => $serviceObject->getFreelancerName(),
            'nom_categorie' => $serviceObject->getNomCategorie(),
            'created_at' => $serviceObject->getCreatedAt(),
            'thumbnail' => $serviceObject->getThumbnail()
        ];

        require_once(__DIR__ . '/../views/FrontOffice/service_detail.php');
    }

    public function myServices()
    {
        $freelancerId = $_GET['freelancer_id'] ?? 1;
        $services = $this->listByFreelancer($freelancerId);

        $categorieController = new CategorieController();
        $categories = $categorieController->listCategories();

        require_once(__DIR__ . '/../views/FrontOffice/my_services.php');
    }

    public function create()
    {
        $categorieController = new CategorieController();
        $categories = $categorieController->listCategories();
        $errors = [];
        $service = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateService();

            if (empty($errors)) {
                $thumbnail = $this->uploadFile($_FILES['thumbnail'] ?? [], 'thumb', ['jpg', 'jpeg', 'png', 'webp']);

                $service = new Service(
                    htmlspecialchars($_POST['titre']),
                    htmlspecialchars($_POST['description']),
                    (float) $_POST['prix'],
                    (int) $_POST['delai_livraison'],
                    (int) $_POST['id_categorie'],
                    'en_attente',
                    $this->getCurrentFreelancerName(),
                    null,
                    null,
                    $thumbnail
                );

                $this->addService($service);
                header("Location: index.php?page=my_services&success=1");
                exit;
            }

            $service = [
                'titre' => htmlspecialchars($_POST['titre'] ?? ''),
                'description' => htmlspecialchars($_POST['description'] ?? ''),
                'prix' => $_POST['prix'] ?? '',
                'delai_livraison' => $_POST['delai_livraison'] ?? '',
                'id_categorie' => $_POST['id_categorie'] ?? ''
            ];
        }

        require_once(__DIR__ . '/../views/FrontOffice/service_form.php');
    }

    public function edit($id)
    {
        $serviceObject = $this->getServiceById($id);
        $errors = [];

        if (!$serviceObject) {
            header("Location: index.php?page=my_services");
            exit;
        }

        $service = [
            'id_service' => $serviceObject->getId(),
            'titre' => $serviceObject->getTitre(),
            'description' => $serviceObject->getDescription(),
            'prix' => $serviceObject->getPrix(),
            'delai_livraison' => $serviceObject->getDelaiLivraison(),
            'statut' => $serviceObject->getStatut(),
            'id_categorie' => $serviceObject->getIdCategorie(),
            'freelancer_name' => $serviceObject->getFreelancerName(),
            'nom_categorie' => $serviceObject->getNomCategorie(),
            'thumbnail' => $serviceObject->getThumbnail()
        ];

        $categorieController = new CategorieController();
        $categories = $categorieController->listCategories();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateService();

            if (empty($errors)) {
                $thumbnail = $this->uploadFile($_FILES['thumbnail'] ?? [], 'thumb', ['jpg', 'jpeg', 'png', 'webp']);
                if ($thumbnail === false) {
                    $errors['thumbnail'] = 'Le fichier miniature doit etre en format JPG, JPEG, PNG ou WEBP.';
                } elseif ($thumbnail === null) {
                    $thumbnail = $serviceObject->getThumbnail();
                }

                if (empty($errors)) {
                    $updatedService = new Service(
                        htmlspecialchars($_POST['titre']),
                        htmlspecialchars($_POST['description']),
                        (float) $_POST['prix'],
                        (int) $_POST['delai_livraison'],
                        (int) $_POST['id_categorie'],
                        $serviceObject->getStatut(),
                        $serviceObject->getFreelancerName(),
                        null,
                        null,
                        $thumbnail
                    );

                    $updatedService->setId($id);
                    $this->updateService($updatedService);

                    header("Location: index.php?page=my_services&success=2");
                    exit;
                }
            }

            $service = [
                'id_service' => $id,
                'titre' => htmlspecialchars($_POST['titre'] ?? ''),
                'description' => htmlspecialchars($_POST['description'] ?? ''),
                'prix' => $_POST['prix'] ?? '',
                'delai_livraison' => $_POST['delai_livraison'] ?? '',
                'id_categorie' => $_POST['id_categorie'] ?? '',
                'freelancer_name' => $serviceObject->getFreelancerName(),
                'nom_categorie' => $serviceObject->getNomCategorie(),
                'thumbnail' => $serviceObject->getThumbnail()
            ];
        }

        require_once(__DIR__ . '/../views/FrontOffice/service_form.php');
    }

    public function delete($id)
    {
        $this->deleteService($id);
        header("Location: index.php?page=my_services&success=3");
        exit;
    }

    public function adminIndex()
    {
        $statut = $_GET['statut'] ?? null;
        $sort = $_GET['sort'] ?? 'recent';
        $search = $_GET['search'] ?? null;

        $services = $this->listAll($statut, null, $search, $sort);
        $stats = $this->getStats();

        require_once(__DIR__ . '/../views/BackOffice/services.php');
    }

    public function adminUpdateStatut($id, $statut)
    {
        $this->updateStatut($id, $statut);
        header("Location: index.php?page=admin_services&success=1");
        exit;
    }
}
