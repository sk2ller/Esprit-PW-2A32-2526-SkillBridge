<?php
if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 1) {
    header('Location: ?action=login');
    exit;
}

require_once __DIR__ . '/../../Controllers/ProjectController.php';
require_once __DIR__ . '/../../Controllers/CandidatureController.php';

$projectController   = new ProjectController();
$candidatureController = new CandidatureController();

if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    $search = trim($_GET['q'] ?? '');
    $projectsForExport = $projectController->listProjects($search);
    $projectController->exportProjectsPdf($projectsForExport);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=UTF-8');

    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $validation = $projectController->validateProjectInput($_POST);
        if (!$validation['is_valid']) {
            echo json_encode(['success' => false, 'message' => implode(' ', $validation['errors'])]);
            exit;
        }

        $d = $validation['data'];
        // Admin ajoute → publié directement | Client ajoute → en attente de validation
        $etat      = ($_SESSION['user_role'] == 1) ? 'publie' : 'en_attente_validation';
        $id_client = ($_SESSION['user_role'] != 1) ? $_SESSION['user_id'] : null;

        $project = new Project($d['titre'], $d['description'], $d['budget'], $d['date_creation'], $d['statut'], $etat, $id_client);

        if ($projectController->addProject($project)) {
            $msg = ($etat === 'publie') ? 'Projet ajouté avec succès.' : 'Projet soumis. En attente de validation par l\'admin.';
            echo json_encode(['success' => true, 'message' => $msg]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Une erreur est survenue lors de l\'ajout.']);
        }
        exit;
    }

    if ($action === 'edit') {
        $validation = $projectController->validateProjectInput($_POST, true);
        if (!$validation['is_valid']) {
            echo json_encode(['success' => false, 'message' => implode(' ', $validation['errors'])]);
            exit;
        }

        $d = $validation['data'];
        $project = new Project($d['titre'], $d['description'], $d['budget'], $d['date_creation'], $d['statut']);
        $project->setId($d['id']);

        if ($projectController->updateProject($project)) {
            echo json_encode(['success' => true, 'message' => 'Projet modifié avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Une erreur est survenue lors de la modification.']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identifiant invalide.']);
            exit;
        }

        if ($projectController->deleteProject($id)) {
            echo json_encode(['success' => true, 'message' => 'Projet supprimé avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Suppression impossible pour le moment.']);
        }
        exit;
    }

    if ($action === 'get_project') {
        $id = (int)($_POST['id'] ?? 0);
        $project = $projectController->getProjectById($id);

        if (!$project) {
            echo json_encode(['success' => false, 'message' => 'Projet introuvable.']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'id' => $project->getId(),
            'titre' => $project->getTitre(),
            'description' => $project->getDescription(),
            'budget' => $project->getBudget(),
            'date_creation' => $project->getDateCreation(),
            'statut' => $project->getStatut(),
        ]);
        exit;
    }

    if ($action === 'accepter' || $action === 'refuser') {
        $id   = (int)($_POST['id'] ?? 0);
        $etat = ($action === 'accepter') ? 'publie' : 'refuse';
        if ($projectController->changerEtat($id, $etat)) {
            echo json_encode(['success' => true, 'message' => $action === 'accepter' ? 'Projet publié.' : 'Projet refusé.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour.']);
        }
        exit;
    }

    if ($action === 'accepter_cand' || $action === 'refuser_cand') {
        $id     = (int)($_POST['id'] ?? 0);
        $statut = ($action === 'accepter_cand') ? 'accepte' : 'refuse';
        $ok     = $candidatureController->changerStatut($id, $statut);
        echo json_encode(['success' => $ok, 'message' => $ok ? ($action === 'accepter_cand' ? 'Candidature acceptée.' : 'Candidature refusée.') : 'Erreur.']);
        exit;
    }

    if ($action === 'add_tache_admin') {
        $id_projet = (int)($_POST['id_projet'] ?? 0);
        $id_freelancer = (int)($_POST['id_freelancer'] ?? 0);
        $titre     = trim($_POST['titre'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $statut_t  = $_POST['statut'] ?? 'a_faire';
        $prix      = (float)($_POST['prix'] ?? 0);
        if (!$titre)        { echo json_encode(['success'=>false,'message'=>'Le titre est obligatoire.']); exit; }
        if (!$id_projet)    { echo json_encode(['success'=>false,'message'=>'Projet invalide.']); exit; }
        if (!$id_freelancer){ echo json_encode(['success'=>false,'message'=>'Freelancer invalide.']); exit; }
        require_once __DIR__ . '/../../Models/Tache.php';
        $tache = new Tache($id_projet, $id_freelancer, $titre, $desc, $statut_t, $prix);
        echo json_encode($candidatureController->ajouterTacheAdmin($tache));
        exit;
    }

    if ($action === 'get_freelancers_projet') {
        $id_projet = (int)($_POST['id_projet'] ?? 0);
        $list = $candidatureController->getFreelancersAcceptes($id_projet);
        echo json_encode(['success' => true, 'freelancers' => $list]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Action non reconnue.']);
    exit;
}

$search      = trim($_GET['q']          ?? '');
$statut      = trim($_GET['statut']     ?? '');
$etat_filtre = trim($_GET['etat']       ?? '');
$budget_min  = trim($_GET['budget_min'] ?? '');
$budget_max  = trim($_GET['budget_max'] ?? '');
$projects        = $projectController->listAllProjects($search, $statut, $etat_filtre, $budget_min, $budget_max);
$stats           = $projectController->getStats();
$pendingProjects = $projectController->listPendingProjects();
$candidatures    = $candidatureController->getAllCandidatures();
$taches          = $candidatureController->getAllTaches();
$pendingCands    = $candidatureController->getAllCandidatures('en_attente');

function badgeStatusClass($status)
{
    if ($status === 'en_cours') {
        return 'warning';
    }
    if ($status === 'termine') {
        return 'success';
    }
    return 'secondary';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Gestion des Projets - SkillBridge Admin</title>
    <script src="<?= BASE_URL ?>/Views/assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: { families: ["Font Awesome 5 Solid","Font Awesome 5 Regular","Font Awesome 5 Brands","simple-line-icons"], urls: ["<?= BASE_URL ?>/Views/assets/css/fonts.min.css"] },
            active: function() { sessionStorage.fonts = true; }
        });
    </script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Views/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Views/assets/css/plugins.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Views/assets/css/kaiadmin.min.css">
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
        .stats-card .card-body { min-height: 100px; }
        .stats-card h4 { font-size: 1.4rem; }
        .project-description {
            max-width: 320px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .table td,
        .table th {
            vertical-align: middle;
        }
        /* Messages d'erreur sous les inputs */
        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .is-invalid {
            border-color: #dc3545 !important;
        }
        .is-valid {
            border-color: #28a745 !important;
        }
        @media (min-width: 992px) {
            .show-sidebar-btn { display: none; }
            .wrapper.sidebar-hidden .show-sidebar-btn { display: block; }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar" data-background-color="dark">
        <div class="sidebar-logo">
            <div class="logo-header" data-background-color="dark">
                <a href="?action=home" class="logo">
                    <img src="<?= BASE_URL ?>/Views/assets/img/logo1.png" alt="SkillBridge" style="height: 30px; width: auto;">
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
                    <li class="nav-item">
                        <a href="?action=userlist">
                            <i class="fas fa-users"></i>
                            <p>Utilisateurs</p>
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a href="?action=projectlist">
                            <i class="fas fa-briefcase"></i>
                            <p>Projets</p>
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

    <div class="main-panel">
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

        <button class="btn btn-outline-secondary show-sidebar-btn" id="showSidebarBtn" title="Afficher la barre latérale">
            <i class="fas fa-bars"></i>
        </button>

        <div class="container">
            <div class="page-inner">
                <div class="page-header">
                    <h4 class="page-title">Gestion des Projets</h4>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="mb-1">Total projets</p>
                                        <h4 class="fw-bold mb-0"><?= (int)$stats['total'] ?></h4>
                                    </div>
                                    <div class="text-primary"><i class="fas fa-folder-open fa-2x"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="mb-1">En cours</p>
                                        <h4 class="fw-bold mb-0 text-warning"><?= (int)$stats['en_cours'] ?></h4>
                                    </div>
                                    <div class="text-warning"><i class="fas fa-spinner fa-2x"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="mb-1">Terminés</p>
                                        <h4 class="fw-bold mb-0 text-success"><?= (int)$stats['termine'] ?></h4>
                                    </div>
                                    <div class="text-success"><i class="fas fa-check-circle fa-2x"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="mb-1">Budget total</p>
                                        <h4 class="fw-bold mb-0"><?= number_format((float)$stats['budget_total'], 2, ',', ' ') ?> TND</h4>
                                    </div>
                                    <div class="text-info"><i class="fas fa-coins fa-2x"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($pendingProjects)): ?>
                <div class="card border-warning mb-4">
                    <div class="card-header bg-warning bg-opacity-10 d-flex align-items-center gap-2">
                        <i class="fas fa-clock text-warning"></i>
                        <h5 class="card-title mb-0 text-warning">
                            En attente de validation
                            <span class="badge bg-warning text-dark ms-2"><?= count($pendingProjects) ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Titre</th>
                                        <th>Description</th>
                                        <th>Budget</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($pendingProjects as $p): ?>
                                    <tr id="pending-row-<?= $p->getId() ?>">
                                        <td><?= htmlspecialchars($p->getTitre()) ?></td>
                                        <td class="project-description" title="<?= htmlspecialchars($p->getDescription()) ?>">
                                            <?= htmlspecialchars($p->getDescription()) ?>
                                        </td>
                                        <td><?= number_format((float)$p->getBudget(), 2, ',', ' ') ?> TND</td>
                                        <td><?= htmlspecialchars($p->getDateCreation()) ?></td>
                                        <td>
                                            <button class="btn btn-success btn-sm me-1" onclick="validerProjet(<?= $p->getId() ?>, 'accepter')">
                                                <i class="fas fa-check me-1"></i>Accepter
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="validerProjet(<?= $p->getId() ?>, 'refuser')">
                                                <i class="fas fa-times me-1"></i>Refuser
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Liste des Projets</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="hidden" name="action" value="projectlist">
                                <input type="text" name="q" class="form-control form-control-sm" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>" style="width:180px;">
                                <select name="statut" class="form-select form-select-sm" style="width:140px;">
                                    <option value="">Tous statuts</option>
                                    <option value="en_attente" <?= $statut === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                    <option value="en_cours"   <?= $statut === 'en_cours'   ? 'selected' : '' ?>>En cours</option>
                                    <option value="termine"    <?= $statut === 'termine'    ? 'selected' : '' ?>>Terminé</option>
                                </select>
                                <select name="etat" class="form-select form-select-sm" style="width:160px;">
                                    <option value="">Tous états</option>
                                    <option value="publie"                <?= $etat_filtre === 'publie'                ? 'selected' : '' ?>>Publié</option>
                                    <option value="en_attente_validation" <?= $etat_filtre === 'en_attente_validation' ? 'selected' : '' ?>>En attente valid.</option>
                                    <option value="refuse"                <?= $etat_filtre === 'refuse'                ? 'selected' : '' ?>>Refusé</option>
                                </select>
                                <input type="number" name="budget_min" class="form-control form-control-sm" placeholder="Budget min" min="0" value="<?= htmlspecialchars($budget_min) ?>" style="width:120px;">
                                <input type="number" name="budget_max" class="form-control form-control-sm" placeholder="Budget max" min="0" value="<?= htmlspecialchars($budget_max) ?>" style="width:120px;">
                                <button type="submit" class="btn btn-outline-primary btn-sm"><i class="fas fa-filter me-1"></i>Filtrer</button>
                                <?php if ($search || $statut || $etat_filtre || $budget_min || $budget_max): ?>
                                    <a href="?action=projectlist" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
                                <?php endif; ?>
                            </form>
                            <a class="btn btn-outline-danger btn-sm" href="?action=projectlist&export=pdf&q=<?= urlencode($search) ?>">
                                <i class="fas fa-file-pdf me-1"></i>Exporter PDF
                            </a>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                                <i class="fas fa-plus me-1"></i>Ajouter Projet
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Titre</th>
                                        <th>Client</th>
                                        <th>Description</th>
                                        <th>Budget</th>
                                        <th>Date création</th>
                                        <th>Statut</th>
                                        <th>État</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($projects)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Aucun projet trouvé.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($projects as $project): ?>
                                        <tr>
                                            <td><?= $project->getId() ?></td>
                                            <td><?= htmlspecialchars($project->getTitre()) ?></td>
                                            <td>
                                                <?php if ($project->getNomClient() && trim($project->getNomClient())): ?>
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="fas fa-user me-1 text-muted"></i><?= htmlspecialchars($project->getNomClient()) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted small">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="project-description" title="<?= htmlspecialchars($project->getDescription()) ?>">
                                                <?= htmlspecialchars($project->getDescription()) ?>
                                            </td>
                                            <td><?= number_format((float)$project->getBudget(), 2, ',', ' ') ?> TND</td>
                                            <td><?= htmlspecialchars($project->getDateCreation()) ?></td>
                                            <td>
                                                <span class="badge bg-<?= badgeStatusClass($project->getStatut()) ?>">
                                                    <?= htmlspecialchars($project->getStatut()) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $etatBadge = ['publie' => 'success', 'en_attente_validation' => 'warning', 'refuse' => 'danger'];
                                                $etatLabel = ['publie' => 'Publié', 'en_attente_validation' => 'En attente', 'refuse' => 'Refusé'];
                                                $e = $project->getEtat();
                                                ?>
                                                <span class="badge bg-<?= $etatBadge[$e] ?? 'secondary' ?>">
                                                    <?= $etatLabel[$e] ?? $e ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 flex-nowrap">
                                                    <button class="btn btn-sm btn-warning" onclick="editProject(<?= $project->getId() ?>)" data-bs-toggle="modal" data-bs-target="#editProjectModal" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-info text-white" onclick="openAddTache(<?= $project->getId() ?>, <?= htmlspecialchars(json_encode($project->getTitre())) ?>)" title="Ajouter une tâche">
                                                        <i class="fas fa-plus"></i> Tâche
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteProject(<?= $project->getId() ?>)" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── CANDIDATURES ─────────────────────────────────────────── -->
        <div class="container mt-2">
            <div class="page-inner">

                <!-- Badge en attente -->
                <?php if (!empty($pendingCands)): ?>
                <div class="card border-warning mb-4">
                    <div class="card-header bg-warning bg-opacity-10 d-flex align-items-center gap-2">
                        <i class="fas fa-user-clock text-warning"></i>
                        <h5 class="card-title mb-0 text-warning">
                            Candidatures en attente
                            <span class="badge bg-warning text-dark ms-2"><?= count($pendingCands) ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr><th>Freelancer</th><th>Projet</th><th>Date</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                <?php foreach ($pendingCands as $c): ?>
                                <tr id="cand-row-<?= $c->getId() ?>">
                                    <td><?= htmlspecialchars($c->getPrenomFreelancer().' '.$c->getNomFreelancer()) ?></td>
                                    <td><?= htmlspecialchars($c->getTitreProjet()) ?></td>
                                    <td><?= date('d/m/Y', strtotime($c->getCreatedAt())) ?></td>
                                    <td>
                                        <button class="btn btn-success btn-sm me-1" onclick="validerCand(<?= $c->getId() ?>, 'accepter_cand')">
                                            <i class="fas fa-check me-1"></i>Accepter
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="validerCand(<?= $c->getId() ?>, 'refuser_cand')">
                                            <i class="fas fa-times me-1"></i>Refuser
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Toutes les candidatures -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="fas fa-paper-plane me-2"></i>Toutes les Candidatures</h5>
                        <span class="badge bg-secondary"><?= count($candidatures) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr><th>Freelancer</th><th>Projet</th><th>Date</th><th>Statut</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                <?php if (empty($candidatures)): ?>
                                    <tr><td colspan="5" class="text-center text-muted">Aucune candidature.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($candidatures as $c):
                                        $badges = ['en_attente'=>'warning','accepte'=>'success','refuse'=>'danger'];
                                        $labels = ['en_attente'=>'En attente','accepte'=>'Acceptée','refuse'=>'Refusée'];
                                        $s = $c->getStatut();
                                    ?>
                                    <tr id="cand-row-<?= $c->getId() ?>">
                                        <td><?= htmlspecialchars($c->getPrenomFreelancer().' '.$c->getNomFreelancer()) ?></td>
                                        <td><?= htmlspecialchars($c->getTitreProjet()) ?></td>
                                        <td><?= date('d/m/Y', strtotime($c->getCreatedAt())) ?></td>
                                        <td><span class="badge bg-<?= $badges[$s]??'secondary' ?>"><?= $labels[$s]??$s ?></span></td>
                                        <td>
                                            <?php if ($s === 'en_attente'): ?>
                                            <button class="btn btn-success btn-sm me-1" onclick="validerCand(<?= $c->getId() ?>, 'accepter_cand')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="validerCand(<?= $c->getId() ?>, 'refuser_cand')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php else: ?>
                                            <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tâches des freelancers -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="fas fa-tasks me-2"></i>Tâches des Freelancers</h5>
                        <span class="badge bg-secondary"><?= count($taches) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr><th>Freelancer</th><th>Projet</th><th>Tâche</th><th>Description</th><th>Statut</th><th>Date</th></tr>
                                </thead>
                                <tbody>
                                <?php if (empty($taches)): ?>
                                    <tr><td colspan="6" class="text-center text-muted">Aucune tâche.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($taches as $t):
                                        $tb = ['a_faire'=>'secondary','en_cours'=>'warning','termine'=>'success'];
                                        $tl = ['a_faire'=>'À faire','en_cours'=>'En cours','termine'=>'Terminé'];
                                        $ts = $t['statut'];
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($t['prenom_freelancer'].' '.$t['nom_freelancer']) ?></td>
                                        <td><?= htmlspecialchars($t['titre_projet']) ?></td>
                                        <td><?= htmlspecialchars($t['titre']) ?></td>
                                        <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($t['description']??'') ?></td>
                                        <td><span class="badge bg-<?= $tb[$ts]??'secondary' ?>"><?= $tl[$ts]??$ts ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($t['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <footer class="footer">
            <div class="container-fluid d-flex justify-content-between">
                <div class="copyright">2026 © SkillBridge</div>
            </div>
        </footer>
    </div>
</div>

<div class="modal fade" id="addProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un projet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addProjectForm" novalidate>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Titre</label>
                            <input type="text" name="titre" id="add_titre" class="form-control">
                            <div class="invalid-feedback" id="err_add_titre"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Budget (TND)</label>
                            <input type="text" name="budget" id="add_budget" class="form-control">
                            <div class="invalid-feedback" id="err_add_budget"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date de création</label>
                            <input type="date" name="date_creation" id="add_date" class="form-control" value="<?= date('Y-m-d') ?>">
                            <div class="invalid-feedback" id="err_add_date"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Statut</label>
                            <select name="statut" id="add_statut" class="form-select">
                                <option value="">-- Choisir --</option>
                                <option value="en_attente">En attente</option>
                                <option value="en_cours">En cours</option>
                                <option value="termine">Terminé</option>
                            </select>
                            <div class="invalid-feedback" id="err_add_statut"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="add_description" class="form-control" rows="4"></textarea>
                            <div class="invalid-feedback" id="err_add_description"></div>
                        </div>
                    </div>
                    <div id="addProjectMsg" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier un projet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editProjectForm">
                <input type="hidden" name="id" id="editProjectId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Titre</label>
                            <input type="text" name="titre" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Budget</label>
                            <input type="number" step="0.01" min="0" name="budget" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date de création</label>
                            <input type="date" name="date_creation" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Statut</label>
                            <select name="statut" class="form-select" required>
                                <option value="en_attente">en_attente</option>
                                <option value="en_cours">en_cours</option>
                                <option value="termine">termine</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div id="editProjectMsg" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajouter Tâche (Admin) -->
<div class="modal fade" id="addTacheAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une tâche</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTacheAdminForm" novalidate>
                <input type="hidden" id="at_id_projet" name="id_projet">
                <div class="modal-body">
                    <p class="text-muted small mb-3" id="at_projet_label"></p>
                    <div class="mb-3">
                        <label class="form-label">Freelancer assigné</label>
                        <select id="at_id_freelancer" name="id_freelancer" class="form-select">
                            <option value="">Chargement...</option>
                        </select>
                        <div class="invalid-feedback" id="err_at_freelancer"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Titre <span class="text-danger">*</span></label>
                        <input type="text" id="at_titre" name="titre" class="form-control" placeholder="Titre de la tâche">
                        <div class="invalid-feedback" id="err_at_titre"></div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Statut</label>
                            <select id="at_statut" name="statut" class="form-select">
                                <option value="a_faire">À faire</option>
                                <option value="en_cours">En cours</option>
                                <option value="termine">Terminé</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prix (TND)</label>
                            <input type="number" id="at_prix" name="prix" class="form-control" min="0" step="0.01" placeholder="0.00">
                            <div class="invalid-feedback" id="err_at_prix"></div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Description</label>
                        <textarea id="at_description" name="description" class="form-control" rows="3" placeholder="Description (optionnel)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/Views/assets/js/core/jquery-3.7.1.min.js"></script>
<script src="<?= BASE_URL ?>/Views/assets/js/core/popper.min.js"></script>
<script src="<?= BASE_URL ?>/Views/assets/js/core/bootstrap.min.js"></script>
<script src="<?= BASE_URL ?>/Views/assets/js/kaiadmin.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .swal2-popup { font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", Helvetica, Arial, sans-serif; }
</style>

<script>
// ── Helpers ───────────────────────────────────────────────────────────
function swalSuccess(msg) {
    Swal.fire({ icon: 'success', title: 'Succès', text: msg, timer: 1500, showConfirmButton: false });
}
function swalError(msg) {
    Swal.fire({ icon: 'error', title: 'Erreur', text: msg });
}

// ── Ajout projet ──────────────────────────────────────────────────────
document.getElementById('addProjectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let valid = true;

    const titre       = document.getElementById('add_titre');
    const budget      = document.getElementById('add_budget');
    const date        = document.getElementById('add_date');
    const statut      = document.getElementById('add_statut');
    const description = document.getElementById('add_description');

    [titre, budget, date, statut, description].forEach(f => f.classList.remove('is-invalid', 'is-valid'));

    if (titre.value.trim() === '') {
        titre.classList.add('is-invalid');
        document.getElementById('err_add_titre').textContent = 'Le titre est obligatoire.';
        valid = false;
    } else if (titre.value.trim().length > 150) {
        titre.classList.add('is-invalid');
        document.getElementById('err_add_titre').textContent = 'Maximum 150 caractères.';
        valid = false;
    } else { titre.classList.add('is-valid'); }

    const bv = budget.value.trim();
    if (bv === '') {
        budget.classList.add('is-invalid');
        document.getElementById('err_add_budget').textContent = 'Le budget est obligatoire.';
        valid = false;
    } else if (isNaN(bv) || parseFloat(bv) < 0) {
        budget.classList.add('is-invalid');
        document.getElementById('err_add_budget').textContent = 'Entrez un nombre positif.';
        valid = false;
    } else { budget.classList.add('is-valid'); }

    if (date.value === '') {
        date.classList.add('is-invalid');
        document.getElementById('err_add_date').textContent = 'La date est obligatoire.';
        valid = false;
    } else { date.classList.add('is-valid'); }

    if (statut.value === '') {
        statut.classList.add('is-invalid');
        document.getElementById('err_add_statut').textContent = 'Veuillez choisir un statut.';
        valid = false;
    } else { statut.classList.add('is-valid'); }

    if (description.value.trim() === '') {
        description.classList.add('is-invalid');
        document.getElementById('err_add_description').textContent = 'La description est obligatoire.';
        valid = false;
    } else if (description.value.trim().length > 2000) {
        description.classList.add('is-invalid');
        document.getElementById('err_add_description').textContent = 'Maximum 2000 caractères.';
        valid = false;
    } else { description.classList.add('is-valid'); }

    if (!valid) return;

    const formData = new FormData(this);
    formData.append('action', 'add');

    fetch('?action=projectlist', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addProjectModal')).hide();
            Swal.fire({ icon: 'success', title: 'Succès', text: data.message, timer: 1500, showConfirmButton: false })
                .then(() => location.reload());
        } else {
            swalError(data.message);
        }
    })
    .catch(() => swalError('Erreur réseau. Réessayez.'));
});

// ── Modifier projet ───────────────────────────────────────────────────
function editProject(id) {
    const formData = new FormData();
    formData.append('action', 'get_project');
    formData.append('id', id);

    fetch('?action=projectlist', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { swalError(data.message || 'Projet introuvable.'); return; }
        document.getElementById('editProjectId').value = data.id;
        document.querySelector('#editProjectForm input[name="titre"]').value = data.titre;
        document.querySelector('#editProjectForm textarea[name="description"]').value = data.description;
        document.querySelector('#editProjectForm input[name="budget"]').value = data.budget;
        document.querySelector('#editProjectForm input[name="date_creation"]').value = data.date_creation;
        document.querySelector('#editProjectForm select[name="statut"]').value = data.statut;
    })
    .catch(() => swalError('Erreur réseau. Réessayez.'));
}

document.getElementById('editProjectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'edit');

    fetch('?action=projectlist', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editProjectModal')).hide();
            Swal.fire({ icon: 'success', title: 'Modifié', text: data.message, timer: 1500, showConfirmButton: false })
                .then(() => location.reload());
        } else {
            swalError(data.message);
        }
    })
    .catch(() => swalError('Erreur réseau. Réessayez.'));
});

