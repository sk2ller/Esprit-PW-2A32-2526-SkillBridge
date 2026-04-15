<?php
$pageTitle = 'Mes Services - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
?>

<div class="page-top">
<div class="page-container">

  <!-- Alerts -->
  <?php if (!empty($_GET['success'])): ?>
    <?php $msgs = ['1'=>'Service créé ! En attente d\'approbation.','2'=>'Service mis à jour.','3'=>'Service supprimé.']; ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $msgs[$_GET['success']] ?? '' ?></div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="dashboard-grid">
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(124,58,237,0.15); color:var(--accent-purple-light);"><i class="fas fa-layer-group"></i></div>
      <div class="stat-value"><?= count($services) ?></div>
      <div class="stat-label">Total Services</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(16,185,129,0.15); color:var(--accent-green);"><i class="fas fa-check-circle"></i></div>
      <div class="stat-value"><?= count(array_filter($services, fn($s) => $s['statut'] === 'actif')) ?></div>
      <div class="stat-label">Services Actifs</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(245,158,11,0.15); color:var(--accent-orange);"><i class="fas fa-clock"></i></div>
      <div class="stat-value"><?= count(array_filter($services, fn($s) => $s['statut'] === 'en_attente')) ?></div>
      <div class="stat-label">En Attente</div>
    </div>
  </div>

  <div class="section-header">
    <h1 class="section-title">Mes <span>Services</span></h1>
    <a href="index.php?page=create_service" class="btn-primary" style="font-size:0.875rem; padding:10px 20px;">
      <i class="fas fa-plus"></i> Nouveau Service
    </a>
  </div>

  <?php if (empty($services)): ?>
  <div class="empty-state">
    <div class="icon">💼</div>
    <h3>Aucun service créé</h3>
    <p>Commencez par créer votre premier service.</p>
    <a href="index.php?page=create_service" class="btn-primary" style="display:inline-flex; margin-top:1rem;">
      <i class="fas fa-plus"></i> Créer un service
    </a>
  </div>
  <?php else: ?>
  <div class="services-grid">
    <?php foreach ($services as $s): ?>
    <div class="service-card">
      <div class="service-card-image">
        <i class="fas fa-briefcase" style="color:rgba(255,255,255,0.15); font-size:3rem; position:relative; z-index:1;"></i>
        <div style="position:absolute; top:10px; right:10px;">
          <?php
          $badgeMap = ['actif'=>'badge-actif','suspendu'=>'badge-suspendu','en_attente'=>'badge-pending'];
          $labelMap = ['actif'=>'Actif','suspendu'=>'Suspendu','en_attente'=>'En attente'];
          ?>
          <span class="badge <?= $badgeMap[$s['statut']] ?>"><?= $labelMap[$s['statut']] ?></span>
        </div>
      </div>
      <div class="service-card-body">
        <span class="service-category-tag"><?= htmlspecialchars($s['nom_categorie']) ?></span>
        <h3 class="service-title"><?= htmlspecialchars($s['titre']) ?></h3>
        <p style="color:var(--text-muted); font-size:0.82rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
          <?= htmlspecialchars($s['description']) ?>
        </p>
      </div>
      <div class="service-card-footer">
        <div class="service-price"><?= number_format($s['prix'], 2) ?> DT</div>
        <div style="display:flex; gap:6px;">
          <a href="index.php?page=edit_service&id=<?= $s['id_service'] ?>" class="btn-sm btn-sm-outline" title="Modifier">
            <i class="fas fa-edit"></i>
          </a>
          <a href="index.php?page=delete_service&id=<?= $s['id_service'] ?>"
             onclick="return confirm('Supprimer ce service ?')"
             class="btn-sm btn-sm-red" title="Supprimer">
            <i class="fas fa-trash"></i>
          </a>
          <a href="index.php?page=service_detail&id=<?= $s['id_service'] ?>" class="btn-sm btn-sm-purple" title="Voir">
            <i class="fas fa-eye"></i>
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
