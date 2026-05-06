<?php
if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 1) {
    header('Location: ?action=login');
    exit;
}

function getSortUrl($column, $currentOrderBy, $currentOrderDir, $searchQuery = '') {
    $newDirection = ($currentOrderBy === $column && $currentOrderDir === 'ASC') ? 'DESC' : 'ASC';
    $url = '?action=idee_admin&order_by=' . urlencode($column) . '&order_dir=' . $newDirection;
    if ($searchQuery !== '') {
        $url .= '&search=' . urlencode($searchQuery);
    }
    return $url;
}

function getSortIcon($column, $currentOrderBy, $currentOrderDir) {
    if ($currentOrderBy !== $column) {
        return '<i class="fas fa-sort"></i>';
    }

    return $currentOrderDir === 'ASC'
        ? '<i class="fas fa-sort-up"></i>'
        : '<i class="fas fa-sort-down"></i>';
}

function ideeStatusBadge($status) {
    return match ($status) {
        'approuvee' => 'badge-actif',
        'rejetee' => 'badge-suspendu',
        'en_etude' => 'badge-pending',
        default => 'badge-pending',
    };
}

$currentOrderBy = $_GET['order_by'] ?? 'created_at';
$currentOrderDir = $_GET['order_dir'] ?? 'DESC';
$currentSearch = trim($_GET['search'] ?? '');
$totalIdees = count($idees);
$approvedIdees = count(array_filter($idees, fn($idee) => $idee['statut'] === 'approuvee'));
$inStudyIdees = count(array_filter($idees, fn($idee) => $idee['statut'] === 'en_etude'));
$rejectedIdees = count(array_filter($idees, fn($idee) => $idee['statut'] === 'rejetee'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Idees - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-admin.css">
    <style>
        .admin-dialog { width: min(920px, calc(100vw - 2rem)); border: 1px solid rgba(223, 209, 189, .95); border-radius: 18px; padding: 0; background: #fffaf4; color: var(--text); box-shadow: 0 26px 70px rgba(31, 31, 35, .22); }
        .admin-dialog::backdrop { background: rgba(22, 20, 18, .54); }
        .admin-dialog-head, .admin-dialog-body, .admin-dialog-foot { padding: 1.2rem 1.4rem; }
        .admin-dialog-head { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(223, 209, 189, .9); }
        .admin-dialog-foot { display: flex; justify-content: flex-end; gap: .7rem; border-top: 1px solid rgba(223, 209, 189, .9); }
        .admin-dialog-title { font-weight: 800; font-size: 1.1rem; }
        .admin-dialog-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .75rem; margin-bottom: 1rem; }
        .admin-score-card { border: 1px solid rgba(223, 209, 189, .9); border-radius: 14px; padding: .9rem; background: rgba(255, 255, 255, .72); text-align: center; }
        .admin-score-label { color: var(--text-muted); font-size: .74rem; text-transform: uppercase; letter-spacing: .04em; font-weight: 800; }
        .admin-score-value { font-size: 1.45rem; font-weight: 900; margin-top: .2rem; }
        .admin-dialog-list { margin: .45rem 0 0; padding-left: 1.1rem; color: var(--text-muted); }
        .admin-mini-select { min-width: 130px; border: 1px solid var(--border); border-radius: 12px; padding: .55rem .65rem; background: #fff; color: var(--text); font-weight: 700; }
        .admin-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        .admin-field { display: flex; flex-direction: column; gap: .35rem; }
        .admin-field.full { grid-column: 1 / -1; }
        .admin-field label { font-weight: 800; color: var(--text); font-size: .86rem; }
        .admin-field input, .admin-field select, .admin-field textarea { border: 1px solid var(--border); border-radius: 14px; padding: .8rem .9rem; background: #fff; color: var(--text); font: inherit; }
        .admin-field textarea { min-height: 130px; resize: vertical; }
        .field-error { color: #b42318; font-size: .78rem; font-weight: 800; line-height: 1.35; min-height: 1rem; }
        .admin-field.has-error input, .admin-field.has-error select, .admin-field.has-error textarea { border-color: #d92d20; box-shadow: 0 0 0 3px rgba(217, 45, 32, .12); }
        .moderation-alert { display: grid; grid-template-columns: 40px minmax(0, 1fr); gap: .8rem; align-items: start; border: 1px solid rgba(217, 45, 32, .18); border-left: 5px solid #d92d20; border-radius: 14px; background: #fff7f5; color: #7a271a; padding: .9rem 1rem; margin-bottom: 1rem; }
        .moderation-alert-icon { width: 38px; height: 38px; display: grid; place-items: center; border-radius: 11px; background: #fee4e2; color: #b42318; }
        .moderation-alert-title { font-weight: 900; margin-bottom: .15rem; }
        .moderation-alert-text { margin: 0; color: #912018; line-height: 1.45; }
        .suggestion-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .8rem; }
        .suggestion-card { border: 1px solid rgba(223, 209, 189, .9); border-radius: 14px; background: rgba(255,255,255,.72); padding: .9rem; }
        .suggestion-card.full { grid-column: 1 / -1; }
        .suggestion-label { color: var(--text-muted); font-size: .74rem; text-transform: uppercase; font-weight: 800; margin-bottom: .25rem; }
        .suggestion-list { margin: .4rem 0 0; padding-left: 1.1rem; color: var(--text-muted); }
        .admin-detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .8rem; }
        .admin-detail-card { border: 1px solid var(--border); border-radius: 14px; background: rgba(255,255,255,.68); padding: .85rem; }
        .admin-detail-label { color: var(--text-muted); font-size: .75rem; font-weight: 800; text-transform: uppercase; }
        .admin-detail-value { margin-top: .25rem; font-weight: 700; line-height: 1.45; }
        @media (max-width: 900px) { .admin-dialog-grid, .admin-form-grid, .admin-detail-grid, .suggestion-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="skillbridge-admin">
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="admin-main">
    <div class="admin-topbar">
        <div>
            <div class="topbar-title">Idees</div>
            <div class="topbar-bread"><a href="?action=statistics">Dashboard</a> / Administration des idees</div>
        </div>
        <div class="topbar-actions">
            <a class="topbar-btn topbar-btn-outline" href="?action=brainstorming_admin">Brainstorming</a>
            <a class="topbar-btn topbar-btn-outline" href="?action=all_idees">Voir front</a>
            <a class="topbar-btn topbar-btn-outline" href="?action=export_idees_excel<?= $currentSearch !== '' ? '&search=' . urlencode($currentSearch) : '' ?>&order_by=<?= htmlspecialchars($currentOrderBy) ?>&order_dir=<?= htmlspecialchars($currentOrderDir) ?>">
                <i class="fas fa-file-excel"></i> Exporter
            </a>
            <button class="topbar-btn topbar-btn-primary" type="button" onclick="openAddIdeeDialog()">Ajouter idee</button>
        </div>
    </div>

    <div id="adminIdeeAlert"></div>

    <div class="stats-grid">
        <div class="stat-widget"><div class="sw-icon"><i class="fas fa-comments"></i></div><div class="sw-value"><?= $totalIdees ?></div><div class="sw-label">Idees totales</div></div>
        <div class="stat-widget green"><div class="sw-icon"><i class="fas fa-check-circle"></i></div><div class="sw-value"><?= $approvedIdees ?></div><div class="sw-label">Approuvees</div></div>
        <div class="stat-widget orange"><div class="sw-icon"><i class="fas fa-hourglass-half"></i></div><div class="sw-value"><?= $inStudyIdees ?></div><div class="sw-label">En etude</div></div>
        <div class="stat-widget blue"><div class="sw-icon"><i class="fas fa-ban"></i></div><div class="sw-value"><?= $rejectedIdees ?></div><div class="sw-label">Rejetees</div></div>
    </div>

    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <div class="admin-table-title">Liste complete</div>
            <form method="get" class="admin-filter-bar" style="padding:0; border:0; background:transparent;">
                <input type="hidden" name="action" value="idee_admin">
                <input type="text" name="search" placeholder="Rechercher idee, auteur, brainstorming" value="<?= htmlspecialchars($currentSearch) ?>">
                <button class="admin-btn admin-btn-outline admin-btn-sm" type="submit">Rechercher</button>
                <?php if ($currentSearch !== ''): ?><a class="admin-btn admin-btn-outline admin-btn-sm" href="?action=idee_admin">Effacer</a><?php endif; ?>
            </form>
        </div>

        <table class="admin-table" id="ideeAdminTable">
            <thead>
                <tr>
                    <th><a href="<?= getSortUrl('id', $currentOrderBy, $currentOrderDir, $currentSearch) ?>">ID <?= getSortIcon('id', $currentOrderBy, $currentOrderDir) ?></a></th>
                    <th><a href="<?= getSortUrl('titre', $currentOrderBy, $currentOrderDir, $currentSearch) ?>">Idee <?= getSortIcon('titre', $currentOrderBy, $currentOrderDir) ?></a></th>
                    <th>Brainstorming</th><th>Auteur</th>
                    <th><a href="<?= getSortUrl('priorite', $currentOrderBy, $currentOrderDir, $currentSearch) ?>">Priorite <?= getSortIcon('priorite', $currentOrderBy, $currentOrderDir) ?></a></th>
                    <th><a href="<?= getSortUrl('statut', $currentOrderBy, $currentOrderDir, $currentSearch) ?>">Statut <?= getSortIcon('statut', $currentOrderBy, $currentOrderDir) ?></a></th>
                    <th><a href="<?= getSortUrl('votes', $currentOrderBy, $currentOrderDir, $currentSearch) ?>">Votes <?= getSortIcon('votes', $currentOrderBy, $currentOrderDir) ?></a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($idees)): ?><tr><td colspan="8">Aucune idee trouvee.</td></tr><?php endif; ?>
            <?php foreach ($idees as $idee): ?>
                <tr id="idee-row-<?= (int)$idee['id'] ?>">
                    <td>#<?= (int)$idee['id'] ?></td>
                    <td><div class="table-service-name"><?= htmlspecialchars($idee['titre']) ?></div><div style="color: var(--text-muted); font-size: .86rem; line-height: 1.45; margin-top: .25rem;"><?= htmlspecialchars($idee['categorie']) ?></div></td>
                    <td><?= htmlspecialchars($idee['brainstorming_titre'] ?? '-') ?></td>
                    <td><?= htmlspecialchars(trim(($idee['user_prenom'] ?? '') . ' ' . ($idee['user_nom'] ?? ''))) ?></td>
                    <td><?= htmlspecialchars($idee['priorite']) ?></td>
                    <td>
                        <select class="admin-mini-select" onchange="updateIdeeStatus(<?= (int)$idee['id'] ?>, this.value)">
                            <option value="proposee" <?= $idee['statut'] === 'proposee' ? 'selected' : '' ?>>Proposee</option>
                            <option value="en_etude" <?= $idee['statut'] === 'en_etude' ? 'selected' : '' ?>>En etude</option>
                            <option value="approuvee" <?= $idee['statut'] === 'approuvee' ? 'selected' : '' ?>>Approuvee</option>
                            <option value="rejetee" <?= $idee['statut'] === 'rejetee' ? 'selected' : '' ?>>Rejetee</option>
                        </select>
                        <span class="badge <?= ideeStatusBadge($idee['statut']) ?>" style="margin-left:.4rem;"><?= htmlspecialchars(str_replace('_', ' ', $idee['statut'])) ?></span>
                    </td>
                    <td><?= (int)$idee['votes'] ?></td>
                    <td>
                        <div class="admin-stack-actions">
                            <button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="openViewIdee(<?= (int)$idee['id'] ?>)">Voir</button>
                            <button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="scoreIdeeWithAi(<?= (int)$idee['id'] ?>)">Score idee</button>
                            <button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="suggestIdeeImprovements(<?= (int)$idee['id'] ?>)">Suggestions</button>
                            <button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="openEditIdee(<?= (int)$idee['id'] ?>)">Modifier</button>
                            <button class="admin-btn admin-btn-danger admin-btn-sm" type="button" onclick="deleteIdee(<?= (int)$idee['id'] ?>)">Supprimer</button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</div>

<dialog class="admin-dialog" id="ideeViewDialog"><div class="admin-dialog-head"><div class="admin-dialog-title">Brainstorming lie</div><button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="closeDialog('ideeViewDialog')">Fermer</button></div><div class="admin-dialog-body" id="ideeViewContent">Chargement...</div></dialog>

<dialog class="admin-dialog" id="aiScoreDialog"><div class="admin-dialog-head"><div class="admin-dialog-title">Score IA de l idee</div><button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="closeDialog('aiScoreDialog')">Fermer</button></div><div class="admin-dialog-body" id="aiScoreContent">Chargement...</div></dialog>

<dialog class="admin-dialog" id="ideaSuggestionDialog"><div class="admin-dialog-head"><div><div class="admin-dialog-title">Suggestions d amelioration</div><div id="suggestionSource" style="color:var(--text-muted); font-size:.85rem; margin-top:.2rem;">Analyse de l idee</div></div><button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="closeDialog('ideaSuggestionDialog')">Fermer</button></div><div class="admin-dialog-body" id="suggestionContent">Chargement...</div></dialog>

<dialog class="admin-dialog" id="ideeAddDialog">
    <form id="addIdeeAdminForm">
        <div class="admin-dialog-head"><div class="admin-dialog-title">Ajouter une idee</div><button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="closeDialog('ideeAddDialog')">Fermer</button></div>
        <div class="admin-dialog-body"><div id="addIdeeErrors"></div><div class="admin-form-grid">
            <div class="admin-field"><label>Titre</label><input name="titre"><div class="field-error" data-error-for="titre"></div></div>
            <div class="admin-field"><label>Categorie</label><input name="categorie" value="General"><div class="field-error" data-error-for="categorie"></div></div>
            <div class="admin-field"><label>Brainstorming</label><select name="brainstorming_id"><?php foreach ($brainstormings as $brainstorming): ?><option value="<?= (int)$brainstorming['id'] ?>"><?= htmlspecialchars($brainstorming['titre']) ?></option><?php endforeach; ?></select><div class="field-error" data-error-for="brainstorming_id"></div></div>
            <div class="admin-field"><label>Priorite</label><select name="priorite"><option value="faible">Faible</option><option value="moyenne" selected>Moyenne</option><option value="haute">Haute</option></select><div class="field-error" data-error-for="priorite"></div></div>
            <div class="admin-field"><label>Statut</label><select name="statut"><option value="proposee">Proposee</option><option value="en_etude">En etude</option><option value="approuvee">Approuvee</option><option value="rejetee">Rejetee</option></select><div class="field-error" data-error-for="statut"></div></div>
            <div class="admin-field"><label>Votes</label><input name="votes" type="number" min="0" value="0"><div class="field-error" data-error-for="votes"></div></div>
            <div class="admin-field full"><label>Contenu</label><textarea name="contenu"></textarea><div class="field-error" data-error-for="contenu"></div></div>
        </div></div>
        <div class="admin-dialog-foot"><button class="admin-btn admin-btn-outline" type="button" onclick="closeDialog('ideeAddDialog')">Annuler</button><button class="admin-btn admin-btn-primary" type="submit">Ajouter</button></div>
    </form>
</dialog>

<dialog class="admin-dialog" id="ideeEditDialog">
    <form id="editIdeeAdminForm">
        <input type="hidden" name="id" id="edit_id">
        <div class="admin-dialog-head"><div class="admin-dialog-title">Modifier l idee</div><button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="closeDialog('ideeEditDialog')">Fermer</button></div>
        <div class="admin-dialog-body"><div id="editIdeeErrors"></div><div class="admin-form-grid">
            <div class="admin-field"><label>Titre</label><input name="titre" id="edit_titre"><div class="field-error" data-error-for="titre"></div></div>
            <div class="admin-field"><label>Categorie</label><input name="categorie" id="edit_categorie"><div class="field-error" data-error-for="categorie"></div></div>
            <div class="admin-field"><label>Brainstorming</label><select name="brainstorming_id" id="edit_brainstorming_id"><?php foreach ($brainstormings as $brainstorming): ?><option value="<?= (int)$brainstorming['id'] ?>"><?= htmlspecialchars($brainstorming['titre']) ?></option><?php endforeach; ?></select><div class="field-error" data-error-for="brainstorming_id"></div></div>
            <div class="admin-field"><label>Priorite</label><select name="priorite" id="edit_priorite"><option value="faible">Faible</option><option value="moyenne">Moyenne</option><option value="haute">Haute</option></select><div class="field-error" data-error-for="priorite"></div></div>
            <div class="admin-field"><label>Statut</label><select name="statut" id="edit_statut"><option value="proposee">Proposee</option><option value="en_etude">En etude</option><option value="approuvee">Approuvee</option><option value="rejetee">Rejetee</option></select><div class="field-error" data-error-for="statut"></div></div>
            <div class="admin-field"><label>Votes</label><input name="votes" id="edit_votes" type="number" min="0"><div class="field-error" data-error-for="votes"></div></div>
            <div class="admin-field full"><label>Contenu</label><textarea name="contenu" id="edit_contenu"></textarea><div class="field-error" data-error-for="contenu"></div></div>
        </div></div>
        <div class="admin-dialog-foot"><button class="admin-btn admin-btn-outline" type="button" onclick="closeDialog('ideeEditDialog')">Annuler</button><button class="admin-btn admin-btn-primary" type="submit">Enregistrer</button></div>
    </form>
</dialog>

<script>
function isModerationMessage(message) { return String(message || '').toLowerCase().includes('contenu bloque'); }
function moderationAlert(title = 'Contenu refuse') { return '<div class="moderation-alert"><div class="moderation-alert-icon"><i class="fas fa-shield-halved"></i></div><div><div class="moderation-alert-title">' + escapeHtml(title) + '</div><p class="moderation-alert-text">Le texte contient un contenu non autorise. Retirez les insultes, menaces ou elements de spam, puis reessayez.</p></div></div>'; }
function renderErrors(errors) { if (!errors) return ''; const list = [...new Set(Array.isArray(errors) ? errors : Object.values(errors))]; if (list.some(isModerationMessage)) return moderationAlert(); return '<div class="admin-alert admin-alert-danger"><strong>Erreur</strong><ul style="margin:.45rem 0 0; padding-left:1.1rem;">' + list.map(error => '<li>' + escapeHtml(error) + '</li>').join('') + '</ul></div>'; }
function clearFieldErrors(form) { form.querySelectorAll('.admin-field').forEach(field => field.classList.remove('has-error')); form.querySelectorAll('.field-error').forEach(box => box.textContent = ''); }
function showFieldErrors(form, errors) {
    clearFieldErrors(form);
    if (!errors || Array.isArray(errors)) return false;
    let shown = false;
    Object.entries(errors).forEach(([field, message]) => {
        const box = form.querySelector('[data-error-for="' + field + '"]');
        if (box) {
            box.textContent = isModerationMessage(message) ? 'Contenu refuse par la moderation automatique.' : message;
            const wrapper = box.closest('.admin-field');
            if (wrapper) wrapper.classList.add('has-error');
            shown = true;
        }
    });
    return shown;
}
function showPageAlert(message, type = 'success') { const alert = document.getElementById('adminIdeeAlert'); const klass = type === 'success' ? 'admin-alert-success' : 'admin-alert-danger'; alert.innerHTML = '<div class="admin-alert ' + klass + '">' + message + '</div>'; window.scrollTo({ top: 0, behavior: 'smooth' }); }
function escapeHtml(value) { return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;'); }
function openDialog(id) { document.getElementById(id).showModal(); }
function closeDialog(id) { document.getElementById(id).close(); }
function renderAiList(items) { if (!Array.isArray(items) || items.length === 0) return '<p style="color:var(--text-muted); margin:0;">Aucun element detaille.</p>'; return '<ul class="admin-dialog-list">' + items.map(item => '<li>' + escapeHtml(item) + '</li>').join('') + '</ul>'; }
function scoreBadge(score, label) { return '<div class="admin-score-card"><div class="admin-score-label">' + escapeHtml(label) + '</div><div class="admin-score-value">' + escapeHtml(score) + '/100</div></div>'; }
function readJsonResponse(response) {
    return response.text().then(text => JSON.parse(text.replace(/^\uFEFF+/, '').trim()));
}
function fetchIdee(id) { return fetch('?action=idee_admin&get_details=' + encodeURIComponent(id)).then(readJsonResponse).then(data => { if (!data.success) throw new Error(data.message || 'Idee introuvable.'); return data.idee; }); }
function openAddIdeeDialog() { const form = document.getElementById('addIdeeAdminForm'); form.reset(); clearFieldErrors(form); document.getElementById('addIdeeErrors').innerHTML = ''; openDialog('ideeAddDialog'); }
function openViewIdee(id) {
    const content = document.getElementById('ideeViewContent');
    content.innerHTML = 'Chargement...';
    openDialog('ideeViewDialog');
    fetchIdee(id)
        .then(idee => {
            const brainstormingStatus = Number(idee.brainstorming_accepted || 0) === 1 ? 'Accepte' : 'En attente';
            content.innerHTML = '<div class="admin-detail-grid">'
                + '<div class="admin-detail-card"><div class="admin-detail-label">Brainstorming</div><div class="admin-detail-value">' + escapeHtml(idee.brainstorming_titre || '-') + '</div></div>'
                + '<div class="admin-detail-card"><div class="admin-detail-label">Statut brainstorming</div><div class="admin-detail-value">' + escapeHtml(brainstormingStatus) + '</div></div>'
                + '<div class="admin-detail-card"><div class="admin-detail-label">Date debut</div><div class="admin-detail-value">' + escapeHtml(idee.brainstorming_date_debut || '-') + '</div></div>'
                + '<div class="admin-detail-card"><div class="admin-detail-label">Idee liee</div><div class="admin-detail-value">' + escapeHtml(idee.titre || '-') + '</div></div>'
                + '<div class="admin-detail-card" style="grid-column:1/-1;"><div class="admin-detail-label">Description brainstorming</div><div class="admin-detail-value">' + escapeHtml(idee.brainstorming_description || '-') + '</div></div>'
                + '</div>';
        })
        .catch(error => {
            content.innerHTML = '<div class="admin-alert admin-alert-danger">' + escapeHtml(error.message) + '</div>';
        });
}
function openEditIdee(id) { const form = document.getElementById('editIdeeAdminForm'); clearFieldErrors(form); document.getElementById('editIdeeErrors').innerHTML = ''; fetchIdee(id).then(idee => { document.getElementById('edit_id').value = idee.id; document.getElementById('edit_titre').value = idee.titre || ''; document.getElementById('edit_categorie').value = idee.categorie || 'General'; document.getElementById('edit_brainstorming_id').value = idee.brainstorming_id || ''; document.getElementById('edit_priorite').value = idee.priorite || 'moyenne'; document.getElementById('edit_statut').value = idee.statut || 'proposee'; document.getElementById('edit_votes').value = idee.votes || 0; document.getElementById('edit_contenu').value = idee.contenu || ''; openDialog('ideeEditDialog'); }).catch(error => showPageAlert(escapeHtml(error.message), 'danger')); }
function submitAdminIdeeForm(form, action, dialogId, errorId) {
    const errorBox = document.getElementById(errorId);
    errorBox.innerHTML = '';
    clearFieldErrors(form);
    const formData = new FormData(form);
    formData.append('action', action);
    fetch('?action=idee_admin', { method: 'POST', body: formData })
        .then(readJsonResponse)
        .then(data => {
            if (!data.success) {
                if (!showFieldErrors(form, data.errors)) {
                    errorBox.innerHTML = renderErrors(data.errors || [data.message || 'Erreur formulaire.']);
                }
                return;
            }
            closeDialog(dialogId);
            showPageAlert(escapeHtml(data.message || 'Operation terminee.'));
            setTimeout(() => location.reload(), 650);
        })
        .catch(() => { errorBox.innerHTML = renderErrors(['Erreur reseau.']); });
}
document.getElementById('addIdeeAdminForm').addEventListener('submit', event => { event.preventDefault(); submitAdminIdeeForm(event.currentTarget, 'add_idee_admin', 'ideeAddDialog', 'addIdeeErrors'); });
document.getElementById('editIdeeAdminForm').addEventListener('submit', event => { event.preventDefault(); submitAdminIdeeForm(event.currentTarget, 'edit_idee_admin', 'ideeEditDialog', 'editIdeeErrors'); });
function scoreIdeeWithAi(id) { const dialog = document.getElementById('aiScoreDialog'); const content = document.getElementById('aiScoreContent'); content.innerHTML = '<p style="margin:0;">Analyse en cours...</p>'; dialog.showModal(); const formData = new FormData(); formData.append('action', 'score_idee_ai'); formData.append('id', id); fetch('?action=idee_admin', { method: 'POST', body: formData }).then(readJsonResponse).then(data => { if (!data.success) { content.innerHTML = '<div class="admin-alert admin-alert-danger">' + escapeHtml(data.message || 'Erreur analyse.') + '</div>'; return; } const s = data.scoring; content.innerHTML = '<div class="admin-dialog-grid">' + scoreBadge(s.clarity, 'Clarte') + scoreBadge(s.innovation, 'Innovation') + scoreBadge(s.feasibility, 'Faisabilite') + scoreBadge(s.positivity, 'Positivite') + scoreBadge(s.confidence, 'Confiance') + '</div><div class="admin-score-card" style="margin-bottom:1rem;"><div class="admin-score-label">Score global</div><div class="admin-score-value">' + escapeHtml(s.global_score) + '/100</div></div><p style="color:var(--text-muted); font-weight:800;">' + escapeHtml(s.source || 'Analyse') + '</p><h4>Resume</h4><p>' + escapeHtml(s.summary) + '</p><h4>Forces</h4>' + renderAiList(s.strengths) + '<h4>Risques</h4>' + renderAiList(s.risks) + '<h4>Recommandation</h4><p>' + escapeHtml(s.recommendation) + '</p>'; }).catch(() => { content.innerHTML = '<div class="admin-alert admin-alert-danger">Erreur reseau pendant l analyse.</div>'; }); }
function renderSuggestionList(items) { if (!Array.isArray(items) || items.length === 0) return '<p style="margin:0; color:var(--text-muted);">Aucune suggestion detaillee.</p>'; return '<ul class="suggestion-list">' + items.map(item => '<li>' + escapeHtml(item) + '</li>').join('') + '</ul>'; }
function renderSuggestions(data) { const s = data.suggestions || {}; document.getElementById('suggestionSource').textContent = data.source || 'Analyse de l idee'; document.getElementById('suggestionContent').innerHTML = '<div class="suggestion-grid">' + '<div class="suggestion-card full"><div class="suggestion-label">Titre ameliore</div><div>' + escapeHtml(s.improved_title || '-') + '</div></div>' + '<div class="suggestion-card full"><div class="suggestion-label">Resume ameliore</div><div>' + escapeHtml(s.improved_summary || '-') + '</div></div>' + '<div class="suggestion-card"><div class="suggestion-label">Utilisateur cible</div><div>' + escapeHtml(s.target_user || '-') + '</div></div>' + '<div class="suggestion-card"><div class="suggestion-label">Probleme</div><div>' + escapeHtml(s.problem || '-') + '</div></div>' + '<div class="suggestion-card full"><div class="suggestion-label">Valeur proposee</div><div>' + escapeHtml(s.value_proposition || '-') + '</div></div>' + '<div class="suggestion-card"><div class="suggestion-label">Prochaines etapes</div>' + renderSuggestionList(s.next_steps) + '</div>' + '<div class="suggestion-card"><div class="suggestion-label">Questions a clarifier</div>' + renderSuggestionList(s.questions) + '</div>' + '</div>'; }
function suggestIdeeImprovements(id) { const dialog = document.getElementById('ideaSuggestionDialog'); const content = document.getElementById('suggestionContent'); document.getElementById('suggestionSource').textContent = 'Gemini API'; content.innerHTML = '<p style="margin:0;">Generation des suggestions...</p>'; dialog.showModal(); const formData = new FormData(); formData.append('action', 'suggest_idee_improvements'); formData.append('id', id); fetch('?action=idee_admin', { method: 'POST', body: formData }).then(readJsonResponse).then(data => { if (!data.success) { document.getElementById('suggestionSource').textContent = 'Configuration API requise'; content.innerHTML = '<div class="admin-alert admin-alert-danger"><strong>Suggestions indisponibles</strong><br>' + escapeHtml(data.message || 'Ajoutez une cle Gemini API pour utiliser cette fonctionnalite.') + '</div>'; return; } renderSuggestions(data); }).catch(() => { document.getElementById('suggestionSource').textContent = 'Erreur reseau'; content.innerHTML = '<div class="admin-alert admin-alert-danger">Erreur reseau pendant la generation.</div>'; }); }
function updateIdeeStatus(id, status) { const formData = new FormData(); formData.append('action', 'update_status'); formData.append('id', id); formData.append('status', status); fetch('?action=idee_admin', { method: 'POST', body: formData }).then(readJsonResponse).then(data => { showPageAlert(escapeHtml(data.message || 'Statut mis a jour.'), data.success ? 'success' : 'danger'); if (data.success) setTimeout(() => location.reload(), 650); }).catch(() => showPageAlert('Erreur reseau pendant la mise a jour.', 'danger')); }
function deleteIdee(id) { if (!confirm('Supprimer cette idee ?')) return; const formData = new FormData(); formData.append('action', 'delete_idee'); formData.append('id', id); fetch('?action=idee_admin', { method: 'POST', body: formData }).then(readJsonResponse).then(data => { showPageAlert(escapeHtml(data.message || 'Operation terminee.'), data.success ? 'success' : 'danger'); if (data.success) { const row = document.getElementById('idee-row-' + id); if (row) row.remove(); } }).catch(() => showPageAlert('Erreur reseau pendant la suppression.', 'danger')); }
</script>
</body>
</html>
