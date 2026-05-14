<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'Admin - SkillBridge Produits' ?></title>
<link rel="stylesheet" href="views/assets/css/admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php
$pendingCount = $pStats['en_attente'] ?? 0;
$currentPage = $_GET['page'] ?? '';
$userName = trim(($_SESSION['user']['name'] ?? 'Admin'));
$currentRole = $_SESSION['role'] ?? 'admin';
?>

<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <div class="logo"><i class="fas fa-bolt"></i></div>
    <div>
      <div class="brand-text">SkillBridge</div>
      <div class="brand-sub">Produits · Back Office</div>
    </div>
  </div>

  <!-- Role Switcher -->
  <div class="sidebar-role-switcher">
    <a href="index.php?role=client&page=produits" class="role-item <?= $currentRole === 'client' ? 'active' : '' ?>" title="Mode Client">
      <i class="fas fa-user"></i>
      <span>Client</span>
    </a>
    <a href="index.php?role=vendeur&page=mes_produits" class="role-item <?= $currentRole === 'vendeur' ? 'active' : '' ?>" title="Mode Vendeur">
      <i class="fas fa-store"></i>
      <span>Vendeur</span>
    </a>
    <a href="index.php?role=admin&page=admin_dashboard" class="role-item <?= $currentRole === 'admin' ? 'active' : '' ?>" title="Mode Admin">
      <i class="fas fa-shield"></i>
      <span>Admin</span>
    </a>
  </div>

  <div class="sidebar-section-label">Navigation</div>

  <nav class="sidebar-nav">
    <a href="index.php?page=admin_dashboard" class="sidebar-item <?= $currentPage === 'admin_dashboard' ? 'active' : '' ?>">
      <i class="fas fa-gauge-high icon"></i> Dashboard
    </a>

    <div class="sidebar-section-label" style="margin-top:0.5rem; padding:0 0.2rem;">Gestion</div>

    <a href="index.php?page=admin_produits" class="sidebar-item <?= $currentPage === 'admin_produits' ? 'active' : '' ?>">
      <i class="fas fa-box icon"></i> Produits
      <?php if ($pendingCount > 0): ?>
      <span class="badge badge-warn"><?= $pendingCount ?></span>
      <?php endif; ?>
    </a>

    <a href="index.php?page=admin_categories" class="sidebar-item <?= $currentPage === 'admin_categories' || strpos($currentPage, 'admin_categorie') === 0 ? 'active' : '' ?>">
      <i class="fas fa-tags icon"></i> Catégories
    </a>

    <a href="index.php?page=admin_commandes" class="sidebar-item <?= $currentPage === 'admin_commandes' || strpos($currentPage, 'admin_commande') === 0 ? 'active' : '' ?>">
      <i class="fas fa-receipt icon"></i> Commandes
    </a>

    <div class="sidebar-section-label" style="margin-top:0.5rem; padding:0 0.2rem;">Liens Rapides</div>

    <a href="index.php?page=produits" class="sidebar-item" target="_blank">
      <i class="fas fa-globe icon"></i> Voir FrontOffice
      <i class="fas fa-external-link-alt" style="margin-left:auto; font-size:0.7rem; color:rgba(203,214,237,0.45);"></i>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="avatar"><?= strtoupper(substr($userName, 0, 1)) ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars($userName) ?></div>
        <div class="user-role">Mode Admin</div>
      </div>
      <button class="theme-toggle-btn" onclick="toggleTheme()" title="Changer le thème">
        <i class="fas fa-moon" id="themeIcon"></i>
      </button>
    </div>
  </div>
</aside>
