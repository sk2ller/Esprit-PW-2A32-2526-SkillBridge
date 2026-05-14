<?php
session_start();
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/ProduitController.php';
require_once __DIR__ . '/controllers/CategorieProduitController.php';
require_once __DIR__ . '/controllers/CommandeController.php';
require_once __DIR__ . '/controllers/AuthController.php';

$page = $_GET['page'] ?? 'welcome';
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
    // ========== AUTHENTICATION ==========
    case 'welcome':
        // Si déjà connecté, rediriger vers le dashboard
        if (AuthController::isLoggedIn()) {
            header('Location: ' . AuthController::getDashboardUrl(AuthController::getCurrentRole()));
            exit;
        }
        require_once __DIR__ . '/views/welcome.php';
        break;

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authCtrl = new AuthController();
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $loginRole = $_POST['role'] ?? 'client';
            $csrfToken = $_POST['csrf_token'] ?? '';

            // Vérification CSRF
            if (!AuthController::verifyCsrfToken($csrfToken)) {
                $_SESSION['auth_errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
                $_SESSION['auth_role'] = $loginRole;
                $_SESSION['auth_tab'] = 'login';
                header('Location: index.php?page=welcome'); exit;
            }

            // Mapper freelancer -> vendeur pour la base de données
            $dbRole = ($loginRole === 'freelancer') ? 'vendeur' : $loginRole;
            $result = $authCtrl->login($email, $password, $dbRole);

            if ($result['success']) {
                $authCtrl->createSession($result['user']);
                header('Location: ' . AuthController::getDashboardUrl($result['user']->getRole()));
                exit;
            } else {
                $_SESSION['auth_errors'] = $result['errors'];
                $_SESSION['auth_role'] = $loginRole;
                $_SESSION['auth_tab'] = 'login';
                $_SESSION['old_input'] = ['email' => $email];
                header('Location: index.php?page=welcome'); exit;
            }
        }
        header('Location: index.php?page=welcome'); exit;
        break;

    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authCtrl = new AuthController();
            $regRole = $_POST['role'] ?? 'client';
            $csrfToken = $_POST['csrf_token'] ?? '';

            // Vérification CSRF
            if (!AuthController::verifyCsrfToken($csrfToken)) {
                $_SESSION['auth_errors'] = ['Token de sécurité invalide. Veuillez réessayer.'];
                $_SESSION['auth_role'] = $regRole;
                $_SESSION['auth_tab'] = 'register';
                header('Location: index.php?page=welcome'); exit;
            }

            if ($regRole === 'freelancer') {
                $result = $authCtrl->registerFreelancer($_POST);
            } else {
                $result = $authCtrl->registerClient($_POST);
            }

            if ($result['success']) {
                $authCtrl->createSession($result['user']);
                header('Location: ' . AuthController::getDashboardUrl($result['user']->getRole()));
                exit;
            } else {
                $_SESSION['auth_errors'] = $result['errors'];
                $_SESSION['auth_role'] = $regRole;
                $_SESSION['auth_tab'] = 'register';
                $_SESSION['old_input'] = $_POST;
                unset($_SESSION['old_input']['password'], $_SESSION['old_input']['confirm_password']);
                header('Location: index.php?page=welcome'); exit;
            }
        }
        header('Location: index.php?page=welcome'); exit;
        break;

    case 'logout':
        $authCtrl = new AuthController();
        $authCtrl->logout();
        session_start();
        $_SESSION['panier'] = [];
        header('Location: index.php?page=welcome'); exit;
        break;

    case 'forgot_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authCtrl = new AuthController();
            $email = trim($_POST['email'] ?? '');
            $csrfToken = $_POST['csrf_token'] ?? '';

            if (!AuthController::verifyCsrfToken($csrfToken)) {
                $_SESSION['forgot_error'] = 'Token de sécurité invalide.';
                header('Location: index.php?page=welcome'); exit;
            }

            $result = $authCtrl->generateResetToken($email);
            if ($result['success']) {
                $_SESSION['reset_token_display'] = $result['token'];
                $_SESSION['reset_email_display'] = $email;
                $_SESSION['forgot_success'] = 'Un lien de réinitialisation a été généré.';
            } else {
                $_SESSION['forgot_error'] = $result['error'];
            }
            header('Location: index.php?page=reset_password_form'); exit;
        }
        header('Location: index.php?page=welcome'); exit;
        break;

    case 'reset_password_form':
        require_once __DIR__ . '/views/FrontOffice/client/reset_password.php';
        break;

    case 'reset_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authCtrl = new AuthController();
            $token = $_POST['token'] ?? '';
            $email = $_POST['email'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $result = $authCtrl->resetPassword($email, $token, $newPassword, $confirmPassword);
            if ($result['success']) {
                $_SESSION['auth_success'] = 'Mot de passe réinitialisé avec succès ! Connectez-vous.';
                $_SESSION['auth_role'] = 'client';
                header('Location: index.php?page=welcome'); exit;
            } else {
                $_SESSION['reset_error'] = $result['error'];
                $_SESSION['reset_email_display'] = $email;
                $_SESSION['reset_token_display'] = $token;
                header('Location: index.php?page=reset_password_form'); exit;
            }
        }
        header('Location: index.php?page=welcome'); exit;
        break;

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
        // Lookup vendor email for chat
        $vendorEmail = '';
        if ($produit->getIdVendeur()) {
            $db = Config::getConnexion();
            $vq = $db->prepare("SELECT email FROM users WHERE id_user = :id");
            $vq->execute([':id' => $produit->getIdVendeur()]);
            $vendorEmail = $vq->fetchColumn() ?: '';
        }
        require_once __DIR__ . '/views/FrontOffice/client/produit_detail.php';
        break;

    // Vendeur: Messages
    case 'mes_messages':
        require_once __DIR__ . '/views/FrontOffice/vendeur/mes_messages.php';
        break;

    // Vendeur: Mes produits
    case 'mes_produits':
        $vendeurId = $_SESSION['user']['id'] ?? null;
        $produits = $produitCtrl->listProduitsVendeur($vendeurId);
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
                $produit = new Produit();
                $produit->setNom(htmlspecialchars($_POST['nom']));
                $produit->setDescription(htmlspecialchars($_POST['description']));
                $produit->setPrix((float)$_POST['prix']);
                $produit->setQuantite((int)$_POST['quantite']);
                $produit->setIdCategorie((int)$_POST['id_categorie']);
                $produit->setIdVendeur($_SESSION['user']['id'] ?? null);
                
                $imagePath = $produitCtrl->handleImageUpload($_FILES['image'] ?? null);
                if ($imagePath) {
                    $produit->setImage($imagePath);
                }
                
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
            $produitUpdated = new Produit();
            $produitUpdated->setNom(htmlspecialchars($_POST['nom']));
            $produitUpdated->setDescription(htmlspecialchars($_POST['description']));
            $produitUpdated->setPrix((float)$_POST['prix']);
            $produitUpdated->setQuantite((int)$_POST['quantite']);
            $produitUpdated->setIdCategorie((int)$_POST['id_categorie']);
            $produitUpdated->setId($id);
            
            $imagePath = $produitCtrl->handleImageUpload($_FILES['image'] ?? null);
            if ($imagePath) {
                $produitUpdated->setImage($imagePath);
            } else {
                $produitUpdated->setImage($produit->getImage());
            }
            
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
        $cStats = $commandeCtrl->getStats();
        $platformRevenue = $commandeCtrl->getPlatformRevenueStats(7);
        $platformTopProducts = $commandeCtrl->getPlatformTopProducts(5);
        $ordersPerDay = $commandeCtrl->getOrdersPerDay(7);
        $totalRevenue = $commandeCtrl->getTotalRevenue();
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
                $categorie = new CategorieProduit();
                $categorie->setNomCategorie(htmlspecialchars($_POST['nom_categorie']));
                $categorie->setDescription(htmlspecialchars($_POST['description'] ?? ''));
                $categorie->setIcone(htmlspecialchars($_POST['icone'] ?? 'fas fa-folder'));
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
            $categorieUpdated = new CategorieProduit();
            $categorieUpdated->setNomCategorie(htmlspecialchars($_POST['nom_categorie']));
            $categorieUpdated->setDescription(htmlspecialchars($_POST['description'] ?? ''));
            $categorieUpdated->setIcone(htmlspecialchars($_POST['icone'] ?? 'fas fa-folder'));
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

    // ========== PANIER (CART) ==========
    case 'panier_add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_p = (int)$_POST['id_produit'];
            $qty = (int)($_POST['quantite'] ?? 1);
            $p = $produitCtrl->getProduitById($id_p);
            if ($p && $p->getStatut() === 'disponible') {
                if (isset($_SESSION['panier'][$id_p])) {
                    $_SESSION['panier'][$id_p]['quantite'] += $qty;
                } else {
                    $_SESSION['panier'][$id_p] = [
                        'quantite' => $qty,
                        'prix' => $p->getPrix(),
                        'nom' => $p->getNom(),
                        'nom_categorie' => $p->getNomCategorie(),
                        'image' => $p->getImage()
                    ];
                }
            }
        }
        $redirect = $_POST['redirect'] ?? 'index.php?page=produits';
        header("Location: " . $redirect); exit;
        break;

    case 'panier_update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_p = (int)$_POST['id_produit'];
            $action = $_POST['action'] ?? '';
            if (isset($_SESSION['panier'][$id_p])) {
                if ($action === 'increase') {
                    $_SESSION['panier'][$id_p]['quantite']++;
                } elseif ($action === 'decrease') {
                    $_SESSION['panier'][$id_p]['quantite']--;
                    if ($_SESSION['panier'][$id_p]['quantite'] <= 0) {
                        unset($_SESSION['panier'][$id_p]);
                    }
                }
            }
        }
        header("Location: index.php?page=panier"); exit;
        break;

    case 'panier_remove':
        $id_p = (int)$_GET['id'];
        if (isset($_SESSION['panier'][$id_p])) {
            unset($_SESSION['panier'][$id_p]);
        }
        header("Location: index.php?page=panier"); exit;
        break;

    case 'panier_clear':
        $_SESSION['panier'] = [];
        header("Location: index.php?page=panier"); exit;
        break;

    case 'panier':
        $allCategories = $categorieCtrl->listCategories();
        require_once __DIR__ . '/views/FrontOffice/client/panier.php';
        break;

    // ========== COMMANDES - FRONT OFFICE ==========
    case 'checkout':
        if (empty($_SESSION['panier'])) {
            header("Location: index.php?page=produits"); exit;
        }
        $allCategories = $categorieCtrl->listCategories();
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['nom_client']) || empty($_POST['email_client']) || empty($_POST['adresse'])) {
                $error = "Tous les champs obligatoires doivent être remplis.";
            } else {
                foreach ($_SESSION['panier'] as $id_p => $item) {
                    $commande = new Commande();
                    $commande->setNomClient(htmlspecialchars($_POST['nom_client']));
                    $commande->setEmailClient(htmlspecialchars($_POST['email_client']));
                    $commande->setTelephone(htmlspecialchars($_POST['telephone'] ?? ''));
                    $commande->setAdresse(htmlspecialchars($_POST['adresse']));
                    $commande->setIdProduit($id_p);
                    $commande->setQuantite($item['quantite']);
                    $commande->setPrixTotal($item['prix'] * $item['quantite']);
                    $commande->setNote(htmlspecialchars($_POST['note'] ?? ''));
                    $commandeCtrl->addCommande($commande);
                }
                $_SESSION['panier'] = []; // Clear cart
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
        $cmdRevenue = $commandeCtrl->getPlatformRevenueStats(7);
        $cmdOrdersPerDay = $commandeCtrl->getOrdersPerDay(7);
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
        // Rediriger les pages inconnues vers la page d'accueil
        header('Location: index.php?page=welcome');
        exit;
}
?>
