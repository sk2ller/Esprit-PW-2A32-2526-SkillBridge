<?php
if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 1) {
    header('Location: ?action=login');
    exit;
}

require_once __DIR__ . '/../../Controllers/BrainstormingController.php';

$brainstormController = new BrainstormingController();
$message = '';
$messageType = 'success';
$brainstormingErrors = [];
$brainstormingOld = ['titre' => '', 'description' => '', 'date_debut' => '', 'accepted' => 1];
$openBrainstormingModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);


    if ($action === 'add_brainstorming_admin') {
        $brainstormingOld = [
            'titre' => $_POST['titre'] ?? '',
            'description' => $_POST['description'] ?? '',
            'date_debut' => $_POST['date_debut'] ?? '',
            'accepted' => (int)($_POST['accepted'] ?? 1),
        ];
        $brainstorm = new Brainstorming(
            $brainstormingOld['titre'],
            $brainstormingOld['description'],
            $brainstormingOld['date_debut'],
            (int)($_SESSION['user_id'] ?? 0),
            (int)$brainstormingOld['accepted']
        );

        if ($brainstorm->validate()) {
            try {
                $brainstormController->addBrainstorming($brainstorm);
                $message = 'Brainstorming ajoute avec succes.';
                $messageType = 'success';
                $brainstormingOld = ['titre' => '', 'description' => '', 'date_debut' => '', 'accepted' => 1];
            } catch (Exception $e) {
                $message = 'Erreur lors de l ajout : ' . $e->getMessage();
                $messageType = 'danger';
                $openBrainstormingModal = true;
            }
        } else {
            $brainstormingErrors = $brainstorm->getValidationErrors();
            $message = 'Veuillez corriger le formulaire.';
            $messageType = 'danger';
            $openBrainstormingModal = true;
        }
    }
    if ($action === 'update_status' && $id > 0) {
        $status = (int)($_POST['status'] ?? 0);
        $success = $brainstormController->updateAccepted($id, $status);
        $message = $success
            ? ($status === 1 ? 'Brainstorming accepte.' : 'Brainstorming remis en attente.')
            : 'Erreur lors de la mise a jour.';
        $messageType = $success ? 'success' : 'danger';
    }

    if ($action === 'delete_brainstorming' && $id > 0) {
        $success = $brainstormController->deleteBrainstorming($id);
        $message = $success ? 'Brainstorming supprime avec succes.' : 'Erreur lors de la suppression.';
        $messageType = $success ? 'success' : 'danger';
    }
}

