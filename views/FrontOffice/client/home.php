<?php
$pageTitle = 'Accueil - SkillBridge Platform';
include __DIR__ . '/../partials/navbar.php';
?>

<div class="page-top">
<div class="page-container" style="max-width: 1200px; padding: 3rem 2rem;">

  <!-- Hero Section -->
  <div style="text-align: center; margin-bottom: 4rem;">
    <h1 style="font-family:'Space Grotesk',sans-serif; font-size:2.5rem; font-weight:800; margin-bottom:1rem;">
      Bienvenue sur <span style="color: var(--accent-purple-light);">SkillBridge Platform</span>
    </h1>
    <p style="font-size:1.1rem; color: var(--text-muted); max-width: 600px; margin: 0 auto;">
      La plateforme #1 pour trouver les meilleurs freelancers et publier vos projets
    </p>
  </div>

  <!-- Navigation Grid -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-bottom: 4rem;">

    <!-- SERVICES Section -->
    <div style="background: linear-gradient(135deg, rgba(124,58,237,0.1), rgba(59,130,246,0.1)); border: 2px solid var(--accent-light); border-radius: 16px; padding: 2rem; text-align: center;">
      <div style="font-size: 3rem; margin-bottom: 1rem;">🛠️</div>
      <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 0.75rem;">Services Freelance</h2>
      <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Découvrez les meilleurs services offerts par les freelancers</p>
      
      <div style="display: flex; flex-direction: column; gap: 0.75rem;">
        <a href="index.php?page=all_services" class="btn-primary" style="text-decoration: none; text-align: center; padding: 12px 20px; border-radius: 8px; display: inline-block; font-weight: 600;">
          <i class="fas fa-search"></i> Voir les Services
        </a>
        <a href="index.php?page=categories" class="btn-outline" style="text-decoration: none; text-align: center; padding: 12px 20px; border-radius: 8px; display: inline-block; font-weight: 600; border: 2px solid var(--accent-light); color: var(--accent-light); background: transparent;">
          <i class="fas fa-grid-2"></i> Par Catégories
        </a>
      </div>
    </div>

    <!-- OFFRES JOB Section -->
    <div style="background: linear-gradient(135deg, rgba(34,197,94,0.1), rgba(34,197,94,0.05)); border: 2px solid var(--accent-green); border-radius: 16px; padding: 2rem; text-align: center;">
      <div style="font-size: 3rem; margin-bottom: 1rem;">📢</div>
      <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 0.75rem;">Offres Job</h2>
      <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Trouvez des missions intéressantes ou publiez vos offres</p>
      
      <div style="display: flex; flex-direction: column; gap: 0.75rem;">
        <a href="index.php?page=offres" class="btn-primary" style="text-decoration: none; text-align: center; padding: 12px 20px; border-radius: 8px; display: inline-block; font-weight: 600; background: var(--accent-green);">
          <i class="fas fa-briefcase"></i> Consulter Offres
        </a>
        <a href="index.php?page=create_offre&id_client=1" class="btn-outline" style="text-decoration: none; text-align: center; padding: 12px 20px; border-radius: 8px; display: inline-block; font-weight: 600; border: 2px solid var(--accent-green); color: var(--accent-green); background: transparent;">
          <i class="fas fa-plus"></i> Publier une Offre
        </a>
      </div>
    </div>

    <!-- CLIENT Section -->
    <div style="background: linear-gradient(135deg, rgba(245,158,11,0.1), rgba(245,158,11,0.05)); border: 2px solid var(--accent-orange); border-radius: 16px; padding: 2rem; text-align: center;">
      <div style="font-size: 3rem; margin-bottom: 1rem;">👤</div>
      <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 0.75rem;">Gestion Client</h2>
      <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Gérez vos offres et consultez votre historique</p>
      
      <div style="display: flex; flex-direction: column; gap: 0.75rem;">
        <a href="index.php?page=mes_offres&id_client=1" class="btn-primary" style="text-decoration: none; text-align: center; padding: 12px 20px; border-radius: 8px; display: inline-block; font-weight: 600; background: var(--accent-orange);">
          <i class="fas fa-folder"></i> Mes Offres
        </a>
        <a href="index.php?page=all_services" class="btn-outline" style="text-decoration: none; text-align: center; padding: 12px 20px; border-radius: 8px; display: inline-block; font-weight: 600; border: 2px solid var(--accent-orange); color: var(--accent-orange); background: transparent;">
          <i class="fas fa-user"></i> Voir Services
        </a>
      </div>
    </div>

  </div>

  <!-- Features Section -->
  <div style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 16px; padding: 3rem; margin-bottom: 4rem;">
    <h2 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 2rem; text-align: center;">
      ✨ Les Avantages de SkillBridge Platform
    </h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
      <div style="text-align: center;">
        <div style="font-size: 2rem; margin-bottom: 0.75rem;">🔒</div>
        <h3 style="font-weight: 700; margin-bottom: 0.5rem;">Paiement Sécurisé</h3>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Transactions protégées et garanties</p>
      </div>
      
      <div style="text-align: center;">
        <div style="font-size: 2rem; margin-bottom: 0.75rem;">⭐</div>
        <h3 style="font-weight: 700; margin-bottom: 0.5rem;">Freelancers Vérifiés</h3>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Tous nos experts sont certifiés</p>
      </div>
      
      <div style="text-align: center;">
        <div style="font-size: 2rem; margin-bottom: 0.75rem;">⚡</div>
        <h3 style="font-weight: 700; margin-bottom: 0.5rem;">Support 24/7</h3>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Une équipe toujours disponible</p>
      </div>
      
      <div style="text-align: center;">
        <div style="font-size: 2rem; margin-bottom: 0.75rem;">📊</div>
        <h3 style="font-weight: 700; margin-bottom: 0.5rem;">Analytics Avancés</h3>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Suivez vos performances</p>
      </div>
    </div>
  </div>

  <!-- ADMIN Quick Access -->
  <div style="background: linear-gradient(135deg, rgba(239,68,68,0.1), rgba(239,68,68,0.05)); border: 2px dashed var(--danger); border-radius: 16px; padding: 2rem; text-align: center;">
    <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 1rem;">
      <i class="fas fa-shield"></i> Administration
    </h2>
    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Accès au panneau d'administration pour modérer le contenu</p>
    
    <a href="index.php?page=admin_dashboard" class="btn-primary" style="text-decoration: none; padding: 12px 30px; border-radius: 8px; display: inline-block; font-weight: 600; background: var(--danger);">
      <i class="fas fa-cog"></i> Aller à l'Admin
    </a>
  </div>

</div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
