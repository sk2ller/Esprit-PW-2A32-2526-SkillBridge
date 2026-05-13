<?php
$pageTitle = 'Commandes - Admin Geeks';
include __DIR__ . '/sidebar.php';
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
        <i class="fas fa-shopping-bag"></i> <?= count($commandes) ?> commandes
      </span>
    </div>
  </div>

  <div class="admin-content">

    <div class="admin-table-wrap">
      <div class="admin-table-header">
        <div class="admin-table-title">Toutes les Commandes</div>
      </div>
      <?php if (empty($commandes)): ?>
      <div style="text-align:center; padding:4rem; color:var(--text-muted);">
        <i class="fas fa-inbox" style="font-size:3rem; display:block; margin-bottom:1rem;"></i>
        Aucune commande pour l'instant
      </div>
      <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th><?php 
              $isSort = $_GET['sort_by'] ?? '' === 'id';
              $nextOrder = $isSort && ($_GET['sort_order'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
            ?>
            <a href="index.php?page=admin_commandes&sort_by=id&sort_order=<?= $nextOrder ?>" 
               style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:4px;">
              # <?php if ($isSort): ?><i class="fas fa-arrow-<?= ($_GET['sort_order'] ?? 'asc') === 'asc' ? 'up' : 'down' ?>"></i><?php endif; ?>
            </a>
            </th>
            <th><?php 
              $isSort = $_GET['sort_by'] ?? '' === 'service';
              $nextOrder = $isSort && ($_GET['sort_order'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
            ?>
            <a href="index.php?page=admin_commandes&sort_by=service&sort_order=<?= $nextOrder ?>" 
               style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:4px;">
              Service <?php if ($isSort): ?><i class="fas fa-arrow-<?= ($_GET['sort_order'] ?? 'asc') === 'asc' ? 'up' : 'down' ?>"></i><?php endif; ?>
            </a>
            </th>
            <th><?php 
              $isSort = $_GET['sort_by'] ?? '' === 'client';
              $nextOrder = $isSort && ($_GET['sort_order'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
            ?>
            <a href="index.php?page=admin_commandes&sort_by=client&sort_order=<?= $nextOrder ?>" 
               style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:4px;">
              Client <?php if ($isSort): ?><i class="fas fa-arrow-<?= ($_GET['sort_order'] ?? 'asc') === 'asc' ? 'up' : 'down' ?>"></i><?php endif; ?>
            </a>
            </th>
            <th><?php 
              $isSort = $_GET['sort_by'] ?? '' === 'freelancer';
              $nextOrder = $isSort && ($_GET['sort_order'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
            ?>
            <a href="index.php?page=admin_commandes&sort_by=freelancer&sort_order=<?= $nextOrder ?>" 
               style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:4px;">
              Freelancer <?php if ($isSort): ?><i class="fas fa-arrow-<?= ($_GET['sort_order'] ?? 'asc') === 'asc' ? 'up' : 'down' ?>"></i><?php endif; ?>
            </a>
            </th>
            <th><?php 
              $isSort = $_GET['sort_by'] ?? '' === 'montant';
              $nextOrder = $isSort && ($_GET['sort_order'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
            ?>
            <a href="index.php?page=admin_commandes&sort_by=montant&sort_order=<?= $nextOrder ?>" 
               style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:4px;">
              Montant <?php if ($isSort): ?><i class="fas fa-arrow-<?= ($_GET['sort_order'] ?? 'asc') === 'asc' ? 'up' : 'down' ?>"></i><?php endif; ?>
            </a>
            </th>
            <th><?php 
              $isSort = $_GET['sort_by'] ?? '' === 'statut';
              $nextOrder = $isSort && ($_GET['sort_order'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
            ?>
            <a href="index.php?page=admin_commandes&sort_by=statut&sort_order=<?= $nextOrder ?>" 
               style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:4px;">
              Statut <?php if ($isSort): ?><i class="fas fa-arrow-<?= ($_GET['sort_order'] ?? 'asc') === 'asc' ? 'up' : 'down' ?>"></i><?php endif; ?>
            </a>
            </th>
            <th><?php 
              $isSort = $_GET['sort_by'] ?? '' === 'date';
              $nextOrder = $isSort && ($_GET['sort_order'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
            ?>
            <a href="index.php?page=admin_commandes&sort_by=date&sort_order=<?= $nextOrder ?>" 
               style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:4px;">
              Date <?php if ($isSort): ?><i class="fas fa-arrow-<?= ($_GET['sort_order'] ?? 'asc') === 'asc' ? 'up' : 'down' ?>"></i><?php endif; ?>
            </a>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php 
          // Tri des commandes
          $sortBy = $_GET['sort_by'] ?? 'id';
          $sortOrder = $_GET['sort_order'] ?? 'asc';
          usort($commandes, function($a, $b) use ($sortBy, $sortOrder) {
              $keyMap = [
                  'id' => 'id_commande',
                  'service' => 'titre',
                  'client' => 'client_nom',
                  'freelancer' => 'freelancer_nom',
                  'montant' => 'prix_total',
                  'statut' => 'statut',
                  'date' => 'created_at'
              ];
              $key = $keyMap[$sortBy] ?? 'id_commande';
              $aVal = $a[$key];
              $bVal = $b[$key];
              
              if (is_numeric($aVal) && is_numeric($bVal)) {
                  $result = $aVal <=> $bVal;
              } else {
                  $result = strcasecmp($aVal, $bVal);
              }
              return $sortOrder === 'desc' ? -$result : $result;
          });
          foreach ($commandes as $c): ?>
          <tr>
            <td style="color:var(--text-muted); font-size:0.8rem;">#<?= $c['id_commande'] ?></td>
            <td>
              <div class="table-service-name"><?= htmlspecialchars(substr($c['titre'], 0, 35)) ?>...</div>
            </td>
            <td>
              <div style="display:flex; align-items:center; gap:8px;">
                <div class="table-avatar" style="background:var(--info);"><?= strtoupper(substr($c['client_nom'], 0, 1)) ?></div>
                <span style="font-size:0.875rem;"><?= htmlspecialchars($c['client_nom']) ?></span>
              </div>
            </td>
            <td>
              <div style="display:flex; align-items:center; gap:8px;">
                <div class="table-avatar"><?= strtoupper(substr($c['freelancer_nom'], 0, 1)) ?></div>
                <span style="font-size:0.875rem;"><?= htmlspecialchars($c['freelancer_nom']) ?></span>
              </div>
            </td>
            <td style="font-weight:700; color:var(--success);"><?= number_format($c['prix_total'], 2) ?> DT</td>
            <td>
              <?php
              $bmap = ['en_attente'=>'badge-pending','en_cours'=>'badge-info','livree'=>'badge-actif','annulee'=>'badge-suspendu'];
              $lmap = ['en_attente'=>'En attente','en_cours'=>'En cours','livree'=>'Livrée','annulee'=>'Annulée'];
              ?>
              <span class="badge <?= $bmap[$c['statut']] ?? 'badge-pending' ?>">
                <?= $lmap[$c['statut']] ?? $c['statut'] ?>
              </span>
            </td>
            <td style="color:var(--text-muted); font-size:0.82rem;"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

  </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