$brainstormings = $brainstormController->listAll();
$total = count($brainstormings);
$accepted = count(array_filter($brainstormings, fn($b) => (int)$b['accepted'] === 1));
$pending = count(array_filter($brainstormings, fn($b) => (int)$b['accepted'] !== 1));
$recent = count(array_filter($brainstormings, function ($b) {
    return !empty($b['created_at']) && strtotime($b['created_at']) >= strtotime('-7 days');
}));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Brainstorming - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-admin.css">
    <style>
        .admin-dialog {
            width: min(820px, calc(100vw - 2rem));
            border: 1px solid rgba(223, 209, 189, .95);
            border-radius: 18px;
            padding: 0;
            background: #fffaf4;
            color: var(--text);
            box-shadow: 0 26px 70px rgba(31, 31, 35, .22);
        }

        .admin-dialog::backdrop {
            background: rgba(22, 20, 18, .54);
        }

        .admin-dialog-head,
        .admin-dialog-body,
        .admin-dialog-foot {
            padding: 1.2rem 1.4rem;
        }

        .admin-dialog-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(223, 209, 189, .9);
        }

        .admin-dialog-foot {
            display: flex;
            justify-content: flex-end;
            gap: .7rem;
            border-top: 1px solid rgba(223, 209, 189, .9);
        }

        .admin-dialog-title {
            font-weight: 800;
            font-size: 1.1rem;
        }

        .admin-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .admin-field {
            display: flex;
            flex-direction: column;
            gap: .35rem;
        }

        .admin-field.full {
            grid-column: 1 / -1;
        }

        .admin-field label {
            font-weight: 800;
            color: var(--text);
            font-size: .86rem;
        }

        .admin-field input,
        .admin-field select,
        .admin-field textarea {
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: .8rem .9rem;
            background: #fff;
            color: var(--text);
            font: inherit;
        }

        .admin-field textarea {
            min-height: 150px;
            resize: vertical;
        }

        .field-error {
            color: #b42318;
            font-size: .78rem;
            font-weight: 800;
            line-height: 1.35;
            min-height: 1rem;
        }

        .admin-field.has-error input,
        .admin-field.has-error select,
        .admin-field.has-error textarea {
            border-color: #d92d20;
            box-shadow: 0 0 0 3px rgba(217, 45, 32, .12);
        }

        @media (max-width: 900px) {
            .admin-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="skillbridge-admin">
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="admin-main">
    <div class="admin-topbar">
        <div>
            <div class="topbar-title">Brainstorming</div>
            <div class="topbar-bread"><a href="?action=statistics">Dashboard</a> / Brainstormings soumis</div>
        </div>
        <div class="topbar-actions">
            <button class="topbar-btn topbar-btn-primary" type="button" onclick="openBrainstormingDialog()">Ajouter brainstorming</button>
            <a class="topbar-btn topbar-btn-outline" href="?action=idee_admin">Idees</a>
            <a class="topbar-btn topbar-btn-outline" href="?action=export_brainstorming_excel">
                <i class="fas fa-file-excel"></i> Exporter
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="admin-alert <?= $messageType === 'success' ? 'admin-alert-success' : 'admin-alert-danger' ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-widget">
            <div class="sw-icon"><i class="fas fa-lightbulb"></i></div>
            <div class="sw-value"><?= $total ?></div>
            <div class="sw-label">Total brainstorming</div>
        </div>
        <div class="stat-widget green">
            <div class="sw-icon"><i class="fas fa-check-circle"></i></div>
            <div class="sw-value"><?= $accepted ?></div>
            <div class="sw-label">Acceptes</div>
        </div>
        <div class="stat-widget orange">
            <div class="sw-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="sw-value"><?= $pending ?></div>
            <div class="sw-label">En attente</div>
        </div>
        <div class="stat-widget blue">
            <div class="sw-icon"><i class="fas fa-calendar-week"></i></div>
            <div class="sw-value"><?= $recent ?></div>
            <div class="sw-label">Cette semaine</div>
        </div>
    </div>

    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <div class="admin-table-title">Liste des brainstormings</div>
            <form method="get" class="admin-filter-bar" style="padding:0; border:0; background:transparent;">
                <input type="hidden" name="action" value="brainstorming_admin">
                <input type="text" id="brainstormingSearch" placeholder="Rechercher titre, auteur ou statut">
            </form>
        </div>

        <table class="admin-table" id="brainstormingAdminTable">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Date debut</th>
                    <th>Statut</th>
                    <th>Creation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($brainstormings)): ?>
                <tr><td colspan="6">Aucun brainstorming soumis pour le moment.</td></tr>
            <?php endif; ?>
            <?php foreach ($brainstormings as $item): ?>
                <?php
                $isAccepted = (int)$item['accepted'] === 1;
                $author = trim(($item['user_prenom'] ?? '') . ' ' . ($item['user_nom'] ?? ''));
                if ($author === '') {
                    $author = 'Utilisateur #' . (int)$item['user_id'];
                }
                ?>
                <tr data-search="<?= htmlspecialchars(mb_strtolower($item['titre'] . ' ' . $item['description'] . ' ' . $author . ' ' . ($isAccepted ? 'accepte' : 'attente'), 'UTF-8'), ENT_QUOTES) ?>">
                    <td>
                        <div class="table-service-name"><?= htmlspecialchars($item['titre']) ?></div>
                        <div style="color: var(--muted); font-size: .86rem; line-height: 1.45; margin-top: .25rem;"><?= htmlspecialchars(mb_strimwidth($item['description'], 0, 120, '...')) ?></div>
                    </td>
                    <td><?= htmlspecialchars($author) ?></td>
                    <td><?= htmlspecialchars($item['date_debut']) ?></td>
                    <td>
                        <span class="badge <?= $isAccepted ? 'badge-actif' : 'badge-pending' ?>">
                            <?= $isAccepted ? 'Accepte' : 'En attente' ?>
                        </span>
                    </td>
                    <td><?= !empty($item['created_at']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($item['created_at']))) : '-' ?></td>
                    <td>
                        <div class="admin-stack-actions">
                            <?php if (!$isAccepted): ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                    <input type="hidden" name="status" value="1">
                                    <button class="admin-btn admin-btn-success admin-btn-sm" type="submit">Accepter</button>
                                </form>
                            <?php else: ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                    <input type="hidden" name="status" value="0">
                                    <button class="admin-btn admin-btn-warning admin-btn-sm" type="submit">Attente</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" onsubmit="return confirm('Supprimer ce brainstorming ?');">
                                <input type="hidden" name="action" value="delete_brainstorming">
                                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                <button class="admin-btn admin-btn-danger admin-btn-sm" type="submit">Supprimer</button>
                            </form>
                            <a class="admin-btn admin-btn-outline admin-btn-sm" href="?action=list_idees&brainstorming_id=<?= (int)$item['id'] ?>">Idees</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</div>

