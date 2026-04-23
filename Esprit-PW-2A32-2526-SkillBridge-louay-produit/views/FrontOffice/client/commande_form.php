<?php
$pageTitle = 'Passer une Commande - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
?>

<div class="page-top">
<div class="container" style="padding-top:2rem; max-width:700px;">

  <div style="margin-bottom:1.5rem;">
    <a href="index.php?page=produit_detail&id=<?= $produit->getId() ?>" style="color:var(--accent-purple-light); text-decoration:none; font-size:0.875rem;">
      <i class="fas fa-arrow-left"></i> Retour au produit
    </a>
  </div>

  <h1 style="font-family:'Space Grotesk',sans-serif; font-size:1.8rem; font-weight:800; margin-bottom:0.5rem;">
    🛒 Passer une commande
  </h1>
  <p style="color:var(--text-muted); margin-bottom:2rem;">
    Remplissez vos informations pour commander <strong><?= htmlspecialchars($produit->getNom()) ?></strong>
  </p>

  <!-- Résumé produit -->
  <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.2rem; margin-bottom:2rem; display:flex; gap:1rem; align-items:center;">
    <div style="width:60px; height:60px; border-radius:10px; background:linear-gradient(135deg, #1a0533, var(--bg-secondary)); display:flex; align-items:center; justify-content:center;">
      <i class="fas fa-box" style="color:rgba(255,255,255,0.3); font-size:1.5rem;"></i>
    </div>
    <div style="flex:1;">
      <div style="font-weight:700; font-size:0.95rem;"><?= htmlspecialchars($produit->getNom()) ?></div>
      <div style="color:var(--text-muted); font-size:0.82rem;"><?= htmlspecialchars($produit->getNomCategorie()) ?></div>
    </div>
    <div style="text-align:right;">
      <div style="font-weight:800; color:var(--accent-purple-light); font-size:1.1rem;"><?= number_format($produit->getPrix(), 2) ?> DT</div>
      <div style="color:var(--text-muted); font-size:0.78rem;"><?= $produit->getQuantite() ?> en stock</div>
    </div>
  </div>

  <div class="form-wrapper" style="max-width:100%;">
    <?php if (!empty($error)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="commandeForm">
      <input type="hidden" name="id_produit" value="<?= $produit->getId() ?>">
      <input type="hidden" name="prix_unitaire" value="<?= $produit->getPrix() ?>">

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

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <input type="text" name="telephone" class="form-control"
                 value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>"
                 placeholder="Ex: 20123456">
        </div>
        <div class="form-group">
          <label class="form-label">Quantité <span style="color:#ef4444">*</span></label>
          <input type="number" name="quantite" class="form-control" min="1" max="<?= $produit->getQuantite() ?>"
                 value="<?= htmlspecialchars($_POST['quantite'] ?? '1') ?>" id="cmdQuantite" required>
        </div>
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

      <!-- Prix total -->
      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:10px; padding:1rem 1.5rem; margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center;">
        <span style="font-weight:600; color:var(--text-secondary);">Total à payer :</span>
        <span id="cmdTotal" style="font-size:1.4rem; font-weight:800; color:var(--accent-purple-light);"><?= number_format($produit->getPrix(), 2) ?> DT</span>
      </div>

      <div style="display:flex; gap:1rem; justify-content:flex-end;">
        <a href="index.php?page=produit_detail&id=<?= $produit->getId() ?>" class="btn-outline">Annuler</a>
        <button type="submit" class="btn-primary">
          <i class="fas fa-shopping-cart"></i> Confirmer la commande
        </button>
      </div>
    </form>
  </div>

</div>
</div>

<script>
// Calcul dynamique du prix total
const qtyInput = document.getElementById('cmdQuantite');
const totalEl = document.getElementById('cmdTotal');
const prixUnit = <?= $produit->getPrix() ?>;
qtyInput.addEventListener('input', function() {
    const qty = parseInt(this.value) || 1;
    totalEl.textContent = (prixUnit * qty).toFixed(2) + ' DT';
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
