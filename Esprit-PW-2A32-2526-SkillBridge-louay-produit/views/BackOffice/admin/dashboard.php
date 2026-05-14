<?php
$pageTitle = 'Dashboard - Admin SkillBridge';
include __DIR__ . '/../partials/sidebar.php';

// Prepare chart data
$revDates = []; $revValues = [];
foreach ($platformRevenue as $s) {
    $revDates[] = $s['date'];
    $revValues[] = (float)$s['revenue'];
}

$topNames = []; $topValues = [];
foreach ($platformTopProducts as $tp) {
    $topNames[] = $tp['nom'];
    $topValues[] = (int)$tp['total_vendus'];
}

$orderDates = []; $orderCounts = [];
foreach ($ordersPerDay as $o) {
    $orderDates[] = $o['date'];
    $orderCounts[] = (int)$o['count'];
}

// Product status distribution for donut chart
$prodStatusLabels = ['Disponibles', 'En attente', 'Rupture'];
$prodStatusValues = [
    (int)($pStats['disponible'] ?? 0),
    (int)($pStats['en_attente'] ?? 0),
    (int)($pStats['rupture'] ?? 0)
];

// Order status distribution for donut chart
$orderStatusLabels = ['En attente', 'Confirmées', 'Expédiées', 'Livrées', 'Annulées'];
$orderStatusValues = [
    (int)($cStats['en_attente'] ?? 0),
    (int)($cStats['confirmee'] ?? 0),
    (int)($cStats['expediee'] ?? 0),
    (int)($cStats['livree'] ?? 0),
    (int)($cStats['annulee'] ?? 0)
];
?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title">Dashboard</div>
      <div class="topbar-bread">Vue d'ensemble de la plateforme</div>
    </div>
  </div>

  <div class="admin-content">
    <h1 class="admin-page-title">Bienvenue, Admin 👋</h1>
    <p class="admin-page-sub">Voici un aperçu complet de SkillBridge.</p>

    <!-- ====== STAT WIDGETS ====== -->
    <div class="stats-grid" style="grid-template-columns: repeat(5, 1fr); margin-bottom: 2rem;">
      <div class="stat-widget purple">
        <div class="sw-icon"><i class="fas fa-layer-group"></i></div>
        <div class="sw-value"><?= $pStats['total'] ?? 0 ?></div>
        <div class="sw-label">Total Produits</div>
      </div>
      <div class="stat-widget green">
        <div class="sw-icon"><i class="fas fa-check-circle"></i></div>
        <div class="sw-value"><?= $pStats['disponible'] ?? 0 ?></div>
        <div class="sw-label">Disponibles</div>
      </div>
      <div class="stat-widget orange">
        <div class="sw-icon"><i class="fas fa-clock"></i></div>
        <div class="sw-value"><?= $pStats['en_attente'] ?? 0 ?></div>
        <div class="sw-label">En attente</div>
      </div>
      <div class="stat-widget blue">
        <div class="sw-icon"><i class="fas fa-receipt"></i></div>
        <div class="sw-value"><?= $cStats['total'] ?? 0 ?></div>
        <div class="sw-label">Commandes</div>
      </div>
      <div class="stat-widget" style="background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(16,185,129,0.05)); border: 1px solid rgba(16,185,129,0.2);">
        <div class="sw-icon" style="background: rgba(16,185,129,0.2); color: #34d399;"><i class="fas fa-coins"></i></div>
        <div class="sw-value" style="color: #34d399;"><?= number_format($totalRevenue, 2) ?></div>
        <div class="sw-label">Revenus (DT)</div>
      </div>
    </div>

    <!-- ====== ROW 1: Revenue + Orders Per Day ====== -->
    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
      <!-- Platform Revenue Area Chart -->
      <div style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:var(--radius-lg); padding:1.5rem; backdrop-filter:blur(10px); box-shadow:0 18px 38px rgba(0,0,0,0.16);">
        <h3 style="font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:700; margin-bottom:1rem; color:#fff;">📈 Revenus Plateforme (7 Jours)</h3>
        <div id="adminRevChart" style="min-height:280px;"></div>
      </div>
      <!-- Orders Per Day Bar Chart -->
      <div style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:var(--radius-lg); padding:1.5rem; backdrop-filter:blur(10px); box-shadow:0 18px 38px rgba(0,0,0,0.16);">
        <h3 style="font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:700; margin-bottom:1rem; color:#fff;">📊 Commandes / Jour</h3>
        <div id="adminOrdersChart" style="min-height:280px;"></div>
      </div>
    </div>

    <!-- ====== ROW 2: Top Products + Product Donut + Order Donut ====== -->
    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:1.5rem; margin-bottom:2rem;">
      <!-- Top Selling Products -->
      <div style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:var(--radius-lg); padding:1.5rem; backdrop-filter:blur(10px); box-shadow:0 18px 38px rgba(0,0,0,0.16);">
        <h3 style="font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:700; margin-bottom:1rem; color:#fff;">🏆 Top Produits Vendus</h3>
        <div id="adminTopChart" style="min-height:260px;"></div>
      </div>
      <!-- Product Status Donut -->
      <div style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:var(--radius-lg); padding:1.5rem; backdrop-filter:blur(10px); box-shadow:0 18px 38px rgba(0,0,0,0.16);">
        <h3 style="font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:700; margin-bottom:1rem; color:#fff;">📦 Répartition Produits</h3>
        <div id="adminProdDonut" style="min-height:260px;"></div>
      </div>
      <!-- Order Status Donut -->
      <div style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:var(--radius-lg); padding:1.5rem; backdrop-filter:blur(10px); box-shadow:0 18px 38px rgba(0,0,0,0.16);">
        <h3 style="font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:700; margin-bottom:1rem; color:#fff;">📋 Répartition Commandes</h3>
        <div id="adminCmdDonut" style="min-height:260px;"></div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div style="display:flex; gap:1rem; flex-wrap:wrap;">
      <a href="index.php?page=admin_produits" class="admin-btn admin-btn-primary">
        <i class="fas fa-box"></i> Gérer les Produits
      </a>
      <a href="index.php?page=admin_categories" class="admin-btn admin-btn-outline">
        <i class="fas fa-tags"></i> Gérer les Catégories
      </a>
      <a href="index.php?page=admin_commandes" class="admin-btn admin-btn-outline">
        <i class="fas fa-receipt"></i> Gérer les Commandes
      </a>
      <a href="index.php?page=produits" class="admin-btn admin-btn-outline" target="_blank">
        <i class="fas fa-globe"></i> Voir le FrontOffice
      </a>
    </div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const textColor = '#f3f4f6';

  // ===== 1. PLATFORM REVENUE AREA CHART =====
  new ApexCharts(document.querySelector("#adminRevChart"), {
    series: [{ name: 'Revenus (DT)', data: <?= json_encode($revValues) ?> }],
    chart: { type: 'area', height: 280, toolbar: { show: false }, foreColor: textColor, background: 'transparent' },
    colors: ['#8b5cf6'],
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.1, stops: [0, 90, 100] } },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
    xaxis: { categories: <?= json_encode($revDates) ?> },
    grid: { borderColor: 'rgba(255,255,255,0.06)' },
    theme: { mode: 'dark' }
  }).render();

  // ===== 2. ORDERS PER DAY BAR CHART =====
  new ApexCharts(document.querySelector("#adminOrdersChart"), {
    series: [{ name: 'Commandes', data: <?= json_encode($orderCounts) ?> }],
    chart: { type: 'bar', height: 280, toolbar: { show: false }, foreColor: textColor, background: 'transparent' },
    colors: ['#3b82f6'],
    plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
    dataLabels: { enabled: true, style: { fontSize: '11px' } },
    xaxis: { categories: <?= json_encode($orderDates) ?> },
    grid: { borderColor: 'rgba(255,255,255,0.06)' },
    theme: { mode: 'dark' }
  }).render();

  // ===== 3. TOP PRODUCTS HORIZONTAL BAR =====
  new ApexCharts(document.querySelector("#adminTopChart"), {
    series: [{ name: 'Unités Vendues', data: <?= json_encode($topValues) ?> }],
    chart: { type: 'bar', height: 260, toolbar: { show: false }, foreColor: textColor, background: 'transparent' },
    plotOptions: { bar: { borderRadius: 4, horizontal: true } },
    dataLabels: { enabled: true },
    colors: ['#10b981'],
    xaxis: { categories: <?= json_encode($topNames) ?> },
    grid: { borderColor: 'rgba(255,255,255,0.06)' },
    theme: { mode: 'dark' }
  }).render();

  // ===== 4. PRODUCT STATUS DONUT =====
  new ApexCharts(document.querySelector("#adminProdDonut"), {
    series: <?= json_encode($prodStatusValues) ?>,
    chart: { type: 'donut', height: 260, foreColor: textColor, background: 'transparent' },
    labels: <?= json_encode($prodStatusLabels) ?>,
    colors: ['#10b981', '#f59e0b', '#ef4444'],
    legend: { position: 'bottom', fontSize: '12px' },
    plotOptions: { pie: { donut: { size: '60%', labels: { show: true, name: { fontSize: '13px' }, value: { fontSize: '18px', fontWeight: 700 }, total: { show: true, label: 'Total', fontSize: '12px' } } } } },
    dataLabels: { enabled: false },
    stroke: { show: false },
    theme: { mode: 'dark' }
  }).render();

  // ===== 5. ORDER STATUS DONUT =====
  new ApexCharts(document.querySelector("#adminCmdDonut"), {
    series: <?= json_encode($orderStatusValues) ?>,
    chart: { type: 'donut', height: 260, foreColor: textColor, background: 'transparent' },
    labels: <?= json_encode($orderStatusLabels) ?>,
    colors: ['#f59e0b', '#60a5fa', '#818cf8', '#10b981', '#ef4444'],
    legend: { position: 'bottom', fontSize: '12px' },
    plotOptions: { pie: { donut: { size: '60%', labels: { show: true, name: { fontSize: '13px' }, value: { fontSize: '18px', fontWeight: 700 }, total: { show: true, label: 'Total', fontSize: '12px' } } } } },
    dataLabels: { enabled: false },
    stroke: { show: false },
    theme: { mode: 'dark' }
  }).render();
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