// ── Supprimer projet ──────────────────────────────────────────────────
function deleteProject(id) {
    Swal.fire({
        title: 'Supprimer ce projet ?',
        text: 'Cette action est irréversible.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then(result => {
        if (!result.isConfirmed) return;
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        fetch('?action=projectlist', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Supprimé', timer: 1200, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                swalError(data.message || 'Suppression impossible.');
            }
        })
        .catch(() => swalError('Erreur réseau.'));
    });
}

// ── Valider / Refuser projet ──────────────────────────────────────────
function validerProjet(id, action) {
    const isAccept = action === 'accepter';
    Swal.fire({
        title: isAccept ? 'Publier ce projet ?' : 'Refuser ce projet ?',
        text: isAccept ? 'Le projet sera visible sur le frontoffice.' : 'Le projet sera masqué.',
        icon: isAccept ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonColor: isAccept ? '#27ae60' : '#e74c3c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: isAccept ? 'Oui, publier' : 'Oui, refuser',
        cancelButtonText: 'Annuler'
    }).then(result => {
        if (!result.isConfirmed) return;
        const formData = new FormData();
        formData.append('action', action);
        formData.append('id', id);
        fetch('?action=projectlist', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: isAccept ? 'Publié !' : 'Refusé', timer: 1200, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                swalError(data.message || 'Erreur.');
            }
        })
        .catch(() => swalError('Erreur réseau.'));
    });
}

