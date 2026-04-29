<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes services - SkillBridge</title>
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
                        <h1 class="section-title">Mes <span>Services</span></h1>
                        <p class="section-subtitle">Espace freelancer complet pour creer, modifier et supprimer vos services dans un layout dashboard avec sidebar.</p>
                    </div>
                    <a href="?action=service_create" class="sb-btn"><i class="fas fa-plus"></i> Nouveau service</a>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="inline-alert success">Operation effectuee avec succes.</div>
                <?php endif; ?>

                <div class="mini-stats">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                        <div class="service-price"><?= count($services) ?></div>
                        <div class="muted-copy">Services au total</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
                        <div class="service-price"><?= count(array_filter($services, fn($s) => $s['statut'] === 'actif')) ?></div>
                        <div class="muted-copy">Services actifs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                        <div class="service-price"><?= count(array_filter($services, fn($s) => $s['statut'] === 'en_attente')) ?></div>
                        <div class="muted-copy">En attente</div>
                    </div>
                </div>

                <?php if (empty($services)): ?>
                    <div class="empty-state">
                        <div class="icon"><i class="fas fa-briefcase"></i></div>
                        <h3>Aucun service cree</h3>
                        <p>Commencez par ajouter votre premier service depuis le dashboard freelancer.</p>
                    </div>
                <?php else: ?>
                    <div class="service-grid">
                        <?php foreach ($services as $s): ?>
                        <article class="service-card">
                            <div class="service-card-top">
                                <?php if (!empty($s['thumbnail'])): ?>
                                    <img src="/Views/assets/uploads/<?= htmlspecialchars($s['thumbnail']) ?>" alt="<?= htmlspecialchars($s['titre']) ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <i class="fas fa-briefcase" style="font-size:3rem; color:rgba(255,255,255,.22);"></i>
                                <?php endif; ?>
                            </div>
                            <div class="service-card-body">
                                <span class="service-badge"><?= htmlspecialchars($s['nom_categorie']) ?></span>
                                <h3 class="service-title"><?= htmlspecialchars($s['titre']) ?></h3>
                                <div class="service-meta">Statut: <?= htmlspecialchars($s['statut']) ?></div>
                                <div class="service-meta">Livraison: <?= (int)$s['delai_livraison'] ?> jours</div>
                            </div>
                            <div class="service-card-footer">
                                <div class="service-price"><?= number_format((float)$s['prix'], 2) ?> DT</div>
                                <div style="display:flex; gap:.5rem;">
                                    <a class="sb-btn-soft" href="?action=service_detail&id=<?= (int)$s['id_service'] ?>"><i class="fas fa-eye"></i></a>
                                    <a class="sb-btn-soft" href="?action=service_edit&id=<?= (int)$s['id_service'] ?>"><i class="fas fa-pen"></i></a>
                                    <a class="sb-btn-danger" href="?action=service_delete&id=<?= (int)$s['id_service'] ?>" onclick="return confirm('Supprimer ce service ?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</body>
</html>
