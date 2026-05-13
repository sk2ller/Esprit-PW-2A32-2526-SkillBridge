<?php $role = $_SESSION['user_role'] ?? null; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Services - SkillBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
</head>
<body class="skillbridge-front">
    <?php include __DIR__ . '/partials/front_navbar.php'; ?>

    <div class="front-page">
        <section class="section-surface">
            <div class="section-header">
                <div>
                    <h1 class="section-title">Marketplace <span>Services</span></h1>
                    <p class="section-subtitle">Page client pour consulter les services publies, avec une navigation claire et des acces logiques selon le role connecte.</p>
                </div>
                <?php if ($role == 3): ?>
                    <a class="sb-btn" href="?action=service_create"><i class="fas fa-plus"></i> Ajouter un service</a>
                <?php endif; ?>
            </div>

            <form class="filter-bar" method="get">
                <input type="hidden" name="action" value="services">
                <div style="display:grid; grid-template-columns:2fr 1.2fr auto; gap:1rem;">
                    <input class="sb-control" name="search" placeholder="Recherche..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <select class="sb-select" name="categorie">
                        <option value="">Toutes categories</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id_categorie'] ?>" <?= ((int)($_GET['categorie'] ?? 0) === (int)$c['id_categorie']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nom_categorie']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="sb-btn" type="submit"><i class="fas fa-filter"></i> Filtrer</button>
                </div>
            </form>
        </section>

        <section class="section-surface">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Services <span>Disponibles</span></h2>
                    <p class="section-subtitle"><?= count($services) ?> service<?= count($services) > 1 ? 's' : '' ?> trouve<?= count($services) > 1 ? 's' : '' ?></p>
                </div>
            </div>

            <?php if (empty($services)): ?>
                <div class="empty-state">
                    <div class="icon"><i class="fas fa-magnifying-glass"></i></div>
                    <h3>Aucun service trouve</h3>
                    <p>Essayez une autre recherche ou une autre categorie.</p>
                </div>
            <?php else: ?>
                <div class="service-grid">
                    <?php foreach ($services as $s): ?>
                    <article class="service-card">
                        <div class="service-card-top">
                            <?php if (!empty($s['thumbnail'])): ?>
                                <img src="/Views/assets/uploads/<?= htmlspecialchars($s['thumbnail']) ?>" alt="<?= htmlspecialchars($s['titre']) ?>" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <i class="fas fa-briefcase" style="font-size:3rem; color:rgba(255,255,255,.25);"></i>
                            <?php endif; ?>
                        </div>
                        <div class="service-card-body">
                            <span class="service-badge"><?= htmlspecialchars($s['nom_categorie']) ?></span>
                            <h3 class="service-title"><?= htmlspecialchars($s['titre']) ?></h3>
                            <div class="service-meta"><?= htmlspecialchars($s['prenom'] . ' ' . $s['nom']) ?></div>
                            <div class="service-meta"><i class="fas fa-clock"></i> <?= (int)$s['delai_livraison'] ?> jours</div>
                            <p class="service-copy"><?= htmlspecialchars(mb_strimwidth($s['description'], 0, 120, '...')) ?></p>
                        </div>
                        <div class="service-card-footer">
                            <div class="service-price"><?= number_format((float)$s['prix'], 2) ?> DT</div>
                            <a class="sb-btn-soft" href="?action=service_detail&id=<?= (int)$s['id_service'] ?>">Voir detail</a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
