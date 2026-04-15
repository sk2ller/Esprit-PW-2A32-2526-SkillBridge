<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header('Location: ?action=home');
    exit;
}
require_once __DIR__ . '/../../Controllers/BrainstormingController.php';

$brainstormController = new BrainstormingController();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'update_status' && $id > 0) {
        $status = (int)($_POST['status'] ?? 0);
        $success = $brainstormController->updateAccepted($id, $status);
        $message = $status === 1 ? 'Brainstorming accepté.' : 'Brainstorming rejeté.';
        
        // Return JSON for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
    
    if ($action === 'add_brainstorming') {
        // Préparer les données pour le controller
        $data = [
            'titre' => $_POST['titre'] ?? '',
            'description' => $_POST['description'] ?? '',
            'date_debut' => $_POST['date_debut'] ?? '',
            'user_id' => $_SESSION['user_id'] // Admin qui crée le brainstorming
        ];

        // Utiliser le controller pour créer le brainstorming avec validation
        $result = $brainstormController->createBrainstorming($data);

        header('Content-Type: application/json');
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => $result['message']]);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message'], 'errors' => $result['errors'] ?? []]);
        }
        exit;
    }

    if ($action === 'edit_brainstorming' && $id > 0) {
        // Préparer les données pour le controller
        $data = [
            'titre' => $_POST['titre'] ?? '',
            'description' => $_POST['description'] ?? '',
            'date_debut' => $_POST['date_debut'] ?? ''
        ];

        // Utiliser le controller pour mettre à jour le brainstorming avec validation
        $result = $brainstormController->updateBrainstormingWithValidation($id, $data);

        header('Content-Type: application/json');
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => $result['message']]);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message'], 'errors' => $result['errors'] ?? []]);
        }
        exit;
    }

    if ($action === 'delete_brainstorming' && $id > 0) {
        $success = $brainstormController->deleteBrainstorming($id);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $success ? 'Brainstorming supprimé avec succès' : 'Erreur lors de la suppression']);
        exit;
    }

    if ($action === 'bulk_accept') {
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        $successCount = 0;
        
        foreach ($ids as $id) {
            if ($brainstormController->updateAccepted((int)$id, 1)) {
                $successCount++;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => "$successCount brainstorming(s) accepté(s)"]);
        exit;
    }

    if ($action === 'bulk_delete') {
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        $successCount = 0;
        
        foreach ($ids as $id) {
            if ($brainstormController->deleteBrainstorming((int)$id)) {
                $successCount++;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => "$successCount brainstorming(s) supprimé(s)"]);
        exit;
    }
}

$brainstormings = $brainstormController->listAll();

