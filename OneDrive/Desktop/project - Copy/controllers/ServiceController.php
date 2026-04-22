<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../models/Service.php');
require_once(__DIR__ . '/../models/Categorie.php');
require_once(__DIR__ . '/CategorieController.php');

class ServiceController
{
    private function uploadFile($file, $prefix, $allowedExtensions)
    {
        if (empty($file['name'])) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
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

    public function addService(Service $service)
    {
        $sql = "INSERT INTO services (titre, description, prix, delai_livraison, statut, id_categorie, cv, portfolio, thumbnail)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $titre = $service->getTitre();
            $description = $service->getDescription();
            $prix = $service->getPrix();
            $delai = $service->getDelaiLivraison();
            $statut = $service->getStatut();
            $idCategorie = $service->getIdCategorie();
            $cv = $service->getCv();
            $portfolio = $service->getPortfolio();
            $thumbnail = $service->getThumbnail();

            $query->bind_param("ssdisisss", $titre, $description, $prix, $delai, $statut, $idCategorie, $cv, $portfolio, $thumbnail);
            return $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

public function listAll($statut = null, $idCategorie = null, $search = null, $sort = null)
{
    $sql = "SELECT s.*, c.nom_categorie
            FROM services s
            JOIN categorie c ON s.id_categorie = c.id_categorie
            WHERE 1=1";

    $params = [];
    $types = "";

    if ($statut !== null && $statut !== '') {
        $sql .= " AND s.statut = ?";
        $params[] = $statut;
        $types .= "s";
    }

    if ($idCategorie !== null && $idCategorie !== '') {
        $sql .= " AND s.id_categorie = ?";
        $params[] = (int) $idCategorie;
        $types .= "i";
    }

    if ($search !== null && $search !== '') {
        $sql .= " AND (s.titre LIKE ? OR s.description LIKE ?)";
        $searchValue = "%" . $search . "%";
        $params[] = $searchValue;
        $params[] = $searchValue;
        $types .= "ss";
    }

    if ($sort === 'prix_asc') {
        $sql .= " ORDER BY s.prix ASC";
    } elseif ($sort === 'prix_desc') {
        $sql .= " ORDER BY s.prix DESC";
    } elseif ($sort === 'statut_asc') {
        $sql .= " ORDER BY s.statut ASC";
    } elseif ($sort === 'statut_desc') {
        $sql .= " ORDER BY s.statut DESC";
    } elseif ($sort === 'recent') {
        $sql .= " ORDER BY s.created_at DESC";
    } elseif ($sort === 'ancien') {
        $sql .= " ORDER BY s.created_at ASC";
    } else {
        $sql .= " ORDER BY s.created_at DESC";
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
        $sql = "SELECT s.*, c.nom_categorie
                FROM services s
                JOIN categorie c ON s.id_categorie = c.id_categorie
                WHERE s.id_service = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->bind_param("i", $id);
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
                    $result['cv'] ?? null,
                    $result['portfolio'] ?? null,
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
        $sql = "SELECT s.*, c.nom_categorie
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
                SET titre = ?, description = ?, prix = ?, delai_livraison = ?, statut = ?, id_categorie = ?, cv = ?, portfolio = ?, thumbnail = ?
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
            $cv = $service->getCv();
            $portfolio = $service->getPortfolio();
            $thumbnail = $service->getThumbnail();

            $query->bind_param("ssdisisssi", $titre, $description, $prix, $delai, $statut, $idCategorie, $cv, $portfolio, $thumbnail, $id);
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
            $query->bind_param("si", $statut, $id);
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
            $query->bind_param("i", $id);
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
            'total' => 0
        ];

        try {
            $query = $db->prepare($sql);
            $query->execute();
            $result = $query->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($result as $row) {
                $stats[$row['statut']] = $row['count'];
            }

            $stats['total'] = 0;
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
            'nom_categorie' => $serviceObject->getNomCategorie(),
            'created_at' => $serviceObject->getCreatedAt(),
            'cv' => $serviceObject->getCv(),
            'portfolio' => $serviceObject->getPortfolio(),
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
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['titre']) || empty($_POST['description']) || empty($_POST['prix'])) {
                $error = "Tous les champs sont requis.";
            } else {
                $cv = $this->uploadFile($_FILES['cv'] ?? [], 'cv', ['pdf', 'doc', 'docx']);
                if ($cv === false) {
                    $error = "Le fichier CV est invalide.";
                }

                $portfolio = $this->uploadFile($_FILES['portfolio'] ?? [], 'portfolio', ['pdf', 'zip', 'jpg', 'jpeg', 'png']);
                if ($portfolio === false) {
                    $error = "Le fichier portfolio est invalide.";
                }

                $thumbnail = $this->uploadFile($_FILES['thumbnail'] ?? [], 'thumb', ['jpg', 'jpeg', 'png', 'webp']);
                if ($thumbnail === false) {
                    $error = "Le fichier thumbnail est invalide.";
                }

                if (!$error) {
                    $service = new Service(
                        htmlspecialchars($_POST['titre']),
                        htmlspecialchars($_POST['description']),
                        (float) $_POST['prix'],
                        (int) $_POST['delai_livraison'],
                        (int) $_POST['id_categorie'],
                        'en_attente',
                        $cv,
                        $portfolio,
                        $thumbnail
                    );

                    $this->addService($service);
                    header("Location: index.php?page=my_services&success=1");
                    exit;
                }
            }
        }

        require_once(__DIR__ . '/../views/FrontOffice/service_form.php');
    }

    public function edit($id)
    {
        $serviceObject = $this->getServiceById($id);
        $error = null;

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
            'nom_categorie' => $serviceObject->getNomCategorie(),
            'created_at' => $serviceObject->getCreatedAt(),
            'cv' => $serviceObject->getCv(),
            'portfolio' => $serviceObject->getPortfolio(),
            'thumbnail' => $serviceObject->getThumbnail()
        ];

        $categorieController = new CategorieController();
        $categories = $categorieController->listCategories();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cv = $this->uploadFile($_FILES['cv'] ?? [], 'cv', ['pdf', 'doc', 'docx']);
            if ($cv === false) {
                $error = "Le fichier CV est invalide.";
            } elseif ($cv === null) {
                $cv = $serviceObject->getCv();
            }

            $portfolio = $this->uploadFile($_FILES['portfolio'] ?? [], 'portfolio', ['pdf', 'zip', 'jpg', 'jpeg', 'png']);
            if ($portfolio === false) {
                $error = "Le fichier portfolio est invalide.";
            } elseif ($portfolio === null) {
                $portfolio = $serviceObject->getPortfolio();
            }

            $thumbnail = $this->uploadFile($_FILES['thumbnail'] ?? [], 'thumb', ['jpg', 'jpeg', 'png', 'webp']);
            if ($thumbnail === false) {
                $error = "Le fichier thumbnail est invalide.";
            } elseif ($thumbnail === null) {
                $thumbnail = $serviceObject->getThumbnail();
            }

            if (!$error) {
                $updatedService = new Service(
                    htmlspecialchars($_POST['titre']),
                    htmlspecialchars($_POST['description']),
                    (float) $_POST['prix'],
                    (int) $_POST['delai_livraison'],
                    (int) $_POST['id_categorie'],
                    $serviceObject->getStatut(),
                    $cv,
                    $portfolio,
                    $thumbnail
                );

                $updatedService->setId($id);
                $this->updateService($updatedService);

                header("Location: index.php?page=my_services&success=2");
                exit;
            }
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

    $services = $this->listAll($statut, null, null, $sort);
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
