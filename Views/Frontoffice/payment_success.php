<?php
$pageTitle = 'Paiement reussi - Geeks';
include __DIR__ . '/navbar.php';
?>

<div class="page-top">
  <div class="container" style="padding-top:4rem; max-width:760px;">
    <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius-lg); padding:2rem; text-align:center;">
      <div style="width:74px; height:74px; border-radius:50%; background:rgba(16,185,129,0.14); color:var(--accent-green); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; font-size:2rem;">
        <i class="fas fa-check"></i>
      </div>
      <h1 style="font-family:'Space Grotesk',sans-serif; font-size:2rem; margin-bottom:0.75rem;">Paiement reussi</h1>
      <?php if ($service): ?>
      <p style="color:var(--text-secondary); line-height:1.7; margin-bottom:1.5rem;">
        Votre paiement pour <strong><?= htmlspecialchars($service['titre']) ?></strong> a ete confirme par Stripe.
        Vous pouvez maintenant contacter le freelancer pour lancer le service.
      </p>
      <div style="display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap;">
        <a href="index.php?role=client&page=chat&service_id=<?= (int) $service['id_service'] ?>" class="btn-primary">
          <i class="fas fa-comments"></i> Contacter le freelancer
        </a>
        <a href="index.php?page=service_detail&id=<?= (int) $service['id_service'] ?>" class="btn-outline">
          <i class="fas fa-arrow-left"></i> Retour au service
        </a>
      </div>
      <?php else: ?>
      <p style="color:var(--text-secondary); line-height:1.7;">Paiement confirme, mais le service est introuvable.</p>
      <a href="index.php?page=services" class="btn-primary">Voir les services</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
