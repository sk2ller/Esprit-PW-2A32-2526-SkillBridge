<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?action=login');
    exit;
}
require_once __DIR__ . '/../../Controllers/BrainstormingController.php';
require_once __DIR__ . '/../../Models/Idee.php';

$brainstormController = new BrainstormingController();
$isAdmin = (int) ($_SESSION['user_role'] ?? 0) === 1;
$brainstormings = $brainstormController->listVisibleForUser($_SESSION['user_id'], $isAdmin);
$ideeModel = new Idee();
$ideesByBrainstorming = [];
foreach ($brainstormings as $brainstormingItem) {
    $ideesByBrainstorming[(int) $brainstormingItem['id']] = $ideeModel->getAllByBrainstorming((int) $brainstormingItem['id']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Brainstormings - SkillBridge</title>
    <link rel="stylesheet" href="Views/assets/css/skillbridge.css">
    <link rel="stylesheet" href="Views/assets/css/enhanced-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
    <style id="brainstorming-dashboard-fix">
        .front-page.brainstorming-dashboard {
            max-width: 1440px;
            width: 100%;
            padding: 1.6rem 2rem 2.4rem;
        }

        .brainstorming-dashboard > .dashboard-shell {
            display: grid !important;
            grid-template-columns: 250px minmax(0, 1fr) !important;
            gap: 1.5rem;
            align-items: start;
            width: 100%;
        }

        .brainstorming-sidebar {
            grid-column: 1;
            width: 250px;
            position: sticky;
            top: calc(var(--nav-height) + 1rem);
            align-self: start;
            background: #1f1f23;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 18px;
            box-shadow: 0 14px 34px rgba(31, 31, 35, .14);
            overflow: hidden;
        }

        .brainstorming-sidebar-head {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .brainstorming-sidebar-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: rgba(224, 112, 32, .18);
            color: #f08a3b;
        }

        .brainstorming-sidebar-title {
            color: #fff;
            font-weight: 800;
            line-height: 1.1;
        }

        .brainstorming-sidebar-subtitle {
            color: rgba(255, 255, 255, .58);
            font-size: .82rem;
            margin-top: .18rem;
        }

        .brainstorming-nav {
            display: grid;
            gap: .35rem;
            padding: .75rem;
        }

        .brainstorming-nav-link {
            min-height: 44px;
            display: grid;
            grid-template-columns: 22px minmax(0, 1fr);
            align-items: center;
            gap: .65rem;
            padding: .72rem .8rem;
            border-radius: 12px;
            color: rgba(255, 255, 255, .72);
            text-decoration: none;
            font-weight: 700;
            line-height: 1.2;
        }

        .brainstorming-nav-link:hover,
        .brainstorming-nav-link.active {
            background: #fff8ef;
            color: #1f1f23;
        }

        .brainstorming-nav-link i {
            color: inherit;
            text-align: center;
        }

        .brainstorming-dashboard .feature-main {
            grid-column: 2;
            min-width: 0;
            width: 100%;
        }

        .brainstorming-dashboard .page-shell {
            min-height: auto;
            width: 100%;
        }

        .brainstorming-dashboard .page-hero {
            border-radius: 22px;
            padding: 2rem;
            margin: 0 0 1.35rem;
            overflow: hidden;
        }

        .brainstorming-dashboard .page-section {
            padding: 0;
        }

        .brainstorming-dashboard .container {
            max-width: none !important;
            width: 100%;
            padding-left: 0;
            padding-right: 0;
        }

        .brainstorming-dashboard .section-toolbar {
            gap: 1rem;
            flex-wrap: wrap;
        }

        .brainstorming-dashboard .front-table-wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
        }

        .brainstorming-dashboard .front-table {
            width: 100%;
            min-width: 760px;
        }

        .brainstorming-dashboard .idea-grid {
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }

        .brainstorming-dashboard .idea-card,
        .brainstorming-dashboard .empty-state,
        .brainstorming-dashboard .front-table-wrap {
            min-width: 0;
        }


        .brainstorming-dashboard .idea-eye-btn {
            width: 42px;
            height: 42px;
            display: inline-grid;
            place-items: center;
            padding: 0;
            border-radius: 12px;
        }

        .idea-dialog {
            width: min(920px, calc(100vw - 2rem));`r`n            max-height: min(86vh, 780px);`r`n            margin: auto;
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 0;
            background: #fffaf4;
            color: var(--text);
            box-shadow: 0 24px 70px rgba(31, 31, 35, .22);
            overflow: hidden;
        }

        .idea-dialog::backdrop {
            background: rgba(31, 31, 35, .45);
        }

        .idea-dialog-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.35rem 1.6rem;
            border-bottom: 1px solid var(--border);
            background: #fff8ef;
        }

        .idea-dialog-title {
            font-weight: 800;
            font-size: 1.25rem;
            line-height: 1.25;
        }

        .idea-dialog-subtitle {
            color: var(--text-muted);
            font-size: .86rem;
            margin-top: .25rem;
        }

        .idea-dialog-close {
            border: 0;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: rgba(31, 31, 35, .08);
            color: var(--text);
            cursor: pointer;
        }

        .idea-dialog-body {
            padding: 1.35rem;`r`n            overflow: auto;
            max-height: calc(min(86vh, 780px) - 88px);
        }

        .idea-popup-list {
            display: grid;
            gap: .8rem;
        }

        .idea-popup-card {
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #fff;
            padding: .95rem;
        }

        .idea-popup-top {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            align-items: flex-start;
            margin-bottom: .55rem;
        }

        .idea-popup-title {
            font-weight: 800;
            line-height: 1.25;
        }

        .idea-popup-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-top: .65rem;
            color: var(--text-muted);
            font-size: .82rem;
        }
        @media (max-width: 980px) {
            .front-page.brainstorming-dashboard {
                padding: 1.25rem;
            }

            .brainstorming-dashboard > .dashboard-shell {
                grid-template-columns: 1fr !important;
            }

            .brainstorming-sidebar,
            .brainstorming-dashboard .feature-main {
                grid-column: 1;
                width: 100%;
            }

            .brainstorming-sidebar {
                position: static;
            }

            .brainstorming-dashboard .page-hero {
                padding: 1.5rem;
            }

            .brainstorming-dashboard .front-table {
                min-width: 680px;
            }
        }
    </style>