<dialog class="admin-dialog" id="brainstormingAddDialog">
    <form method="post">
        <input type="hidden" name="action" value="add_brainstorming_admin">
        <div class="admin-dialog-head">
            <div class="admin-dialog-title">Ajouter un brainstorming</div>
            <button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="closeBrainstormingDialog()">Fermer</button>
        </div>
        <div class="admin-dialog-body">
            <div class="admin-form-grid">
                <div class="admin-field <?= isset($brainstormingErrors['titre']) ? 'has-error' : '' ?>">
                    <label>Titre</label>
                    <input name="titre" maxlength="100" value="<?= htmlspecialchars($brainstormingOld['titre']) ?>">
                    <div class="field-error"><?= htmlspecialchars($brainstormingErrors['titre'] ?? '') ?></div>
                </div>
                <div class="admin-field <?= isset($brainstormingErrors['date_debut']) ? 'has-error' : '' ?>">
                    <label>Date debut</label>
                    <input name="date_debut" type="date" value="<?= htmlspecialchars($brainstormingOld['date_debut']) ?>">
                    <div class="field-error"><?= htmlspecialchars($brainstormingErrors['date_debut'] ?? '') ?></div>
                </div>
                <div class="admin-field">
                    <label>Statut</label>
                    <select name="accepted">
                        <option value="1" <?= (int)$brainstormingOld['accepted'] === 1 ? 'selected' : '' ?>>Accepte</option>
                        <option value="0" <?= (int)$brainstormingOld['accepted'] === 0 ? 'selected' : '' ?>>En attente</option>
                    </select>
                </div>
                <div class="admin-field full <?= isset($brainstormingErrors['description']) ? 'has-error' : '' ?>">
                    <label>Description</label>
                    <textarea name="description"><?= htmlspecialchars($brainstormingOld['description']) ?></textarea>
                    <div class="field-error"><?= htmlspecialchars($brainstormingErrors['description'] ?? '') ?></div>
                </div>
            </div>
        </div>
        <div class="admin-dialog-foot">
            <button class="admin-btn admin-btn-outline" type="button" onclick="closeBrainstormingDialog()">Annuler</button>
            <button class="admin-btn admin-btn-primary" type="submit">Ajouter</button>
        </div>
    </form>
</dialog>

<script>
function openBrainstormingDialog() {
    document.getElementById('brainstormingAddDialog').showModal();
}

function closeBrainstormingDialog() {
    document.getElementById('brainstormingAddDialog').close();
}

const brainstormingSearch = document.getElementById('brainstormingSearch');
if (brainstormingSearch) {
    brainstormingSearch.addEventListener('input', () => {
        const term = brainstormingSearch.value.trim().toLowerCase();
        document.querySelectorAll('#brainstormingAdminTable tbody tr[data-search]').forEach((row) => {
            row.style.display = row.dataset.search.includes(term) ? '' : 'none';
        });
    });
}
</script>
<?php if ($openBrainstormingModal): ?>
<script>document.getElementById('brainstormingAddDialog').showModal();</script>
<?php endif; ?>
</body>
</html>


