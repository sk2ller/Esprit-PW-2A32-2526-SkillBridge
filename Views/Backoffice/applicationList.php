<?php if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 1) { header('Location: ?action=login'); exit; } ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Candidatures - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-admin.css">
</head>
<body class="skillbridge-admin">
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="admin-main">
    <div class="admin-topbar">
        <div><div class="topbar-title">Candidatures</div><div class="topbar-bread"><a href="?action=statistics">Dashboard</a> / Candidatures freelancers</div></div>
        <div class="topbar-actions"><a class="topbar-btn topbar-btn-outline" href="?action=job_offers_admin">Offres Job</a></div>
    </div>
    <div class="stats-grid">
        <div class="stat-widget"><div class="sw-icon"><i class="fas fa-file-signature"></i></div><div class="sw-value"><?= count($applications) ?></div><div class="sw-label">Liste chargee</div></div>
        <div class="stat-widget orange"><div class="sw-icon"><i class="fas fa-hourglass-half"></i></div><div class="sw-value"><?= count(array_filter($applications, fn($a) => $a['statut'] === 'en_attente')) ?></div><div class="sw-label">En attente</div></div>
        <div class="stat-widget green"><div class="sw-icon"><i class="fas fa-check-circle"></i></div><div class="sw-value"><?= count(array_filter($applications, fn($a) => $a['statut'] === 'acceptee')) ?></div><div class="sw-label">Acceptees</div></div>
        <div class="stat-widget blue"><div class="sw-icon"><i class="fas fa-link"></i></div><div class="sw-value"><?= count(array_filter($applications, fn($a) => !empty($a['cv_url']) || !empty($a['portfolio_url']))) ?></div><div class="sw-label">Avec pieces</div></div>
    </div>
    <div class="admin-table-wrap">
        <div class="admin-table-header"><div class="admin-table-title">Liste des candidatures</div></div>
        <table class="admin-table">
            <thead><tr><th>Offre</th><th>Client</th><th>Freelancer</th><th>Budget propose</th><th>Disponibilite</th><th>Documents</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($applications as $application): ?>
                <tr>
                    <td><div class="table-service-name"><?= htmlspecialchars($application['titre']) ?></div></td>
                    <td><?= htmlspecialchars($application['client_prenom'] . ' ' . $application['client_nom']) ?></td>
                    <td><?= htmlspecialchars($application['freelancer_prenom'] . ' ' . $application['freelancer_nom']) ?></td>
                    <td><?= number_format((float)$application['budget_propose'], 2) ?> DT</td>
                    <td><?= (int)$application['disponibilite_jours'] ?> jours</td>
                    <td>
                        <?php if (!empty($application['cv_url'])): ?><div><a href="<?= htmlspecialchars($application['cv_url']) ?>" target="_blank" rel="noopener">CV</a></div><?php endif; ?>
                        <?php if (!empty($application['portfolio_url'])): ?><div><a href="<?= htmlspecialchars($application['portfolio_url']) ?>" target="_blank" rel="noopener">Portfolio</a></div><?php endif; ?>
                        <?php if (empty($application['cv_url']) && empty($application['portfolio_url'])): ?>-<?php endif; ?>
                    </td>
                    <td><span class="badge <?= $application['statut'] === 'acceptee' ? 'badge-actif' : ($application['statut'] === 'refusee' ? 'badge-suspendu' : 'badge-pending') ?>"><?= htmlspecialchars($application['statut']) ?></span></td>
                    <td><div class="admin-stack-actions"><span class="admin-btn admin-btn-outline admin-btn-sm">Decision client</span></div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</div>
</body>
</html>
