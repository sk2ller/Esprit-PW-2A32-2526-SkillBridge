<?php
$currentPage = $_GET['page'] ?? '';
?>

<aside class="freelancer-sidebar">
  <div class="freelancer-sidebar-brand">
    <div class="freelancer-sidebar-badge">
      <i class="fas fa-briefcase"></i>
    </div>
    <div>
      <div class="freelancer-sidebar-title">Freelancer</div>
      <div class="freelancer-sidebar-subtitle">Dashboard</div>
    </div>
  </div>

  <div class="freelancer-sidebar-section">Navigation</div>

  <nav class="freelancer-sidebar-nav">
    <a href="index.php?role=freelancer&page=my_services" class="freelancer-sidebar-link <?= $currentPage === 'my_services' ? 'active' : '' ?>">
      <i class="fas fa-layer-group"></i>
      <span>Mes Services</span>
    </a>

    <a href="index.php?role=freelancer&page=create_service" class="freelancer-sidebar-link <?= $currentPage === 'create_service' ? 'active' : '' ?>">
      <i class="fas fa-plus-circle"></i>
      <span>Ajouter Service</span>
    </a>

    <a href="index.php?role=freelancer&page=chat" class="freelancer-sidebar-link <?= $currentPage === 'chat' ? 'active' : '' ?>">
      <i class="fas fa-comments"></i>
      <span>Messages</span>
    </a>

    <a href="index.php?page=services" class="freelancer-sidebar-link">
      <i class="fas fa-globe"></i>
      <span>Voir Catalogue</span>
    </a>
  </nav>

  <div class="freelancer-sidebar-section">Raccourcis</div>

  <div class="freelancer-sidebar-panel">
    <div class="freelancer-sidebar-panel-title">Espace de travail</div>
    <p>Gerez vos services depuis un tableau de bord clair avec actions rapides.</p>
  </div>
</aside>
