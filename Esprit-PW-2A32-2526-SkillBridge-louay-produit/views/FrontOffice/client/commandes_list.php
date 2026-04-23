<?php
$pageTitle = 'Mes Commandes - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
?>

<div class="page-top">
<div class="container">

  <div class="section-header" style="margin-bottom:2rem;">
    <div>
      <h1 style="font-family:'Space Grotesk',sans-serif; font-size:1.8rem; font-weight:800;">📋 Mes Commandes</h1>
      <p style="color:var(--text-muted); font-size:0.9rem;">Suivez l'état de vos commandes sur SkillBridge</p>
    </div>
    <a href="index.php?page=all_produits" class="btn-primary" style="font-size:0.875rem; padding:10px 20px;">
      <i class="fas fa-shopping-bag"></i> Continuer les achats
    </a>
  </div>

  <?php if (!empty($_GET['success'])): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <?php
    $msgs = ['1'=>'Commande passée avec succès ! Vous recevrez une confirmation.','2'=>'Commande modifiée.','3'=>'Commande annulée.'];
    echo $msgs[$_GET['success']] ?? 'Opération effectuée.';
    ?>
  </div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="dashboard-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom:2rem;">
    <?php
    $totalC = count($commandes);
    $enAttenteC = count(array_filter($commandes, fn($c) => $c->getStatut() === 'en_attente'));
    $enCoursC = count(array_filter($commandes, fn($c) => in_array($c->getStatut(), ['confirmee','expediee'])));
    $livreeC = count(array_filter($commandes, fn($c) => $c->getStatut() === 'livree'));
    ?>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(124,58,237,0.15); color:var(--accent-purple-light);"><i class="fas fa-receipt"></i></div>
      <div class="stat-value"><?= $totalC ?></div>
      <div class="stat-label">Total</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(245,158,11,0.15); color:#fbbf24;"><i class="fas fa-clock"></i></div>
      <div class="stat-value"><?= $enAttenteC ?></div>
      <div class="stat-label">En attente</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(59,130,246,0.15); color:#60a5fa;"><i class="fas fa-truck"></i></div>
      <div class="stat-value"><?= $enCoursC ?></div>
      <div class="stat-label">En cours</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(16,185,129,0.15); color:#34d399;"><i class="fas fa-check-double"></i></div>
      <div class="stat-value"><?= $livreeC ?></div>
      <div class="stat-label">Livrées</div>
    </div>
  </div>

  <?php if (empty($commandes)): ?>
  <div class="empty-state">
    <div class="icon">📦</div>
    <h3>Aucune commande pour le moment</h3>
    <p>Explorez nos produits et passez votre première commande</p>
    <a href="index.php?page=all_produits" class="btn-primary" style="display:inline-flex; margin-top:1rem;">
      <i class="fas fa-shopping-bag"></i> Explorer les produits
    </a>
  </div>
  <?php else: ?>

  <!-- Commandes list -->
  <div style="display:flex; flex-direction:column; gap:1rem;">
    <?php foreach ($commandes as $cmd): ?>
    <?php
    $statusConfig = [
        'en_attente' => ['badge' => 'badge-pending', 'label' => '⏳ En attente', 'icon' => 'fa-clock', 'color' => '#fbbf24'],
        'confirmee'  => ['badge' => 'badge-info', 'label' => '✓ Confirmée', 'icon' => 'fa-check', 'color' => '#60a5fa'],
        'expediee'   => ['badge' => 'badge-info', 'label' => '🚚 Expédiée', 'icon' => 'fa-truck', 'color' => '#818cf8'],
        'livree'     => ['badge' => 'badge-disponible', 'label' => '✅ Livrée', 'icon' => 'fa-check-double', 'color' => '#34d399'],
        'annulee'    => ['badge' => 'badge-rupture', 'label' => '✗ Annulée', 'icon' => 'fa-ban', 'color' => '#f87171'],
    ];
    $sc = $statusConfig[$cmd->getStatut()] ?? $statusConfig['en_attente'];
    ?>
    <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.2rem 1.5rem; display:flex; align-items:center; gap:1.5rem; transition:all 0.2s; border-left:4px solid <?= $sc['color'] ?>;">
      <!-- Icon -->
      <div style="width:50px; height:50px; border-radius:10px; background:rgba(124,58,237,0.1); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <i class="fas <?= $sc['icon'] ?>" style="color:<?= $sc['color'] ?>; font-size:1.2rem;"></i>
      </div>
      <!-- Info -->
      <div style="flex:1; min-width:0;">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
          <span style="font-weight:700; font-size:0.95rem;"><?= htmlspecialchars($cmd->getNomProduit()) ?></span>
          <span class="badge <?= $sc['badge'] ?>" style="font-size:0.7rem;"><?= $sc['label'] ?></span>
        </div>
        <div style="color:var(--text-muted); font-size:0.82rem;">
          Commande #<?= $cmd->getId() ?> · <?= date('d/m/Y H:i', strtotime($cmd->getCreatedAt())) ?> · Qté: <?= $cmd->getQuantite() ?>
        </div>
      </div>
      <!-- Prix -->
      <div style="text-align:right; flex-shrink:0;">
        <div style="font-weight:800; color:var(--accent-purple-light); font-size:1.1rem;"><?= number_format($cmd->getPrixTotal(), 2) ?> DT</div>
      </div>
      <!-- Actions -->
      <div style="display:flex; gap:6px; flex-shrink:0;">
        <a href="index.php?page=commande_detail&id=<?= $cmd->getId() ?>" class="btn-sm btn-sm-purple" title="Détails">
          <i class="fas fa-eye"></i>
        </a>
        <?php if ($cmd->getStatut() === 'en_attente'): ?>
        <a href="index.php?page=annuler_commande&id=<?= $cmd->getId() ?>" class="btn-sm btn-sm-red"
           onclick="return confirm('Annuler cette commande ?')" title="Annuler">
          <i class="fas fa-times"></i>
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>

</div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
