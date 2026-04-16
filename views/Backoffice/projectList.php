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
$search = trim($_GET['search'] ?? '');
$isClient = false;

if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    $projects = $projectController->listProjects($search);
    $pdfContent = $projectController->renderProjectsPdf($projects);

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="projets_skillbridge.pdf"');
    header('Content-Length: ' . strlen($pdfContent));
    echo $pdfContent;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $ok = $projectController->deleteProject($id);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Projet supprime' : 'Erreur suppression']);
        exit;
    }

    if ($action === 'add') {
        if (!$isClient) {
            echo json_encode(['success' => false, 'message' => 'Seul un client peut ajouter un projet']);
            exit;
        }

        $validation = $projectController->validateProjectData($_POST, true);
        if (!$validation['valid']) {
            echo json_encode(['success' => false, 'message' => $validation['message']]);
            exit;
        }

        $projectData = $validation['data'];

        $project = new Project(
            $projectData['titre'],
            $projectData['description'],
            $projectData['budget'],
            $projectData['date_creation'],
            $projectData['statut']
        );
        $ok = $projectController->addProject($project);

        echo json_encode(['success' => $ok, 'message' => $ok ? 'Projet ajoute' : 'Erreur ajout']);
        exit;
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Donnees invalides']);
            exit;
        }

        $validation = $projectController->validateProjectData($_POST, true);
        if (!$validation['valid']) {
            echo json_encode(['success' => false, 'message' => $validation['message']]);
            exit;
        }

        $projectData = $validation['data'];

        $project = new Project(
            $projectData['titre'],
            $projectData['description'],
            $projectData['budget'],
            $projectData['date_creation'],
            $projectData['statut']
        );
        $project->setId($id);
        $ok = $projectController->updateProject($project);

        echo json_encode(['success' => $ok, 'message' => $ok ? 'Projet modifie' : 'Erreur modification']);
        exit;
    }

    if ($action === 'publish') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identifiant invalide']);
            exit;
        }

        $project = $projectController->getProjectById($id);
        if (!$project) {
            echo json_encode(['success' => false, 'message' => 'Projet introuvable']);
            exit;
        }

        $project->setStatut('en_cours');
        $ok = $projectController->updateProject($project);

        echo json_encode(['success' => $ok, 'message' => $ok ? 'Projet publie' : 'Erreur publication']);
        exit;
    }

    if ($action === 'get_project') {
        $id = (int)($_POST['id'] ?? 0);
        $project = $projectController->getProjectById($id);

        if ($project) {
            echo json_encode([
                'success' => true,
                'id' => $project->getId(),
                'titre' => $project->getTitre(),
                'description' => $project->getDescription(),
                'budget' => $project->getBudget(),
                'date_creation' => $project->getDateCreation(),
                'statut' => $project->getStatut(),
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Projet introuvable']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
    exit;
}

$projects = $projectController->listProjects($search);
$stats = $projectController->getStats();

function badgeClassForStatus($status)
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
                    <li class="nav-item active">
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
                <div class="d-flex align-items-center justify-content-between mb-4 mt-3">
                    <h4 class="page-title mb-0">Gestion des Projets Freelance</h4>
                    <div class="d-flex gap-2">
                        <a href="?action=projectdashboard" class="btn btn-primary btn-sm">
                            <i class="fas fa-chart-pie me-1"></i>Tableau de bord
                        </a>
                        <?php if ($isClient): ?>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                            <i class="fas fa-plus me-1"></i>Ajouter Projet
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card card-stats card-round">
                            <div class="card-body">
                                <p class="card-category">Total projets</p>
                                <h4 class="card-title"><?= (int)$stats['total'] ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-stats card-round">
                            <div class="card-body">
                                <p class="card-category">Budget total</p>
                                <h4 class="card-title"><?= number_format((float)$stats['budget_total'], 2, ',', ' ') ?> TND</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-stats card-round">
                            <div class="card-body">
                                <p class="card-category">Repartition statuts</p>
                                <h6 class="mb-0">
                                    En cours: <?= (int)$stats['status']['en_cours'] ?> |
                                    Termine: <?= (int)$stats['status']['termine'] ?> |
                                    En attente: <?= (int)$stats['status']['en_attente'] ?>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <form method="GET" class="d-flex gap-2" style="width: 100%; max-width: 520px;">
                                <input type="hidden" name="action" value="projectlist">
                                <input type="text" class="form-control" name="search" placeholder="Rechercher titre, description, statut..." value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="btn btn-outline-primary">Rechercher</button>
                            </form>
                            <a href="?action=projectlist&export=pdf&search=<?= urlencode($search) ?>" class="btn btn-danger btn-sm">
                                <i class="fas fa-file-pdf me-1"></i>Export PDF
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Titre</th>
                                        <th>Description</th>
                                        <th>Budget</th>
                                        <th>Date creation</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($projects)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Aucun projet trouve.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($projects as $project): ?>
                                    <tr>
                                        <td><?= $project->getId() ?></td>
                                        <td><?= htmlspecialchars($project->getTitre()) ?></td>
                                        <td><?= htmlspecialchars(mb_strimwidth($project->getDescription(), 0, 90, '...')) ?></td>
                                        <td><?= number_format($project->getBudget(), 2, ',', ' ') ?> TND</td>
                                        <td><?= htmlspecialchars($project->getDateCreation()) ?></td>
                                        <td>
                                            <span class="badge bg-<?= badgeClassForStatus($project->getStatut()) ?>">
                                                <?= htmlspecialchars($project->getStatut()) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($project->getStatut() === 'en_attente'): ?>
                                            <button class="btn btn-success btn-sm" title="Publier" onclick="publishProject(<?= $project->getId() ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <?php endif; ?>
                                            <button class="btn btn-warning btn-sm" onclick="editProject(<?= $project->getId() ?>)" data-bs-toggle="modal" data-bs-target="#editProjectModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="deleteProject(<?= $project->getId() ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
    </div>
</div>

<?php if ($isClient): ?>
<div class="modal fade" id="addProjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un projet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addProjectForm" novalidate>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Titre</label>
                        <input type="text" name="titre" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Budget</label>
                        <input type="number" step="0.01" name="budget" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date de creation</label>
                        <input type="date" name="date_creation" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select">
                            <option value="en_attente">en_attente</option>
                            <option value="en_cours">en_cours</option>
                            <option value="termine">termine</option>
                        </select>
                    </div>
                    <div id="addProjectMsg"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="editProjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier le projet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editProjectForm" novalidate>
                <input type="hidden" name="id" id="editProjectId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Titre</label>
                        <input type="text" name="titre" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Budget</label>
                        <input type="number" step="0.01" name="budget" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date de creation</label>
                        <input type="date" name="date_creation" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select">
                            <option value="en_attente">en_attente</option>
                            <option value="en_cours">en_cours</option>
                            <option value="termine">termine</option>
                        </select>
                    </div>
                    <div id="editProjectMsg"></div>
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
function validateProjectFormData(formData) {
    const titre = String(formData.get('titre') || '').trim();
    const description = String(formData.get('description') || '').trim();
    const budgetText = String(formData.get('budget') || '').trim();
    const dateCreation = String(formData.get('date_creation') || '').trim();
    const statut = String(formData.get('statut') || '').trim();
    const validStatus = ['en_attente', 'en_cours', 'termine'];

    if (!titre || !description) {
        return 'Titre et description obligatoires';
    }

    if (!budgetText || Number.isNaN(Number(budgetText)) || Number(budgetText) < 0) {
        return 'Le budget doit etre positif';
    }

    if (!/^\d{4}-\d{2}-\d{2}$/.test(dateCreation)) {
        return 'Date de creation invalide';
    }

    if (statut && !validStatus.includes(statut)) {
        return 'Statut invalide';
    }

    return '';
}

const addProjectForm = document.getElementById('addProjectForm');
if (addProjectForm) {
    addProjectForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const validationMessage = validateProjectFormData(formData);
        const msg = document.getElementById('addProjectMsg');

        if (validationMessage) {
            msg.innerHTML = '<div class="alert alert-danger">' + validationMessage + '</div>';
            return;
        }

        formData.append('action', 'add');

        fetch('?action=projectlist', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    msg.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                    setTimeout(() => location.reload(), 800);
                } else {
                    msg.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                }
            });
    });
}

