<?php
require_once __DIR__ . '/../../Controllers/ProjectController.php';
require_once __DIR__ . '/../../Controllers/CandidatureController.php';
require_once __DIR__ . '/../../Models/Tache.php';

$projectController = new ProjectController();
$cc                = new CandidatureController();

$search     = trim($_GET['q']          ?? '');
$statut     = trim($_GET['statut']     ?? '');
$budget_min = trim($_GET['budget_min'] ?? '');
$budget_max = trim($_GET['budget_max'] ?? '');
$tab        = $_GET['tab'] ?? 'tous'; // 'tous' ou 'mes'

// ── AJAX postuler ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ajaxAction = $_POST['action'] ?? '';

    if ($ajaxAction === 'postuler') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 3) {
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']); exit;
        }
        echo json_encode($cc->postuler((int)$_POST['id_projet'], $_SESSION['user_id']));
        exit;
    }

    if ($ajaxAction === 'update_projet') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 2) {
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']); exit;
        }
        $id        = (int)($_POST['id'] ?? 0);
        $titre     = trim($_POST['titre'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $budget    = trim($_POST['budget'] ?? '');
        // Vérifier que le projet appartient bien à ce client
        $proj = $projectController->getProjectById($id);
        if (!$proj || $proj->getIdClient() != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Projet introuvable.']); exit;
        }
        // Seuls titre, description et budget sont modifiables par le client
        if (!$titre)              { echo json_encode(['success'=>false,'message'=>'Le titre est obligatoire.']); exit; }
        if (!$desc)               { echo json_encode(['success'=>false,'message'=>'La description est obligatoire.']); exit; }
        if (!is_numeric($budget) || (float)$budget < 0) { echo json_encode(['success'=>false,'message'=>'Budget invalide.']); exit; }
        $proj->setTitre($titre);
        $proj->setDescription($desc);
        $proj->setBudget(round((float)$budget, 2));
        if ($projectController->updateProject($proj)) {
            echo json_encode(['success' => true, 'message' => 'Projet mis à jour.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour.']);
        }
        exit;
    }

    if ($ajaxAction === 'add') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non connecté.']); exit;
        }
        $validation = $projectController->validateProjectInput($_POST);
        if (!$validation['is_valid']) {
            echo json_encode(['success' => false, 'message' => implode(' ', $validation['errors'])]); exit;
        }
        $d         = $validation['data'];
        $etat      = ($_SESSION['user_role'] == 1) ? 'publie' : 'en_attente_validation';
        $id_client = ($_SESSION['user_role'] != 1) ? $_SESSION['user_id'] : null;
        $project   = new Project($d['titre'], $d['description'], $d['budget'], $d['date_creation'], 'en_attente', $etat, $id_client);
        if ($projectController->addProject($project)) {
            $msg = ($etat === 'publie') ? 'Projet ajouté avec succès.' : 'Projet soumis. En attente de validation par l\'admin.';
            echo json_encode(['success' => true, 'message' => $msg]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Une erreur est survenue.']);
        }
        exit;
    }

    if ($ajaxAction === 'update_avancement') {        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 3) {
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']); exit;
        }
        $id_projet  = (int)($_POST['id_projet']  ?? 0);
        $avancement = (int)($_POST['avancement'] ?? 0);
        echo json_encode($cc->updateAvancement($id_projet, $_SESSION['user_id'], $avancement));
        exit;
    }

    if ($ajaxAction === 'add_tache') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 3) {
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']); exit;
        }
        $titre     = trim($_POST['titre'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $id_projet = (int)($_POST['id_projet'] ?? 0);
        $statut_t  = $_POST['statut'] ?? 'a_faire';
        $prix      = (float)($_POST['prix'] ?? 0);
        if (!$titre) { echo json_encode(['success'=>false,'message'=>'Le titre est obligatoire.']); exit; }
        if ($prix < 0) { echo json_encode(['success'=>false,'message'=>'Le prix ne peut pas être négatif.']); exit; }
        $tache = new Tache($id_projet, $_SESSION['user_id'], $titre, $desc, $statut_t, $prix);
        echo json_encode($cc->ajouterTache($tache));
        exit;
    }

    if ($ajaxAction === 'delete_tache') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 3) {
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']); exit;
        }
        $ok = $cc->supprimerTache((int)$_POST['id'], $_SESSION['user_id']);
        echo json_encode(['success' => $ok]);
        exit;
    }

    if ($ajaxAction === 'payer_tache') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 2) {
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']); exit;
        }
        $id_tache = (int)($_POST['id'] ?? 0);
        echo json_encode($cc->payerTache($id_tache, $_SESSION['user_id']));
        exit;
    }

    if ($ajaxAction === 'update_tache_statut') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 3) {
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']); exit;
        }
        $id_tache = (int)($_POST['id'] ?? 0);
        $statut   = $_POST['statut'] ?? '';
        $statuts_valides = ['a_faire', 'en_cours', 'termine'];
        if (!in_array($statut, $statuts_valides)) {
            echo json_encode(['success' => false, 'message' => 'Statut invalide.']); exit;
        }
        echo json_encode($cc->updateTacheStatut($id_tache, $_SESSION['user_id'], $statut));
        exit;
    }
}

$projects = $projectController->listProjects($search, $statut, $budget_min, $budget_max);

// Si client (role=2), il voit uniquement ses propres projets publiés
if (isset($_SESSION['user_id']) && (int)$_SESSION['user_role'] === 2) {
    $projects = array_filter($projects, fn($p) => $p->getIdClient() == $_SESSION['user_id']);
}

// Données "Mes Projets" selon le rôle
$mesProjets      = [];
$mesCandidatures = [];
$tachesParProjet = [];

if (isset($_SESSION['user_id'])) {
    $role = (int)$_SESSION['user_role'];
    if ($role === 2) {
        // Client — ses projets soumis + tâches
        $mesProjets = $cc->getProjetsClient($_SESSION['user_id']);
        foreach ($mesProjets as $p) {
            $tachesParProjet[$p['id']] = $cc->getTachesProjet($p['id']);
        }
    } elseif ($role === 3) {
        // Freelancer — ses candidatures + projets acceptés + tâches
        $mesCandidatures = $cc->getMesCandidatures($_SESSION['user_id']);
        $mesProjets      = $cc->getProjetsAcceptes($_SESSION['user_id']);
        foreach ($mesProjets as &$p) {
            $tachesParProjet[$p['id']] = $cc->getTaches($p['id'], $_SESSION['user_id']);
            $budgetInfo = $cc->getBudgetRestant($p['id']);
            $p['budget_restant']      = $budgetInfo ? $budgetInfo['restant']      : (float)$p['budget'];
            $p['budget_total_taches'] = $budgetInfo ? $budgetInfo['total_taches'] : 0;
        }
        unset($p);
    }
}

