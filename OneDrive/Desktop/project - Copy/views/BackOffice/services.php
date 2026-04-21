<?php
$pageTitle = 'Gestion des Services - Admin Geeks';
include __DIR__ . '/sidebar.php';
?>

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title">Services</div>
      <div class="topbar-bread">Geeks Admin › <span style="color:var(--text-secondary)">Gestion des services</span></div>
    </div>
  </div>

  <div class="admin-content">

    <div class="stats-grid" style="margin-bottom:1.5rem;">
      <div class="stat-widget purple">
        <div class="sw-icon"><i class="fas fa-briefcase"></i></div>
        <div class="sw-value"><?= $stats['total'] ?? 0 ?></div>
        <div class="sw-label">Total</div>
      </div>
      <div class="stat-widget green">
        <div class="sw-icon"><i class="fas fa-check-circle"></i></div>
        <div class="sw-value"><?= $stats['actif'] ?? 0 ?></div>
        <div class="sw-label">Actifs</div>
      </div>
      <div class="stat-widget orange">
        <div class="sw-icon"><i class="fas fa-clock"></i></div>
        <div class="sw-value"><?= $stats['en_attente'] ?? 0 ?></div>
        <div class="sw-label">En attente</div>
      </div>
      <div class="stat-widget blue">
        <div class="sw-icon"><i class="fas fa-ban"></i></div>
        <div class="sw-value"><?= ($stats['suspendu'] ?? 0) + ($stats['rejete'] ?? 0) + ($stats['rejetee'] ?? 0) ?></div>
        <div class="sw-label">Suspendus / Rejetés</div>
      </div>
    </div>

    <div class="admin-table-wrap">
      <div class="admin-table-header" style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;">
        <div class="admin-table-title">Liste des services</div>

        <form method="GET" action="index.php" style="display:flex; gap:0.75rem; align-items:end; flex-wrap:wrap;">
          <input type="hidden" name="page" value="admin_services">

          <div>
            <label style="display:block; margin-bottom:6px; font-size:0.82rem; font-weight:600;">État</label>
            <select name="statut" class="form-control">
              <option value="">Tous</option>
              <option value="actif" <?= (($_GET['statut'] ?? '') === 'actif') ? 'selected' : '' ?>>Actif</option>
              <option value="en_attente" <?= (($_GET['statut'] ?? '') === 'en_attente') ? 'selected' : '' ?>>En attente</option>
              <option value="suspendu" <?= (($_GET['statut'] ?? '') === 'suspendu') ? 'selected' : '' ?>>Suspendu</option>
              <option value="rejete" <?= (($_GET['statut'] ?? '') === 'rejete') ? 'selected' : '' ?>>Rejeté</option>
              <option value="rejetee" <?= (($_GET['statut'] ?? '') === 'rejetee') ? 'selected' : '' ?>>Rejetée</option>
            </select>
          </div>

          <div>
            <label style="display:block; margin-bottom:6px; font-size:0.82rem; font-weight:600;">Tri</label>
            <select name="sort" class="form-control">
              <option value="recent" <?= (($_GET['sort'] ?? 'recent') === 'recent') ? 'selected' : '' ?>>Plus récents</option>
              <option value="ancien" <?= (($_GET['sort'] ?? '') === 'ancien') ? 'selected' : '' ?>>Plus anciens</option>
              <option value="prix_asc" <?= (($_GET['sort'] ?? '') === 'prix_asc') ? 'selected' : '' ?>>Prix croissant</option>
              <option value="prix_desc" <?= (($_GET['sort'] ?? '') === 'prix_desc') ? 'selected' : '' ?>>Prix décroissant</option>
              <option value="statut_asc" <?= (($_GET['sort'] ?? '') === 'statut_asc') ? 'selected' : '' ?>>État A-Z</option>
              <option value="statut_desc" <?= (($_GET['sort'] ?? '') === 'statut_desc') ? 'selected' : '' ?>>État Z-A</option>
            </select>
          </div>

          <button type="submit" class="topbar-btn topbar-btn-primary">
            <i class="fas fa-filter"></i> Appliquer
          </button>

          <a href="index.php?page=admin_services" class="topbar-btn topbar-btn-outline">
            Réinitialiser
          </a>
        </form>
      </div>

      <table class="admin-table">
        <thead>
          <tr>
            <th>Service</th>
            <th>Catégorie</th>
            <th>Prix</th>
            <th>Délai</th>
            <th>État</th>
            <th>Documents</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($services)): ?>
          <tr>
            <td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted);">
              Aucun service trouvé.
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($services as $s): ?>
          <tr>
            <td>
              <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:56px; height:42px; border-radius:10px; overflow:hidden; background:linear-gradient(135deg,#5c6f86,#d9d9d9); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                  <?php if (!empty($s['thumbnail'])): ?>
                  <img src="views/assets/uploads/<?= htmlspecialchars($s['thumbnail']) ?>" alt="<?= htmlspecialchars($s['titre']) ?>" style="width:100%; height:100%; object-fit:cover;">
                  <?php else: ?>
                  <i class="fas fa-image" style="color:rgba(255,255,255,0.45);"></i>
                  <?php endif; ?>
                </div>
                <div>
                  <div class="table-service-name"><?= htmlspecialchars($s['titre']) ?></div>
                  <div style="color:var(--text-muted); font-size:0.78rem;">
                    <?= htmlspecialchars(mb_strimwidth($s['description'], 0, 60, '...')) ?>
                  </div>
                </div>
              </div>
            </td>

            <td style="color:var(--text-secondary); font-size:0.82rem;">
              <?= htmlspecialchars($s['nom_categorie']) ?>
            </td>

            <td style="font-weight:700; color:var(--success);">
              <?= number_format($s['prix'], 2) ?> DT
            </td>

            <td style="color:var(--text-secondary);">
              <?= (int) $s['delai_livraison'] ?> jours
            </td>

            <td>
              <?php
              $bmap = [
                  'actif' => 'badge-actif',
                  'suspendu' => 'badge-suspendu',
                  'en_attente' => 'badge-pending',
                  'rejete' => 'badge-suspendu',
                  'rejetee' => 'badge-suspendu'
              ];
              $lmap = [
                  'actif' => 'Actif',
                  'suspendu' => 'Suspendu',
                  'en_attente' => 'En attente',
                  'rejete' => 'Rejeté',
                  'rejetee' => 'Rejetée'
              ];
              ?>
              <span class="badge <?= $bmap[$s['statut']] ?? 'badge-pending' ?>">
                <?= $lmap[$s['statut']] ?? htmlspecialchars($s['statut']) ?>
              </span>
            </td>

            <td>
              <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <?php if (!empty($s['cv'])): ?>
                <a href="views/assets/uploads/<?= urlencode($s['cv']) ?>" download class="admin-btn admin-btn-sm admin-btn-outline">
                  CV
                </a>
                <?php endif; ?>

                <?php if (!empty($s['portfolio'])): ?>
                <a href="views/assets/uploads/<?= urlencode($s['portfolio']) ?>" download class="admin-btn admin-btn-sm admin-btn-outline">
                  Portfolio
                </a>
                <?php endif; ?>

                <?php if (empty($s['cv']) && empty($s['portfolio'])): ?>
                <span style="color:var(--text-muted); font-size:0.8rem;">Aucun</span>
                <?php endif; ?>
              </div>
            </td>

            <td>
              <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="index.php?page=service_detail&id=<?= $s['id_service'] ?>" target="_blank" class="admin-btn admin-btn-sm admin-btn-outline">
                  <i class="fas fa-eye"></i> Voir
                </a>

                <?php if ($s['statut'] === 'en_attente'): ?>
                <a href="index.php?page=admin_service_statut&id=<?= $s['id_service'] ?>&statut=actif" class="admin-btn admin-btn-success admin-btn-sm">
                  <i class="fas fa-check"></i> Approuver
                </a>
                <?php elseif ($s['statut'] === 'actif'): ?>
                <a href="index.php?page=admin_service_statut&id=<?= $s['id_service'] ?>&statut=suspendu" class="admin-btn admin-btn-danger admin-btn-sm">
                  <i class="fas fa-ban"></i> Suspendre
                </a>
                <?php else: ?>
                <a href="index.php?page=admin_service_statut&id=<?= $s['id_service'] ?>&statut=actif" class="admin-btn admin-btn-warning admin-btn-sm">
                  <i class="fas fa-undo"></i> Réactiver
                </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
