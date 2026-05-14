<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'SkillBridge Produits' ?></title>
<link rel="stylesheet" href="views/assets/css/front.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
  <a href="index.php?page=home" class="navbar-brand">
    <img src="logo.png" alt="SkillBridge" style="height: 42px; width: auto;">
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
        <i class="fas fa-box" style="font-size:0.85rem"></i>
        Produits <span class="arrow">▾</span>
      </span>
      <div class="dropdown-menu">
        <a href="index.php?page=all_produits" class="dropdown-item">
          <i class="fas fa-th-large icon"></i> Tous les Produits
        </a>
        <div style="border-top: 1px solid var(--border); margin: 6px 0;"></div>
        <?php if (isset($allCategories)): ?>
        <?php foreach ($allCategories as $cat): ?>
        <a href="index.php?page=all_produits&categorie=<?= $cat->getId() ?>" class="dropdown-item">
          <i class="<?= htmlspecialchars($cat->getIcone()) ?> icon"></i>
          <?= htmlspecialchars($cat->getNomCategorie()) ?>
          <?php if ($cat->getNbProduits() > 0): ?>
          <span class="badge-count"><?= $cat->getNbProduits() ?></span>
          <?php endif; ?>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </li>

    <?php if (($_SESSION['role'] ?? '') === 'vendeur'): ?>
    <li class="nav-item">
      <a href="index.php?role=vendeur&page=mes_produits" class="nav-link">
        <i class="fas fa-store" style="font-size:0.85rem"></i>
        Mes Produits
      </a>
    </li>
    <li class="nav-item">
      <a href="index.php?page=mes_messages" class="nav-link">
        <i class="fas fa-comments" style="font-size:0.85rem"></i>
        Messages
      </a>
    </li>
    <?php endif; ?>

    <li class="nav-item">
      <a href="index.php?page=mes_commandes" class="nav-link">
        <i class="fas fa-receipt" style="font-size:0.85rem"></i>
        Mes Commandes
      </a>
    </li>
  </ul>

  <div class="navbar-actions">
    <!-- Cart Icon -->
    <?php 
    $panierCount = 0;
    if (isset($_SESSION['panier'])) {
        foreach ($_SESSION['panier'] as $item) {
            $panierCount += $item['quantite'];
        }
    }
    ?>
    <a href="index.php?page=panier" style="position: relative; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 14px; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12); color: #fff; text-decoration: none; transition: all 0.22s;">
      <i class="fas fa-shopping-cart"></i>
      <?php if ($panierCount > 0): ?>
      <span style="position: absolute; top: -5px; right: -5px; background: linear-gradient(135deg, var(--amber), var(--amber-light)); color: white; font-size: 0.65rem; font-weight: 800; width: 20px; height: 20px; border-radius: 999px; display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 14px rgba(224,112,32,.3);">
        <?= $panierCount ?>
      </span>
      <?php endif; ?>
    </a>

    <!-- Account Switcher -->
    <details class="account-switcher" style="position: relative;">
      <summary style="list-style: none; cursor: pointer;">
        <div class="accounts-btn" style="display: inline-flex; align-items: center; gap: 0.7rem;">
          <i class="fas fa-user-circle"></i>
          <span style="font-size: 0.82rem; font-weight: 700; color: rgba(255,255,255,.72);">Compte</span>
          <span style="font-size: 0.9rem; font-weight: 700;">
            <?= ucfirst($_SESSION['role'] ?? 'Client') ?>
          </span>
          <i class="fas fa-chevron-down" style="font-size: 0.7rem; color: rgba(255,255,255,.5);"></i>
        </div>
      </summary>
      <div class="accounts-menu" style="position: absolute; top: calc(100% + 12px); right: 0; width: 280px; padding: 1rem; z-index: 1200;">
        <div style="margin-bottom: .9rem; padding-bottom: .85rem; border-bottom: 1px solid var(--border); text-transform: uppercase; letter-spacing: .08em; font-size: .78rem; font-weight: 800; color: var(--text-muted);">
          Changer de mode
        </div>

        <a href="index.php?page=all_produits" style="display: flex; align-items: center; gap: .75rem; padding: .88rem .5rem; text-decoration: none; color: var(--text-primary); border-radius: 14px; transition: all .2s ease; font-weight: 700;">
          <i class="fas fa-user" style="width: 18px; text-align: center; color: var(--amber);"></i>
          Client
          <i class="fas fa-chevron-right" style="margin-left: auto; font-size: .8rem; color: var(--text-muted);"></i>
        </a>

        <a href="index.php?role=vendeur&page=mes_produits" style="display: flex; align-items: center; gap: .75rem; padding: .88rem .5rem; text-decoration: none; color: var(--text-primary); border-radius: 14px; transition: all .2s ease; font-weight: 700; <?= ($_SESSION['role'] ?? '') === 'vendeur' ? 'background: rgba(224,112,32,.08);' : '' ?>">
          <i class="fas fa-store" style="width: 18px; text-align: center; color: var(--amber);"></i>
          Vendeur
          <?php if (($_SESSION['role'] ?? '') === 'vendeur'): ?>
          <span style="margin-left: auto; padding: .3rem .6rem; border-radius: 10px; background: linear-gradient(135deg, var(--amber), var(--amber-light)); color: #fff; font-size: .72rem; font-weight: 800; box-shadow: 0 8px 16px rgba(224,112,32,.2);">Actif</span>
          <?php else: ?>
          <i class="fas fa-chevron-right" style="margin-left: auto; font-size: .8rem; color: var(--text-muted);"></i>
          <?php endif; ?>
        </a>

        <a href="index.php?role=admin&page=admin_dashboard" style="display: flex; align-items: center; gap: .75rem; padding: .88rem .5rem; text-decoration: none; color: var(--text-primary); border-radius: 14px; transition: all .2s ease; font-weight: 700; <?= ($_SESSION['role'] ?? '') === 'admin' ? 'background: rgba(224,112,32,.08);' : '' ?>">
          <i class="fas fa-shield" style="width: 18px; text-align: center; color: var(--amber);"></i>
          Admin
          <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
          <span style="margin-left: auto; padding: .3rem .6rem; border-radius: 10px; background: linear-gradient(135deg, var(--amber), var(--amber-light)); color: #fff; font-size: .72rem; font-weight: 800;">Actif</span>
          <?php else: ?>
          <i class="fas fa-chevron-right" style="margin-left: auto; font-size: .8rem; color: var(--text-muted);"></i>
          <?php endif; ?>
        </a>

        <?php if (isset($_SESSION['user'])): ?>
        <div style="margin-top: .5rem; padding-top: .8rem; border-top: 1px solid var(--border);">
          <a href="index.php?page=logout" style="display: flex; align-items: center; gap: .75rem; padding: .88rem .5rem; text-decoration: none; color: var(--danger); border-radius: 14px; transition: all .2s ease; font-weight: 700;">
            <i class="fas fa-sign-out-alt" style="width: 18px; text-align: center;"></i>
            Déconnexion
          </a>
        </div>
        <?php endif; ?>
      </div>
    </details>
  </div>
</nav>
