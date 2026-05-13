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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Projets - SkillBridge Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/Views/assets/css/skillbridge-admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600;700&display=swap">
    <style>
        .project-desc-cell { max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    </style>
</head>
<body class="skillbridge-admin">
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <div class="topbar-title">Gestion des Projets</div>
                <div class="topbar-bread">Vue d ensemble et gestion complete des projets</div>
            </div>
            <div class="topbar-actions">
                <a class="topbar-btn topbar-btn-outline" href="?action=projectlist&export=pdf&q=<?= urlencode($search) ?>">
                    <i class="fas fa-file-export"></i> Export PDF
                </a>
                <button class="topbar-btn topbar-btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                    <i class="fas fa-plus"></i> Ajouter Projet
                </button>
            </div>
        </div>

        <!-- STATS CARDS -->
        <div class="stats-grid">
            <div class="stat-widget purple">
                <div class="sw-icon"><i class="fas fa-diagram-project"></i></div>
                <div class="sw-value"><?= (int)$stats['total'] ?></div>
                <div class="sw-label">Total Projets</div>
            </div>
            <div class="stat-widget green">
                <div class="sw-icon"><i class="fas fa-users"></i></div>
                <div class="sw-value"><?= (int)$statsUsers['total'] ?></div>
                <div class="sw-label">Utilisateurs</div>
            </div>
            <div class="stat-widget orange">
                <div class="sw-icon"><i class="fas fa-tasks"></i></div>
                <div class="sw-value"><?= (int)$statsTaches['total'] ?></div>
                <div class="sw-label">Tâches</div>
            </div>
            <div class="stat-widget blue">
                <div class="sw-icon"><i class="fas fa-coins"></i></div>
                <div class="sw-value"><?= number_format((float)$stats['budget_total'], 0, ',', ' ') ?></div>
                <div class="sw-label">TND budget total</div>
            </div>
        </div>

        <!-- CHARTS -->
        <div class="admin-charts-row" style="display:grid;grid-template-columns:2fr 1fr;gap:1.25rem;margin-bottom:1.5rem;">
            <section class="admin-card" style="padding:1.25rem;">
                <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#7c3aed;margin-bottom:1rem;">
                    <i class="fas fa-chart-bar" style="margin-right:.4rem;"></i>Projets créés (6 derniers mois)
                </div>
                <canvas id="chartProjets" height="90"></canvas>
            </section>
            <section class="admin-card" style="padding:1.25rem;">
                <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#10b981;margin-bottom:1rem;">
                    <i class="fas fa-chart-pie" style="margin-right:.4rem;"></i>Candidatures
                </div>
                <canvas id="chartCandidatures" height="160"></canvas>
            </section>
        </div>

        <!-- PROJETS EN ATTENTE DE VALIDATION -->
        <?php if (!empty($pendingProjects)): ?>
        <section class="admin-card" style="margin-bottom:1.5rem;border-left:4px solid #f59e0b;">
            <div class="admin-table-header">
                <div class="admin-table-title" style="color:#f59e0b;">
                    <i class="fas fa-clock" style="margin-right:.5rem;"></i>
                    En attente de validation
                    <span class="badge-pending" style="margin-left:.5rem;"><?= count($pendingProjects) ?></span>
                </div>
            </div>
            <table class="admin-table">
                <thead>
                    <tr><th>Titre</th><th>Description</th><th>Budget</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($pendingProjects as $p): ?>
                    <tr id="pending-row-<?= $p->getId() ?>">
                        <td><?= htmlspecialchars($p->getTitre()) ?></td>
                        <td class="project-desc-cell" title="<?= htmlspecialchars($p->getDescription()) ?>"><?= htmlspecialchars($p->getDescription()) ?></td>
                        <td><?= number_format((float)$p->getBudget(), 2, ',', ' ') ?> TND</td>
                        <td><?= htmlspecialchars($p->getDateCreation()) ?></td>
                        <td>
                            <div class="admin-stack-actions">
                                <button class="admin-btn admin-btn-success admin-btn-sm" onclick="validerProjet(<?= $p->getId() ?>, 'accepter')"><i class="fas fa-check"></i> Accepter</button>
                                <button class="admin-btn admin-btn-danger admin-btn-sm" onclick="validerProjet(<?= $p->getId() ?>, 'refuser')"><i class="fas fa-times"></i> Refuser</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>

        <!-- FILTRES -->
        <section class="admin-card admin-filter-bar-card">
            <form method="GET" class="admin-filter-bar" id="project-filter-form">
                <input type="hidden" name="action" value="projectlist">
                <div class="admin-filter-bar-title"><i class="fas fa-sliders"></i><span>Recherche</span></div>
                <input type="text" name="q" placeholder="Rechercher un projet..." value="<?= htmlspecialchars($search) ?>">
                <select name="statut">
                    <option value="">Tous statuts</option>
                    <option value="en_attente" <?= $statut === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="en_cours"   <?= $statut === 'en_cours'   ? 'selected' : '' ?>>En cours</option>
                    <option value="termine"    <?= $statut === 'termine'    ? 'selected' : '' ?>>Terminé</option>
                </select>
                <select name="etat">
                    <option value="">Tous états</option>
                    <option value="publie"                <?= $etat_filtre === 'publie'                ? 'selected' : '' ?>>Publié</option>
                    <option value="en_attente_validation" <?= $etat_filtre === 'en_attente_validation' ? 'selected' : '' ?>>En attente valid.</option>
                    <option value="refuse"                <?= $etat_filtre === 'refuse'                ? 'selected' : '' ?>>Refusé</option>
                </select>
                <button class="admin-btn admin-btn-primary" type="submit">Filtrer</button>
                <?php if ($search || $statut || $etat_filtre || $budget_min || $budget_max): ?>
                    <a class="admin-btn admin-btn-outline" href="?action=projectlist">Reset</a>
                <?php endif; ?>
            </form>
        </section>

        <!-- LISTE DES PROJETS -->
        <div class="admin-table-wrap">
            <div class="admin-table-header">
                <div class="admin-table-title">Liste des Projets</div>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th><th>Titre</th><th>Client</th><th>Description</th>
                        <th>Budget</th><th>Date</th><th>Statut</th><th>État</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($projects)): ?>
                    <tr><td colspan="9" style="text-align:center;color:#9ca3af;padding:2rem;">Aucun projet trouvé.</td></tr>
                <?php else: ?>
                    <?php foreach ($projects as $project):
                        $etatBadge = ['publie'=>'badge-actif','en_attente_validation'=>'badge-pending','refuse'=>'badge-suspendu'];
                        $etatLabel = ['publie'=>'Publié','en_attente_validation'=>'En attente','refuse'=>'Refusé'];
                        $statutBadge = ['en_cours'=>'badge-pending','termine'=>'badge-actif','en_attente'=>'badge-suspendu'];
                        $e = $project->getEtat(); $s = $project->getStatut();
                    ?>
                    <tr>
                        <td>#<?= $project->getId() ?></td>
                        <td>
                            <div class="table-service-name">
                                <a href="#" onclick="voirProjet(<?= $project->getId() ?>); return false;" style="color:inherit;text-decoration:none;">
                                    <?= htmlspecialchars($project->getTitre()) ?>
                                </a>
                            </div>
                        </td>
                        <td><?= $project->getNomClient() ? htmlspecialchars($project->getNomClient()) : '<span style="color:#9ca3af">—</span>' ?></td>
                        <td class="project-desc-cell" title="<?= htmlspecialchars($project->getDescription()) ?>"><?= htmlspecialchars($project->getDescription()) ?></td>
                        <td><?= number_format((float)$project->getBudget(), 2, ',', ' ') ?> TND</td>
                        <td><?= htmlspecialchars($project->getDateCreation()) ?></td>
                        <td><span class="badge <?= $statutBadge[$s] ?? 'badge-pending' ?>"><?= htmlspecialchars($s) ?></span></td>
                        <td><span class="badge <?= $etatBadge[$e] ?? 'badge-pending' ?>"><?= $etatLabel[$e] ?? $e ?></span></td>
                        <td>
                            <div class="admin-stack-actions">
                                <button class="admin-btn admin-btn-warning admin-btn-sm" onclick="editProject(<?= $project->getId() ?>)" data-bs-toggle="modal" data-bs-target="#editProjectModal"><i class="fas fa-edit"></i> Modifier</button>
                                <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="openAddTache(<?= $project->getId() ?>, <?= htmlspecialchars(json_encode($project->getTitre())) ?>)"><i class="fas fa-plus"></i> Tâche</button>
                                <button class="admin-btn admin-btn-danger admin-btn-sm" onclick="deleteProject(<?= $project->getId() ?>)"><i class="fas fa-trash"></i> Supprimer</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>


        <!-- CANDIDATURES EN ATTENTE -->
        <?php if (!empty($pendingCands)): ?>
        <section class="admin-card" style="margin-top:1.5rem;border-left:4px solid #f59e0b;">
            <div class="admin-table-header">
                <div class="admin-table-title" style="color:#f59e0b;">
                    <i class="fas fa-user-clock" style="margin-right:.5rem;"></i>
                    Candidatures en attente
                    <span class="badge-pending" style="margin-left:.5rem;"><?= count($pendingCands) ?></span>
                </div>
            </div>
            <table class="admin-table">
                <thead><tr><th>Freelancer</th><th>Projet</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($pendingCands as $c): ?>
                <tr id="cand-row-<?= $c->getId() ?>">
                    <td><?= htmlspecialchars($c->getPrenomFreelancer().' '.$c->getNomFreelancer()) ?></td>
                    <td><?= htmlspecialchars($c->getTitreProjet()) ?></td>
                    <td><?= date('d/m/Y', strtotime($c->getCreatedAt())) ?></td>
                    <td>
                        <div class="admin-stack-actions">
                            <button class="admin-btn admin-btn-success admin-btn-sm" onclick="validerCand(<?= $c->getId() ?>, 'accepter_cand')"><i class="fas fa-check"></i> Accepter</button>
                            <button class="admin-btn admin-btn-danger admin-btn-sm" onclick="validerCand(<?= $c->getId() ?>, 'refuser_cand')"><i class="fas fa-times"></i> Refuser</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>

        <!-- TOUTES LES CANDIDATURES -->
        <div class="admin-table-wrap" style="margin-top:1.5rem;">
            <div class="admin-table-header">
                <div class="admin-table-title"><i class="fas fa-paper-plane" style="margin-right:.5rem;"></i>Toutes les Candidatures</div>
                <span class="badge-pending"><?= count($candidatures) ?></span>
            </div>
            <table class="admin-table">
                <thead><tr><th>Freelancer</th><th>Projet</th><th>Date</th><th>Statut</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (empty($candidatures)): ?>
                    <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:2rem;">Aucune candidature.</td></tr>
                <?php else: ?>
                    <?php foreach ($candidatures as $c):
                        $cbadge = ['en_attente'=>'badge-pending','accepte'=>'badge-actif','refuse'=>'badge-suspendu'];
                        $clabel = ['en_attente'=>'En attente','accepte'=>'Acceptée','refuse'=>'Refusée'];
                        $cs = $c->getStatut();
                    ?>
                    <tr id="cand-row-<?= $c->getId() ?>">
                        <td><?= htmlspecialchars($c->getPrenomFreelancer().' '.$c->getNomFreelancer()) ?></td>
                        <td><?= htmlspecialchars($c->getTitreProjet()) ?></td>
                        <td><?= date('d/m/Y', strtotime($c->getCreatedAt())) ?></td>
                        <td><span class="badge <?= $cbadge[$cs]??'badge-pending' ?>"><?= $clabel[$cs]??$cs ?></span></td>
                        <td>
                            <?php if ($cs === 'en_attente'): ?>
                            <div class="admin-stack-actions">
                                <button class="admin-btn admin-btn-success admin-btn-sm" onclick="validerCand(<?= $c->getId() ?>, 'accepter_cand')"><i class="fas fa-check"></i></button>
                                <button class="admin-btn admin-btn-danger admin-btn-sm" onclick="validerCand(<?= $c->getId() ?>, 'refuser_cand')"><i class="fas fa-times"></i></button>
                            </div>
                            <?php else: ?><span style="color:#9ca3af;">—</span><?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- TÂCHES DES FREELANCERS -->
        <div class="admin-table-wrap" style="margin-top:1.5rem;">
            <div class="admin-table-header">
                <div class="admin-table-title"><i class="fas fa-tasks" style="margin-right:.5rem;"></i>Tâches des Freelancers</div>
                <span class="badge-pending"><?= count($taches) ?></span>
            </div>
            <table class="admin-table">
                <thead><tr><th>Freelancer</th><th>Projet</th><th>Tâche</th><th>Description</th><th>Statut</th><th>Prix</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (empty($taches)): ?>
                    <tr><td colspan="8" style="text-align:center;color:#9ca3af;padding:2rem;">Aucune tâche.</td></tr>
                <?php else: ?>
                    <?php foreach ($taches as $t):
                        $tbadge = ['a_faire'=>'badge-pending','en_cours'=>'badge-warning','termine'=>'badge-actif'];
                        $tlabel = ['a_faire'=>'À faire','en_cours'=>'En cours','termine'=>'Terminé'];
                        $ts = $t['statut'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($t['prenom_freelancer'].' '.$t['nom_freelancer']) ?></td>
                        <td><?= htmlspecialchars($t['titre_projet']) ?></td>
                        <td><?= htmlspecialchars($t['titre']) ?></td>
                        <td class="project-desc-cell"><?= htmlspecialchars($t['description']??'') ?></td>
                        <td><span class="badge <?= $tbadge[$ts]??'badge-pending' ?>"><?= $tlabel[$ts]??$ts ?></span></td>
                        <td><?= !empty($t['prix']) && (float)$t['prix'] > 0 ? number_format((float)$t['prix'],2,',',' ').' TND' : '—' ?></td>
                        <td><?= date('d/m/Y', strtotime($t['created_at'])) ?></td>
                        <td>
                            <div class="admin-stack-actions">
                                <button class="admin-btn admin-btn-warning admin-btn-sm" onclick="openEditTacheAdmin(<?= $t['id'] ?>, <?= htmlspecialchars(json_encode($t['titre'])) ?>, <?= htmlspecialchars(json_encode($t['description']??'')) ?>, '<?= $t['statut'] ?>', <?= (float)($t['prix']??0) ?>)"><i class="fas fa-edit"></i></button>
                                <button class="admin-btn admin-btn-danger admin-btn-sm" onclick="deleteTacheAdmin(<?= $t['id'] ?>)"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

document.getElementById('addProjectModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('addProjectForm');
    form.reset();
    form.querySelectorAll('.is-invalid, .is-valid').forEach(el => el.classList.remove('is-invalid', 'is-valid'));
    document.getElementById('addProjectMsg').innerHTML = '';
});

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
