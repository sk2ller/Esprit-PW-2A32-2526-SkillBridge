<?php
$pageTitle = htmlspecialchars($produit->getNom()) . ' - SkillBridge';
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
    <div class="detail-image" style="background: linear-gradient(135deg, <?= ['#1a0533','#0a2240','#002a1f','#1a1000'][crc32($produit->getNom()) % 4] ?>, var(--bg-secondary));">
      <i class="fas fa-box" style="color: rgba(255,255,255,0.3); font-size:6rem; position:relative; z-index:1;"></i>
    </div>

    <div class="detail-info">
      <span class="product-category-tag" style="font-size:0.85rem; padding:5px 14px;">
        <?= htmlspecialchars($produit->getNomCategorie()) ?>
      </span>

      <h1><?= htmlspecialchars($produit->getNom()) ?></h1>

      <div class="detail-price"><?= number_format($produit->getPrix(), 2) ?> DT</div>

      <div class="detail-meta">
        <div class="detail-meta-item">
          <i class="fas fa-cubes"></i>
          <span>Stock : <strong><?= $produit->getQuantite() ?></strong> unités disponibles</span>
        </div>
        <div class="detail-meta-item">
          <i class="fas fa-tags"></i>
          <span>Catégorie : <strong><?= htmlspecialchars($produit->getNomCategorie()) ?></strong></span>
        </div>
        <div class="detail-meta-item">
          <i class="fas fa-circle-check"></i>
          <span>Statut : 
            <?php
            $bmap = ['disponible'=>'badge-disponible','rupture'=>'badge-rupture','en_attente'=>'badge-pending'];
            $lmap = ['disponible'=>'✓ Disponible','rupture'=>'✗ Rupture','en_attente'=>'⏳ En attente'];
            ?>
            <span class="badge <?= $bmap[$produit->getStatut()] ?>"><?= $lmap[$produit->getStatut()] ?></span>
          </span>
        </div>
        <div class="detail-meta-item">
          <i class="fas fa-calendar"></i>
          <span>Publié le : <?= date('d/m/Y', strtotime($produit->getCreatedAt())) ?></span>
        </div>
      </div>

      <?php if ($produit->getStatut() === 'disponible'): ?>
      <a href="index.php?page=commander&id=<?= $produit->getId() ?>" class="btn-primary" style="width:100%; justify-content:center;">
        <i class="fas fa-shopping-cart"></i> Acheter maintenant
      </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="detail-description">
    <h3 style="font-family:'Space Grotesk',sans-serif; margin-bottom:1rem;">Description</h3>
    <p><?= nl2br(htmlspecialchars($produit->getDescription())) ?></p>
  </div>

</div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
