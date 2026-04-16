<?php
$pageTitle = 'Services - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
?>

<!-- HERO -->
<?php if (!isset($_GET['search']) && !isset($_GET['categorie'])): ?>
<section style="margin-top: var(--nav-height); background: linear-gradient(135deg, #0d1117 0%, #130a2e 50%, #0d1117 100%); padding: 80px 2rem 60px; position: relative; overflow: hidden;">
  <div style="position:absolute; top:-100px; right:-100px; width:500px; height:500px; background: radial-gradient(circle, rgba(124,58,237,0.12) 0%, transparent 70%); pointer-events:none;"></div>
  <div style="max-width:1400px; margin:0 auto; display:grid; grid-template-columns:1fr 1fr; gap:4rem; align-items:center;">
    <div>
      <div class="hero-eyebrow">🚀 Empower Your Learning Journey Today</div>
      <h1 class="hero-title">The <span class="highlight">#1 Service</span><br>Platform</h1>
      <p class="hero-desc">Trouvez les meilleurs freelancers pour vos projets. Certifiés, professionnels et disponibles.</p>
      <ul class="hero-features">
        <li><span class="check"><i class="fas fa-check"></i></span> Freelancers Expert Certifiés</li>
        <li><span class="check"><i class="fas fa-check"></i></span> Services Vérifiés & Approuvés</li>
        <li><span class="check"><i class="fas fa-check"></i></span> Paiement Sécurisé Garanti</li>
      </ul>
      <div class="hero-cta">
        <?php if (!isset($_SESSION['user'])): ?>
        <a href="index.php?page=register" class="btn-primary"><i class="fas fa-user-plus"></i> Join For Free</a>
        <?php endif; ?>
        <a href="#services-section" class="btn-outline"><i class="fas fa-compass"></i> Explore Services</a>
      </div>
    </div>
    <div class="hero-visuals">
      <div class="hero-card hero-card-green" style="grid-column:1">
        <div style="font-size:2rem; margin-bottom:0.5rem;">📚</div>
        <div class="card-stat">50+ Services</div>
        <div class="card-label">Certified & Verified</div>
      </div>
      <div class="hero-card hero-card-students" style="grid-column:2">
        <div class="big-num">70K+</div>
        <div class="sub">Clients satisfaits</div>
      </div>
    </div>
  </div>
</section>
<?php else: ?>
<div style="margin-top: var(--nav-height); padding-top: 2rem;"></div>
<?php endif; ?>

<!-- SEARCH -->
<div style="max-width:800px; margin: 3rem auto 2rem; padding: 0 2rem;" id="services-section">
  <form method="GET" action="index.php">
    <input type="hidden" name="page" value="all_services">
    <?php if (!empty($_GET['categorie'])): ?>
    <input type="hidden" name="categorie" value="<?= (int)$_GET['categorie'] ?>">
    <?php endif; ?>
    <div class="search-bar">
      <input type="text" name="search" placeholder="🔍  Rechercher un service..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
    </div>
  </form>
</div>

<!-- CATEGORIES BAR -->
<div class="categories-bar">
  <a href="index.php?page=all_services" class="cat-chip <?= empty($_GET['categorie']) ? 'active' : '' ?>">
    <i class="fas fa-border-all"></i> Tous <span class="count"><?= count($services) ?></span>
  </a>
  <?php foreach ($categories as $cat): ?>
  <a href="index.php?page=all_services&categorie=<?= $cat['id_categorie'] ?>" class="cat-chip <?= (($_GET['categorie'] ?? '') == $cat['id_categorie']) ? 'active' : '' ?>">
    <i class="<?= htmlspecialchars($cat['icone']) ?>"></i>
    <?= htmlspecialchars($cat['nom_categorie']) ?>
    <span class="count"><?= $cat['nb_services'] ?></span>
  </a>
  <?php endforeach; ?>
</div>

<!-- SERVICES GRID -->
<div class="page-container">
  <?php if (!empty($_GET['search']) || !empty($_GET['categorie'])): ?>
  <div class="section-header">
    <h2 class="section-title">
      <?= count($services) ?> <span>résultat<?= count($services) > 1 ? 's' : '' ?></span> trouvé<?= count($services) > 1 ? 's' : '' ?>
    </h2>
    <a href="index.php?page=all_services" style="color:var(--text-muted); font-size:0.875rem; text-decoration:none;">
      <i class="fas fa-times"></i> Effacer filtres
    </a>
  </div>
  <?php else: ?>
  <div class="section-header">
    <h2 class="section-title">Tous les <span>Services</span></h2>
    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'freelancer'): ?>
    <a href="index.php?page=create_service" class="btn-primary" style="font-size:0.875rem; padding:10px 20px;">
      <i class="fas fa-plus"></i> Ajouter un Service
    </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if (empty($services)): ?>
  <div class="empty-state">
    <div class="icon">🔍</div>
    <h3>Aucun service trouvé</h3>
    <p>Essayez une autre recherche ou catégorie</p>
    <a href="index.php?page=all_services" class="btn-outline" style="display:inline-block; margin-top:1rem;">Voir tous les services</a>
  </div>
  <?php else: ?>
  <div class="services-grid">
    <?php foreach ($services as $s): ?>
    <div class="service-card">
      <div class="service-card-image" style="background: linear-gradient(135deg, <?= ['#1a0533','#0a2240','#002a1f','#1a1000'][crc32($s['titre']) % 4] ?>, var(--bg-secondary));">
        <i class="<?= htmlspecialchars($s['icone'] ?? 'fas fa-star') ?>" style="color: rgba(255,255,255,0.2); font-size:4rem; position:relative; z-index:1;"></i>
      </div>
      <div class="service-card-body">
        <span class="service-category-tag"><?= htmlspecialchars($s['nom_categorie']) ?></span>
        <h3 class="service-title"><?= htmlspecialchars($s['titre']) ?></h3>
        <p style="color:var(--text-muted); font-size:0.82rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
          <?= htmlspecialchars($s['description']) ?>
        </p>
      </div>
      <div class="service-card-footer">
        <div>
          <div class="service-price"><?= number_format($s['prix'], 2) ?> DT</div>
          <div class="service-delay"><i class="fas fa-clock"></i> <?= $s['delai_livraison'] ?> jours</div>
        </div>
        <a href="index.php?page=service_detail&id=<?= $s['id_service'] ?>" class="btn-sm btn-sm-purple">
          Voir <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
