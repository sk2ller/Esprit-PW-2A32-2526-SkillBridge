<?php
$pageTitle = 'Dashboard - Admin SkillBridge';
require_once __DIR__ . '/../../controllers/OffreController.php';
require_once __DIR__ . '/../../controllers/CandidatureController.php';

$offreController = new OffreController();
$candidatureController = new CandidatureController();
$offreStats = $offreController->getStats();
$offreInsights = $offreController->getAdminInsights();
$candidatureStats = $candidatureController->getStats();
$candidatureInsights = $candidatureController->getAdminInsights();

include __DIR__ . '/sidebar.php';
?>

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title">Dashboard</div>
      <div class="topbar-bread">SkillBridge Admin > Vue d'ensemble offre job</div>
    </div>
    <div class="topbar-actions">
      <a href="index.php?page=admin_dashboard_export_pdf" class="topbar-btn topbar-btn-outline">
        <i class="fas fa-file-pdf"></i> Export PDF
      </a>
      <a href="index.php?page=admin_offres" class="topbar-btn topbar-btn-primary">
        <i class="fas fa-briefcase"></i> Voir les offres
      </a>
    </div>
  </div>

  <div class="admin-content">
    <div class="stats-grid">
      <div class="stat-widget purple">
        <div class="sw-icon"><i class="fas fa-briefcase"></i></div>
        <div class="sw-value"><?= $offreStats['total'] ?? 0 ?></div>
        <div class="sw-label">Total offres job</div>
      </div>
      <div class="stat-widget green">
        <div class="sw-icon"><i class="fas fa-check-circle"></i></div>
        <div class="sw-value"><?= $offreStats['actif'] ?? 0 ?></div>
        <div class="sw-label">Offres actives</div>
      </div>
      <div class="stat-widget orange">
        <div class="sw-icon"><i class="fas fa-file-signature"></i></div>
        <div class="sw-value"><?= $candidatureStats['total'] ?? 0 ?></div>
        <div class="sw-label">Candidatures recues</div>
      </div>
      <div class="stat-widget blue">
        <div class="sw-icon"><i class="fas fa-chart-line"></i></div>
        <div class="sw-value"><?= number_format((float) ($offreInsights['average_budget'] ?? 0), 2) ?></div>
        <div class="sw-label">Budget moyen DT</div>
      </div>
    </div>

    <section class="admin-table-wrap dashboard-visual-wrap" style="margin-bottom:1.5rem;">
      <div class="admin-table-header">
        <div class="admin-table-title">Vue graphique</div>
      </div>
      <div class="analytics-panel">
        <div class="chart-grid">
          <div class="chart-card">
            <div class="chart-card-head">
              <h3>Repartition des offres</h3>
              <span>statuts</span>
            </div>
            <div class="chart-stage chart-stage-donut">
              <canvas id="dashboardOffersChart" height="170"></canvas>
            </div>
          </div>
          <div class="chart-card">
            <div class="chart-card-head">
              <h3>Repartition des candidatures</h3>
              <span>decisions</span>
            </div>
            <div class="chart-stage">
              <canvas id="dashboardCandidaturesChart" height="170"></canvas>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="dashboard-table-grid">
      <section class="admin-table-wrap">
        <div class="admin-table-header">
          <div class="admin-table-title">Statistiques par niveau</div>
        </div>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Niveau</th>
              <th>Nombre d'offres</th>
              <th>Budget moyen</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($offreInsights['by_level'])): ?>
            <tr><td colspan="3">Aucune donnee disponible</td></tr>
            <?php else: ?>
            <?php foreach ($offreInsights['by_level'] as $row): ?>
            <tr>
              <td><?= ucfirst($row['niveau_requis']) ?></td>
              <td><?= (int) $row['total'] ?></td>
              <td><?= number_format((float) $row['avg_budget'], 2) ?> DT</td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </section>

      <section class="admin-table-wrap">
        <div class="admin-table-header">
          <div class="admin-table-title">Top offres par candidatures</div>
        </div>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Offre</th>
              <th>Total candidatures</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($candidatureInsights['by_offer'])): ?>
            <tr><td colspan="2">Aucune donnee disponible</td></tr>
            <?php else: ?>
            <?php foreach ($candidatureInsights['by_offer'] as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['titre_offre']) ?></td>
              <td><?= (int) $row['total'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </section>
    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  if (!window.Chart) return;

  new Chart(document.getElementById('dashboardOffersChart'), {
    type: 'doughnut',
    data: {
      labels: ['Actif', 'En attente', 'Suspendu'],
      datasets: [{
        data: [<?= (int) ($offreStats['actif'] ?? 0) ?>, <?= (int) ($offreStats['en_attente'] ?? 0) ?>, <?= (int) ($offreStats['suspendu'] ?? 0) ?>],
        backgroundColor: ['#2f7d57', '#e07020', '#b84942'],
        borderWidth: 0,
        hoverOffset: 10
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '52%',
      plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true, pointStyle: 'circle' } }
      }
    }
  });

  new Chart(document.getElementById('dashboardCandidaturesChart'), {
    type: 'bar',
    data: {
      labels: ['En attente', 'Acceptees', 'Refusees'],
      datasets: [{
        data: [<?= (int) ($candidatureStats['en_attente'] ?? 0) ?>, <?= (int) ($candidatureStats['acceptee'] ?? 0) ?>, <?= (int) ($candidatureStats['refusee'] ?? 0) ?>],
        backgroundColor: ['#e07020', '#2f7d57', '#b84942'],
        borderRadius: 12,
        barThickness: 48
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false } },
        y: {
          beginAtZero: true,
          ticks: { precision: 0, stepSize: 1 },
          grid: { color: 'rgba(223,209,189,.6)' }
        }
      }
    }
  });
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
