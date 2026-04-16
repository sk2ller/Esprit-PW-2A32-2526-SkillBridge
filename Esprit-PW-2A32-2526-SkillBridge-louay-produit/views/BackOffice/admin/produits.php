<?php
$pageTitle = 'Produits - Admin SkillBridge';
include __DIR__ . '/../partials/sidebar.php';
?>

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title">Gestion des Produits</div>
      <div class="topbar-bread">
        <a href="index.php?page=admin_dashboard">Dashboard</a> › Produits
      </div>
    </div>
    <div class="topbar-actions">
      <span style="color:var(--text-muted); font-size:0.875rem;">
        <i class="fas fa-box"></i> <?= $stats['total'] ?? 0 ?> produits total
      </span>
    </div>
  </div>

  <div class="admin-content">

    <?php if (!empty($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Statut du produit mis à jour.</div>
    <?php endif; ?>

    <!-- Stats mini -->
    <div style="display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap;">
      <?php
      $statItems = [
        ['label'=>'Total','val'=>$stats['total']??0,'color'=>'var(--accent-light)','icon'=>'fa-layer-group'],
        ['label'=>'Disponibles','val'=>$stats['disponible']??0,'color'=>'var(--success)','icon'=>'fa-check-circle'],
        ['label'=>'En attente','val'=>$stats['en_attente']??0,'color'=>'var(--warning)','icon'=>'fa-clock'],
        ['label'=>'Rupture','val'=>$stats['rupture']??0,'color'=>'var(--danger)','icon'=>'fa-ban'],
      ];
      foreach ($statItems as $si): ?>
      <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:1rem 1.5rem; display:flex; align-items:center; gap:12px; min-width:160px;">
        <i class="fas <?= $si['icon'] ?>" style="color:<?= $si['color'] ?>; font-size:1.2rem;"></i>
        <div>
          <div style="font-size:1.4rem; font-weight:800; color:<?= $si['color'] ?>;"><?= $si['val'] ?></div>
          <div style="font-size:0.78rem; color:var(--text-muted);"><?= $si['label'] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Filter tabs -->
    <div style="display:flex; gap:4px; margin-bottom:1.5rem; background:var(--bg-card); padding:4px; border-radius:10px; width:fit-content; border:1px solid var(--border);">
      <?php
      $filterStatut = $_GET['filter'] ?? 'all';
      $filters = ['all'=>'Tous','en_attente'=>'En attente','disponible'=>'Disponibles','rupture'=>'Rupture'];
      foreach ($filters as $fval => $flabel): ?>
      <a href="index.php?page=admin_produits&filter=<?= $fval ?>"
         style="padding:7px 16px; border-radius:7px; font-size:0.82rem; font-weight:600; text-decoration:none; transition:all 0.2s; color:<?= $filterStatut === $fval ? 'white' : 'var(--text-muted)' ?>; background:<?= $filterStatut === $fval ? 'var(--accent)' : 'transparent' ?>;">
        <?= $flabel ?>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="admin-table-wrap">
      <div class="admin-table-header">
        <div class="admin-table-title">Liste des Produits</div>
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Produit</th>
            <th>Catégorie</th>
            <th>Prix</th>
            <th>Stock</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $filtered = $produits;
          if ($filterStatut !== 'all') {
              $filtered = array_filter($produits, fn($p) => $p['statut'] === $filterStatut);
          }
          if (empty($filtered)): ?>
          <tr><td colspan="7" style="text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:0.5rem;"></i>
            Aucun produit dans cette catégorie
          </td></tr>
          <?php else: ?>
          <?php foreach ($filtered as $p): ?>
          <tr>
            <td style="color:var(--text-muted); font-size:0.8rem;">#<?= $p['id_produit'] ?></td>
            <td>
              <div class="table-product-name"><?= htmlspecialchars(substr($p['nom'], 0, 40)) ?>...</div>
              <div class="table-product-meta"><?= date('d/m/Y', strtotime($p['created_at'])) ?></div>
            </td>
            <td>
              <span style="background:var(--accent-glow); color:var(--accent-light); padding:3px 10px; border-radius:20px; font-size:0.75rem;">
                <?= htmlspecialchars($p['nom_categorie']) ?>
              </span>
            </td>
            <td style="font-weight:700; color:var(--success);"><?= number_format($p['prix'], 2) ?> DT</td>
            <td style="color:var(--text-muted); font-size:0.82rem;"><?= $p['quantite'] ?></td>
            <td>
              <?php
              $bmap = ['disponible'=>'badge-disponible','rupture'=>'badge-rupture','en_attente'=>'badge-pending'];
              $lmap = ['disponible'=>'✓ Disponible','rupture'=>'✗ Rupture','en_attente'=>'⏳ En attente'];
              ?>
              <span class="badge <?= $bmap[$p['statut']] ?>"><?= $lmap[$p['statut']] ?></span>
            </td>
            <td>
              <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <?php if ($p['statut'] === 'en_attente'): ?>
                <a href="index.php?page=admin_produit_statut&id=<?= $p['id_produit'] ?>&statut=disponible"
                   class="admin-btn admin-btn-success admin-btn-sm">
                  <i class="fas fa-check"></i> Approuver
                </a>
                <a href="index.php?page=admin_produit_statut&id=<?= $p['id_produit'] ?>&statut=rupture"
                   class="admin-btn admin-btn-danger admin-btn-sm">
                  <i class="fas fa-ban"></i> Refuser
                </a>
                <?php elseif ($p['statut'] === 'disponible'): ?>
                <a href="index.php?page=admin_produit_statut&id=<?= $p['id_produit'] ?>&statut=rupture"
                   class="admin-btn admin-btn-danger admin-btn-sm">
                  <i class="fas fa-ban"></i> Rupture
                </a>
                <?php else: ?>
                <a href="index.php?page=admin_produit_statut&id=<?= $p['id_produit'] ?>&statut=disponible"
                   class="admin-btn admin-btn-warning admin-btn-sm">
                  <i class="fas fa-undo"></i> Réactiver
                </a>
                <?php endif; ?>
                <a href="index.php?page=produit_detail&id=<?= $p['id_produit'] ?>" target="_blank"
                   class="admin-btn admin-btn-outline admin-btn-sm" title="Voir">
                  <i class="fas fa-eye"></i>
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
