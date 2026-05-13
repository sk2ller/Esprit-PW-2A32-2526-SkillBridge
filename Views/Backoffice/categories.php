<?php
$pageTitle = 'Categories - Admin Geeks';
include __DIR__ . '/sidebar.php';
$categorieController = new CategorieController();
$categoryInsights = $categorieController->getCategoryInsights();
$success = $_GET['success'] ?? null;
$messages = [
    '1' => 'Categorie creee avec succes.',
    '2' => 'Categorie modifiee avec succes.',
    '3' => 'Categorie supprimee avec succes.'
];
?>

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title">Gestion des Categories</div>
      <div class="topbar-bread">
        <a href="index.php?page=admin_dashboard">Dashboard</a> &rsaquo; Categories
      </div>
    </div>
    <div class="topbar-actions">
      <a href="index.php?page=admin_export_pdf&type=categories" class="topbar-btn topbar-btn-outline js-admin-export">
        <i class="fas fa-file-pdf"></i> Export PDF
      </a>
      <a href="index.php?page=admin_categorie_create" class="topbar-btn topbar-btn-primary">
        <i class="fas fa-plus"></i> Nouvelle Categorie
      </a>
    </div>
  </div>

  <div class="admin-content">

    <div class="stats-grid" style="margin-bottom:1.5rem;">
      <div class="stat-widget purple">
        <div class="sw-icon"><i class="fas fa-tags"></i></div>
        <div class="sw-value"><?= $categoryInsights['total'] ?? 0 ?></div>
        <div class="sw-label">Categories</div>
      </div>
      <div class="stat-widget green">
        <div class="sw-icon"><i class="fas fa-layer-group"></i></div>
        <div class="sw-value"><?= $categoryInsights['with_services'] ?? 0 ?></div>
        <div class="sw-label">Avec services</div>
      </div>
      <div class="stat-widget orange">
        <div class="sw-icon"><i class="fas fa-box-open"></i></div>
        <div class="sw-value"><?= $categoryInsights['empty'] ?? 0 ?></div>
        <div class="sw-label">Sans service</div>
      </div>
      <div class="stat-widget blue">
        <div class="sw-icon"><i class="fas fa-crown"></i></div>
        <div class="sw-value"><?= (int) ($categoryInsights['most_used_count'] ?? 0) ?></div>
        <div class="sw-label"><?= htmlspecialchars($categoryInsights['most_used'] ?? 'Aucune') ?></div>
      </div>
    </div>

    <div class="admin-info-card" style="margin-bottom:1.5rem;">
      <div class="admin-info-kicker">Lecture rapide</div>
      <div class="admin-info-value"><?= htmlspecialchars($categoryInsights['most_used'] ?? 'Aucune') ?></div>
      <div class="admin-info-note">Categorie la plus representee actuellement avec <?= (int) ($categoryInsights['most_used_count'] ?? 0) ?> service(s) actifs.</div>
    </div>

    <div style="margin-bottom:1.5rem; display:flex; gap:8px; align-items:center;">
      <label style="font-weight:600; color:var(--text-muted); font-size:0.9rem;">Trier par :</label>
      <?php
      $sortBy = $_GET['sort_by'] ?? 'nom';
      $sortOrder = $_GET['sort_order'] ?? 'asc';
      ?>
      <select onchange="window.location.href='index.php?page=admin_categories&sort_by=' + this.value + '&sort_order=<?= $sortOrder ?>'"
              style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:0.5rem 1rem; font-size:0.9rem; cursor:pointer; color:var(--text-primary);">
        <option value="nom" <?= $sortBy === 'nom' ? 'selected' : '' ?>>Nom (A-Z)</option>
        <option value="services" <?= $sortBy === 'services' ? 'selected' : '' ?>>Nombre de services</option>
        <option value="date" <?= $sortBy === 'date' ? 'selected' : '' ?>>Date de creation</option>
      </select>
      <a href="index.php?page=admin_categories&sort_by=<?= $sortBy ?>&sort_order=<?= $sortOrder === 'asc' ? 'desc' : 'asc' ?>"
         style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:0.5rem 0.75rem; font-size:0.9rem; cursor:pointer; color:var(--text-primary); text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
        <i class="fas fa-arrow-<?= $sortOrder === 'asc' ? 'up' : 'down' ?>"></i>
      </a>
    </div>

    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px,1fr)); gap:1.5rem;">
      <?php
      usort($categories, function ($a, $b) {
          $sortBy = $_GET['sort_by'] ?? 'nom';
          $sortOrder = $_GET['sort_order'] ?? 'asc';

          if ($sortBy === 'nom') {
              $result = strcasecmp($a['nom_categorie'], $b['nom_categorie']);
          } elseif ($sortBy === 'services') {
              $result = $a['nb_services'] <=> $b['nb_services'];
          } elseif ($sortBy === 'date') {
              $result = strtotime($a['created_at'] ?? 0) <=> strtotime($b['created_at'] ?? 0);
          } else {
              $result = 0;
          }

          return $sortOrder === 'desc' ? -$result : $result;
      });

      foreach ($categories as $cat): ?>
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
             class="admin-btn admin-btn-danger admin-btn-sm js-swal-confirm"
             data-swal-title="Supprimer cette categorie ?"
             data-swal-text="Categorie : <?= htmlspecialchars($cat['nom_categorie'], ENT_QUOTES) ?>"
             data-swal-confirm="Oui, supprimer"
             data-swal-cancel="Annuler">
            <i class="fas fa-trash"></i>
          </a>
        </div>
      </div>
      <?php endforeach; ?>

      <a href="index.php?page=admin_categorie_create"
         style="background:var(--bg-card); border:2px dashed var(--border); border-radius:var(--radius-lg); padding:2rem; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; text-decoration:none; color:var(--text-muted); transition:all 0.2s; min-height:180px; gap:0.5rem;"
         onmouseover="this.style.borderColor='var(--accent)'; this.style.color='var(--accent-light)'"
         onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-muted)'">
        <i class="fas fa-plus-circle" style="font-size:2rem; margin-bottom:0.5rem;"></i>
        <div style="font-weight:600;">Nouvelle categorie</div>
      </a>
    </div>

  </div>
</main>

<?php if ($success && isset($messages[$success])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  Swal.fire({
    icon: 'success',
    title: 'Operation reussie',
    text: <?= json_encode($messages[$success]) ?>,
    confirmButtonColor: '#2563eb'
  });
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
