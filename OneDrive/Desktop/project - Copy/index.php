<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/ServiceController.php';
require_once __DIR__ . '/controllers/CategorieController.php';

$page = $_GET['page'] ?? 'home';
$role = $_GET['role'] ?? 'client';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$serviceCtrl = new ServiceController();
$categorieCtrl = new CategorieController();

// Set role in session
if (isset($_GET['role'])) {
    $_SESSION['role'] = $_GET['role'];
}
$currentRole = $_SESSION['role'] ?? 'client';

switch ($page) {
    case 'home':
    case 'services':
        $serviceCtrl->index();
        break;
    case 'service_detail':
        $serviceCtrl->show($id);
        break;

    // Freelancer
    case 'my_services':
        $serviceCtrl->myServices();
        break;
    case 'create_service':
        $serviceCtrl->create();
        break;
    case 'edit_service':
        $serviceCtrl->edit($id);
        break;
    case 'delete_service':
        $serviceCtrl->delete($id);
        break;

    // Admin
    case 'admin_dashboard':
        require_once __DIR__ . '/views/BackOffice/dashboard.php';
        break;
    case 'admin_services':
        $serviceCtrl->adminIndex();
        break;
    case 'admin_service_statut':
        $serviceCtrl->adminUpdateStatut($id, $_GET['statut']);
        break;
    case 'admin_categories':
        $categorieCtrl->adminIndex();
        break;
    case 'admin_categorie_create':
        $categorieCtrl->adminCreate();
        break;
    case 'admin_categorie_edit':
        $categorieCtrl->adminEdit($id);
        break;
    case 'admin_categorie_delete':
        $categorieCtrl->adminDelete($id);
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Page non trouvée</h1>";
}
?>