// ── Valider candidature ───────────────────────────────────────────────
function validerCand(id, action) {
    const isAccept = action === 'accepter_cand';
    Swal.fire({
        title: isAccept ? 'Accepter cette candidature ?' : 'Refuser cette candidature ?',
        text: isAccept ? 'Le freelancer pourra ajouter des tâches sur ce projet.' : 'La candidature sera refusée.',
        icon: isAccept ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonColor: isAccept ? '#27ae60' : '#e74c3c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: isAccept ? 'Oui, accepter' : 'Oui, refuser',
        cancelButtonText: 'Annuler'
    }).then(r => {
        if (!r.isConfirmed) return;
        const fd = new FormData();
        fd.append('action', action);
        fd.append('id', id);
        fetch('?action=projectlist', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: isAccept ? 'Acceptée !' : 'Refusée', timer: 1200, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                swalError(data.message || 'Erreur.');
            }
        })
        .catch(() => swalError('Erreur réseau.'));
    });
}

const toggleSidebarBtn = document.querySelector('.toggle-sidebar');
const showSidebarBtn   = document.getElementById('showSidebarBtn');

document.getElementById('addProjectModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('addProjectForm');
    form.reset();
    form.querySelectorAll('.is-invalid, .is-valid').forEach(el => el.classList.remove('is-invalid', 'is-valid'));
    document.getElementById('addProjectMsg').innerHTML = '';
});

