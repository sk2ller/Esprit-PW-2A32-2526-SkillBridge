<?php
$pageTitle = 'Mes Produits - SkillBridge';
include __DIR__ . '/../partials/navbar.php';

require_once __DIR__ . '/../../../controllers/CommandeController.php';
$commandeCtrl = new CommandeController();
$vendeurId = $_SESSION['user']['id'];

$revStats = $commandeCtrl->getVendorRevenueStats($vendeurId);
$topProducts = $commandeCtrl->getTopProducts($vendeurId, 5);

$revDates = []; $revValues = [];
foreach($revStats as $s) {
    $revDates[] = $s['date'];
    $revValues[] = (float)$s['revenue'];
}

$topNames = []; $topValues = [];
foreach($topProducts as $p) {
    $topNames[] = $p['nom'];
    $topValues[] = (int)$p['total_vendus'];
}

// Product status distribution
$totalP = count($produits);
$dispoP = count(array_filter($produits, fn($p) => $p->getStatut() === 'disponible'));
$pendP = count(array_filter($produits, fn($p) => $p->getStatut() === 'en_attente'));
$ruptP = count(array_filter($produits, fn($p) => $p->getStatut() === 'rupture'));
$totalRevenue = array_sum($revValues);
?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="page-top">
<div class="container">

  <div class="section-header" style="margin-bottom:2rem;">
    <div>
      <h1 style="font-family:'Space Grotesk',sans-serif; font-size:1.8rem; font-weight:800; background:linear-gradient(135deg,#8b5cf6,#3b82f6); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">🏪 Mes Produits</h1>
      <p style="color:var(--text-muted); font-size:0.9rem;">Gérez vos produits numériques sur SkillBridge</p>
    </div>
    <a href="index.php?page=create_produit" class="btn-primary" style="font-size:0.875rem; padding:10px 20px;">
      <i class="fas fa-plus"></i> Ajouter un Produit
    </a>
  </div>

  <?php if (!empty($_GET['success'])): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <?php
    $msgs = ['1'=>'Produit créé avec succès ! Il sera soumis à approbation.','2'=>'Produit modifié avec succès.','3'=>'Produit supprimé.'];
    echo $msgs[$_GET['success']] ?? 'Opération effectuée.';
    ?>
  </div>
  <?php endif; ?>

  <!-- ====== STAT WIDGETS ====== -->
  <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:1rem; margin-bottom:2rem;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.2rem; display:flex; align-items:center; gap:12px;">
      <div style="width:42px;height:42px;border-radius:10px;background:rgba(139,92,246,0.15);display:flex;align-items:center;justify-content:center;">
        <i class="fas fa-box" style="color:#a78bfa; font-size:1.1rem;"></i>
      </div>
      <div>
        <div style="font-size:1.4rem; font-weight:800; color:var(--text-primary);"><?= $totalP ?></div>
        <div style="font-size:0.72rem; color:var(--text-muted);">Total Produits</div>
      </div>
    </div>
    <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.2rem; display:flex; align-items:center; gap:12px;">
      <div style="width:42px;height:42px;border-radius:10px;background:rgba(16,185,129,0.15);display:flex;align-items:center;justify-content:center;">
        <i class="fas fa-check-circle" style="color:#34d399; font-size:1.1rem;"></i>
      </div>
      <div>
        <div style="font-size:1.4rem; font-weight:800; color:#34d399;"><?= $dispoP ?></div>
        <div style="font-size:0.72rem; color:var(--text-muted);">Disponibles</div>
      </div>
    </div>
    <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.2rem; display:flex; align-items:center; gap:12px;">
      <div style="width:42px;height:42px;border-radius:10px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center;">
        <i class="fas fa-clock" style="color:#fbbf24; font-size:1.1rem;"></i>
      </div>
      <div>
        <div style="font-size:1.4rem; font-weight:800; color:#fbbf24;"><?= $pendP ?></div>
        <div style="font-size:0.72rem; color:var(--text-muted);">En attente</div>
      </div>
    </div>
    <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.2rem; display:flex; align-items:center; gap:12px;">
      <div style="width:42px;height:42px;border-radius:10px;background:rgba(16,185,129,0.2);display:flex;align-items:center;justify-content:center;">
        <i class="fas fa-coins" style="color:#34d399; font-size:1.1rem;"></i>
      </div>
      <div>
        <div style="font-size:1.4rem; font-weight:800; color:#34d399;"><?= number_format($totalRevenue, 2) ?></div>
        <div style="font-size:0.72rem; color:var(--text-muted);">Revenus (DT)</div>
      </div>
    </div>
  </div>

  <!-- ====== CHARTS ROW ====== -->
  <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1.5rem; margin-bottom:2rem;">
    <!-- Revenue Area -->
    <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.5rem;">
      <h3 style="font-size:0.95rem; font-weight:700; margin-bottom:1rem; color:var(--text-primary);">📈 Revenus (7 Jours)</h3>
      <div id="vendorRevChart" style="min-height:250px;"></div>
    </div>
    <!-- Top Products -->
    <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.5rem;">
      <h3 style="font-size:0.95rem; font-weight:700; margin-bottom:1rem; color:var(--text-primary);">🏆 Top Produits</h3>
      <div id="vendorTopChart" style="min-height:250px;"></div>
    </div>
    <!-- Product Status Donut -->
    <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.5rem;">
      <h3 style="font-size:0.95rem; font-weight:700; margin-bottom:1rem; color:var(--text-primary);">📦 Répartition Produits</h3>
      <div id="vendorDonut" style="min-height:250px;"></div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const tc = '#f3f4f6';

    // Revenue Area Chart
    new ApexCharts(document.querySelector("#vendorRevChart"), {
      series: [{ name: 'Revenus (DT)', data: <?= json_encode($revValues) ?> }],
      chart: { type: 'area', height: 250, toolbar: { show: false }, foreColor: tc, background: 'transparent' },
      colors: ['#8b5cf6'],
      fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.1, stops: [0, 90, 100] } },
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 3 },
      xaxis: { categories: <?= json_encode($revDates) ?> },
      grid: { borderColor: 'rgba(255,255,255,0.06)' },
      theme: { mode: 'dark' }
    }).render();

    // Top Products Horizontal Bar
    new ApexCharts(document.querySelector("#vendorTopChart"), {
      series: [{ name: 'Unités Vendues', data: <?= json_encode($topValues) ?> }],
      chart: { type: 'bar', height: 250, toolbar: { show: false }, foreColor: tc, background: 'transparent' },
      plotOptions: { bar: { borderRadius: 4, horizontal: true } },
      dataLabels: { enabled: true },
      colors: ['#10b981'],
      xaxis: { categories: <?= json_encode($topNames) ?> },
      grid: { borderColor: 'rgba(255,255,255,0.06)' },
      theme: { mode: 'dark' }
    }).render();

    // Product Status Donut
    new ApexCharts(document.querySelector("#vendorDonut"), {
      series: [<?= $dispoP ?>, <?= $pendP ?>, <?= $ruptP ?>],
      chart: { type: 'donut', height: 250, foreColor: tc, background: 'transparent' },
      labels: ['Disponibles', 'En attente', 'Rupture'],
      colors: ['#10b981', '#f59e0b', '#ef4444'],
      legend: { position: 'bottom', fontSize: '12px' },
      plotOptions: { pie: { donut: { size: '60%', labels: { show: true, name: { fontSize: '13px' }, value: { fontSize: '18px', fontWeight: 700 }, total: { show: true, label: 'Total', fontSize: '12px' } } } } },
      dataLabels: { enabled: false },
      stroke: { show: false },
      theme: { mode: 'dark' }
    }).render();
  });
  </script>

  <?php if (empty($produits)): ?>
  <div class="empty-state">
    <div class="icon">📦</div>
    <h3>Aucun produit pour le moment</h3>
    <p>Commencez à vendre en ajoutant votre premier produit</p>
    <a href="index.php?page=create_produit" class="btn-primary" style="display:inline-flex; margin-top:1rem;">
      <i class="fas fa-plus"></i> Ajouter un Produit
    </a>
  </div>
  <?php else: ?>
  <div class="products-grid">
    <?php foreach ($produits as $p): ?>
    <div class="product-card">
      <div class="product-card-image" style="background: var(--bg-secondary); overflow: hidden; position: relative;">
        <?php if ($p->getImage()): ?>
          <img src="<?= htmlspecialchars($p->getImage()) ?>" alt="<?= htmlspecialchars($p->getNom()) ?>" style="width: 100%; height: 100%; object-fit: cover;">
        <?php else: ?>
          <div style="width: 100%; height: 100%; background: linear-gradient(135deg, <?= ['#1a0533','#0a2240','#002a1f','#1a1000'][crc32($p->getNom()) % 4] ?>, var(--bg-secondary)); display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-box" style="color: rgba(255,255,255,0.2); font-size:4rem; position:relative; z-index:1;"></i>
          </div>
        <?php endif; ?>
      </div>
      <div class="product-card-body">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
          <span class="product-category-tag"><?= htmlspecialchars($p->getNomCategorie()) ?></span>
          <?php
          $bmap = ['disponible'=>'badge-disponible','rupture'=>'badge-rupture','en_attente'=>'badge-pending'];
          $lmap = ['disponible'=>'✓ Dispo','rupture'=>'✗ Rupture','en_attente'=>'⏳ Attente'];
          ?>
          <span class="badge <?= $bmap[$p->getStatut()] ?>"><?= $lmap[$p->getStatut()] ?></span>
        </div>
        <h3 class="product-title"><?= htmlspecialchars($p->getNom()) ?></h3>
      </div>
      <div class="product-card-footer">
        <div>
          <div class="product-price"><?= number_format($p->getPrix(), 2) ?> DT</div>
          <div class="product-stock"><i class="fas fa-cubes"></i> <?= $p->getQuantite() ?> en stock</div>
        </div>
        <div style="display:flex; gap:6px;">
          <a href="index.php?page=edit_produit&id=<?= $p->getId() ?>" class="btn-sm btn-sm-outline" title="Modifier">
            <i class="fas fa-pen"></i>
          </a>
          <a href="index.php?page=delete_produit&id=<?= $p->getId() ?>" class="btn-sm btn-sm-red"
             onclick="return confirm('Supprimer ce produit ?')" title="Supprimer">
            <i class="fas fa-trash"></i>
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
