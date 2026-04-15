<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header('Location: ?action=login');
    exit;
}
require_once __DIR__ . '/../../Controllers/UserController.php';
require_once __DIR__ . '/../../Controllers/ExcelExporter.php';

$userController = new UserController();
$users = $userController->listUsers();
$msg = $_GET['msg'] ?? '';

function isValidUserListName($value)
{
    return (bool) preg_match("/^[a-zA-ZÀ-ÿ][a-zA-ZÀ-ÿ' -]{1,49}$/u", $value);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'filter') {
        // Handle search/filter
        $filters = [
            'search' => trim($_POST['search'] ?? ''),
            'id_role' => $_POST['id_role'] ?? 'all',
            'is_approved' => $_POST['is_approved'] ?? 'all',
            'niveau' => $_POST['niveau'] ?? 'all'
        ];
        
        $users = $userController->searchUsers($filters);
        
        // Return HTML for table body
        if (empty($users)) {
            echo '<tr><td colspan="8" class="text-center">Aucun utilisateur ne correspond aux critères.</td></tr>';
        } else {
            foreach ($users as $user) {
                echo '<tr>';
                echo '<td>' . $user->getIdUser() . '</td>';
                echo '<td>' . htmlspecialchars($user->getNom()) . '</td>';
                echo '<td>' . htmlspecialchars($user->getPrenom()) . '</td>';
                echo '<td>' . htmlspecialchars($user->getEmail()) . '</td>';
                echo '<td>' . htmlspecialchars($user->getNiveau()) . '</td>';
                
                $roleName = ($user->getIdRole() == 1) ? 'Admin' : (($user->getIdRole() == 3) ? 'Freelancer' : 'Client');
                $roleBadge = ($user->getIdRole() == 1) ? 'danger' : (($user->getIdRole() == 3) ? 'info' : 'success');
                echo '<td><span class="badge bg-' . $roleBadge . '">' . $roleName . '</span></td>';
                
                $approvedBadge = $user->getIsApproved() ? 'success' : 'warning';
                $approvedText = $user->getIsApproved() ? 'Oui' : 'Non';
                echo '<td><span class="badge bg-' . $approvedBadge . '">' . $approvedText . '</span></td>';
                
                echo '<td>';
                // Approve/Disapprove only for freelancers
                if ($user->getIdRole() == 3) {
                    if (!$user->getIsApproved()) {
                        echo '<button class="btn btn-success btn-sm" onclick="approveUser(' . $user->getIdUser() . ')" title="Approuver"><i class="fas fa-check"></i></button>';
                    }
                    if ($user->getIsApproved()) {
                        echo '<button class="btn btn-warning btn-sm" onclick="disapproveUser(' . $user->getIdUser() . ')" title="Désapprouver"><i class="fas fa-ban"></i></button>';
                    }
                }
                if (!$user->getIsBanned()) {
                    echo '<button class="btn btn-danger btn-sm" onclick="banUser(' . $user->getIdUser() . ')" title="Bannir"><i class="fas fa-lock"></i></button>';
                } else {
                    echo '<button class="btn btn-info btn-sm" onclick="unbanUser(' . $user->getIdUser() . ')" title="Débannir"><i class="fas fa-unlock"></i></button>';
                }
                echo '<button class="btn btn-info btn-sm" onclick="editUser(' . $user->getIdUser() . ')" data-bs-toggle="modal" data-bs-target="#editUserModal" title="Modifier"><i class="fas fa-edit"></i></button>';
                echo '<button class="btn btn-danger btn-sm" onclick="deleteUser(' . $user->getIdUser() . ')" title="Supprimer Définitivement"><i class="fas fa-trash"></i></button>';
                echo '</td>';
                echo '</tr>';
            }
        }
        exit;
    }
    
    if ($action === 'export') {
        ExcelExporter::exportUsersToPDF($users, 'skillbridge_users_' . date('Y-m-d') . '.pdf');
        exit;
    }
    
    if ($action === 'approve') {
        $id = (int)($_POST['id'] ?? 0);
        $user = $userController->getUserById($id);
        if ($user) {
            $userController->approveUser($id, 1);
            echo json_encode(['success' => true, 'message' => 'Utilisateur approuvé']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
        }
        exit;
    }
    
    if ($action === 'disapprove') {
        $id = (int)($_POST['id'] ?? 0);
        $user = $userController->getUserById($id);
        if ($user) {
            $userController->approveUser($id, 0);
            echo json_encode(['success' => true, 'message' => 'Utilisateur désapprouvé']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
        }
        exit;
    }
    
    if ($action === 'ban') {
        $id = (int)($_POST['id'] ?? 0);
        $user = $userController->getUserById($id);
        if ($user) {
            $userController->banUser($id, 1);
            echo json_encode(['success' => true, 'message' => 'Utilisateur banni']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
        }
        exit;
    }
    
    if ($action === 'unban') {
        $id = (int)($_POST['id'] ?? 0);
        $user = $userController->getUserById($id);
        if ($user) {
            $userController->banUser($id, 0);
            echo json_encode(['success' => true, 'message' => 'Utilisateur débanni']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
        }
        exit;
    }
    
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $userController->deleteUser($id);
        echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé']);
        exit;
    }
    
    if ($action === 'add') {
        $nom    = trim($_POST['nom']    ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email  = trim($_POST['email']  ?? '');
        $pass   = $_POST['password']    ?? '';
        $niveau = $_POST['niveau']      ?? 'débutant';
        $role   = (int)($_POST['id_role'] ?? 2);

        if (!$nom || !$prenom || !$email || !$pass) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires']);
            exit;
        }
        if (!isValidUserListName($nom) || !isValidUserListName($prenom)) {
            echo json_encode(['success' => false, 'message' => 'Le nom et le pr�nom doivent contenir uniquement des lettres, espaces, apostrophes ou tirets']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            echo json_encode(['success' => false, 'message' => 'L\'email n\'est pas valide']);
            exit;
        }
        if (strlen($pass) < 8 || strlen($pass) > 72 || !preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,72}$/', $pass)) {
            echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir 8 � 72 caract�res avec au moins une lettre et un chiffre']);
            exit;
        }
        if ($userController->emailExists($email)) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            exit;
        }
        
        $user = new User($nom, $prenom, $email, $pass, $niveau, $role, 0, 1);
        $userController->addUser($user);
        echo json_encode(['success' => true, 'message' => 'Utilisateur créé']);
        exit;
    }
    
    if ($action === 'edit') {
        $id     = (int)($_POST['id'] ?? 0);
        $nom    = trim($_POST['nom']    ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email  = trim($_POST['email']  ?? '');
        $niveau = $_POST['niveau']      ?? 'débutant';
        $role   = (int)($_POST['id_role'] ?? 2);

        if (!$nom || !$prenom || !$email) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires']);
            exit;
        }
        if (!isValidUserListName($nom) || !isValidUserListName($prenom)) {
            echo json_encode(['success' => false, 'message' => 'Le nom et le pr�nom doivent contenir uniquement des lettres, espaces, apostrophes ou tirets']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            echo json_encode(['success' => false, 'message' => 'L\'email n\'est pas valide']);
            exit;
        }
        if ($userController->emailExists($email, $id)) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            exit;
        }
        
        $user = new User($nom, $prenom, $email, '', $niveau, $role);
        $user->setIdUser($id);
        $userController->updateUser($user);
        echo json_encode(['success' => true, 'message' => 'Utilisateur modifié']);
        exit;
    }
    
    if ($action === 'get_user') {
        $id = (int)($_POST['id'] ?? 0);
        $user = $userController->getUserById($id);
        if ($user) {
            echo json_encode([
                'success' => true,
                'id' => $user->getIdUser(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'niveau' => $user->getNiveau(),
                'id_role' => $user->getIdRole(),
                'is_approved' => $user->getIsApproved()
            ]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}

// Refresh users list
$users = $userController->listUsers();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Gestion des Utilisateurs — SkillBridge Admin</title>
    <script src="/Views/assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: { families: ["Font Awesome 5 Solid","Font Awesome 5 Regular","Font Awesome 5 Brands","simple-line-icons"], urls: ["/Views/assets/css/fonts.min.css"] },
            active: function() { sessionStorage.fonts = true; }
        });
    </script>
    <link rel="stylesheet" href="/Views/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/Views/assets/css/plugins.min.css">
    <link rel="stylesheet" href="/Views/assets/css/kaiadmin.min.css">
    <style>
        .sidebar { transition: transform 0.3s ease, width 0.3s ease; }
        .wrapper.sidebar-hidden .sidebar { display: none; }
        .wrapper .main-panel { transition: margin-left 0.3s ease, width 0.3s ease; }
        .wrapper.sidebar-hidden .main-panel { margin-left: 0 !important; width: 100% !important; }
        .show-sidebar-btn { 
            display: block; 
            position: fixed; 
            top: 20px; 
            left: 20px; 
            z-index: 1050;
        }
        .wrapper:not(.sidebar-hidden) .show-sidebar-btn { display: none !important; }
        .toggle-sidebar { cursor: pointer; }
        @media (min-width: 992px) {
            .show-sidebar-btn { display: none; }
            .wrapper.sidebar-hidden .show-sidebar-btn { display: block; }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar" data-background-color="dark">
        <div class="sidebar-logo">
            <div class="logo-header" data-background-color="dark">
                <a href="?action=home" class="logo">
                    <img src="/Views/assets/img/logo1.png" alt="SkillBridge" style="height: 30px; width: auto;">
                </a>
                <div class="nav-toggle">
                    <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                    <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
                </div>
            </div>
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
            <div class="sidebar-content">
                <ul class="nav nav-secondary">
                    <li class="nav-section"><h4 class="text-section">Menu</h4></li>
                    <li class="nav-item active">
                        <a href="?action=userlist">
                            <i class="fas fa-users"></i>
                            <p>Utilisateurs</p>
                        </a>
                    </li>
                    <li class="nav-section"><h4 class="text-section">Compte</h4></li>
                    <li class="nav-item">
                        <a href="?action=logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <p>Déconnexion</p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Panel -->
    <div class="main-panel">
        <!-- Navbar -->
        <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
                <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                    <li class="nav-item">
                        <span class="nav-link" style="color: #2c3e50;">👤 <?= htmlspecialchars($_SESSION['user_prenom']) ?></span>
                    </li>
                    <li class="nav-item">
                        <a href="?action=logout" class="nav-link" title="Déconnexion">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Show Sidebar Button -->
        <button class="btn btn-outline-secondary show-sidebar-btn" id="showSidebarBtn" title="Afficher la barre latérale">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Page Content -->
        <div class="container">
            <div class="page-inner">
                <div class="page-header d-flex justify-content-between align-items-center">
                    <h4 class="page-title">Gestion des Utilisateurs</h4>
                    <a href="?action=home" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-home me-1"></i>Accueil
                    </a>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Liste des Utilisateurs</h5>
                                <div>
                                    <button class="btn btn-success btn-sm ms-2" onclick="exportUsers()">
                                        <i class="fas fa-download me-1"></i>Exporter PDF
                                    </button>
                                    <button class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                        <i class="fas fa-plus me-1"></i>Ajouter Utilisateur
                                    </button>
                                </div>
                            </div>
                        <div class="card-body">
                            <!-- Search/Filter Form -->
                            <div class="mb-4">
                                <div class="card card-light">
                                    <div class="card-header">
                                        <h5 class="card-title">Rechercher & Filtrer</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="filterForm" method="POST" action="?action=userlist">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">Recherche (Nom, Prénom, Email)</label>
                                                    <input type="text" name="search" id="filterSearch" class="form-control" placeholder="Entrez un terme...">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Rôle</label>
                                                    <select name="id_role" id="filterRole" class="form-select">
                                                        <option value="all">Tous</option>
                                                        <option value="1">Admin</option>
                                                        <option value="2">Client</option>
                                                        <option value="3">Freelancer</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Niveau</label>
                                                    <select name="niveau" id="filterNiveau" class="form-select">
                                                        <option value="all">Tous</option>
                                                        <option value="débutant">Débutant</option>
                                                        <option value="intermédiaire">Intermédiaire</option>
                                                        <option value="expert">Expert</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Approuvé</label>
                                                    <select name="is_approved" id="filterApproved" class="form-select">
                                                        <option value="all">Tous</option>
                                                        <option value="1">Oui</option>
                                                        <option value="0">Non</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-secondary w-100" onclick="applyFilter()" title="Filtrer manuellement (les filtres s'appliquent automatiquement)">
                                                        <i class="fas fa-redo me-2"></i>Actualiser
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nom</th>
                                                <th>Prénom</th>
                                                <th>Email</th>
                                                <th>Niveau</th>
                                                <th>Rôle</th>
                                                <th>Approuvé</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?= $user->getIdUser() ?></td>
                                                <td><?= htmlspecialchars($user->getNom()) ?></td>
                                                <td><?= htmlspecialchars($user->getPrenom()) ?></td>
                                                <td><?= htmlspecialchars($user->getEmail()) ?></td>
                                                <td><?= htmlspecialchars($user->getNiveau()) ?></td>
                                                <td><span class="badge bg-<?= ($user->getIdRole() == 1) ? 'danger' : (($user->getIdRole() == 3) ? 'info' : 'success') ?>"><?= ($user->getIdRole() == 1) ? 'Admin' : (($user->getIdRole() == 3) ? 'Freelancer' : 'Client') ?></span></td>
                                                <td><span class="badge bg-<?= $user->getIsApproved() ? 'success' : 'warning' ?>"><?= $user->getIsApproved() ? 'Oui' : 'Non' ?></span></td>
                                                <td>
                                                    <?php if ($user->getIdRole() == 3): ?>
                                                        <?php if (!$user->getIsApproved()): ?>
                                                        <button class="btn btn-success btn-sm" onclick="approveUser(<?= $user->getIdUser() ?>)" title="Approuver">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        <?php if ($user->getIsApproved()): ?>
                                                        <button class="btn btn-warning btn-sm" onclick="disapproveUser(<?= $user->getIdUser() ?>)" title="Désapprouver">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if (!$user->getIsBanned()): ?>
                                                    <button class="btn btn-danger btn-sm" onclick="banUser(<?= $user->getIdUser() ?>)" title="Bannir">
                                                        <i class="fas fa-lock"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button class="btn btn-info btn-sm" onclick="unbanUser(<?= $user->getIdUser() ?>)" title="Débannir">
                                                        <i class="fas fa-unlock"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-info btn-sm" onclick="editUser(<?= $user->getIdUser() ?>)" data-bs-toggle="modal" data-bs-target="#editUserModal" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteUser(<?= $user->getIdUser() ?>)" title="Supprimer Définitivement">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="container-fluid d-flex justify-content-between">
                <div class="copyright">2026 © SkillBridge</div>
            </div>
        </footer>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de Passe</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Niveau</label>
                        <select name="niveau" class="form-select">
                            <option>débutant</option>
                            <option>intermédiaire</option>
                            <option>expert</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rôle</label>
                        <select name="id_role" class="form-select">
                            <option value="2">Client</option>
                            <option value="1">Admin</option>
                            <option value="3">Freelance</option>
                        </select>
                    </div>
                    <div id="addUserMsg"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier Utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm">
                <input type="hidden" name="id" id="editUserId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Niveau</label>
                        <select name="niveau" class="form-select">
                            <option value="débutant">Débutant</option>
                            <option value="intermédiaire">Intermédiaire</option>
                            <option value="avancé">Avancé</option>
                            <option value="expert">Expert</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rôle</label>
                        <select name="id_role" class="form-select">
                            <option value="2">Client</option>
                            <option value="3">Freelancer</option>
                            <option value="1">Administrateur</option>
                        </select>
                    </div>
                    <div id="editUserMsg"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/Views/assets/js/core/jquery-3.7.1.min.js"></script>
<script src="/Views/assets/js/core/popper.min.js"></script>
<script src="/Views/assets/js/core/bootstrap.min.js"></script>
<script src="/Views/assets/js/kaiadmin.min.js"></script>

<script>
// Debounce timer for search input
let filterTimeout;


function applyFilter() {
    const tbody = document.querySelector('tbody');
    
    // Show loading state
    tbody.style.opacity = '0.6';
    
    const formData = new FormData();
    formData.append('action', 'filter');
    formData.append('search', document.getElementById('filterSearch').value);
    formData.append('id_role', document.getElementById('filterRole').value);
    formData.append('niveau', document.getElementById('filterNiveau').value);
    formData.append('is_approved', document.getElementById('filterApproved').value);
    
    fetch('?action=userlist', {
        method: 'POST',
        body: formData
    })
    .then(r => r.text())
    .then(html => {
        tbody.innerHTML = html;
        tbody.style.opacity = '1'; // Restore opacity
    })
    .catch(err => {
        alert('Erreur lors de la recherche: ' + err.message);
        tbody.style.opacity = '1'; // Restore opacity on error
    });
}

// Auto-refresh search on typing (with debounce)
const filterSearchInput = document.getElementById('filterSearch');
if (filterSearchInput) {
    filterSearchInput.addEventListener('input', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => {
            applyFilter();
        }, 300); // Wait 300ms after user stops typing
    });
}

// Auto-refresh on filter dropdown changes
const filterRole = document.getElementById('filterRole');
const filterNiveau = document.getElementById('filterNiveau');
const filterApproved = document.getElementById('filterApproved');

if (filterRole) {
    filterRole.addEventListener('change', applyFilter);
}
if (filterNiveau) {
    filterNiveau.addEventListener('change', applyFilter);
}
if (filterApproved) {
    filterApproved.addEventListener('change', applyFilter);
}

document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const msg = document.getElementById('addUserMsg');

    formData.append('action', 'add');
    
    fetch('?action=userlist', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            msg.innerHTML = '<div class="alert alert-success">✓ ' + data.message + '</div>';
            setTimeout(() => location.reload(), 1500);
        } else {
            msg.innerHTML = '<div class="alert alert-danger">✗ ' + data.message + '</div>';
        }
    });
});