</head>
<body class="skillbridge-front">
<?php include __DIR__ . '/partials/front_navbar.php'; ?>
<div class="front-page brainstorming-dashboard"><div class="dashboard-shell brainstorming-layout">
    <?php include __DIR__ . '/partials/brainstorming_sidebar.php'; ?>
    <main class="page-shell feature-main">
    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">Frontoffice</span>
            <h1>Liste des brainstormings</h1>
            <p>Cette vue affiche uniquement les brainstormings visibles pour votre profil, tout en gardant un acces direct a leurs idees liees.</p>
        </div>
    </section>

    <section class="page-section">
        <div class="container" style="max-width: 1120px;">
            <div class="section-toolbar">
                <div>
                    <h2>Brainstormings disponibles</h2>
                    <p>Les admins voient l ensemble du module. Les autres utilisateurs voient les brainstormings valides ainsi que leurs propres propositions.</p>
                </div>
                <div class="action-row">
                    <a href="?action=brainstorming_add" class="btn btn-primary">Nouveau brainstorming</a>
                    <a href="?action=all_idees" class="btn btn-secondary page-btn-secondary">Toutes les idees</a>
                </div>
            </div>

            <?php if (empty($brainstormings)): ?>
                <div class="empty-state">
                    <h3>Aucun brainstorming trouve</h3>
                    <p>Commencez par ajouter un brainstorming, puis rattachez-y des idees.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive front-table-wrap">
                    <table class="table table-striped front-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Titre</th>
                                <th>Description</th>
                                <th>Date debut</th>
                                <th>Statut</th>
                                <th>Propose par</th>
                                <th>Idees</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($brainstormings as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['id']) ?></td>
                                    <td><?= htmlspecialchars($item['titre']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($item['description'])) ?></td>
                                    <td><?= htmlspecialchars($item['date_debut']) ?></td>
                                    <td>
                                        <?php if ((int) $item['accepted'] === 1): ?>
                                            <span class="pill pill-success">Accepte</span>
                                        <?php else: ?>
                                            <span class="pill pill-warning">En attente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars(trim(($item['user_prenom'] ?? 'Utilisateur') . ' ' . ($item['user_nom'] ?? ''))) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-secondary page-btn-secondary idea-eye-btn" title="Voir les idees" onclick="openIdeaDialog(<?= (int) $item['id'] ?>)"><i class="fas fa-eye"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
