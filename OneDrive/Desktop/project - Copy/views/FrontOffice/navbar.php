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
require_once __DIR__ . '/../../controllers/CategorieController.php';

$categorieController = new CategorieController();
$allCategories = $categorieController->listCategories();
?>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <span class="logo-icon"><i class="fas fa-bolt"></i></span>
    <span>SkillBridge <span>Services</span></span>
  </a>

  <ul class="navbar-nav">
    <li class="nav-item">
      <span class="nav-link">
        <i class="fas fa-grid-2" style="font-size:0.85rem"></i>
        Catégories <span class="arrow">▾</span>
      </span>
      <div class="dropdown-menu">
        <?php foreach ($allCategories as $cat): ?>
        <a href="index.php?page=services&categorie=<?= $cat['id_categorie'] ?>" class="dropdown-item">
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
      <a href="index.php?page=services" class="nav-link <?= (($_GET['page'] ?? '') === 'services') ? 'active' : '' ?>">
        <i class="fas fa-briefcase" style="font-size:0.85rem"></i>
        Services
      </a>
    </li>

    <li class="nav-item">
      <a href="index.php?role=freelancer&page=my_services" class="nav-link">
        <i class="fas fa-layer-group" style="font-size:0.85rem"></i>
        Mes Services
      </a>
    </li>
  </ul>

  <div class="navbar-actions">
    <div class="role-switcher" style="display: flex; gap: 0.5rem;">
      <a href="index.php?role=client&page=services" 
         class="role-btn <?= ($_SESSION['role'] ?? 'client') === 'client' ? 'active' : '' ?>" 
         title="Mode Client">
        <i class="fas fa-user"></i> Client
      </a>
      <a href="index.php?role=freelancer&page=my_services" 
         class="role-btn <?= ($_SESSION['role'] ?? '') === 'freelancer' ? 'active' : '' ?>"
         title="Mode Freelancer">
        <i class="fas fa-briefcase"></i> Freelancer
      </a>
      <a href="index.php?role=admin&page=admin_dashboard" 
         class="role-btn <?= ($_SESSION['role'] ?? '') === 'admin' ? 'active' : '' ?>"
         title="Mode Admin">
        <i class="fas fa-shield"></i> Admin
      </a>
    </div>
  </div>
</nav>
