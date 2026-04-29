<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Offres job - SkillBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
</head>
<body class="skillbridge-front">
<?php include __DIR__ . '/partials/front_navbar.php'; ?>
<div class="front-page">
    <div class="dashboard-shell">
        <?php include __DIR__ . '/partials/freelancer_sidebar.php'; ?>
        <section class="section-surface">
            <div class="section-header">
                <div>
                    <h1 class="section-title">Offres <span>Job</span></h1>
                    <p class="section-subtitle">Explorez les missions publiees par les clients et candidatez directement depuis votre dashboard freelancer.</p>
                </div>
                <form method="get" style="display:flex; gap:.6rem;">
                    <input type="hidden" name="action" value="job_offers">
                    <input class="sb-control" name="search" placeholder="Rechercher une offre..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="sb-btn" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="mini-stats">
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-bullhorn"></i></div><div class="service-price"><?= count($offres) ?></div><div class="muted-copy">Offres actives</div></div>
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-paper-plane"></i></div><div class="service-price"><?= (int)($stats['total_candidatures'] ?? 0) ?></div><div class="muted-copy">Mes candidatures</div></div>
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div class="service-price"><?= (int)($stats['candidatures_acceptees'] ?? 0) ?></div><div class="muted-copy">Acceptees</div></div>
            </div>
            <div class="service-grid">
                <?php foreach ($offres as $offre): ?>
                    <article class="service-card">
                        <div class="service-card-top"><i class="fas fa-bullhorn" style="font-size:3rem; color:rgba(255,255,255,.22);"></i></div>
                        <div class="service-card-body">
                            <span class="service-badge"><?= htmlspecialchars($offre['niveau_requis']) ?></span>
                            <h3 class="service-title"><?= htmlspecialchars($offre['titre']) ?></h3>
                            <div class="service-meta">Client: <?= htmlspecialchars($offre['prenom'] . ' ' . $offre['nom']) ?></div>
                            <div class="service-meta">Budget: <?= number_format((float)$offre['budget'], 2) ?> DT</div>
                            <div class="service-copy"><?= htmlspecialchars(mb_strimwidth($offre['description'], 0, 120, '...')) ?></div>
                        </div>
                        <div class="service-card-footer">
                            <div class="service-price"><?= (int)$offre['candidature_count'] ?> candidats</div>
                            <a class="sb-btn" href="?action=job_offer_detail&id=<?= (int)$offre['id_offre'] ?>">Voir</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>
</body>
</html>
