<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header('Location: ?action=login'); exit;
}
require_once __DIR__ . '/../../Controllers/CandidatureController.php';
$cc = new CandidatureController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['accepter','refuser'])) {
        $id     = (int)($_POST['id'] ?? 0);
        $statut = ($action === 'accepter') ? 'accepte' : 'refuse';
        $ok     = $cc->changerStatut($id, $statut);
        echo json_encode(['success' => $ok, 'message' => $ok ? ($action === 'accepter' ? 'Candidature acceptée.' : 'Candidature refusée.') : 'Erreur.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
    }
    exit;
}

$filtre       = trim($_GET['statut'] ?? '');
$candidatures = $cc->getAllCandidatures($filtre);
$taches       = $cc->getAllTaches();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatures — SkillBridge Admin</title>
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
        .sidebar { transition: transform 0.3s ease; }
        .wrapper.sidebar-hidden .sidebar { display: none; }
        .wrapper.sidebar-hidden .main-panel { margin-left: 0 !important; width: 100% !important; }
        .show-sidebar-btn { display: block; position: fixed; top: 20px; left: 20px; z-index: 1050; }
        .wrapper:not(.sidebar-hidden) .show-sidebar-btn { display: none !important; }
        @media (min-width: 992px) { .show-sidebar-btn { display: none; } .wrapper.sidebar-hidden .show-sidebar-btn { display: block; } }
        .swal2-popup { font-family: "Open Sans", sans-serif; }
    </style>
</head>
<body>
<div class="wrapper">
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
                    <li class="nav-item"><a href="?action=userlist"><i class="fas fa-users"></i><p>Utilisateurs</p></a></li>
                    <li class="nav-item"><a href="?action=projectlist"><i class="fas fa-briefcase"></i><p>Projets</p></a></li>
                    <li class="nav-item active"><a href="?action=candidatures"><i class="fas fa-paper-plane"></i><p>Candidatures</p></a></li>
                    <li class="nav-section"><h4 class="text-section">Compte</h4></li>
                    <li class="nav-item"><a href="?action=logout"><i class="fas fa-sign-out-alt"></i><p>Déconnexion</p></a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="main-panel">
        <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
            <div class="container-fluid">
                <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                    <li class="nav-item"><span class="nav-link">👤 <?= htmlspecialchars($_SESSION['user_prenom']) ?></span></li>
                    <li class="nav-item"><a href="?action=logout" class="nav-link"><i class="fas fa-sign-out-alt"></i></a></li>
                </ul>
            </div>
        </nav>

        <button class="btn btn-outline-secondary show-sidebar-btn" id="showSidebarBtn"><i class="fas fa-bars"></i></button>

        <div class="container">
            <div class="page-inner">
                <div class="page-header"><h4 class="page-title">Candidatures Freelancers</h4></div>

                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body py-2">
                        <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
                            <input type="hidden" name="action" value="candidatures">
                            <label class="mb-0 fw-semibold">Filtrer :</label>
                            <select name="statut" class="form-select form-select-sm" style="width:160px;">
                                <option value="">Tous</option>
                                <option value="en_attente" <?= $filtre==='en_attente'?'selected':'' ?>>En attente</option>
                                <option value="accepte"    <?= $filtre==='accepte'   ?'selected':'' ?>>Acceptées</option>
                                <option value="refuse"     <?= $filtre==='refuse'    ?'selected':'' ?>>Refusées</option>
                            </select>
                            <button type="submit" class="btn btn-outline-primary btn-sm">Filtrer</button>
                            <?php if ($filtre): ?><a href="?action=candidatures" class="btn btn-outline-secondary btn-sm">Réinitialiser</a><?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Tableau candidatures -->
                <div class="card mb-5">
                    <div class="card-header"><h5 class="card-title mb-0">Liste des Candidatures</h5></div>
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
                                    <?php foreach ($candidatures as $c): ?>
                                    <tr id="cand-<?= $c->getId() ?>">
                                        <td><?= htmlspecialchars($c->getPrenomFreelancer() . ' ' . $c->getNomFreelancer()) ?></td>
                                        <td><?= htmlspecialchars($c->getTitreProjet()) ?></td>
                                        <td><?= date('d/m/Y', strtotime($c->getCreatedAt())) ?></td>
                                        <td>
                                            <?php
                                            $badges = ['en_attente'=>'warning','accepte'=>'success','refuse'=>'danger'];
                                            $labels = ['en_attente'=>'En attente','accepte'=>'Acceptée','refuse'=>'Refusée'];
                                            $s = $c->getStatut();
                                            ?>
                                            <span class="badge bg-<?= $badges[$s]??'secondary' ?>"><?= $labels[$s]??$s ?></span>
                                        </td>
                                        <td>
                                            <?php if ($c->getStatut() === 'en_attente'): ?>
                                            <button class="btn btn-success btn-sm me-1" onclick="validerCand(<?= $c->getId() ?>, 'accepter')">
                                                <i class="fas fa-check me-1"></i>Accepter
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="validerCand(<?= $c->getId() ?>, 'refuser')">
                                                <i class="fas fa-times me-1"></i>Refuser
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

                <!-- Tableau tâches -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Tâches des Freelancers</h5></div>
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
                                    <?php foreach ($taches as $t): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($t['prenom_freelancer'] . ' ' . $t['nom_freelancer']) ?></td>
                                        <td><?= htmlspecialchars($t['titre_projet']) ?></td>
                                        <td><?= htmlspecialchars($t['titre']) ?></td>
                                        <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($t['description'] ?? '') ?></td>
                                        <td>
                                            <?php
                                            $tb = ['a_faire'=>'secondary','en_cours'=>'warning','termine'=>'success'];
                                            $tl = ['a_faire'=>'À faire','en_cours'=>'En cours','termine'=>'Terminé'];
                                            $ts = $t['statut'];
                                            ?>
                                            <span class="badge bg-<?= $tb[$ts]??'secondary' ?>"><?= $tl[$ts]??$ts ?></span>
                                        </td>
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
            <div class="container-fluid"><div class="copyright">2026 © SkillBridge</div></div>
        </footer>
    </div>
</div>

<script src="<?= BASE_URL ?>/Views/assets/js/core/jquery-3.7.1.min.js"></script>
<script src="<?= BASE_URL ?>/Views/assets/js/core/popper.min.js"></script>
<script src="<?= BASE_URL ?>/Views/assets/js/core/bootstrap.min.js"></script>
<script src="<?= BASE_URL ?>/Views/assets/js/kaiadmin.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function validerCand(id, action) {
    const isAccept = action === 'accepter';
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
        fetch('?action=candidatures', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: isAccept ? 'Acceptée !' : 'Refusée', timer: 1200, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Erreur', text: data.message });
            }
        });
    });
}

const wrapper = document.querySelector('.wrapper');
const toggleBtn = document.querySelector('.toggle-sidebar');
const showBtn = document.getElementById('showSidebarBtn');
if (toggleBtn) toggleBtn.addEventListener('click', () => wrapper.classList.toggle('sidebar-hidden'));
if (showBtn)   showBtn.addEventListener('click',   () => wrapper.classList.remove('sidebar-hidden'));
</script>
</body>
</html>
