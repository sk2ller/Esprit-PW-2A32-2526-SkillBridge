<?php
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Categorie.php';

class ServiceController {
    private $serviceModel;
    private $categorieModel;

    public function __construct() {
        $this->serviceModel = new Service();
        $this->categorieModel = new Categorie();
    }

    // FrontOffice: liste des services actifs
    public function index() {
        $search = $_GET['search'] ?? null;
        $id_categorie = $_GET['categorie'] ?? null;
        $services = $this->serviceModel->getAll('actif', $id_categorie, $search);
        $categories = $this->categorieModel->getAll();
        require_once __DIR__ . '/../views/FrontOffice/client/services_list.php';
    }

    // FrontOffice: détail d'un service
    public function show($id) {
        $service = $this->serviceModel->getById($id);
        if (!$service) { header("Location: index.php?page=services"); exit; }
        require_once __DIR__ . '/../views/FrontOffice/client/service_detail.php';
    }

    // Freelancer: mes services
    public function myServices() {
        $freelancer_id = $_GET['freelancer_id'] ?? 1;
        $services = $this->serviceModel->getByFreelancer($freelancer_id);
        $categories = $this->categorieModel->getAll();
        require_once __DIR__ . '/../views/FrontOffice/freelancer/my_services.php';
    }

    // Freelancer: formulaire création
    public function create() {
        $categories = $this->categorieModel->getAll();
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['titre']) || empty($_POST['description']) || empty($_POST['prix'])) {
                $error = "Tous les champs sont requis.";
            } else {
                $data = [
                    'titre' => htmlspecialchars($_POST['titre']),
                    'description' => htmlspecialchars($_POST['description']),
                    'prix' => (float)$_POST['prix'],
                    'delai_livraison' => (int)$_POST['delai_livraison'],
                    'id_categorie' => (int)$_POST['id_categorie']
                ];
                $id = $this->serviceModel->create($data);
                header("Location: index.php?page=my_services&success=1"); exit;
            }
        }
        require_once __DIR__ . '/../views/FrontOffice/freelancer/service_form.php';
    }

    // Freelancer: modifier un service
    public function edit($id) {
        $service = $this->serviceModel->getById($id);
        $categories = $this->categorieModel->getAll();
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre' => htmlspecialchars($_POST['titre']),
                'description' => htmlspecialchars($_POST['description']),
                'prix' => (float)$_POST['prix'],
                'delai_livraison' => (int)$_POST['delai_livraison'],
                'id_categorie' => (int)$_POST['id_categorie']
            ];
            $this->serviceModel->update($id, $data);
            header("Location: index.php?page=my_services&success=2"); exit;
        }
        require_once __DIR__ . '/../views/FrontOffice/freelancer/service_form.php';
    }

    // Freelancer: supprimer
    public function delete($id) {
        $this->serviceModel->delete($id);
        header("Location: index.php?page=my_services&success=3"); exit;
    }

    // Admin: liste tous services
    public function adminIndex() {
        $services = $this->serviceModel->getAll();
        $stats = $this->serviceModel->getStats();
        require_once __DIR__ . '/../views/BackOffice/admin/services.php';
    }

    // Admin: approuver / suspendre
    public function adminUpdateStatut($id, $statut) {
        $this->serviceModel->updateStatut($id, $statut);
        header("Location: index.php?page=admin_services&success=1"); exit;
    }
}
?>
