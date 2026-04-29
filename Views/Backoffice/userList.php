<?php
if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 1) {
    header('Location: ?action=login');
    exit;
}

require_once __DIR__ . '/../../Controllers/UserController.php';
require_once __DIR__ . '/../../Controllers/ExcelExporter.php';
require_once __DIR__ . '/../../Models/User.php';
require_once __DIR__ . '/../../config.php';

$userController = new UserController();
$formError = null;
$formFieldErrors = [];
$editingUser = null;

function isValidUserListName($value)
{
    return (bool) preg_match("/^[\\p{L}][\\p{L}' -]{1,49}$/u", $value);
}

function buildUserFiltersFromArray($source)
{
    return [
        'search' => trim($source['search'] ?? ''),
        'id_role' => $source['id_role'] ?? 'all',
        'is_approved' => $source['is_approved'] ?? 'all',
        'niveau' => $source['niveau'] ?? 'all',
    ];
}

function isAjaxUserListRequest()
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}

function renderUserListRows($users)
{
    if (empty($users)) {
        return '<tr id="user-empty-row"><td colspan="8">Aucun utilisateur trouve.</td></tr>';
    }

    ob_start();
    foreach ($users as $user):
        $roleId = (int)$user->getIdRole();
        $roleName = $roleId === 1 ? 'Admin' : ($roleId === 3 ? 'Freelancer' : 'Client');
        $roleClass = $roleId === 1 ? 'badge-suspendu' : ($roleId === 3 ? 'badge-actif' : 'badge-pending');
        $approvedClass = $user->getIsApproved() ? 'badge-actif' : 'badge-pending';
        $banClass = $user->getIsBanned() ? 'badge-suspendu' : 'badge-actif';
    ?>
        <tr
            class="js-user-row"
            data-search="<?= htmlspecialchars(mb_strtolower(trim($user->getPrenom() . ' ' . $user->getNom() . ' ' . $user->getEmail()), 'UTF-8'), ENT_QUOTES) ?>"
            data-role="<?= $roleId ?>"
            data-approved="<?= $user->getIsApproved() ? '1' : '0' ?>"
            data-level="<?= htmlspecialchars(mb_strtolower($user->getNiveau(), 'UTF-8'), ENT_QUOTES) ?>"
        >
            <td>#<?= (int)$user->getIdUser() ?></td>
            <td><div class="table-service-name"><?= htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()) ?></div></td>
            <td><?= htmlspecialchars($user->getEmail()) ?></td>
            <td><span class="badge <?= $roleClass ?>"><?= $roleName ?></span></td>
            <td><?= htmlspecialchars($user->getNiveau()) ?></td>
            <td><span class="badge <?= $approvedClass ?>"><?= $user->getIsApproved() ? 'Oui' : 'Non' ?></span></td>
            <td><span class="badge <?= $banClass ?>"><?= $user->getIsBanned() ? 'Banni' : 'Actif' ?></span></td>
            <td>
                <div class="admin-stack-actions">
                    <?php if ($roleId === 3 && !$user->getIsApproved()): ?>
                        <form method="post" class="js-confirm-action" data-confirm-title="Approuver ce freelancer ?" data-confirm-text="Le compte sera approuve et pourra proposer ses services."><input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="<?= (int)$user->getIdUser() ?>"><button class="admin-btn admin-btn-success admin-btn-sm" type="submit">Approuver</button></form>
                    <?php endif; ?>
                    <?php if ($roleId === 3 && $user->getIsApproved()): ?>
                        <form method="post" class="js-confirm-action" data-confirm-title="Retirer l approbation ?" data-confirm-text="Le freelancer repassera en attente d approbation."><input type="hidden" name="action" value="disapprove"><input type="hidden" name="id" value="<?= (int)$user->getIdUser() ?>"><button class="admin-btn admin-btn-warning admin-btn-sm" type="submit">Retirer</button></form>
                    <?php endif; ?>
                    <?php if ($user->getIsBanned()): ?>
                        <form method="post" class="js-confirm-action" data-confirm-title="Debannir cet utilisateur ?" data-confirm-text="Le compte sera reactive sur la plateforme."><input type="hidden" name="action" value="unban"><input type="hidden" name="id" value="<?= (int)$user->getIdUser() ?>"><button class="admin-btn admin-btn-success admin-btn-sm" type="submit">Debannir</button></form>
                    <?php else: ?>
                        <form method="post" class="js-confirm-action" data-confirm-title="Bannir cet utilisateur ?" data-confirm-text="Le compte sera bloque jusqu a une nouvelle activation."><input type="hidden" name="action" value="ban"><input type="hidden" name="id" value="<?= (int)$user->getIdUser() ?>"><button class="admin-btn admin-btn-warning admin-btn-sm" type="submit">Bannir</button></form>
                    <?php endif; ?>
                    <button
                        class="admin-btn admin-btn-outline admin-btn-sm js-edit-user"
                        type="button"
                        data-id="<?= (int)$user->getIdUser() ?>"
                        data-nom="<?= htmlspecialchars($user->getNom(), ENT_QUOTES) ?>"
                        data-prenom="<?= htmlspecialchars($user->getPrenom(), ENT_QUOTES) ?>"
                        data-email="<?= htmlspecialchars($user->getEmail(), ENT_QUOTES) ?>"
                        data-role="<?= (int)$user->getIdRole() ?>"
                        data-niveau="<?= htmlspecialchars($user->getNiveau(), ENT_QUOTES) ?>"
                    >
                        Modifier
                    </button>
                    <form method="post" class="js-confirm-action" data-confirm-title="Supprimer cet utilisateur ?" data-confirm-text="Cette action est irreversible."><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$user->getIdUser() ?>"><button class="admin-btn admin-btn-danger admin-btn-sm" type="submit">Supprimer</button></form>
                </div>
            </td>
        </tr>
    <?php
    endforeach;

    return ob_get_clean();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'approve') {
        $userController->approveUser((int)($_POST['id'] ?? 0), 1);
        $_SESSION['userlist_status'] = 'approved';
        header('Location: ?action=userlist');
        exit;
    }

    if ($action === 'disapprove') {
        $userController->approveUser((int)($_POST['id'] ?? 0), 0);
        $_SESSION['userlist_status'] = 'disapproved';
        header('Location: ?action=userlist');
        exit;
    }

    if ($action === 'ban') {
        $userController->banUser((int)($_POST['id'] ?? 0), 1);
        $_SESSION['userlist_status'] = 'banned';
        header('Location: ?action=userlist');
        exit;
    }

    if ($action === 'unban') {
        $userController->banUser((int)($_POST['id'] ?? 0), 0);
        $_SESSION['userlist_status'] = 'unbanned';
        header('Location: ?action=userlist');
        exit;
    }

    if ($action === 'delete') {
        $userController->deleteUser((int)($_POST['id'] ?? 0));
        $_SESSION['userlist_status'] = 'deleted';
        header('Location: ?action=userlist');
        exit;
    }

    if ($action === 'filter') {
        $filters = buildUserFiltersFromArray($_POST);
        $hasFilters = $filters['search'] !== '' || $filters['id_role'] !== 'all' || $filters['is_approved'] !== 'all' || $filters['niveau'] !== 'all';
        $filteredUsers = $hasFilters ? $userController->searchUsers($filters) : $userController->listUsers();
        echo renderUserListRows($filteredUsers);
        exit;
    }

    if ($action === 'export') {
        $filters = buildUserFiltersFromArray($_POST);
        $hasFilters = $filters['search'] !== '' || $filters['id_role'] !== 'all' || $filters['is_approved'] !== 'all' || $filters['niveau'] !== 'all';
        $usersToExport = $hasFilters ? $userController->searchUsers($filters) : $userController->listUsers();
        ExcelExporter::exportUsersToPDF($usersToExport, 'skillbridge_users_' . date('Y-m-d') . '.pdf');
        exit;
    }

    if ($action === 'save_user') {
        $id = (int)($_POST['id'] ?? 0);
        $isEdit = $id > 0;
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $niveau = $_POST['niveau'] ?? 'debutant';
        $role = (int)($_POST['id_role'] ?? 2);

        if ($nom === '' || $prenom === '' || $email === '') {
            $formError = 'Tous les champs obligatoires doivent etre remplis.';
            if ($nom === '') {
                $formFieldErrors['nom'] = 'Le nom est obligatoire.';
            }
            if ($prenom === '') {
                $formFieldErrors['prenom'] = 'Le prenom est obligatoire.';
            }
            if ($email === '') {
                $formFieldErrors['email'] = 'L email est obligatoire.';
            }
        } elseif (!isValidUserListName($nom) || !isValidUserListName($prenom)) {
            $formError = 'Le nom et le prenom doivent contenir uniquement des lettres, espaces, apostrophes ou tirets.';
            if (!isValidUserListName($nom)) {
                $formFieldErrors['nom'] = 'Le nom doit contenir uniquement des lettres, espaces, apostrophes ou tirets.';
            }
            if (!isValidUserListName($prenom)) {
                $formFieldErrors['prenom'] = 'Le prenom doit contenir uniquement des lettres, espaces, apostrophes ou tirets.';
            }
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $formError = 'Veuillez saisir un email valide.';
            $formFieldErrors['email'] = 'Veuillez saisir un email valide.';
        } elseif ($userController->emailExists($email, $isEdit ? $id : null)) {
            $formError = 'Cet email est deja utilise.';
            $formFieldErrors['email'] = 'Cet email est deja utilise.';
        } elseif (!$isEdit && (strlen($password) < 8 || !preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,72}$/', $password))) {
            $formError = 'Le mot de passe doit contenir au moins 8 caracteres avec une lettre et un chiffre.';
            $formFieldErrors['password'] = 'Le mot de passe doit contenir au moins 8 caracteres avec une lettre et un chiffre.';
        }

        if ($formError === null) {
            if ($isEdit) {
                $existingUser = $userController->getUserById($id);
                if ($existingUser) {
                    $existingUser->setNom($nom);
                    $existingUser->setPrenom($prenom);
                    $existingUser->setEmail($email);
                    $existingUser->setNiveau($niveau);
                    $existingUser->setIdRole($role);
                    $updated = $userController->updateUser($existingUser);
                    if (isAjaxUserListRequest()) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => (bool)$updated,
                            'message' => $updated ? 'Utilisateur modifie avec succes.' : 'La modification a echoue.',
                            'field_errors' => []
                        ]);
                        exit;
                    }
                    $_SESSION['userlist_status'] = 'updated';
                    header('Location: ?action=userlist');
                    exit;
                }
                $formError = 'Utilisateur introuvable.';
            } else {
                $user = new User($nom, $prenom, $email, $password, $niveau, $role, 0, 1);
                $created = $userController->addUser($user);
                if (isAjaxUserListRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => (bool)$created,
                        'message' => $created ? 'Utilisateur ajoute avec succes.' : 'La creation a echoue.',
                        'field_errors' => []
                    ]);
                    exit;
                }
                $_SESSION['userlist_status'] = 'created';
                header('Location: ?action=userlist');
                exit;
            }
        }

        if (isAjaxUserListRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $formError ?: 'Une erreur est survenue.',
                'field_errors' => $formFieldErrors
            ]);
            exit;
        }

        $editingUser = new User($nom, $prenom, $email, '', $niveau, $role);
        if ($isEdit) {
            $editingUser->setIdUser($id);
        }
    }
}

