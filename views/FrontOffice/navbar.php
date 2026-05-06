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

<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <span class="logo-icon"><i class="fas fa-bolt"></i></span>
    <span>SkillBridge <span>Services</span></span>
  </a>

  <ul class="navbar-nav">
    <li class="nav-item">
      <a href="index.php?page=home" class="nav-link <?= (($_GET['page'] ?? 'home') === 'home') ? 'active' : '' ?>">
        <i class="fas fa-home" style="font-size:0.85rem"></i>
        Accueil
      </a>
    </li>

    <li class="nav-item">
      <span class="nav-link <?= in_array(($_GET['page'] ?? ''), ['offres', 'mes_offres', 'create_offre', 'client_candidatures']) ? 'active' : '' ?>">
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
        <a href="index.php?page=client_candidatures&id_client=1" class="dropdown-item">
          <i class="fas fa-file-signature icon"></i> Candidatures reçues
        </a>
      </div>
    </li>

    <?php if (($_SESSION['role'] ?? '') === 'freelancer'): ?>
    <li class="nav-item">
      <a href="index.php?page=job_match"
         class="nav-link <?= (($_GET['page'] ?? '') === 'job_match') ? 'active' : '' ?>"
         style="display:flex; align-items:center; gap:.4rem;">
        <i class="fas fa-brain" style="font-size:0.85rem; color:var(--amber-light)"></i>
        Job Match
        <span style="background:var(--amber); color:#fff; font-size:.58rem; font-weight:800;
                     padding:.1rem .4rem; border-radius:4px; letter-spacing:.04em;
                     text-transform:uppercase; line-height:1.6;">IA</span>
      </a>
    </li>
    <?php endif; ?>

  </ul>

  <div class="navbar-actions">
    <div class="role-switcher">
      <a href="index.php?role=client&page=mes_offres&id_client=1"
         class="role-btn <?= ($_SESSION['role'] ?? 'client') === 'client' ? 'active' : '' ?>"
         title="Mode Client">
        <i class="fas fa-user"></i> Client
      </a>
      <a href="index.php?role=freelancer&page=offres"
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
