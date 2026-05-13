<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes candidatures - SkillBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
</head>
<body class="skillbridge-front">
<?php include __DIR__ . '/partials/front_navbar.php'; ?>
<div class="front-page">
    <div class="dashboard-shell">
        <?php include __DIR__ . '/partials/freelancer_sidebar.php'; ?>
        <section class="section-surface">
            <div class="section-header"><div><h1 class="section-title">Mes <span>Candidatures</span></h1><p class="section-subtitle">Suivez vos reponses, vos budgets proposes et les offres auxquelles vous avez deja postule.</p></div></div>
            <div class="mini-stats">
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-paper-plane"></i></div><div class="service-price"><?= (int)($stats['total_candidatures'] ?? 0) ?></div><div class="muted-copy">Total candidatures</div></div>
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-hourglass-half"></i></div><div class="service-price"><?= (int)($stats['candidatures_attente'] ?? 0) ?></div><div class="muted-copy">En attente</div></div>
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-circle-check"></i></div><div class="service-price"><?= (int)($stats['candidatures_acceptees'] ?? 0) ?></div><div class="muted-copy">Acceptees</div></div>
            </div>
            <div class="dashboard-grid">
                <?php foreach ($applications as $application): ?>
                    <div class="surface-card">
                        <span class="service-badge"><?= htmlspecialchars($application['statut']) ?></span>
                        <h3><?= htmlspecialchars($application['titre']) ?></h3>
                        <p class="muted-copy">Client: <?= htmlspecialchars($application['prenom'] . ' ' . $application['nom']) ?></p>
                        <div class="service-meta">Budget offre: <?= number_format((float)$application['budget'], 2) ?> DT</div>
                        <div class="service-meta">Mon budget: <?= number_format((float)$application['budget_propose'], 2) ?> DT</div>
                        <div class="service-meta">Disponibilite: <?= (int)$application['disponibilite_jours'] ?> jours</div>
                        <?php if (!empty($application['cv_url'])): ?><div class="service-meta">CV ajoute</div><?php endif; ?>
                        <?php if (!empty($application['portfolio_url'])): ?><div class="service-meta">Portfolio ajoute</div><?php endif; ?>
                        <div style="margin-top:1rem;">
                            <a class="sb-btn-soft" href="?action=chat&offer_id=<?= (int)$application['id_offre'] ?>"><i class="fas fa-comments"></i> Ouvrir le chat</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>
</body>
</html>
