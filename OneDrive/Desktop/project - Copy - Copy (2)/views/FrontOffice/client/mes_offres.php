<?php
$pageTitle = 'Mes Offres - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
$id_client = $_GET['id_client'] ?? 1;
?>

<div class="page-top">
<div class="page-container">

  <!-- Alerts -->
  <?php if (!empty($_GET['success'])): ?>
    <?php $msgs = ['1'=>'Offre publiée ! En attente d\'approbation.','2'=>'Offre mise à jour.','3'=>'Offre supprimée.']; ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $msgs[$_GET['success']] ?? '' ?></div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="dashboard-grid">
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(124,58,237,0.15); color:var(--accent-purple-light);"><i class="fas fa-briefcase"></i></div>
      <div class="stat-value"><?= $stats['total_offres'] ?? 0 ?></div>
      <div class="stat-label">Total Offres</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(16,185,129,0.15); color:var(--accent-green);"><i class="fas fa-check-circle"></i></div>
      <div class="stat-value"><?= $stats['offres_actives'] ?? 0 ?></div>
      <div class="stat-label">Offres Actives</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(245,158,11,0.15); color:var(--accent-orange);"><i class="fas fa-clock"></i></div>
      <div class="stat-value"><?= $stats['en_attente'] ?? 0 ?></div>
      <div class="stat-label">En Attente</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(239,68,68,0.15); color:var(--danger);"><i class="fas fa-ban"></i></div>
      <div class="stat-value"><?= $stats['suspendues'] ?? 0 ?></div>
      <div class="stat-label">Suspendues</div>
    </div>
  </div>

  <div class="section-header">
    <h1 class="section-title">Mes <span>Offres Job</span></h1>
    <a href="index.php?page=create_offre&id_client=<?= $id_client ?>" class="btn-primary" style="font-size:0.875rem; padding:10px 20px;">
      <i class="fas fa-plus"></i> Nouvelle Offre
    </a>
  </div>

  <?php if (empty($offres)): ?>
  <div class="empty-state">
    <div class="icon">📢</div>
    <h3>Aucune offre publiée</h3>
    <p>Commencez par publier votre première offre job pour trouver des freelancers.</p>
    <a href="index.php?page=create_offre&id_client=<?= $id_client ?>" class="btn-primary" style="display:inline-flex; margin-top:1rem;">
      <i class="fas fa-plus"></i> Publier une offre
    </a>
  </div>
  <?php else: ?>

  <div style="display:grid; gap:1rem;">
    <?php foreach ($offres as $offre): ?>
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:1.5rem; transition:all 0.3s;">
      
      <!-- Header -->
      <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:1rem;">
        <div>
          <h3 style="font-size:1.1rem; font-weight:700; margin-bottom:0.25rem;">
            <?= htmlspecialchars(substr($offre['titre'], 0, 60)) ?>
          </h3>
          <div style="display:flex; gap:1rem; font-size:0.85rem; color:var(--text-muted);">
            <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($offre['created_at'])) ?></span>
            <span><i class="fas fa-money-bill"></i> <?= number_format($offre['budget'], 2) ?> DT</span>
          </div>
        </div>
        
        <!-- Status Badge -->
        <?php 
        $statusColors = [
          'actif' => ['bg' => 'rgba(16,185,129,0.15)', 'color' => 'var(--accent-green)', 'text' => '✓ Actif'],
          'en_attente' => ['bg' => 'rgba(245,158,11,0.15)', 'color' => 'var(--accent-orange)', 'text' => '⏳ En attente'],
          'suspendu' => ['bg' => 'rgba(239,68,68,0.15)', 'color' => 'var(--danger)', 'text' => '⛔ Suspendu']
        ];
        $status = $statusColors[$offre['statut']] ?? $statusColors['en_attente'];
        ?>
        <span style="background:<?= $status['bg'] ?>; color:<?= $status['color'] ?>; padding:6px 12px; border-radius:20px; font-size:0.8rem; font-weight:600;">
          <?= $status['text'] ?>
        </span>
      </div>

      <!-- Description preview -->
      <p style="color:var(--text-muted); margin-bottom:1rem; line-height:1.5;">
        <?= htmlspecialchars(substr($offre['description'], 0, 150)) ?>...
      </p>

      <!-- Meta -->
      <div style="display:flex; gap:1rem; margin-bottom:1rem; flex-wrap:wrap; font-size:0.85rem;">
        <span style="background:var(--bg-light); padding:4px 10px; border-radius:6px;">
          <i class="fas fa-user-graduate"></i> <?= ucfirst($offre['niveau_requis']) ?>
        </span>
        <span style="background:var(--bg-light); padding:4px 10px; border-radius:6px;">
          <i class="fas fa-list"></i> <?= count(array_filter(array_map('trim', explode(',', $offre['competences_requises'])))) ?> compétences
        </span>
      </div>

      <!-- Actions -->
      <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
        <a href="index.php?page=offre_detail&id=<?= $offre['id_offre'] ?>" class="btn-outline" style="padding:8px 16px; font-size:0.85rem;">
          <i class="fas fa-eye"></i> Voir
        </a>
        <a href="index.php?page=edit_offre&id=<?= $offre['id_offre'] ?>" class="btn-outline" style="padding:8px 16px; font-size:0.85rem;">
          <i class="fas fa-edit"></i> Éditer
        </a>
        <a href="index.php?page=delete_offre&id=<?= $offre['id_offre'] ?>" class="btn-danger" style="padding:8px 16px; font-size:0.85rem; color:var(--danger); border: 1px solid var(--danger);" onclick="return confirm('Êtes-vous sûr ?');">
          <i class="fas fa-trash"></i> Supprimer
        </a>
      </div>

    </div>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>

</div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