if (toggleSidebarBtn) toggleSidebarBtn.addEventListener('click', () => wrapper.classList.toggle('sidebar-hidden'));
if (showSidebarBtn)   showSidebarBtn.addEventListener('click',   () => wrapper.classList.remove('sidebar-hidden'));

// ── Ajouter tâche (Admin) ─────────────────────────────────────────────
function openAddTache(idProjet, titreProjet) {
    document.getElementById('at_id_projet').value = idProjet;
    document.getElementById('at_projet_label').textContent = 'Projet : ' + titreProjet;
    document.getElementById('at_titre').value = '';
    document.getElementById('at_description').value = '';
    document.getElementById('at_prix').value = '';
    document.getElementById('at_statut').value = 'a_faire';
    ['at_titre','at_id_freelancer','at_prix'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('is-invalid');
    });
    const sel = document.getElementById('at_id_freelancer');
    sel.innerHTML = '<option value="">Chargement...</option>';
    const fd = new FormData();
    fd.append('action', 'get_freelancers_projet');
    fd.append('id_projet', idProjet);
    fetch('?action=projectlist', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.freelancers.length > 0) {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                data.freelancers.map(f => `<option value="${f.id}">${f.prenom} ${f.nom}</option>`).join('');
        } else {
            sel.innerHTML = '<option value="">Aucun freelancer accepté sur ce projet</option>';
        }
    })
    .catch(() => { sel.innerHTML = '<option value="">Erreur de chargement</option>'; });
    new bootstrap.Modal(document.getElementById('addTacheAdminModal')).show();
}

