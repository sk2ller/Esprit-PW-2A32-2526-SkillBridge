<?php if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 1) { header('Location: ?action=login'); exit; } ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Offres Job - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-admin.css">
</head>
<body class="skillbridge-admin">
<div class="admin-layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="admin-main">
    <div class="admin-topbar">
        <div><div class="topbar-title">Offres Job</div><div class="topbar-bread"><a href="?action=statistics">Dashboard</a> / Offres clients</div></div>
        <div class="topbar-actions"><a class="topbar-btn topbar-btn-outline" href="?action=job_applications_admin">Candidatures</a></div>
    </div>
    <div class="stats-grid">
        <div class="stat-widget"><div class="sw-icon"><i class="fas fa-bullhorn"></i></div><div class="sw-value"><?= (int)$stats['total_offres'] ?></div><div class="sw-label">Total offres</div></div>
        <div class="stat-widget green"><div class="sw-icon"><i class="fas fa-check-circle"></i></div><div class="sw-value"><?= (int)$stats['offres_actives'] ?></div><div class="sw-label">Actives</div></div>
        <div class="stat-widget orange"><div class="sw-icon"><i class="fas fa-hourglass-half"></i></div><div class="sw-value"><?= (int)$stats['offres_attente'] ?></div><div class="sw-label">En attente</div></div>
        <div class="stat-widget blue"><div class="sw-icon"><i class="fas fa-file-signature"></i></div><div class="sw-value"><?= (int)$stats['total_candidatures'] ?></div><div class="sw-label">Candidatures</div></div>
        <div class="stat-widget"><div class="sw-icon"><i class="fas fa-user-check"></i></div><div class="sw-value"><?= (int)$stats['candidatures_acceptees'] ?></div><div class="sw-label">Acceptees</div></div>
        <div class="stat-widget orange"><div class="sw-icon"><i class="fas fa-business-time"></i></div><div class="sw-value"><?= (int)$stats['candidatures_attente'] ?></div><div class="sw-label">A traiter</div></div>
    </div>
    <div class="admin-table-wrap">
        <div class="admin-table-header"><div class="admin-table-title">Liste des offres</div></div>
        <table class="admin-table">
            <thead><tr><th>Offre</th><th>Client</th><th>Budget</th><th>Candidatures</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($offres as $offre): ?>
                <tr>
                    <td><div class="table-service-name"><?= htmlspecialchars($offre['titre']) ?></div></td>
                    <td><?= htmlspecialchars($offre['prenom'] . ' ' . $offre['nom']) ?></td>
                    <td><?= number_format((float)$offre['budget'], 2) ?> DT</td>
                    <td><?= (int)$offre['candidature_count'] ?></td>
                    <td><span class="badge <?= $offre['statut'] === 'actif' ? 'badge-actif' : ($offre['statut'] === 'suspendu' ? 'badge-suspendu' : 'badge-pending') ?>"><?= htmlspecialchars($offre['statut']) ?></span></td>
                    <td><div class="admin-stack-actions"><a class="admin-btn admin-btn-success admin-btn-sm" href="?action=job_offer_status&id=<?= (int)$offre['id_offre'] ?>&status=actif">Activer</a><a class="admin-btn admin-btn-warning admin-btn-sm" href="?action=job_offer_status&id=<?= (int)$offre['id_offre'] ?>&status=en_attente">Attente</a><a class="admin-btn admin-btn-danger admin-btn-sm" href="?action=job_offer_status&id=<?= (int)$offre['id_offre'] ?>&status=suspendu">Suspendre</a></div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</div>
</body>
</html>