// Handle GET request for details
if (isset($_GET['get_details'])) {
    $id = (int)$_GET['get_details'];
    $brainstorming = $brainstormController->getById($id);
    
    header('Content-Type: application/json');
    if ($brainstorming) {
        // Get user info
        $sql = "SELECT nom, prenom FROM User WHERE id = :id";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id' => $brainstorming['user_id']]);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        
        $brainstorming['user_nom'] = $user['nom'] ?? '';
        $brainstorming['user_prenom'] = $user['prenom'] ?? '';
        
        echo json_encode(['success' => true, 'brainstorming' => $brainstorming]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Brainstorming non trouvé']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Administration Brainstormings — SkillBridge</title>
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
    <script src="/Views/assets/js/brainstorming-validation.js"></script>
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
                    <li class="nav-section"><h4 class="text-section">Menu</h4></li>
                    <li class="nav-item">
                        <a href="?action=userlist">
                            <i class="fas fa-users"></i>
                            <p>Utilisateurs</p>
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a href="?action=brainstorming_admin">
                            <i class="fas fa-lightbulb"></i>
                            <p>Brainstormings</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=job_offers">
                            <i class="fas fa-briefcase"></i>
                            <p>Offre de Job</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=projects">
                            <i class="fas fa-project-diagram"></i>
                            <p>Projets</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=services">
                            <i class="fas fa-cogs"></i>
                            <p>Services</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=products">
                            <i class="fas fa-cube"></i>
                            <p>Produits</p>
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
                <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                    <li class="nav-item">
                        <a href="?action=logout" class="nav-link" title="Déconnexion">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="container">
            <div class="page-inner">
                <div class="page-header">
                    <h4 class="page-title">Brainstormings soumis</h4>
                    <div style="margin-top: 1rem;">
                        <a href="?action=home" class="btn btn-outline-primary btn-sm">Retour Accueil</a>
                    </div>
                </div>
                <?php if ($message): ?>
                    <div class="alert alert-success" id="statusMessage"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Liste des Brainstormings</h5>
                                <div class="d-flex gap-2">
                                    <div class="d-flex flex-column">
                                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Rechercher par titre, date, auteur ou statut..." style="width: 250px;">
                                        <small class="text-muted mt-1" style="font-size: 0.75rem;">Ex: "accepté", "2024-01", "Jean"</small>
                                    </div>
                                    <select id="statusFilter" class="form-select form-select-sm" style="width: 150px;">
                                        <option value="">Tous les statuts</option>
                                        <option value="1">Acceptés</option>
                                        <option value="0">En attente</option>
                                    </select>
                                    <a href="?action=export_brainstorming_excel" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-excel me-1"></i>Exporter Excel
                                    </a>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBrainstormingModal">
                                        <i class="fas fa-plus me-1"></i>Ajouter Brainstorming
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Statistics Cards -->
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h5 class="card-title"><?= count($brainstormings) ?></h5>
                                                <p class="card-text">Total Brainstormings</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h5 class="card-title"><?= count(array_filter($brainstormings, fn($b) => $b['accepted'] == 1)) ?></h5>
                                                <p class="card-text">Acceptés</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body text-center">
                                                <h5 class="card-title"><?= count(array_filter($brainstormings, fn($b) => $b['accepted'] == 0)) ?></h5>
                                                <p class="card-text">En attente</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body text-center">
                                                <h5 class="card-title">
                                                    <?php
                                                    $today = date('Y-m-d');
                                                    $recent = count(array_filter($brainstormings, fn($b) => $b['created_at'] >= date('Y-m-d H:i:s', strtotime('-7 days'))));
                                                    echo $recent;
                                                    ?>
                                                </h5>
                                                <p class="card-text">Cette semaine</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <button class="btn btn-success btn-sm me-2" id="bulkAcceptBtn" style="display: none;">
                                            <i class="fas fa-check me-1"></i>Accepter sélectionnés
                                        </button>
                                        <button class="btn btn-danger btn-sm" id="bulkDeleteBtn" style="display: none;">
                                            <i class="fas fa-trash me-1"></i>Supprimer sélectionnés
                                        </button>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                        <label class="form-check-label" for="selectAll">
                                            Tout sélectionner
                                        </label>
                                    </div>
                                </div>
                                
                                <?php if (empty($brainstormings)): ?>
                                    <p>Aucun brainstorming soumis pour le moment.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped" id="brainstormingTable">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th><input type="checkbox" id="headerCheckbox"></th>
                                                    <th>ID</th>
                                                    <th>Titre</th>
                                                    <th>Description</th>
                                                    <th>Date début</th>
                                                    <th>Accepté</th>
                                                    <th>User ID</th>
                                                    <th>Créé le</th>
                                                    <th>Proposé par</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($brainstormings as $item): ?>
                                                    <tr id="row-<?= htmlspecialchars($item['id']) ?>" data-id="<?= htmlspecialchars($item['id']) ?>">
                                                        <td><input type="checkbox" class="rowCheckbox" value="<?= htmlspecialchars($item['id']) ?>"></td>
                                                        <td><code><?= htmlspecialchars($item['id']) ?></code></td>
                                                        <td><strong><?= htmlspecialchars($item['titre']) ?></strong></td>
                                                        <td>
                                                            <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($item['description']) ?>">
                                                                <?= htmlspecialchars($item['description']) ?>
                                                            </div>
                                                        </td>
                                                        <td><span class="badge bg-light text-dark"><?= htmlspecialchars($item['date_debut']) ?></span></td>
                                                        <td>
                                                            <?php if ($item['accepted'] == 1): ?>
                                                                <span class="badge bg-success"><i class="fas fa-check"></i> Oui</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning"><i class="fas fa-clock"></i> Non</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><code><?= htmlspecialchars($item['user_id']) ?></code></td>
                                                        <td><small class="text-muted"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($item['created_at']))) ?></small></td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-circle me-2" style="width: 30px; height: 30px; background: #007bff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">
                                                                    <?= strtoupper(substr($item['user_prenom'] ?? 'U', 0, 1) . substr($item['user_nom'] ?? '', 0, 1)) ?>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-bold"><?= htmlspecialchars(($item['user_prenom'] ?? '') . ' ' . ($item['user_nom'] ?? '')) ?></div>
                                                                    <small class="text-muted"><?= htmlspecialchars($item['user_email'] ?? '') ?></small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <button type="button" class="btn btn-outline-info btn-sm" onclick="viewDetails(<?= htmlspecialchars($item['id']) ?>)" title="Voir détails">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="editBrainstorming(<?= htmlspecialchars($item['id']) ?>)" title="Modifier">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteBrainstorming(<?= htmlspecialchars($item['id']) ?>)" title="Supprimer">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-outline-success btn-sm" onclick="updateStatus(<?= htmlspecialchars($item['id']) ?>, 1)" title="Accepter">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateStatus(<?= htmlspecialchars($item['id']) ?>, 0)" title="Rejeter">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<!-- Add Brainstorming Modal -->
<div class="modal fade" id="addBrainstormingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Brainstorming</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addBrainstormingForm" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="addTitre">Titre <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            id="addTitre"
                            name="titre" 
                            class="form-control" 
                            minlength="5"
                            maxlength="100"
                            pattern="[a-zA-Z0-9À-ÿ\s\-\.,!?\']+"
                            required
                            onblur="validateField('addTitre')"
                            oninput="clearError('addTitre')">
                        <div class="text-danger small mt-1" id="addTitre-error" style="display: none;"></div>
                        <div class="text-muted small mt-1">Minimum 5 caractères, maximum 100 caractères</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="addDescription">Description <span class="text-danger">*</span></label>
                        <textarea 
                            id="addDescription"
                            name="description" 
                            class="form-control" 
                            rows="4" 
                            minlength="20"
                            maxlength="2000"
                            required
                            onblur="validateField('addDescription')"
                            oninput="clearError('addDescription')"></textarea>
                        <div class="text-danger small mt-1" id="addDescription-error" style="display: none;"></div>
                        <div class="text-muted small mt-1">Minimum 20 caractères, maximum 2000 caractères</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="addDateDebut">Date de début <span class="text-danger">*</span></label>
                        <input 
                            type="date" 
                            id="addDateDebut"
                            name="date_debut" 
                            class="form-control" 
                            required
                            onblur="validateField('addDateDebut')"
                            oninput="clearError('addDateDebut')">
                        <div class="text-danger small mt-1" id="addDateDebut-error" style="display: none;"></div>
                        <div class="text-muted small mt-1">La date doit être aujourd'hui ou dans le futur</div>
                    </div>
                    <div id="addBrainstormingMsg"></div>
                </div>
 

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails du Brainstorming</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detailsContent">
                    <p>Chargement...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Brainstorming Modal -->
<div class="modal fade" id="editBrainstormingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier le Brainstorming</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBrainstormingForm" method="post">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="editTitre">Titre <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            id="editTitre"
                            name="titre" 
                            class="form-control" 
                            minlength="5"
                            maxlength="100"
                            pattern="[a-zA-Z0-9À-ÿ\s\-\.,!?\']+"
                            required
                            onblur="validateField('editTitre')"
                            oninput="clearError('editTitre')">
                        <div class="text-danger small mt-1" id="editTitre-error" style="display: none;"></div>
                        <div class="text-muted small mt-1">Minimum 5 caractères, maximum 100 caractères</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editDescription">Description <span class="text-danger">*</span></label>
                        <textarea 
                            id="editDescription"
                            name="description" 
                            class="form-control" 
                            rows="4" 
                            minlength="20"
                            maxlength="2000"
                            required
                            onblur="validateField('editDescription')"
                            oninput="clearError('editDescription')"></textarea>
                        <div class="text-danger small mt-1" id="editDescription-error" style="display: none;"></div>
                        <div class="text-muted small mt-1">Minimum 20 caractères, maximum 2000 caractères</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editDateDebut">Date de début <span class="text-danger">*</span></label>
                        <input 
                            type="date" 
                            id="editDateDebut"
                            name="date_debut" 
                            class="form-control" 
                            required
                            onblur="validateField('editDateDebut')"
                            oninput="clearError('editDateDebut')">
                        <div class="text-danger small mt-1" id="editDateDebut-error" style="display: none;"></div>
                        <div class="text-muted small mt-1">La date doit être aujourd'hui ou dans le futur</div>
                    </div>
                    <div id="editBrainstormingMsg"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer ce brainstorming ? Cette action est irréversible.</p>
                <div id="deleteMsg"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Supprimer</button>
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

<script>
function updateStatus(id, status) {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('id', id);
    formData.append('status', status);
    
    fetch('?action=brainstorming_admin', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update the badge in the table
            const row = document.getElementById('row-' + id);
            const badge = row.querySelector('.badge');
            if (status == 1) {
                badge.className = 'badge bg-success';
                badge.textContent = 'Accepté';
            } else {
                badge.className = 'badge bg-warning';
                badge.textContent = 'En attente';
            }
            
            // Show success message
            const msgDiv = document.getElementById('statusMessage');
            if (msgDiv) {
                msgDiv.textContent = data.message;
                msgDiv.className = 'alert alert-success';
                msgDiv.style.display = 'block';
                setTimeout(() => msgDiv.style.display = 'none', 3000);
            }
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

<script>
function renderValidationErrors(errors) {
    if (!errors) return '';
    const list = Array.isArray(errors) ? errors : Object.values(errors);
    return '<ul>' + list.map(error => '<li>' + error + '</li>').join('') + '</ul>';
}

// Validation functions for brainstorming forms
function validateField(fieldName) {
    const field = document.getElementById(fieldName);
    if (!field) return true;
    
    const errorDiv = document.getElementById(fieldName + '-error');
    let error = '';

    if (fieldName === 'addTitre' || fieldName === 'editTitre') {
        const titre = field.value.trim();
        if (!titre) {
            error = 'Le titre est obligatoire.';
        } else if (titre.length < 5) {
            error = 'Le titre doit contenir au moins 5 caractères.';
        } else if (titre.length > 100) {
            error = 'Le titre ne peut pas dépasser 100 caractères.';
        } else if (!/^[a-zA-Z0-9À-ÿ\s\-\.,!?\'"]+$/.test(titre)) {
            error = 'Le titre contient des caractères non autorisés.';
        }
    } else if (fieldName === 'addDescription' || fieldName === 'editDescription') {
        const description = field.value.trim();
        if (!description) {
            error = 'La description est obligatoire.';
        } else if (description.length < 20) {
            error = 'La description doit contenir au moins 20 caractères.';
        } else if (description.length > 2000) {
            error = 'La description ne peut pas dépasser 2000 caractères.';
        }
    } else if (fieldName === 'addDateDebut' || fieldName === 'editDateDebut') {
        const date = field.value;
        if (!date) {
            error = 'La date est obligatoire.';
        } else {
            const selectedDate = new Date(date);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                error = 'La date ne peut pas être dans le passé.';
            } else {
                const maxDate = new Date();
                maxDate.setFullYear(maxDate.getFullYear() + 1);
                if (selectedDate > maxDate) {
                    error = 'La date ne peut pas dépasser un an.';
                }
            }
        }
    }

    if (error) {
        errorDiv.textContent = error;
        errorDiv.style.display = 'block';
        field.classList.add('is-invalid');
        return false;
    } else {
        errorDiv.style.display = 'none';
        field.classList.remove('is-invalid');
        return true;
    }
}

function clearError(fieldName) {
    const errorDiv = document.getElementById(fieldName + '-error');
    if (errorDiv) {
        errorDiv.style.display = 'none';
        document.getElementById(fieldName).classList.remove('is-invalid');
    }
}

function validateFormFields(formPrefix) {
    const fields = [
        formPrefix + 'Titre',
        formPrefix + 'Description',
        formPrefix + 'DateDebut'
    ];
    let isValid = true;

    fields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });

    return isValid;
}

// Handle add form submission
document.getElementById('addBrainstormingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validateFormFields('add')) {
        return false;
    }
    
    const formData = new FormData(this);
    formData.append('action', 'add_brainstorming');
    
    fetch('?action=brainstorming_admin', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        const msg = document.getElementById('addBrainstormingMsg');
        if (data.success) {
            msg.innerHTML = '<div class="alert alert-success">✓ ' + data.message + '</div>';
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            msg.innerHTML = '<div class="alert alert-danger">✗ ' + data.message + renderValidationErrors(data.errors) + '</div>';
        }
    })
    .catch(error => console.error('Error:', error));
});

