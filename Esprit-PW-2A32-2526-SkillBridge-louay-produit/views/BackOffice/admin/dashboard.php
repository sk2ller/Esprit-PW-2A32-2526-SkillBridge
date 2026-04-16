<?php
$pageTitle = 'Dashboard - Admin SkillBridge';
include __DIR__ . '/../partials/sidebar.php';
?>

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title">Dashboard Produits</div>
      <div class="topbar-bread">Vue d'ensemble</div>
    </div>
  </div>

  <div class="admin-content">
    <h1 class="admin-page-title">Bienvenue, Admin 👋</h1>
    <p class="admin-page-sub">Voici un aperçu de vos produits sur SkillBridge.</p>

    <!-- Stats -->
    <div class="stats-grid">
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
        <div class="sw-icon"><i class="fas fa-ban"></i></div>
        <div class="sw-value"><?= $pStats['rupture'] ?? 0 ?></div>
        <div class="sw-label">Rupture de stock</div>
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
      <a href="index.php?page=produits" class="admin-btn admin-btn-outline" target="_blank">
        <i class="fas fa-globe"></i> Voir le FrontOffice
      </a>
    </div>
  </div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
