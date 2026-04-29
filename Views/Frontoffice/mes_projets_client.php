<?php
if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 2) {
    header('Location: ?action=login'); exit;
}
require_once __DIR__ . '/../../Controllers/CandidatureController.php';
require_once __DIR__ . '/../../Controllers/ProjectController.php';

$cc = new CandidatureController();
$pc = new ProjectController();

// Projets soumis par ce client
$projets = $cc->getProjetsClient($_SESSION['user_id']);

// Tâches par projet
$tachesParProjet = [];
foreach ($projets as $p) {
    $tachesParProjet[$p['id']] = $cc->getTachesProjet($p['id']);
}

function statutProjetLabel($etat) {
    return ['publie' => 'Publié', 'en_attente_validation' => 'En attente de validation', 'refuse' => 'Refusé'][$etat] ?? $etat;
}
function statutProjetBadge($etat) {
    return ['publie' => 'success', 'en_attente_validation' => 'warning', 'refuse' => 'danger'][$etat] ?? 'secondary';
}
function statutTacheBadge($s) {
    return ['a_faire' => 'secondary', 'en_cours' => 'warning', 'termine' => 'success'][$s] ?? 'secondary';
}
function statutTacheLabel($s) {
    return ['a_faire' => 'À faire', 'en_cours' => 'En cours', 'termine' => 'Terminé'][$s] ?? $s;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Projets — SkillBridge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Views/assets/css/skillbridge.css">
    <style>
        .page-layout  { padding: 3rem 1.5rem 5rem; background: var(--creme); min-height: calc(100vh - 68px - 65px); }
        .page-wrap    { max-width: 980px; margin: 0 auto; }
        .page-heading { font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--charcoal); margin-bottom: 0.4rem; }
        .page-sub     { color: var(--text-light); font-size: 0.92rem; margin-bottom: 2.5rem; }

        /* Projet card */
        .projet-card  { background: var(--white); border: 1px solid var(--beige-border); border-radius: var(--radius-lg); margin-bottom: 2rem; box-shadow: var(--shadow-sm); overflow: hidden; }
        .projet-head  { background: var(--charcoal); padding: 1.1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; }
        .projet-head h3 { font-family: 'Playfair Display', serif; color: var(--white); font-size: 1.15rem; margin: 0; }
        .projet-meta  { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
        .projet-body  { padding: 1.5rem; }

        /* Badge */
        .sbadge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
        .sbadge-success { background: #d1e7dd; color: #0f5132; }
        .sbadge-warning { background: #fff3cd; color: #856404; }
        .sbadge-danger  { background: #f8d7da; color: #842029; }
        .sbadge-secondary { background: #e2e3e5; color: #383d41; }

        /* Tâches */
        .taches-title { font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-light); margin-bottom: 0.75rem; }
        .tache-row    { display: flex; justify-content: space-between; align-items: center; padding: 0.7rem 1rem; border: 1px solid var(--beige-border); border-radius: var(--radius); margin-bottom: 0.5rem; background: var(--creme); gap: 1rem; flex-wrap: wrap; }
        .tache-info   { flex: 1; }
        .tache-titre  { font-weight: 600; color: var(--charcoal); font-size: 0.95rem; }
        .tache-freelancer { font-size: 0.82rem; color: var(--text-light); margin-top: 0.15rem; }
        .tache-desc   { font-size: 0.85rem; color: var(--text-mid); margin-top: 0.2rem; }

        /* Progress bar */
        .progress-wrap { margin-bottom: 1.25rem; }
        .progress-label { display: flex; justify-content: space-between; font-size: 0.82rem; color: var(--text-light); margin-bottom: 0.35rem; }
        .progress-bar-bg { background: var(--beige-border); border-radius: 999px; height: 8px; overflow: hidden; }
        .progress-bar-fill { height: 100%; border-radius: 999px; background: var(--amber); transition: width 0.4s ease; }

        .empty-taches { text-align: center; color: var(--text-light); padding: 1.25rem; font-size: 0.9rem; border: 1px dashed var(--beige-border); border-radius: var(--radius); }
        .empty-state  { text-align: center; color: var(--text-light); padding: 3rem 1.5rem; background: var(--white); border: 1px dashed var(--beige-border); border-radius: var(--radius-lg); }

        /* ── KANBAN ── */
        .kanban-board { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 0.5rem; }
        .kanban-col   { background: #f0f2f5; border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; min-height: 180px; }
        .kanban-col-header { padding: 0.75rem 1rem; color: #fff; font-weight: 700; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.06em; display: flex; justify-content: space-between; align-items: center; }
        .kanban-count { background: rgba(255,255,255,0.3); border-radius: 999px; padding: 0.1rem 0.55rem; font-size: 0.78rem; font-weight: 700; }
        .kanban-col-body { padding: 0.75rem; flex: 1; display: flex; flex-direction: column; gap: 0.6rem; }
        .kanban-empty { text-align: center; color: #aaa; font-size: 0.82rem; padding: 1rem 0; }
        .kanban-card  { background: #fff; border-radius: 8px; padding: 0.85rem 1rem; box-shadow: 0 1px 4px rgba(0,0,0,0.08); border: 1px solid #e8e8e8; transition: transform 0.2s, box-shadow 0.2s; }
        .kanban-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
        .kanban-card-title { font-weight: 600; color: var(--charcoal); font-size: 0.92rem; margin-bottom: 0.35rem; }
        .kanban-card-desc  { font-size: 0.82rem; color: var(--text-light); margin-bottom: 0.6rem; line-height: 1.4; }
        .kanban-card-footer { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid #f0f0f0; }
        .kanban-avatar { width: 26px; height: 26px; border-radius: 50%; background: var(--charcoal); color: var(--amber); font-size: 0.7rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .kanban-name   { font-size: 0.78rem; color: var(--text-light); }
        @media (max-width: 640px) { .kanban-board { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="has-sidebar">
<?php require __DIR__ . '/partials/sidebar.php'; ?>
<nav class="navbar-top">
    <div class="container">
        <button class="sb-toggle" onclick="openSidebar()"><i class="fas fa-bars"></i></button>
        <a href="?action=home" class="logo">
            <img src="<?= BASE_URL ?>/Views/assets/img/logo1.png" alt="SkillBridge" style="height:50px;width:auto;">
        </a>
    </div>
</nav>

<main class="page-layout">
    <div class="page-wrap">
        <h1 class="page-heading">Mes Projets</h1>
        <p class="page-sub">Suivez l'avancement des tâches sur vos projets en cours de traitement.</p>

        <?php if (empty($projets)): ?>
            <div class="empty-state">
                <p style="font-size:1.1rem;margin-bottom:1rem;">Vous n'avez pas encore soumis de projet.</p>
                <a href="?action=projects" class="btn btn-primary">Voir les projets</a>
            </div>
        <?php else: ?>
            <?php foreach ($projets as $p):
                $taches   = $tachesParProjet[$p['id']] ?? [];
                $total    = count($taches);
                $termines = count(array_filter($taches, fn($t) => $t['statut'] === 'termine'));
                $enCours  = count(array_filter($taches, fn($t) => $t['statut'] === 'en_cours'));
                $pct      = $total > 0 ? round(($termines / $total) * 100) : 0;
            ?>
            <div class="projet-card">
                <div class="projet-head">
                    <h3><?= htmlspecialchars($p['titre']) ?></h3>
                    <div class="projet-meta">
                        <span style="color:rgba(255,255,255,0.6);font-size:0.85rem;">
                            <?= number_format((float)$p['budget'], 2, ',', ' ') ?> TND
                        </span>
                        <span class="sbadge sbadge-<?= statutProjetBadge($p['etat']) ?>">
                            <?= statutProjetLabel($p['etat']) ?>
                        </span>
                    </div>
                </div>

                <div class="projet-body">

                    <?php if ($total > 0): ?>
                    <!-- Barre de progression -->
                    <div class="progress-wrap">
                        <div class="progress-label">
                            <span><?= $termines ?>/<?= $total ?> tâches terminées</span>
                            <span><?= $pct ?>%</span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width:<?= $pct ?>%;"></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Liste des tâches -->
                    <p class="taches-title">
                        Tâches
                        <?php if ($total > 0): ?>
                            — <span style="color:var(--amber);"><?= $enCours ?> en cours</span>
                            · <span style="color:#0f5132;"><?= $termines ?> terminées</span>
                        <?php endif; ?>
                    </p>

                    <?php if (empty($taches)): ?>
                        <div class="empty-taches">Aucune tâche assignée pour l'instant.</div>
                    <?php else:
                        $cols = [
                            'a_faire' => ['label'=>'À faire',  'color'=>'#e74c3c', 'tasks'=>[]],
                            'en_cours'=> ['label'=>'En cours', 'color'=>'#f39c12', 'tasks'=>[]],
                            'termine' => ['label'=>'Terminé',  'color'=>'#27ae60', 'tasks'=>[]],
                        ];
                        foreach ($taches as $t) {
                            $cols[$t['statut']]['tasks'][] = $t;
                        }
                    ?>
                    <div class="kanban-board">
                        <?php foreach ($cols as $key => $col): ?>
                        <div class="kanban-col">
                            <div class="kanban-col-header" style="background:<?= $col['color'] ?>;">
                                <?= $col['label'] ?>
                                <span class="kanban-count"><?= count($col['tasks']) ?></span>
                            </div>
                            <div class="kanban-col-body">
                                <?php if (empty($col['tasks'])): ?>
                                    <div class="kanban-empty">Aucune tâche</div>
                                <?php else: ?>
                                    <?php foreach ($col['tasks'] as $t): ?>
                                    <div class="kanban-card">
                                        <div class="kanban-card-title"><?= htmlspecialchars($t['titre']) ?></div>
                                        <?php if (!empty($t['description'])): ?>
                                            <div class="kanban-card-desc"><?= htmlspecialchars($t['description']) ?></div>
                                        <?php endif; ?>
                                        <div class="kanban-card-footer">
                                            <span class="kanban-avatar"><?= strtoupper(mb_substr($t['prenom_freelancer'],0,1).mb_substr($t['nom_freelancer'],0,1)) ?></span>
                                            <span class="kanban-name"><?= htmlspecialchars($t['prenom_freelancer'].' '.$t['nom_freelancer']) ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<footer class="footer">
    <p>&copy; 2026 SkillBridge. Tous droits réservés.</p>
</footer>
</body>
</html>
