<?php
$pageTitle = 'Commande #' . $commande->getId() . ' - SkillBridge';
include __DIR__ . '/../partials/navbar.php';

$statusConfig = [
    'en_attente' => ['badge' => 'badge-pending', 'label' => '⏳ En attente', 'color' => '#fbbf24', 'step' => 1],
    'confirmee'  => ['badge' => 'badge-info', 'label' => '✓ Confirmée', 'color' => '#60a5fa', 'step' => 2],
    'expediee'   => ['badge' => 'badge-info', 'label' => '🚚 Expédiée', 'color' => '#818cf8', 'step' => 3],
    'livree'     => ['badge' => 'badge-disponible', 'label' => '✅ Livrée', 'color' => '#34d399', 'step' => 4],
    'annulee'    => ['badge' => 'badge-rupture', 'label' => '✗ Annulée', 'color' => '#f87171', 'step' => 0],
];
$sc = $statusConfig[$commande->getStatut()] ?? $statusConfig['en_attente'];
$currentStep = $sc['step'];
?>

<div class="page-top">
<div class="detail-container">

  <div style="margin-bottom:1.5rem;">
    <a href="index.php?page=mes_commandes" style="color:var(--accent-purple-light); text-decoration:none; font-size:0.875rem;">
      <i class="fas fa-arrow-left"></i> Mes Commandes
    </a>
  </div>

  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
    <div>
      <h1 style="font-family:'Space Grotesk',sans-serif; font-size:1.6rem; font-weight:800; margin-bottom:4px;">
        Commande #<?= $commande->getId() ?>
      </h1>
      <div style="color:var(--text-muted); font-size:0.85rem;">
        Passée le <?= date('d/m/Y à H:i', strtotime($commande->getCreatedAt())) ?>
      </div>
    </div>
    <span class="badge <?= $sc['badge'] ?>" style="font-size:0.85rem; padding:6px 16px;"><?= $sc['label'] ?></span>
  </div>

  <!-- Progress tracker -->
  <?php if ($commande->getStatut() !== 'annulee'): ?>
  <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:2rem; margin-bottom:2rem;">
    <div style="display:flex; justify-content:space-between; position:relative;">
      <!-- Progress line -->
      <div style="position:absolute; top:20px; left:40px; right:40px; height:3px; background:var(--border); z-index:0;"></div>
      <div style="position:absolute; top:20px; left:40px; height:3px; background:var(--accent-purple-light); z-index:1; width:<?= max(0, ($currentStep - 1)) * 33.33 ?>%; transition:width 0.5s;"></div>
      
      <?php
      $steps = [
          ['icon' => 'fa-clock', 'label' => 'En attente', 'step' => 1],
          ['icon' => 'fa-check', 'label' => 'Confirmée', 'step' => 2],
          ['icon' => 'fa-truck', 'label' => 'Expédiée', 'step' => 3],
          ['icon' => 'fa-check-double', 'label' => 'Livrée', 'step' => 4],
      ];
      foreach ($steps as $s): 
          $isActive = $currentStep >= $s['step'];
          $isCurrent = $currentStep === $s['step'];
      ?>
      <div style="display:flex; flex-direction:column; align-items:center; gap:8px; z-index:2;">
        <div style="width:40px; height:40px; border-radius:50%; background:<?= $isActive ? 'var(--accent-purple-light)' : 'var(--bg-card)' ?>; border:3px solid <?= $isActive ? 'var(--accent-purple-light)' : 'var(--border)' ?>; display:flex; align-items:center; justify-content:center; transition:all 0.3s; <?= $isCurrent ? 'box-shadow:0 0 0 4px rgba(124,58,237,0.3);' : '' ?>">
          <i class="fas <?= $s['icon'] ?>" style="font-size:0.85rem; color:<?= $isActive ? 'white' : 'var(--text-muted)' ?>;"></i>
        </div>
        <span style="font-size:0.75rem; font-weight:<?= $isCurrent ? '700' : '500' ?>; color:<?= $isActive ? 'var(--text-primary)' : 'var(--text-muted)' ?>;">
          <?= $s['label'] ?>
        </span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="detail-grid">
    <!-- Infos commande -->
    <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.5rem;">
      <h3 style="font-family:'Space Grotesk',sans-serif; margin-bottom:1rem; font-size:1rem;">📦 Détails du produit</h3>
      <div class="detail-meta">
        <div class="detail-meta-item">
          <i class="fas fa-box"></i>
          <span>Produit : <strong><?= htmlspecialchars($commande->getNomProduit()) ?></strong></span>
        </div>
        <div class="detail-meta-item">
          <i class="fas fa-cubes"></i>
          <span>Quantité : <strong><?= $commande->getQuantite() ?></strong></span>
        </div>
        <div class="detail-meta-item">
          <i class="fas fa-coins"></i>
          <span>Total : <strong style="color:var(--accent-purple-light);"><?= number_format($commande->getPrixTotal(), 2) ?> DT</strong></span>
        </div>
      </div>
    </div>

    <!-- Infos client -->
    <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.5rem;">
      <h3 style="font-family:'Space Grotesk',sans-serif; margin-bottom:1rem; font-size:1rem;">👤 Informations client</h3>
      <div class="detail-meta">
        <div class="detail-meta-item">
          <i class="fas fa-user"></i>
          <span><?= htmlspecialchars($commande->getNomClient()) ?></span>
        </div>
        <div class="detail-meta-item">
          <i class="fas fa-envelope"></i>
          <span><?= htmlspecialchars($commande->getEmailClient()) ?></span>
        </div>
        <?php if ($commande->getTelephone()): ?>
        <div class="detail-meta-item">
          <i class="fas fa-phone"></i>
          <span><?= htmlspecialchars($commande->getTelephone()) ?></span>
        </div>
        <?php endif; ?>
        <div class="detail-meta-item">
          <i class="fas fa-map-marker-alt"></i>
          <span><?= htmlspecialchars($commande->getAdresse()) ?></span>
        </div>
      </div>
    </div>
  </div>

  <?php if ($commande->getNote()): ?>
  <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.5rem; margin-top:1.5rem;">
    <h3 style="font-family:'Space Grotesk',sans-serif; margin-bottom:0.5rem; font-size:1rem;">📝 Note</h3>
    <p style="color:var(--text-muted);"><?= nl2br(htmlspecialchars($commande->getNote())) ?></p>
  </div>
  <?php endif; ?>

  <?php if ($commande->getStatut() === 'en_attente'): ?>
  <div style="margin-top:1.5rem; display:flex; gap:1rem;">
    <a href="index.php?page=annuler_commande&id=<?= $commande->getId() ?>"
       class="btn-outline" style="color:#f87171; border-color:#f87171;"
       onclick="return confirm('Annuler cette commande ?')">
      <i class="fas fa-times"></i> Annuler la commande
    </a>
  </div>
  <?php endif; ?>

  <!-- SECTION NOTATION (Inspirée du système de feedback du coéquipier) -->
  <?php if ($commande->getStatut() === 'livree'): ?>
    <?php if ($commande->getRating() === null): ?>
    <!-- Formulaire d'évaluation -->
    <div style="background:linear-gradient(135deg, rgba(124,58,237,0.05) 0%, rgba(124,58,237,0.1) 100%); border:1px solid rgba(124,58,237,0.2); border-radius:12px; padding:1.5rem; margin-top:2rem;">
      <h3 style="font-family:'Space Grotesk',sans-serif; margin-bottom:1rem; font-size:1.1rem; color:var(--accent-purple-light);">
        <i class="fas fa-star"></i> Noter ce produit
      </h3>
      <form action="index.php?page=submit_review&id=<?= $commande->getId() ?>" method="POST">
        <div class="form-group" style="margin-bottom:1rem;">
          <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Note (sur 5)</label>
          <div style="display:flex; gap:0.5rem; font-size:1.5rem; color:#fbbf24; flex-direction:row-reverse; justify-content:flex-end;" class="star-rating">
            <input type="radio" name="rating" value="5" id="star5" required style="display:none;"><label for="star5" style="cursor:pointer;"><i class="far fa-star"></i></label>
            <input type="radio" name="rating" value="4" id="star4" style="display:none;"><label for="star4" style="cursor:pointer;"><i class="far fa-star"></i></label>
            <input type="radio" name="rating" value="3" id="star3" style="display:none;"><label for="star3" style="cursor:pointer;"><i class="far fa-star"></i></label>
            <input type="radio" name="rating" value="2" id="star2" style="display:none;"><label for="star2" style="cursor:pointer;"><i class="far fa-star"></i></label>
            <input type="radio" name="rating" value="1" id="star1" style="display:none;"><label for="star1" style="cursor:pointer;"><i class="far fa-star"></i></label>
          </div>
          <style>
            .star-rating label:hover, .star-rating label:hover ~ label, .star-rating input:checked ~ label { color: #fbbf24; }
            .star-rating label { color: #d1d5db; transition: color 0.2s; }
            .star-rating input:checked ~ label i::before { content: "\f005"; font-weight: 900; }
            .star-rating label:hover i::before, .star-rating label:hover ~ label i::before { content: "\f005"; font-weight: 900; }
          </style>
        </div>
        <div class="form-group">
          <label class="form-label">Votre avis (optionnel)</label>
          <textarea name="review" class="form-control" rows="3" placeholder="Qu'avez-vous pensé de ce produit ?"></textarea>
        </div>
        <button type="submit" class="btn-primary" style="margin-top:1rem;">Soumettre mon avis</button>
      </form>
    </div>
    <?php else: ?>
    <!-- Affichage de l'évaluation -->
    <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.5rem; margin-top:2rem;">
      <h3 style="font-family:'Space Grotesk',sans-serif; margin-bottom:1rem; font-size:1.1rem;">
        <i class="fas fa-comment-dots" style="color:var(--accent-purple-light);"></i> Votre évaluation
      </h3>
      <div style="display:flex; align-items:center; gap:0.5rem; font-size:1.2rem; color:#fbbf24; margin-bottom:0.5rem;">
        <?php for($i=1; $i<=5; $i++): ?>
          <i class="fa<?= $i <= $commande->getRating() ? 's' : 'r' ?> fa-star"></i>
        <?php endfor; ?>
        <span style="color:var(--text-muted); font-size:0.9rem; margin-left:0.5rem;">(<?= $commande->getRating() ?>/5)</span>
      </div>
      <?php if ($commande->getReview()): ?>
        <p style="color:var(--text-secondary); font-style:italic; border-left:3px solid var(--border); padding-left:1rem; margin-top:1rem;">
          "<?= nl2br(htmlspecialchars($commande->getReview())) ?>"
        </p>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  <?php endif; ?>

</div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
