<?php
$pageTitle = htmlspecialchars($service['titre']) . ' - Geeks';
include __DIR__ . '/navbar.php';
?>

<div class="page-top">
<div class="container" style="padding-top:2rem;">
  <div style="color:var(--text-muted); font-size:0.85rem; margin-bottom:2rem; display:flex; align-items:center; gap:8px;">
    <a href="index.php?page=services" style="color:var(--accent-purple-light); text-decoration:none;">Services</a>
    <span>›</span>
    <a href="index.php?page=services&categorie=<?= $service['id_categorie'] ?>" style="color:var(--accent-purple-light); text-decoration:none;"><?= htmlspecialchars($service['nom_categorie']) ?></a>
    <span>›</span>
    <span><?= htmlspecialchars(substr($service['titre'], 0, 40)) ?>...</span>
  </div>

  <div style="display:grid; grid-template-columns:1fr 340px; gap:2rem; align-items:start;">
    <div>
      <div style="height:320px; border-radius:var(--radius-lg); overflow:hidden; background:linear-gradient(135deg, #5c6f86, #d9d9d9); display:flex; align-items:center; justify-content:center; margin-bottom:2rem; border:1px solid var(--border);">
        <?php if (!empty($service['thumbnail'])): ?>
          <img src="views/assets/uploads/<?= htmlspecialchars($service['thumbnail']) ?>"
               alt="<?= htmlspecialchars($service['titre']) ?>"
               style="width:100%; height:100%; object-fit:cover;">
        <?php else: ?>
          <i class="fas fa-briefcase" style="font-size:6rem; color:rgba(255,255,255,0.25);"></i>
        <?php endif; ?>
      </div>

      <span class="service-category-tag"><?= htmlspecialchars($service['nom_categorie']) ?></span>
      <h1 style="font-family:'Space Grotesk',sans-serif; font-size:1.8rem; font-weight:800; margin:1rem 0;"><?= htmlspecialchars($service['titre']) ?></h1>

      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius); padding:1.5rem; margin-bottom:2rem;">
        <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:1rem;">À propos de ce service</h2>
        <p style="color:var(--text-secondary); line-height:1.8;"><?= nl2br(htmlspecialchars($service['description'])) ?></p>
      </div>

      <?php if (!empty($service['cv']) || !empty($service['portfolio'])): ?>
      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius); padding:1.5rem; margin-bottom:2rem;">
        <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:1rem;">Documents du freelancer</h2>

        <div style="display:flex; flex-direction:column; gap:1rem;">

          <?php if (!empty($service['cv'])): ?>
          <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:1rem; border:1px solid var(--border); border-radius:12px;">
            <div style="display:flex; align-items:center; gap:12px;">
              <div style="width:42px; height:42px; border-radius:10px; background:rgba(34,197,94,0.12); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-file-lines" style="color:var(--accent-green);"></i>
              </div>
              <div>
                <div style="font-weight:700;">CV du freelancer</div>
                <div style="font-size:0.85rem; color:var(--text-muted);"><?= htmlspecialchars($service['cv']) ?></div>
              </div>
            </div>

            <a href="views/assets/uploads/<?= urlencode($service['cv']) ?>" download class="btn-primary" style="white-space:nowrap;">
              <i class="fas fa-download"></i> Télécharger
            </a>
          </div>
          <?php endif; ?>

          <?php if (!empty($service['portfolio'])): ?>
          <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:1rem; border:1px solid var(--border); border-radius:12px;">
            <div style="display:flex; align-items:center; gap:12px;">
              <div style="width:42px; height:42px; border-radius:10px; background:rgba(249,115,22,0.12); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-folder-open" style="color:var(--accent-orange);"></i>
              </div>
              <div>
                <div style="font-weight:700;">Portfolio du freelancer</div>
                <div style="font-size:0.85rem; color:var(--text-muted);"><?= htmlspecialchars($service['portfolio']) ?></div>
              </div>
            </div>

            <a href="views/assets/uploads/<?= urlencode($service['portfolio']) ?>" download class="btn-primary" style="white-space:nowrap;">
              <i class="fas fa-download"></i> Télécharger
            </a>
          </div>
          <?php endif; ?>

        </div>
      </div>
      <?php endif; ?>
    </div>

    <div style="position:sticky; top:calc(var(--nav-height) + 1rem);">
      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius-lg); padding:1.5rem;">
        <div style="font-size:2rem; font-weight:800; color:var(--accent-green); margin-bottom:0.5rem;">
          <?= number_format($service['prix'], 2) ?> DT
        </div>
        <div style="color:var(--text-muted); font-size:0.875rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:6px;">
          <i class="fas fa-clock"></i> Livraison en <?= $service['delai_livraison'] ?> jours
        </div>

        <div style="border-top:1px solid var(--border); padding-top:1.2rem; margin-bottom:1.5rem;">
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px; color:var(--text-secondary); font-size:0.875rem;">
            <i class="fas fa-check-circle" style="color:var(--accent-green)"></i> Service approuvé
          </div>
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px; color:var(--text-secondary); font-size:0.875rem;">
            <i class="fas fa-shield-alt" style="color:var(--accent-purple-light)"></i> Paiement sécurisé
          </div>
          <div style="display:flex; align-items:center; gap:8px; color:var(--text-secondary); font-size:0.875rem;">
            <i class="fas fa-undo" style="color:var(--accent-orange)"></i> Satisfait ou remboursé
          </div>
        </div>

        <a href="index.php?page=services" class="btn-primary" style="width:100%; justify-content:center; margin-bottom:0.75rem;">
          <i class="fas fa-arrow-left"></i> Retour aux services
        </a>
        <a href="index.php?role=admin&page=admin_dashboard" class="btn-outline" style="width:100%; justify-content:center;">
          <i class="fas fa-gauge-high"></i> Admin Panel
        </a>
      </div>
    </div>
  </div>
</div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
