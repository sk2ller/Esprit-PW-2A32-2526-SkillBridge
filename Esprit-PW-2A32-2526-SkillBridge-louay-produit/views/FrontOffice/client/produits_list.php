<?php
$pageTitle = 'Produits - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
?>

<!-- HERO -->
<?php if (!isset($_GET['search']) && !isset($_GET['categorie'])): ?>
<section style="margin-top: var(--nav-height); background: linear-gradient(135deg, #0d1117 0%, #130a2e 50%, #0d1117 100%); padding: 80px 2rem 60px; position: relative; overflow: hidden;">
  <div style="position:absolute; top:-100px; right:-100px; width:500px; height:500px; background: radial-gradient(circle, rgba(124,58,237,0.12) 0%, transparent 70%); pointer-events:none;"></div>
  <div style="max-width:1400px; margin:0 auto; display:grid; grid-template-columns:1fr 1fr; gap:4rem; align-items:center;">
    <div>
      <div class="hero-eyebrow">🛍️ Marketplace de Produits Numériques</div>
      <h1 class="hero-title">La <span class="highlight">Boutique</span><br>SkillBridge</h1>
      <p class="hero-desc">Découvrez des produits numériques de qualité : templates, e-books, plugins, formations et bien plus.</p>
      <ul class="hero-features">
        <li><span class="check"><i class="fas fa-check"></i></span> Produits Vérifiés & Approuvés</li>
        <li><span class="check"><i class="fas fa-check"></i></span> Téléchargement Instantané</li>
        <li><span class="check"><i class="fas fa-check"></i></span> Paiement Sécurisé Garanti</li>
      </ul>
      <div class="hero-cta">
        <a href="#produits-section" class="btn-outline"><i class="fas fa-compass"></i> Explorer les Produits</a>
      </div>
    </div>
    <div class="hero-visuals">
      <div class="hero-card hero-card-green" style="grid-column:1">
        <div style="font-size:2rem; margin-bottom:0.5rem;">📦</div>
        <div class="card-stat">100+ Produits</div>
        <div class="card-label">Numériques & Vérifiés</div>
      </div>
      <div class="hero-card hero-card-students" style="grid-column:2">
        <div class="big-num">50K+</div>
        <div class="sub">Téléchargements</div>
      </div>
    </div>
  </div>
</section>
<?php else: ?>
<div style="margin-top: var(--nav-height); padding-top: 2rem;"></div>
<?php endif; ?>

<!-- SEARCH -->
<div style="max-width:800px; margin: 3rem auto 2rem; padding: 0 2rem;" id="produits-section">
  <form method="GET" action="index.php">
    <input type="hidden" name="page" value="all_produits">
    <?php if (!empty($_GET['categorie'])): ?>
    <input type="hidden" name="categorie" value="<?= (int)$_GET['categorie'] ?>">
    <?php endif; ?>
    <div class="search-bar">
      <input type="text" name="search" placeholder="🔍  Rechercher un produit..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
    </div>
  </form>
</div>

<!-- CATEGORIES BAR -->
<div class="categories-bar">
  <a href="index.php?page=all_produits" class="cat-chip <?= empty($_GET['categorie']) ? 'active' : '' ?>">
    <i class="fas fa-border-all"></i> Tous <span class="count"><?= count($produits) ?></span>
  </a>
  <?php foreach ($categories as $cat): ?>
  <a href="index.php?page=all_produits&categorie=<?= $cat['id_categorie'] ?>" class="cat-chip <?= (($_GET['categorie'] ?? '') == $cat['id_categorie']) ? 'active' : '' ?>">
    <i class="<?= htmlspecialchars($cat['icone']) ?>"></i>
    <?= htmlspecialchars($cat['nom_categorie']) ?>
    <span class="count"><?= $cat['nb_produits'] ?></span>
  </a>
  <?php endforeach; ?>
</div>

<!-- PRODUCTS GRID -->
<div class="page-container">
  <?php if (!empty($_GET['search']) || !empty($_GET['categorie'])): ?>
  <div class="section-header">
    <h2 class="section-title">
      <?= count($produits) ?> <span>résultat<?= count($produits) > 1 ? 's' : '' ?></span> trouvé<?= count($produits) > 1 ? 's' : '' ?>
    </h2>
    <a href="index.php?page=all_produits" style="color:var(--text-muted); font-size:0.875rem; text-decoration:none;">
      <i class="fas fa-times"></i> Effacer filtres
    </a>
  </div>
  <?php else: ?>
  <div class="section-header">
    <h2 class="section-title">Tous les <span>Produits</span></h2>
  </div>
  <?php endif; ?>

  <?php if (empty($produits)): ?>
  <div class="empty-state">
    <div class="icon">🔍</div>
    <h3>Aucun produit trouvé</h3>
    <p>Essayez une autre recherche ou catégorie</p>
    <a href="index.php?page=all_produits" class="btn-outline" style="display:inline-block; margin-top:1rem;">Voir tous les produits</a>
  </div>
  <?php else: ?>
  <div class="products-grid">
    <?php foreach ($produits as $p): ?>
    <div class="product-card">
      <div class="product-card-image" style="background: linear-gradient(135deg, <?= ['#1a0533','#0a2240','#002a1f','#1a1000'][crc32($p['nom']) % 4] ?>, var(--bg-secondary));">
        <i class="<?= htmlspecialchars($p['icone'] ?? 'fas fa-box') ?>" style="color: rgba(255,255,255,0.2); font-size:4rem; position:relative; z-index:1;"></i>
      </div>
      <div class="product-card-body">
        <span class="product-category-tag"><?= htmlspecialchars($p['nom_categorie']) ?></span>
        <h3 class="product-title"><?= htmlspecialchars($p['nom']) ?></h3>
        <p style="color:var(--text-muted); font-size:0.82rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
          <?= htmlspecialchars($p['description']) ?>
        </p>
      </div>
      <div class="product-card-footer">
        <div>
          <div class="product-price"><?= number_format($p['prix'], 2) ?> DT</div>
          <div class="product-stock"><i class="fas fa-cubes"></i> <?= $p['quantite'] ?> en stock</div>
        </div>
        <a href="index.php?page=produit_detail&id=<?= $p['id_produit'] ?>" class="btn-sm btn-sm-purple">
          Voir <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