function viewDetails(id) {
    // Fetch brainstorming details
    fetch('?action=brainstorming_admin&get_details=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const item = data.brainstorming;
                const userName = (item.user_prenom || '') + ' ' + (item.user_nom || '');
                const status = item.accepted == 1 ? 'Accepté' : 'En attente';
                const statusClass = item.accepted == 1 ? 'success' : 'warning';
                
                document.getElementById('detailsContent').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informations générales</h6>
                            <p><strong>ID:</strong> ${item.id}</p>
                            <p><strong>Titre:</strong> ${item.titre}</p>
                            <p><strong>Date de début:</strong> ${item.date_debut}</p>
                            <p><strong>Statut:</strong> <span class="badge bg-${statusClass}">${status}</span></p>
                            <p><strong>Proposé par:</strong> ${userName}</p>
                            <p><strong>Date de création:</strong> ${item.created_at}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Description</h6>
                            <div class="border p-3 rounded">
                                ${item.description.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    </div>
                `;
                new bootstrap.Modal(document.getElementById('viewDetailsModal')).show();
            }
        })
        .catch(error => console.error('Error:', error));
}

function editBrainstorming(id) {
    // Fetch brainstorming details for editing
    fetch('?action=brainstorming_admin&get_details=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const item = data.brainstorming;
                document.getElementById('editId').value = item.id;
                document.getElementById('editTitre').value = item.titre;
                document.getElementById('editDescription').value = item.description;
                document.getElementById('editDateDebut').value = item.date_debut;
                
                new bootstrap.Modal(document.getElementById('editBrainstormingModal')).show();
            }
        })
        .catch(error => console.error('Error:', error));
}

function deleteBrainstorming(id) {
    document.getElementById('confirmDeleteBtn').onclick = function() {
        const formData = new FormData();
        formData.append('action', 'delete_brainstorming');
        formData.append('id', id);
        
        fetch('?action=brainstorming_admin', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            const msg = document.getElementById('deleteMsg');
            if (data.success) {
                msg.innerHTML = '<div class="alert alert-success">✓ ' + data.message + '</div>';
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                msg.innerHTML = '<div class="alert alert-danger">✗ ' + data.message + '</div>';
            }
        })
        .catch(error => console.error('Error:', error));
    };
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Handle edit form submission
document.getElementById('editBrainstormingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validateFormFields('edit')) {
        return false;
    }
    
    const formData = new FormData(this);
    formData.append('action', 'edit_brainstorming');
    
    fetch('?action=brainstorming_admin', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        const msg = document.getElementById('editBrainstormingMsg');
        if (data.success) {
            msg.innerHTML = '<div class="alert alert-success">✓ ' + data.message + '</div>';
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            msg.innerHTML = '<div class="alert alert-danger">✗ ' + data.message + renderValidationErrors(data.errors) + '</div>';
        }
    })
    .catch(error => console.error('Error:', error));
});

// Search and filter functionality
document.getElementById('searchInput').addEventListener('input', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function filterTable() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        // Get data from different columns
        const title = row.cells[2].textContent.toLowerCase();
        const description = row.cells[3].textContent.toLowerCase();
        const dateDebut = row.cells[4].textContent.toLowerCase();
        const statusBadge = row.cells[5].querySelector('.badge');
        const status = statusBadge.classList.contains('bg-success') ? '1' : '0';
        const author = row.cells[8].textContent.toLowerCase();

        // Check if search term matches any of the fields
        const matchesSearch = searchTerm === '' ||
            title.includes(searchTerm) ||
            description.includes(searchTerm) ||
            dateDebut.includes(searchTerm) ||
            author.includes(searchTerm) ||
            (searchTerm === 'accepté' && status === '1') ||
            (searchTerm === 'en attente' && status === '0') ||
            (searchTerm === 'acceptée' && status === '1') ||
            (searchTerm === 'attente' && status === '0');

        // Check status filter
        const matchesStatus = !statusFilter || status === statusFilter;

        // Show/hide row based on filters
        row.style.display = matchesSearch && matchesStatus ? '' : 'none';
    });

    // Update bulk action buttons visibility
    updateBulkButtons();
}

// Bulk actions functionality
document.getElementById('headerCheckbox').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.rowCheckbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkButtons();
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('rowCheckbox')) {
        updateBulkButtons();
    }
});

function updateBulkButtons() {
    const checkedBoxes = document.querySelectorAll('.rowCheckbox:checked');
    const bulkAcceptBtn = document.getElementById('bulkAcceptBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    if (checkedBoxes.length > 0) {
        bulkAcceptBtn.style.display = 'inline-block';
        bulkDeleteBtn.style.display = 'inline-block';
    } else {
        bulkAcceptBtn.style.display = 'none';
        bulkDeleteBtn.style.display = 'none';
    }
}

document.getElementById('bulkAcceptBtn').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.rowCheckbox:checked');
    const ids = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (confirm(`Accepter ${ids.length} brainstorming(s) ?`)) {
        bulkAction('accept', ids);
    }
});

document.getElementById('bulkDeleteBtn').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.rowCheckbox:checked');
    const ids = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (confirm(`Supprimer ${ids.length} brainstorming(s) ? Cette action est irréversible.`)) {
        bulkAction('delete', ids);
    }
});

function bulkAction(action, ids) {
    const formData = new FormData();
    formData.append('action', 'bulk_' + action);
    formData.append('ids', JSON.stringify(ids));
    
    fetch('?action=brainstorming_admin', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
</body>
</html>