function editUser(id) {
    const formData = new FormData();
    formData.append('action', 'get_user');
    formData.append('id', id);
    
    fetch('?action=userlist', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('editUserId').value = data.id;
            document.querySelector('#editUserForm input[name="nom"]').value = data.nom;
            document.querySelector('#editUserForm input[name="prenom"]').value = data.prenom;
            document.querySelector('#editUserForm input[name="email"]').value = data.email;
            document.querySelector('#editUserForm select[name="niveau"]').value = data.niveau;
            document.querySelector('#editUserForm select[name="id_role"]').value = data.id_role;
        }
    });
}

document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const msg = document.getElementById('editUserMsg');

    formData.append('action', 'edit');
    
    fetch('?action=userlist', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            msg.innerHTML = '<div class="alert alert-success">✓ ' + data.message + '</div>';
            setTimeout(() => location.reload(), 1500);
        } else {
            msg.innerHTML = '<div class="alert alert-danger">✗ ' + data.message + '</div>';
        }
    });
});

function deleteUser(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        fetch('?action=userlist', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
    }
}

function approveUser(id) {
    if (confirm('Êtes-vous sûr de vouloir approuver cet utilisateur ?')) {
        const formData = new FormData();
        formData.append('action', 'approve');
        formData.append('id', id);
        fetch('?action=userlist', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {  
            if (data.success) {
                alert(data.message);
                location.reload(); 
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}

function disapproveUser(id) {
    if (confirm('Êtes-vous sûr de vouloir désapprouver cet utilisateur ?')) {
        const formData = new FormData();
        formData.append('action', 'disapprove');
        formData.append('id', id);
        fetch('?action=userlist', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {  
            if (data.success) {
                alert(data.message);
                location.reload(); 
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}

function banUser(id) {
    if (confirm('Êtes-vous sûr de vouloir bannir cet utilisateur ?')) {
        const formData = new FormData();
        formData.append('action', 'ban');
        formData.append('id', id);
        fetch('?action=userlist', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {  
            if (data.success) {
                alert(data.message);
                location.reload(); 
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}

function unbanUser(id) {
    if (confirm('Êtes-vous sûr de vouloir débannir cet utilisateur ?')) {
        const formData = new FormData();
        formData.append('action', 'unban');
        formData.append('id', id);
        fetch('?action=userlist', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {  
            if (data.success) {
                alert(data.message);
                location.reload(); 
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}

function exportUsers() {
    const formData = new FormData();
    formData.append('action', 'export');
    fetch('?action=userlist', { method: 'POST', body: formData })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'skillbridge_users_' + new Date().toISOString().split('T')[0] + '.pdf';
        document.body.appendChild(link);
        link.click();
        link.parentNode.removeChild(link);
        window.URL.revokeObjectURL(url);
    })
    .catch(err => alert('Erreur lors de l\'export: ' + err.message));
}

// Sidebar Toggle
const wrapper = document.querySelector('.wrapper');
const toggleSidebarBtn = document.querySelector('.toggle-sidebar');
const showSidebarBtn = document.getElementById('showSidebarBtn');

if (toggleSidebarBtn) {
    toggleSidebarBtn.addEventListener('click', function() {
        wrapper.classList.toggle('sidebar-hidden');
    });
}

if (showSidebarBtn) {
    showSidebarBtn.addEventListener('click', function() {
        wrapper.classList.remove('sidebar-hidden');
    });
}
</script>

</body>
</html>