$filters = buildUserFiltersFromArray($_GET);
$hasFilters = $filters['search'] !== '' || $filters['id_role'] !== 'all' || $filters['is_approved'] !== 'all' || $filters['niveau'] !== 'all';
$users = $hasFilters ? $userController->searchUsers($filters) : $userController->listUsers();
$allUsers = $userController->listUsers();

if ($editingUser === null && isset($_GET['edit'])) {
    $editingUser = $userController->getUserById((int)$_GET['edit']);
}

$statusMap = [
    'approved' => 'Utilisateur approuve avec succes.',
    'disapproved' => 'Freelancer desapprouve avec succes.',
    'banned' => 'Utilisateur banni avec succes.',
    'unbanned' => 'Utilisateur debanni avec succes.',
    'deleted' => 'Utilisateur supprime avec succes.',
    'created' => 'Utilisateur ajoute avec succes.',
    'updated' => 'Utilisateur modifie avec succes.',
];
$statusKey = $_SESSION['userlist_status'] ?? ($_GET['status'] ?? '');
unset($_SESSION['userlist_status']);
$statusMessage = $statusMap[$statusKey] ?? '';

$totalUsers = count($allUsers);
$totalAdmins = count(array_filter($allUsers, fn($user) => (int)$user->getIdRole() === 1));
$totalClients = count(array_filter($allUsers, fn($user) => (int)$user->getIdRole() === 2));
$totalFreelancers = count(array_filter($allUsers, fn($user) => (int)$user->getIdRole() === 3));
$db = Config::getConnexion();
$dashboardStats = [
    'total_interactions' => 0,
    'total_likes' => 0,
    'total_dislikes' => 0,
    'avg_rating' => 0.0,
    'total_job_offers' => 0,
    'active_job_offers' => 0,
    'total_job_applications' => 0
];

