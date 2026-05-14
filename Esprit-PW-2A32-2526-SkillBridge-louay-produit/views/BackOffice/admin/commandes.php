<?php
$pageTitle = 'Commandes - Admin SkillBridge';
include __DIR__ . '/../partials/sidebar.php';

// Tri, Recherche, Filter
$sortBy = $_GET['sort'] ?? 'date';
$sortOrder = $_GET['order'] ?? 'desc';
$searchQuery = $_GET['search'] ?? '';
$filterStatut = $_GET['filter'] ?? 'all';

// Prepare chart data
$cmdRevDates = []; $cmdRevValues = [];
foreach ($cmdRevenue as $s) {
    $cmdRevDates[] = $s['date'];
    $cmdRevValues[] = (float)$s['revenue'];
}
$cmdOrdDates = []; $cmdOrdCounts = [];
foreach ($cmdOrdersPerDay as $o) {
    $cmdOrdDates[] = $o['date'];
    $cmdOrdCounts[] = (int)$o['count'];
}
$cmdStatusLabels = ['En attente', 'Confirmées', 'Expédiées', 'Livrées', 'Annulées'];
$cmdStatusValues = [
    (int)($cStats['en_attente'] ?? 0),
    (int)($cStats['confirmee'] ?? 0),
    (int)($cStats['expediee'] ?? 0),
    (int)($cStats['livree'] ?? 0),
    (int)($cStats['annulee'] ?? 0)
];

// Apply filter
$filtered = $commandes;
if ($filterStatut !== 'all') {
    $filtered = array_filter($commandes, fn($c) => $c->getStatut() === $filterStatut);
}
// Apply search
if (!empty($searchQuery)) {
    $filtered = array_filter($filtered, function($c) use ($searchQuery) {
        return stripos($c->getNomClient(), $searchQuery) !== false 
            || stripos($c->getEmailClient(), $searchQuery) !== false
            || stripos($c->getNomProduit(), $searchQuery) !== false;
    });
}
// Apply sorting
$filtered = array_values($filtered);
usort($filtered, function($a, $b) use ($sortBy, $sortOrder) {
    switch ($sortBy) {
        case 'client': $cmp = strcmp($a->getNomClient(), $b->getNomClient()); break;
        case 'total':  $cmp = $a->getPrixTotal() <=> $b->getPrixTotal(); break;
        case 'date':
        default:       $cmp = strcmp($a->getCreatedAt(), $b->getCreatedAt()); break;
    }
    return $sortOrder === 'asc' ? $cmp : -$cmp;
});

