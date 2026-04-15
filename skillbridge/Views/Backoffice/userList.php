<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ?action=login');
    exit;
}
require_once __DIR__ . '/../../Controllers/UserController.php';

$userController = new UserController();
$users = $userController->listUsers();
$msg = $_GET['msg'] ?? '';

function sanitizeInput(array $data): array {
    $filtered = filter_var_array($data, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    return $filtered ? array_map('trim', $filtered) : [];
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $userController->deleteUser($id);
        echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé']);
        exit;
    }
    
    if ($action === 'add') {
        // Nettoyer les données d'entrée
        $data = sanitizeInput($_POST);

        $nom    = $data['nom']    ?? '';
        $prenom = $data['prenom'] ?? '';
        $email  = $data['email']  ?? '';
        $pass   = $_POST['password']    ?? ''; // Ne pas nettoyer le mot de passe
        $niveau = $data['niveau']      ?? 'débutant';
        $role   = (int)($data['id_role'] ?? 2);

        // Validation des données
        if (!$nom || !$prenom || !$email || !$pass) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires ne sont pas remplis.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email invalide.']);
            exit;
        }

        if (strlen($pass) < 8) {
            echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères.']);
            exit;
        }

        if ($userController->emailExists($email)) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            exit;
        }
        
        $user = new User($nom, $prenom, $email, $pass, $niveau, $role);
        $userController->addUser($user);
        echo json_encode(['success' => true, 'message' => 'Utilisateur créé']);
        exit;
    }
    
    if ($action === 'edit') {
        $id     = (int)($_POST['id'] ?? 0);
        
        // Nettoyer les données d'entrée
        $data = sanitizeInput($_POST);

        $nom    = $data['nom']    ?? '';
        $prenom = $data['prenom'] ?? '';
        $email  = $data['email']  ?? '';
        $niveau = $data['niveau']      ?? 'débutant';
        $role   = (int)($data['id_role'] ?? 2);

        // Validation des données
        if (!$nom || !$prenom || !$email) {
            echo json_encode(['success' => false, 'message' => 'Nom, prénom et email sont obligatoires.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email invalide.']);
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
                'id_role' => $user->getIdRole()
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
                    <li class="nav-item">
                        <a href="?action=brainstorming_admin">
                            <i class="fas fa-lightbulb"></i>
                            <p>Brainstormings</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=job_offers">
                            <i class="fas fa-briefcase"></i>
                            <p>Offre de Job</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=projects">
                            <i class="fas fa-project-diagram"></i>
                            <p>Projets</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=services">
                            <i class="fas fa-cogs"></i>
                            <p>Services</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=products">
                            <i class="fas fa-cube"></i>
                            <p>Produits</p>
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
                <div class="page-header">
                    <h4 class="page-title">Gestion des Utilisateurs</h4>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Liste des Utilisateurs</h5>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                    <i class="fas fa-plus me-1"></i>Ajouter Utilisateur
                                </button>
                            </div>
                            <div class="card-body">
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
                                                <td><span class="badge bg-<?= ($user->getIdRole() == 1) ? 'danger' : 'success' ?>"><?= ($user->getIdRole() == 1) ? 'Admin' : 'Utilisateur' ?></span></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" onclick="editUser(<?= $user->getIdUser() ?>)" data-bs-toggle="modal" data-bs-target="#editUserModal">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteUser(<?= $user->getIdUser() ?>)">
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
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de Passe</label>
                        <input type="password" name="password" class="form-control" required>
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
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
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
                            <option value="2">Utilisateur</option>
                            <option value="1">Admin</option>
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
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'add');
    
    fetch('?action=userlist', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        const msg = document.getElementById('addUserMsg');
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
    formData.append('action', 'edit');
    
    fetch('?action=userlist', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        const msg = document.getElementById('editUserMsg');
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
