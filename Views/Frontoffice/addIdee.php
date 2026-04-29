<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?action=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une idee - SkillBridge</title>
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
            <h1>Soumettre une idee</h1>
            <p>Ajoutez une proposition claire, rattachee a un brainstorming valide, avec un parcours adapte a votre role.</p>
        </div>
    </section>

    <section class="page-section">
        <div class="container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                    <?php if (!empty($validationErrors)): ?>
                        <ul>
                            <?php foreach ($validationErrors as $message): ?>
                                <li><?= htmlspecialchars($message) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <div class="form-card">
                <form method="post" action="index.php?action=add_idee">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="titre">Titre</label>
                            <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="categorie">Categorie</label>
                            <input type="text" id="categorie" name="categorie" value="<?= htmlspecialchars($_POST['categorie'] ?? '') ?>" placeholder="Ex : UX, produit, IA">
                        </div>
                        <div class="form-group">
                            <label for="priorite">Priorite</label>
                            <select id="priorite" name="priorite">
                                <option value="faible" <?= ($_POST['priorite'] ?? '') === 'faible' ? 'selected' : '' ?>>Faible</option>
                                <option value="moyenne" <?= !isset($_POST['priorite']) || ($_POST['priorite'] ?? '') === 'moyenne' ? 'selected' : '' ?>>Moyenne</option>
                                <option value="haute" <?= ($_POST['priorite'] ?? '') === 'haute' ? 'selected' : '' ?>>Haute</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="brainstorming_id">Brainstorming lie</label>
                            <select id="brainstorming_id" name="brainstorming_id">
                                <option value="">Choisir un brainstorming</option>
                                <?php foreach ($brainstormings as $brainstorming): ?>
                                    <option value="<?= (int) $brainstorming['id'] ?>" <?= $brainstormingId === (int) $brainstorming['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($brainstorming['titre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if ($isAdmin): ?>
                            <div class="form-group">
                                <label for="statut">Statut</label>
                                <select id="statut" name="statut">
                                    <option value="proposee">Proposee</option>
                                    <option value="en_etude">En etude</option>
                                    <option value="approuvee">Approuvee</option>
                                    <option value="rejetee">Rejetee</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="votes">Votes</label>
                                <input type="number" id="votes" name="votes" value="<?= htmlspecialchars($_POST['votes'] ?? '0') ?>">
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!$isAdmin): ?>
                        <div class="alert alert-info" style="margin-bottom: 1.5rem;">
                            Votre idee sera enregistree avec le statut <strong>Proposee</strong>. Le suivi administratif reste reserve aux admins.
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="contenu">Contenu de l idee</label>
                        <textarea id="contenu" name="contenu" rows="7"><?= htmlspecialchars($_POST['contenu'] ?? '') ?></textarea>
                    </div>

                    <div class="action-row">
                        <a href="<?= $brainstormingId > 0 ? 'index.php?action=list_idees&brainstorming_id=' . $brainstormingId : 'index.php?action=all_idees' ?>" class="btn btn-secondary page-btn-secondary">Retour</a>
                        <button type="submit" class="btn btn-primary">Enregistrer l idee</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>
</div></div>

<footer class="footer">
    <p>&copy; 2026 SkillBridge</p>
</footer>
</body>
</html>




