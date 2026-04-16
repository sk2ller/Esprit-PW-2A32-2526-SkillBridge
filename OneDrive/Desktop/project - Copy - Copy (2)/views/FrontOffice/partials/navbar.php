<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'SkillBridge Services' ?></title>
<link rel="stylesheet" href="views/assets/css/front.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php
require_once __DIR__ . '/../../../models/Categorie.php';
$catModel = new Categorie();
$allCategories = $catModel->getAll();
?>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <img src="logo.png" alt="Logo" style="height: 40px; width: auto;">
  </a>

  <ul class="navbar-nav">
    <li class="nav-item">
      <a href="index.php?page=home" class="nav-link">
        <i class="fas fa-home" style="font-size:0.85rem"></i>
        Accueil
      </a>
    </li>

    <li class="nav-item">
      <span class="nav-link">
        <i class="fas fa-grid-2" style="font-size:0.85rem"></i>
        Services <span class="arrow">▾</span>
      </span>
      <div class="dropdown-menu">
        <a href="index.php?page=all_services" class="dropdown-item">
          <i class="fas fa-briefcase icon"></i> Tous les Services
        </a>
        <div style="border-top: 1px solid var(--border); margin: 4px 0;"></div>
        <?php foreach ($allCategories as $cat): ?>
        <a href="index.php?page=all_services&categorie=<?= $cat['id_categorie'] ?>" class="dropdown-item">
          <i class="<?= htmlspecialchars($cat['icone']) ?> icon"></i>
          <?= htmlspecialchars($cat['nom_categorie']) ?>
          <?php if ($cat['nb_services'] > 0): ?>
          <span class="badge-count"><?= $cat['nb_services'] ?></span>
          <?php endif; ?>
        </a>
        <?php endforeach; ?>
      </div>
    </li>

    <li class="nav-item">
      <span class="nav-link">
        <i class="fas fa-briefcase" style="font-size:0.85rem"></i>
        Offres Job <span class="arrow">▾</span>
      </span>
      <div class="dropdown-menu">
        <a href="index.php?page=offres" class="dropdown-item">
          <i class="fas fa-search icon"></i> Voir les Offres
        </a>
        <a href="index.php?page=mes_offres&id_client=1" class="dropdown-item">
          <i class="fas fa-plus icon"></i> Mes Offres
        </a>
        <a href="index.php?page=create_offre&id_client=1" class="dropdown-item">
          <i class="fas fa-pen-to-square icon"></i> Publier une Offre
        </a>
      </div>
    </li>

    <li class="nav-item">
      <a href="index.php?role=freelancer&page=my_services" class="nav-link">
        <i class="fas fa-layer-group" style="font-size:0.85rem"></i>
        Mes Services
      </a>
    </li>
  </ul>

  <div class="navbar-actions">
    <!-- Role Switcher Dropdown -->
    <div class="accounts-dropdown" style="position: relative;">
      <button class="accounts-btn" onclick="toggleAccountsMenu()" style="padding: 0.6rem 1rem; border-radius: 8px; background: var(--bg-secondary); border: 1px solid var(--border); color: var(--text-primary); text-decoration: none; display: flex; align-items: center; gap: 6px; transition: all 0.3s ease; font-weight: 600; font-size: 0.9rem; cursor: pointer;">
        <i class="fas fa-user-circle"></i> Accounts
        <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
      </button>
      
      <div class="accounts-menu" id="accountsMenu" style="position: absolute; top: 100%; right: 0; margin-top: 0.5rem; background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; min-width: 220px; box-shadow: 0 8px 24px rgba(0,0,0,0.3); display: none; z-index: 1000; overflow: hidden;">
        
        <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px;">Modes</span>
          <button onclick="toggleAccountsMenu()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.2rem;">✕</button>
        </div>
        
        <a href="index.php?page=all_services" class="accounts-item" style="padding: 0.75rem 1rem; display: flex; align-items: center; gap: 10px; color: var(--text-primary); text-decoration: none; transition: all 0.2s ease; border-left: 3px solid transparent;">
          <i class="fas fa-user" style="font-size: 1rem;"></i>
          <span style="font-weight: 500;">Client</span>
        </a>
        
        <a href="index.php?role=freelancer&page=my_services" class="accounts-item" style="padding: 0.75rem 1rem; display: flex; align-items: center; gap: 10px; color: var(--text-primary); text-decoration: none; transition: all 0.2s ease; border-left: 3px solid <?= ($_SESSION['role'] ?? '') === 'freelancer' ? 'var(--accent-purple-light)' : 'transparent' ?>; background: <?= ($_SESSION['role'] ?? '') === 'freelancer' ? 'rgba(124, 58, 237, 0.1)' : 'transparent' ?>;">
          <i class="fas fa-briefcase" style="font-size: 1rem;"></i>
          <span style="font-weight: 500;">Freelancer</span>
        </a>
        
        <a href="index.php?role=admin&page=admin_dashboard" class="accounts-item" style="padding: 0.75rem 1rem; display: flex; align-items: center; gap: 10px; color: var(--text-primary); text-decoration: none; transition: all 0.2s ease; border-left: 3px solid <?= ($_SESSION['role'] ?? '') === 'admin' ? 'var(--accent-purple-light)' : 'transparent' ?>; background: <?= ($_SESSION['role'] ?? '') === 'admin' ? 'rgba(124, 58, 237, 0.1)' : 'transparent' ?>;">
          <i class="fas fa-shield" style="font-size: 1rem;"></i>
          <span style="font-weight: 500;">Admin</span>
        </a>
      </div>
    </div>
  </div>

  <script>
  function toggleAccountsMenu() {
    const menu = document.getElementById('accountsMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
  }
  
  // Close menu when clicking outside
  document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.accounts-dropdown');
    if (!dropdown.contains(event.target)) {
      document.getElementById('accountsMenu').style.display = 'none';
    }
  });
  </script>
</nav>