function cmdSortUrl($col, $currentSort, $currentOrder, $filter, $search) {
    $newOrder = ($currentSort === $col && $currentOrder === 'asc') ? 'desc' : 'asc';
    $url = "index.php?page=admin_commandes&sort=$col&order=$newOrder";
    if ($filter !== 'all') $url .= "&filter=$filter";
    if (!empty($search)) $url .= "&search=" . urlencode($search);
    return $url;
}
function cmdSortIcon($col, $currentSort, $currentOrder) {
    if ($currentSort !== $col) return '<i class="fas fa-sort" style="opacity:0.3;"></i>';
    return $currentOrder === 'asc' ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
}
?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

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

    <!-- ====== CHARTS ROW ====== -->
    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:1.5rem; margin-bottom:2rem;">
      <!-- Revenue -->
      <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:1.5rem;">
        <h3 style="font-size:0.95rem; font-weight:700; margin-bottom:1rem; color:var(--text-primary);">📈 Revenus (7 Jours)</h3>
        <div id="cmdRevChart" style="min-height:240px;"></div>
      </div>
      <!-- Orders per day -->
      <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:1.5rem;">
        <h3 style="font-size:0.95rem; font-weight:700; margin-bottom:1rem; color:var(--text-primary);">📊 Commandes / Jour</h3>
        <div id="cmdOrdChart" style="min-height:240px;"></div>
      </div>
      <!-- Order status donut -->
      <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:1.5rem;">
        <h3 style="font-size:0.95rem; font-weight:700; margin-bottom:1rem; color:var(--text-primary);">📋 Répartition Statuts</h3>
        <div id="cmdStatusDonut" style="min-height:240px;"></div>
      </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
      const tc = '#f3f4f6';
      // Revenue
      new ApexCharts(document.querySelector("#cmdRevChart"), {
        series: [{ name: 'Revenus (DT)', data: <?= json_encode($cmdRevValues) ?> }],
        chart: { type: 'area', height: 240, toolbar: { show: false }, foreColor: tc, background: 'transparent' },
        colors: ['#8b5cf6'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.1 } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        xaxis: { categories: <?= json_encode($cmdRevDates) ?> },
        grid: { borderColor: 'rgba(255,255,255,0.06)' },
        theme: { mode: 'dark' }
      }).render();
      // Orders per day
      new ApexCharts(document.querySelector("#cmdOrdChart"), {
        series: [{ name: 'Commandes', data: <?= json_encode($cmdOrdCounts) ?> }],
        chart: { type: 'bar', height: 240, toolbar: { show: false }, foreColor: tc, background: 'transparent' },
        colors: ['#3b82f6'],
        plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
        dataLabels: { enabled: true, style: { fontSize: '11px' } },
        xaxis: { categories: <?= json_encode($cmdOrdDates) ?> },
        grid: { borderColor: 'rgba(255,255,255,0.06)' },
        theme: { mode: 'dark' }
      }).render();
      // Status donut
      new ApexCharts(document.querySelector("#cmdStatusDonut"), {
        series: <?= json_encode($cmdStatusValues) ?>,
        chart: { type: 'donut', height: 240, foreColor: tc, background: 'transparent' },
        labels: <?= json_encode($cmdStatusLabels) ?>,
        colors: ['#f59e0b', '#60a5fa', '#818cf8', '#10b981', '#ef4444'],
        legend: { position: 'bottom', fontSize: '11px' },
        plotOptions: { pie: { donut: { size: '58%', labels: { show: true, name: { fontSize: '12px' }, value: { fontSize: '16px', fontWeight: 700 }, total: { show: true, label: 'Total', fontSize: '11px' } } } } },
        dataLabels: { enabled: false },
        stroke: { show: false },
        theme: { mode: 'dark' }
      }).render();
    });
    </script>

    <!-- Search + Filter + PDF -->
    <div style="display:flex; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap; align-items:center;">
      <form method="GET" action="index.php" style="display:flex; flex:1; min-width:250px;">
        <input type="hidden" name="page" value="admin_commandes">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filterStatut) ?>">
        <div style="display:flex; flex:1; background:var(--bg-card); border:1px solid var(--border); border-radius:8px; overflow:hidden;">
          <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="🔍 Rechercher par client ou produit..." 
                 style="flex:1; padding:10px 14px; background:transparent; border:none; color:var(--text-primary); font-size:0.875rem; outline:none; font-family:inherit;">
          <button type="submit" style="padding:10px 16px; background:var(--accent); border:none; color:white; cursor:pointer; font-weight:600; font-size:0.82rem;">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </form>

      <div style="display:flex; gap:4px; background:var(--bg-card); padding:4px; border-radius:10px; border:1px solid var(--border);">
        <?php
        $filters = ['all'=>'Tous','en_attente'=>'En attente','confirmee'=>'Confirmées','expediee'=>'Expédiées','livree'=>'Livrées','annulee'=>'Annulées'];
        foreach ($filters as $fval => $flabel): 
          $fUrl = "index.php?page=admin_commandes&filter=$fval";
          if (!empty($searchQuery)) $fUrl .= "&search=" . urlencode($searchQuery);
        ?>
        <a href="<?= $fUrl ?>"
           style="padding:7px 14px; border-radius:7px; font-size:0.78rem; font-weight:600; text-decoration:none; transition:all 0.2s; color:<?= $filterStatut === $fval ? 'white' : 'var(--text-muted)' ?>; background:<?= $filterStatut === $fval ? 'var(--accent)' : 'transparent' ?>;">
          <?= $flabel ?>
        </a>
        <?php endforeach; ?>
      </div>

      <button onclick="exportCommandesPDF()" class="admin-btn admin-btn-outline" style="white-space:nowrap;">
        <i class="fas fa-file-pdf" style="color:var(--danger);"></i> Export PDF
      </button>
    </div>

    <?php if (!empty($searchQuery)): ?>
    <div style="display:flex; align-items:center; gap:8px; margin-bottom:1rem; color:var(--text-muted); font-size:0.875rem;">
      <span><?= count($filtered) ?> résultat(s) pour "<strong style="color:var(--text-primary);"><?= htmlspecialchars($searchQuery) ?></strong>"</span>
      <a href="index.php?page=admin_commandes&filter=<?= $filterStatut ?>" style="color:var(--danger); text-decoration:none; font-size:0.8rem;">
        <i class="fas fa-times"></i> Effacer
      </a>
    </div>
    <?php endif; ?>

    <div class="admin-table-wrap" id="commandesTable">
      <div class="admin-table-header">
        <div class="admin-table-title">Liste des Commandes</div>
        <div style="color:var(--text-muted); font-size:0.8rem;"><?= count($filtered) ?> commande(s)</div>
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>
              <a href="<?= cmdSortUrl('client', $sortBy, $sortOrder, $filterStatut, $searchQuery) ?>" style="color:inherit; text-decoration:none; display:flex; align-items:center; gap:4px;">
                Client <?= cmdSortIcon('client', $sortBy, $sortOrder) ?>
              </a>
            </th>
            <th>Produit</th>
            <th>Qté</th>
            <th>
              <a href="<?= cmdSortUrl('total', $sortBy, $sortOrder, $filterStatut, $searchQuery) ?>" style="color:inherit; text-decoration:none; display:flex; align-items:center; gap:4px;">
                Total <?= cmdSortIcon('total', $sortBy, $sortOrder) ?>
              </a>
            </th>
            <th>
              <a href="<?= cmdSortUrl('date', $sortBy, $sortOrder, $filterStatut, $searchQuery) ?>" style="color:inherit; text-decoration:none; display:flex; align-items:center; gap:4px;">
                Date <?= cmdSortIcon('date', $sortBy, $sortOrder) ?>
              </a>
            </th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($filtered)): ?>
          <tr><td colspan="8" style="text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:0.5rem;"></i>
            Aucune commande trouvée
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

