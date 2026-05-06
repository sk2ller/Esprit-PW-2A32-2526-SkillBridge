<?php
require_once __DIR__ . '/../../Controllers/ProjectController.php';
require_once __DIR__ . '/../../Controllers/CandidatureController.php';
require_once __DIR__ . '/../../Controllers/MessageController.php';
require_once __DIR__ . '/../../Controllers/ChatbotController.php';
require_once __DIR__ . '/../../Models/Tache.php';

$projectController = new ProjectController();
$cc                = new CandidatureController();
$mc                = new MessageController();

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

    if ($ajaxAction === 'edit_tache') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 3) {
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']); exit;
        }
        $id    = (int)($_POST['id'] ?? 0);
        $titre = trim($_POST['titre'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $stat  = $_POST['statut'] ?? 'a_faire';
        $prix  = (float)($_POST['prix'] ?? 0);
        if (!$titre) { echo json_encode(['success'=>false,'message'=>'Le titre est obligatoire.']); exit; }
        $db = Config::getConnexion();
        try {
            $q = $db->prepare("UPDATE tache SET titre=:t, description=:d, statut=:s, prix=:p WHERE id=:id AND id_freelancer=:f");
            $q->execute(['t'=>$titre,'d'=>$desc,'s'=>$stat,'p'=>$prix,'id'=>$id,'f'=>$_SESSION['user_id']]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
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

    if ($ajaxAction === 'envoyer_message') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non connecté.']); exit;
        }
        $id_projet = (int)($_POST['id_projet'] ?? 0);
        $contenu   = trim($_POST['contenu'] ?? '');
        echo json_encode($mc->envoyerMessage($id_projet, $_SESSION['user_id'], $contenu));
        exit;
    }

    if ($ajaxAction === 'get_messages') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non connecté.']); exit;
        }
        $id_projet = (int)($_POST['id_projet'] ?? 0);
        echo json_encode($mc->getMessages($id_projet, $_SESSION['user_id']));
        exit;
    }

    if ($ajaxAction === 'get_messages_poll') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non connecté.']); exit;
        }
        $id_projet = (int)($_POST['id_projet'] ?? 0);
        $depuis_id = (int)($_POST['depuis_id'] ?? 0);
        echo json_encode($mc->getNouveauxMessages($id_projet, $_SESSION['user_id'], $depuis_id));
        exit;
    }

    if ($ajaxAction === 'get_notifs') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]); exit;
        }
        echo json_encode($mc->getNotifications($_SESSION['user_id']));
        exit;
    }

    if ($ajaxAction === 'marquer_lu') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]); exit;
        }
        $id_projet = (int)($_POST['id_projet'] ?? 0);
        echo json_encode($mc->marquerLu($id_projet, $_SESSION['user_id']));
        exit;
    }

    if ($ajaxAction === 'marquer_tout_lu') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]); exit;
        }
        echo json_encode($mc->marquerToutLu($_SESSION['user_id']));
        exit;
    }

    if ($ajaxAction === 'chatbot_gemini') {
        ChatbotController::handleRequest();
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
        /* ── Notification Bell ── */
        .notif-bell-wrap { position:relative; margin-left:auto; }
        .notif-bell-btn { background:none; border:none; color:rgba(255,255,255,0.85); font-size:1.2rem; cursor:pointer; padding:0.3rem 0.5rem; border-radius:8px; position:relative; transition:color 0.2s; }
        .notif-bell-btn:hover { color:#fff; }
        .notif-bell-btn .notif-count { position:absolute; top:-4px; right:-4px; background:#ef4444; color:#fff; border-radius:999px; font-size:0.65rem; font-weight:800; min-width:18px; height:18px; display:flex; align-items:center; justify-content:center; padding:0 4px; border:2px solid var(--charcoal); animation:bell-shake 0.5s ease; }
        @keyframes bell-shake { 0%,100%{transform:rotate(0)} 20%{transform:rotate(-15deg)} 40%{transform:rotate(15deg)} 60%{transform:rotate(-10deg)} 80%{transform:rotate(10deg)} }
        .notif-dropdown { position:absolute; top:calc(100% + 10px); right:0; width:320px; background:#fff; border-radius:12px; box-shadow:0 8px 32px rgba(0,0,0,0.18); z-index:3000; overflow:hidden; }
        .notif-header { display:flex; justify-content:space-between; align-items:center; padding:0.85rem 1rem; border-bottom:1px solid #f0f0f0; font-weight:700; font-size:0.88rem; color:#1a1a2e; }
        .notif-clear-btn { background:none; border:none; color:#2563eb; font-size:0.78rem; cursor:pointer; font-weight:600; }
        .notif-clear-btn:hover { text-decoration:underline; }
        .notif-list { max-height:320px; overflow-y:auto; }
        .notif-empty { text-align:center; color:#9ca3af; font-size:0.85rem; padding:2rem 1rem; }
        .notif-item { display:flex; gap:0.75rem; padding:0.85rem 1rem; border-bottom:1px solid #f8f8f8; cursor:pointer; transition:background 0.15s; align-items:flex-start; }
        .notif-item:hover { background:#f0f7ff; }
        .notif-item.unread { background:#eff6ff; }
        .notif-icon { width:36px; height:36px; border-radius:50%; background:#dbeafe; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
        .notif-content { flex:1; min-width:0; }
        .notif-projet { font-weight:700; font-size:0.82rem; color:#1a1a2e; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .notif-text { font-size:0.8rem; color:#4b5563; margin-top:0.1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .notif-time { font-size:0.72rem; color:#9ca3af; margin-top:0.2rem; }
        .notif-dot { width:8px; height:8px; border-radius:50%; background:#2563eb; flex-shrink:0; margin-top:4px; }
        /* Chat */
        .chat-section { border-top: 1px solid var(--beige-border); }
        .chat-toggle { padding: 0.85rem 1.5rem; cursor: pointer; color: #2563eb; font-weight: 600; font-size: 0.88rem; display: flex; align-items: center; gap: 0.5rem; transition: background 0.2s; justify-content: space-between; }
        .chat-toggle:hover { background: rgba(37,99,235,0.05); }
        .chat-body { border-top: 1px solid var(--beige-border); }
        .chat-messages { height: 280px; overflow-y: auto; padding: 1rem 1.5rem; display: flex; flex-direction: column; gap: 0.6rem; background: #f8f9fa; }
        .chat-msg { max-width: 75%; display: flex; flex-direction: column; gap: 0.15rem; }
        .chat-msg.mine { align-self: flex-end; align-items: flex-end; }
        .chat-msg.other { align-self: flex-start; align-items: flex-start; }
        .chat-bubble { padding: 0.6rem 0.9rem; border-radius: 12px; font-size: 0.88rem; line-height: 1.45; word-break: break-word; }
        .chat-msg.mine .chat-bubble { background: #2563eb; color: #fff; border-bottom-right-radius: 3px; }
        .chat-msg.other .chat-bubble { background: #fff; color: #1a1a2e; border: 1px solid #e5e7eb; border-bottom-left-radius: 3px; }
        .chat-meta { font-size: 0.72rem; color: #9ca3af; }
        .chat-input-wrap { display: flex; gap: 0.5rem; padding: 0.75rem 1.5rem; border-top: 1px solid var(--beige-border); background: #fff; }
        .chat-input { flex: 1; padding: 0.6rem 0.9rem; border: 1.5px solid var(--beige-border); border-radius: 8px; font-family: 'DM Sans', sans-serif; font-size: 0.88rem; }
        .chat-input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .chat-send-btn { background: #2563eb; color: #fff; border: none; border-radius: 8px; padding: 0.6rem 1.1rem; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: background 0.2s; white-space: nowrap; }
        .chat-send-btn:hover { background: #1d4ed8; }
        .chat-badge { background: #ef4444; color: #fff; border-radius: 999px; padding: 0.1rem 0.5rem; font-size: 0.72rem; font-weight: 700; display: none; }
        .chat-empty { text-align: center; color: #9ca3af; font-size: 0.85rem; padding: 2rem 0; }
    </style>
</head>
<body class="has-sidebar">
<?php require __DIR__ . '/partials/sidebar.php'; ?>
<nav class="navbar-top">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;">
        <button class="sb-toggle" onclick="openSidebar()"><i class="fas fa-bars"></i></button>
        <div class="nav-buttons" style="display:flex;align-items:center;gap:0.5rem;margin-left:auto;">
            <?php if (isset($_SESSION['user_id']) && in_array((int)$_SESSION['user_role'], [2,3])): ?>
            <div class="notif-bell-wrap" id="notifBellWrap">
                <button class="notif-bell-btn" onclick="toggleNotifDropdown()" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notif-count" id="notifCount" style="display:none;">0</span>
                </button>
                <div class="notif-dropdown" id="notifDropdown" style="display:none;">
                    <div class="notif-header">
                        <span>Notifications</span>
                        <button class="notif-clear-btn" onclick="marquerToutLu()">Tout marquer lu</button>
                    </div>
                    <div class="notif-list" id="notifList">
                        <div class="notif-empty">Aucune nouvelle notification</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
                                        <button class="btn-edit-k" onclick="openEditTache(<?= $t->getId() ?>, <?= htmlspecialchars(json_encode($t->getTitre())) ?>, <?= htmlspecialchars(json_encode($t->getDescription() ?? '')) ?>, '<?= $t->getStatut() ?>', <?= (float)$t->getPrix() ?>, <?= (float)$p['budget'] ?>, <?= (float)$p['budget_total_taches'] ?>)">✏ Modifier</button>
                                        <button class="btn-del-k" onclick="deleteTacheK(<?= $t->getId() ?>)">✕ Supprimer</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Chat section (freelancer) -->
                <div class="chat-section" id="chat-<?= $p['id'] ?>">
                    <div class="chat-toggle" onclick="toggleChat(<?= $p['id'] ?>)">
                        <span>💬 Messages</span>
                        <span class="chat-badge" id="chat-badge-<?= $p['id'] ?>"></span>
                    </div>
                    <div class="chat-body" id="chat-body-<?= $p['id'] ?>" style="display:none;">
                        <div class="chat-messages" id="chat-msgs-<?= $p['id'] ?>">
                            <!-- messages loaded via JS -->
                        </div>
                        <div class="chat-input-wrap">
                            <input type="text" class="chat-input" id="chat-input-<?= $p['id'] ?>" placeholder="Écrire un message..." maxlength="500">
                            <button class="chat-send-btn" onclick="envoyerMessage(<?= $p['id'] ?>)">Envoyer</button>
                        </div>
                    </div>
                </div>

                <!-- Formulaire ajout tâche -->
                <div class="add-tache-wrap">                    <div class="add-tache-toggle" onclick="toggleAddTache(<?= $p['id'] ?>)">
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
                                    <input type="number" name="prix" min="0" step="0.01" placeholder="Prix (TND) *" id="ati_prix_<?= $p['id'] ?>">
                                    <span class="ati-err" id="ati_prix_err_<?= $p['id'] ?>"></span>
                                </div>
                            </div>
                            <div class="ati-field ati-full">
                                <textarea name="description" rows="2" placeholder="Description *" id="ati_desc_<?= $p['id'] ?>"></textarea>
                                <span class="ati-err" id="ati_desc_err_<?= $p['id'] ?>"></span>
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

                    <!-- Chat section (client) -->
                    <div class="chat-section" id="chat-<?= $p['id'] ?>">
                        <div class="chat-toggle" onclick="toggleChat(<?= $p['id'] ?>)">
                            <span>💬 Messages</span>
                            <span class="chat-badge" id="chat-badge-<?= $p['id'] ?>"></span>
                        </div>
                        <div class="chat-body" id="chat-body-<?= $p['id'] ?>" style="display:none;">
                            <div class="chat-messages" id="chat-msgs-<?= $p['id'] ?>">
                                <!-- messages loaded via JS -->
                            </div>
                            <div class="chat-input-wrap">
                                <input type="text" class="chat-input" id="chat-input-<?= $p['id'] ?>" placeholder="Écrire un message..." maxlength="500">
                                <button class="chat-send-btn" onclick="envoyerMessage(<?= $p['id'] ?>)">Envoyer</button>
                            </div>
                        </div>
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
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_role'] === 3): ?>
<!-- Modal Modifier Tâche -->
<div class="modal-overlay" id="editTacheOverlay">
    <div class="modal-box" style="max-width:480px;">
        <button class="modal-close" onclick="closeEditTache()">&times;</button>
        <h2>Modifier la tâche</h2>
        <form id="editTacheForm" novalidate>
            <input type="hidden" id="et_id" name="id">
            <input type="hidden" id="et_budget">
            <input type="hidden" id="et_alloue">
            <input type="hidden" id="et_prix_original">
            <div class="modal-form-group">
                <label>Titre <span style="color:#dc3545">*</span></label>
                <input type="text" id="et_titre" name="titre">
                <span class="field-error" id="et_e_titre"></span>
            </div>
            <div class="modal-form-row">
                <div class="modal-form-group">
                    <label>Statut</label>
                    <select id="et_statut" name="statut">
                        <option value="a_faire">À faire</option>
                        <option value="en_cours">En cours</option>
                        <option value="termine">Terminé</option>
                    </select>
                </div>
                <div class="modal-form-group">
                    <label>Prix (TND) <span style="color:#dc3545">*</span></label>
                    <input type="number" id="et_prix" name="prix" min="0" step="0.01" placeholder="0.00">
                    <span class="field-error" id="et_e_prix"></span>
                </div>
            </div>
            <div class="modal-form-group">
                <label>Description <span style="color:#dc3545">*</span></label>
                <textarea id="et_description" name="description" rows="3"></textarea>
                <span class="field-error" id="et_e_description"></span>
            </div>
            <div class="modal-footer-btns">
                <button type="button" class="btn btn-cancel" onclick="closeEditTache()">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
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
.ati-field input.ok  { border-color:#27ae60; }
.ati-field textarea.err { border-color:#dc3545; }
.ati-field textarea.ok  { border-color:#27ae60; }
.ati-err  { color:#dc3545; font-size:0.78rem; display:none; }
.ati-actions { display:flex; justify-content:flex-end; gap:0.5rem; }
.btn-ati-cancel { background:none; border:1px solid var(--beige-border); border-radius:var(--radius); padding:0.55rem 1rem; font-size:0.85rem; cursor:pointer; color:var(--text-mid); transition:background 0.2s; }
.btn-ati-cancel:hover { background:var(--beige-border); }
.kanban-card-actions { margin-top:0.5rem; padding-top:0.5rem; border-top:1px solid #f0f0f0; text-align:right; }
.btn-del-k { background:none; border:none; color:#dc3545; cursor:pointer; font-size:0.75rem; font-weight:600; padding:0.2rem 0.4rem; border-radius:4px; transition:background 0.2s; }
.btn-del-k:hover { background:#f8d7da; }
.btn-edit-k { background:none; border:none; color:#2563eb; cursor:pointer; font-size:0.75rem; font-weight:600; padding:0.2rem 0.4rem; border-radius:4px; transition:background 0.2s; margin-right:0.3rem; }
.btn-edit-k:hover { background:#dbeafe; }
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

    const form      = e.target;
    const idProjet  = form.dataset.projet;
    const budget    = parseFloat(form.dataset.budget) || 0;
    const alloue    = parseFloat(form.dataset.alloue) || 0;
    const titreEl   = document.getElementById('ati_titre_'    + idProjet);
    const prixEl    = document.getElementById('ati_prix_'     + idProjet);
    const descEl    = document.getElementById('ati_desc_'     + idProjet);
    const errEl     = document.getElementById('ati_err_'      + idProjet);
    const prixErrEl = document.getElementById('ati_prix_err_' + idProjet);
    const descErrEl = document.getElementById('ati_desc_err_' + idProjet);

    // Reset
    [titreEl, prixEl, descEl].forEach(el => el.classList.remove('err', 'ok'));
    [errEl, prixErrEl, descErrEl].forEach(el => { el.style.display = 'none'; el.textContent = ''; });

    let valid = true;

    // ── Titre ──────────────────────────────────────────────────────
    const titreVal = titreEl.value.trim();
    if (titreVal === '') {
        titreEl.classList.add('err');
        errEl.textContent = 'Le titre est obligatoire.';
        errEl.style.display = 'block';
        valid = false;
    } else if (titreVal.length < 3) {
        titreEl.classList.add('err');
        errEl.textContent = 'Le titre doit contenir au moins 3 caractères.';
        errEl.style.display = 'block';
        valid = false;
    } else if (titreVal.length > 150) {
        titreEl.classList.add('err');
        errEl.textContent = 'Le titre ne doit pas dépasser 150 caractères.';
        errEl.style.display = 'block';
        valid = false;
    } else {
        titreEl.classList.add('ok');
    }

    // ── Prix ───────────────────────────────────────────────────────
    const prixRaw = prixEl.value.trim();
    const prixVal = parseFloat(prixRaw);
    if (prixRaw === '') {
        prixEl.classList.add('err');
        prixErrEl.textContent = 'Le prix est obligatoire.';
        prixErrEl.style.display = 'block';
        valid = false;
    } else if (isNaN(prixVal) || prixVal < 0) {
        prixEl.classList.add('err');
        prixErrEl.textContent = 'Le prix doit être un nombre positif ou nul.';
        prixErrEl.style.display = 'block';
        valid = false;
    } else if (prixVal > 0 && (alloue + prixVal) > budget) {
        const restant = Math.max(0, budget - alloue);
        prixEl.classList.add('err');
        prixErrEl.textContent = 'Budget dépassé. Restant : ' + restant.toFixed(2) + ' TND.';
        prixErrEl.style.display = 'block';
        valid = false;
    } else {
        prixEl.classList.add('ok');
    }

    // ── Description ────────────────────────────────────────────────
    const descVal = descEl ? descEl.value.trim() : '';
    if (descEl) {
        if (descVal === '') {
            descEl.classList.add('err');
            descErrEl.textContent = 'La description est obligatoire.';
            descErrEl.style.display = 'block';
            valid = false;
        } else if (descVal.length < 5) {
            descEl.classList.add('err');
            descErrEl.textContent = 'La description doit contenir au moins 5 caractères.';
            descErrEl.style.display = 'block';
            valid = false;
        } else if (descVal.length > 500) {
            descEl.classList.add('err');
            descErrEl.textContent = 'La description ne doit pas dépasser 500 caractères.';
            descErrEl.style.display = 'block';
            valid = false;
        } else {
            descEl.classList.add('ok');
        }
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

// ── Modifier tâche ────────────────────────────────────────────────────
function openEditTache(id, titre, description, statut, prix, budget, alloue) {
    document.getElementById('et_id').value             = id;
    document.getElementById('et_titre').value          = titre;
    document.getElementById('et_description').value    = description || '';
    document.getElementById('et_statut').value         = statut;
    document.getElementById('et_prix').value           = prix > 0 ? prix : '';
    document.getElementById('et_budget').value         = budget || 0;
    document.getElementById('et_alloue').value         = alloue || 0;
    document.getElementById('et_prix_original').value  = prix || 0;
    // Reset erreurs
    ['et_titre','et_prix','et_description'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('error');
    });
    ['et_e_titre','et_e_prix','et_e_description'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = '';
    });
    document.getElementById('editTacheOverlay').classList.add('active');
}

function closeEditTache() {
    document.getElementById('editTacheOverlay').classList.remove('active');
}

document.getElementById('editTacheOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeEditTache();
});

document.getElementById('editTacheForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const titreEl = document.getElementById('et_titre');
    const prixEl  = document.getElementById('et_prix');
    const descEl  = document.getElementById('et_description');
    const budget  = parseFloat(document.getElementById('et_budget').value) || 0;
    const alloue  = parseFloat(document.getElementById('et_alloue').value) || 0;
    const prixOri = parseFloat(document.getElementById('et_prix_original').value) || 0;

    // Reset
    [titreEl, prixEl, descEl].forEach(el => el.classList.remove('error'));
    ['et_e_titre','et_e_prix','et_e_description'].forEach(id => {
        document.getElementById(id).textContent = '';
    });

    let valid = true;

    // Titre
    const titreVal = titreEl.value.trim();
    if (titreVal === '') {
        titreEl.classList.add('error');
        document.getElementById('et_e_titre').textContent = 'Le titre est obligatoire.';
        valid = false;
    } else if (titreVal.length < 3) {
        titreEl.classList.add('error');
        document.getElementById('et_e_titre').textContent = 'Minimum 3 caractères.';
        valid = false;
    } else if (titreVal.length > 150) {
        titreEl.classList.add('error');
        document.getElementById('et_e_titre').textContent = 'Maximum 150 caractères.';
        valid = false;
    }

    // Prix
    const prixRaw = prixEl.value.trim();
    const prixVal = parseFloat(prixRaw);
    if (prixRaw === '') {
        prixEl.classList.add('error');
        document.getElementById('et_e_prix').textContent = 'Le prix est obligatoire.';
        valid = false;
    } else if (isNaN(prixVal) || prixVal < 0) {
        prixEl.classList.add('error');
        document.getElementById('et_e_prix').textContent = 'Le prix doit être un nombre positif ou nul.';
        valid = false;
    } else {
        // Budget : alloué - prix original + nouveau prix <= budget
        const alloueAjuste = alloue - prixOri + prixVal;
        if (alloueAjuste > budget) {
            const restant = Math.max(0, budget - (alloue - prixOri));
            prixEl.classList.add('error');
            document.getElementById('et_e_prix').textContent = 'Budget dépassé. Restant : ' + restant.toFixed(2) + ' TND.';
            valid = false;
        }
    }

    // Description
    const descVal = descEl.value.trim();
    if (descVal === '') {
        descEl.classList.add('error');
        document.getElementById('et_e_description').textContent = 'La description est obligatoire.';
        valid = false;
    } else if (descVal.length < 5) {
        descEl.classList.add('error');
        document.getElementById('et_e_description').textContent = 'Minimum 5 caractères.';
        valid = false;
    } else if (descVal.length > 500) {
        descEl.classList.add('error');
        document.getElementById('et_e_description').textContent = 'Maximum 500 caractères.';
        valid = false;
    }

    if (!valid) return;

    const fd = new FormData(this);
    fd.append('action', 'edit_tache');
    fetch('?action=projects', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeEditTache();
            Swal.fire({ icon:'success', title:'Tâche modifiée !', timer:1200, showConfirmButton:false })
                .then(() => location.reload());
        } else {
            Swal.fire({ icon:'error', title:'Erreur', text: data.message });
        }
    })
    .catch(() => Swal.fire({ icon:'error', title:'Erreur réseau' }));
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

<?php if (isset($_SESSION['user_id']) && in_array((int)$_SESSION['user_role'], [2,3])): ?>
<script>
// ── Chat Pusher (temps réel) ──────────────────────────────────────────
const chatLastId     = {};
const pusherChannels = {};
const CURRENT_USER_ID = <?= (int)($_SESSION['user_id'] ?? 0) ?>;
const pusherClient   = new Pusher('43b4440459346a92371d', { cluster: 'eu' });

function toggleChat(idProjet) {
    const body = document.getElementById('chat-body-' + idProjet);
    const isOpen = body.style.display !== 'none';
    body.style.display = isOpen ? 'none' : 'block';
    if (!isOpen) {
        chargerMessages(idProjet);
        subscriberPusher(idProjet);
    }
}

function subscriberPusher(idProjet) {
    if (pusherChannels[idProjet]) return;
    const channel = pusherClient.subscribe('chat-projet-' + idProjet);
    pusherChannels[idProjet] = channel;
    channel.bind('nouveau-message', function(data) {
        if (parseInt(data.id_expediteur) === CURRENT_USER_ID) return;
        const container = document.getElementById('chat-msgs-' + idProjet);
        if (!container) return;
        const empty = container.querySelector('.chat-empty');
        if (empty) empty.remove();
        container.insertAdjacentHTML('beforeend', renderMessage({
            contenu: data.contenu, expediteur: data.expediteur,
            heure: data.heure, is_mine: false
        }));
        container.scrollTop = container.scrollHeight;
        if (typeof chargerNotifications === 'function') chargerNotifications();
    });
}

function chargerMessages(idProjet) {
    const fd = new FormData();
    fd.append('action', 'get_messages');
    fd.append('id_projet', idProjet);
    fetch('?action=projects', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        const container = document.getElementById('chat-msgs-' + idProjet);
        if (data.messages.length === 0) {
            container.innerHTML = '<div class="chat-empty">Aucun message. Commencez la conversation !</div>';
            chatLastId[idProjet] = 0;
            return;
        }
        container.innerHTML = data.messages.map(m => renderMessage(m)).join('');
        container.scrollTop = container.scrollHeight;
        chatLastId[idProjet] = data.messages[data.messages.length - 1].id;
    });
}

function renderMessage(m) {
    const cls = m.is_mine ? 'mine' : 'other';
    return '<div class="chat-msg ' + cls + '"><div class="chat-bubble">' + escapeHtml(m.contenu) + '</div><div class="chat-meta">' + (m.is_mine ? 'Vous' : escapeHtml(m.expediteur)) + ' · ' + m.heure + '</div></div>';
}

function escapeHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function envoyerMessage(idProjet) {
    const input = document.getElementById('chat-input-' + idProjet);
    const contenu = input.value.trim();
    if (!contenu) return;
    input.value = '';
    const now = new Date();
    const heure = String(now.getDate()).padStart(2,'0') + '/' + String(now.getMonth()+1).padStart(2,'0') + ' ' + String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
    const container = document.getElementById('chat-msgs-' + idProjet);
    const empty = container ? container.querySelector('.chat-empty') : null;
    if (empty) empty.remove();
    if (container) {
        container.insertAdjacentHTML('beforeend', renderMessage({ contenu, is_mine: true, heure }));
        container.scrollTop = container.scrollHeight;
    }
    const fd = new FormData();
    fd.append('action', 'envoyer_message');
    fd.append('id_projet', idProjet);
    fd.append('contenu', contenu);
    fetch('?action=projects', { method: 'POST', body: fd });
}

// Send on Enter key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target.classList.contains('chat-input')) {
        const idProjet = e.target.id.replace('chat-input-', '');
        envoyerMessage(idProjet);
    }
});

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
    if (titre.value.trim() === '') { titre.classList.add('error'); titre.nextElementSibling.textContent = 'Le titre est obligatoire.'; valid = false; }
    if (budget.value.trim() === '' || isNaN(budget.value) || parseFloat(budget.value) < 0) { budget.classList.add('error'); budget.nextElementSibling.textContent = 'Budget invalide.'; valid = false; }
    if (desc.value.trim() === '') { desc.classList.add('error'); desc.nextElementSibling.textContent = 'La description est obligatoire.'; valid = false; }
    if (!valid) return;
    const fd = new FormData(this);
    fd.append('action', 'update_projet');
    fetch('?action=projects', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) { closeEditModal(); Swal.fire({ icon:'success', title:'Projet mis à jour !', timer:1500, showConfirmButton:false }).then(() => location.reload()); }
        else { Swal.fire({ icon:'error', title:'Erreur', text: data.message }); }
    });
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
    if (fields.titre.el.value.trim() === '') { fields.titre.el.classList.add('error'); fields.titre.err.textContent = 'Le titre est obligatoire.'; valid = false; }
    else if (fields.titre.el.value.trim().length > 150) { fields.titre.el.classList.add('error'); fields.titre.err.textContent = 'Maximum 150 caractères.'; valid = false; }
    const bv = fields.budget.el.value.trim();
    if (bv === '') { fields.budget.el.classList.add('error'); fields.budget.err.textContent = 'Le budget est obligatoire.'; valid = false; }
    else if (isNaN(bv) || parseFloat(bv) < 0) { fields.budget.el.classList.add('error'); fields.budget.err.textContent = 'Entrez un nombre positif.'; valid = false; }
    if (fields.date.el.value === '') { fields.date.el.classList.add('error'); fields.date.err.textContent = 'La date est obligatoire.'; valid = false; }
    if (fields.statut.el.value === '') { fields.statut.el.classList.add('error'); fields.statut.err.textContent = 'Veuillez choisir un statut.'; valid = false; }
    if (fields.description.el.value.trim() === '') { fields.description.el.classList.add('error'); fields.description.err.textContent = 'La description est obligatoire.'; valid = false; }
    else if (fields.description.el.value.trim().length > 2000) { fields.description.el.classList.add('error'); fields.description.err.textContent = 'Maximum 2000 caractères.'; valid = false; }
    if (!valid) return;
    const formData = new FormData(this);
    formData.append('action', 'add');
    fetch('?action=projects', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) { closeModal(); Swal.fire({ icon:'success', title:'Projet soumis !', text: data.message, timer:2500, showConfirmButton:false }).then(() => location.reload()); }
        else { Swal.fire({ icon:'error', title:'Erreur', text: data.message }); }
    })
    .catch(() => Swal.fire({ icon:'error', title:'Erreur réseau' }));
});
function payerTache(id, prix) {
    const montant = prix > 0 ? ' (' + prix.toFixed(2) + ' TND)' : '';
    Swal.fire({
        title: 'Confirmer le paiement ?',
        html: `Vous allez payer cette tâche<strong>${montant}</strong>.`,
        icon: 'question', showCancelButton: true,
        confirmButtonColor: '#2563eb', cancelButtonColor: '#6c757d',
        confirmButtonText: '💳 Oui, payer', cancelButtonText: 'Annuler'
    }).then(result => {
        if (!result.isConfirmed) return;
        window.location.href = '?action=paiement&id_tache=' + id;
    });
}
</script>
<?php endif; ?>

</body>
</html>
<?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_role'] === 2): ?>
<!-- ═══════════════════════════════════════════════════════════════════
     WIDGET CHATBOT IA — SkillBridge (ai-chat-*)
     Visible uniquement pour les clients (role=2)
════════════════════════════════════════════════════════════════════ -->
<style>
/* ── Conteneur racine ─────────────────────────────────────────────── */
#ai-chat-widget {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 9999;
    font-family: 'DM Sans', sans-serif;
}

/* ── Bouton d'ouverture (état fermé) ─────────────────────────────── */
#ai-chat-toggle-btn {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--amber, #E07020), #f5a623);
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(224, 112, 32, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    color: #fff;
}
#ai-chat-toggle-btn:hover {
    transform: scale(1.08);
    box-shadow: 0 6px 20px rgba(224, 112, 32, 0.55);
}

/* ── Fenêtre de conversation ─────────────────────────────────────── */
#ai-chat-window {
    display: none;
    width: 360px;
    max-width: 90vw;
    height: 480px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
    flex-direction: column;
    overflow: hidden;
    background: #fff;
    margin-bottom: 0.75rem;
}

/* ── En-tête ─────────────────────────────────────────────────────── */
.ai-chat-header {
    background: var(--charcoal, #1a1a2e);
    color: #fff;
    padding: 0.85rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.ai-chat-header-title {
    font-weight: 700;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
#ai-chat-close-btn {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.75);
    font-size: 1.2rem;
    cursor: pointer;
    line-height: 1;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    transition: color 0.2s, background 0.2s;
}
#ai-chat-close-btn:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.12);
}

/* ── Zone de messages ────────────────────────────────────────────── */
#ai-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    background: var(--creme, #faf8f5);
}

/* ── Bulles de messages ──────────────────────────────────────────── */
.ai-chat-msg {
    max-width: 80%;
    padding: 0.65rem 0.9rem;
    border-radius: 12px;
    font-size: 0.88rem;
    line-height: 1.5;
    word-break: break-word;
}
.ai-chat-msg--user {
    align-self: flex-end;
    background: var(--amber, #E07020);
    color: #fff;
    border-bottom-right-radius: 3px;
}
.ai-chat-msg--ai {
    align-self: flex-start;
    background: #fff;
    color: var(--charcoal, #1a1a2e);
    border: 1px solid var(--beige-border, #e8e0d5);
    border-bottom-left-radius: 3px;
}
.ai-chat-msg--error {
    align-self: flex-start;
    background: #fde8e8;
    color: #c0392b;
    border: 1px solid #f5c6cb;
    border-bottom-left-radius: 3px;
}

/* ── Indicateur de chargement (3 points) ────────────────────────── */
#ai-chat-typing {
    display: none;
    align-self: flex-start;
    padding: 0.55rem 0.9rem;
    background: #fff;
    border: 1px solid var(--beige-border, #e8e0d5);
    border-radius: 12px;
    border-bottom-left-radius: 3px;
    gap: 0.3rem;
    align-items: center;
}
#ai-chat-typing span {
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--amber, #E07020);
    animation: ai-chat-bounce 1.2s infinite ease-in-out;
}
#ai-chat-typing span:nth-child(2) { animation-delay: 0.2s; }
#ai-chat-typing span:nth-child(3) { animation-delay: 0.4s; }
@keyframes ai-chat-bounce {
    0%, 80%, 100% { transform: scale(0.7); opacity: 0.5; }
    40%           { transform: scale(1.1); opacity: 1; }
}

/* ── Zone de saisie ──────────────────────────────────────────────── */
.ai-chat-input-row {
    display: flex;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-top: 1px solid var(--beige-border, #e8e0d5);
    background: #fff;
    flex-shrink: 0;
    align-items: flex-end;
}
#ai-chat-input {
    flex: 1;
    resize: none;
    border: 1.5px solid var(--beige-border, #e8e0d5);
    border-radius: 8px;
    padding: 0.55rem 0.8rem;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem;
    color: var(--charcoal, #1a1a2e);
    line-height: 1.4;
    max-height: 100px;
    overflow-y: auto;
    transition: border-color 0.2s, box-shadow 0.2s;
}
#ai-chat-input:focus {
    outline: none;
    border-color: var(--amber, #E07020);
    box-shadow: 0 0 0 3px rgba(224, 112, 32, 0.15);
}
#ai-chat-send-btn {
    background: var(--amber, #E07020);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.55rem 1rem;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    transition: opacity 0.2s, background 0.2s;
    white-space: nowrap;
    flex-shrink: 0;
}
#ai-chat-send-btn:hover:not(:disabled) {
    background: #c96018;
}
#ai-chat-send-btn:disabled,
#ai-chat-input:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

/* ── Responsive mobile ───────────────────────────────────────────── */
@media (max-width: 640px) {
    #ai-chat-window {
        width: 90vw;
    }
}
</style>

<!-- Structure HTML du widget -->
<div id="ai-chat-widget">
    <div id="ai-chat-window">
        <div class="ai-chat-header">
            <div class="ai-chat-header-title">
                <span>✨</span>
                <span>Assistant SkillBridge</span>
            </div>
            <button id="ai-chat-close-btn" title="Fermer">&times;</button>
        </div>
        <div id="ai-chat-messages"></div>
        <div id="ai-chat-typing">
            <span></span><span></span><span></span>
        </div>
        <div class="ai-chat-input-row">
            <textarea id="ai-chat-input" rows="1" placeholder="Posez votre question…" maxlength="1000"></textarea>
            <button id="ai-chat-send-btn">Envoyer</button>
        </div>
    </div>
    <button id="ai-chat-toggle-btn" title="Assistant IA SkillBridge">✨</button>
</div>

<script>
(function () {
    // ── Tâche 9.1 — Variables globales et message de bienvenue ────────
    let aiChatHistory = [];
    let aiChatLoading = false;

    const aiChatWidget    = document.getElementById('ai-chat-widget');
    const aiChatWindow    = document.getElementById('ai-chat-window');
    const aiChatToggleBtn = document.getElementById('ai-chat-toggle-btn');
    const aiChatCloseBtn  = document.getElementById('ai-chat-close-btn');
    const aiChatMessages  = document.getElementById('ai-chat-messages');
    const aiChatTyping    = document.getElementById('ai-chat-typing');
    const aiChatInput     = document.getElementById('ai-chat-input');
    const aiChatSendBtn   = document.getElementById('ai-chat-send-btn');

    const aiChatPrenom = <?= json_encode(htmlspecialchars($_SESSION['user_prenom'] ?? $_SESSION['prenom'] ?? 'Client', ENT_QUOTES, 'UTF-8')) ?>;

    // Message de bienvenue
    document.addEventListener('DOMContentLoaded', function () {
        aiChatAppendMessage(
            'Bonjour ' + aiChatPrenom + ' ! 👋 Je suis votre assistant SkillBridge. '
            + 'Je peux vous aider sur : le statut et l\'avancement de vos projets, '
            + 'le détail de vos tâches, votre budget et vos paiements, '
            + 'des conseils de gestion de projet, et les fonctionnalités de la plateforme. '
            + 'Comment puis-je vous aider ?',
            'ai'
        );
    });

    // ── Tâche 9.2 — Ouverture / fermeture du widget ──────────────────
    aiChatToggleBtn.addEventListener('click', function () {
        const isOpen = aiChatWindow.style.display === 'flex';
        aiChatWindow.style.display = isOpen ? 'none' : 'flex';
        if (!isOpen) {
            aiChatInput.focus();
        }
    });

    aiChatCloseBtn.addEventListener('click', function () {
        aiChatWindow.style.display = 'none';
    });

    // ── Tâche 9.3 — Envoi d'un message ───────────────────────────────
    function aiChatSendMessage() {
        const message = aiChatInput.value.trim();

        if (message === '') {
            aiChatInput.focus();
            return;
        }

        if (aiChatLoading) {
            return;
        }

        aiChatAppendMessage(message, 'user');
        aiChatInput.value = '';
        aiChatLoading = true;
        aiChatInput.disabled  = true;
        aiChatSendBtn.disabled = true;
        aiChatTyping.style.display = 'flex';

        const formData = new FormData();
        formData.append('action',  'chatbot_gemini');
        formData.append('message', message);
        formData.append('history', JSON.stringify(aiChatHistory));

        fetch(window.location.href, { method: 'POST', body: formData })
            // ── Tâche 9.5 — Traitement de la réponse AJAX ────────────
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                aiChatTyping.style.display = 'none';

                if (data.success === true) {
                    aiChatAppendMessage(data.reply, 'ai');
                    aiChatHistory.push({ role: 'user',  parts: [{ text: message }] });
                    aiChatHistory.push({ role: 'model', parts: [{ text: data.reply }] });
                } else {
                    aiChatAppendMessage((data.message || 'Une erreur est survenue.'), 'error');
                }
            })
            .catch(function (err) {
                aiChatTyping.style.display = 'none';
                console.error('[ChatbotAI] Erreur fetch:', err);
                aiChatAppendMessage('Erreur de connexion. Veuillez réessayer.', 'error');
            })
            .finally(function () {
                aiChatInput.disabled   = false;
                aiChatSendBtn.disabled = false;
                aiChatLoading = false;
                aiChatInput.focus();
            });
    }

    aiChatSendBtn.addEventListener('click', aiChatSendMessage);

    // ── Tâche 9.7 — Afficher un message dans la zone de chat ─────────
    function aiChatAppendMessage(text, type) {
        const div = document.createElement('div');
        div.classList.add('ai-chat-msg', 'ai-chat-msg--' + type);
        div.textContent = text; // textContent — pas innerHTML (sécurité XSS)
        aiChatMessages.appendChild(div);
        aiChatMessages.scrollTop = aiChatMessages.scrollHeight;
    }

    // ── Tâche 9.9 — Soumission par touche Entrée ─────────────────────
    aiChatInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            aiChatSendMessage();
        }
    });
})();
</script>
<?php endif; ?>

<?php if (isset($_SESSION['user_id']) && in_array((int)$_SESSION['user_role'], [2,3])): ?>
<script>
// ── Notification Bell ─────────────────────────────────────────────────
let notifDropdownOpen = false;

function toggleNotifDropdown() {
    const dd = document.getElementById('notifDropdown');
    notifDropdownOpen = !notifDropdownOpen;
    dd.style.display = notifDropdownOpen ? 'block' : 'none';
}

// Fermer si clic en dehors
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('notifBellWrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('notifDropdown').style.display = 'none';
        notifDropdownOpen = false;
    }
});

function marquerToutLu() {
    const fd = new FormData();
    fd.append('action', 'marquer_tout_lu');
    fetch('?action=projects', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(() => {
        document.getElementById('notifCount').style.display = 'none';
        document.getElementById('notifList').innerHTML = '<div class="notif-empty">Aucune nouvelle notification</div>';
    });
}

function marquerProjetLu(idProjet) {
    const fd = new FormData();
    fd.append('action', 'marquer_lu');
    fd.append('id_projet', idProjet);
    fetch('?action=projects', { method: 'POST', body: fd });
}

function chargerNotifications() {
    const fd = new FormData();
    fd.append('action', 'get_notifs');
    fetch('?action=projects', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        const countEl = document.getElementById('notifCount');
        const listEl  = document.getElementById('notifList');

        if (data.total > 0) {
            countEl.textContent = data.total > 99 ? '99+' : data.total;
            countEl.style.display = 'flex';
            // Animer la cloche
            countEl.parentElement.classList.remove('bell-anim');
            void countEl.parentElement.offsetWidth;
            countEl.parentElement.classList.add('bell-anim');
        } else {
            countEl.style.display = 'none';
        }

        if (data.notifs.length === 0) {
            listEl.innerHTML = '<div class="notif-empty">Aucune nouvelle notification</div>';
            return;
        }

        listEl.innerHTML = data.notifs.map(n => `
            <div class="notif-item unread" onclick="ouvrirChat(${n.id_projet})">
                <div class="notif-icon">💬</div>
                <div class="notif-content">
                    <div class="notif-projet">${escapeHtmlNotif(n.titre_projet)}</div>
                    <div class="notif-text"><strong>${escapeHtmlNotif(n.expediteur)}</strong> : ${escapeHtmlNotif(n.apercu)}</div>
                    <div class="notif-time">${n.heure} · ${n.count} nouveau${n.count > 1 ? 'x' : ''}</div>
                </div>
                <div class="notif-dot"></div>
            </div>
        `).join('');
    });
}

function ouvrirChat(idProjet) {
    // Fermer dropdown
    document.getElementById('notifDropdown').style.display = 'none';
    notifDropdownOpen = false;
    // Aller à l'onglet "Mes Projets" si pas déjà dessus
    const url = new URL(window.location.href);
    if (url.searchParams.get('tab') !== 'mes') {
        window.location.href = '?action=projects&tab=mes#chat-' + idProjet;
        return;
    }
    // Ouvrir le chat du projet
    const chatBody = document.getElementById('chat-body-' + idProjet);
    if (chatBody) {
        chatBody.style.display = 'block';
        if (typeof chargerMessages === 'function') chargerMessages(idProjet);
        if (typeof startPolling === 'function') startPolling(idProjet);
        document.getElementById('chat-' + idProjet)?.scrollIntoView({ behavior: 'smooth' });
    }
    marquerProjetLu(idProjet);
}

function escapeHtmlNotif(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Polling notifications toutes les 10 secondes
chargerNotifications();
setInterval(chargerNotifications, 10000);
</script>
<?php endif; ?>
