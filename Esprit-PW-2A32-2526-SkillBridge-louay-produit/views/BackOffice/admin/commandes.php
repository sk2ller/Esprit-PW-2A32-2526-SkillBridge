<?php
$pageTitle = 'Commandes - Admin SkillBridge';
include __DIR__ . '/../partials/sidebar.php';
?>

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title">Gestion des Commandes</div>
      <div class="topbar-bread">
        <a href="index.php?page=admin_dashboard">Dashboard</a> › Commandes
      </div>
    </div>
    <div class="topbar-actions">
      <span style="color:var(--text-muted); font-size:0.875rem;">
        <i class="fas fa-receipt"></i> <?= $cStats['total'] ?? 0 ?> commandes total
      </span>
    </div>
  </div>

  <div class="admin-content">

    <?php if (!empty($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Statut de la commande mis à jour.</div>
    <?php endif; ?>

    <!-- Stats mini -->
    <div style="display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap;">
      <?php
      $statItems = [
        ['label'=>'Total','val'=>$cStats['total']??0,'color'=>'var(--accent-light)','icon'=>'fa-receipt'],
        ['label'=>'En attente','val'=>$cStats['en_attente']??0,'color'=>'var(--warning)','icon'=>'fa-clock'],
        ['label'=>'Confirmées','val'=>$cStats['confirmee']??0,'color'=>'#60a5fa','icon'=>'fa-check'],
        ['label'=>'Expédiées','val'=>$cStats['expediee']??0,'color'=>'#818cf8','icon'=>'fa-truck'],
        ['label'=>'Livrées','val'=>$cStats['livree']??0,'color'=>'var(--success)','icon'=>'fa-check-double'],
        ['label'=>'Annulées','val'=>$cStats['annulee']??0,'color'=>'var(--danger)','icon'=>'fa-ban'],
      ];
      foreach ($statItems as $si): ?>
      <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:1rem 1.2rem; display:flex; align-items:center; gap:10px; min-width:130px;">
        <i class="fas <?= $si['icon'] ?>" style="color:<?= $si['color'] ?>; font-size:1rem;"></i>
        <div>
          <div style="font-size:1.2rem; font-weight:800; color:<?= $si['color'] ?>;"><?= $si['val'] ?></div>
          <div style="font-size:0.72rem; color:var(--text-muted);"><?= $si['label'] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Filter tabs -->
    <div style="display:flex; gap:4px; margin-bottom:1.5rem; background:var(--bg-card); padding:4px; border-radius:10px; width:fit-content; border:1px solid var(--border);">
      <?php
      $filterStatut = $_GET['filter'] ?? 'all';
      $filters = ['all'=>'Tous','en_attente'=>'En attente','confirmee'=>'Confirmées','expediee'=>'Expédiées','livree'=>'Livrées','annulee'=>'Annulées'];
      foreach ($filters as $fval => $flabel): ?>
      <a href="index.php?page=admin_commandes&filter=<?= $fval ?>"
         style="padding:7px 14px; border-radius:7px; font-size:0.78rem; font-weight:600; text-decoration:none; transition:all 0.2s; color:<?= $filterStatut === $fval ? 'white' : 'var(--text-muted)' ?>; background:<?= $filterStatut === $fval ? 'var(--accent)' : 'transparent' ?>;">
        <?= $flabel ?>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="admin-table-wrap">
      <div class="admin-table-header">
        <div class="admin-table-title">Liste des Commandes</div>
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Client</th>
            <th>Produit</th>
            <th>Qté</th>
            <th>Total</th>
            <th>Date</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $filtered = $commandes;
          if ($filterStatut !== 'all') {
              $filtered = array_filter($commandes, fn($c) => $c->getStatut() === $filterStatut);
          }
          if (empty($filtered)): ?>
          <tr><td colspan="8" style="text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:0.5rem;"></i>
            Aucune commande dans cette catégorie
          </td></tr>
          <?php else: ?>
          <?php foreach ($filtered as $cmd): ?>
          <?php
          $bmap = ['en_attente'=>'badge-pending','confirmee'=>'badge-info','expediee'=>'badge-info','livree'=>'badge-disponible','annulee'=>'badge-rupture'];
          $lmap = ['en_attente'=>'⏳ En attente','confirmee'=>'✓ Confirmée','expediee'=>'🚚 Expédiée','livree'=>'✅ Livrée','annulee'=>'✗ Annulée'];
          ?>
          <tr>
            <td style="color:var(--text-muted); font-size:0.8rem;">#<?= $cmd->getId() ?></td>
            <td>
              <div class="table-product-name"><?= htmlspecialchars($cmd->getNomClient()) ?></div>
              <div class="table-product-meta"><?= htmlspecialchars($cmd->getEmailClient()) ?></div>
            </td>
            <td>
              <span style="background:var(--accent-glow); color:var(--accent-light); padding:3px 10px; border-radius:20px; font-size:0.75rem;">
                <?= htmlspecialchars(substr($cmd->getNomProduit(), 0, 25)) ?>
              </span>
            </td>
            <td style="color:var(--text-muted); font-size:0.85rem;"><?= $cmd->getQuantite() ?></td>
            <td style="font-weight:700; color:var(--success);"><?= number_format($cmd->getPrixTotal(), 2) ?> DT</td>
            <td style="color:var(--text-muted); font-size:0.8rem;"><?= date('d/m/Y', strtotime($cmd->getCreatedAt())) ?></td>
            <td>
              <span class="badge <?= $bmap[$cmd->getStatut()] ?>"><?= $lmap[$cmd->getStatut()] ?></span>
            </td>
            <td>
              <div style="display:flex; gap:4px; flex-wrap:wrap;">
                <?php if ($cmd->getStatut() === 'en_attente'): ?>
                <a href="index.php?page=admin_commande_statut&id=<?= $cmd->getId() ?>&statut=confirmee"
                   class="admin-btn admin-btn-success admin-btn-sm" title="Confirmer">
                  <i class="fas fa-check"></i>
                </a>
                <a href="index.php?page=admin_commande_statut&id=<?= $cmd->getId() ?>&statut=annulee"
                   class="admin-btn admin-btn-danger admin-btn-sm" title="Annuler">
                  <i class="fas fa-ban"></i>
                </a>
                <?php elseif ($cmd->getStatut() === 'confirmee'): ?>
                <a href="index.php?page=admin_commande_statut&id=<?= $cmd->getId() ?>&statut=expediee"
                   class="admin-btn admin-btn-primary admin-btn-sm" title="Expédier">
                  <i class="fas fa-truck"></i>
                </a>
                <?php elseif ($cmd->getStatut() === 'expediee'): ?>
                <a href="index.php?page=admin_commande_statut&id=<?= $cmd->getId() ?>&statut=livree"
                   class="admin-btn admin-btn-success admin-btn-sm" title="Marquer livrée">
                  <i class="fas fa-check-double"></i>
                </a>
                <?php endif; ?>
                <a href="index.php?page=admin_commande_delete&id=<?= $cmd->getId() ?>"
                   class="admin-btn admin-btn-danger admin-btn-sm" title="Supprimer"
                   onclick="return confirm('Supprimer cette commande ?')">
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
