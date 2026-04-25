<?php
$pageTitle = 'Dashboard - Admin Geeks';

require_once __DIR__ . '/../../controllers/ServiceController.php';
require_once __DIR__ . '/../../controllers/CategorieController.php';

$serviceController = new ServiceController();
$categorieController = new CategorieController();

$sStats = $serviceController->getStats();
$recentServices = $serviceController->listAll(null, null, null);
$categories = $categorieController->listCategories();

include __DIR__ . '/sidebar.php';
?>

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title">Dashboard</div>
      <div class="topbar-bread">Geeks Admin &rsaquo; <span style="color:var(--text-secondary)">Vue d ensemble</span></div>
    </div>
    <div class="topbar-actions">
      <a href="index.php?page=admin_categories" class="topbar-btn topbar-btn-outline">
        <i class="fas fa-tags"></i> Categories
      </a>
      <a href="index.php?page=admin_services" class="topbar-btn topbar-btn-primary">
        <i class="fas fa-briefcase"></i> Services
        <?php if (($sStats['en_attente'] ?? 0) > 0): ?>
        <span style="background:white; color:var(--accent); border-radius:20px; padding:1px 7px; font-size:0.72rem; margin-left:4px;"><?= $sStats['en_attente'] ?></span>
        <?php endif; ?>
      </a>
    </div>
  </div>

  <div class="admin-content">

    <div class="stats-grid">
      <div class="stat-widget purple">
        <div class="sw-icon"><i class="fas fa-briefcase"></i></div>
        <div class="sw-value"><?= $sStats['total'] ?? 0 ?></div>
        <div class="sw-label">Total Services</div>
      </div>
      <div class="stat-widget green">
        <div class="sw-icon"><i class="fas fa-check-circle"></i></div>
        <div class="sw-value"><?= $sStats['actif'] ?? 0 ?></div>
        <div class="sw-label">Services Actifs</div>
      </div>
      <div class="stat-widget orange">
        <div class="sw-icon"><i class="fas fa-clock"></i></div>
        <div class="sw-value"><?= $sStats['en_attente'] ?? 0 ?></div>
        <div class="sw-label">En Attente d approbation</div>
      </div>
      <div class="stat-widget blue">
        <div class="sw-icon"><i class="fas fa-tags"></i></div>
        <div class="sw-value"><?= count($categories) ?></div>
        <div class="sw-label">Categories</div>
      </div>
    </div>

    <div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem; margin-bottom:2rem;">

      <div class="admin-table-wrap">
        <div class="admin-table-header">
          <div class="admin-table-title">Services Recents</div>
          <a href="index.php?page=admin_services" class="topbar-btn topbar-btn-outline" style="font-size:0.78rem; padding:6px 12px;">Voir tout</a>
        </div>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Service</th>
              <th>Categorie</th>
              <th>Prix</th>
              <th>Statut</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (array_slice($recentServices, 0, 6) as $s): ?>
            <tr>
              <td>
                <div class="table-service-name"><?= htmlspecialchars(substr($s['titre'], 0, 35)) ?>...</div>
              </td>
              <td style="color:var(--text-secondary); font-size:0.82rem;"><?= htmlspecialchars($s['nom_categorie']) ?></td>
              <td style="font-weight:700; color:var(--success);"><?= number_format($s['prix'], 2) ?> DT</td>
              <td>
                <?php
                $bmap = ['actif' => 'badge-actif', 'suspendu' => 'badge-suspendu', 'en_attente' => 'badge-pending'];
                $lmap = ['actif' => 'Actif', 'suspendu' => 'Suspendu', 'en_attente' => 'En attente'];
                ?>
                <span class="badge <?= $bmap[$s['statut']] ?? 'badge-pending' ?>">
                  <?= $lmap[$s['statut']] ?? htmlspecialchars($s['statut']) ?>
                </span>
              </td>
              <td>
                <?php if ($s['statut'] === 'en_attente'): ?>
                <a href="index.php?page=admin_service_statut&id=<?= $s['id_service'] ?>&statut=actif"
                   class="admin-btn admin-btn-success admin-btn-sm">
                  <i class="fas fa-check"></i> Approuver
                </a>
                <?php elseif ($s['statut'] === 'actif'): ?>
                <a href="index.php?page=admin_service_statut&id=<?= $s['id_service'] ?>&statut=suspendu"
                   class="admin-btn admin-btn-danger admin-btn-sm">
                  <i class="fas fa-ban"></i> Suspendre
                </a>
                <?php else: ?>
                <a href="index.php?page=admin_service_statut&id=<?= $s['id_service'] ?>&statut=actif"
                   class="admin-btn admin-btn-warning admin-btn-sm">
                  <i class="fas fa-undo"></i> Reactiver
                </a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="admin-table-wrap">
        <div class="admin-table-header">
          <div class="admin-table-title">Categories</div>
          <a href="index.php?page=admin_categorie_create" class="topbar-btn topbar-btn-primary" style="font-size:0.78rem; padding:6px 12px;">
            <i class="fas fa-plus"></i>
          </a>
        </div>
        <div style="padding:0.5rem;">
          <?php foreach ($categories as $cat): ?>
          <div style="display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:8px; transition:background 0.2s;" onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background=''">
            <div style="width:32px; height:32px; background:var(--accent-glow); border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
              <i class="<?= htmlspecialchars($cat['icone']) ?>" style="color:var(--accent-light); font-size:0.85rem;"></i>
            </div>
            <div style="flex:1;">
              <div style="font-size:0.875rem; font-weight:600;"><?= htmlspecialchars($cat['nom_categorie']) ?></div>
              <div style="font-size:0.75rem; color:var(--text-muted);"><?= $cat['nb_services'] ?> service(s)</div>
            </div>
            <div style="display:flex; gap:4px;">
              <a href="index.php?page=admin_categorie_edit&id=<?= $cat['id_categorie'] ?>" style="color:var(--text-muted); font-size:0.85rem; transition:color 0.2s;" onmouseover="this.style.color='var(--accent-light)'" onmouseout="this.style.color='var(--text-muted)'"><i class="fas fa-edit"></i></a>
              <a href="index.php?page=admin_categorie_delete&id=<?= $cat['id_categorie'] ?>"
                 class="js-swal-confirm"
                 data-swal-title="Supprimer cette categorie ?"
                 data-swal-text="Categorie : <?= htmlspecialchars($cat['nom_categorie'], ENT_QUOTES) ?>"
                 data-swal-confirm="Oui, supprimer"
                 data-swal-cancel="Annuler"
                 style="color:var(--text-muted); font-size:0.85rem; transition:color 0.2s;"
                 onmouseover="this.style.color='var(--danger)'"
                 onmouseout="this.style.color='var(--text-muted)'"><i class="fas fa-trash"></i></a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

  </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
