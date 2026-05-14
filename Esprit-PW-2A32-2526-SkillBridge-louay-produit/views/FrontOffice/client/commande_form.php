<?php
$pageTitle = 'Checkout - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
$panier = $_SESSION['panier'] ?? [];
$totalCart = 0;
foreach ($panier as $item) {
    $totalCart += $item['prix'] * $item['quantite'];
}
?>

<div class="page-top">
<div class="container" style="padding-top:2rem; max-width:900px;">

  <div style="margin-bottom:1.5rem;">
    <a href="index.php?page=panier" style="color:var(--accent-purple-light); text-decoration:none; font-size:0.875rem;">
      <i class="fas fa-arrow-left"></i> Retour au panier
    </a>
  </div>

  <h1 style="font-family:'Space Grotesk',sans-serif; font-size:1.8rem; font-weight:800; margin-bottom:0.5rem;">
    💳 Finaliser la commande
  </h1>
  <p style="color:var(--text-muted); margin-bottom:2rem;">
    Veuillez renseigner vos informations pour finaliser l'achat de vos produits.
  </p>

  <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem; align-items: start;">
    
    <!-- Formulaire d'informations -->
    <div class="form-wrapper" style="max-width:100%; padding: 2rem;">
      <h3 style="font-family:'Space Grotesk',sans-serif; margin-bottom:1.5rem; font-size:1.2rem; padding-bottom:1rem; border-bottom:1px solid var(--border);">Informations de facturation</h3>
      
      <?php if (!empty($error)): ?>
      <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" id="checkoutForm">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label class="form-label">Nom complet <span style="color:#ef4444">*</span></label>
            <input type="text" name="nom_client" class="form-control"
                   value="<?= htmlspecialchars($_POST['nom_client'] ?? '') ?>"
                   placeholder="Ex: Ahmed Ben Ali" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email <span style="color:#ef4444">*</span></label>
            <input type="email" name="email_client" class="form-control"
                   value="<?= htmlspecialchars($_POST['email_client'] ?? '') ?>"
                   placeholder="Ex: ahmed@email.com" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <input type="text" name="telephone" class="form-control"
                 value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>"
                 placeholder="Ex: 20123456">
        </div>

        <div class="form-group">
          <label class="form-label">Adresse de livraison <span style="color:#ef4444">*</span></label>
          <textarea name="adresse" class="form-control" rows="3"
                    placeholder="Votre adresse complète..." required><?= htmlspecialchars($_POST['adresse'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Note (optionnel)</label>
          <textarea name="note" class="form-control" rows="2"
                    placeholder="Instructions spéciales..."><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
        </div>

        <h3 style="font-family:'Space Grotesk',sans-serif; margin:2rem 0 1rem; font-size:1.2rem; padding-bottom:1rem; border-bottom:1px solid var(--border);">Méthode de paiement</h3>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:2rem;">
          <label style="border:1px solid var(--accent-purple); background:rgba(124,58,237,0.1); padding:1rem; border-radius:10px; cursor:pointer; display:flex; align-items:center; gap:10px;">
            <input type="radio" name="payment_method" value="card" checked style="accent-color:var(--accent-purple);">
            <div>
              <div style="font-weight:600;">Carte Bancaire</div>
              <div style="font-size:0.8rem; color:var(--text-muted);">Paiement sécurisé en ligne</div>
            </div>
          </label>
          <label style="border:1px solid var(--border); padding:1rem; border-radius:10px; cursor:pointer; display:flex; align-items:center; gap:10px; opacity:0.7;">
            <input type="radio" name="payment_method" value="cash" disabled>
            <div>
              <div style="font-weight:600;">À la livraison</div>
              <div style="font-size:0.8rem; color:var(--text-muted);">Indisponible pour produits numériques</div>
            </div>
          </label>
        </div>

        <div style="display:flex; justify-content:flex-end;">
          <button type="submit" class="btn-primary" style="padding:16px 32px;">
            <i class="fas fa-lock"></i> Payer et commander
          </button>
        </div>
      </form>
    </div>

    <!-- Résumé de la commande -->
    <div class="checkout-sidebar" style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.5rem; position:sticky; top:100px;">
      <h3 style="font-family:'Space Grotesk',sans-serif; margin-bottom:1.5rem; font-size:1.2rem;">Résumé (<?= count($panier) ?> articles)</h3>
      
      <div style="display:flex; flex-direction:column; gap:1rem; margin-bottom:1.5rem; max-height:300px; overflow-y:auto; padding-right:10px;">
        <?php foreach ($panier as $id_p => $item): ?>
        <div style="display:flex; gap:1rem; align-items:center;">
          <div style="width:50px; height:50px; border-radius:8px; background:var(--bg-card); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
            <?php if (!empty($item['image'])): ?>
              <img src="<?= htmlspecialchars($item['image']) ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
              <i class="fas fa-box" style="color:rgba(255,255,255,0.2); font-size:1.5rem;"></i>
            <?php endif; ?>
          </div>
          <div style="flex:1;">
            <div style="font-weight:600; font-size:0.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:150px;"><?= htmlspecialchars($item['nom']) ?></div>
            <div style="color:var(--text-muted); font-size:0.8rem;">Qté: <?= $item['quantite'] ?></div>
          </div>
          <div style="font-weight:700; font-size:0.95rem; color:var(--accent-green);">
            <?= number_format($item['prix'] * $item['quantite'], 2) ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      
      <div style="border-top:1px solid var(--border); padding-top:1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
          <span style="font-weight:600;">Total à payer</span>
          <span style="font-size:1.6rem; font-weight:800; color:var(--accent-purple-light);"><?= number_format($totalCart, 2) ?> DT</span>
        </div>
      </div>
    </div>

  </div>

</div>
</div>

<style>
@media (max-width: 900px) {
  .page-top > .container > div:nth-child(3) { grid-template-columns: 1fr !important; }
  .checkout-sidebar { order: -1; position: relative; top: 0; margin-bottom: 2rem; }
}
</style>

<?php include __DIR__ . '/../partials/footer.php'; ?>
