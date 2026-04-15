<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ?action=login');
    exit;
}
require_once __DIR__ . '/../../Controllers/BrainstormingController.php';

$brainstormController = new BrainstormingController();
$brainstormings = $brainstormController->listAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Brainstormings — SkillBridge</title>
    <link rel="stylesheet" href="/Views/assets/css/skillbridge.css">
</head>
<body>
<nav class="navbar-top">
    <div class="container">
        <a href="?action=home" class="logo"><img src="/Views/assets/img/logo1.png" alt="SkillBridge" style="height: 50px; width: auto;"></a>
        <div class="nav-buttons">
            <span>Bonjour, <?= htmlspecialchars($_SESSION['user_prenom']) ?></span>
            <a href="?action=brainstorming_add" class="btn btn-secondary">Ajouter Brainstorming</a>
            <a href="?action=profile" class="btn btn-secondary">Mon Profil</a>
            <a href="?action=logout" class="btn btn-logout">Déconnexion</a>
        </div>
    </div>
</nav>
<main class="page-content" style="padding: 4rem 1.5rem;">
    <div class="container" style="max-width: 960px;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <h1>Liste des brainstormings</h1>
                <p>Découvrez les idées soumises par l’équipe et leur statut.</p>
            </div>
            <div>
                <a href="?action=brainstorming_add" class="btn btn-primary">Nouveau brainstorming</a>
                <?php if ($_SESSION['user_role'] == 1): ?>
                    <a href="?action=brainstorming_admin" class="btn btn-warning">Admin Panel</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($brainstormings)): ?>
            <div class="alert alert-info">Aucun brainstorming trouvé pour le moment.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Date début</th>
                            <th>Statut</th>
                            <th>Proposé par</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($brainstormings as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['id']) ?></td>
                                <td><?= htmlspecialchars($item['titre']) ?></td>
                                <td><?= nl2br(htmlspecialchars($item['description'])) ?></td>
                                <td><?= htmlspecialchars($item['date_debut']) ?></td>
                                <td>
                                    <?php if ($item['accepted'] == 1): ?>
                                        <span class="badge bg-success">Accepté</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(($item['user_prenom'] ?? 'Utilisateur') . ' ' . ($item['user_nom'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>
<footer class="footer">
    <p>&copy; 2026 SkillBridge</p>
</footer>
</body>
</html>