try {
    $dashboardStats['total_interactions'] = (int)$db->query("SELECT COUNT(*) FROM Interaction")->fetchColumn();
    $dashboardStats['total_likes'] = (int)$db->query("SELECT COUNT(*) FROM Interaction WHERE type = 'like'")->fetchColumn();
    $dashboardStats['total_dislikes'] = (int)$db->query("SELECT COUNT(*) FROM Interaction WHERE type = 'dislike'")->fetchColumn();
    $dashboardStats['avg_rating'] = (float)($db->query("SELECT AVG(rating) FROM User WHERE id_role = 3")->fetchColumn() ?? 0);
    $dashboardStats['total_job_offers'] = (int)$db->query("SELECT COUNT(*) FROM offre_job")->fetchColumn();
    $dashboardStats['active_job_offers'] = (int)$db->query("SELECT COUNT(*) FROM offre_job WHERE statut = 'actif'")->fetchColumn();
    $dashboardStats['total_job_applications'] = (int)$db->query("SELECT COUNT(*) FROM candidature_offre")->fetchColumn();
} catch (Exception $e) {
}

$modalUserData = [
    'id' => $editingUser ? (int)$editingUser->getIdUser() : 0,
    'nom' => $editingUser ? $editingUser->getNom() : '',
    'prenom' => $editingUser ? $editingUser->getPrenom() : '',
    'email' => $editingUser ? $editingUser->getEmail() : '',
    'niveau' => $editingUser ? $editingUser->getNiveau() : 'debutant',
    'role' => $editingUser ? (int)$editingUser->getIdRole() : 2,
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs - SkillBridge Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="Views/assets/css/skillbridge-admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600;700&display=swap">
</head>
<body class="skillbridge-admin">
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <div class="topbar-title">Utilisateurs</div>
                <div class="topbar-bread">Gestion des utilisateurs et vue d ensemble de la plateforme</div>
            </div>
            <div class="topbar-actions">
                <form method="post" style="margin:0;">
                    <input type="hidden" name="action" value="export">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($filters['search']) ?>">
                    <input type="hidden" name="id_role" value="<?= htmlspecialchars($filters['id_role']) ?>">
                    <input type="hidden" name="is_approved" value="<?= htmlspecialchars($filters['is_approved']) ?>">
                    <input type="hidden" name="niveau" value="<?= htmlspecialchars($filters['niveau']) ?>">
                    <button class="topbar-btn topbar-btn-outline" type="submit"><i class="fas fa-file-export"></i> Export PDF</button>
                </form>
                <button class="topbar-btn topbar-btn-primary" type="button" id="open-user-create" onclick="window.skillbridgeOpenUserModal && window.skillbridgeOpenUserModal()"><i class="fas fa-user-plus"></i> Ajouter utilisateur</button>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-widget purple">
                <div class="sw-icon"><i class="fas fa-users"></i></div>
                <div class="sw-value"><?= $totalUsers ?></div>
                <div class="sw-label">Utilisateurs</div>
            </div>
            <div class="stat-widget orange">
                <div class="sw-icon"><i class="fas fa-user-shield"></i></div>
                <div class="sw-value"><?= $totalAdmins ?></div>
                <div class="sw-label">Admins</div>
            </div>
            <div class="stat-widget blue">
                <div class="sw-icon"><i class="fas fa-user"></i></div>
                <div class="sw-value"><?= $totalClients ?></div>
                <div class="sw-label">Clients</div>
            </div>
            <div class="stat-widget green">
                <div class="sw-icon"><i class="fas fa-user-tie"></i></div>
                <div class="sw-value"><?= $totalFreelancers ?></div>
                <div class="sw-label">Freelancers</div>
            </div>
        </div>

        <section class="admin-card admin-filter-bar-card">
            <form method="get" class="admin-filter-bar" id="user-filter-form" onsubmit="window.skillbridgeApplyUserFilters(); return false;">
                <input type="hidden" name="action" value="userlist">
                <div class="admin-filter-bar-title">
                    <i class="fas fa-sliders"></i>
                    <span>Recherche</span>
                </div>
                <input name="search" id="user-filter-search" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Recherche nom, prenom ou email" autocomplete="off" oninput="window.skillbridgeApplyUserFilters && window.skillbridgeApplyUserFilters()" onkeyup="window.skillbridgeApplyUserFilters && window.skillbridgeApplyUserFilters()">
                <select name="id_role" id="user-filter-role" onchange="window.skillbridgeApplyUserFilters && window.skillbridgeApplyUserFilters()">
                    <option value="all">Tous les roles</option>
                    <option value="1" <?= $filters['id_role'] === '1' ? 'selected' : '' ?>>Admin</option>
                    <option value="2" <?= $filters['id_role'] === '2' ? 'selected' : '' ?>>Client</option>
                    <option value="3" <?= $filters['id_role'] === '3' ? 'selected' : '' ?>>Freelancer</option>
                </select>
                <select name="is_approved" id="user-filter-approved" onchange="window.skillbridgeApplyUserFilters && window.skillbridgeApplyUserFilters()">
                    <option value="all">Approbation</option>
                    <option value="1" <?= $filters['is_approved'] === '1' ? 'selected' : '' ?>>Approuves</option>
                    <option value="0" <?= $filters['is_approved'] === '0' ? 'selected' : '' ?>>Non approuves</option>
                </select>
                <select name="niveau" id="user-filter-level" onchange="window.skillbridgeApplyUserFilters && window.skillbridgeApplyUserFilters()">
                    <option value="all">Tous niveaux</option>
                    <option value="debutant" <?= $filters['niveau'] === 'debutant' ? 'selected' : '' ?>>Debutant</option>
                    <option value="intermediaire" <?= $filters['niveau'] === 'intermediaire' ? 'selected' : '' ?>>Intermediaire</option>
                    <option value="expert" <?= $filters['niveau'] === 'expert' ? 'selected' : '' ?>>Expert</option>
                </select>
                <button class="admin-btn admin-btn-primary" type="submit">Filtrer</button>
                <a class="admin-btn admin-btn-outline" href="?action=userlist">Reset</a>
            </form>
        </section>

        <div class="admin-table-wrap">
            <div class="admin-table-header">
                <div class="admin-table-title">Liste des utilisateurs</div>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Niveau</th>
                        <th>Approbation</th>
                        <th>Etat</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="user-table-body">
                <?= renderUserListRows($users) ?>
                </tbody>
            </table>
        </div>

        <div class="admin-split-grid" style="margin-top: 1.5rem;">
            <div class="admin-table-wrap">
                <div class="admin-table-header">
                    <div class="admin-table-title">Performance utilisateurs</div>
                </div>
                <div class="admin-chart-grid" style="display:grid; grid-template-columns:1.2fr .8fr; gap:1.25rem; padding:1.25rem;">
                    <div class="admin-chart-card" style="border-radius:22px; padding:1rem 1.1rem 1.2rem;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:.75rem;">
                            <div>
                                <div class="table-service-name">Interactions overview</div>
                                <div class="admin-note-text">Lecture rapide des retours utilisateurs</div>
                            </div>
                            <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                                <span class="badge badge-actif"><?= $dashboardStats['total_likes'] ?> likes</span>
                                <span class="badge badge-suspendu"><?= $dashboardStats['total_dislikes'] ?> dislikes</span>
                            </div>
                        </div>
                        <div style="height:260px;">
                            <canvas id="userInteractionChart"></canvas>
                        </div>
                    </div>
                    <div class="admin-chart-card" style="border-radius:22px; padding:1rem 1.1rem 1.2rem; display:flex; flex-direction:column; gap:1rem;">
                        <div>
                            <div class="table-service-name">Satisfaction snapshot</div>
                            <div class="admin-note-text">Poids relatif des avis et note moyenne</div>
                        </div>
                        <div style="height:220px;">
                            <canvas id="userRatingChart"></canvas>
                        </div>
                        <div style="display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:.75rem;">
                            <div class="admin-chart-mini-stat" style="border-radius:16px; padding:.9rem 1rem;">
                                <div class="admin-note-text">Clients</div>
                                <div style="font-size:1.35rem; font-weight:700; color:#1f1f23;"><?= $totalClients ?></div>
                            </div>
                            <div class="admin-chart-mini-stat" style="border-radius:16px; padding:.9rem 1rem;">
                                <div class="admin-note-text">Freelancers</div>
                                <div style="font-size:1.35rem; font-weight:700; color:#1f1f23;"><?= $totalFreelancers ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card admin-user-info-card">
                <div class="admin-card-title">Resume rapide</div>
                <div class="admin-list-panel">
                    <div class="admin-list-item">
                        <div class="admin-list-icon"><i class="fas fa-user-check"></i></div>
                        <div>
                            <div class="table-service-name"><?= count(array_filter($allUsers, fn($user) => !$user->getIsBanned())) ?> comptes actifs</div>
                            <div class="admin-note-text">Utilisateurs actuellement autorises sur la plateforme.</div>
                        </div>
                    </div>
                    <div class="admin-list-item">
                        <div class="admin-list-icon"><i class="fas fa-user-clock"></i></div>
                        <div>
                            <div class="table-service-name"><?= count(array_filter($allUsers, fn($user) => (int)$user->getIdRole() === 3 && !$user->getIsApproved())) ?> freelancers en attente</div>
                            <div class="admin-note-text">Profils freelancer a approuver ou a revoir.</div>
                        </div>
                    </div>
                    <div class="admin-list-item">
                        <div class="admin-list-icon"><i class="fas fa-user-slash"></i></div>
                        <div>
                            <div class="table-service-name"><?= count(array_filter($allUsers, fn($user) => $user->getIsBanned())) ?> comptes bannis</div>
                            <div class="admin-note-text">Comptes limites temporairement ou bloques.</div>
                        </div>
                    </div>
                    <div class="admin-list-item">
                        <div class="admin-list-icon"><i class="fas fa-user-shield"></i></div>
                        <div>
                            <div class="table-service-name"><?= $totalAdmins ?> administrateur(s)</div>
                            <div class="admin-note-text">Comptes responsables de la moderation et du pilotage.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<form method="post" id="user-hidden-form" style="display:none;">
    <input type="hidden" name="action" value="save_user">
    <input type="hidden" name="id" id="hidden-user-id" value="0">
    <input type="hidden" name="nom" id="hidden-user-nom" value="">
    <input type="hidden" name="prenom" id="hidden-user-prenom" value="">
    <input type="hidden" name="email" id="hidden-user-email" value="">
    <input type="hidden" name="password" id="hidden-user-password" value="">
    <input type="hidden" name="id_role" id="hidden-user-role" value="2">
    <input type="hidden" name="niveau" id="hidden-user-level" value="debutant">
</form>
<script>
const statusMessage = <?= json_encode($statusMessage) ?>;
const formErrorMessage = <?= json_encode($formError) ?>;
const formFieldErrors = <?= json_encode($formFieldErrors) ?>;
const modalUserData = <?= json_encode($modalUserData) ?>;
const dashboardChartStats = <?= json_encode([
    'total_interactions' => (int)$dashboardStats['total_interactions'],
    'total_likes' => (int)$dashboardStats['total_likes'],
    'total_dislikes' => (int)$dashboardStats['total_dislikes'],
    'avg_rating' => (float)$dashboardStats['avg_rating'],
    'total_clients' => (int)$totalClients,
    'total_freelancers' => (int)$totalFreelancers,
]) ?>;
const skillbridgeUserNameRegex = /^[\p{L}][\p{L}' -]{1,49}$/u;
const skillbridgeUserPasswordRegex = /^(?=.*[A-Za-z])(?=.*\d).{8,72}$/;
const skillbridgeUserEmailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
let skillbridgeUserFilterTimer = null;

function skillbridgeEscapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function skillbridgeClearModalFieldErrors() {
    document.querySelectorAll('.sb-field-error').forEach((element) => element.remove());
    ['swal-user-nom', 'swal-user-prenom', 'swal-user-email', 'swal-user-password'].forEach((fieldId) => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.style.borderColor = '';
        }
    });
}

function skillbridgeShowModalFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) {
        return;
    }

    field.style.borderColor = '#c33';
    const error = document.createElement('div');
    error.className = 'sb-field-error';
    error.style.color = '#c33';
    error.style.fontSize = '0.82rem';
    error.style.marginTop = '0.45rem';
    error.textContent = message;
    field.insertAdjacentElement('afterend', error);
}

function skillbridgeApplyServerFieldErrors(fieldErrors) {
    const fieldMap = {
        nom: 'swal-user-nom',
        prenom: 'swal-user-prenom',
        email: 'swal-user-email',
        password: 'swal-user-password'
    };

    skillbridgeClearModalFieldErrors();
    Object.entries(fieldErrors || {}).forEach(([key, message]) => {
        if (fieldMap[key] && message) {
            skillbridgeShowModalFieldError(fieldMap[key], message);
        }
    });
}

window.skillbridgeApplyUserFilters = function skillbridgeApplyUserFilters() {
    const filterSearchInput = document.getElementById('user-filter-search');
    const filterRoleSelect = document.getElementById('user-filter-role');
    const filterApprovedSelect = document.getElementById('user-filter-approved');
    const filterLevelSelect = document.getElementById('user-filter-level');
    const userTableBody = document.getElementById('user-table-body');

    if (!userTableBody) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'filter');
    formData.append('search', filterSearchInput ? filterSearchInput.value : '');
    formData.append('id_role', filterRoleSelect ? filterRoleSelect.value : 'all');
    formData.append('is_approved', filterApprovedSelect ? filterApprovedSelect.value : 'all');
    formData.append('niveau', filterLevelSelect ? filterLevelSelect.value : 'all');

    userTableBody.style.opacity = '0.55';

    fetch('?action=userlist', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
        .then((response) => response.text())
        .then((html) => {
            userTableBody.innerHTML = html;
            userTableBody.style.opacity = '1';
            window.skillbridgeBindUserActions();
        })
        .catch(() => {
            userTableBody.style.opacity = '1';
        });
};