</div></div>

<?php foreach ($brainstormings as $item): ?>
    <?php $dialogIdees = $ideesByBrainstorming[(int) $item['id']] ?? []; ?>
    <dialog class="idea-dialog" id="ideas-dialog-<?= (int) $item['id'] ?>">
        <div class="idea-dialog-head">
            <div>
                <div class="idea-dialog-title"><?= htmlspecialchars($item['titre']) ?></div>
                <div class="idea-dialog-subtitle"><?= count($dialogIdees) ?> idee<?= count($dialogIdees) > 1 ? 's' : '' ?> liee<?= count($dialogIdees) > 1 ? 's' : '' ?></div>
            </div>
            <button type="button" class="idea-dialog-close" onclick="closeIdeaDialog(<?= (int) $item['id'] ?>)"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="idea-dialog-body">
            <?php if (empty($dialogIdees)): ?>
                <div class="empty-state" style="padding:1.5rem; margin:0;">
                    <h3>Aucune idee</h3>
                    <p>Il n y a pas encore d idee pour ce brainstorming.</p>
                    <a class="btn btn-primary" href="?action=add_idee&brainstorming_id=<?= (int) $item['id'] ?>">Ajouter idee</a>
                </div>
            <?php else: ?>
                <div class="idea-popup-list">
                    <?php foreach ($dialogIdees as $idee): ?>
                        <article class="idea-popup-card">
                            <div class="idea-popup-top">
                                <div>
                                    <div class="idea-popup-title"><?= htmlspecialchars($idee['titre']) ?></div>
                                    <span class="idea-tag"><?= htmlspecialchars($idee['categorie']) ?></span>
                                </div>
                                <span class="idea-status status-<?= htmlspecialchars($idee['statut']) ?>"><?= htmlspecialchars(str_replace('_', ' ', $idee['statut'])) ?></span>
                            </div>
                            <p><?= nl2br(htmlspecialchars($idee['contenu'])) ?></p>
                            <div class="idea-popup-meta">
                                <span>Priorite : <?= htmlspecialchars($idee['priorite']) ?></span>
                                <span>Votes : <?= (int) $idee['votes'] ?></span>
                                <span>Auteur : <?= htmlspecialchars(trim(($idee['user_prenom'] ?? '') . ' ' . ($idee['user_nom'] ?? ''))) ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </dialog>
<?php endforeach; ?>

<script>
function openIdeaDialog(id) {
    const dialog = document.getElementById('ideas-dialog-' + id);
    if (!dialog) return;
    if (typeof dialog.showModal === 'function') {
        dialog.showModal();
    } else {
        dialog.setAttribute('open', 'open');
    }
}

function closeIdeaDialog(id) {
    const dialog = document.getElementById('ideas-dialog-' + id);
    if (!dialog) return;
    dialog.close ? dialog.close() : dialog.removeAttribute('open');
}

document.addEventListener('click', function (event) {
    if (event.target.classList && event.target.classList.contains('idea-dialog')) {
        event.target.close();
    }
});
</script>
<footer class="footer">
    <p>&copy; 2026 SkillBridge</p>
</footer>
</body>
</html>