document.getElementById('addTacheAdminForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let valid = true;
    const titre = document.getElementById('at_titre');
    const freelancer = document.getElementById('at_id_freelancer');
    const prix = document.getElementById('at_prix');
    [titre, freelancer, prix].forEach(el => el.classList.remove('is-invalid'));
    if (titre.value.trim() === '') {
        titre.classList.add('is-invalid');
        document.getElementById('err_at_titre').textContent = 'Le titre est obligatoire.';
        valid = false;
    }
    if (!freelancer.value) {
        freelancer.classList.add('is-invalid');
        document.getElementById('err_at_freelancer').textContent = 'Choisissez un freelancer.';
        valid = false;
    }
    if (prix.value !== '' && (isNaN(prix.value) || parseFloat(prix.value) < 0)) {
        prix.classList.add('is-invalid');
        document.getElementById('err_at_prix').textContent = 'Prix invalide.';
        valid = false;
    }
    if (!valid) return;
    const fd = new FormData(this);
    fd.append('action', 'add_tache_admin');
    fetch('?action=projectlist', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addTacheAdminModal')).hide();
            Swal.fire({ icon:'success', title:'Tâche ajoutée !', timer:1500, showConfirmButton:false })
                .then(() => location.reload());
        } else {
            swalError(data.message);
        }
    })
    .catch(() => swalError('Erreur réseau.'));
});
</script>
</body>
</html>