window.skillbridgeOpenUserModal = async function skillbridgeOpenUserModal(data = null) {
    if (typeof Swal === 'undefined') {
        return;
    }

    const isEdit = !!(data && Number(data.id) > 0);
    const result = await Swal.fire({
        title: isEdit ? 'Modifier utilisateur' : 'Ajouter utilisateur',
        width: 760,
        background: '#1f2636',
        color: '#eef2fb',
        confirmButtonText: isEdit ? 'Mettre a jour' : 'Ajouter',
        confirmButtonColor: '#7e56ff',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        focusConfirm: false,
        customClass: {
            popup: 'sb-user-modal',
            htmlContainer: 'sb-user-modal-body'
        },
        html: `
            <div class="sb-user-modal-grid">
                <div class="sb-user-modal-field">
                    <label for="swal-user-nom">Nom</label>
                    <input id="swal-user-nom" class="swal2-input" value="${skillbridgeEscapeHtml(isEdit ? data.nom : '')}" placeholder="Nom">
                </div>
                <div class="sb-user-modal-field">
                    <label for="swal-user-prenom">Prenom</label>
                    <input id="swal-user-prenom" class="swal2-input" value="${skillbridgeEscapeHtml(isEdit ? data.prenom : '')}" placeholder="Prenom">
                </div>
                <div class="sb-user-modal-field sb-user-modal-field-full">
                    <label for="swal-user-email">Email</label>
                    <input id="swal-user-email" class="swal2-input" value="${skillbridgeEscapeHtml(isEdit ? data.email : '')}" placeholder="Email">
                </div>
                ${isEdit ? '' : `
                <div class="sb-user-modal-field sb-user-modal-field-full">
                    <label for="swal-user-password">Mot de passe</label>
                    <input id="swal-user-password" type="password" class="swal2-input" placeholder="Au moins 8 caracteres, 1 lettre, 1 chiffre">
                </div>`}
                <div class="sb-user-modal-field">
                    <label for="swal-user-role">Role</label>
                    <select id="swal-user-role" class="swal2-select">
                        <option value="1" ${(isEdit ? String(data.role) : '2') === '1' ? 'selected' : ''}>Admin</option>
                        <option value="2" ${(isEdit ? String(data.role) : '2') === '2' ? 'selected' : ''}>Client</option>
                        <option value="3" ${(isEdit ? String(data.role) : '2') === '3' ? 'selected' : ''}>Freelancer</option>
                    </select>
                </div>
                <div class="sb-user-modal-field">
                    <label for="swal-user-level">Niveau</label>
                    <select id="swal-user-level" class="swal2-select">
                        <option value="debutant" ${(isEdit ? data.niveau : 'debutant') === 'debutant' ? 'selected' : ''}>Debutant</option>
                        <option value="intermediaire" ${(isEdit ? data.niveau : 'debutant') === 'intermediaire' ? 'selected' : ''}>Intermediaire</option>
                        <option value="expert" ${(isEdit ? data.niveau : 'debutant') === 'expert' ? 'selected' : ''}>Expert</option>
                    </select>
                </div>
            </div>
        `,
        preConfirm: () => {
            skillbridgeClearModalFieldErrors();

            const nom = document.getElementById('swal-user-nom').value.trim();
            const prenom = document.getElementById('swal-user-prenom').value.trim();
            const email = document.getElementById('swal-user-email').value.trim();
            const passwordInput = document.getElementById('swal-user-password');
            const password = passwordInput ? passwordInput.value : '';
            const role = document.getElementById('swal-user-role').value;
            const niveau = document.getElementById('swal-user-level').value;
            let hasError = false;

            if (!nom) {
                skillbridgeShowModalFieldError('swal-user-nom', 'Le nom est obligatoire.');
                hasError = true;
            } else if (!skillbridgeUserNameRegex.test(nom)) {
                skillbridgeShowModalFieldError('swal-user-nom', 'Le nom doit contenir uniquement des lettres, espaces, apostrophes ou tirets.');
                hasError = true;
            }

            if (!prenom) {
                skillbridgeShowModalFieldError('swal-user-prenom', 'Le prenom est obligatoire.');
                hasError = true;
            } else if (!skillbridgeUserNameRegex.test(prenom)) {
                skillbridgeShowModalFieldError('swal-user-prenom', 'Le prenom doit contenir uniquement des lettres, espaces, apostrophes ou tirets.');
                hasError = true;
            }

            if (!email) {
                skillbridgeShowModalFieldError('swal-user-email', 'L email est obligatoire.');
                hasError = true;
            } else if (!skillbridgeUserEmailRegex.test(email)) {
                skillbridgeShowModalFieldError('swal-user-email', 'Veuillez saisir une adresse email valide.');
                hasError = true;
            }

            if (!isEdit && !password) {
                skillbridgeShowModalFieldError('swal-user-password', 'Le mot de passe est obligatoire.');
                hasError = true;
            } else if (!isEdit && !skillbridgeUserPasswordRegex.test(password)) {
                skillbridgeShowModalFieldError('swal-user-password', 'Au moins 8 caracteres avec une lettre et un chiffre.');
                hasError = true;
            }

            if (hasError) {
                return false;
            }

            return {
                id: isEdit ? data.id : 0,
                nom,
                prenom,
                email,
                password,
                role,
                niveau
            };
        }
    });

    if (!(result.isConfirmed && result.value)) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'save_user');
    formData.append('id', result.value.id);
    formData.append('nom', result.value.nom);
    formData.append('prenom', result.value.prenom);
    formData.append('email', result.value.email);
    formData.append('password', result.value.password || '');
    formData.append('id_role', result.value.role);
    formData.append('niveau', result.value.niveau);

    const response = await fetch('?action=userlist', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    });
    const payload = await response.json();

    if (payload.success) {
        await Swal.fire({
            icon: 'success',
            title: isEdit ? 'Utilisateur modifie' : 'Utilisateur ajoute',
            text: payload.message || 'Operation reussie.',
            confirmButtonColor: '#7e56ff',
            background: '#1f2636',
            color: '#eef2fb'
        });
        window.location.href = '?action=userlist';
        return;
    }

    await Swal.fire({
        icon: 'error',
        title: 'Erreur de saisie',
        text: payload.message || 'Une erreur est survenue.',
        confirmButtonColor: '#7e56ff',
        background: '#1f2636',
        color: '#eef2fb'
    });

    window.skillbridgeOpenUserModal({
        id: result.value.id,
        nom: result.value.nom,
        prenom: result.value.prenom,
        email: result.value.email,
        role: result.value.role,
        niveau: result.value.niveau
    });

    setTimeout(() => {
        skillbridgeApplyServerFieldErrors(payload.field_errors || {});
    }, 0);
};

