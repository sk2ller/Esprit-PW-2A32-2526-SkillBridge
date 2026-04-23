<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/ProduitController.php';
require_once __DIR__ . '/controllers/CategorieProduitController.php';
require_once __DIR__ . '/controllers/CommandeController.php';

$page = $_GET['page'] ?? 'home';
$role = $_GET['role'] ?? 'client';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$produitCtrl = new ProduitController();
$categorieCtrl = new CategorieProduitController();
$commandeCtrl = new CommandeController();

// Stocker le rôle en session
if (isset($_GET['role'])) {
    $_SESSION['role'] = $_GET['role'];
}
$currentRole = $_SESSION['role'] ?? 'client';

switch ($page) {
    // ========== FRONT OFFICE ==========
    case 'home':
        $allCategories = $categorieCtrl->listCategories();
        require_once __DIR__ . '/views/FrontOffice/client/home.php';
        break;
    case 'produits':
    case 'all_produits':
        $search = $_GET['search'] ?? null;
        $id_categorie = $_GET['categorie'] ?? null;
        $produits = $produitCtrl->listProduits('disponible', $id_categorie, $search);
        $categories = $categorieCtrl->listCategories();
        $allCategories = $categories;
        require_once __DIR__ . '/views/FrontOffice/client/produits_list.php';
        break;
    case 'produit_detail':
        $produit = $produitCtrl->getProduitById($id);
        if (!$produit) { header("Location: index.php?page=produits"); exit; }
        $allCategories = $categorieCtrl->listCategories();
        require_once __DIR__ . '/views/FrontOffice/client/produit_detail.php';
        break;

    // Vendeur: Mes produits
    case 'mes_produits':
        $produits = $produitCtrl->listProduitsVendeur();
        $categories = $categorieCtrl->listCategories();
        $allCategories = $categories;
        require_once __DIR__ . '/views/FrontOffice/vendeur/mes_produits.php';
        break;
    case 'create_produit':
        $categories = $categorieCtrl->listCategories();
        $allCategories = $categories;
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['nom']) || empty($_POST['description']) || empty($_POST['prix'])) {
                $error = "Tous les champs obligatoires doivent être remplis.";
            } else {
                $produit = new Produit(
                    htmlspecialchars($_POST['nom']),
                    htmlspecialchars($_POST['description']),
                    (float)$_POST['prix'],
                    (int)$_POST['quantite'],
                    null,
                    null,
                    (int)$_POST['id_categorie']
                );
                $produitCtrl->addProduit($produit);
                header("Location: index.php?page=mes_produits&success=1"); exit;
            }
        }
        require_once __DIR__ . '/views/FrontOffice/vendeur/produit_form.php';
        break;
    case 'edit_produit':
        $produit = $produitCtrl->getProduitById($id);
        $categories = $categorieCtrl->listCategories();
        $allCategories = $categories;
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $produitUpdated = new Produit(
                htmlspecialchars($_POST['nom']),
                htmlspecialchars($_POST['description']),
                (float)$_POST['prix'],
                (int)$_POST['quantite'],
                null,
                null,
                (int)$_POST['id_categorie']
            );
            $produitUpdated->setId($id);
            $produitCtrl->updateProduit($produitUpdated);
            header("Location: index.php?page=mes_produits&success=2"); exit;
        }
        require_once __DIR__ . '/views/FrontOffice/vendeur/produit_form.php';
        break;
    case 'delete_produit':
        $produitCtrl->deleteProduit($id);
        header("Location: index.php?page=mes_produits&success=3"); exit;
        break;

    // ========== BACK OFFICE (Admin) ==========
    case 'admin_dashboard':
        $pStats = $produitCtrl->getStats();
        require_once __DIR__ . '/views/BackOffice/admin/dashboard.php';
        break;
    case 'admin_produits':
        $produits = $produitCtrl->listProduits();
        $stats = $produitCtrl->getStats();
        $pStats = $stats;
        require_once __DIR__ . '/views/BackOffice/admin/produits.php';
        break;
    case 'admin_produit_statut':
        $statut = $_GET['statut'] ?? 'en_attente';
        $produitCtrl->updateStatut($id, $statut);
        header("Location: index.php?page=admin_produits&success=1"); exit;
        break;
    case 'admin_categories':
        $categories = $categorieCtrl->listCategories();
        $pStats = $produitCtrl->getStats();
        require_once __DIR__ . '/views/BackOffice/admin/categories.php';
        break;
    case 'admin_categorie_create':
        $pStats = $produitCtrl->getStats();
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['nom_categorie'])) {
                $error = "Le nom de la catégorie est requis.";
            } else {
                $categorie = new CategorieProduit(
                    htmlspecialchars($_POST['nom_categorie']),
                    htmlspecialchars($_POST['description'] ?? ''),
                    htmlspecialchars($_POST['icone'] ?? 'fas fa-folder')
                );
                $categorieCtrl->addCategorie($categorie);
                header("Location: index.php?page=admin_categories&success=1"); exit;
            }
        }
        require_once __DIR__ . '/views/BackOffice/admin/categorie_form.php';
        break;
    case 'admin_categorie_edit':
        $pStats = $produitCtrl->getStats();
        $categorie = $categorieCtrl->getCategorieById($id);
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categorieUpdated = new CategorieProduit(
                htmlspecialchars($_POST['nom_categorie']),
                htmlspecialchars($_POST['description'] ?? ''),
                htmlspecialchars($_POST['icone'] ?? 'fas fa-folder')
            );
            $categorieUpdated->setId($id);
            $categorieCtrl->updateCategorie($categorieUpdated);
            header("Location: index.php?page=admin_categories&success=2"); exit;
        }
        require_once __DIR__ . '/views/BackOffice/admin/categorie_form.php';
        break;
    case 'admin_categorie_delete':
        $categorieCtrl->deleteCategorie($id);
        header("Location: index.php?page=admin_categories&success=3"); exit;
        break;

    // ========== COMMANDES - FRONT OFFICE ==========
    case 'commander':
        $produit = $produitCtrl->getProduitById($id);
        if (!$produit) { header("Location: index.php?page=produits"); exit; }
        $allCategories = $categorieCtrl->listCategories();
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['nom_client']) || empty($_POST['email_client']) || empty($_POST['adresse'])) {
                $error = "Tous les champs obligatoires doivent être remplis.";
            } else {
                $quantite = max(1, (int)$_POST['quantite']);
                $prixUnit = (float)$_POST['prix_unitaire'];
                $commande = new Commande(
                    htmlspecialchars($_POST['nom_client']),
                    htmlspecialchars($_POST['email_client']),
                    htmlspecialchars($_POST['telephone'] ?? ''),
                    htmlspecialchars($_POST['adresse']),
                    (int)$_POST['id_produit'],
                    $quantite,
                    $prixUnit * $quantite,
                    htmlspecialchars($_POST['note'] ?? '')
                );
                $commandeCtrl->addCommande($commande);
                header("Location: index.php?page=mes_commandes&success=1"); exit;
            }
        }
        require_once __DIR__ . '/views/FrontOffice/client/commande_form.php';
        break;
    case 'mes_commandes':
        $commandes = $commandeCtrl->listCommandes();
        $allCategories = $categorieCtrl->listCategories();
        require_once __DIR__ . '/views/FrontOffice/client/commandes_list.php';
        break;
    case 'commande_detail':
        $commande = $commandeCtrl->getCommandeById($id);
        if (!$commande) { header("Location: index.php?page=mes_commandes"); exit; }
        $allCategories = $categorieCtrl->listCategories();
        require_once __DIR__ . '/views/FrontOffice/client/commande_detail.php';
        break;
    case 'annuler_commande':
        $commandeCtrl->updateStatut($id, 'annulee');
        header("Location: index.php?page=mes_commandes&success=3"); exit;
        break;
    case 'submit_review':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rating = (int)$_POST['rating'];
            $review = htmlspecialchars($_POST['review'] ?? '');
            $commandeCtrl->submitReview($id, $rating, $review);
            header("Location: index.php?page=commande_detail&id=$id&success=1"); exit;
        }
        break;

    // ========== COMMANDES - BACK OFFICE ==========
    case 'admin_commandes':
        $commandes = $commandeCtrl->listCommandes();
        $cStats = $commandeCtrl->getStats();
        $pStats = $produitCtrl->getStats();
        require_once __DIR__ . '/views/BackOffice/admin/commandes.php';
        break;
    case 'admin_commande_statut':
        $statut = $_GET['statut'] ?? 'en_attente';
        $commandeCtrl->updateStatut($id, $statut);
        header("Location: index.php?page=admin_commandes&success=1"); exit;
        break;
    case 'admin_commande_delete':
        $commandeCtrl->deleteCommande($id);
        header("Location: index.php?page=admin_commandes&success=1"); exit;
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Page non trouvée</h1>";
}
?>
