<?php
$pageTitle = 'Mes Produits - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
?>

<div class="page-top">
<div class="container">

  <div class="section-header" style="margin-bottom:2rem;">
    <div>
      <h1 style="font-family:'Space Grotesk',sans-serif; font-size:1.8rem; font-weight:800;">🏪 Mes Produits</h1>
      <p style="color:var(--text-muted); font-size:0.9rem;">Gérez vos produits numériques sur SkillBridge</p>
    </div>
    <a href="index.php?page=create_produit" class="btn-primary" style="font-size:0.875rem; padding:10px 20px;">
      <i class="fas fa-plus"></i> Ajouter un Produit
    </a>
  </div>

  <?php if (!empty($_GET['success'])): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <?php
    $msgs = ['1'=>'Produit créé avec succès ! Il sera soumis à approbation.','2'=>'Produit modifié avec succès.','3'=>'Produit supprimé.'];
    echo $msgs[$_GET['success']] ?? 'Opération effectuée.';
    ?>
  </div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="dashboard-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom:2rem;">
    <?php
    $totalP = count($produits);
    $dispoP = count(array_filter($produits, fn($p) => $p['statut'] === 'disponible'));
    $pendP = count(array_filter($produits, fn($p) => $p['statut'] === 'en_attente'));
    ?>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(124,58,237,0.15); color:var(--accent-purple-light);"><i class="fas fa-box"></i></div>
      <div class="stat-value"><?= $totalP ?></div>
      <div class="stat-label">Total Produits</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(16,185,129,0.15); color:#34d399;"><i class="fas fa-check"></i></div>
      <div class="stat-value"><?= $dispoP ?></div>
      <div class="stat-label">Disponibles</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(245,158,11,0.15); color:#fbbf24;"><i class="fas fa-clock"></i></div>
      <div class="stat-value"><?= $pendP ?></div>
      <div class="stat-label">En attente</div>
    </div>
  </div>

  <?php if (empty($produits)): ?>
  <div class="empty-state">
    <div class="icon">📦</div>
    <h3>Aucun produit pour le moment</h3>
    <p>Commencez à vendre en ajoutant votre premier produit</p>
    <a href="index.php?page=create_produit" class="btn-primary" style="display:inline-flex; margin-top:1rem;">
      <i class="fas fa-plus"></i> Ajouter un Produit
    </a>
  </div>
  <?php else: ?>
  <div class="products-grid">
    <?php foreach ($produits as $p): ?>
    <div class="product-card">
      <div class="product-card-image" style="background: linear-gradient(135deg, <?= ['#1a0533','#0a2240','#002a1f','#1a1000'][crc32($p['nom']) % 4] ?>, var(--bg-secondary));">
        <i class="<?= htmlspecialchars($p['icone'] ?? 'fas fa-box') ?>" style="color: rgba(255,255,255,0.2); font-size:4rem; position:relative; z-index:1;"></i>
      </div>
      <div class="product-card-body">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
          <span class="product-category-tag"><?= htmlspecialchars($p['nom_categorie']) ?></span>
          <?php
          $bmap = ['disponible'=>'badge-disponible','rupture'=>'badge-rupture','en_attente'=>'badge-pending'];
          $lmap = ['disponible'=>'✓ Dispo','rupture'=>'✗ Rupture','en_attente'=>'⏳ Attente'];
          ?>
          <span class="badge <?= $bmap[$p['statut']] ?>"><?= $lmap[$p['statut']] ?></span>
        </div>
        <h3 class="product-title"><?= htmlspecialchars($p['nom']) ?></h3>
      </div>
      <div class="product-card-footer">
        <div>
          <div class="product-price"><?= number_format($p['prix'], 2) ?> DT</div>
          <div class="product-stock"><i class="fas fa-cubes"></i> <?= $p['quantite'] ?> en stock</div>
        </div>
        <div style="display:flex; gap:6px;">
          <a href="index.php?page=edit_produit&id=<?= $p['id_produit'] ?>" class="btn-sm btn-sm-outline" title="Modifier">
            <i class="fas fa-pen"></i>
          </a>
          <a href="index.php?page=delete_produit&id=<?= $p['id_produit'] ?>" class="btn-sm btn-sm-red"
             onclick="return confirm('Supprimer ce produit ?')" title="Supprimer">
            <i class="fas fa-trash"></i>
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