window.skillbridgeBindUserActions = function skillbridgeBindUserActions() {
    document.querySelectorAll('.js-edit-user').forEach((button) => {
        if (button.dataset.bound === '1') {
            return;
        }
        button.dataset.bound = '1';
        button.addEventListener('click', () => {
            window.skillbridgeOpenUserModal({
                id: button.dataset.id,
                nom: button.dataset.nom,
                prenom: button.dataset.prenom,
                email: button.dataset.email,
                role: button.dataset.role,
                niveau: button.dataset.niveau
            });
        });
    });

    document.querySelectorAll('.js-confirm-action').forEach((form) => {
        if (form.dataset.bound === '1') {
            return;
        }
        form.dataset.bound = '1';
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (typeof Swal === 'undefined') {
                form.submit();
                return;
            }

            const result = await Swal.fire({
                icon: 'question',
                title: form.dataset.confirmTitle || 'Confirmer cette action ?',
                text: form.dataset.confirmText || 'Cette action va modifier les donnees.',
                showCancelButton: true,
                confirmButtonText: 'Confirmer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#7e56ff',
                cancelButtonColor: '#4b5568',
                background: '#1f2636',
                color: '#eef2fb'
            });

            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
};

window.skillbridgeInitUserListPage = function skillbridgeInitUserListPage() {
    const filterForm = document.getElementById('user-filter-form');
    const filterSearchInput = document.getElementById('user-filter-search');
    const filterRoleSelect = document.getElementById('user-filter-role');
    const filterApprovedSelect = document.getElementById('user-filter-approved');
    const filterLevelSelect = document.getElementById('user-filter-level');
    const createButton = document.getElementById('open-user-create');

    window.skillbridgeBindUserActions();

    if (createButton && createButton.dataset.bound !== '1') {
        createButton.dataset.bound = '1';
        createButton.addEventListener('click', () => window.skillbridgeOpenUserModal());
    }

    if (filterForm && filterForm.dataset.bound !== '1') {
        filterForm.dataset.bound = '1';
        filterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            window.skillbridgeApplyUserFilters();
        });
    }

    if (filterSearchInput && filterSearchInput.dataset.bound !== '1') {
        filterSearchInput.dataset.bound = '1';
        filterSearchInput.addEventListener('input', () => {
            clearTimeout(skillbridgeUserFilterTimer);
            skillbridgeUserFilterTimer = setTimeout(() => {
                window.skillbridgeApplyUserFilters();
            }, 220);
        });
    }

    [filterRoleSelect, filterApprovedSelect, filterLevelSelect].forEach((field) => {
        if (field && field.dataset.bound !== '1') {
            field.dataset.bound = '1';
            field.addEventListener('change', () => window.skillbridgeApplyUserFilters());
        }
    });

    const resetLink = filterForm ? filterForm.querySelector('a[href="?action=userlist"]') : null;
    if (resetLink && resetLink.dataset.bound !== '1') {
        resetLink.dataset.bound = '1';
        resetLink.addEventListener('click', (event) => {
            event.preventDefault();
            if (filterSearchInput) {
                filterSearchInput.value = '';
            }
            if (filterRoleSelect) {
                filterRoleSelect.value = 'all';
            }
            if (filterApprovedSelect) {
                filterApprovedSelect.value = 'all';
            }
            if (filterLevelSelect) {
                filterLevelSelect.value = 'all';
            }
            window.skillbridgeApplyUserFilters();
        });
    }

    if (statusMessage && typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Operation reussie',
            text: statusMessage,
            confirmButtonColor: '#7e56ff',
            background: '#1f2636',
            color: '#eef2fb'
        });
    }

    if ((formErrorMessage || Number(modalUserData.id) > 0) && typeof Swal !== 'undefined') {
        window.skillbridgeOpenUserModal(modalUserData).then(() => {
            if (Object.keys(formFieldErrors || {}).length > 0) {
                setTimeout(() => {
                    skillbridgeApplyServerFieldErrors(formFieldErrors);
                }, 0);
            }
        });
    }

    if (typeof Chart !== 'undefined') {
        const interactionCanvas = document.getElementById('userInteractionChart');
        const ratingCanvas = document.getElementById('userRatingChart');

        if (interactionCanvas && !interactionCanvas.dataset.bound) {
            interactionCanvas.dataset.bound = '1';
            new Chart(interactionCanvas, {
                type: 'bar',
                data: {
                    labels: ['Total', 'Likes', 'Dislikes'],
                    datasets: [{
                        label: 'Volume',
                        data: [
                            dashboardChartStats.total_interactions,
                            dashboardChartStats.total_likes,
                            dashboardChartStats.total_dislikes
                        ],
                        backgroundColor: ['#f3a25a', '#2db784', '#e05a5a'],
                        borderRadius: 14,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1f1f23',
                            padding: 12,
                            displayColors: false
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#6b7280', font: { weight: '600' } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(17,24,39,.08)' },
                            ticks: { color: '#6b7280' }
                        }
                    }
                }
            });
        }

        if (ratingCanvas && !ratingCanvas.dataset.bound) {
            ratingCanvas.dataset.bound = '1';
            new Chart(ratingCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Clients', 'Freelancers'],
                    datasets: [{
                        data: [
                            dashboardChartStats.total_clients,
                            dashboardChartStats.total_freelancers
                        ],
                        backgroundColor: ['#4f46e5', '#f59e0b'],
                        borderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                color: '#6b7280',
                                padding: 16
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1f1f23',
                            padding: 12
                        }
                    }
                }
            });
        }
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.skillbridgeInitUserListPage);
} else {
    window.skillbridgeInitUserListPage();
}
</script>
</body>
</html>
