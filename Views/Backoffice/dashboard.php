<?php
// ─── Auth guard ───────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 1) {
    header('Location: ?action=login');
    exit;
}

require_once __DIR__ . '/../../Controllers/ProjectController.php';
require_once __DIR__ . '/../../Controllers/CandidatureController.php';

$projectController     = new ProjectController();
$candidatureController = new CandidatureController();

// ─── Data queries ─────────────────────────────────────────────────────────────
$stats    = $projectController->getStats();
$projects = $projectController->listAllProjects('', '', '', '', '');
// Keep only last 5
$projects = array_slice($projects, 0, 5);

$db = Config::getConnexion();

// Users
$q = $db->query("SELECT COUNT(*) AS total,
    SUM(CASE WHEN id_role=2 THEN 1 ELSE 0 END) AS clients,
    SUM(CASE WHEN id_role=3 THEN 1 ELSE 0 END) AS freelancers
    FROM user WHERE id_role != 1");
$statsUsers = $q->fetch(PDO::FETCH_ASSOC);

// Users this month
$q = $db->query("SELECT COUNT(*) AS nb FROM user WHERE id_role != 1 AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");
$usersThisMonth = (int)$q->fetchColumn();

// Tasks
$q = $db->query("SELECT COUNT(*) AS total,
    SUM(CASE WHEN statut='termine'  THEN 1 ELSE 0 END) AS terminees,
    SUM(CASE WHEN statut='en_cours' THEN 1 ELSE 0 END) AS en_cours,
    SUM(CASE WHEN statut='a_faire'  THEN 1 ELSE 0 END) AS a_faire,
    COALESCE(SUM(prix),0) AS total_prix
    FROM tache");
$statsTaches = $q->fetch(PDO::FETCH_ASSOC);

// Paid tasks
$q = $db->query("SELECT COUNT(*) AS total_payees, COALESCE(SUM(prix),0) AS montant_paye FROM tache WHERE payee=1");
$statsPaiements = $q->fetch(PDO::FETCH_ASSOC);

// Candidatures
$q = $db->query("SELECT COUNT(*) AS total,
    SUM(CASE WHEN statut='accepte'    THEN 1 ELSE 0 END) AS acceptees,
    SUM(CASE WHEN statut='en_attente' THEN 1 ELSE 0 END) AS en_attente,
    SUM(CASE WHEN statut='refuse'     THEN 1 ELSE 0 END) AS refusees
    FROM candidature");
$statsCands = $q->fetch(PDO::FETCH_ASSOC);

// Projects per month – last 6 months (stacked: en_cours / termine / en_attente)
$q = $db->query("SELECT DATE_FORMAT(date_creation,'%Y-%m') AS mois,
    SUM(CASE WHEN statut='en_cours'  THEN 1 ELSE 0 END) AS en_cours,
    SUM(CASE WHEN statut='termine'   THEN 1 ELSE 0 END) AS termine,
    SUM(CASE WHEN statut='en_attente'THEN 1 ELSE 0 END) AS en_attente,
    COUNT(*) AS total
    FROM projet
    WHERE date_creation >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mois ORDER BY mois ASC");
$projetsParMois = $q->fetchAll(PDO::FETCH_ASSOC);
$moisLabels     = array_column($projetsParMois, 'mois');
$moisEnCours    = array_column($projetsParMois, 'en_cours');
$moisTermine    = array_column($projetsParMois, 'termine');
$moisEnAttente  = array_column($projetsParMois, 'en_attente');
$moisTotals     = array_column($projetsParMois, 'total');

// Revenue: current month vs last month (paid tasks)
$q = $db->query("SELECT
    COALESCE(SUM(CASE WHEN MONTH(created_at)=MONTH(CURDATE())   AND YEAR(created_at)=YEAR(CURDATE())   THEN prix ELSE 0 END),0) AS ce_mois,
    COALESCE(SUM(CASE WHEN MONTH(created_at)=MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) AND YEAR(created_at)=YEAR(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) THEN prix ELSE 0 END),0) AS mois_dernier
    FROM tache WHERE payee=1");
$revenueMonths = $q->fetch(PDO::FETCH_ASSOC);

// Revenue per week for current month (line chart – 4 data points)
$q = $db->query("SELECT WEEK(created_at,1) AS sem, COALESCE(SUM(prix),0) AS montant
    FROM tache WHERE payee=1 AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())
    GROUP BY sem ORDER BY sem ASC LIMIT 4");
$revCeMois = $q->fetchAll(PDO::FETCH_ASSOC);

$q = $db->query("SELECT WEEK(created_at,1) AS sem, COALESCE(SUM(prix),0) AS montant
    FROM tache WHERE payee=1 AND MONTH(created_at)=MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) AND YEAR(created_at)=YEAR(DATE_SUB(CURDATE(),INTERVAL 1 MONTH))
    GROUP BY sem ORDER BY sem ASC LIMIT 4");
$revMoisDernier = $q->fetchAll(PDO::FETCH_ASSOC);

// Pad to 4 points
function padRevenue(array $rows, int $n = 4): array {
    $vals = array_column($rows, 'montant');
    while (count($vals) < $n) $vals[] = 0;
    return array_slice($vals, 0, $n);
}
$revCeMoisData      = padRevenue($revCeMois);
$revMoisDernierData = padRevenue($revMoisDernier);

// ─── Helpers ──────────────────────────────────────────────────────────────────
function statutBadge(string $s): string {
    return match($s) {
        'en_cours'  => '<span class="db-badge db-badge-orange">En cours</span>',
        'termine'   => '<span class="db-badge db-badge-green">Terminé</span>',
        'en_attente'=> '<span class="db-badge db-badge-gray">En attente</span>',
        default     => '<span class="db-badge db-badge-gray">'.htmlspecialchars($s).'</span>',
    };
}
function etatBadge(string $e): string {
    return match($e) {
        'publie'                => '<span class="db-badge db-badge-green">Publié</span>',
        'en_attente_validation' => '<span class="db-badge db-badge-yellow">Validation</span>',
        'refuse'                => '<span class="db-badge db-badge-red">Refusé</span>',
        default                 => '<span class="db-badge db-badge-gray">'.htmlspecialchars($e).'</span>',
    };
}

$totalProjects = (int)$stats['total'];
$pctEnCours    = $totalProjects > 0 ? round((int)$stats['en_cours']  / $totalProjects * 100) : 0;
$pctTermine    = $totalProjects > 0 ? round((int)$stats['termine']   / $totalProjects * 100) : 0;
$pctEnAttente  = $totalProjects > 0 ? round((int)$stats['en_attente']/ $totalProjects * 100) : 0;
$pctValidation = $totalProjects > 0 ? round((int)$stats['en_attente_validation'] / $totalProjects * 100) : 0;

$totalUsers    = (int)$statsUsers['total'];
$pctClients    = $totalUsers > 0 ? round((int)$statsUsers['clients']    / $totalUsers * 100) : 0;
$pctFreelancers= $totalUsers > 0 ? round((int)$statsUsers['freelancers']/ $totalUsers * 100) : 0;

$today = new DateTime();
$locale_days   = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
$locale_months = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$dateStr = $locale_days[(int)$today->format('w')] . ' ' . (int)$today->format('j') . ' ' . $locale_months[(int)$today->format('n')-1] . ' ' . $today->format('Y');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Dashboard – SkillBridge Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= BASE_URL ?>/Views/assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            custom: { families: ["Font Awesome 5 Solid","Font Awesome 5 Regular","Font Awesome 5 Brands","simple-line-icons"], urls: ["<?= BASE_URL ?>/Views/assets/css/fonts.min.css"] },
            active: function() { sessionStorage.fonts = true; }
        });
    </script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Views/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Views/assets/css/plugins.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Views/assets/css/kaiadmin.min.css">
    <style>
        /* ── Base ── */
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif !important; background: #f4f3ff !important; }

        /* ── Sidebar overrides ── */
        .sidebar { transition: transform .3s ease, width .3s ease; }
        .wrapper.sidebar-hidden .sidebar { display: none; }
        .wrapper .main-panel { transition: margin-left .3s ease, width .3s ease; }
        .wrapper.sidebar-hidden .main-panel { margin-left: 0 !important; width: 100% !important; }
        .show-sidebar-btn { display: block; position: fixed; top: 20px; left: 20px; z-index: 1050; }
        .wrapper:not(.sidebar-hidden) .show-sidebar-btn { display: none !important; }
        @media (min-width: 992px) {
            .show-sidebar-btn { display: none; }
            .wrapper.sidebar-hidden .show-sidebar-btn { display: block; }
        }

        /* ── Dashboard card ── */
        .db-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(124,58,237,.07);
            border: none;
            overflow: hidden;
        }
        .db-card-header {
            padding: 18px 22px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f0eeff;
        }
        .db-card-title {
            font-size: .85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #7c3aed;
            margin: 0;
        }
        .db-card-body { padding: 18px 22px 20px; }

        /* ── Greeting header ── */
        .dash-header {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: #fff;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }
        .dash-header h2 { font-size: 1.7rem; font-weight: 800; margin: 0; }
        .dash-header p  { margin: 4px 0 0; opacity: .8; font-size: .9rem; }
        .btn-export-pdf {
            background: rgba(255,255,255,.18);
            border: 1.5px solid rgba(255,255,255,.45);
            color: #fff;
            border-radius: 10px;
            padding: 9px 20px;
            font-size: .85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: background .2s;
        }
        .btn-export-pdf:hover { background: rgba(255,255,255,.3); color: #fff; }

        /* ── Big number ── */
        .big-number { font-size: 2.6rem; font-weight: 800; color: #1e1b4b; line-height: 1; }
        .active-dot { width: 9px; height: 9px; border-radius: 50%; background: #10b981; display: inline-block; margin-right: 5px; }

        /* ── Progress bar ── */
        .db-progress { height: 10px; border-radius: 99px; background: #ede9fe; overflow: hidden; }
        .db-progress-bar { height: 100%; border-radius: 99px; }

        /* ── Legend dot ── */
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; flex-shrink: 0; }

        /* ── Status list ── */
        .status-row { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
        .status-row:last-child { margin-bottom: 0; }
        .status-label { font-size: .82rem; color: #6b7280; min-width: 130px; }
        .status-count { font-size: .9rem; font-weight: 700; color: #1e1b4b; min-width: 28px; text-align: right; }
        .status-pct   { font-size: .78rem; color: #9ca3af; min-width: 36px; text-align: right; }

        /* ── Table badges ── */
        .db-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: .75rem;
            font-weight: 600;
        }
        .db-badge-orange { background: #fff7ed; color: #c2410c; }
        .db-badge-green  { background: #ecfdf5; color: #065f46; }
        .db-badge-gray   { background: #f3f4f6; color: #374151; }
        .db-badge-yellow { background: #fefce8; color: #92400e; }
        .db-badge-red    { background: #fef2f2; color: #991b1b; }
        .db-badge-purple { background: #f5f3ff; color: #5b21b6; }

        /* ── Dropdown pill ── */
        .pill-select {
            background: #f5f3ff;
            border: none;
            border-radius: 8px;
            padding: 4px 12px;
            font-size: .78rem;
            font-weight: 600;
            color: #7c3aed;
            cursor: pointer;
        }

        /* ── Monthly evolution chip ── */
        .evo-chip {
            background: #f5f3ff;
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 16px;
        }
        .evo-chip .label { font-size: .78rem; color: #6b7280; }
        .evo-chip .value { font-size: 1.3rem; font-weight: 800; color: #7c3aed; }

        /* ── Full report link ── */
        .full-report-link {
            font-size: .78rem;
            color: #7c3aed;
            font-weight: 600;
            text-decoration: none;
        }
        .full-report-link:hover { text-decoration: underline; }

        /* ── Recent projects table ── */
        .db-table { width: 100%; border-collapse: collapse; }
        .db-table th {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
            padding: 0 12px 12px;
            border-bottom: 1px solid #f0eeff;
            text-align: left;
        }
        .db-table td {
            padding: 13px 12px;
            font-size: .84rem;
            color: #374151;
            border-bottom: 1px solid #f9f8ff;
            vertical-align: middle;
        }
        .db-table tr:last-child td { border-bottom: none; }
        .db-table tr:hover td { background: #faf9ff; }
        .project-name { font-weight: 600; color: #1e1b4b; }
        .client-name  { color: #6b7280; }

        /* ── Donut center text ── */
        .donut-wrap { position: relative; display: flex; align-items: center; justify-content: center; }
        .donut-center {
            position: absolute;
            text-align: center;
            pointer-events: none;
        }
        .donut-center .dc-num  { font-size: 1.6rem; font-weight: 800; color: #1e1b4b; line-height: 1; }
        .donut-center .dc-lbl  { font-size: .7rem; color: #9ca3af; margin-top: 2px; }

        /* ── Revenue total ── */
        .rev-total { font-size: .78rem; color: #6b7280; margin-top: 10px; }
        .rev-total strong { color: #1e1b4b; font-size: 1rem; }

        /* ── Sidebar active ── */
        .nav-item.active > a { background: rgba(124,58,237,.12) !important; border-radius: 8px; }
    </style>
</head>
<body>
<div class="wrapper">

    <!-- ═══════════════════════════════ SIDEBAR ═══════════════════════════════ -->
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
                    <li class="nav-item active">
                        <a href="?action=dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=userlist">
                            <i class="fas fa-users"></i>
                            <p>Utilisateurs</p>
                        </a>
                    </li>
                    <li class="nav-item">
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

    <!-- ═══════════════════════════════ MAIN PANEL ════════════════════════════ -->
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
                        <span class="nav-link" style="color:#2c3e50;">👤 <?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Admin') ?></span>
                    </li>
                    <li class="nav-item">
                        <a href="?action=logout" class="nav-link" title="Déconnexion"><i class="fas fa-sign-out-alt"></i></a>
                    </li>
                </ul>
            </div>
        </nav>

        <button class="btn btn-outline-secondary show-sidebar-btn" id="showSidebarBtn" title="Afficher la barre latérale">
            <i class="fas fa-bars"></i>
        </button>

        <div class="container-fluid">
            <div class="page-inner" style="padding-top:24px;">

                <!-- ── Greeting header ── -->
                <div class="dash-header">
                    <div>
                        <h2>Bonjour, Admin 👋</h2>
                        <p><?= $dateStr ?></p>
                    </div>
                    <a href="?action=projectlist&export=pdf" class="btn-export-pdf">
                        <i class="fas fa-file-pdf"></i> Exporter PDF
                    </a>
                </div>

                <!-- ══════════════════════════════════════════════════════════
                     SECTION 1 – Budget Projets + Composition Utilisateurs
                ══════════════════════════════════════════════════════════ -->
                <div class="row g-4 mb-4">

                    <!-- Budget des Projets (col-8) -->
                    <div class="col-lg-8">
                        <div class="db-card h-100">
                            <div class="db-card-header">
                                <span class="db-card-title">Budget des Projets</span>
                                <select class="pill-select">
                                    <option>Monthly</option>
                                    <option>Weekly</option>
                                    <option>Yearly</option>
                                </select>
                            </div>
                            <div class="db-card-body">
                                <canvas id="chartBudget" height="110"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Composition Utilisateurs (col-4) -->
                    <div class="col-lg-4">
                        <div class="db-card h-100">
                            <div class="db-card-header">
                                <span class="db-card-title">Composition Utilisateurs</span>
                            </div>
                            <div class="db-card-body">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="big-number"><?= $totalUsers ?></span>
                                    <div class="ms-2">
                                        <span class="active-dot"></span>
                                        <span style="font-size:.8rem;color:#6b7280;font-weight:500;">Utilisateurs actifs</span>
                                    </div>
                                </div>

                                <!-- Stacked progress bar -->
                                <div class="db-progress mb-2" style="height:12px;">
                                    <div style="display:flex;height:100%;border-radius:99px;overflow:hidden;">
                                        <div style="width:<?= $pctClients ?>%;background:#7c3aed;"></div>
                                        <div style="width:<?= $pctFreelancers ?>%;background:#f59e0b;"></div>
                                    </div>
                                </div>
                                <div class="d-flex gap-3 mb-3">
                                    <span style="font-size:.78rem;color:#6b7280;">
                                        <span class="legend-dot" style="background:#7c3aed;"></span>
                                        Clients <strong style="color:#1e1b4b;"><?= (int)$statsUsers['clients'] ?></strong>
                                    </span>
                                    <span style="font-size:.78rem;color:#6b7280;">
                                        <span class="legend-dot" style="background:#f59e0b;"></span>
                                        Freelancers <strong style="color:#1e1b4b;"><?= (int)$statsUsers['freelancers'] ?></strong>
                                    </span>
                                </div>

                                <div class="evo-chip">
                                    <div>
                                        <div class="label">Évolution mensuelle</div>
                                        <div style="font-size:.72rem;color:#9ca3af;"><?= $locale_months[(int)$today->format('n')-1] ?> <?= $today->format('Y') ?></div>
                                    </div>
                                    <div class="value">+<?= $usersThisMonth ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════
                     SECTION 2 – Tâches / Revenus / Statut Projets
                ══════════════════════════════════════════════════════════ -->
                <div class="row g-4 mb-4">

                    <!-- Répartition Tâches – Donut (col-4) -->
                    <div class="col-lg-4">
                        <div class="db-card h-100">
                            <div class="db-card-header">
                                <span class="db-card-title">Répartition Tâches</span>
                            </div>
                            <div class="db-card-body">
                                <div class="donut-wrap" style="height:180px;">
                                    <canvas id="chartTaches" style="max-height:180px;"></canvas>
                                    <div class="donut-center">
                                        <div class="dc-num"><?= (int)$statsTaches['total'] ?></div>
                                        <div class="dc-lbl">Total tâches</div>
                                    </div>
                                </div>
                                <!-- Legend -->
                                <div class="mt-3 d-flex flex-column gap-2">
                                    <?php
                                    $tacheLegend = [
                                        ['À faire',   $statsTaches['a_faire'],   '#7c3aed'],
                                        ['En cours',  $statsTaches['en_cours'],  '#f59e0b'],
                                        ['Terminées', $statsTaches['terminees'], '#10b981'],
                                        ['Payées',    $statsPaiements['total_payees'], '#3b82f6'],
                                    ];
                                    foreach ($tacheLegend as [$lbl, $cnt, $col]):
                                    ?>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="legend-dot" style="background:<?= $col ?>;"></span>
                                            <span style="font-size:.8rem;color:#6b7280;"><?= $lbl ?></span>
                                        </div>
                                        <span style="font-size:.82rem;font-weight:700;color:#1e1b4b;"><?= (int)$cnt ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenus Tâches – Line chart (col-4) -->
                    <div class="col-lg-4">
                        <div class="db-card h-100">
                            <div class="db-card-header">
                                <span class="db-card-title">Revenus Tâches</span>
                                <a href="?action=projectlist" class="full-report-link">Full report →</a>
                            </div>
                            <div class="db-card-body">
                                <canvas id="chartRevenu" height="160"></canvas>
                                <div class="rev-total mt-2">
                                    Total payé : <strong><?= number_format((float)$statsPaiements['montant_paye'], 2, ',', ' ') ?> TND</strong>
                                </div>
                                <!-- Legend -->
                                <div class="d-flex gap-3 mt-2">
                                    <span style="font-size:.78rem;color:#6b7280;">
                                        <span class="legend-dot" style="background:#7c3aed;"></span>
                                        Ce mois
                                    </span>
                                    <span style="font-size:.78rem;color:#6b7280;">
                                        <span class="legend-dot" style="background:#f59e0b;"></span>
                                        Mois dernier
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statut Projets (col-4) -->
                    <div class="col-lg-4">
                        <div class="db-card h-100">
                            <div class="db-card-header">
                                <span class="db-card-title">Statut Projets</span>
                            </div>
                            <div class="db-card-body">
                                <?php
                                $statusRows = [
                                    ['En cours',            (int)$stats['en_cours'],               $pctEnCours,    '#f59e0b'],
                                    ['Terminés',            (int)$stats['termine'],                 $pctTermine,    '#10b981'],
                                    ['En attente',          (int)$stats['en_attente'],              $pctEnAttente,  '#9ca3af'],
                                    ['En attente validation',(int)$stats['en_attente_validation'],  $pctValidation, '#fbbf24'],
                                ];
                                foreach ($statusRows as [$lbl, $cnt, $pct, $col]):
                                ?>
                                <div class="status-row">
                                    <div style="flex:1;">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="status-label"><?= $lbl ?></span>
                                            <div class="d-flex gap-2">
                                                <span class="status-count"><?= $cnt ?></span>
                                                <span class="status-pct"><?= $pct ?>%</span>
                                            </div>
                                        </div>
                                        <div class="db-progress">
                                            <div class="db-progress-bar" style="width:<?= $pct ?>%;background:<?= $col ?>;"></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <!-- Total -->
                                <div class="evo-chip mt-3">
                                    <div>
                                        <div class="label">Total projets</div>
                                        <div style="font-size:.72rem;color:#9ca3af;">Budget total</div>
                                    </div>
                                    <div>
                                        <div class="value"><?= $totalProjects ?></div>
                                        <div style="font-size:.72rem;color:#9ca3af;text-align:right;"><?= number_format((float)$stats['budget_total'],0,',',' ') ?> TND</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════
                     SECTION 3 – Projets Récents
                ══════════════════════════════════════════════════════════ -->
                <div class="db-card mb-5">
                    <div class="db-card-header">
                        <span class="db-card-title">Projets Récents</span>
                        <a href="?action=projectlist" class="full-report-link">Voir tous →</a>
                    </div>
                    <div class="db-card-body" style="padding-top:8px;">
                        <div class="table-responsive">
                            <table class="db-table">
                                <thead>
                                    <tr>
                                        <th>Projet</th>
                                        <th>Client</th>
                                        <th>Budget</th>
                                        <th>Statut</th>
                                        <th>État</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($projects)): ?>
                                    <tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:24px;">Aucun projet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($projects as $p): ?>
                                    <tr>
                                        <td><span class="project-name"><?= htmlspecialchars($p->getTitre()) ?></span></td>
                                        <td><span class="client-name"><?= htmlspecialchars($p->getNomClient() ?: '—') ?></span></td>
                                        <td style="font-weight:600;color:#1e1b4b;"><?= number_format((float)$p->getBudget(),0,',',' ') ?> TND</td>
                                        <td><?= statutBadge($p->getStatut()) ?></td>
                                        <td><?= etatBadge($p->getEtat() ?? '') ?></td>
                                        <td style="color:#9ca3af;"><?= htmlspecialchars($p->getDateCreation()) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div><!-- /page-inner -->
        </div><!-- /container-fluid -->
    </div><!-- /main-panel -->
</div><!-- /wrapper -->

<!-- ═══════════════════════════════ SCRIPTS ═══════════════════════════════ -->
<script src="<?= BASE_URL ?>/Views/assets/js/core/jquery-3.7.1.min.js"></script>
<script src="<?= BASE_URL ?>/Views/assets/js/core/popper.min.js"></script>
<script src="<?= BASE_URL ?>/Views/assets/js/core/bootstrap.min.js"></script>
<script src="<?= BASE_URL ?>/Views/assets/js/kaiadmin.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
// ── PHP → JS data ──────────────────────────────────────────────────────────
const moisLabels    = <?= json_encode($moisLabels) ?>;
const moisEnCours   = <?= json_encode(array_map('intval', $moisEnCours)) ?>;
const moisTermine   = <?= json_encode(array_map('intval', $moisTermine)) ?>;
const moisEnAttente = <?= json_encode(array_map('intval', $moisEnAttente)) ?>;
const moisTotals    = <?= json_encode(array_map('intval', $moisTotals)) ?>;

const tachesData = [
    <?= (int)$statsTaches['a_faire'] ?>,
    <?= (int)$statsTaches['en_cours'] ?>,
    <?= (int)$statsTaches['terminees'] ?>,
    <?= (int)$statsPaiements['total_payees'] ?>
];

const revCeMois      = <?= json_encode(array_map('floatval', $revCeMoisData)) ?>;
const revMoisDernier = <?= json_encode(array_map('floatval', $revMoisDernierData)) ?>;

// ── Color palette ──────────────────────────────────────────────────────────
const C_PURPLE = '#7c3aed';
const C_ORANGE = '#f59e0b';
const C_BLUE   = '#3b82f6';
const C_GREEN  = '#10b981';
const C_GRAY   = '#e5e7eb';

// ── Chart defaults ─────────────────────────────────────────────────────────
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size   = 12;
Chart.defaults.color       = '#9ca3af';

// ── 1. Stacked bar – Budget des Projets ────────────────────────────────────
new Chart(document.getElementById('chartBudget'), {
    type: 'bar',
    data: {
        labels: moisLabels,
        datasets: [
            {
                label: 'En cours',
                data: moisEnCours,
                backgroundColor: C_PURPLE,
                borderRadius: 4,
                borderSkipped: false,
            },
            {
                label: 'Terminés',
                data: moisTermine,
                backgroundColor: C_ORANGE,
                borderRadius: 4,
                borderSkipped: false,
            },
            {
                label: 'En attente',
                data: moisEnAttente,
                backgroundColor: C_BLUE,
                borderRadius: 4,
                borderSkipped: false,
            },
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
                labels: { usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 12 } }
            },
            tooltip: {
                callbacks: {
                    footer: (items) => {
                        const idx = items[0].dataIndex;
                        return 'Total : ' + moisTotals[idx];
                    }
                }
            }
        },
        scales: {
            x: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 } } },
            y: { stacked: true, beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1, font: { size: 11 } } }
        }
    }
});

// ── 2. Donut – Répartition Tâches ──────────────────────────────────────────
new Chart(document.getElementById('chartTaches'), {
    type: 'doughnut',
    data: {
        labels: ['À faire', 'En cours', 'Terminées', 'Payées'],
        datasets: [{
            data: tachesData,
            backgroundColor: [C_PURPLE, C_ORANGE, C_GREEN, C_BLUE],
            borderWidth: 3,
            borderColor: '#fff',
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        cutout: '68%',
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (ctx) => ` ${ctx.label} : ${ctx.parsed}`
                }
            }
        }
    }
});

// ── 3. Line – Revenus Tâches ───────────────────────────────────────────────
new Chart(document.getElementById('chartRevenu'), {
    type: 'line',
    data: {
        labels: ['S1', 'S2', 'S3', 'S4'],
        datasets: [
            {
                label: 'Ce mois',
                data: revCeMois,
                borderColor: C_PURPLE,
                backgroundColor: 'rgba(124,58,237,.08)',
                borderWidth: 2.5,
                pointBackgroundColor: C_PURPLE,
                pointRadius: 4,
                tension: 0.4,
                fill: true,
            },
            {
                label: 'Mois dernier',
                data: revMoisDernier,
                borderColor: C_ORANGE,
                backgroundColor: 'rgba(245,158,11,.06)',
                borderWidth: 2.5,
                pointBackgroundColor: C_ORANGE,
                pointRadius: 4,
                tension: 0.4,
                fill: true,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (ctx) => ` ${ctx.dataset.label} : ${ctx.parsed.y.toFixed(2)} TND`
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 } } }
        }
    }
});

// ── Sidebar toggle (reuse kaiadmin pattern) ────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const showBtn = document.getElementById('showSidebarBtn');
    if (showBtn) {
        showBtn.addEventListener('click', () => {
            document.querySelector('.wrapper').classList.remove('sidebar-hidden');
        });
    }
    document.querySelectorAll('.toggle-sidebar').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelector('.wrapper').classList.toggle('sidebar-hidden');
        });
    });
});
</script>
</body>
</html>
