<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header('Location: ?action=login'); exit;
}
require_once __DIR__ . '/../../Controllers/CandidatureController.php';

$cc = new CandidatureController();

// ── AJAX ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'add_tache') {
        $titre       = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $id_projet   = (int)($_POST['id_projet'] ?? 0);
        $statut      = $_POST['statut'] ?? 'a_faire';

        if (!$titre) { echo json_encode(['success'=>false,'message'=>'Le titre est obligatoire.']); exit; }

        $tache = new Tache($id_projet, $_SESSION['user_id'], $titre, $description, $statut);
        echo json_encode($cc->ajouterTache($tache));
        exit;
    }

    if ($action === 'delete_tache') {
        $id = (int)($_POST['id'] ?? 0);
        $ok = $cc->supprimerTache($id, $_SESSION['user_id']);
        echo json_encode(['success' => $ok]);
        exit;
    }

    echo json_encode(['success'=>false,'message'=>'Action inconnue.']);
    exit;
}

$candidatures   = $cc->getMesCandidatures($_SESSION['user_id']);
$projetsAcceptes = $cc->getProjetsAcceptes($_SESSION['user_id']);

// Tâches par projet
$tachesParProjet = [];
foreach ($projetsAcceptes as $p) {
    $tachesParProjet[$p['id']] = $cc->getTaches($p['id'], $_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Projets — SkillBridge</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Views/assets/css/skillbridge.css">
    <style>
        .page-layout { padding: 3rem 1.5rem 5rem; background: var(--creme); min-height: calc(100vh - 68px - 65px); }
        .page-container { max-width: 980px; margin: 0 auto; }
        .section-title { font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--charcoal); margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--amber); display: inline-block; }
        .cand-table { width: 100%; border-collapse: collapse; background: var(--white); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 3rem; }
        .cand-table th { background: var(--charcoal); color: var(--white); padding: 0.85rem 1rem; text-align: left; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .cand-table td { padding: 0.85rem 1rem; border-bottom: 1px solid var(--beige-border); font-size: 0.93rem; color: var(--text-mid); }
        .cand-table tr:last-child td { border-bottom: none; }
        .badge-statut { display: inline-block; padding: 0.25rem 0.7rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .badge-en_attente { background: #fff3cd; color: #856404; }
        .badge-accepte    { background: #d1e7dd; color: #0f5132; }
        .badge-refuse     { background: #f8d7da; color: #842029; }
        .badge-a_faire    { background: #e2e3e5; color: #383d41; }
        .badge-en_cours   { background: #fff3cd; color: #856404; }
        .badge-termine    { background: #d1e7dd; color: #0f5132; }
        .projet-block { background: var(--white); border: 1px solid var(--beige-border); border-radius: var(--radius-lg); margin-bottom: 2rem; box-shadow: var(--shadow-sm); overflow: hidden; }
        .projet-block-header { background: var(--charcoal); color: var(--white); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .projet-block-header h3 { font-family: 'Playfair Display', serif; font-size: 1.1rem; margin: 0; }
        .projet-block-body { padding: 1.5rem; }
        .tache-list { list-style: none; padding: 0; margin: 0 0 1.5rem; }
        .tache-item { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border: 1px solid var(--beige-border); border-radius: var(--radius); margin-bottom: 0.5rem; background: var(--creme); }
        .tache-item-info { flex: 1; }
        .tache-item-title { font-weight: 600; color: var(--charcoal); font-size: 0.95rem; }
        .tache-item-desc { color: var(--text-light); font-size: 0.85rem; margin-top: 0.2rem; }
        .btn-del { background: none; border: none; color: #dc3545; cursor: pointer; font-size: 1rem; padding: 0.3rem 0.5rem; border-radius: 4px; transition: background 0.2s; }
        .btn-del:hover { background: #f8d7da; }
        .add-tache-form { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        .add-tache-form .full { grid-column: 1 / -1; }
        .add-tache-form input, .add-tache-form textarea, .add-tache-form select { width: 100%; padding: 0.65rem 0.9rem; border: 1px solid var(--beige-border); border-radius: var(--radius); font-family: 'DM Sans', sans-serif; font-size: 0.9rem; }
        .add-tache-form input:focus, .add-tache-form textarea:focus, .add-tache-form select:focus { outline: none; border-color: var(--amber); box-shadow: 0 0 0 3px var(--amber-glow); }
        .field-err { color: #dc3545; font-size: 0.8rem; margin-top: 0.2rem; display: none; }
        .empty-box { text-align: center; color: var(--text-light); padding: 1.5rem; font-size: 0.9rem; }

        /* ── KANBAN ── */
        .kanban-board { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
        .kanban-col   { background: #f0f2f5; border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; min-height: 120px; }
        .kanban-col-header { padding: 0.75rem 1rem; color: #fff; font-weight: 700; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.06em; display: flex; justify-content: space-between; align-items: center; }
        .kanban-count { background: rgba(255,255,255,0.3); border-radius: 999px; padding: 0.1rem 0.55rem; font-size: 0.78rem; font-weight: 700; }
        .kanban-col-body { padding: 0.75rem; flex: 1; display: flex; flex-direction: column; gap: 0.6rem; }
        .kanban-empty { text-align: center; color: #aaa; font-size: 0.82rem; padding: 1rem 0; }
        .kanban-card  { background: #fff; border-radius: 8px; padding: 0.85rem 1rem; box-shadow: 0 1px 4px rgba(0,0,0,0.08); border: 1px solid #e8e8e8; transition: transform 0.2s, box-shadow 0.2s; }
        .kanban-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
        .kanban-card-title { font-weight: 600; color: var(--charcoal); font-size: 0.92rem; margin-bottom: 0.3rem; }
        .kanban-card-desc  { font-size: 0.82rem; color: var(--text-light); margin-bottom: 0.5rem; line-height: 1.4; }
        .kanban-card-actions { margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid #f0f0f0; text-align: right; }
        .btn-del-k { background: none; border: none; color: #dc3545; cursor: pointer; font-size: 0.78rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 4px; transition: background 0.2s; }
        .btn-del-k:hover { background: #f8d7da; }
        @media (max-width: 640px) { .kanban-board { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="has-sidebar">
<?php require __DIR__ . '/partials/sidebar.php'; ?>
<nav class="navbar-top">
    <div class="container">
        <button class="sb-toggle" onclick="openSidebar()"><i class="fas fa-bars"></i></button>
        <a href="?action=home" class="logo"><img src="<?= BASE_URL ?>/Views/assets/img/logo1.png" alt="SkillBridge" style="height:50px;width:auto;"></a>
    </div>
</nav>

<main class="page-layout">
    <div class="page-container">

        <!-- Mes candidatures -->
        <h2 class="section-title">Mes Candidatures</h2>
        <?php if (empty($candidatures)): ?>
            <div class="empty-box" style="background:var(--white);border:1px dashed var(--beige-border);border-radius:12px;margin-bottom:3rem;">
                Aucune candidature pour l'instant. Parcourez les <a href="?action=projects">projets disponibles</a>.
            </div>
        <?php else: ?>
        <table class="cand-table">
            <thead><tr><th>Projet</th><th>Date</th><th>Statut</th></tr></thead>
            <tbody>
            <?php foreach ($candidatures as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c->getTitreProjet()) ?></td>
                    <td><?= date('d/m/Y', strtotime($c->getCreatedAt())) ?></td>
                    <td><span class="badge-statut badge-<?= $c->getStatut() ?>"><?= ucfirst(str_replace('_',' ',$c->getStatut())) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- Projets acceptés + tâches -->
        <h2 class="section-title">Mes Projets Acceptés</h2>
        <?php if (empty($projetsAcceptes)): ?>
            <div class="empty-box" style="background:var(--white);border:1px dashed var(--beige-border);border-radius:12px;">
                Aucun projet accepté pour l'instant.
            </div>
        <?php else: ?>
            <?php foreach ($projetsAcceptes as $p): ?>
            <div class="projet-block">
                <div class="projet-block-header">
                    <h3><?= htmlspecialchars($p['titre']) ?></h3>
                    <span style="font-size:0.8rem;opacity:0.7;"><?= number_format((float)$p['budget'],2,',',' ') ?> TND</span>
                </div>
                <div class="projet-block-body">

                    <!-- Liste des tâches -->
                    <ul class="tache-list" id="taches-<?= $p['id'] ?>">
                    <?php if (empty($tachesParProjet[$p['id']])): ?>
                        <li class="empty-box">Aucune tâche ajoutée.</li>
                    <?php else:
                        $cols = [
                            'a_faire'  => ['label'=>'À faire',  'color'=>'#e74c3c', 'items'=>[]],
                            'en_cours' => ['label'=>'En cours', 'color'=>'#f39c12', 'items'=>[]],
                            'termine'  => ['label'=>'Terminé',  'color'=>'#27ae60', 'items'=>[]],
                        ];
                        foreach ($tachesParProjet[$p['id']] as $t) {
                            $cols[$t->getStatut()]['items'][] = $t;
                        }
                    ?>
                    <li style="list-style:none;padding:0;">
                        <div class="kanban-board">
                            <?php foreach ($cols as $key => $col): ?>
                            <div class="kanban-col">
                                <div class="kanban-col-header" style="background:<?= $col['color'] ?>;">
                                    <?= $col['label'] ?>
                                    <span class="kanban-count"><?= count($col['items']) ?></span>
                                </div>
                                <div class="kanban-col-body">
                                    <?php if (empty($col['items'])): ?>
                                        <div class="kanban-empty">Aucune tâche</div>
                                    <?php else: ?>
                                        <?php foreach ($col['items'] as $t): ?>
                                        <div class="kanban-card" id="tache-<?= $t->getId() ?>">
                                            <div class="kanban-card-title"><?= htmlspecialchars($t->getTitre()) ?></div>
                                            <?php if ($t->getDescription()): ?>
                                                <div class="kanban-card-desc"><?= htmlspecialchars($t->getDescription()) ?></div>
                                            <?php endif; ?>
                                            <div class="kanban-card-actions">
                                                <button class="btn-del-k" onclick="deleteTache(<?= $t->getId() ?>, <?= $p['id'] ?>)" title="Supprimer">✕ Supprimer</button>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <?php endif; ?>
                    </ul>

                    <!-- Formulaire ajout tâche -->
                    <form class="add-tache-form" id="form-tache-<?= $p['id'] ?>" novalidate>
                        <input type="hidden" name="id_projet" value="<?= $p['id'] ?>">
                        <div>
                            <input type="text" name="titre" placeholder="Titre de la tâche *" id="t_titre_<?= $p['id'] ?>">
                            <span class="field-err" id="err_titre_<?= $p['id'] ?>">Titre obligatoire.</span>
                        </div>
                        <div>
                            <select name="statut">
                                <option value="a_faire">À faire</option>
                                <option value="en_cours">En cours</option>
                                <option value="termine">Terminé</option>
                            </select>
                        </div>
                        <div class="full">
                            <textarea name="description" rows="2" placeholder="Description (optionnel)"></textarea>
                        </div>
                        <div class="full" style="text-align:right;">
                            <button type="submit" class="btn btn-primary" style="padding:0.6rem 1.4rem;font-size:0.88rem;">+ Ajouter la tâche</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</main>

<footer class="footer"><p>&copy; 2026 SkillBridge. Tous droits réservés.</p></footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>.swal2-popup{font-family:"Open Sans",sans-serif;}</style>
<script>
document.querySelectorAll('[id^="form-tache-"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const idProjet = this.querySelector('[name="id_projet"]').value;
        const titreEl  = document.getElementById('t_titre_' + idProjet);
        const errEl    = document.getElementById('err_titre_' + idProjet);

        titreEl.style.borderColor = '';
        errEl.style.display = 'none';

        if (titreEl.value.trim() === '') {
            titreEl.style.borderColor = '#dc3545';
            errEl.style.display = 'block';
            return;
        }

        const fd = new FormData(this);
        fd.append('action', 'add_tache');

        fetch('?action=mes_projets', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Tâche ajoutée !', timer: 1200, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Erreur', text: data.message });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Erreur réseau' }));
    });
});

function deleteTache(id, idProjet) {
    Swal.fire({
        title: 'Supprimer cette tâche ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then(r => {
        if (!r.isConfirmed) return;
        const fd = new FormData();
        fd.append('action', 'delete_tache');
        fd.append('id', id);
        fetch('?action=mes_projets', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('tache-' + id).remove();
                Swal.fire({ icon: 'success', title: 'Supprimée', timer: 1000, showConfirmButton: false });
            }
        });
    });
}
</script>
</body>
</html>
