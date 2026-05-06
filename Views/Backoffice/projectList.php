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
        if (!$validation['is_valid']) { echo json_encode(['success' => false, 'message' => implode(' ', $validation['errors'])]); exit; }
        $d = $validation['data'];
        $etat = ($_SESSION['user_role'] == 1) ? 'publie' : 'en_attente_validation';
        $id_client = ($_SESSION['user_role'] != 1) ? $_SESSION['user_id'] : null;
        $project = new Project($d['titre'], $d['description'], $d['budget'], $d['date_creation'], $d['statut'], $etat, $id_client);
        if ($projectController->addProject($project)) { echo json_encode(['success' => true, 'message' => 'Projet ajouté avec succès.']); } else { echo json_encode(['success' => false, 'message' => 'Une erreur est survenue.']); }
        exit;
    }
    if ($action === 'edit') {
        $validation = $projectController->validateProjectInput($_POST, true);
        if (!$validation['is_valid']) { echo json_encode(['success' => false, 'message' => implode(' ', $validation['errors'])]); exit; }
        $d = $validation['data'];
        $project = new Project($d['titre'], $d['description'], $d['budget'], $d['date_creation'], $d['statut']);
        $project->setId($d['id']);
        if ($projectController->updateProject($project)) { echo json_encode(['success' => true, 'message' => 'Projet modifié avec succès.']); } else { echo json_encode(['success' => false, 'message' => 'Erreur.']); }
        exit;
    }
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($projectController->deleteProject($id)) { echo json_encode(['success' => true, 'message' => 'Projet supprimé.']); } else { echo json_encode(['success' => false, 'message' => 'Suppression impossible.']); }
        exit;
    }
    if ($action === 'get_project') {
        $id = (int)($_POST['id'] ?? 0);
        $project = $projectController->getProjectById($id);
        if (!$project) { echo json_encode(['success' => false, 'message' => 'Projet introuvable.']); exit; }
        echo json_encode(['success' => true, 'id' => $project->getId(), 'titre' => $project->getTitre(), 'description' => $project->getDescription(), 'budget' => $project->getBudget(), 'date_creation' => $project->getDateCreation(), 'statut' => $project->getStatut()]);
        exit;
    }

    if ($action === 'get_project_details') {
        $id = (int)($_POST['id'] ?? 0);
        $project = $projectController->getProjectById($id);
        if (!$project) { echo json_encode(['success' => false, 'message' => 'Projet introuvable.']); exit; }
        // Récupérer les tâches du projet
        $tachesProjet = $candidatureController->getTachesProjet($id);
        $tachesData = array_map(function($t) {
            return [
                'id'               => $t['id'],
                'titre'            => $t['titre'],
                'description'      => $t['description'] ?? '',
                'statut'           => $t['statut'],
                'prix'             => (float)($t['prix'] ?? 0),
                'payee'            => (bool)($t['payee'] ?? false),
                'nom_freelancer'   => $t['nom_freelancer'] ?? '',
                'prenom_freelancer'=> $t['prenom_freelancer'] ?? '',
                'created_at'       => $t['created_at'] ?? '',
            ];
        }, $tachesProjet);
        echo json_encode([
            'success'     => true,
            'id'          => $project->getId(),
            'titre'       => $project->getTitre(),
            'description' => $project->getDescription(),
            'budget'      => (float)$project->getBudget(),
            'date_creation'=> $project->getDateCreation(),
            'statut'      => $project->getStatut(),
            'etat'        => $project->getEtat(),
            'avancement'  => $project->getAvancement(),
            'nom_client'  => $project->getNomClient(),
            'taches'      => $tachesData,
        ]);
        exit;
    }
    if ($action === 'accepter' || $action === 'refuser') {
        $id = (int)($_POST['id'] ?? 0);
        $etat = ($action === 'accepter') ? 'publie' : 'refuse';
        if ($projectController->changerEtat($id, $etat)) { echo json_encode(['success' => true, 'message' => $action === 'accepter' ? 'Projet publié.' : 'Projet refusé.']); } else { echo json_encode(['success' => false, 'message' => 'Erreur.']); }
        exit;
    }
    if ($action === 'accepter_cand' || $action === 'refuser_cand') {
        $id = (int)($_POST['id'] ?? 0);
        $statut = ($action === 'accepter_cand') ? 'accepte' : 'refuse';
        $ok = $candidatureController->changerStatut($id, $statut);
        echo json_encode(['success' => $ok, 'message' => $ok ? ($action === 'accepter_cand' ? 'Candidature acceptée.' : 'Candidature refusée.') : 'Erreur.']);
        exit;
    }
    if ($action === 'add_tache_admin') {
        $id_projet = (int)($_POST['id_projet'] ?? 0);
        $id_freelancer = (int)($_POST['id_freelancer'] ?? 0);
        $titre = trim($_POST['titre'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $statut_t = $_POST['statut'] ?? 'a_faire';
        $prix = (float)($_POST['prix'] ?? 0);
        if (!$titre) { echo json_encode(['success'=>false,'message'=>'Le titre est obligatoire.']); exit; }
        if (!$id_projet) { echo json_encode(['success'=>false,'message'=>'Projet invalide.']); exit; }
        if (!$id_freelancer) { echo json_encode(['success'=>false,'message'=>'Freelancer invalide.']); exit; }
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
    if ($action === 'edit_tache_admin') {
        $id = (int)($_POST['id'] ?? 0);
        $titre = trim($_POST['titre'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $stat = $_POST['statut'] ?? 'a_faire';
        $prix = (float)($_POST['prix'] ?? 0);
        if (!$titre) { echo json_encode(['success'=>false,'message'=>'Le titre est obligatoire.']); exit; }
        echo json_encode($candidatureController->modifierTacheAdmin($id, $titre, $desc, $stat, $prix));
        exit;
    }
    if ($action === 'delete_tache_admin') {
        $id = (int)($_POST['id'] ?? 0);
        echo json_encode($candidatureController->supprimerTacheAdmin($id));
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

$db = Config::getConnexion();
$q = $db->query("SELECT COUNT(*) AS total, SUM(CASE WHEN id_role=2 THEN 1 ELSE 0 END) AS clients, SUM(CASE WHEN id_role=3 THEN 1 ELSE 0 END) AS freelancers FROM user WHERE id_role != 1");
$statsUsers = $q->fetch();
$q = $db->query("SELECT COUNT(*) AS total, SUM(CASE WHEN statut='termine' THEN 1 ELSE 0 END) AS terminees, SUM(CASE WHEN statut='en_cours' THEN 1 ELSE 0 END) AS en_cours, SUM(CASE WHEN statut='a_faire' THEN 1 ELSE 0 END) AS a_faire, COALESCE(SUM(prix),0) AS total_prix FROM tache");
$statsTaches = $q->fetch();
$q = $db->query("SELECT COUNT(*) AS total, SUM(CASE WHEN statut='accepte' THEN 1 ELSE 0 END) AS acceptees, SUM(CASE WHEN statut='en_attente' THEN 1 ELSE 0 END) AS en_attente, SUM(CASE WHEN statut='refuse' THEN 1 ELSE 0 END) AS refusees FROM candidature");
$statsCands = $q->fetch();
$q = $db->query("SELECT COUNT(*) AS total_payees, COALESCE(SUM(prix),0) AS montant_paye FROM tache WHERE payee=1");
$statsPaiements = $q->fetch();
$q = $db->query("SELECT DATE_FORMAT(date_creation,'%Y-%m') AS mois, COUNT(*) AS nb FROM projet WHERE date_creation >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY mois ORDER BY mois ASC");
$projetsParMois = $q->fetchAll();
$moisLabels = array_column($projetsParMois, 'mois');
$moisData   = array_column($projetsParMois, 'nb');

function badgeStatusClass($status) {
    if ($status === 'en_cours') return 'warning';
    if ($status === 'termine') return 'success';
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
        @media (min-width: 992px) {
            .show-sidebar-btn { display: none; }
            .wrapper.sidebar-hidden .show-sidebar-btn { display: block; }
        }
        .stats-card { border-radius: 14px; background: #fff; }
        .project-description {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .invalid-feedback { display: none; }
        .is-invalid ~ .invalid-feedback,
        .is-invalid + .invalid-feedback { display: block; }
        .is-invalid { border-color: #dc3545 !important; }
        .is-valid { border-color: #198754 !important; }
    </style>
</head>
<body>
<div class="wrapper" id="wrapper">

    <!-- Sidebar -->
    <div class="sidebar" data-background-color="dark">
        <div class="sidebar-logo">
            <div class="logo-header" data-background-color="dark">
                <a href="?action=home" class="logo">
                    <img src="<?= BASE_URL ?>/Views/assets/img/logo1.png" alt="SkillBridge" style="height:30px;width:auto;">
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
    <!-- End Sidebar -->

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
                        <span class="nav-link" style="color:#2c3e50;">👤 <?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?></span>
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
                    <h4 class="page-title">Gestion des Projets</h4>
                </div>

                <!-- STATS SECTION -->
                <div class="row g-3 mb-3">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;background:#fff;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <p class="text-uppercase fw-bold mb-0" style="font-size:.7rem;letter-spacing:.07em;color:#6b7280;">Total Projets</p>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:#ede9fe;"><i class="fas fa-folder-open" style="color:#7c3aed;font-size:.85rem;"></i></div>
                                </div>
                                <h2 class="fw-bold mb-0" style="font-size:1.8rem;color:#1e1b4b;"><?= (int)$stats['total'] ?></h2>
                                <small class="text-muted"><?= (int)$stats['en_attente_validation'] ?> en attente validation</small>
                                <hr class="my-2" style="border-color:#f0eeff;">
                                <div class="d-flex justify-content-between" style="font-size:.78rem;"><span class="text-muted">En cours</span><span class="fw-bold" style="color:#f59e0b;"><?= (int)$stats['en_cours'] ?></span></div>
                                <div class="d-flex justify-content-between" style="font-size:.78rem;"><span class="text-muted">Terminés</span><span class="fw-bold" style="color:#10b981;"><?= (int)$stats['termine'] ?></span></div>
                                <div class="d-flex justify-content-between" style="font-size:.78rem;"><span class="text-muted">En attente</span><span class="fw-bold" style="color:#9ca3af;"><?= (int)$stats['en_attente'] ?></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;background:#fff;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <p class="text-uppercase fw-bold mb-0" style="font-size:.7rem;letter-spacing:.07em;color:#6b7280;">Utilisateurs</p>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:#dcfce7;"><i class="fas fa-users" style="color:#10b981;font-size:.85rem;"></i></div>
                                </div>
                                <h2 class="fw-bold mb-0" style="font-size:1.8rem;color:#1e1b4b;"><?= (int)$statsUsers['total'] ?></h2>
                                <small class="text-muted">inscrits</small>
                                <hr class="my-2" style="border-color:#f0eeff;">
                                <div class="d-flex justify-content-between" style="font-size:.78rem;"><span class="text-muted">Clients</span><span class="fw-bold" style="color:#7c3aed;"><?= (int)$statsUsers['clients'] ?></span></div>
                                <div class="d-flex justify-content-between" style="font-size:.78rem;"><span class="text-muted">Freelancers</span><span class="fw-bold" style="color:#f59e0b;"><?= (int)$statsUsers['freelancers'] ?></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;background:#fff;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <p class="text-uppercase fw-bold mb-0" style="font-size:.7rem;letter-spacing:.07em;color:#6b7280;">Tâches</p>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:#fef3c7;"><i class="fas fa-tasks" style="color:#f59e0b;font-size:.85rem;"></i></div>
                                </div>
                                <h2 class="fw-bold mb-0" style="font-size:1.8rem;color:#1e1b4b;"><?= (int)$statsTaches['total'] ?></h2>
                                <small class="text-muted"><?= number_format((float)$statsTaches['total_prix'],2,',',' ') ?> TND alloués</small>
                                <hr class="my-2" style="border-color:#f0eeff;">
                                <div class="d-flex justify-content-between" style="font-size:.78rem;"><span class="text-muted">À faire</span><span class="fw-bold" style="color:#9ca3af;"><?= (int)$statsTaches['a_faire'] ?></span></div>
                                <div class="d-flex justify-content-between" style="font-size:.78rem;"><span class="text-muted">En cours</span><span class="fw-bold" style="color:#f59e0b;"><?= (int)$statsTaches['en_cours'] ?></span></div>
                                <div class="d-flex justify-content-between" style="font-size:.78rem;"><span class="text-muted">Terminées</span><span class="fw-bold" style="color:#10b981;"><?= (int)$statsTaches['terminees'] ?></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;background:#fff;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <p class="text-uppercase fw-bold mb-0" style="font-size:.7rem;letter-spacing:.07em;color:#6b7280;">Finances</p>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:#dbeafe;"><i class="fas fa-coins" style="color:#3b82f6;font-size:.85rem;"></i></div>
                                </div>
                                <h2 class="fw-bold mb-0" style="font-size:1.8rem;color:#1e1b4b;"><?= number_format((float)$stats['budget_total'],0,',',' ') ?></h2>
                                <small class="text-muted">TND budget total</small>
                                <hr class="my-2" style="border-color:#f0eeff;">
                                <div class="d-flex justify-content-between" style="font-size:.78rem;"><span class="text-muted">Tâches payées</span><span class="fw-bold" style="color:#10b981;"><?= (int)$statsPaiements['total_payees'] ?></span></div>
                                <div class="d-flex justify-content-between" style="font-size:.78rem;"><span class="text-muted">Montant payé</span><span class="fw-bold" style="color:#10b981;"><?= number_format((float)$statsPaiements['montant_paye'],2,',',' ') ?> TND</span></div>
                                <div class="d-flex justify-content-between" style="font-size:.78rem;"><span class="text-muted">Candidatures</span><span class="fw-bold" style="color:#7c3aed;"><?= (int)$statsCands['total'] ?></span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm" style="border-radius:14px;">
                            <div class="card-header bg-white border-0 pb-0 pt-3 px-3">
                                <h6 class="fw-bold mb-0" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.06em;color:#7c3aed;"><i class="fas fa-chart-bar me-2"></i>Projets créés (6 derniers mois)</h6>
                            </div>
                            <div class="card-body px-3 pb-3"><canvas id="chartProjets" height="100"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm" style="border-radius:14px;">
                            <div class="card-header bg-white border-0 pb-0 pt-3 px-3">
                                <h6 class="fw-bold mb-0" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.06em;color:#10b981;"><i class="fas fa-chart-pie me-2"></i>Candidatures</h6>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center px-3 pb-3"><canvas id="chartCandidatures" height="180"></canvas></div>
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
                                            <td>
                                                <a href="#" class="fw-semibold text-decoration-none" style="color:#1e1b4b;" onclick="voirProjet(<?= $project->getId() ?>); return false;">
                                                    <?= htmlspecialchars($project->getTitre()) ?>
                                                </a>
                                            </td>
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
        <!-- End Projects Container -->

        <!-- CANDIDATURES SECTION -->
        <div class="container mt-2">
            <div class="page-inner">

                <!-- Candidatures en attente -->
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
                                    <tr><th>Freelancer</th><th>Projet</th><th>Tâche</th><th>Description</th><th>Statut</th><th>Prix</th><th>Date</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                <?php if (empty($taches)): ?>
                                    <tr><td colspan="8" class="text-center text-muted">Aucune tâche.</td></tr>
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
                                        <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($t['description']??'') ?></td>
                                        <td><span class="badge bg-<?= $tb[$ts]??'secondary' ?>"><?= $tl[$ts]??$ts ?></span></td>
                                        <td><?= !empty($t['prix']) && (float)$t['prix'] > 0 ? number_format((float)$t['prix'],2,',',' ').' TND' : '—' ?></td>
                                        <td><?= date('d/m/Y', strtotime($t['created_at'])) ?></td>
                                        <td>
                                            <div class="d-flex gap-1 flex-nowrap">
                                                <button class="btn btn-warning btn-sm" title="Modifier"
                                                    onclick="openEditTacheAdmin(<?= $t['id'] ?>, <?= htmlspecialchars(json_encode($t['titre'])) ?>, <?= htmlspecialchars(json_encode($t['description']??'')) ?>, '<?= $t['statut'] ?>', <?= (float)($t['prix']??0) ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm" title="Supprimer"
                                                    onclick="deleteTacheAdmin(<?= $t['id'] ?>)">
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

<!-- Modal Modifier Tâche (Admin) -->
<div class="modal fade" id="editTacheAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Modifier la tâche</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTacheAdminForm" novalidate>
                <input type="hidden" id="eta_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Titre <span class="text-danger">*</span></label>
                        <input type="text" id="eta_titre" name="titre" class="form-control" placeholder="Titre de la tâche">
                        <div class="invalid-feedback" id="err_eta_titre"></div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Statut</label>
                            <select id="eta_statut" name="statut" class="form-select">
                                <option value="a_faire">À faire</option>
                                <option value="en_cours">En cours</option>
                                <option value="termine">Terminé</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prix (TND) <span class="text-danger">*</span></label>
                            <input type="number" id="eta_prix" name="prix" class="form-control" min="0" step="0.01" placeholder="0.00">
                            <div class="invalid-feedback" id="err_eta_prix"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea id="eta_description" name="description" class="form-control" rows="3" placeholder="Description de la tâche"></textarea>
                        <div class="invalid-feedback" id="err_eta_description"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
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

<!-- Modal Détails Projet -->
<div class="modal fade" id="projetDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#1e1b4b,#3730a3);color:#fff;">
                <div>
                    <h5 class="modal-title mb-0" id="pd_titre">—</h5>
                    <small id="pd_client" class="opacity-75"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Infos générales -->
                <div class="p-4 border-bottom" style="background:#f8f9ff;">
                    <div class="row g-3">
                        <div class="col-sm-3 text-center">
                            <div class="small text-muted text-uppercase fw-bold mb-1">Budget</div>
                            <div class="fw-bold fs-5" style="color:#7c3aed;" id="pd_budget">—</div>
                        </div>
                        <div class="col-sm-3 text-center">
                            <div class="small text-muted text-uppercase fw-bold mb-1">Statut</div>
                            <div id="pd_statut">—</div>
                        </div>
                        <div class="col-sm-3 text-center">
                            <div class="small text-muted text-uppercase fw-bold mb-1">État</div>
                            <div id="pd_etat">—</div>
                        </div>
                        <div class="col-sm-3 text-center">
                            <div class="small text-muted text-uppercase fw-bold mb-1">Avancement</div>
                            <div class="fw-bold fs-5" style="color:#f59e0b;" id="pd_avancement">—</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="small text-muted text-uppercase fw-bold mb-1">Description</div>
                        <p class="mb-0" id="pd_description" style="color:#374151;line-height:1.6;">—</p>
                    </div>
                </div>
                <!-- Tâches -->
                <div class="p-4">
                    <h6 class="fw-bold mb-3" style="color:#1e1b4b;"><i class="fas fa-tasks me-2" style="color:#7c3aed;"></i>Tâches <span class="badge bg-secondary ms-1" id="pd_taches_count">0</span></h6>
                    <div id="pd_taches_content">
                        <p class="text-muted text-center py-3">Aucune tâche.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <a href="?action=projectlist" class="btn btn-outline-primary btn-sm">Voir tous les projets</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

// ── Voir détails projet ───────────────────────────────────────────────
function voirProjet(id) {
    const fd = new FormData();
    fd.append('action', 'get_project_details');
    fd.append('id', id);
    fetch('?action=projectlist', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { swalError(data.message || 'Erreur.'); return; }

        document.getElementById('pd_titre').textContent = data.titre;
        document.getElementById('pd_client').textContent = data.nom_client ? '👤 ' + data.nom_client : '';
        document.getElementById('pd_budget').textContent = parseFloat(data.budget).toLocaleString('fr-FR', {minimumFractionDigits:2}) + ' TND';
        document.getElementById('pd_avancement').textContent = data.avancement + '%';
        document.getElementById('pd_description').textContent = data.description || '—';

        // Statut badge
        const statutColors = { en_cours: '#f59e0b', termine: '#10b981', en_attente: '#9ca3af' };
        const statutLabels = { en_cours: 'En cours', termine: 'Terminé', en_attente: 'En attente' };
        document.getElementById('pd_statut').innerHTML = `<span class="badge" style="background:${statutColors[data.statut]||'#9ca3af'}">${statutLabels[data.statut]||data.statut}</span>`;

        // Etat badge
        const etatColors = { publie: '#10b981', en_attente_validation: '#f59e0b', refuse: '#ef4444' };
        const etatLabels = { publie: 'Publié', en_attente_validation: 'En attente', refuse: 'Refusé' };
        document.getElementById('pd_etat').innerHTML = `<span class="badge" style="background:${etatColors[data.etat]||'#9ca3af'}">${etatLabels[data.etat]||data.etat}</span>`;

        // Tâches
        document.getElementById('pd_taches_count').textContent = data.taches.length;
        if (data.taches.length === 0) {
            document.getElementById('pd_taches_content').innerHTML = '<p class="text-muted text-center py-3">Aucune tâche pour ce projet.</p>';
        } else {
            const tacheColors = { a_faire: '#9ca3af', en_cours: '#f59e0b', termine: '#10b981' };
            const tacheLabels = { a_faire: 'À faire', en_cours: 'En cours', termine: 'Terminé' };
            const rows = data.taches.map(t => `
                <div class="d-flex align-items-start gap-3 p-3 mb-2 rounded" style="background:#f8f9ff;border:1px solid #e8e4ff;">
                    <div style="width:10px;height:10px;border-radius:50%;background:${tacheColors[t.statut]||'#9ca3af'};margin-top:5px;flex-shrink:0;"></div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <strong style="color:#1e1b4b;">${escapeHtmlAdmin(t.titre)}</strong>
                            <div class="d-flex gap-2 align-items-center">
                                ${t.prix > 0 ? `<span class="badge" style="background:#fff3cd;color:#8a6d3b;">${parseFloat(t.prix).toFixed(2)} TND</span>` : ''}
                                <span class="badge" style="background:${tacheColors[t.statut]||'#9ca3af'};color:#fff;">${tacheLabels[t.statut]||t.statut}</span>
                                ${t.payee ? '<span class="badge" style="background:#d1fae5;color:#065f46;">✓ Payée</span>' : ''}
                            </div>
                        </div>
                        ${t.description ? `<div class="small text-muted mt-1">${escapeHtmlAdmin(t.description)}</div>` : ''}
                        <div class="small mt-1" style="color:#9ca3af;">
                            <i class="fas fa-user me-1"></i>${escapeHtmlAdmin(t.prenom_freelancer + ' ' + t.nom_freelancer)}
                        </div>
                    </div>
                </div>
            `).join('');
            document.getElementById('pd_taches_content').innerHTML = rows;
        }

        new bootstrap.Modal(document.getElementById('projetDetailsModal')).show();
    })
    .catch(() => swalError('Erreur réseau.'));
}

function escapeHtmlAdmin(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
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
const wrapper          = document.getElementById('wrapper');

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

// ── Modifier tâche (Admin) ────────────────────────────────────────────
function openEditTacheAdmin(id, titre, description, statut, prix) {
    document.getElementById('eta_id').value          = id;
    document.getElementById('eta_titre').value       = titre;
    document.getElementById('eta_description').value = description || '';
    document.getElementById('eta_statut').value      = statut;
    document.getElementById('eta_prix').value        = prix > 0 ? prix : '';
    // Reset validation
    ['eta_titre','eta_prix','eta_description'].forEach(fid => {
        const el = document.getElementById(fid);
        if (el) el.classList.remove('is-invalid','is-valid');
    });
    ['err_eta_titre','err_eta_prix','err_eta_description'].forEach(fid => {
        const el = document.getElementById(fid);
        if (el) el.textContent = '';
    });
    new bootstrap.Modal(document.getElementById('editTacheAdminModal')).show();
}

document.getElementById('editTacheAdminForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let valid = true;

    const titre = document.getElementById('eta_titre');
    const prix  = document.getElementById('eta_prix');
    const desc  = document.getElementById('eta_description');

    [titre, prix, desc].forEach(el => el.classList.remove('is-invalid','is-valid'));

    // Titre
    if (titre.value.trim() === '') {
        titre.classList.add('is-invalid');
        document.getElementById('err_eta_titre').textContent = 'Le titre est obligatoire.';
        valid = false;
    } else if (titre.value.trim().length < 3) {
        titre.classList.add('is-invalid');
        document.getElementById('err_eta_titre').textContent = 'Minimum 3 caractères.';
        valid = false;
    } else if (titre.value.trim().length > 150) {
        titre.classList.add('is-invalid');
        document.getElementById('err_eta_titre').textContent = 'Maximum 150 caractères.';
        valid = false;
    } else { titre.classList.add('is-valid'); }

    // Prix
    const prixVal = parseFloat(prix.value);
    if (prix.value.trim() === '') {
        prix.classList.add('is-invalid');
        document.getElementById('err_eta_prix').textContent = 'Le prix est obligatoire.';
        valid = false;
    } else if (isNaN(prixVal) || prixVal < 0) {
        prix.classList.add('is-invalid');
        document.getElementById('err_eta_prix').textContent = 'Le prix doit être un nombre positif ou nul.';
        valid = false;
    } else { prix.classList.add('is-valid'); }

    // Description
    if (desc.value.trim() === '') {
        desc.classList.add('is-invalid');
        document.getElementById('err_eta_description').textContent = 'La description est obligatoire.';
        valid = false;
    } else if (desc.value.trim().length < 5) {
        desc.classList.add('is-invalid');
        document.getElementById('err_eta_description').textContent = 'Minimum 5 caractères.';
        valid = false;
    } else if (desc.value.trim().length > 500) {
        desc.classList.add('is-invalid');
        document.getElementById('err_eta_description').textContent = 'Maximum 500 caractères.';
        valid = false;
    } else { desc.classList.add('is-valid'); }

    if (!valid) return;

    const fd = new FormData(this);
    fd.append('action', 'edit_tache_admin');
    fetch('?action=projectlist', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editTacheAdminModal')).hide();
            Swal.fire({ icon:'success', title:'Tâche modifiée !', timer:1500, showConfirmButton:false })
                .then(() => location.reload());
        } else {
            swalError(data.message);
        }
    })
    .catch(() => swalError('Erreur réseau.'));
});

// ── Supprimer tâche (Admin) ───────────────────────────────────────────
function deleteTacheAdmin(id) {
    Swal.fire({
        title: 'Supprimer cette tâche ?',
        text: 'Cette action est irréversible.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then(r => {
        if (!r.isConfirmed) return;
        const fd = new FormData();
        fd.append('action', 'delete_tache_admin');
        fd.append('id', id);
        fetch('?action=projectlist', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon:'success', title:'Supprimée !', timer:1200, showConfirmButton:false })
                    .then(() => location.reload());
            } else {
                swalError(data.message || 'Erreur.');
            }
        })
        .catch(() => swalError('Erreur réseau.'));
    });
}
// ── Graphiques Chart.js ───────────────────────────────────────────────
const moisLabels = <?= json_encode($moisLabels) ?>;
const moisData   = <?= json_encode(array_map('intval', $moisData)) ?>;

// Graphique barres — projets par mois
new Chart(document.getElementById('chartProjets'), {
    type: 'bar',
    data: {
        labels: moisLabels.length ? moisLabels : ['Aucune donnée'],
        datasets: [{
            label: 'Projets créés',
            data: moisData.length ? moisData : [0],
            backgroundColor: 'rgba(37,99,235,0.7)',
            borderColor: '#2563eb',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// Graphique donut — candidatures
new Chart(document.getElementById('chartCandidatures'), {
    type: 'doughnut',
    data: {
        labels: ['Acceptées', 'En attente', 'Refusées'],
        datasets: [{
            data: [
                <?= (int)$statsCands['acceptees'] ?>,
                <?= (int)$statsCands['en_attente'] ?>,
                <?= (int)$statsCands['refusees'] ?>
            ],
            backgroundColor: ['#27ae60','#f39c12','#e74c3c'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 12 } } }
        },
        cutout: '65%'
    }
});
</script>
</body>
</html>
