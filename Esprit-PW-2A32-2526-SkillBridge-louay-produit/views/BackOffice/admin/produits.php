<?php
$pageTitle = 'Produits - Admin SkillBridge';
include __DIR__ . '/../partials/sidebar.php';

// Tri (Sorting) parameters
$sortBy = $_GET['sort'] ?? 'date';
$sortOrder = $_GET['order'] ?? 'desc';
$searchQuery = $_GET['search'] ?? '';
$filterStatut = $_GET['filter'] ?? 'all';

// Apply filters
$filtered = $produits;
if ($filterStatut !== 'all') {
    $filtered = array_filter($produits, fn($p) => $p->getStatut() === $filterStatut);
}
// Apply search
if (!empty($searchQuery)) {
    $filtered = array_filter($filtered, function($p) use ($searchQuery) {
        return stripos($p->getNom(), $searchQuery) !== false 
            || stripos($p->getDescription(), $searchQuery) !== false
            || stripos($p->getNomCategorie(), $searchQuery) !== false;
    });
}
// Apply sorting
$filtered = array_values($filtered);
usort($filtered, function($a, $b) use ($sortBy, $sortOrder) {
    switch ($sortBy) {
        case 'nom':   $cmp = strcmp($a->getNom(), $b->getNom()); break;
        case 'prix':  $cmp = $a->getPrix() <=> $b->getPrix(); break;
        case 'stock': $cmp = $a->getQuantite() <=> $b->getQuantite(); break;
        case 'date':
        default:      $cmp = strcmp($a->getCreatedAt(), $b->getCreatedAt()); break;
    }
    return $sortOrder === 'asc' ? $cmp : -$cmp;
});

