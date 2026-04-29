<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/CategorieController.php';
require_once __DIR__ . '/../Models/Service.php';

class ServiceController
{
    private function tableExists($tableName)
    {
        $stmt = Config::getConnexion()->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->execute([$tableName]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function getUploadDirectory()
    {
        return __DIR__ . '/../Views/assets/uploads';
    }

    private function ensureUploadDirectory()
    {
        $directory = $this->getUploadDirectory();
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        return $directory;
    }

    private function handleThumbnailUpload($fieldName, $currentThumbnail = null)
    {
        if (empty($_FILES[$fieldName]) || (int)($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $currentThumbnail;
        }

        if ((int)($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Erreur lors de l upload de la miniature.');
        }

        $tmpName = $_FILES[$fieldName]['tmp_name'];
        $originalName = $_FILES[$fieldName]['name'] ?? 'thumbnail';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new RuntimeException('La miniature doit etre au format JPG, PNG, WEBP ou GIF.');
        }

        $fileName = uniqid('thumb_', true) . '.' . $extension;
        $destination = $this->ensureUploadDirectory() . '/' . $fileName;

        if (!move_uploaded_file($tmpName, $destination)) {
            throw new RuntimeException('Impossible d enregistrer la miniature.');
        }

        return $fileName;
    }

    public function addService(Service $service)
    {
        $db = Config::getConnexion();
        $insert = $db->prepare("INSERT INTO services (titre, description, prix, delai_livraison, statut, thumbnail, id_categorie, id_freelancer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $insert->execute([
            $service->getTitre(),
            $service->getDescription(),
            $service->getPrix(),
            $service->getDelaiLivraison(),
            $service->getStatut(),
            $service->getThumbnail(),
            $service->getCategorieId(),
            $service->getFreelancerId()
        ]);
    }

    public function getServiceById($id)
    {
        if (!$this->tableExists('services') || !$this->tableExists('categorie') || !$this->tableExists('User')) {
            return null;
        }

        $stmt = Config::getConnexion()->prepare("SELECT s.*, c.nom_categorie, u.nom, u.prenom
            FROM services s
            JOIN categorie c ON c.id_categorie = s.id_categorie
            JOIN User u ON u.id = s.id_freelancer
            WHERE s.id_service = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $service = new Service(
                $result['titre'],
                $result['description'],
                $result['prix'],
                $result['delai_livraison'],
                $result['id_categorie'],
                $result['id_freelancer'],
                $result['statut'],
                $result['thumbnail'] ?? null
            );
            $service->setId($result['id_service']);

            return [
                'entity' => $service,
                'data' => $result
            ];
        }

        return null;
    }

    public function updateService(Service $service)
    {
        $db = Config::getConnexion();
        $update = $db->prepare("UPDATE services SET titre = ?, description = ?, prix = ?, delai_livraison = ?, statut = ?, thumbnail = ?, id_categorie = ?, id_freelancer = ? WHERE id_service = ?");
        return $update->execute([
            $service->getTitre(),
            $service->getDescription(),
            $service->getPrix(),
            $service->getDelaiLivraison(),
            $service->getStatut(),
            $service->getThumbnail(),
            $service->getCategorieId(),
            $service->getFreelancerId(),
            $service->getId()
        ]);
    }

    public function publicList()
    {
        if (!$this->tableExists('services') || !$this->tableExists('categorie') || !$this->tableExists('User')) {
            $services = [];
            $categories = [];
            require __DIR__ . '/../Views/Frontoffice/services.php';
            return;
        }

        $db = Config::getConnexion();
        $search = trim($_GET['search'] ?? '');
        $cat = (int)($_GET['categorie'] ?? 0);

        $sql = "SELECT s.*, c.nom_categorie, u.nom, u.prenom
                FROM services s
                JOIN categorie c ON c.id_categorie = s.id_categorie
                JOIN User u ON u.id = s.id_freelancer
                WHERE s.statut = 'actif'";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (s.titre LIKE ? OR s.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($cat > 0) {
            $sql .= " AND s.id_categorie = ?";
            $params[] = $cat;
        }

        $sql .= " ORDER BY s.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $categories = (new CategorieController())->listCategories();
        require __DIR__ . '/../Views/Frontoffice/services.php';
    }

    public function detail($id)
    {
        $result = $this->getServiceById($id);
        $service = $result['data'] ?? null;
        if (!$service) {
            header('Location: ?action=services');
            exit;
        }
        require __DIR__ . '/../Views/Frontoffice/service_detail.php';
    }

    public function freelancerList()
    {
        $this->requireRole(3);

        if (!$this->tableExists('services') || !$this->tableExists('categorie')) {
            $services = [];
            require __DIR__ . '/../Views/Frontoffice/my_services.php';
            return;
        }

        $stmt = Config::getConnexion()->prepare("SELECT s.*, c.nom_categorie
            FROM services s JOIN categorie c ON c.id_categorie = s.id_categorie
            WHERE s.id_freelancer = ? ORDER BY s.created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../Views/Frontoffice/my_services.php';
    }

    public function save($id = null)
    {
        $this->requireRole(3);
        $db = Config::getConnexion();
        $categories = (new CategorieController())->listCategories();
        $service = null;
        $error = null;

        if ($id) {
            $stmt = $db->prepare("SELECT * FROM services WHERE id_service = ? AND id_freelancer = ?");
            $stmt->execute([$id, $_SESSION['user_id']]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$service) {
                header('Location: ?action=my_services');
                exit;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $prix = (float)($_POST['prix'] ?? 0);
            $delai = (int)($_POST['delai_livraison'] ?? 1);
            $cat = (int)($_POST['id_categorie'] ?? 0);

            if ($titre === '' || $description === '' || $prix <= 0 || $delai <= 0 || $cat <= 0) {
                $error = 'Veuillez remplir correctement tous les champs obligatoires.';
                $service = [
                    'id_service' => $id,
                    'titre' => $titre,
                    'description' => $description,
                    'prix' => $prix,
                    'delai_livraison' => $delai,
                    'id_categorie' => $cat,
                    'statut' => $service['statut'] ?? 'en_attente',
                    'thumbnail' => $service['thumbnail'] ?? null,
                ];
            } else {
                try {
                    $thumbnail = $this->handleThumbnailUpload('thumbnail', $service['thumbnail'] ?? null);
                } catch (RuntimeException $exception) {
                    $error = $exception->getMessage();
                    $service = [
                        'id_service' => $id,
                        'titre' => $titre,
                        'description' => $description,
                        'prix' => $prix,
                        'delai_livraison' => $delai,
                        'id_categorie' => $cat,
                        'statut' => $service['statut'] ?? 'en_attente',
                        'thumbnail' => $service['thumbnail'] ?? null,
                    ];
                    require __DIR__ . '/../Views/Frontoffice/service_form.php';
                    return;
                }

                if ($id) {
                    $serviceEntity = new Service($titre, $description, $prix, $delai, $cat, $_SESSION['user_id'], 'en_attente', $thumbnail);
                    $serviceEntity->setId($id);
                    $this->updateService($serviceEntity);
                    header('Location: ?action=my_services&success=2');
                } else {
                    $serviceEntity = new Service($titre, $description, $prix, $delai, $cat, $_SESSION['user_id'], 'en_attente', $thumbnail);
                    $this->addService($serviceEntity);
                    header('Location: ?action=my_services&success=1');
                }
                exit;
            }
        }

        require __DIR__ . '/../Views/Frontoffice/service_form.php';
    }

    public function delete($id)
    {
        $this->requireRole(3);
        $stmt = Config::getConnexion()->prepare("DELETE FROM services WHERE id_service = ? AND id_freelancer = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        header('Location: ?action=my_services');
        exit;
    }

    public function adminList()
    {
        $this->requireRole(1);
        $db = Config::getConnexion();

        if (!$this->tableExists('services') || !$this->tableExists('categorie') || !$this->tableExists('User')) {
            $services = [];
            $categories = [];

            if ($this->tableExists('categorie')) {
                $categories = $db->query("SELECT c.*, 0 AS nb_services FROM categorie c ORDER BY c.nom_categorie ASC")
                    ->fetchAll(PDO::FETCH_ASSOC);
            }

            $serviceStats = [
                'total' => 0,
                'actifs' => 0,
                'attente' => 0,
                'suspendus' => 0,
                'prix_moyen' => 0,
                'avec_thumbnail' => 0,
                'categorie_top' => 'Aucune'
            ];
            $serviceInsights = [
                'top_freelancers' => [],
                'freelancer_stats' => [],
                'category_rows' => []
            ];

            require __DIR__ . '/../Views/Backoffice/serviceList.php';
            return;
        }

        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? 'all');
        $category = (int)($_GET['category'] ?? 0);
        $sort = trim($_GET['sort'] ?? 'recent');

        $sql = "SELECT s.*, c.nom_categorie, u.nom, u.prenom
            FROM services s
            JOIN categorie c ON c.id_categorie = s.id_categorie
            JOIN User u ON u.id = s.id_freelancer
            WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (s.titre LIKE :search OR s.description LIKE :search OR c.nom_categorie LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if (in_array($status, ['actif', 'en_attente', 'suspendu'], true)) {
            $sql .= " AND s.statut = :status";
            $params[':status'] = $status;
        }

        if ($category > 0) {
            $sql .= " AND s.id_categorie = :category";
            $params[':category'] = $category;
        }

        $orderBy = [
            'recent' => 's.created_at DESC',
            'oldest' => 's.created_at ASC',
            'price_asc' => 's.prix ASC',
            'price_desc' => 's.prix DESC',
            'title_asc' => 's.titre ASC',
            'title_desc' => 's.titre DESC'
        ];
        $sql .= ' ORDER BY ' . ($orderBy[$sort] ?? $orderBy['recent']);

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $categories = (new CategorieController())->listCategories();

        $totalPrice = array_sum(array_map(fn($service) => (float)$service['prix'], $services));
        $thumbnailCount = count(array_filter($services, fn($service) => !empty($service['thumbnail'])));
        $categoryCounts = [];
        foreach ($services as $service) {
            $name = $service['nom_categorie'] ?? 'Sans categorie';
            $categoryCounts[$name] = ($categoryCounts[$name] ?? 0) + 1;
        }
        arsort($categoryCounts);
        $topCategory = array_key_first($categoryCounts);
        $topFreelancers = [];
        $freelancerStats = [];
        foreach ($services as $service) {
            $name = trim(($service['prenom'] ?? '') . ' ' . ($service['nom'] ?? ''));
            $topFreelancers[$name] = ($topFreelancers[$name] ?? 0) + 1;
            if (!isset($freelancerStats[$name])) {
                $freelancerStats[$name] = [
                    'count' => 0,
                    'active' => 0,
                    'total_price' => 0.0
                ];
            }
            $freelancerStats[$name]['count']++;
            $freelancerStats[$name]['total_price'] += (float) ($service['prix'] ?? 0);
            if (($service['statut'] ?? '') === 'actif') {
                $freelancerStats[$name]['active']++;
            }
        }
        arsort($topFreelancers);
        $topFreelancers = array_slice($topFreelancers, 0, 3, true);
        uasort($freelancerStats, fn($a, $b) => $b['count'] <=> $a['count']);
        $freelancerStats = array_slice($freelancerStats, 0, 5, true);
        $categoryRows = [];
        foreach ($categoryCounts as $categoryName => $count) {
            $categoryRows[] = [
                'name' => $categoryName,
                'count' => $count
            ];
        }
        $categoryRows = array_slice($categoryRows, 0, 5);

        $serviceStats = [
            'total' => count($services),
            'actifs' => count(array_filter($services, fn($service) => $service['statut'] === 'actif')),
            'attente' => count(array_filter($services, fn($service) => $service['statut'] === 'en_attente')),
            'suspendus' => count(array_filter($services, fn($service) => $service['statut'] === 'suspendu')),
            'prix_moyen' => count($services) > 0 ? $totalPrice / count($services) : 0,
            'avec_thumbnail' => $thumbnailCount,
            'categorie_top' => $topCategory ?: 'Aucune'
        ];
        $serviceInsights = [
            'top_freelancers' => $topFreelancers,
            'freelancer_stats' => $freelancerStats,
            'category_rows' => $categoryRows
        ];
        require __DIR__ . '/../Views/Backoffice/serviceList.php';
    }

    public function adminCreate()
    {
        $this->requireRole(1);
        header('Location: ?action=services_admin&error=admin_service_form_disabled');
        exit;
    }

    public function adminEdit($id)
    {
        $this->requireRole(1);
        header('Location: ?action=services_admin&error=admin_service_form_disabled');
        exit;
    }

    public function adminDelete($id)
    {
        $this->requireRole(1);
        $stmt = Config::getConnexion()->prepare("DELETE FROM services WHERE id_service = ?");
        $stmt->execute([$id]);
        header('Location: ?action=services_admin&success=3');
        exit;
    }

    public function updateStatus($id, $status)
    {
        $this->requireRole(1);
        if (in_array($status, ['actif', 'en_attente', 'suspendu'], true)) {
            $stmt = Config::getConnexion()->prepare("UPDATE services SET statut = ? WHERE id_service = ?");
            $stmt->execute([$status, $id]);
        }
        header('Location: ?action=services_admin');
        exit;
    }

    private function getFreelancers()
    {
        $stmt = Config::getConnexion()->query("SELECT id, nom, prenom FROM User WHERE id_role = 3 AND is_approved = 1 AND is_banned = 0 ORDER BY nom ASC, prenom ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function requireRole($role)
    {
        if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== (int)$role) {
            header('Location: ?action=login');
            exit;
        }
    }
}
?>
