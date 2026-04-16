<?php
$pageTitle = (isset($categorie) ? 'Modifier' : 'Nouvelle') . ' Catégorie - Admin SkillBridge';
include __DIR__ . '/../partials/sidebar.php';
$isEdit = isset($categorie) && $categorie;
?>

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title"><?= $isEdit ? 'Modifier la Catégorie' : 'Nouvelle Catégorie' ?></div>
      <div class="topbar-bread">
        <a href="index.php?page=admin_dashboard">Dashboard</a> › 
        <a href="index.php?page=admin_categories">Catégories</a> › 
        <?= $isEdit ? 'Modifier' : 'Nouvelle' ?>
      </div>
    </div>
  </div>

  <div class="admin-content">
    <div class="admin-form-card">

      <?php if (!empty($error)): ?>
      <div class="admin-alert admin-alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" id="categorieForm">
        <div class="form-group">
          <label class="form-label">Nom de la catégorie <span style="color:var(--danger);">*</span></label>
          <input type="text" id="nom_categorie" name="nom_categorie" class="form-control"
                 value="<?= htmlspecialchars($categorie['nom_categorie'] ?? '') ?>"
                 placeholder="Ex: Templates Web">
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4"
                    placeholder="Description courte de la catégorie..."><?= htmlspecialchars($categorie['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Icône (classe Font Awesome)</label>
          <input type="text" name="icone" class="form-control"
                 value="<?= htmlspecialchars($categorie['icone'] ?? 'fas fa-folder') ?>"
                 placeholder="Ex: fas fa-laptop-code">
          <div style="color:var(--text-muted); font-size:0.78rem; margin-top:4px;">
            Utilisez les classes Font Awesome (ex: fas fa-code, fas fa-palette)
          </div>
        </div>

        <div style="display:flex; gap:1rem; margin-top:1.5rem;">
          <button type="submit" class="admin-btn admin-btn-primary">
            <i class="fas fa-<?= $isEdit ? 'save' : 'plus' ?>"></i>
            <?= $isEdit ? 'Enregistrer' : 'Créer la catégorie' ?>
          </button>
          <a href="index.php?page=admin_categories" class="admin-btn admin-btn-outline">Annuler</a>
        </div>
      </form>
    </div>
  </div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<script src="views/assets/js/add_categ.js"></script>