// Helper to build sort URL
function sortUrl($col, $currentSort, $currentOrder, $filter, $search) {
    $newOrder = ($currentSort === $col && $currentOrder === 'asc') ? 'desc' : 'asc';
    $url = "index.php?page=admin_produits&sort=$col&order=$newOrder";
    if ($filter !== 'all') $url .= "&filter=$filter";
    if (!empty($search)) $url .= "&search=" . urlencode($search);
    return $url;
}
function sortIcon($col, $currentSort, $currentOrder) {
    if ($currentSort !== $col) return '<i class="fas fa-sort" style="opacity:0.3;"></i>';
    return $currentOrder === 'asc' ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
}
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
      <div style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:var(--radius-lg); padding:1rem 1.5rem; display:flex; align-items:center; gap:12px; min-width:160px; backdrop-filter:blur(10px);">
        <i class="fas <?= $si['icon'] ?>" style="color:<?= $si['color'] ?>; font-size:1.2rem;"></i>
        <div>
          <div style="font-size:1.4rem; font-weight:800; color:<?= $si['color'] ?>;"><?= $si['val'] ?></div>
          <div style="font-size:0.78rem; color:var(--text-muted);"><?= $si['label'] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Search + Filter + Export bar -->
    <div style="display:flex; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap; align-items:center;">
      <!-- Search -->
      <form method="GET" action="index.php" style="display:flex; flex:1; min-width:250px;">
        <input type="hidden" name="page" value="admin_produits">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filterStatut) ?>">
        <input type="hidden" name="sort" value="<?= htmlspecialchars($sortBy) ?>">
        <input type="hidden" name="order" value="<?= htmlspecialchars($sortOrder) ?>">
        <div style="display:flex; flex:1; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); border-radius:14px; overflow:hidden;">
          <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="🔍 Rechercher un produit..." 
                 style="flex:1; padding:10px 14px; background:transparent; border:none; color:#eef2fb; font-size:0.875rem; outline:none; font-family:inherit;">
          <button type="submit" style="padding:10px 16px; background:linear-gradient(135deg,#7e56ff,#9d6bff); border:none; color:white; cursor:pointer; font-weight:700; font-size:0.82rem;">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </form>

      <!-- Filter tabs -->
      <div style="display:flex; gap:4px; background:rgba(255,255,255,0.05); padding:4px; border-radius:14px; border:1px solid rgba(255,255,255,0.08);">
        <?php
        $filters = ['all'=>'Tous','en_attente'=>'En attente','disponible'=>'Disponibles','rupture'=>'Rupture'];
        foreach ($filters as $fval => $flabel): 
          $filterUrl = "index.php?page=admin_produits&filter=$fval";
          if (!empty($searchQuery)) $filterUrl .= "&search=" . urlencode($searchQuery);
          if ($sortBy !== 'date') $filterUrl .= "&sort=$sortBy&order=$sortOrder";
        ?>
        <a href="<?= $filterUrl ?>"
           style="padding:7px 16px; border-radius:10px; font-size:0.82rem; font-weight:700; text-decoration:none; transition:all 0.2s; color:<?= $filterStatut === $fval ? 'white' : 'rgba(222,228,242,0.6)' ?>; background:<?= $filterStatut === $fval ? 'linear-gradient(135deg,#7e56ff,#9d6bff)' : 'transparent' ?>; <?= $filterStatut === $fval ? 'box-shadow:0 8px 18px rgba(126,86,255,0.24);' : '' ?>">
          <?= $flabel ?>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- PDF Export Button -->
      <button onclick="exportPDF()" class="admin-btn admin-btn-outline" style="white-space:nowrap;">
        <i class="fas fa-file-pdf" style="color:var(--danger);"></i> Export PDF
      </button>
    </div>

    <?php if (!empty($searchQuery)): ?>
    <div style="display:flex; align-items:center; gap:8px; margin-bottom:1rem; color:var(--text-muted); font-size:0.875rem;">
      <span><?= count($filtered) ?> résultat(s) pour "<strong style="color:var(--text-primary);"><?= htmlspecialchars($searchQuery) ?></strong>"</span>
      <a href="index.php?page=admin_produits&filter=<?= $filterStatut ?>" style="color:var(--danger); text-decoration:none; font-size:0.8rem;">
        <i class="fas fa-times"></i> Effacer
      </a>
    </div>
    <?php endif; ?>

    <div class="admin-table-wrap" id="produitsTable">
      <div class="admin-table-header">
        <div class="admin-table-title">Liste des Produits</div>
        <div style="color:var(--text-muted); font-size:0.8rem;"><?= count($filtered) ?> produit(s)</div>
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>
              <a href="<?= sortUrl('nom', $sortBy, $sortOrder, $filterStatut, $searchQuery) ?>" style="color:inherit; text-decoration:none; display:flex; align-items:center; gap:4px;">
                Produit <?= sortIcon('nom', $sortBy, $sortOrder) ?>
              </a>
            </th>
            <th>Catégorie</th>
            <th>
              <a href="<?= sortUrl('prix', $sortBy, $sortOrder, $filterStatut, $searchQuery) ?>" style="color:inherit; text-decoration:none; display:flex; align-items:center; gap:4px;">
                Prix <?= sortIcon('prix', $sortBy, $sortOrder) ?>
              </a>
            </th>
            <th>
              <a href="<?= sortUrl('stock', $sortBy, $sortOrder, $filterStatut, $searchQuery) ?>" style="color:inherit; text-decoration:none; display:flex; align-items:center; gap:4px;">
                Stock <?= sortIcon('stock', $sortBy, $sortOrder) ?>
              </a>
            </th>
            <th>Statut</th>
            <th>
              <a href="<?= sortUrl('date', $sortBy, $sortOrder, $filterStatut, $searchQuery) ?>" style="color:inherit; text-decoration:none; display:flex; align-items:center; gap:4px;">
                Date <?= sortIcon('date', $sortBy, $sortOrder) ?>
              </a>
            </th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($filtered)): ?>
          <tr><td colspan="8" style="text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:0.5rem;"></i>
            Aucun produit trouvé
          </td></tr>
          <?php else: ?>
          <?php foreach ($filtered as $p): ?>
          <tr>
            <td style="color:var(--text-muted); font-size:0.8rem;">#<?= $p->getId() ?></td>
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:40px; height:40px; border-radius:6px; background:var(--bg-card); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
                  <?php if ($p->getImage()): ?>
                    <img src="<?= htmlspecialchars($p->getImage()) ?>" style="width:100%; height:100%; object-fit:cover;">
                  <?php else: ?>
                    <i class="fas fa-box" style="color:var(--border-light);"></i>
                  <?php endif; ?>
                </div>
                <div>
                  <div class="table-product-name"><?= htmlspecialchars(substr($p->getNom(), 0, 40)) ?></div>
                </div>
              </div>
            </td>
            <td>
              <span style="background:var(--accent-glow); color:var(--accent-light); padding:3px 10px; border-radius:20px; font-size:0.75rem;">
                <?= htmlspecialchars($p->getNomCategorie()) ?>
              </span>
            </td>
            <td style="font-weight:700; color:var(--success);"><?= number_format($p->getPrix(), 2) ?> DT</td>
            <td style="color:var(--text-muted); font-size:0.82rem;"><?= $p->getQuantite() ?></td>
            <td>
              <?php
              $bmap = ['disponible'=>'badge-disponible','rupture'=>'badge-rupture','en_attente'=>'badge-pending'];
              $lmap = ['disponible'=>'✓ Disponible','rupture'=>'✗ Rupture','en_attente'=>'⏳ En attente'];
              ?>
              <span class="badge <?= $bmap[$p->getStatut()] ?>"><?= $lmap[$p->getStatut()] ?></span>
            </td>
            <td style="color:var(--text-muted); font-size:0.8rem;"><?= date('d/m/Y', strtotime($p->getCreatedAt())) ?></td>
            <td>
              <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <?php if ($p->getStatut() === 'en_attente'): ?>
                <a href="index.php?page=admin_produit_statut&id=<?= $p->getId() ?>&statut=disponible"
                   class="admin-btn admin-btn-success admin-btn-sm">
                  <i class="fas fa-check"></i> Approuver
                </a>
                <a href="index.php?page=admin_produit_statut&id=<?= $p->getId() ?>&statut=rupture"
                   class="admin-btn admin-btn-danger admin-btn-sm">
                  <i class="fas fa-ban"></i> Refuser
                </a>
                <?php elseif ($p->getStatut() === 'disponible'): ?>
                <a href="index.php?page=admin_produit_statut&id=<?= $p->getId() ?>&statut=rupture"
                   class="admin-btn admin-btn-danger admin-btn-sm">
                  <i class="fas fa-ban"></i> Rupture
                </a>
                <?php else: ?>
                <a href="index.php?page=admin_produit_statut&id=<?= $p->getId() ?>&statut=disponible"
                   class="admin-btn admin-btn-warning admin-btn-sm">
                  <i class="fas fa-undo"></i> Réactiver
                </a>
                <?php endif; ?>
                <a href="index.php?page=produit_detail&id=<?= $p->getId() ?>" target="_blank"
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

