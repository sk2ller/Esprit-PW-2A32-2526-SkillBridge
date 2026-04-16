<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/ProduitController.php';
require_once __DIR__ . '/controllers/CategorieProduitController.php';

$page = $_GET['page'] ?? 'home';
$role = $_GET['role'] ?? 'client';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$produitCtrl = new ProduitController();
$categorieCtrl = new CategorieProduitController();

// Stocker le rôle en session
if (isset($_GET['role'])) {
    $_SESSION['role'] = $_GET['role'];
}
$currentRole = $_SESSION['role'] ?? 'client';

switch ($page) {
    // ========== FRONT OFFICE ==========
    case 'home':
        require_once __DIR__ . '/views/FrontOffice/client/home.php';
        break;
    case 'produits':
    case 'all_produits':
        $produitCtrl->index();
        break;
    case 'produit_detail':
        $produitCtrl->show($id);
        break;

    // Vendeur: Mes produits
    case 'mes_produits':
        $produitCtrl->mesProduits();
        break;
    case 'create_produit':
        $produitCtrl->create();
        break;
    case 'edit_produit':
        $produitCtrl->edit($id);
        break;
    case 'delete_produit':
        $produitCtrl->delete($id);
        break;

    // ========== BACK OFFICE (Admin) ==========
    case 'admin_dashboard':
        require_once __DIR__ . '/views/BackOffice/admin/dashboard.php';
        break;
    case 'admin_produits':
        $produitCtrl->adminIndex();
        break;
    case 'admin_produit_statut':
        $statut = $_GET['statut'] ?? 'en_attente';
        $produitCtrl->adminUpdateStatut($id, $statut);
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
