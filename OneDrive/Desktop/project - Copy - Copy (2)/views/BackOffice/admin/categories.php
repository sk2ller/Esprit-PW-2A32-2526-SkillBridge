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
      <a href="index.php?page=admin_categorie_create" class="topbar-btn topbar-btn-primary">
        <i class="fas fa-plus"></i> Nouvelle Catégorie
      </a>
    </div>
  </div>

  <div class="admin-content">

    <?php if (!empty($_GET['success'])): ?>
    <?php $msgs = ['1'=>'Catégorie créée.','2'=>'Catégorie modifiée.','3'=>'Catégorie supprimée.']; ?>
    <div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> <?= $msgs[$_GET['success']] ?></div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px,1fr)); gap:1.5rem;">
      <?php foreach ($categories as $cat): ?>
      <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-lg); padding:1.5rem; transition:all 0.2s; position:relative; overflow:hidden;"
           onmouseover="this.style.borderColor='var(--border-light)'" onmouseout="this.style.borderColor='var(--border)'">
        <div style="position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg, var(--accent), #4f46e5);"></div>
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:1rem;">
          <div style="width:48px; height:48px; background:var(--accent-glow); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">
            <i class="<?= htmlspecialchars($cat['icone']) ?>" style="color:var(--accent-light);"></i>
          </div>
          <div>
            <div style="font-weight:700; font-size:1rem;"><?= htmlspecialchars($cat['nom_categorie']) ?></div>
            <div style="color:var(--text-muted); font-size:0.78rem;"><?= $cat['nb_services'] ?> service(s)</div>
          </div>
        </div>
        <?php if ($cat['description']): ?>
        <p style="color:var(--text-muted); font-size:0.82rem; margin-bottom:1rem; line-height:1.5;">
          <?= htmlspecialchars(substr($cat['description'], 0, 80)) ?>...
        </p>
        <?php endif; ?>
        <div style="display:flex; gap:8px; justify-content:flex-end; border-top:1px solid var(--border); padding-top:1rem;">
          <a href="index.php?page=admin_categorie_edit&id=<?= $cat['id_categorie'] ?>"
             class="admin-btn admin-btn-outline admin-btn-sm">
            <i class="fas fa-edit"></i> Modifier
          </a>
          <a href="index.php?page=admin_categorie_delete&id=<?= $cat['id_categorie'] ?>"
             onclick="return confirm('Supprimer la catégorie « <?= htmlspecialchars($cat['nom_categorie']) ?> » ?')"
             class="admin-btn admin-btn-danger admin-btn-sm">
            <i class="fas fa-trash"></i>
          </a>
        </div>
      </div>
      <?php endforeach; ?>

      <!-- Add card -->
      <a href="index.php?page=admin_categorie_create"
         style="background:var(--bg-card); border:2px dashed var(--border); border-radius:var(--radius-lg); padding:2rem; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; text-decoration:none; color:var(--text-muted); transition:all 0.2s; min-height:180px; gap:0.5rem;"
         onmouseover="this.style.borderColor='var(--accent)'; this.style.color='var(--accent-light)'"
         onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-muted)'">
        <i class="fas fa-plus-circle" style="font-size:2rem; margin-bottom:0.5rem;"></i>
        <div style="font-weight:600;">Nouvelle catégorie</div>
      </a>
    </div>

  </div>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
