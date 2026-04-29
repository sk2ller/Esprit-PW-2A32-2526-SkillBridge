<?php if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 1) { header('Location: ?action=login'); exit; } ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Candidatures - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-admin.css">
    <style>
        .admin-dialog { width: min(880px, calc(100vw - 2rem)); border: 1px solid rgba(223, 209, 189, .95); border-radius: 18px; padding: 0; background: #fffaf4; color: var(--text); box-shadow: 0 26px 70px rgba(31, 31, 35, .22); }
        .admin-dialog::backdrop { background: rgba(22, 20, 18, .54); }
        .admin-dialog-head, .admin-dialog-body, .admin-dialog-foot { padding: 1.2rem 1.4rem; }
        .admin-dialog-head { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(223, 209, 189, .9); }
        .admin-dialog-foot { display: flex; justify-content: flex-end; gap: .7rem; border-top: 1px solid rgba(223, 209, 189, .9); }
        .admin-dialog-title { font-weight: 800; font-size: 1.1rem; }
        .admin-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        .admin-field { display: flex; flex-direction: column; gap: .35rem; }
        .admin-field.full { grid-column: 1 / -1; }
        .admin-field label { font-weight: 800; color: var(--text); font-size: .86rem; }
        .admin-field input, .admin-field select, .admin-field textarea { border: 1px solid var(--border); border-radius: 14px; padding: .8rem .9rem; background: #fff; color: var(--text); font: inherit; }
        .admin-field textarea { min-height: 130px; resize: vertical; }
        @media (max-width: 900px) { .admin-form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="skillbridge-admin">
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="admin-main">
    <div class="admin-topbar">
        <div><div class="topbar-title">Candidatures</div><div class="topbar-bread"><a href="?action=statistics">Dashboard</a> / Candidatures freelancers</div></div>
        <div class="topbar-actions">
            <a class="topbar-btn topbar-btn-outline" href="?action=job_offers_admin">Offres Job</a>
            <button class="topbar-btn topbar-btn-primary" type="button" onclick="openApplicationDialog()">Ajouter candidature</button>
        </div>
    </div>
    <div id="adminApplicationAlert"></div>
    <div class="stats-grid">
        <div class="stat-widget"><div class="sw-icon"><i class="fas fa-file-signature"></i></div><div class="sw-value"><?= count($applications) ?></div><div class="sw-label">Liste chargee</div></div>
        <div class="stat-widget orange"><div class="sw-icon"><i class="fas fa-hourglass-half"></i></div><div class="sw-value"><?= count(array_filter($applications, fn($a) => $a['statut'] === 'en_attente')) ?></div><div class="sw-label">En attente</div></div>
        <div class="stat-widget green"><div class="sw-icon"><i class="fas fa-check-circle"></i></div><div class="sw-value"><?= count(array_filter($applications, fn($a) => $a['statut'] === 'acceptee')) ?></div><div class="sw-label">Acceptees</div></div>
        <div class="stat-widget blue"><div class="sw-icon"><i class="fas fa-link"></i></div><div class="sw-value"><?= count(array_filter($applications, fn($a) => !empty($a['cv_url']) || !empty($a['portfolio_url']))) ?></div><div class="sw-label">Avec pieces</div></div>
    </div>
    <div class="admin-table-wrap">
        <div class="admin-table-header"><div class="admin-table-title">Liste des candidatures</div></div>
        <table class="admin-table">
            <thead><tr><th>Offre</th><th>Client</th><th>Freelancer</th><th>Budget propose</th><th>Disponibilite</th><th>Documents</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($applications)): ?><tr><td colspan="8">Aucune candidature trouvee.</td></tr><?php endif; ?>
            <?php foreach ($applications as $application): ?>
                <tr>
                    <td><div class="table-service-name"><?= htmlspecialchars($application['titre']) ?></div></td>
                    <td><?= htmlspecialchars($application['client_prenom'] . ' ' . $application['client_nom']) ?></td>
                    <td><?= htmlspecialchars($application['freelancer_prenom'] . ' ' . $application['freelancer_nom']) ?></td>
                    <td><?= number_format((float)$application['budget_propose'], 2) ?> DT</td>
                    <td><?= (int)$application['disponibilite_jours'] ?> jours</td>
                    <td>
                        <?php if (!empty($application['cv_url'])): ?><div><a href="<?= htmlspecialchars($application['cv_url']) ?>" target="_blank" rel="noopener">CV</a></div><?php endif; ?>
                        <?php if (!empty($application['portfolio_url'])): ?><div><a href="<?= htmlspecialchars($application['portfolio_url']) ?>" target="_blank" rel="noopener">Portfolio</a></div><?php endif; ?>
                        <?php if (empty($application['cv_url']) && empty($application['portfolio_url'])): ?>-<?php endif; ?>
                    </td>
                    <td><span class="badge <?= $application['statut'] === 'acceptee' ? 'badge-actif' : ($application['statut'] === 'refusee' ? 'badge-suspendu' : 'badge-pending') ?>"><?= htmlspecialchars($application['statut']) ?></span></td>
                    <td><div class="admin-stack-actions"><span class="admin-btn admin-btn-outline admin-btn-sm">Decision client</span></div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</div>

<dialog class="admin-dialog" id="applicationAddDialog">
    <form id="addApplicationAdminForm">
        <div class="admin-dialog-head"><div class="admin-dialog-title">Ajouter une candidature</div><button class="admin-btn admin-btn-outline admin-btn-sm" type="button" onclick="closeApplicationDialog()">Fermer</button></div>
        <div class="admin-dialog-body">
            <div id="applicationFormErrors"></div>
            <div class="admin-form-grid">
                <div class="admin-field"><label>Offre</label><select name="id_offre" required><?php foreach ($offers as $offer): ?><option value="<?= (int)$offer['id_offre'] ?>"><?= htmlspecialchars($offer['titre']) ?></option><?php endforeach; ?></select></div>
                <div class="admin-field"><label>Freelancer</label><select name="id_freelancer" required><?php foreach ($freelancers as $freelancer): ?><option value="<?= (int)$freelancer['id'] ?>"><?= htmlspecialchars($freelancer['prenom'] . ' ' . $freelancer['nom'] . ' - ' . $freelancer['email']) ?></option><?php endforeach; ?></select></div>
                <div class="admin-field"><label>Budget propose</label><input name="budget_propose" type="number" step="0.01" min="1" required></div>
                <div class="admin-field"><label>Disponibilite (jours)</label><input name="disponibilite_jours" type="number" min="1" required></div>
                <div class="admin-field"><label>Mode</label><select name="execution_mode" id="application_execution_mode"><option value="full_project">Projet complet</option><option value="milestone">Milestones</option></select></div>
                <div class="admin-field"><label>CV URL</label><input name="cv_url" type="url"></div>
                <div class="admin-field full"><label>Portfolio URL</label><input name="portfolio_url" type="url"></div>
                <div class="admin-field full"><label>Message</label><textarea name="message" required></textarea></div>
                <div class="admin-field full"><label>Plan milestones</label><textarea name="milestone_plan"></textarea></div>
            </div>
        </div>
        <div class="admin-dialog-foot"><button class="admin-btn admin-btn-outline" type="button" onclick="closeApplicationDialog()">Annuler</button><button class="admin-btn admin-btn-primary" type="submit">Ajouter</button></div>
    </form>
</dialog>

<script>
function escapeHtml(value) { return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;'); }
function renderErrors(errors) { const list = Array.isArray(errors) ? errors : [errors || 'Erreur formulaire.']; return '<div class="admin-alert admin-alert-danger"><strong>Erreur</strong><ul style="margin:.45rem 0 0; padding-left:1.1rem;">' + list.map(error => '<li>' + escapeHtml(error) + '</li>').join('') + '</ul></div>'; }
function showApplicationAlert(message, type = 'success') { const alert = document.getElementById('adminApplicationAlert'); const klass = type === 'success' ? 'admin-alert-success' : 'admin-alert-danger'; alert.innerHTML = '<div class="admin-alert ' + klass + '">' + escapeHtml(message) + '</div>'; window.scrollTo({ top: 0, behavior: 'smooth' }); }
function openApplicationDialog() { document.getElementById('addApplicationAdminForm').reset(); document.getElementById('applicationFormErrors').innerHTML = ''; document.getElementById('applicationAddDialog').showModal(); }
function closeApplicationDialog() { document.getElementById('applicationAddDialog').close(); }
document.getElementById('addApplicationAdminForm').addEventListener('submit', event => {
    event.preventDefault();
    const errorBox = document.getElementById('applicationFormErrors');
    errorBox.innerHTML = '';
    const formData = new FormData(event.currentTarget);
    formData.append('action', 'add_application_admin');
    fetch('?action=job_applications_admin', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                errorBox.innerHTML = renderErrors(data.errors || data.message);
                return;
            }
            closeApplicationDialog();
            showApplicationAlert(data.message || 'Candidature ajoutee avec succes.');
            setTimeout(() => location.reload(), 650);
        })
        .catch(() => { errorBox.innerHTML = renderErrors('Erreur reseau.'); });
});
</script>
</body>
</html>
