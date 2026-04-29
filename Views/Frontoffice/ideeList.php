<?php
$canManage = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Idees liees au brainstorming - SkillBridge</title>
    <link rel="stylesheet" href="Views/assets/css/skillbridge.css">
    <link rel="stylesheet" href="Views/assets/css/enhanced-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
</head>
<body class="skillbridge-front">
<?php include __DIR__ . '/partials/front_navbar.php'; ?>
<div class="front-page"><div class="dashboard-shell">
    <?php include __DIR__ . '/partials/brainstorming_sidebar.php'; ?>
    <main class="page-shell" style="flex:1;">
    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">Espace idees</span>
            <h1><?= htmlspecialchars($brainstorming['titre']) ?></h1>
            <p><?= htmlspecialchars($brainstorming['description']) ?></p>
            <div class="hero-inline-meta">
                <span class="pill">Date debut : <?= htmlspecialchars($brainstorming['date_debut']) ?></span>
                <span class="pill"><?= (int) $brainstorming['accepted'] === 1 ? 'Brainstorming accepte' : 'En attente de validation' ?></span>
                <span class="pill">Porteur : <?= htmlspecialchars(trim(($brainstorming['user_prenom'] ?? '') . ' ' . ($brainstorming['user_nom'] ?? ''))) ?></span>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="container">
            <div class="section-toolbar">
                <div>
                    <h2>Idees rattachees</h2>
                    <p>Chaque carte presente une proposition liee a ce brainstorming, avec des actions adaptees a l auteur ou a l admin.</p>
                </div>
                <?php if ($canManage): ?>
                    <a href="index.php?action=add_idee&brainstorming_id=<?= (int) $brainstorming['id'] ?>" class="btn btn-primary">Ajouter une idee</a>
                <?php endif; ?>
            </div>

            <?php if (empty($idees)): ?>
                <div class="empty-state">
                    <h3>Aucune idee pour le moment</h3>
                    <p>Commencez par ajouter la premiere idee liee a ce brainstorming.</p>
                </div>
            <?php else: ?>
                <div class="idea-grid">
                    <?php foreach ($idees as $idee): ?>
                        <?php $canEditIdea = $isAdmin || ($currentUserId > 0 && $currentUserId === (int) $idee['user_id']); ?>
                        <article class="idea-card">
                            <div class="idea-card-top">
                                <div>
                                    <span class="idea-tag"><?= htmlspecialchars($idee['categorie']) ?></span>
                                    <h3><?= htmlspecialchars($idee['titre']) ?></h3>
                                </div>
                                <span class="idea-status status-<?= htmlspecialchars($idee['statut']) ?>"><?= htmlspecialchars(str_replace('_', ' ', $idee['statut'])) ?></span>
                            </div>
                            <p><?= nl2br(htmlspecialchars($idee['contenu'])) ?></p>
                            <div class="idea-meta">
                                <span>Priorite : <?= htmlspecialchars($idee['priorite']) ?></span>
                                <span>Votes : <?= (int) $idee['votes'] ?></span>
                                <span>Auteur : <?= htmlspecialchars(trim(($idee['user_prenom'] ?? '') . ' ' . ($idee['user_nom'] ?? ''))) ?></span>
                            </div>
                            <?php if ($canEditIdea): ?>
                                <div class="action-row">
                                    <a href="index.php?action=edit_idee&id=<?= (int) $idee['id'] ?>" class="btn btn-secondary page-btn-secondary">Modifier</a>
                                    <a href="index.php?action=delete_idee&id=<?= (int) $idee['id'] ?>&brainstorming_id=<?= (int) $brainstorming['id'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer cette idee ?')">Supprimer</a>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
</div></div>

<footer class="footer">
    <p>&copy; 2026 SkillBridge</p>
</footer>
</body>
</html>

