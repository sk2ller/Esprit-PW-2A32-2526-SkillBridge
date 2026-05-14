<?php
$pageTitle = 'Produits - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
?>

<!-- HERO -->
<?php if (!isset($_GET['search']) && !isset($_GET['categorie'])): ?>
<section style="margin-top: 0; background: linear-gradient(135deg, rgba(30,30,32,.96), rgba(58,45,39,.92)); padding: 80px 2rem 60px; position: relative; overflow: hidden; border-radius: 0 0 30px 30px; box-shadow: 0 20px 50px rgba(30,30,32,.14);">
  <div style="position:absolute; inset:0; background: radial-gradient(circle at top right, rgba(240,138,59,.16), transparent 24%); pointer-events:none;"></div>
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
      <div style="background: linear-gradient(135deg, var(--amber), var(--amber-light)); color: white; border-radius: 22px; padding: 1.5rem; grid-column:1; box-shadow: 0 14px 30px rgba(224,112,32,.24);">
        <div style="font-size:2rem; margin-bottom:0.5rem;">📦</div>
        <div style="font-size: 1.4rem; font-weight: 800;">100+ Produits</div>
        <div style="font-size: 0.85rem; opacity: 0.85;">Numériques & Vérifiés</div>
      </div>
      <div style="background: linear-gradient(135deg, rgba(30,30,32,.88), rgba(45,45,49,.9)); color: white; border: 1px solid rgba(255,255,255,.08); border-radius: 22px; padding: 1.5rem; grid-column:2; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
        <div style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700;">50K+</div>
        <div style="font-size: 0.8rem; color: rgba(255,255,255,.65);">Téléchargements</div>
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
  <a href="index.php?page=all_produits&categorie=<?= $cat->getId() ?>" class="cat-chip <?= (($_GET['categorie'] ?? '') == $cat->getId()) ? 'active' : '' ?>">
    <i class="<?= htmlspecialchars($cat->getIcone()) ?>"></i>
    <?= htmlspecialchars($cat->getNomCategorie()) ?>
    <span class="count"><?= $cat->getNbProduits() ?></span>
  </a>
  <?php endforeach; ?>
</div>

<!-- SORTING BAR -->
<div style="max-width:1400px; margin:0 auto 1.5rem; padding:0 2rem; display:flex; justify-content:flex-end;">
  <div style="display:flex; align-items:center; gap:8px;">
    <span style="color:var(--text-muted); font-size:0.85rem;"><i class="fas fa-sort"></i> Trier par :</span>
    <select id="sortSelect" onchange="applySort()" style="padding:8px 14px; background:var(--paper); border:1px solid var(--border); border-radius:14px; color:var(--text-primary); font-size:0.85rem; cursor:pointer; outline:none; font-family:inherit; box-shadow: 0 4px 12px rgba(0,0,0,.04);">
      <option value="default">Par défaut</option>
      <option value="prix_asc">Prix croissant</option>
      <option value="prix_desc">Prix décroissant</option>
      <option value="nom_asc">Nom A-Z</option>
      <option value="nom_desc">Nom Z-A</option>
    </select>
  </div>
</div>

<script>
function applySort() {
    const val = document.getElementById('sortSelect').value;
    const grid = document.querySelector('.products-grid');
    if (!grid) return;
    const cards = Array.from(grid.children);
    cards.sort((a, b) => {
        const priceA = parseFloat(a.querySelector('.product-price')?.textContent) || 0;
        const priceB = parseFloat(b.querySelector('.product-price')?.textContent) || 0;
        const nameA = a.querySelector('.product-title')?.textContent?.trim() || '';
        const nameB = b.querySelector('.product-title')?.textContent?.trim() || '';
        switch(val) {
            case 'prix_asc': return priceA - priceB;
            case 'prix_desc': return priceB - priceA;
            case 'nom_asc': return nameA.localeCompare(nameB);
            case 'nom_desc': return nameB.localeCompare(nameA);
            default: return 0;
        }
    });
    cards.forEach(card => grid.appendChild(card));
}
</script>

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
      <div class="product-card-image" style="overflow: hidden; position: relative;">
        <?php if ($p->getImage()): ?>
          <img src="<?= htmlspecialchars($p->getImage()) ?>" alt="<?= htmlspecialchars($p->getNom()) ?>" style="width: 100%; height: 100%; object-fit: cover;">
        <?php else: ?>
          <div style="width: 100%; height: 100%; background: linear-gradient(135deg, <?= ['#1a0533','#0a2240','#002a1f','#1a1000'][crc32($p->getNom()) % 4] ?>, var(--bg-secondary)); display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-box" style="color: rgba(255,255,255,0.2); font-size:4rem; position:relative; z-index:1;"></i>
          </div>
        <?php endif; ?>
      </div>
      <div class="product-card-body">
        <span class="product-category-tag"><?= htmlspecialchars($p->getNomCategorie()) ?></span>
        <h3 class="product-title"><?= htmlspecialchars($p->getNom()) ?></h3>
        <p style="color:var(--text-muted); font-size:0.82rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
          <?= htmlspecialchars($p->getDescription()) ?>
        </p>
      </div>
      <div class="product-card-footer">
        <div>
          <div class="product-price"><?= number_format($p->getPrix(), 2) ?> DT</div>
          <div class="product-stock"><i class="fas fa-cubes"></i> <?= $p->getQuantite() ?> en stock</div>
        </div>
        <div style="display:flex; gap:6px;">
          <?php if ($p->getStatut() === 'disponible'): ?>
          <form method="POST" action="index.php?page=panier_add" style="margin:0;">
            <input type="hidden" name="id_produit" value="<?= $p->getId() ?>">
            <input type="hidden" name="quantite" value="1">
            <button type="submit" class="btn-sm btn-sm-purple" title="Ajouter au panier" style="cursor:pointer; display:flex; align-items:center; justify-content:center; padding: 7px 12px;">
              <i class="fas fa-cart-plus"></i>
            </button>
          </form>
          <?php endif; ?>
          <a href="index.php?page=produit_detail&id=<?= $p->getId() ?>" class="btn-sm btn-sm-outline" style="display:flex; align-items:center; justify-content:center; padding: 7px 12px;">
            <i class="fas fa-eye"></i>
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
