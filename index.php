<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/ServiceController.php';
require_once __DIR__ . '/controllers/CategorieController.php';
require_once __DIR__ . '/controllers/OffreController.php';

$page = $_GET['page'] ?? 'home';
$role = $_GET['role'] ?? 'client';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$serviceCtrl = new ServiceController();
$categorieCtrl = new CategorieController();
$offreCtrl = new OffreController();

// Set role in session
if (isset($_GET['role'])) {
    $_SESSION['role'] = $_GET['role'];
}
$currentRole = $_SESSION['role'] ?? 'client';

switch ($page) {
    case 'home':
        require_once __DIR__ . '/views/FrontOffice/client/home.php';
        break;
    case 'services':
        $serviceCtrl->index();
        break;
    case 'all_services':
        $serviceCtrl->index();
        break;
    case 'categories':
        require_once __DIR__ . '/views/FrontOffice/client/categories.php';
        break;
    case 'service_detail':
        $serviceCtrl->show($id);
        break;

    // Freelancer Services
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

    // ========== OFFRES JOB ROUTES ==========
    
    // Freelancer: Browse job offers
    case 'offres':
        $offreCtrl->index();
        break;
    
    // Freelancer: View job offer details
    case 'offre_detail':
        $offreCtrl->show($id);
        break;
    
    // Client: My published offers
    case 'mes_offres':
        $offreCtrl->myOffres();
        break;
    
    // Client: Create new offer
    case 'create_offre':
        $offreCtrl->create();
        break;
    
    // Client: Edit offer
    case 'edit_offre':
        $offreCtrl->edit($id);
        break;
    
    // Client: Delete offer
    case 'delete_offre':
        $offreCtrl->delete($id);
        break;
    
    // Admin: View all offers
    case 'admin_offres':
        $offreCtrl->adminIndex();
        break;
    
    // Admin: Update offer status
    case 'admin_offre_statut':
        $statut = $_GET['statut'] ?? 'en_attente';
        $offreCtrl->adminUpdateStatut($id, $statut);
        break;

    // Admin: Services
    case 'admin_dashboard':
        require_once __DIR__ . '/views/BackOffice/admin/dashboard.php';
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