function editProject(id) {
    const formData = new FormData();
    formData.append('action', 'get_project');
    formData.append('id', id);

    fetch('?action=projectlist', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                return;
            }
            document.getElementById('editProjectId').value = data.id;
            document.querySelector('#editProjectForm input[name="titre"]').value = data.titre;
            document.querySelector('#editProjectForm textarea[name="description"]').value = data.description;
            document.querySelector('#editProjectForm input[name="budget"]').value = data.budget;
            document.querySelector('#editProjectForm input[name="date_creation"]').value = data.date_creation;
            document.querySelector('#editProjectForm select[name="statut"]').value = data.statut;
        });
}

document.getElementById('editProjectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const validationMessage = validateProjectFormData(formData);
    const msg = document.getElementById('editProjectMsg');

    if (validationMessage) {
        msg.innerHTML = '<div class="alert alert-danger">' + validationMessage + '</div>';
        return;
    }

    formData.append('action', 'edit');

    fetch('?action=projectlist', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                msg.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                setTimeout(() => location.reload(), 800);
            } else {
                msg.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
        });
});

function deleteProject(id) {
    if (!confirm('Supprimer ce projet ?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('?action=projectlist', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erreur');
            }
        });
}

function publishProject(id) {
    if (!confirm('Publier ce projet maintenant ?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'publish');
    formData.append('id', id);

    fetch('?action=projectlist', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erreur');
            }
        });
}
</script>

</body>
</html>
