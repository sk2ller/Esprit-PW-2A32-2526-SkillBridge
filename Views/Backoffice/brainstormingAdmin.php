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

    if ($action === 'edit_brainstorming_admin' && $id > 0) {
        $editBrainstormingOld = [
            'id' => $id,
            'titre' => $_POST['titre'] ?? '',
            'description' => $_POST['description'] ?? '',
            'date_debut' => $_POST['date_debut'] ?? '',
            'accepted' => (int)($_POST['accepted'] ?? 0),
        ];

        $result = $brainstormController->updateBrainstormingWithValidation($id, [
            'titre' => $editBrainstormingOld['titre'],
            'description' => $editBrainstormingOld['description'],
            'date_debut' => $editBrainstormingOld['date_debut'],
            'user_id' => (int)($_SESSION['user_id'] ?? 0),
        ]);

        if ($result['success']) {
            $brainstormController->updateAccepted($id, (int)$editBrainstormingOld['accepted']);
            $message = 'Brainstorming modifie avec succes.';
            $messageType = 'success';
            $editBrainstormingOld = ['id' => 0, 'titre' => '', 'description' => '', 'date_debut' => '', 'accepted' => 1];
        } else {
            $editBrainstormingErrors = $result['errors'] ?? [];
            $message = $result['message'] ?? 'Veuillez corriger le formulaire.';
            $messageType = 'danger';
            $openEditBrainstormingModal = true;
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

        .summary-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .8rem; }
        .summary-card { border: 1px solid rgba(223, 209, 189, .9); border-radius: 14px; background: rgba(255, 255, 255, .72); padding: .95rem; }
        .summary-card.full { grid-column: 1 / -1; }
        .summary-label { color: var(--text-muted); font-size: .74rem; text-transform: uppercase; font-weight: 800; margin-bottom: .25rem; }
        .summary-list { margin: .35rem 0 0; padding-left: 1.1rem; color: var(--text-muted); }
        .pexels-preview { display: grid; gap: .8rem; }
        .pexels-preview img { width: 100%; aspect-ratio: 16 / 9; object-fit: cover; border-radius: 14px; border: 1px solid rgba(223, 209, 189, .9); background: #fff; }
        .pexels-credit { color: var(--text-muted); font-size: .9rem; }
        .pexels-credit a { color: inherit; font-weight: 800; }

        @media (max-width: 900px) {
            .admin-form-grid,
            .summary-grid {
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
                            <button
                                class="admin-btn admin-btn-outline admin-btn-sm"
                                type="button"
                                onclick='openEditBrainstormingDialog(<?= json_encode([
                                    "id" => (int)$item["id"],
                                    "titre" => $item["titre"],
                                    "description" => $item["description"],
                                    "date_debut" => $item["date_debut"],
                                    "accepted" => (int)$item["accepted"],
                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                            >Modifier</button>
                            <button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="summarizeBrainstorming(<?= (int)$item['id'] ?>)">Resume IA</button>
                            <button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="loadBrainstormingImage(<?= (int)$item['id'] ?>)">Image</button>
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


<dialog class="admin-dialog" id="brainstormingEditDialog">
    <form method="post">
        <input type="hidden" name="action" value="edit_brainstorming_admin">
        <input type="hidden" name="id" id="edit_brainstorming_id" value="<?= (int)$editBrainstormingOld['id'] ?>">
        <div class="admin-dialog-head">
            <div class="admin-dialog-title">Modifier un brainstorming</div>
            <button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="closeEditBrainstormingDialog()">Fermer</button>
        </div>
        <div class="admin-dialog-body">
            <div class="admin-form-grid">
                <div class="admin-field <?= isset($editBrainstormingErrors['titre']) ? 'has-error' : '' ?>">
                    <label>Titre</label>
                    <input name="titre" id="edit_brainstorming_titre" maxlength="100" value="<?= htmlspecialchars($editBrainstormingOld['titre']) ?>">
                    <div class="field-error"><?= htmlspecialchars($editBrainstormingErrors['titre'] ?? '') ?></div>
                </div>
                <div class="admin-field <?= isset($editBrainstormingErrors['date_debut']) ? 'has-error' : '' ?>">
                    <label>Date debut</label>
                    <input name="date_debut" id="edit_brainstorming_date_debut" type="date" value="<?= htmlspecialchars($editBrainstormingOld['date_debut']) ?>">
                    <div class="field-error"><?= htmlspecialchars($editBrainstormingErrors['date_debut'] ?? '') ?></div>
                </div>
                <div class="admin-field">
                    <label>Statut</label>
                    <select name="accepted" id="edit_brainstorming_accepted">
                        <option value="1" <?= (int)$editBrainstormingOld['accepted'] === 1 ? 'selected' : '' ?>>Accepte</option>
                        <option value="0" <?= (int)$editBrainstormingOld['accepted'] === 0 ? 'selected' : '' ?>>En attente</option>
                    </select>
                </div>
                <div class="admin-field full <?= isset($editBrainstormingErrors['description']) ? 'has-error' : '' ?>">
                    <label>Description</label>
                    <textarea name="description" id="edit_brainstorming_description"><?= htmlspecialchars($editBrainstormingOld['description']) ?></textarea>
                    <div class="field-error"><?= htmlspecialchars($editBrainstormingErrors['description'] ?? '') ?></div>
                </div>
            </div>
        </div>
        <div class="admin-dialog-foot">
            <button class="admin-btn admin-btn-outline" type="button" onclick="closeEditBrainstormingDialog()">Annuler</button>
            <button class="admin-btn admin-btn-primary" type="submit">Enregistrer</button>
        </div>
    </form>
</dialog>

<dialog class="admin-dialog" id="pexelsDialog">
    <div class="admin-dialog-head">
        <div>
            <div class="admin-dialog-title">Image du brainstorming</div>
            <div id="pexelsSource" style="color:var(--text-muted); font-size:.85rem; margin-top:.2rem;">Pexels API</div>
        </div>
        <button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="closePexelsDialog()">Fermer</button>
    </div>
    <div class="admin-dialog-body" id="pexelsContent">Chargement...</div>
</dialog>

<dialog class="admin-dialog" id="summaryDialog">
    <div class="admin-dialog-head">
        <div>
            <div class="admin-dialog-title">Resume IA du brainstorming</div>
            <div id="summarySource" style="color:var(--text-muted); font-size:.85rem; margin-top:.2rem;">Gemini API</div>
        </div>
        <button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="closeSummaryDialog()">Fermer</button>
    </div>
    <div class="admin-dialog-body" id="summaryContent">Chargement...</div>
</dialog>
<script>

function openEditBrainstormingDialog(item) {
    document.getElementById('edit_brainstorming_id').value = item.id || 0;
    document.getElementById('edit_brainstorming_titre').value = item.titre || '';
    document.getElementById('edit_brainstorming_date_debut').value = item.date_debut || '';
    document.getElementById('edit_brainstorming_accepted').value = String(Number(item.accepted || 0));
    document.getElementById('edit_brainstorming_description').value = item.description || '';
    document.getElementById('brainstormingEditDialog').showModal();
}

function closeEditBrainstormingDialog() {
    document.getElementById('brainstormingEditDialog').close();
}
function closeSummaryDialog() {
    document.getElementById('summaryDialog').close();
}
function closePexelsDialog() {
    document.getElementById('pexelsDialog').close();
}
function escapeHtml(value) {
    return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}
function renderSummaryList(items) {
    if (!Array.isArray(items) || items.length === 0) {
        return '<p style="margin:0; color:var(--text-muted);">Aucun element detaille.</p>';
    }
    return '<ul class="summary-list">' + items.map(item => '<li>' + escapeHtml(item) + '</li>').join('') + '</ul>';
}
function renderSummary(data) {
    const s = data.summary || {};
    document.getElementById('summarySource').textContent = data.source || 'Gemini API';
    document.getElementById('summaryContent').innerHTML = '<div class="summary-grid">'
        + '<div class="summary-card full"><div class="summary-label">Vue generale</div><div>' + escapeHtml(s.overview || '-') + '</div></div>'
        + '<div class="summary-card"><div class="summary-label">Themes dominants</div>' + renderSummaryList(s.dominant_topics) + '</div>'
        + '<div class="summary-card"><div class="summary-label">Idees les plus fortes</div>' + renderSummaryList(s.strongest_ideas) + '</div>'
        + '<div class="summary-card"><div class="summary-label">Risques</div>' + renderSummaryList(s.risks) + '</div>'
        + '<div class="summary-card"><div class="summary-label">Recommandations</div>' + renderSummaryList(s.recommendations) + '</div>'
        + '<div class="summary-card full"><div class="summary-label">Decision proposee</div><div>' + escapeHtml(s.decision || '-') + '</div></div>'
        + '</div>';
}
function summarizeBrainstorming(id) {
    const dialog = document.getElementById('summaryDialog');
    const content = document.getElementById('summaryContent');
    document.getElementById('summarySource').textContent = 'Gemini API';
    content.innerHTML = '<p style="margin:0;">Generation du resume IA...</p>';
    dialog.showModal();

    const formData = new FormData();
    formData.append('id', id);

    fetch('?action=summarize_brainstorming', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                document.getElementById('summarySource').textContent = 'Resume indisponible';
                content.innerHTML = '<div class="admin-alert admin-alert-danger">' + escapeHtml(data.message || 'Impossible de generer le resume.') + '</div>';
                return;
            }
            renderSummary(data);
        })
        .catch(() => {
            document.getElementById('summarySource').textContent = 'Erreur reseau';
            content.innerHTML = '<div class="admin-alert admin-alert-danger">Erreur reseau pendant le resume IA.</div>';
        });
}
function loadBrainstormingImage(id) {
    const dialog = document.getElementById('pexelsDialog');
    const content = document.getElementById('pexelsContent');
    document.getElementById('pexelsSource').textContent = 'Pexels API';
    content.innerHTML = '<p style="margin:0;">Recherche d image...</p>';
    dialog.showModal();

    const formData = new FormData();
    formData.append('id', id);

    fetch('?action=brainstorming_image', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                document.getElementById('pexelsSource').textContent = 'Image indisponible';
                content.innerHTML = '<div class="admin-alert admin-alert-danger">' + escapeHtml(data.message || 'Impossible de charger l image.') + '</div>';
                return;
            }
            const image = data.image || {};
            document.getElementById('pexelsSource').textContent = (data.source || 'Pexels API') + ' - ' + (image.query || '');
            content.innerHTML = '<div class="pexels-preview">'
                + '<img src="' + escapeHtml(image.url || '') + '" alt="' + escapeHtml(image.alt || 'Brainstorming') + '">'
                + '<div class="pexels-credit">Photo par <a href="' + escapeHtml(image.photographer_url || '#') + '" target="_blank" rel="noopener">' + escapeHtml(image.photographer || 'Pexels') + '</a> sur <a href="' + escapeHtml(image.original_url || 'https://www.pexels.com') + '" target="_blank" rel="noopener">Pexels</a></div>'
                + '</div>';
        })
        .catch(() => {
            document.getElementById('pexelsSource').textContent = 'Erreur reseau';
            content.innerHTML = '<div class="admin-alert admin-alert-danger">Erreur reseau pendant la recherche Pexels.</div>';
        });
}
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


