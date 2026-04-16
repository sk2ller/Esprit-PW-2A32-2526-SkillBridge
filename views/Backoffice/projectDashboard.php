<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ?action=login');
    exit;
}

if (!isset($_SESSION['user_role']) || (int)$_SESSION['user_role'] !== 1) {
    header('Location: ?action=home');
    exit;
}

require_once __DIR__ . '/../../Controllers/ProjectController.php';

$projectController = new ProjectController();
$stats = $projectController->getStats();
$recentProjects = array_slice($projectController->listProjects(), 0, 6);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Dashboard Projets - SkillBridge Admin</title>
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
</head>
<body>
<div class="wrapper">
    <div class="sidebar" data-background-color="dark">
        <div class="sidebar-logo">
            <div class="logo-header" data-background-color="dark">
                <a href="?action=home" class="logo">
                    <img src="/Views/assets/img/logo1.png" alt="SkillBridge" style="height: 30px; width: auto;">
                </a>
            </div>
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
            <div class="sidebar-content">
                <ul class="nav nav-secondary">
                    <li class="nav-item">
                        <a href="?action=projectlist">
                            <i class="fas fa-briefcase"></i>
                            <p>Projets</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=userlist">
                            <i class="fas fa-users"></i>
                            <p>Utilisateurs</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <p>Deconnexion</p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="main-panel">
        <div class="container">
            <div class="page-inner">
                <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
                    <h4 class="page-title mb-0">Tableau de Bord Projets</h4>
                    <a href="?action=projectlist" class="btn btn-primary btn-sm">
                        <i class="fas fa-list me-1"></i>Gerer les projets
                    </a>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="card card-stats card-round">
                            <div class="card-body">
                                <p class="card-category">Total projets</p>
                                <h4 class="card-title"><?= (int)$stats['total'] ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stats card-round">
                            <div class="card-body">
                                <p class="card-category">Budget total</p>
                                <h4 class="card-title"><?= number_format((float)$stats['budget_total'], 2, ',', ' ') ?> TND</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card card-stats card-round">
                            <div class="card-body">
                                <p class="card-category">En cours</p>
                                <h4 class="card-title"><?= (int)$stats['status']['en_cours'] ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card card-stats card-round">
                            <div class="card-body">
                                <p class="card-category">Termine</p>
                                <h4 class="card-title"><?= (int)$stats['status']['termine'] ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card card-stats card-round">
                            <div class="card-body">
                                <p class="card-category">En attente</p>
                                <h4 class="card-title"><?= (int)$stats['status']['en_attente'] ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Derniers projets</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Titre</th>
                                    <th>Budget</th>
                                    <th>Date creation</th>
                                    <th>Statut</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($recentProjects)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Aucun projet disponible.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentProjects as $project): ?>
                                        <tr>
                                            <td><?= $project->getId() ?></td>
                                            <td><?= htmlspecialchars($project->getTitre()) ?></td>
                                            <td><?= number_format($project->getBudget(), 2, ',', ' ') ?> TND</td>
                                            <td><?= htmlspecialchars($project->getDateCreation()) ?></td>
                                            <td><?= htmlspecialchars($project->getStatut()) ?></td>
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
    </div>
</div>

<script src="/Views/assets/js/core/jquery-3.7.1.min.js"></script>
<script src="/Views/assets/js/core/popper.min.js"></script>
<script src="/Views/assets/js/core/bootstrap.min.js"></script>
<script src="/Views/assets/js/kaiadmin.min.js"></script>
</body>
</html>
