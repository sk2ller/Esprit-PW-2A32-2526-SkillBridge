<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($service['titre']) ?> - SkillBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
</head>
<body class="skillbridge-front">
    <?php include __DIR__ . '/partials/front_navbar.php'; ?>

    <div class="front-page">
        <div class="detail-shell">
            <div>
                <div class="detail-banner">
                    <span class="service-badge"><?= htmlspecialchars($service['nom_categorie']) ?></span>
                    <h1 class="section-title" style="margin:0 0 .75rem 0;"><?= htmlspecialchars($service['titre']) ?></h1>
                    <p class="section-subtitle" style="margin:0;">Page detail cote client pour consulter un service, verifier le freelancer et poursuivre le parcours depuis la marketplace.</p>
                </div>

                <?php if (!empty($service['thumbnail'])): ?>
                    <div class="detail-main-card" style="padding:0; overflow:hidden; margin-bottom:1.5rem;">
                        <img src="/Views/assets/uploads/<?= htmlspecialchars($service['thumbnail']) ?>" alt="<?= htmlspecialchars($service['titre']) ?>" style="width:100%; max-height:360px; object-fit:cover; display:block;">
                    </div>
                <?php endif; ?>

                <div class="detail-main-card">
                    <div class="service-meta" style="margin-bottom:1rem;">Freelancer: <strong><?= htmlspecialchars($service['prenom'] . ' ' . $service['nom']) ?></strong></div>
                    <h3 style="font-family:'Playfair Display', serif; margin-bottom:.75rem;">A propos de ce service</h3>
                    <p class="muted-copy"><?= nl2br(htmlspecialchars($service['description'])) ?></p>
                </div>
            </div>

            <aside class="detail-side-card">
                <div class="detail-price"><?= number_format((float)$service['prix'], 2) ?> DT</div>
                <div class="muted-copy" style="margin:.45rem 0 1.5rem 0;"><i class="fas fa-clock"></i> Livraison en <?= (int)$service['delai_livraison'] ?> jours</div>
                <div class="surface-card" style="padding:1rem; box-shadow:none;">
                    <div class="service-meta">Categorie</div>
                    <div style="font-weight:700;"><?= htmlspecialchars($service['nom_categorie']) ?></div>
                </div>
                <div class="surface-card" style="padding:1rem; margin-top:1rem; box-shadow:none;">
                    <div class="service-meta">Statut</div>
                    <div style="font-weight:700;"><?= htmlspecialchars($service['statut']) ?></div>
                </div>
                <?php if ((int)($_SESSION['user_role'] ?? 0) === 2): ?>
                    <div class="surface-card" style="padding:1rem; margin-top:1rem; box-shadow:none;">
                        <div class="service-meta">Espace client</div>
                        <div style="font-weight:700;">Vous pouvez consulter les services et choisir le freelancer adapte a votre besoin.</div>
                    </div>
                    <a href="?action=chat&service_id=<?= (int)$service['id_service'] ?>" class="sb-btn" style="width:100%; margin-top:1rem;">
                        <i class="fas fa-comments"></i> Contacter le freelancer
                    </a>
                <?php endif; ?>
                <?php if ((int)($_SESSION['user_role'] ?? 0) === 3 && (int)($_SESSION['user_id'] ?? 0) === (int)$service['id_freelancer']): ?>
                    <a href="?action=service_edit&id=<?= (int)$service['id_service'] ?>" class="sb-btn-soft" style="width:100%; margin-top:1rem;">Modifier mon service</a>
                <?php endif; ?>
                <a href="?action=services" class="sb-btn" style="width:100%; margin-top:1rem;">Retour aux services</a>
            </aside>
        </div>
    </div>
</body>
</html>
