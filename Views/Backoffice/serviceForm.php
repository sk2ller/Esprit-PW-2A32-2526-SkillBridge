<?php
if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 1) {
    header('Location: ?action=login');
    exit;
}
$isEdit = isset($service) && $service;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Modifier service' : 'Nouveau service' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600;700&display=swap">
</head>
<body class="skillbridge-admin">
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <div class="topbar-title"><?= $isEdit ? 'Modifier service' : 'Nouveau service' ?></div>
                <div class="topbar-bread"><a href="?action=statistics">Dashboard</a> / <a href="?action=services_admin">Services</a> / Formulaire</div>
            </div>
            <div class="topbar-actions">
                <a class="topbar-btn topbar-btn-outline" href="?action=services_admin">Retour</a>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="admin-form-wrap">
            <form method="post" enctype="multipart/form-data" class="admin-form-grid">
                <div class="admin-form-group">
                    <label>Titre</label>
                    <input name="titre" value="<?= htmlspecialchars($service['titre'] ?? '') ?>" required>
                </div>

                <div class="admin-form-group admin-form-group-full">
                    <label>Description</label>
                    <textarea name="description" rows="5" required><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
                </div>

                <div class="admin-form-group">
                    <label>Prix</label>
                    <input type="number" step="0.01" min="0.01" name="prix" value="<?= htmlspecialchars($service['prix'] ?? '') ?>" required>
                </div>

                <div class="admin-form-group">
                    <label>Delai (jours)</label>
                    <input type="number" min="1" name="delai_livraison" value="<?= htmlspecialchars($service['delai_livraison'] ?? 1) ?>" required>
                </div>

                <div class="admin-form-group">
                    <label>Statut</label>
                    <?php $currentStatut = $service['statut'] ?? 'en_attente'; ?>
                    <select name="statut">
                        <option value="en_attente" <?= $currentStatut === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                        <option value="actif" <?= $currentStatut === 'actif' ? 'selected' : '' ?>>Actif</option>
                        <option value="suspendu" <?= $currentStatut === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                    </select>
                </div>

                <div class="admin-form-group">
                    <label>Categorie</label>
                    <select name="id_categorie" required>
                        <option value="">Choisir une categorie</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int)$c['id_categorie'] ?>" <?= (($service['id_categorie'] ?? '') == $c['id_categorie']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nom_categorie']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-form-group">
                    <label>Freelancer</label>
                    <select name="id_freelancer" required>
                        <option value="">Choisir un freelancer</option>
                        <?php foreach ($freelancers as $freelancer): ?>
                            <option value="<?= (int)$freelancer['id'] ?>" <?= (($service['id_freelancer'] ?? '') == $freelancer['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($freelancer['prenom'] . ' ' . $freelancer['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-form-group admin-form-group-full">
                    <label>Thumbnail</label>
                    <input type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp,.gif">
                    <?php if (!empty($service['thumbnail'])): ?>
                        <div style="margin-top:.8rem;">
                            <div style="font-size:.82rem; color:var(--text-muted); margin-bottom:.4rem;">Miniature actuelle</div>
                            <img src="/Views/assets/uploads/<?= htmlspecialchars($service['thumbnail']) ?>" alt="Thumbnail" style="width:180px; height:120px; object-fit:cover; border-radius:14px; border:1px solid var(--border);">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="admin-form-actions admin-form-group-full">
                    <a class="admin-btn admin-btn-outline" href="?action=services_admin">Annuler</a>
                    <button class="admin-btn admin-btn-primary"><?= $isEdit ? 'Enregistrer' : 'Ajouter' ?></button>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
