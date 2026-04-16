<?php
$pageTitle = 'Accueil - SkillBridge Produits';
include __DIR__ . '/../partials/navbar.php';
?>

<!-- HERO -->
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
        <a href="index.php?page=all_produits" class="btn-primary"><i class="fas fa-shopping-bag"></i> Explorer les Produits</a>
        <a href="index.php?role=vendeur&page=mes_produits" class="btn-outline"><i class="fas fa-store"></i> Vendre un Produit</a>
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

<?php include __DIR__ . '/../partials/footer.php'; ?>
