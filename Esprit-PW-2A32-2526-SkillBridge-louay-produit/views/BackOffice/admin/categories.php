<?php
$pageTitle = 'Catégories - Admin SkillBridge';
include __DIR__ . '/../partials/sidebar.php';
?>

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title">Gestion des Catégories</div>
      <div class="topbar-bread">
        <a href="index.php?page=admin_dashboard">Dashboard</a> › Catégories
      </div>
    </div>
    <div class="topbar-actions">
      <a href="index.php?page=admin_categorie_create" class="admin-btn admin-btn-primary">
        <i class="fas fa-plus"></i> Nouvelle Catégorie
      </a>
    </div>
  </div>

  <div class="admin-content">

    <?php if (!empty($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success">
      <i class="fas fa-check-circle"></i>
      <?php
      $msgs = ['1'=>'Catégorie créée avec succès.','2'=>'Catégorie modifiée.','3'=>'Catégorie supprimée.'];
      echo $msgs[$_GET['success']] ?? 'Opération effectuée.';
      ?>
    </div>
    <?php endif; ?>

    <div class="admin-table-wrap">
      <div class="admin-table-header">
        <div class="admin-table-title">Liste des Catégories</div>
        <span style="color:var(--text-muted); font-size:0.85rem;"><?= count($categories) ?> catégories</span>
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Icône</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Produits</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($categories)): ?>
          <tr><td colspan="6" style="text-align:center; padding:3rem; color:var(--text-muted);">
            Aucune catégorie
          </td></tr>
          <?php else: ?>
          <?php foreach ($categories as $cat): ?>
          <tr>
            <td style="color:var(--text-muted); font-size:0.8rem;">#<?= $cat['id_categorie'] ?></td>
            <td><i class="<?= htmlspecialchars($cat['icone']) ?>" style="font-size:1.2rem; color:var(--accent-light);"></i></td>
            <td style="font-weight:600;"><?= htmlspecialchars($cat['nom_categorie']) ?></td>
            <td style="color:var(--text-muted); font-size:0.82rem; max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
              <?= htmlspecialchars($cat['description'] ?? '') ?>
            </td>
            <td>
              <span class="badge badge-info"><?= $cat['nb_produits'] ?> produit<?= $cat['nb_produits'] > 1 ? 's' : '' ?></span>
            </td>
            <td>
              <div style="display:flex; gap:6px;">
                <a href="index.php?page=admin_categorie_edit&id=<?= $cat['id_categorie'] ?>"
                   class="admin-btn admin-btn-outline admin-btn-sm">
                  <i class="fas fa-pen"></i>
                </a>
                <a href="index.php?page=admin_categorie_delete&id=<?= $cat['id_categorie'] ?>"
                   class="admin-btn admin-btn-danger admin-btn-sm"
                   onclick="return confirm('Supprimer cette catégorie ? Les produits associés seront aussi supprimés.')">
                  <i class="fas fa-trash"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