function formatStatusLabel($status)
{
    if ($status === 'en_cours') {
        return 'En cours';
    }
    if ($status === 'termine') {
        return 'Terminé';
    }
    return 'En attente';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projets Freelance - SkillBridge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Views/assets/css/skillbridge.css">
    <style>
        .projects-layout {
            padding: 3rem 1.5rem 5rem;
            min-height: calc(100vh - 68px - 65px);
            background: var(--creme);
        }
        .projects-header {
            max-width: 980px;
            margin: 0 auto 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--beige-border);
        }
        .projects-title-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: space-between;
            align-items: center;
        }
        .projects-header h1 {
            font-family: 'Playfair Display', serif;
            margin: 0;
            color: var(--charcoal);
            font-size: 2rem;
        }
        .projects-subtitle {
            color: var(--text-light);
            margin-top: 0.4rem;
            font-size: 0.92rem;
        }
        .search-card {
            max-width: 980px;
            margin: 0 auto 1.4rem;
            background: var(--white);
            border: 1px solid var(--beige-border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            padding: 1rem;
        }
        .search-box {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .search-box input[type="text"],
        .search-box input[type="number"] {
            flex: 1;
            min-width: 100px;
            max-width: 180px;
            padding: 0.65rem 0.9rem;
            border: 1px solid var(--beige-border);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            color: var(--text-dark);
        }
        .search-box select {
            flex: 1;
            min-width: 120px;
            max-width: 160px;
            padding: 0.65rem 0.9rem;
            border: 1px solid var(--beige-border);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            color: var(--text-dark);
            background: var(--white);
        }
        .search-box input:focus,
        .search-box select:focus {
            outline: none;
            border-color: var(--amber);
            box-shadow: 0 0 0 2px var(--amber-glow);
        }
        .search-box .btn {
            white-space: nowrap;
            flex-shrink: 0;
        }
        .projects-grid {
            max-width: 980px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.2rem;
        }
        .project-card {
            background: var(--white);
            border: 1px solid var(--beige-border);
            border-radius: var(--radius-lg);
            padding: 1.2rem;
            box-shadow: var(--shadow-sm);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            position: relative;
        }
        .project-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 1.2rem;
            right: 1.2rem;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--amber), transparent);
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .project-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        .project-card:hover::before {
            opacity: 1;
        }
        .project-card h3 {
            margin: 0 0 0.65rem;
            color: var(--charcoal);
            font-size: 1.12rem;
            line-height: 1.3;
        }
        .project-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.86rem;
            color: var(--text-light);
            margin-bottom: 0.7rem;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .project-description {
            color: var(--text-mid);
            font-size: 0.93rem;
            line-height: 1.55;
            min-height: 70px;
            margin-bottom: 0.85rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.28rem 0.62rem;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .status-en-cours { background: #fff3cd; color: #8a6d3b; }
        .status-termine { background: #d4edda; color: #155724; }
        .status-en-attente { background: #e2e3e5; color: #383d41; }
        .empty-state {
            max-width: 980px;
            margin: 2rem auto;
            background: var(--white);
            border: 1px dashed var(--beige-border);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            color: var(--text-light);
        }
        /* ── ONGLETS ── */
        .tabs-bar { display:flex; gap:0.5rem; margin-top:1.25rem; border-bottom:2px solid var(--beige-border); }
        .tab-btn  { display:flex; align-items:center; gap:0.4rem; padding:0.6rem 1.2rem; font-size:0.88rem; font-weight:600; color:var(--text-light); text-decoration:none; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all 0.2s; border-radius:6px 6px 0 0; }
        .tab-btn:hover { color:var(--charcoal); background:var(--sable); }
        .tab-btn.active { color:var(--amber); border-bottom-color:var(--amber); background:rgba(224,112,32,0.06); }
        .tab-badge { background:var(--amber); color:#fff; border-radius:999px; padding:0.05rem 0.5rem; font-size:0.72rem; font-weight:700; }

        /* ── MES PROJETS ── */
        .mes-section-title { max-width:980px; margin:0 auto 1rem; font-family:'Playfair Display',serif; font-size:1.3rem; color:var(--charcoal); font-weight:700; padding-top:0.5rem; }
        .cand-grid { max-width:980px; margin:0 auto; display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:1rem; margin-bottom:1.5rem; }
        .cand-card { background:var(--white); border:1px solid var(--beige-border); border-radius:var(--radius-lg); padding:1.1rem 1.25rem; box-shadow:var(--shadow-sm); }
        .cand-card-title { font-weight:600; color:var(--charcoal); font-size:0.95rem; margin-bottom:0.3rem; }
        .cand-card-date  { font-size:0.8rem; color:var(--text-light); margin-bottom:0.6rem; }
        .cand-badge { display:inline-block; padding:0.2rem 0.65rem; border-radius:999px; font-size:0.75rem; font-weight:700; }
        .mes-projet-block { max-width:980px; margin:0 auto 2rem; background:var(--white); border:1px solid var(--beige-border); border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-sm); }
        .mes-projet-head  { background:var(--charcoal); padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center; color:#fff; font-family:'Playfair Display',serif; font-size:1.05rem; font-weight:600; flex-wrap:wrap; gap:0.5rem; }

        /* ── KANBAN ── */
        .kanban-board { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; }
        .kanban-col   { background:#f0f2f5; border-radius:10px; overflow:hidden; display:flex; flex-direction:column; min-height:120px; }
        .kanban-col-header { padding:0.75rem 1rem; color:#fff; font-weight:700; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.06em; display:flex; justify-content:space-between; align-items:center; }
        .kanban-count { background:rgba(255,255,255,0.3); border-radius:999px; padding:0.1rem 0.55rem; font-size:0.75rem; font-weight:700; }
        .kanban-col-body { padding:0.75rem; flex:1; display:flex; flex-direction:column; gap:0.6rem; }
        .kanban-empty { text-align:center; color:#aaa; font-size:0.82rem; padding:1rem 0; }
        .kanban-card  { background:#fff; border-radius:8px; padding:0.85rem 1rem; box-shadow:0 1px 4px rgba(0,0,0,0.08); border:1px solid #e8e8e8; transition:transform 0.2s,box-shadow 0.2s; }
        .kanban-card:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,0.12); }
        .kanban-card-title { font-weight:600; color:var(--charcoal); font-size:0.9rem; margin-bottom:0.3rem; }
        .kanban-card-desc  { font-size:0.8rem; color:var(--text-light); margin-bottom:0.5rem; line-height:1.4; }
        .kanban-card-footer { display:flex; align-items:center; gap:0.5rem; margin-top:0.5rem; padding-top:0.5rem; border-top:1px solid #f0f0f0; }
        .kanban-avatar { width:24px; height:24px; border-radius:50%; background:var(--charcoal); color:var(--amber); font-size:0.65rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .kanban-name   { font-size:0.75rem; color:var(--text-light); }
        @media (max-width:640px) { .kanban-board { grid-template-columns:1fr; } .tabs-bar { gap:0; } }
        /* Modal styles */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-box {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 2rem;
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: var(--shadow-lg);
        }
        .modal-box h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: var(--charcoal);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--beige-border);
        }
        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.4rem;
            cursor: pointer;
            color: var(--text-light);
            line-height: 1;
        }
        .modal-close:hover { color: var(--charcoal); }
        .modal-form-group {
            margin-bottom: 1.2rem;
        }
        .modal-form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--charcoal-soft);
            margin-bottom: 0.4rem;
        }
        .modal-form-group input,
        .modal-form-group select,
        .modal-form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--beige-border);
            border-radius: var(--radius);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            color: var(--text-dark);
            background: var(--white);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .modal-form-group input:focus,
        .modal-form-group select:focus,
        .modal-form-group textarea:focus {
            outline: none;
            border-color: var(--amber);
            box-shadow: 0 0 0 3px var(--amber-glow);
        }
        .modal-form-group input.error,
        .modal-form-group select.error,
        .modal-form-group textarea.error {
            border-color: #dc3545;
        }
        .field-error {
            display: block;
            color: #dc3545;
            font-size: 0.82rem;
            margin-top: 0.3rem;
        }
        .modal-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .modal-footer-btns {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--beige-border);
        }
        .btn-cancel {
            background: var(--creme);
            color: var(--charcoal-soft);
            border: 1px solid var(--beige-border);
        }
        .btn-cancel:hover {
            background: var(--charcoal);
            color: var(--white);
        }
        #frontAddMsg { margin-top: 1rem; }
        /* Bouton modifier projet */
        .btn-edit-projet { background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.25); color:#fff; border-radius:6px; padding:0.3rem 0.75rem; font-size:0.8rem; font-weight:600; cursor:pointer; transition:background 0.2s; }
        .btn-edit-projet:hover { background:rgba(255,255,255,0.22); }
        /* Bouton payer */
        .btn-payer { background:linear-gradient(135deg,#27ae60,#2ecc71); border:none; color:#fff; border-radius:6px; padding:0.25rem 0.65rem; font-size:0.75rem; font-weight:700; cursor:pointer; transition:opacity 0.2s; margin-left:auto; }
        .btn-payer:hover { opacity:0.85; }
        .badge-payee { margin-left:auto; background:#d4edda; color:#155724; border-radius:6px; padding:0.2rem 0.55rem; font-size:0.72rem; font-weight:700; }
        /* Bouton payer */
        .btn-payer { background:linear-gradient(135deg,#27ae60,#2ecc71); border:none; color:#fff; border-radius:6px; padding:0.25rem 0.65rem; font-size:0.75rem; font-weight:700; cursor:pointer; transition:opacity 0.2s; margin-left:auto; }
        .btn-payer:hover { opacity:0.85; }
        .badge-payee { margin-left:auto; background:#d4edda; color:#155724; border-radius:6px; padding:0.2rem 0.55rem; font-size:0.72rem; font-weight:700; }
        @media (max-width: 640px) {
            .search-box { width: 100%; }
            .search-box input { min-width: 0; width: 100%; }
            .projects-header h1 { font-size: 1.6rem; }
            .search-box {
                flex-direction: column;
                align-items: stretch;
            }
            .search-box .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body class="has-sidebar">
<?php require __DIR__ . '/partials/sidebar.php'; ?>
<nav class="navbar-top">
    <div class="container">
        <button class="sb-toggle" onclick="openSidebar()"><i class="fas fa-bars"></i></button>
        <div class="nav-buttons">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="?action=login" class="btn btn-secondary">Connexion</a>
                <a href="?action=register" class="btn btn-primary">S'inscrire</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="projects-layout">
    <div class="projects-header">
        <div class="projects-title-row">
            <h1>Projets Freelance</h1>
            <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_role'] === 2): ?>
                <button class="btn btn-primary" onclick="document.getElementById('addProjectOverlay').classList.add('active')">
                    + Ajouter un projet
                </button>
            <?php endif; ?>
        </div>
        <p class="projects-subtitle">Parcourez les missions disponibles et suivez leur statut en temps réel.</p>

        <?php if (isset($_SESSION['user_id']) && in_array((int)$_SESSION['user_role'], [2,3])): ?>
        <!-- Onglets -->
        <div class="tabs-bar">
            <a href="?action=projects&tab=tous" class="tab-btn <?= $tab==='tous'?'active':'' ?>">
                <i class="fas fa-globe"></i> Tous les projets
            </a>
            <a href="?action=projects&tab=mes" class="tab-btn <?= $tab==='mes'?'active':'' ?>">
                <i class="fas fa-folder-open"></i> Mes Projets
                <?php
                $cnt = count($mesProjets);
                if ($cnt > 0): ?>
                    <span class="tab-badge"><?= $cnt ?></span>
                <?php endif; ?>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($tab === 'tous'): ?>

    <div class="search-card">
        <form method="GET" class="search-box">
            <input type="hidden" name="action" value="projects">
            <input type="hidden" name="tab" value="tous">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher...">
            <select name="statut">
                <option value="">Tous les statuts</option>
                <option value="en_attente" <?= ($_GET['statut'] ?? '') === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                <option value="en_cours"   <?= ($_GET['statut'] ?? '') === 'en_cours'   ? 'selected' : '' ?>>En cours</option>
                <option value="termine"    <?= ($_GET['statut'] ?? '') === 'termine'    ? 'selected' : '' ?>>Terminé</option>
            </select>
            <input type="number" name="budget_min" min="0" placeholder="Budget min" value="<?= htmlspecialchars($_GET['budget_min'] ?? '') ?>">
            <input type="number" name="budget_max" min="0" placeholder="Budget max" value="<?= htmlspecialchars($_GET['budget_max'] ?? '') ?>">
            <button type="submit" class="btn btn-primary">Filtrer</button>
            <?php if (!empty($_GET['q']) || !empty($_GET['statut']) || !empty($_GET['budget_min']) || !empty($_GET['budget_max'])): ?>
                <a href="?action=projects" class="btn btn-secondary">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($projects)): ?>
        <div class="empty-state">Aucun projet trouvé pour votre recherche.</div>
    <?php else: ?>
        <div class="projects-grid">
            <?php foreach ($projects as $project): ?>
                <?php
                    $status = $project->getStatut();
                    $statusClass = 'status-en-attente';
                    if ($status === 'en_cours') {
                        $statusClass = 'status-en-cours';
                    } elseif ($status === 'termine') {
                        $statusClass = 'status-termine';
                    }
                ?>
                <article class="project-card">
                    <h3><?= htmlspecialchars($project->getTitre()) ?></h3>
                    <div class="project-meta">
                        <span><?= htmlspecialchars($project->getDateCreation()) ?></span>
                        <span><?= number_format((float)$project->getBudget(), 2, ',', ' ') ?> TND</span>
                    </div>
                    <p class="project-description"><?= nl2br(htmlspecialchars($project->getDescription())) ?></p>
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
                        <span class="status-badge <?= $statusClass ?>"><?= formatStatusLabel($status) ?></span>
                        <?php if (isset($_SESSION['user_role']) && (int)$_SESSION['user_role'] === 3): ?>
                            <button class="btn btn-primary btn-compact"
                                onclick="postuler(<?= $project->getId() ?>)">
                                Postuler
                            </button>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php elseif ($tab === 'mes' && isset($_SESSION['user_id'])): ?>

        <?php if ($role === 3): ?>
            <!-- ── FREELANCER : Candidatures ── -->
            <div class="mes-section-title">Mes Candidatures</div>
            <?php if (empty($mesCandidatures)): ?>
                <div class="empty-state">Aucune candidature. <a href="?action=projects&tab=tous">Parcourez les projets</a>.</div>
            <?php else: ?>
            <div class="cand-grid">
                <?php foreach ($mesCandidatures as $c):
                    $cb = ['en_attente'=>'#f39c12','accepte'=>'#27ae60','refuse'=>'#e74c3c'];
                    $cl = ['en_attente'=>'En attente','accepte'=>'Acceptée','refuse'=>'Refusée'];
                    $cs = $c->getStatut();
                ?>
                <div class="cand-card">
                    <div class="cand-card-title"><?= htmlspecialchars($c->getTitreProjet()) ?></div>
                    <div class="cand-card-date"><?= date('d/m/Y', strtotime($c->getCreatedAt())) ?></div>
                    <span class="cand-badge" style="background:<?= $cb[$cs]??'#999' ?>20;color:<?= $cb[$cs]??'#999' ?>;border:1px solid <?= $cb[$cs]??'#999' ?>40;">
                        <?= $cl[$cs]??$cs ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- ── FREELANCER : Projets acceptés + Kanban ── -->
            <?php if (!empty($mesProjets)): ?>
            <div class="mes-section-title" style="margin-top:2.5rem;">Mes Projets Acceptés</div>
            <?php foreach ($mesProjets as $p):
                $taches = $tachesParProjet[$p['id']] ?? [];
                $cols = [
                    'a_faire'  => ['label'=>'À faire',  'color'=>'#e74c3c','items'=>[]],
                    'en_cours' => ['label'=>'En cours', 'color'=>'#f39c12','items'=>[]],
                    'termine'  => ['label'=>'Terminé',  'color'=>'#27ae60','items'=>[]],
                ];
                foreach ($taches as $t) $cols[$t->getStatut()]['items'][] = $t;
            ?>
            <div class="mes-projet-block">
                <div class="mes-projet-head">
                    <span><?= htmlspecialchars($p['titre']) ?></span>
                    <div style="display:flex;align-items:center;gap:0.75rem;">
                        <?php if (!empty($p['nom_client'])): ?>
                        <span style="font-size:0.8rem;color:rgba(255,255,255,0.6);font-weight:400;">
                            <i class="fas fa-user" style="color:var(--amber);margin-right:0.3rem;"></i><?= htmlspecialchars($p['prenom_client'].' '.$p['nom_client']) ?>
                        </span>
                        <?php endif; ?>
                        <span><?= number_format((float)$p['budget'],2,',',' ') ?> TND</span>
                    </div>
                </div>

                <!-- Avancement -->
                <?php $avc = (int)($p['avancement'] ?? 0); ?>
                <div class="avancement-wrap" id="avc-wrap-<?= $p['id'] ?>">
                    <div class="avancement-header">
                        <span class="avancement-label">Avancement du projet</span>
                        <span class="avancement-pct" id="avc-pct-<?= $p['id'] ?>"><?= $avc ?>%</span>
                    </div>
                    <div class="avancement-bar-bg">
                        <div class="avancement-bar-fill" id="avc-bar-<?= $p['id'] ?>" style="width:<?= $avc ?>%;"></div>
                    </div>
                    <div class="avancement-controls">
                        <input type="range" min="0" max="100" step="5" value="<?= $avc ?>"
                            class="avancement-slider"
                            id="avc-slider-<?= $p['id'] ?>"
                            oninput="updateAvcDisplay(<?= $p['id'] ?>, this.value)"
                            onchange="saveAvancement(<?= $p['id'] ?>, this.value)">
                        <span class="avancement-hint">Glissez pour mettre à jour</span>
                    </div>
                </div>
                <div class="kanban-board" style="padding:1rem;">
                    <?php foreach ($cols as $colKey => $col): ?>
                    <div class="kanban-col" data-statut="<?= $colKey ?>" ondragover="kanbanDragOver(event)" ondrop="kanbanDrop(event)" ondragleave="kanbanDragLeave(event)">
                        <div class="kanban-col-header" style="background:<?= $col['color'] ?>;">
                            <?= $col['label'] ?> <span class="kanban-count" id="kcount-<?= $colKey ?>-<?= $p['id'] ?>"><?= count($col['items']) ?></span>
                        </div>
                        <div class="kanban-col-body" id="kcol-<?= $colKey ?>-<?= $p['id'] ?>">
                            <?php if (empty($col['items'])): ?>
                                <div class="kanban-empty">Aucune tâche</div>
                            <?php else: ?>
                                <?php foreach ($col['items'] as $t): ?>
                                <div class="kanban-card" id="ktache-<?= $t->getId() ?>" draggable="true" ondragstart="kanbanDragStart(event, <?= $t->getId() ?>)">
                                    <div class="kanban-drag-handle">⠿</div>
                                    <div class="kanban-card-title"><?= htmlspecialchars($t->getTitre()) ?></div>
                                    <?php if ($t->getDescription()): ?>
                                        <div class="kanban-card-desc"><?= htmlspecialchars($t->getDescription()) ?></div>
                                    <?php endif; ?>
                                    <?php if ($t->getPrix() > 0): ?>
                                        <div class="kanban-card-prix"><?= number_format((float)$t->getPrix(),2,',',' ') ?> TND</div>
                                    <?php endif; ?>
                                    <div class="kanban-card-actions">
                                        <button class="btn-del-k" onclick="deleteTacheK(<?= $t->getId() ?>)">✕ Supprimer</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Formulaire ajout tâche -->
                <div class="add-tache-wrap">
                    <div class="add-tache-toggle" onclick="toggleAddTache(<?= $p['id'] ?>)">
                        <span>+ Ajouter une tâche</span>
                    </div>
                    <div class="add-tache-form-wrap" id="add-form-<?= $p['id'] ?>" style="display:none;">
                        <div class="budget-restant-info" id="budget-info-<?= $p['id'] ?>">
                            <span>Budget projet : <strong><?= number_format((float)$p['budget'],2,',',' ') ?> TND</strong></span>
                            <span>Alloué aux tâches : <strong id="budget-alloue-<?= $p['id'] ?>"><?= number_format((float)$p['budget_total_taches'],2,',',' ') ?> TND</strong></span>
                            <span>Restant : <strong id="budget-restant-<?= $p['id'] ?>" class="<?= $p['budget_restant'] <= 0 ? 'budget-zero' : 'budget-ok' ?>"><?= number_format((float)$p['budget_restant'],2,',',' ') ?> TND</strong></span>
                        </div>
                        <form class="add-tache-inline" data-projet="<?= $p['id'] ?>" data-budget="<?= (float)$p['budget'] ?>" data-alloue="<?= (float)$p['budget_total_taches'] ?>" novalidate>
                            <div class="ati-row">
                                <div class="ati-field">
                                    <input type="text" name="titre" placeholder="Titre de la tâche *" id="ati_titre_<?= $p['id'] ?>">
                                    <span class="ati-err" id="ati_err_<?= $p['id'] ?>">Titre obligatoire.</span>
                                </div>
                                <div class="ati-field">
                                    <select name="statut">
                                        <option value="a_faire">À faire</option>
                                        <option value="en_cours">En cours</option>
                                        <option value="termine">Terminé</option>
                                    </select>
                                </div>
                            </div>
                            <div class="ati-row">
                                <div class="ati-field">
                                    <input type="number" name="prix" min="0" step="0.01" placeholder="Prix (TND)" id="ati_prix_<?= $p['id'] ?>">
                                    <span class="ati-err" id="ati_prix_err_<?= $p['id'] ?>"></span>
                                </div>
                            </div>
                            <div class="ati-field ati-full">
                                <textarea name="description" rows="2" placeholder="Description (optionnel)"></textarea>
                            </div>
                            <div class="ati-actions">
                                <button type="button" class="btn-ati-cancel" onclick="toggleAddTache(<?= $p['id'] ?>)">Annuler</button>
                                <button type="submit" class="btn btn-primary" style="padding:0.55rem 1.2rem;font-size:0.85rem;">Ajouter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

        <?php elseif ($role === 2): ?>
            <!-- ── CLIENT : Ses projets + Kanban tâches ── -->
            <div class="mes-section-title">Mes Projets Soumis</div>
            <?php if (empty($mesProjets)): ?>
                <div class="empty-state">Vous n'avez pas encore soumis de projet. <a href="?action=projects&tab=tous">Voir les projets</a>.</div>
            <?php else: ?>
                <?php foreach ($mesProjets as $p):
                    $taches   = $tachesParProjet[$p['id']] ?? [];
                    $total    = count($taches);
                    $termines = count(array_filter($taches, fn($t) => $t['statut']==='termine'));
                    $pct      = $total > 0 ? round(($termines/$total)*100) : 0;
                    $etatBadge = ['publie'=>'#27ae60','en_attente_validation'=>'#f39c12','refuse'=>'#e74c3c'];
                    $etatLabel = ['publie'=>'Publié','en_attente_validation'=>'En attente','refuse'=>'Refusé'];
                    $e = $p['etat'];
                    $cols = [
                        'a_faire'  => ['label'=>'À faire',  'color'=>'#e74c3c','tasks'=>[]],
                        'en_cours' => ['label'=>'En cours', 'color'=>'#f39c12','tasks'=>[]],
                        'termine'  => ['label'=>'Terminé',  'color'=>'#27ae60','tasks'=>[]],
                    ];
                    foreach ($taches as $t) $cols[$t['statut']]['tasks'][] = $t;
                ?>
                <div class="mes-projet-block">
                    <div class="mes-projet-head">
                        <span><?= htmlspecialchars($p['titre']) ?></span>
                        <div style="display:flex;align-items:center;gap:0.75rem;">
                            <span class="cand-badge" style="background:<?= $etatBadge[$e]??'#999' ?>30;color:<?= $etatBadge[$e]??'#fff' ?>;border:1px solid <?= $etatBadge[$e]??'#999' ?>50;">
                                <?= $etatLabel[$e]??$e ?>
                            </span>
                            <?php if ($p['statut'] === 'en_attente'): ?>
                            <button class="btn-edit-projet" onclick="openEditProjet(<?= $p['id'] ?>, <?= htmlspecialchars(json_encode($p['titre'])) ?>, <?= htmlspecialchars(json_encode($p['description'])) ?>, <?= (float)$p['budget'] ?>)">
                                ✏️ Modifier
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Avancement défini par le freelancer -->
                    <?php $avc = (int)($p['avancement'] ?? 0); ?>
                    <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--beige-border);background:var(--creme);">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                            <span style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-light);">Avancement du projet</span>
                            <span style="font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:700;color:var(--amber);"><?= $avc ?>%</span>
                        </div>
                        <div style="background:#e0e0e0;border-radius:999px;height:12px;overflow:hidden;">
                            <div style="width:<?= $avc ?>%;height:100%;border-radius:999px;background:linear-gradient(90deg,var(--amber),#f5a623);transition:width 0.4s ease;"></div>
                        </div>
                        <?php if ($avc === 0): ?>
                            <p style="font-size:0.78rem;color:var(--text-light);margin-top:0.4rem;">En attente de mise à jour par le freelancer.</p>
                        <?php elseif ($avc === 100): ?>
                            <p style="font-size:0.78rem;color:#27ae60;margin-top:0.4rem;font-weight:600;">✓ Projet terminé à 100%</p>
                        <?php else: ?>
                            <p style="font-size:0.78rem;color:var(--text-light);margin-top:0.4rem;">Mis à jour par le freelancer.</p>
                        <?php endif; ?>
                    </div>
                    <div class="kanban-board" style="padding:1rem;">
                        <?php foreach ($cols as $col): ?>
                        <div class="kanban-col">
                            <div class="kanban-col-header" style="background:<?= $col['color'] ?>;">
                                <?= $col['label'] ?> <span class="kanban-count"><?= count($col['tasks']) ?></span>
                            </div>
                            <div class="kanban-col-body">
                                <?php if (empty($col['tasks'])): ?>
                                    <div class="kanban-empty">Aucune tâche</div>
                                <?php else: ?>
                                    <?php foreach ($col['tasks'] as $t): ?>
                                    <div class="kanban-card" id="ctache-<?= $t['id'] ?>">
                                        <div class="kanban-card-title"><?= htmlspecialchars($t['titre']) ?></div>
                                        <?php if (!empty($t['description'])): ?>
                                            <div class="kanban-card-desc"><?= htmlspecialchars($t['description']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($t['prix']) && (float)$t['prix'] > 0): ?>
                                            <div class="kanban-card-prix"><?= number_format((float)$t['prix'],2,',',' ') ?> TND</div>
                                        <?php endif; ?>
                                        <div class="kanban-card-footer">
                                            <span class="kanban-avatar"><?= strtoupper(mb_substr($t['prenom_freelancer'],0,1).mb_substr($t['nom_freelancer'],0,1)) ?></span>
                                            <span class="kanban-name"><?= htmlspecialchars($t['prenom_freelancer'].' '.$t['nom_freelancer']) ?></span>
                                            <?php if ($t['statut'] === 'termine'): ?>
                                                <?php if (!empty($t['payee']) && $t['payee']): ?>
                                                    <span class="badge-payee">✓ Payée</span>
                                                <?php else: ?>
                                                    <button class="btn-payer" onclick="payerTache(<?= $t['id'] ?>, <?= (float)($t['prix'] ?? 0) ?>)">
                                                        💳 Payer
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>

    <?php endif; ?>
</main>

<footer class="footer">
    <p>&copy; 2026 SkillBridge. Tous droits réservés.</p>
</footer>

<?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_role'] === 2): ?>
<div class="modal-overlay" id="addProjectOverlay">

    <div class="modal-box">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <h2>Ajouter un projet</h2>

        <form id="frontAddForm" novalidate>
            <div class="modal-form-row">
                <div class="modal-form-group">
                    <label>Titre</label>
                    <input type="text" id="f_titre" name="titre">
                    <span class="field-error" id="e_titre"></span>
                </div>
                <div class="modal-form-group">
                    <label>Budget (TND)</label>
                    <input type="text" id="f_budget" name="budget">
                    <span class="field-error" id="e_budget"></span>
                </div>
            </div>
            <div class="modal-form-row">
                <div class="modal-form-group">
                    <label>Date de création</label>
                    <input type="date" id="f_date" name="date_creation" value="<?= date('Y-m-d') ?>">
                    <span class="field-error" id="e_date"></span>
                </div>
                <div class="modal-form-group">
                    <label>Statut</label>
                    <select id="f_statut" name="statut">
                        <option value="">-- Choisir --</option>
                        <option value="en_attente">En attente</option>
                        <option value="en_cours">En cours</option>
                        <option value="termine">Terminé</option>
                    </select>
                    <span class="field-error" id="e_statut"></span>
                </div>
            </div>
            <div class="modal-form-group">
                <label>Description</label>
                <textarea id="f_description" name="description" rows="4"></textarea>
                <span class="field-error" id="e_description"></span>
            </div>

            <div class="modal-footer-btns">
                <button type="button" class="btn btn-cancel" onclick="closeModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Modifier Projet -->
<div class="modal-overlay" id="editProjectOverlay">
    <div class="modal-box">
        <button class="modal-close" onclick="closeEditModal()">&times;</button>
        <h2>Modifier le projet</h2>
        <form id="editProjectForm" novalidate>
            <input type="hidden" id="ep_id" name="id">
            <div class="modal-form-group">
                <label>Titre</label>
                <input type="text" id="ep_titre" name="titre">
                <span class="field-error" id="ep_e_titre"></span>
            </div>
            <div class="modal-form-group">
                <label>Budget (TND)</label>
                <input type="text" id="ep_budget" name="budget">
                <span class="field-error" id="ep_e_budget"></span>
            </div>
            <div class="modal-form-group">
                <label>Description</label>
                <textarea id="ep_description" name="description" rows="4"></textarea>
                <span class="field-error" id="ep_e_description"></span>
            </div>
            <div class="modal-footer-btns">
                <button type="button" class="btn btn-cancel" onclick="closeEditModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_role'] === 3): ?>
<style>
.swal2-popup{font-family:"Open Sans",sans-serif;}
/* Avancement */
.avancement-wrap    { padding:1rem 1.5rem; border-bottom:1px solid var(--beige-border); background:var(--creme); }
.avancement-header  { display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem; }
.avancement-label   { font-size:0.82rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-light); }
.avancement-pct     { font-family:'Playfair Display',serif; font-size:1.3rem; font-weight:700; color:var(--amber); }
.avancement-bar-bg  { background:#e0e0e0; border-radius:999px; height:10px; overflow:hidden; margin-bottom:0.6rem; }
.avancement-bar-fill{ height:100%; border-radius:999px; background:linear-gradient(90deg,var(--amber),#f5a623); transition:width 0.3s ease; }
.avancement-controls{ display:flex; align-items:center; gap:1rem; }
.avancement-slider  { flex:1; -webkit-appearance:none; height:4px; border-radius:999px; background:#ddd; outline:none; cursor:pointer; }
.avancement-slider::-webkit-slider-thumb { -webkit-appearance:none; width:18px; height:18px; border-radius:50%; background:var(--amber); cursor:pointer; box-shadow:0 2px 6px rgba(224,112,32,0.4); }
.avancement-hint    { font-size:0.75rem; color:var(--text-light); white-space:nowrap; }
/* Formulaire ajout tâche */
.add-tache-wrap   { border-top:1px solid var(--beige-border); }
.add-tache-toggle { padding:0.85rem 1.5rem; cursor:pointer; color:var(--amber); font-weight:600; font-size:0.88rem; display:flex; align-items:center; gap:0.4rem; transition:background 0.2s; }
.add-tache-toggle:hover { background:rgba(224,112,32,0.05); }
.add-tache-form-wrap { padding:1rem 1.5rem 1.25rem; background:var(--creme); border-top:1px solid var(--beige-border); }
.ati-row  { display:grid; grid-template-columns:1fr 160px; gap:0.75rem; margin-bottom:0.75rem; }
.ati-field { display:flex; flex-direction:column; gap:0.25rem; }
.ati-full { margin-bottom:0.75rem; }
.ati-field input, .ati-field select, .ati-field textarea { width:100%; padding:0.65rem 0.9rem; border:1px solid var(--beige-border); border-radius:var(--radius); font-family:'DM Sans',sans-serif; font-size:0.88rem; background:#fff; }
.ati-field input:focus, .ati-field select:focus, .ati-field textarea:focus { outline:none; border-color:var(--amber); box-shadow:0 0 0 3px var(--amber-glow); }
.ati-field input.err { border-color:#dc3545; }
.ati-err  { color:#dc3545; font-size:0.78rem; display:none; }
.ati-actions { display:flex; justify-content:flex-end; gap:0.5rem; }
.btn-ati-cancel { background:none; border:1px solid var(--beige-border); border-radius:var(--radius); padding:0.55rem 1rem; font-size:0.85rem; cursor:pointer; color:var(--text-mid); transition:background 0.2s; }
.btn-ati-cancel:hover { background:var(--beige-border); }
.kanban-card-actions { margin-top:0.5rem; padding-top:0.5rem; border-top:1px solid #f0f0f0; text-align:right; }
.btn-del-k { background:none; border:none; color:#dc3545; cursor:pointer; font-size:0.75rem; font-weight:600; padding:0.2rem 0.4rem; border-radius:4px; transition:background 0.2s; }
.btn-del-k:hover { background:#f8d7da; }
/* Budget restant */
.budget-restant-info { display:flex; flex-wrap:wrap; gap:1rem; padding:0.65rem 1.5rem; background:#fffbf5; border-bottom:1px solid var(--beige-border); font-size:0.82rem; color:var(--text-mid); }
.budget-restant-info strong { color:var(--charcoal); }
.budget-ok   { color:#27ae60 !important; }
.budget-zero { color:#e74c3c !important; }
/* Prix tâche */
.kanban-card-prix { display:inline-block; margin-top:0.3rem; padding:0.15rem 0.5rem; background:#fff3cd; color:#8a6d3b; border-radius:999px; font-size:0.75rem; font-weight:700; }
/* Drag & Drop */
.kanban-card { cursor:grab; }
.kanban-card:active { cursor:grabbing; }
.kanban-card.dragging { opacity:0.4; transform:rotate(2deg); box-shadow:0 8px 24px rgba(0,0,0,0.2); }
.kanban-col.drag-over .kanban-col-body { background:rgba(224,112,32,0.08); border:2px dashed var(--amber); border-radius:8px; }
.kanban-drag-handle { color:#ccc; font-size:1rem; margin-bottom:0.3rem; cursor:grab; user-select:none; }
@media(max-width:600px){ .ati-row { grid-template-columns:1fr; } }
</style>
<script>
function postuler(idProjet) {
    Swal.fire({
        title: 'Postuler à ce projet ?',
        text: 'Votre candidature sera envoyée à l\'administrateur.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#E07020',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, postuler',
        cancelButtonText: 'Annuler'
    }).then(result => {
        if (!result.isConfirmed) return;
        const fd = new FormData();
        fd.append('action', 'postuler');
        fd.append('id_projet', idProjet);
        fetch('?action=projects', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            Swal.fire({
                icon: data.success ? 'success' : 'warning',
                title: data.success ? 'Candidature envoyée !' : 'Attention',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Erreur réseau' }));
    });
}

// ── Avancement ────────────────────────────────────────────────────────
function updateAvcDisplay(idProjet, val) {
    document.getElementById('avc-pct-' + idProjet).textContent = val + '%';
    document.getElementById('avc-bar-' + idProjet).style.width = val + '%';
}

let avcTimer = {};
function saveAvancement(idProjet, val) {
    clearTimeout(avcTimer[idProjet]);
    avcTimer[idProjet] = setTimeout(() => {
        const fd = new FormData();
        fd.append('action', 'update_avancement');
        fd.append('id_projet', idProjet);
        fd.append('avancement', val);
        fetch('?action=projects', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (data.projet_termine) {
                    Swal.fire({ icon:'success', title:'🎉 Projet terminé !', text:'Toutes les tâches sont terminées et l\'avancement est à 100%.', timer:2500, showConfirmButton:false })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon:'success', title:'Avancement mis à jour !', text: val + '%', timer:1200, showConfirmButton:false });
                }
            } else {
                Swal.fire({ icon:'error', title:'Erreur', text: data.message });
            }
        })
        .catch(() => Swal.fire({ icon:'error', title:'Erreur réseau' }));
    }, 600);
}

// ── Toggle formulaire ajout tâche ─────────────────────────────────────
function toggleAddTache(idProjet) {
    const wrap = document.getElementById('add-form-' + idProjet);
    wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
}

// ── Submit ajout tâche ────────────────────────────────────────────────
document.addEventListener('submit', function(e) {
    if (!e.target.classList.contains('add-tache-inline')) return;
    e.preventDefault();

    const form     = e.target;
    const idProjet = form.dataset.projet;
    const budget   = parseFloat(form.dataset.budget)  || 0;
    const alloue   = parseFloat(form.dataset.alloue)  || 0;
    const titreEl  = document.getElementById('ati_titre_' + idProjet);
    const prixEl   = document.getElementById('ati_prix_'  + idProjet);
    const errEl    = document.getElementById('ati_err_'   + idProjet);
    const prixErrEl= document.getElementById('ati_prix_err_' + idProjet);

    // Reset
    titreEl.classList.remove('err');
    prixEl.classList.remove('err');
    errEl.style.display = 'none';
    prixErrEl.style.display = 'none';

    let valid = true;

    if (titreEl.value.trim() === '') {
        titreEl.classList.add('err');
        errEl.style.display = 'block';
        valid = false;
    }

    const prixVal = parseFloat(prixEl.value) || 0;
    if (prixEl.value !== '' && prixVal < 0) {
        prixEl.classList.add('err');
        prixErrEl.textContent = 'Le prix ne peut pas être négatif.';
        prixErrEl.style.display = 'block';
        valid = false;
    } else if (prixVal > 0 && (alloue + prixVal) > budget) {
        const restant = budget - alloue;
        prixEl.classList.add('err');
        prixErrEl.textContent = 'Budget dépassé. Restant : ' + restant.toFixed(2) + ' TND.';
        prixErrEl.style.display = 'block';
        valid = false;
    }

    if (!valid) return;

    const fd = new FormData(form);
    fd.append('action', 'add_tache');
    fd.append('id_projet', idProjet);

    fetch('?action=projects', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({ icon:'success', title:'Tâche ajoutée !', timer:1200, showConfirmButton:false })
                .then(() => location.reload());
        } else {
            Swal.fire({ icon:'error', title:'Erreur', text: data.message });
        }
    })
    .catch(() => Swal.fire({ icon:'error', title:'Erreur réseau' }));
});

// ── Drag & Drop Kanban ────────────────────────────────────────────────
let draggedId = null;

function kanbanDragStart(event, id) {
    draggedId = id;
    event.dataTransfer.effectAllowed = 'move';
    setTimeout(() => document.getElementById('ktache-' + id).classList.add('dragging'), 0);
}

function kanbanDragOver(event) {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
    event.currentTarget.classList.add('drag-over');
}

function kanbanDragLeave(event) {
    event.currentTarget.classList.remove('drag-over');
}

function kanbanDrop(event) {
    event.preventDefault();
    const col    = event.currentTarget;
    const statut = col.dataset.statut;
    col.classList.remove('drag-over');

    if (!draggedId) return;

    const card = document.getElementById('ktache-' + draggedId);
    if (!card) return;
    card.classList.remove('dragging');

    const labels = { 'a_faire': 'À faire', 'en_cours': 'En cours', 'termine': 'Terminé' };
    const titre  = card.querySelector('.kanban-card-title')?.textContent || 'cette tâche';
    const savedId = draggedId;
    draggedId = null;

    // Trouver la colonne source avant de déplacer
    const sourceCol = card.closest('.kanban-col');

    Swal.fire({
        title: 'Déplacer la tâche ?',
        html: `<b>${titre}</b><br>→ <span style="color:var(--amber);font-weight:700;">${labels[statut]}</span>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#E07020',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, déplacer',
        cancelButtonText: 'Annuler'
    }).then(result => {
        if (!result.isConfirmed) return;

        const colBody = col.querySelector('.kanban-col-body');
        const empty   = colBody.querySelector('.kanban-empty');
        if (empty) empty.remove();
        colBody.appendChild(card);

        // ── Mettre à jour les compteurs ───────────────────────
        if (sourceCol && sourceCol !== col) {
            const srcBody    = sourceCol.querySelector('.kanban-col-body');
            const srcCount   = sourceCol.querySelector('.kanban-count');
            const destCount  = col.querySelector('.kanban-count');
            const srcCards   = srcBody.querySelectorAll('.kanban-card').length;
            const destCards  = colBody.querySelectorAll('.kanban-card').length;
            if (srcCount)  srcCount.textContent  = srcCards;
            if (destCount) destCount.textContent = destCards;
            // Remettre "Aucune tâche" si la colonne source est vide
            if (srcCards === 0) {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'kanban-empty';
                emptyDiv.textContent = 'Aucune tâche';
                srcBody.appendChild(emptyDiv);
            }
        }

        const fd = new FormData();
        fd.append('action', 'update_tache_statut');
        fd.append('id', savedId);
        fd.append('statut', statut);

        fetch('?action=projects', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (data.projet_termine) {
                    Swal.fire({ icon:'success', title:'🎉 Projet terminé !', text:'Toutes les tâches sont terminées et l\'avancement est à 100%.', timer:2500, showConfirmButton:false })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon:'success', title:'Tâche déplacée !', text: `Statut : ${labels[statut]}`, timer:1200, showConfirmButton:false });
                }
            } else {
                Swal.fire({ icon:'error', title:'Erreur', text: data.message || 'Impossible de déplacer la tâche.' });
                location.reload();
            }
        })
        .catch(() => { Swal.fire({ icon:'error', title:'Erreur réseau' }); location.reload(); });
    });
}

document.addEventListener('dragend', function(e) {
    if (e.target.classList.contains('kanban-card')) {
        e.target.classList.remove('dragging');
    }
    document.querySelectorAll('.kanban-col').forEach(c => c.classList.remove('drag-over'));
});

// ── Supprimer tâche ───────────────────────────────────────────────────
function deleteTacheK(id) {
    Swal.fire({
        title: 'Supprimer cette tâche ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then(r => {
        if (!r.isConfirmed) return;
        const fd = new FormData();
        fd.append('action', 'delete_tache');
        fd.append('id', id);
        fetch('?action=projects', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon:'success', title:'Supprimée', timer:1000, showConfirmButton:false })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon:'error', title:'Erreur' });
            }
        });
    });
}
</script>
<?php endif; ?>
<?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_role'] === 2): ?>
<script>
function closeModal() {
    document.getElementById('addProjectOverlay').classList.remove('active');
    document.getElementById('frontAddForm').reset();
    ['titre','budget','date','statut','description'].forEach(f => {
        const el  = document.getElementById('f_' + f);
        const err = document.getElementById('e_' + f);
        if (el)  el.classList.remove('error');
        if (err) err.textContent = '';
    });
}

document.getElementById('addProjectOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// ── Modifier projet ───────────────────────────────────────────────────
function openEditProjet(id, titre, description, budget) {
    document.getElementById('ep_id').value          = id;
    document.getElementById('ep_titre').value       = titre;
    document.getElementById('ep_description').value = description;
    document.getElementById('ep_budget').value      = budget;
    ['titre','budget','description'].forEach(f => {
        const el  = document.getElementById('ep_' + f);
        const err = document.getElementById('ep_e_' + f);
        if (el)  el.classList.remove('error');
        if (err) err.textContent = '';
    });
    document.getElementById('editProjectOverlay').classList.add('active');
}

function closeEditModal() {
    document.getElementById('editProjectOverlay').classList.remove('active');
}

document.getElementById('editProjectOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

document.getElementById('editProjectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let valid = true;

    const titre = document.getElementById('ep_titre');
    const budget = document.getElementById('ep_budget');
    const desc = document.getElementById('ep_description');

    [titre, budget, desc].forEach(el => { el.classList.remove('error'); el.nextElementSibling.textContent = ''; });

    if (titre.value.trim() === '') {
        titre.classList.add('error'); titre.nextElementSibling.textContent = 'Le titre est obligatoire.'; valid = false;
    }
    if (budget.value.trim() === '' || isNaN(budget.value) || parseFloat(budget.value) < 0) {
        budget.classList.add('error'); budget.nextElementSibling.textContent = 'Budget invalide.'; valid = false;
    }
    if (desc.value.trim() === '') {
        desc.classList.add('error'); desc.nextElementSibling.textContent = 'La description est obligatoire.'; valid = false;
    }
    if (!valid) return;

    const fd = new FormData(this);
    fd.append('action', 'update_projet');

    fetch('?action=projects', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeEditModal();
            Swal.fire({ icon:'success', title:'Projet mis à jour !', timer:1500, showConfirmButton:false })
                .then(() => location.reload());
        } else {
            Swal.fire({ icon:'error', title:'Erreur', text: data.message });
        }
    })
    .catch(() => Swal.fire({ icon:'error', title:'Erreur réseau' }));
});

document.getElementById('frontAddForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let valid = true;

    const fields = {
        titre:       { el: document.getElementById('f_titre'),       err: document.getElementById('e_titre') },
        budget:      { el: document.getElementById('f_budget'),      err: document.getElementById('e_budget') },
        date:        { el: document.getElementById('f_date'),        err: document.getElementById('e_date') },
        statut:      { el: document.getElementById('f_statut'),      err: document.getElementById('e_statut') },
        description: { el: document.getElementById('f_description'), err: document.getElementById('e_description') },
    };

    Object.values(fields).forEach(f => { f.el.classList.remove('error'); f.err.textContent = ''; });

    if (fields.titre.el.value.trim() === '') {
        fields.titre.el.classList.add('error'); fields.titre.err.textContent = 'Le titre est obligatoire.'; valid = false;
    } else if (fields.titre.el.value.trim().length > 150) {
        fields.titre.el.classList.add('error'); fields.titre.err.textContent = 'Maximum 150 caractères.'; valid = false;
    }

    const bv = fields.budget.el.value.trim();
    if (bv === '') {
        fields.budget.el.classList.add('error'); fields.budget.err.textContent = 'Le budget est obligatoire.'; valid = false;
    } else if (isNaN(bv) || parseFloat(bv) < 0) {
        fields.budget.el.classList.add('error'); fields.budget.err.textContent = 'Entrez un nombre positif.'; valid = false;
    }

    if (fields.date.el.value === '') {
        fields.date.el.classList.add('error'); fields.date.err.textContent = 'La date est obligatoire.'; valid = false;
    }

    if (fields.statut.el.value === '') {
        fields.statut.el.classList.add('error'); fields.statut.err.textContent = 'Veuillez choisir un statut.'; valid = false;
    }

    if (fields.description.el.value.trim() === '') {
        fields.description.el.classList.add('error'); fields.description.err.textContent = 'La description est obligatoire.'; valid = false;
    } else if (fields.description.el.value.trim().length > 2000) {
        fields.description.el.classList.add('error'); fields.description.err.textContent = 'Maximum 2000 caractères.'; valid = false;
    }

    if (!valid) return;

    const formData = new FormData(this);
    formData.append('action', 'add');

    fetch('?action=projects', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal();
            Swal.fire({
                icon: 'success',
                title: 'Projet soumis !',
                text: data.message,
                timer: 2500,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire({ icon: 'error', title: 'Erreur', text: data.message });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Erreur réseau', text: 'Réessayez plus tard.' }));
});

// ── Payer une tâche ───────────────────────────────────────────────────
function payerTache(id, prix) {
    const montant = prix > 0 ? ' (' + prix.toFixed(2) + ' TND)' : '';
    Swal.fire({
        title: 'Confirmer le paiement ?',
        html: `Vous allez payer cette tâche<strong>${montant}</strong>.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '💳 Oui, payer',
        cancelButtonText: 'Annuler'
    }).then(result => {
        if (!result.isConfirmed) return;
        window.location.href = '?action=paiement&id_tache=' + id;
    });
}
</script>
<?php endif; ?>
</body>
</html>
