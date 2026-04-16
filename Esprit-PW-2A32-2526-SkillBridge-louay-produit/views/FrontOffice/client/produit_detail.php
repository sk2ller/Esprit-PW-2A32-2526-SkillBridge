<?php
$pageTitle = htmlspecialchars($produit['nom']) . ' - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
?>

<div class="page-top">
<div class="detail-container">

  <div style="margin-bottom:1.5rem;">
    <a href="index.php?page=all_produits" style="color:var(--accent-purple-light); text-decoration:none; font-size:0.875rem;">
      <i class="fas fa-arrow-left"></i> Retour aux produits
    </a>
  </div>

  <div class="detail-grid">
    <div class="detail-image" style="background: linear-gradient(135deg, <?= ['#1a0533','#0a2240','#002a1f','#1a1000'][crc32($produit['nom']) % 4] ?>, var(--bg-secondary));">
      <i class="<?= htmlspecialchars($produit['icone'] ?? 'fas fa-box') ?>" style="color: rgba(255,255,255,0.3); font-size:6rem; position:relative; z-index:1;"></i>
    </div>

    <div class="detail-info">
      <span class="product-category-tag" style="font-size:0.85rem; padding:5px 14px;">
        <?= htmlspecialchars($produit['nom_categorie']) ?>
      </span>

      <h1><?= htmlspecialchars($produit['nom']) ?></h1>

      <div class="detail-price"><?= number_format($produit['prix'], 2) ?> DT</div>

      <div class="detail-meta">
        <div class="detail-meta-item">
          <i class="fas fa-cubes"></i>
          <span>Stock : <strong><?= $produit['quantite'] ?></strong> unités disponibles</span>
        </div>
        <div class="detail-meta-item">
          <i class="fas fa-tags"></i>
          <span>Catégorie : <strong><?= htmlspecialchars($produit['nom_categorie']) ?></strong></span>
        </div>
        <div class="detail-meta-item">
          <i class="fas fa-circle-check"></i>
          <span>Statut : 
            <?php
            $bmap = ['disponible'=>'badge-disponible','rupture'=>'badge-rupture','en_attente'=>'badge-pending'];
            $lmap = ['disponible'=>'✓ Disponible','rupture'=>'✗ Rupture','en_attente'=>'⏳ En attente'];
            ?>
            <span class="badge <?= $bmap[$produit['statut']] ?>"><?= $lmap[$produit['statut']] ?></span>
          </span>
        </div>
        <div class="detail-meta-item">
          <i class="fas fa-calendar"></i>
          <span>Publié le : <?= date('d/m/Y', strtotime($produit['created_at'])) ?></span>
        </div>
      </div>

      <?php if ($produit['statut'] === 'disponible'): ?>
      <a href="#" class="btn-primary" style="width:100%; justify-content:center;">
        <i class="fas fa-shopping-cart"></i> Acheter maintenant
      </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="detail-description">
    <h3 style="font-family:'Space Grotesk',sans-serif; margin-bottom:1rem;">Description</h3>
    <p><?= nl2br(htmlspecialchars($produit['description'])) ?></p>
  </div>

</div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
