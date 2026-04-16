<?php
require_once __DIR__ . '/../../Controllers/ProjectController.php';
require_once __DIR__ . '/../../Models/Project.php';

$projectController = new ProjectController();
$isClient = isset($_SESSION['user_id'], $_SESSION['user_role']) && (int)$_SESSION['user_role'] === 2;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $action = $_POST['action'] ?? '';
    if ($action === 'add_project') {
        if (!$isClient) {
            echo json_encode(['success' => false, 'message' => 'Seul un client peut ajouter un projet']);
            exit;
        }

        $validation = $projectController->validateProjectData($_POST, false);
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

        echo json_encode(['success' => $ok, 'message' => $ok ? 'Projet envoye. En attente de confirmation admin.' : 'Erreur ajout']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
    exit;
}

$search = trim($_GET['search'] ?? '');
$projects = $projectController->listProjects($search);
$projects = array_values(array_filter($projects, function ($project) {
    return $project->getStatut() !== 'en_attente';
}));

function statusLabel($status)
{
    if ($status === 'en_cours') {
        return 'En cours';
    }
    if ($status === 'termine') {
        return 'Termine';
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
    <link rel="stylesheet" href="/Views/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/Views/assets/css/skillbridge.css">
    <style>
        .projects-wrapper {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 0 1rem 3rem;
        }
        .projects-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        .projects-toolbar-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            width: 100%;
            max-width: 720px;
        }
        .projects-search {
            display: flex;
            gap: 0.5rem;
            width: 100%;
            max-width: 560px;
        }
        .projects-search input {
            flex: 1;
        }
        .add-project-btn {
            white-space: nowrap;
            padding: 0.4rem 0.75rem;
            font-size: 0.86rem;
            border-radius: 10px;
            font-weight: 600;
        }
        .project-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
        }
        .project-table th,
        .project-table td {
            padding: 0.8rem;
            border-bottom: 1px solid #ececec;
            text-align: left;
        }
        .project-table th {
            background: #f7f7f7;
            font-weight: 700;
        }
        .status-chip {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            font-size: 0.85rem;
            background: #efefef;
        }
        .status-chip.en_cours { background: #fff4d9; color: #8a6500; }
        .status-chip.termine { background: #def8e8; color: #12693f; }
        .status-chip.en_attente { background: #ececff; color: #2f3f90; }
        /* Fallback to keep modal hidden until explicit open if external styles fail. */
        .modal {
            display: none;
        }
        .modal.show {
            display: block;
        }
        .add-project-modal .modal-content {
            border: 0;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 18px 45px rgba(20, 22, 35, 0.22);
        }
        .add-project-modal .modal-header {
            border-bottom: 1px solid #eceff5;
            background: linear-gradient(120deg, #f8fafc, #eef4ff);
        }
        .add-project-modal .modal-footer {
            border-top: 1px solid #eceff5;
            background: #fbfcfe;
        }
        .add-project-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.85rem;
        }
        .add-project-full {
            grid-column: 1 / -1;
        }
        .form-help {
            margin-top: 0.35rem;
            color: #6c757d;
            font-size: 0.82rem;
        }
        @media (max-width: 768px) {
            .projects-toolbar-actions {
                max-width: 100%;
                justify-content: stretch;
                flex-wrap: wrap;
            }
            .projects-search {
                max-width: 100%;
            }
            .add-project-btn {
                width: 100%;
            }
            .add-project-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<nav class="navbar-top">
    <div class="container">
        <a href="?action=home" class="logo">
            <img src="/Views/assets/img/logo1.png" alt="SkillBridge" style="height: 50px; width: auto;">
        </a>
        <div class="nav-buttons">
            <a href="?action=projects" class="btn btn-secondary">Projets</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_role'] == 1): ?>
                    <a href="?action=projectdashboard" class="btn btn-secondary">Backoffice Projets</a>
                <?php endif; ?>
                <a href="?action=profile" class="btn btn-secondary">Mon Profil</a>
                <a href="?action=logout" class="btn btn-logout">Deconnexion</a>
            <?php else: ?>
                <a href="?action=login" class="btn btn-secondary">Connexion</a>
                <a href="?action=register" class="btn btn-primary">S'inscrire</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="projects-wrapper">
    <div class="projects-toolbar">
        <h1 style="margin: 0;">Catalogue des projets freelance</h1>
        <div class="projects-toolbar-actions">
            <form class="projects-search" method="GET">
                <input type="hidden" name="action" value="projects">
                <input type="text" name="search" placeholder="Recherche (titre, description, statut)" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Rechercher</button>
            </form>
            <?php if ($isClient): ?>
                <button type="button" class="btn btn-primary add-project-btn" data-bs-toggle="modal" data-bs-target="#addProjectModal" aria-label="Ajouter un projet">Ajouter</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <table class="project-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Budget</th>
                    <th>Date creation</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($projects)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #666;">Aucun projet ne correspond a la recherche.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?= htmlspecialchars($project->getTitre()) ?></td>
                    <td><?= htmlspecialchars($project->getDescription()) ?></td>
                    <td><?= number_format($project->getBudget(), 2, ',', ' ') ?> TND</td>
                    <td><?= htmlspecialchars($project->getDateCreation()) ?></td>
                    <td>
                        <span class="status-chip <?= htmlspecialchars($project->getStatut()) ?>">
                            <?= htmlspecialchars(statusLabel($project->getStatut())) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php if ($isClient): ?>
<div class="modal fade add-project-modal" id="addProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouveau projet freelance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addProjectForm" novalidate>
                <div class="modal-body">
                    <div class="add-project-grid">
                        <div class="add-project-full">
                            <label class="form-label">Titre du projet</label>
                            <input type="text" name="titre" class="form-control" placeholder="Ex: Application de suivi client">
                        </div>
                        <div class="add-project-full">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Decrivez l'objectif, les livrables et le contexte"></textarea>
                        </div>
                        <div>
                            <label class="form-label">Budget (TND)</label>
                            <input type="number" step="0.01" name="budget" class="form-control" placeholder="1500">
                            <div class="form-help">Saisir un montant positif.</div>
                        </div>
                        <div>
                            <label class="form-label">Date de creation</label>
                            <input type="date" name="date_creation" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="add-project-full">
                            <label class="form-label">Publication</label>
                            <input type="text" class="form-control" value="Apres confirmation par l'admin" readonly>
                            <div class="form-help">Votre projet sera publie apres validation de l'administration.</div>
                        </div>
                    </div>
                    <div id="addProjectMsg"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="addProjectSubmitBtn">Envoyer pour validation</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<footer class="footer">
    <p>&copy; 2026 SkillBridge. Tous droits reserves.</p>
</footer>

<script src="/Views/assets/js/core/jquery-3.7.1.min.js"></script>
<script src="/Views/assets/js/core/popper.min.js"></script>
<script src="/Views/assets/js/core/bootstrap.min.js"></script>
<?php if ($isClient): ?>
<script>
function validateProjectFormData(formData) {
    const titre = String(formData.get('titre') || '').trim();
    const description = String(formData.get('description') || '').trim();
    const budgetText = String(formData.get('budget') || '').trim();
    const dateCreation = String(formData.get('date_creation') || '').trim();

    if (!titre || !description) {
        return 'Titre et description obligatoires';
    }

    if (!budgetText || Number.isNaN(Number(budgetText)) || Number(budgetText) < 0) {
        return 'Le budget doit etre positif';
    }

    if (!/^\d{4}-\d{2}-\d{2}$/.test(dateCreation)) {
        return 'Date de creation invalide';
    }

    return '';
}

document.getElementById('addProjectForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const msg = document.getElementById('addProjectMsg');
    const formData = new FormData(this);
    const validationMessage = validateProjectFormData(formData);
    if (validationMessage) {
        msg.innerHTML = '<div class="alert alert-danger">' + validationMessage + '</div>';
        return;
    }

    const submitBtn = document.getElementById('addProjectSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Envoi...';

    formData.append('action', 'add_project');

    fetch('?action=projects', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                msg.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                setTimeout(() => location.reload(), 700);
            } else {
                msg.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Envoyer pour validation';
            }
        })
        .catch(() => {
            const msg = document.getElementById('addProjectMsg');
            msg.innerHTML = '<div class="alert alert-danger">Erreur reseau, veuillez reessayer.</div>';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Envoyer pour validation';
        });
});
</script>
<?php endif; ?>
</body>
</html>
