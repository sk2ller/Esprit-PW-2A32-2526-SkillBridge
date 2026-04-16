<?php
$pageTitle = htmlspecialchars($service['titre']) . ' - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
?>

<div class="page-top">
<div class="container" style="padding-top:2rem;">
  <!-- Breadcrumb -->
  <div style="color:var(--text-muted); font-size:0.85rem; margin-bottom:2rem; display:flex; align-items:center; gap:8px;">
    <a href="index.php?page=services" style="color:var(--accent-purple-light); text-decoration:none;">Services</a>
    <span>›</span>
    <a href="index.php?page=services&categorie=<?= $service['id_categorie'] ?>" style="color:var(--accent-purple-light); text-decoration:none;"><?= htmlspecialchars($service['nom_categorie']) ?></a>
    <span>›</span>
    <span><?= htmlspecialchars(substr($service['titre'], 0, 40)) ?>...</span>
  </div>

  <div style="display:grid; grid-template-columns:1fr 340px; gap:2rem; align-items:start;">
    <!-- Main -->
    <div>
      <div style="background: linear-gradient(135deg, #1a0533, #0a2240); border-radius:var(--radius-lg); height:320px; display:flex; align-items:center; justify-content:center; margin-bottom:2rem; border:1px solid var(--border);">
        <i class="fas fa-briefcase" style="font-size:6rem; color:rgba(255,255,255,0.1);"></i>
      </div>

      <span class="service-category-tag"><?= htmlspecialchars($service['nom_categorie']) ?></span>
      <h1 style="font-family:'Space Grotesk',sans-serif; font-size:1.8rem; font-weight:800; margin:1rem 0;"><?= htmlspecialchars($service['titre']) ?></h1>

      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius); padding:1.5rem; margin-bottom:2rem;">
        <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:1rem;">À propos de ce service</h2>
        <p style="color:var(--text-secondary); line-height:1.8;"><?= nl2br(htmlspecialchars($service['description'])) ?></p>
      </div>
    </div>

    <!-- Sidebar / Order card -->
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

        <!-- Payment CTA Section -->
        <?php if (($_SESSION['role'] ?? 'client') === 'client'): ?>
        <div style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(59, 130, 246, 0.1)); border:2px solid var(--accent-green); border-radius:var(--radius); padding:1rem; margin-bottom:1.2rem;">
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:0.75rem;">
            <i class="fas fa-lock" style="color:var(--accent-green); font-size:0.9rem;"></i>
            <span style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">PAIEMENT SÉCURISÉ</span>
          </div>
          <button onclick="initPayment(<?= $service['prix'] ?>)" style="width:100%; padding:1rem; border:none; background:linear-gradient(135deg, var(--accent-green), #22c55e); color:white; border-radius:var(--radius); font-weight:700; font-size:1rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(34, 197, 94, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
            <i class="fas fa-credit-card"></i> Passer à la caisse
          </button>
          <div style="display:flex; justify-content:space-around; margin-top:0.75rem; font-size:0.7rem; color:var(--text-muted);">
            <div style="display:flex; align-items:center; gap:4px;">
              <i class="fas fa-shield-alt" style="color:var(--accent-green);"></i> Sécurisé
            </div>
            <div style="display:flex; align-items:center; gap:4px;">
              <i class="fas fa-undo" style="color:var(--accent-green);"></i> Garanti
            </div>
            <div style="display:flex; align-items:center; gap:4px;">
              <i class="fas fa-clock" style="color:var(--accent-green);"></i> Instant
            </div>
          </div>
        </div>
        <?php endif; ?>

        <a href="#contact-freelancer" class="btn-primary" style="width:100%; justify-content:center; margin-bottom:0.75rem;">
          <i class="fas fa-envelope"></i> Contacter le freelancer
        </a>
        <a href="index.php?page=services" class="btn-outline" style="width:100%; justify-content:center;">
          <i class="fas fa-arrow-left"></i> Retour aux services
        </a>
      </div>
    </div>
  </div>
</div>
</div>

<?php if (($_SESSION['role'] ?? 'client') === 'client'): ?>
<script>
function initPayment(prix) {
  // Create modal
  const modal = document.createElement('div');
  modal.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); display:flex; align-items:center; justify-content:center; z-index:9999;';
  
  modal.innerHTML = `
    <div style="background:var(--bg-primary); border-radius:var(--radius-lg); padding:2rem; max-width:500px; width:95%; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h2 style="font-size:1.5rem; font-weight:800;">Résumé du paiement</h2>
        <button onclick="this.closest('div').parentElement.remove()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--text-secondary);">✕</button>
      </div>
      
      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius); padding:1rem; margin-bottom:1.5rem;">
        <div style="display:flex; justify-content:space-between; margin-bottom:0.75rem;">
          <span style="color:var(--text-secondary);">Service:</span>
          <span style="font-weight:600;">${document.querySelector('h1').textContent}</span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:0.75rem;">
          <span style="color:var(--text-secondary);">Prix:</span>
          <span style="font-weight:600;">${prix} DT</span>
        </div>
        <div style="border-top:1px solid var(--border); padding-top:0.75rem; display:flex; justify-content:space-between;">
          <span style="font-weight:700;">Total:</span>
          <span style="font-size:1.3rem; font-weight:800; color:var(--accent-green);">${prix} DT</span>
        </div>
      </div>

      <div style="background:rgba(34, 197, 94, 0.1); border:1px solid var(--accent-green); border-radius:var(--radius); padding:1rem; margin-bottom:1.5rem; display:flex; gap:8px; align-items:flex-start;">
        <i class="fas fa-shield-alt" style="color:var(--accent-green); margin-top:2px; flex-shrink:0;"></i>
        <div style="font-size:0.875rem; color:var(--text-secondary);">
          <strong>Paiement sécurisé.</strong> Vos données sont chiffrées et protégées. Aucune information bancaire n'est stockée.
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
        <button style="padding:0.75rem; border:1px solid var(--border); background:var(--bg-secondary); border-radius:var(--radius); cursor:pointer; font-weight:600; transition:all 0.3s;" onmouseover="this.style.background='var(--bg-tertiary)'" onmouseout="this.style.background='var(--bg-secondary)'" onclick="this.closest('div').parentElement.parentElement.remove();">
          Annuler
        </button>
        <button style="padding:0.75rem; background:linear-gradient(135deg, var(--accent-green), #22c55e); color:white; border:none; border-radius:var(--radius); cursor:pointer; font-weight:700; transition:all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(34,197,94,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'" onclick="processPayment(${prix});">
          <i class="fas fa-lock" style="margin-right:6px;"></i> Payer maintenant
        </button>
      </div>

      <div style="text-align:center; font-size:0.75rem; color:var(--text-muted);">
        <i class="fas fa-info-circle"></i> Garantie au 100% ou argent remboursé
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
}

function processPayment(prix) {
  // Simulate payment processing
  const buttons = document.querySelectorAll('button');
  const paymentBtn = Array.from(buttons).find(b => b.textContent.includes('Payer maintenant'));
  if (paymentBtn) {
    paymentBtn.disabled = true;
    paymentBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
  }
  
  setTimeout(() => {
    alert('✅ Paiement réussi!\\n\\nMontant: ' + prix + ' DT\\n\\nVous allez être redirigé vers la commande.');
    // Redirect to orders page or payment confirmation
    // window.location.href = 'index.php?page=my_orders';
  }, 1500);
}
</script>
<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>
