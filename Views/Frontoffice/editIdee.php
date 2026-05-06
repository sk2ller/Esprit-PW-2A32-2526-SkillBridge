<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?action=login');
    exit;
}

$uniqueValidationErrors = array_values(array_unique($validationErrors ?? []));
$displayValidationErrors = array_values(array_filter($uniqueValidationErrors, fn($message) => $message !== ($error ?? '')));
$isModerationError = !empty($error) && stripos($error, 'Contenu bloque') !== false;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une idee - SkillBridge</title>
    <link rel="stylesheet" href="Views/assets/css/skillbridge.css">
    <link rel="stylesheet" href="Views/assets/css/enhanced-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
    <style>
        .moderation-alert {
            display: grid;
            grid-template-columns: 44px minmax(0, 1fr);
            gap: .9rem;
            align-items: start;
            border: 1px solid rgba(217, 45, 32, .18);
            border-left: 5px solid #d92d20;
            border-radius: 16px;
            background: #fff7f5;
            color: #7a271a;
            padding: 1rem 1.1rem;
            margin-bottom: 1.2rem;
        }

        .moderation-alert-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: #fee4e2;
            color: #b42318;
            font-size: 1.1rem;
        }

        .moderation-alert-title {
            font-weight: 900;
            color: #7a271a;
            margin-bottom: .2rem;
        }

        .moderation-alert-text {
            margin: 0;
            color: #912018;
            line-height: 1.45;
        }
    </style>
</head>
<body class="skillbridge-front">
<?php include __DIR__ . '/partials/front_navbar.php'; ?>
<div class="front-page"><div class="dashboard-shell">
    <?php include __DIR__ . '/partials/brainstorming_sidebar.php'; ?>
    <main class="page-shell" style="flex:1;">
    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">Frontoffice</span>
            <h1>Mettre a jour une idee</h1>
            <p>Le formulaire respecte maintenant les permissions metier : l auteur gere son contenu, l admin garde la moderation.</p>
        </div>
    </section>

    <section class="page-section">
        <div class="container">
            <?php if (!empty($error)): ?>
                <?php if ($isModerationError): ?>
                    <div class="moderation-alert">
                        <div class="moderation-alert-icon"><i class="fas fa-shield-halved"></i></div>
                        <div>
                            <div class="moderation-alert-title">Modification refusee</div>
                            <p class="moderation-alert-text">Le texte contient un contenu non autorise. Retirez les insultes, menaces ou elements de spam, puis reessayez.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!$isModerationError && !empty($displayValidationErrors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($displayValidationErrors as $message): ?>
                                <li><?= htmlspecialchars($message) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="form-card">
                <form method="post" action="index.php?action=edit_idee&id=<?= (int) $idee['id'] ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="titre">Titre</label>
                            <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($idee['titre']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="categorie">Categorie</label>
                            <input type="text" id="categorie" name="categorie" value="<?= htmlspecialchars($idee['categorie']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="priorite">Priorite</label>
                            <select id="priorite" name="priorite">
                                <option value="faible" <?= $idee['priorite'] === 'faible' ? 'selected' : '' ?>>Faible</option>
                                <option value="moyenne" <?= $idee['priorite'] === 'moyenne' ? 'selected' : '' ?>>Moyenne</option>
                                <option value="haute" <?= $idee['priorite'] === 'haute' ? 'selected' : '' ?>>Haute</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="brainstorming_id">Brainstorming lie</label>
                            <select id="brainstorming_id" name="brainstorming_id">
                                <?php foreach ($brainstormings as $brainstorming): ?>
                                    <option value="<?= (int) $brainstorming['id'] ?>" <?= (int) $idee['brainstorming_id'] === (int) $brainstorming['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($brainstorming['titre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if ($isAdmin): ?>
                            <div class="form-group">
                                <label for="statut">Statut</label>
                                <select id="statut" name="statut">
                                    <option value="proposee" <?= $idee['statut'] === 'proposee' ? 'selected' : '' ?>>Proposee</option>
                                    <option value="en_etude" <?= $idee['statut'] === 'en_etude' ? 'selected' : '' ?>>En etude</option>
                                    <option value="approuvee" <?= $idee['statut'] === 'approuvee' ? 'selected' : '' ?>>Approuvee</option>
                                    <option value="rejetee" <?= $idee['statut'] === 'rejetee' ? 'selected' : '' ?>>Rejetee</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="votes">Votes</label>
                                <input type="number" id="votes" name="votes" value="<?= (int) $idee['votes'] ?>">
                            </div>
                        <?php else: ?>
                            <div class="form-group">
                                <label>Statut</label>
                                <input type="text" value="<?= htmlspecialchars(str_replace('_', ' ', ucfirst($idee['statut']))) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label>Votes</label>
                                <input type="text" value="<?= (int) $idee['votes'] ?>" disabled>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="contenu">Contenu de l idee</label>
                        <textarea id="contenu" name="contenu" rows="7"><?= htmlspecialchars($idee['contenu']) ?></textarea>
                    </div>

                    <div class="action-row">
                        <a href="index.php?action=list_idees&brainstorming_id=<?= (int) $idee['brainstorming_id'] ?>" class="btn btn-secondary page-btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Mettre a jour</button>
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