<script>
function exportCommandesPDF() {
    const win = window.open('', '_blank');
    win.document.write(`
    <html><head><title>Liste des Commandes - SkillBridge</title>
    <style>
      body { font-family: Arial, sans-serif; padding: 20px; color: #333; }
      h1 { font-size: 20px; margin-bottom: 5px; }
      h2 { font-size: 14px; color: #666; margin-bottom: 20px; }
      table { width: 100%; border-collapse: collapse; }
      th { background: #f5f5f5; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; border-bottom: 2px solid #ddd; }
      td { padding: 10px 12px; border-bottom: 1px solid #eee; font-size: 12px; }
      tr:nth-child(even) td { background: #fafafa; }
      .footer { margin-top: 30px; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style></head><body>
    <h1>📋 Liste des Commandes - SkillBridge</h1>
    <h2>Exporté le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</h2>
    <table><thead><tr>
      <th>#</th><th>Client</th><th>Produit</th><th>Qté</th><th>Total</th><th>Date</th><th>Statut</th>
    </tr></thead><tbody>`);

    const rows = document.querySelectorAll('#commandesTable .admin-table tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 7) {
            win.document.write('<tr>');
            for (let i = 0; i < 7; i++) {
                win.document.write('<td>' + cells[i].textContent.trim() + '</td>');
            }
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