<!-- PDF Export Script -->
<script>
function exportPDF() {
    const table = document.getElementById('produitsTable');
    const win = window.open('', '_blank');
    win.document.write(`
    <html><head><title>Liste des Produits - SkillBridge</title>
    <style>
      body { font-family: Arial, sans-serif; padding: 20px; color: #333; }
      h1 { font-size: 20px; margin-bottom: 5px; }
      h2 { font-size: 14px; color: #666; margin-bottom: 20px; }
      table { width: 100%; border-collapse: collapse; margin-top: 10px; }
      th { background: #f5f5f5; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #ddd; }
      td { padding: 10px 12px; border-bottom: 1px solid #eee; font-size: 12px; }
      tr:nth-child(even) td { background: #fafafa; }
      .badge { padding: 3px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
      .badge-dispo { background: #d1fae5; color: #065f46; }
      .badge-rupture { background: #fee2e2; color: #991b1b; }
      .badge-attente { background: #fef3c7; color: #92400e; }
      .footer { margin-top: 30px; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
      @media print { body { padding: 0; } }
    </style></head><body>
    <h1>📦 Liste des Produits - SkillBridge</h1>
    <h2>Exporté le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</h2>
    <table>
      <thead><tr>
        <th>#</th><th>Produit</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Statut</th><th>Date</th>
      </tr></thead><tbody>`);
    
    const rows = document.querySelectorAll('#produitsTable .admin-table tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 7) {
            const statut = cells[5].textContent.trim();
            let badgeClass = 'badge-attente';
            if (statut.includes('Disponible')) badgeClass = 'badge-dispo';
            else if (statut.includes('Rupture')) badgeClass = 'badge-rupture';
            
            win.document.write('<tr>');
            win.document.write('<td>' + cells[0].textContent.trim() + '</td>');
            win.document.write('<td>' + cells[1].textContent.trim() + '</td>');
            win.document.write('<td>' + cells[2].textContent.trim() + '</td>');
            win.document.write('<td>' + cells[3].textContent.trim() + '</td>');
            win.document.write('<td>' + cells[4].textContent.trim() + '</td>');
            win.document.write('<td><span class="badge ' + badgeClass + '">' + statut + '</span></td>');
            win.document.write('<td>' + cells[6].textContent.trim() + '</td>');
            win.document.write('</tr>');
        }
    });
    
    win.document.write('</tbody></table>');
    win.document.write('<div class="footer">SkillBridge Admin — Rapport généré automatiquement</div>');
    win.document.write('</body></html>');
    win.document.close();
    setTimeout(() => { win.print(); }, 500);
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
