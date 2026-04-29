<?php
$currentAction = $_GET['action'] ?? '';
$userName = trim(($_SESSION['user_prenom'] ?? 'Admin') . ' ' . ($_SESSION['user_nom'] ?? ''));
?>
<aside class="admin-sidebar">
  <div class="admin-brand">
    <img src="/Views/assets/img/logo1.png" alt="SkillBridge">
  </div>

  <div class="admin-sidebar-head">
    <div>
      <div class="admin-sidebar-title">Backoffice</div>
      <div class="admin-sidebar-subtitle">Gestion complete de la plateforme</div>
    </div>
  </div>

  <div class="admin-section-label">Navigation</div>
  <nav class="admin-nav">
    <a href="?action=userlist" class="admin-link <?= in_array($currentAction, ['userlist', 'statistics'], true) ? 'active' : '' ?>">
      <span class="admin-link-icon"><i class="fas fa-users"></i></span>
      <span class="admin-link-text">
        <strong>Utilisateurs</strong>
        <small>Comptes, roles et resume</small>
      </span>
      <span class="admin-link-pill">Users</span>
      <span class="admin-link-arrow"><i class="fas fa-chevron-right"></i></span>
    </a>
    <a href="?action=services_admin" class="admin-link <?= in_array($currentAction, ['services_admin', 'service_create_admin', 'service_edit_admin'], true) ? 'active' : '' ?>">
      <span class="admin-link-icon"><i class="fas fa-briefcase"></i></span>
      <span class="admin-link-text">
        <strong>Services</strong>
        <small>CRUD services</small>
      </span>
      <span class="admin-link-pill">CRUD</span>
      <span class="admin-link-arrow"><i class="fas fa-chevron-right"></i></span>
    </a>
    <a href="?action=categories_admin" class="admin-link <?= in_array($currentAction, ['categories_admin', 'category_create', 'category_edit'], true) ? 'active' : '' ?>">
      <span class="admin-link-icon"><i class="fas fa-tags"></i></span>
      <span class="admin-link-text">
        <strong>Categories</strong>
        <small>Organisation</small>
      </span>
      <span class="admin-link-arrow"><i class="fas fa-chevron-right"></i></span>
    </a>
    <a href="?action=job_offers_admin" class="admin-link <?= $currentAction === 'job_offers_admin' ? 'active' : '' ?>">
      <span class="admin-link-icon"><i class="fas fa-bullhorn"></i></span>
      <span class="admin-link-text">
        <strong>Offres Job</strong>
        <small>Publications clients</small>
      </span>
      <span class="admin-link-pill">Jobs</span>
      <span class="admin-link-arrow"><i class="fas fa-chevron-right"></i></span>
    </a>
    <a href="?action=job_applications_admin" class="admin-link <?= $currentAction === 'job_applications_admin' ? 'active' : '' ?>">
      <span class="admin-link-icon"><i class="fas fa-file-signature"></i></span>
      <span class="admin-link-text">
        <strong>Candidatures</strong>
        <small>Suivi freelancers</small>
      </span>
      <span class="admin-link-arrow"><i class="fas fa-chevron-right"></i></span>
    </a>    <a href="?action=brainstorming_admin" class="admin-link <?= $currentAction === 'brainstorming_admin' ? 'active' : '' ?>">
      <span class="admin-link-icon"><i class="fas fa-lightbulb"></i></span>
      <span class="admin-link-text">
        <strong>Brainstorming</strong>
        <small>Validation et suivi</small>
      </span>
      <span class="admin-link-arrow"><i class="fas fa-chevron-right"></i></span>
    </a>
    <a href="?action=idee_admin" class="admin-link <?= $currentAction === 'idee_admin' ? 'active' : '' ?>">
      <span class="admin-link-icon"><i class="fas fa-comments"></i></span>
      <span class="admin-link-text">
        <strong>Idees</strong>
        <small>Propositions et scoring</small>
      </span>
      <span class="admin-link-arrow"><i class="fas fa-chevron-right"></i></span>
    </a>
    <a href="?action=home" class="admin-link">
      <span class="admin-link-icon"><i class="fas fa-globe"></i></span>
      <span class="admin-link-text">
        <strong>Voir Front</strong>
        <small>Retour site public</small>
      </span>
      <span class="admin-link-arrow"><i class="fas fa-chevron-right"></i></span>
    </a>
  </nav>

  <div class="admin-sidebar-footer">
    <div class="admin-user-card">
      <div class="admin-avatar">A</div>
      <div>
        <div class="admin-user-name"><?= htmlspecialchars($userName) ?></div>
        <div class="admin-user-role">Mode Admin</div>
      </div>
      <a href="?action=home" class="admin-home-link" title="Retour front">
        <i class="fas fa-house"></i>
      </a>
    </div>
  </div>
</aside>

