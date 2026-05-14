<?php
$pageTitle = 'Mon Panier - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
$panier = $_SESSION['panier'] ?? [];
$totalCart = 0;
foreach ($panier as $item) {
    $totalCart += $item['prix'] * $item['quantite'];
}
?>

<div class="page-top">
<div class="container cart-container" style="max-width: 1200px; padding-top: 2rem;">

  <div style="margin-bottom:1.5rem;">
    <a href="index.php?page=all_produits" style="color:var(--accent-purple-light); text-decoration:none; font-size:0.875rem;">
      <i class="fas fa-arrow-left"></i> Continuer mes achats
    </a>
  </div>

  <h1 style="font-family:'Space Grotesk',sans-serif; font-size:2rem; font-weight:800; margin-bottom:0.5rem;">
    🛒 Mon Panier
  </h1>
  
  <?php if (empty($panier)): ?>
  <div class="empty-state" style="margin-top: 2rem;">
    <div class="icon">🛒</div>
    <h3>Votre panier est vide</h3>
    <p>Découvrez nos produits numériques et commencez vos achats.</p>
    <a href="index.php?page=all_produits" class="btn-primary" style="display:inline-flex; margin-top:1rem;">
      <i class="fas fa-shopping-bag"></i> Explorer les produits
    </a>
  </div>
  <?php else: ?>

  <div class="cart-grid" style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem; margin-top: 2rem; align-items: start;">
    
    <!-- Liste des articles -->
    <div class="cart-items">
      <?php foreach ($panier as $id_p => $item): ?>
      <div class="cart-item" style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.2rem; margin-bottom:1rem; display:flex; gap:1.5rem; align-items:center;">
        
        <!-- Image -->
        <div style="width:100px; height:100px; border-radius:10px; background:var(--bg-card); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
          <?php if (!empty($item['image'])): ?>
            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>" style="width:100%; height:100%; object-fit:cover;">
          <?php else: ?>
            <i class="fas fa-box" style="color:rgba(255,255,255,0.2); font-size:2.5rem;"></i>
          <?php endif; ?>
        </div>

        <!-- Infos -->
        <div style="flex:1;">
          <div style="color:var(--accent-purple-light); font-size:0.75rem; font-weight:600; margin-bottom:4px;"><?= htmlspecialchars($item['nom_categorie']) ?></div>
          <h3 style="font-size:1.1rem; font-weight:700; margin-bottom:8px;"><a href="index.php?page=produit_detail&id=<?= $id_p ?>" style="color:inherit; text-decoration:none;"><?= htmlspecialchars($item['nom']) ?></a></h3>
          <div style="color:var(--text-muted); font-size:0.9rem;"><?= number_format($item['prix'], 2) ?> DT / unité</div>
        </div>

        <!-- Quantité -->
        <div class="qty-selector" style="display:flex; align-items:center; background:var(--bg-card); border:1px solid var(--border); border-radius:8px; overflow:hidden;">
          <form method="POST" action="index.php?page=panier_update" style="margin:0;">
            <input type="hidden" name="id_produit" value="<?= $id_p ?>">
            <input type="hidden" name="action" value="decrease">
            <button type="submit" style="background:none; border:none; color:var(--text-primary); padding:8px 12px; cursor:pointer; transition:background 0.2s;"><i class="fas fa-minus"></i></button>
          </form>
          <div style="padding:0 12px; font-weight:600; min-width:30px; text-align:center;"><?= $item['quantite'] ?></div>
          <form method="POST" action="index.php?page=panier_update" style="margin:0;">
            <input type="hidden" name="id_produit" value="<?= $id_p ?>">
            <input type="hidden" name="action" value="increase">
            <button type="submit" style="background:none; border:none; color:var(--text-primary); padding:8px 12px; cursor:pointer; transition:background 0.2s;"><i class="fas fa-plus"></i></button>
          </form>
        </div>

        <!-- Total Ligne -->
        <div style="text-align:right; min-width:100px;">
          <div style="font-size:1.2rem; font-weight:800; color:var(--accent-green);"><?= number_format($item['prix'] * $item['quantite'], 2) ?> DT</div>
        </div>

        <!-- Supprimer -->
        <div style="margin-left:auto;">
          <a href="index.php?page=panier_remove&id=<?= $id_p ?>" class="btn-sm btn-sm-red" title="Supprimer" onclick="return confirm('Retirer cet article du panier ?');">
            <i class="fas fa-trash"></i>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
      
      <div style="margin-top: 1.5rem;">
        <a href="index.php?page=panier_clear" class="btn-outline" style="color:#f87171; border-color:rgba(248,113,113,0.3);" onclick="return confirm('Vider tout le panier ?');">
          <i class="fas fa-times"></i> Vider le panier
        </a>
      </div>
    </div>

    <!-- Sidebar Summary -->
    <div class="cart-summary" style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.5rem; position:sticky; top:100px;">
      <h3 style="font-family:'Space Grotesk',sans-serif; margin-bottom:1.5rem; font-size:1.2rem; padding-bottom:1rem; border-bottom:1px solid var(--border);">Résumé de la commande</h3>
      
      <div style="display:flex; justify-content:space-between; margin-bottom:1rem; color:var(--text-secondary);">
        <span>Articles (<?= count($panier) ?>)</span>
        <span><?= number_format($totalCart, 2) ?> DT</span>
      </div>
      <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem; color:var(--text-secondary);">
        <span>Frais de livraison</span>
        <span style="color:#34d399;">Gratuit</span>
      </div>
      
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; padding-top:1rem; border-top:1px solid var(--border);">
        <span style="font-weight:600;">Total TTC</span>
        <span style="font-size:1.8rem; font-weight:800; color:var(--accent-purple-light);"><?= number_format($totalCart, 2) ?> DT</span>
      </div>
      
      <a href="index.php?page=checkout" class="btn-primary" style="width:100%; justify-content:center; padding:16px;">
        Passer la commande <i class="fas fa-arrow-right" style="margin-left:8px;"></i>
      </a>
      
      <div style="margin-top:1.5rem; display:flex; align-items:center; justify-content:center; gap:8px; color:var(--text-muted); font-size:0.8rem;">
        <i class="fas fa-lock"></i> Paiement sécurisé
      </div>
    </div>

  </div>
  <?php endif; ?>

</div>
</div>

<style>
@media (max-width: 900px) {
  .cart-grid { grid-template-columns: 1fr !important; }
  .cart-item { flex-wrap: wrap; }
  .cart-item > div:first-child { width: 80px !important; height: 80px !important; }
  .cart-item .qty-selector { order: 4; margin-right: auto; }
  .cart-item > div:nth-child(4) { order: 5; }
  .cart-item > div:last-child { order: 3; margin-left: auto; position: absolute; right: 1.5rem; top: 1.5rem; }
  .cart-item { position: relative; padding-top: 3rem !important; }
}
</style>

<?php include __DIR__ . '/../partials/footer.php'; ?>
