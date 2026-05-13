<?php
$pageTitle = htmlspecialchars($service['titre']) . ' - Geeks';
include __DIR__ . '/navbar.php';
?>

<div class="page-top">
<div class="container" style="padding-top:2rem;">
  <div style="color:var(--text-muted); font-size:0.85rem; margin-bottom:2rem; display:flex; align-items:center; gap:8px;">
    <a href="index.php?page=services" style="color:var(--accent-purple-light); text-decoration:none;">Services</a>
    <span>&rsaquo;</span>
    <a href="index.php?page=services&categorie=<?= $service['id_categorie'] ?>" style="color:var(--accent-purple-light); text-decoration:none;"><?= htmlspecialchars($service['nom_categorie']) ?></a>
    <span>&rsaquo;</span>
    <span><?= htmlspecialchars(substr($service['titre'], 0, 40)) ?>...</span>
  </div>

  <div style="display:grid; grid-template-columns:1fr 340px; gap:2rem; align-items:start;">
    <div>
      <div style="height:320px; border-radius:var(--radius-lg); overflow:hidden; background:linear-gradient(135deg, #5c6f86, #d9d9d9); display:flex; align-items:center; justify-content:center; margin-bottom:2rem; border:1px solid var(--border);">
        <?php if (!empty($service['thumbnail'])): ?>
          <img src="views/assets/uploads/<?= rawurlencode($service['thumbnail']) ?>"
               alt="<?= htmlspecialchars($service['titre']) ?>"
               style="width:100%; height:100%; object-fit:cover;">
        <?php else: ?>
          <i class="fas fa-briefcase" style="font-size:6rem; color:rgba(255,255,255,0.25);"></i>
        <?php endif; ?>
      </div>

      <span class="service-category-tag"><?= htmlspecialchars($service['nom_categorie']) ?></span>
      <h1 style="font-family:'Space Grotesk',sans-serif; font-size:1.8rem; font-weight:800; margin:1rem 0;"><?= htmlspecialchars($service['titre']) ?></h1>

      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius); padding:1.5rem; margin-bottom:1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; margin-bottom:1rem;">
          <h2 style="font-size:1.1rem; font-weight:700; margin:0;">A propos de ce service</h2>
          <div style="display:flex; gap:0.45rem; flex-wrap:wrap;">
            <button type="button" class="client-translate-btn" data-lang="ar" style="border:1px solid var(--border); background:transparent; color:var(--text-secondary); padding:0.45rem 0.7rem; border-radius:8px; cursor:pointer; font-weight:700;">العربية</button>
            <button type="button" class="client-translate-btn" data-lang="fr" style="border:1px solid var(--border); background:transparent; color:var(--text-secondary); padding:0.45rem 0.7rem; border-radius:8px; cursor:pointer; font-weight:700;">Francais</button>
            <button type="button" class="client-translate-btn" data-lang="en" style="border:1px solid var(--border); background:transparent; color:var(--text-secondary); padding:0.45rem 0.7rem; border-radius:8px; cursor:pointer; font-weight:700;">English</button>
          </div>
        </div>
        <p id="clientServiceDescription" style="color:var(--text-secondary); line-height:1.8;"><?= nl2br(htmlspecialchars($service['description'])) ?></p>
        <div id="clientTranslationStatus" style="color:var(--text-muted); font-size:0.82rem; margin-top:0.75rem;"></div>
      </div>

      <?php if (($service['langue_detectee'] ?? '') === 'ar' && (!empty($service['description_fr']) || !empty($service['description_en']))): ?>
      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius); padding:1.5rem; margin-bottom:1.5rem;">
        <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:1rem;">Traductions automatiques</h2>
        <?php if (!empty($service['description_fr'])): ?>
        <div style="margin-bottom:1rem;">
          <div style="font-weight:700; margin-bottom:0.4rem;">Francais</div>
          <p style="color:var(--text-secondary); line-height:1.8;"><?= nl2br(htmlspecialchars($service['description_fr'])) ?></p>
        </div>
        <?php endif; ?>
        <?php if (!empty($service['description_en'])): ?>
        <div>
          <div style="font-weight:700; margin-bottom:0.4rem;">English</div>
          <p style="color:var(--text-secondary); line-height:1.8;"><?= nl2br(htmlspecialchars($service['description_en'])) ?></p>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius); padding:1.5rem;">
        <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:1rem;">Informations utiles</h2>
        <div style="display:grid; gap:12px;">
          <div style="display:flex; align-items:center; gap:12px; padding:1rem; border:1px solid var(--border); border-radius:12px;">
            <div style="width:42px; height:42px; border-radius:10px; background:rgba(16,185,129,0.12); display:flex; align-items:center; justify-content:center;">
              <i class="fas fa-money-bill-wave" style="color:var(--accent-green);"></i>
            </div>
            <div>
              <div style="font-weight:700;">Prix propose</div>
              <div style="font-size:0.85rem; color:var(--text-muted);"><?= number_format($service['prix'], 2) ?> DT</div>
            </div>
          </div>

          <div style="display:flex; align-items:center; gap:12px; padding:1rem; border:1px solid var(--border); border-radius:12px;">
            <div style="width:42px; height:42px; border-radius:10px; background:rgba(124,58,237,0.12); display:flex; align-items:center; justify-content:center;">
              <i class="fas fa-clock" style="color:var(--accent-purple-light);"></i>
            </div>
            <div>
              <div style="font-weight:700;">Delai de livraison</div>
              <div style="font-size:0.85rem; color:var(--text-muted);"><?= (int) $service['delai_livraison'] ?> jour(s)</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div style="position:sticky; top:calc(var(--nav-height) + 1rem);">
      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius-lg); padding:1.5rem;">
        <div style="font-size:2rem; font-weight:800; color:var(--accent-green); margin-bottom:0.5rem;">
          <?= number_format($service['prix'], 2) ?> DT
        </div>
        <?php if (!empty($_SESSION['payment_error'])): ?>
        <div style="background:rgba(239,68,68,0.10); border:1px solid rgba(239,68,68,0.35); color:#ef4444; border-radius:10px; padding:0.85rem; margin-bottom:1rem; font-size:0.86rem;">
          <?= htmlspecialchars($_SESSION['payment_error']) ?>
        </div>
        <?php unset($_SESSION['payment_error']); endif; ?>
        <div style="color:var(--text-muted); font-size:0.875rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:6px;">
          <i class="fas fa-clock"></i> Livraison en <?= $service['delai_livraison'] ?> jours
        </div>

        <div style="border-top:1px solid var(--border); padding-top:1.2rem; margin-bottom:1.5rem;">
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px; color:var(--text-secondary); font-size:0.875rem;">
            <i class="fas fa-check-circle" style="color:var(--accent-green)"></i> Service approuve
          </div>
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px; color:var(--text-secondary); font-size:0.875rem;">
            <i class="fas fa-shield-alt" style="color:var(--accent-purple-light)"></i> Paiement securise
          </div>
          <div style="display:flex; align-items:center; gap:8px; color:var(--text-secondary); font-size:0.875rem;">
            <i class="fas fa-undo" style="color:var(--accent-orange)"></i> Suivi simple et clair
          </div>
        </div>

        <a href="index.php?page=services" class="btn-primary" style="width:100%; justify-content:center; margin-bottom:0.75rem;">
          <i class="fas fa-arrow-left"></i> Retour aux services
        </a>
        <a href="index.php?page=stripe_checkout&id=<?= (int) $service['id_service'] ?>" class="btn-primary" style="width:100%; justify-content:center; margin-bottom:0.75rem; background:#635bff;">
          <i class="fas fa-credit-card"></i> Payer avec Stripe
        </a>
        <a href="index.php?role=client&page=chat&service_id=<?= (int) $service['id_service'] ?>" class="btn-primary" style="width:100%; justify-content:center; margin-bottom:0.75rem;">
          <i class="fas fa-comments"></i> Contacter le freelancer
        </a>
        <a href="index.php?role=admin&page=admin_dashboard" class="btn-outline" style="width:100%; justify-content:center;">
          <i class="fas fa-gauge-high"></i> Dashboard Admin
        </a>
      </div>
    </div>
  </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const descriptionBox = document.getElementById('clientServiceDescription');
  const status = document.getElementById('clientTranslationStatus');
  const buttons = document.querySelectorAll('.client-translate-btn');
  const originalDescription = <?= json_encode($service['description']) ?>;
  const serviceId = <?= (int) $service['id_service'] ?>;
  let translations = null;

  function setDescription(text, lang) {
    descriptionBox.textContent = text;
    descriptionBox.dir = lang === 'ar' ? 'rtl' : 'ltr';
    descriptionBox.style.textAlign = lang === 'ar' ? 'right' : 'left';
  }

  function loadTranslations(callback) {
    if (translations) {
      callback();
      return;
    }

    if (status) {
      status.textContent = 'Traduction en cours...';
    }

    const formData = new FormData();
    formData.append('id_service', serviceId);

    fetch('index.php?page=client_translate_service', {
      method: 'POST',
      body: formData
    })
      .then((response) => response.json())
      .then((data) => {
        if (!data.success) {
          throw new Error(data.message || 'Traduction impossible pour le moment.');
        }
        translations = data;
        if (status) {
          if (data.source === 'gemini') {
            status.textContent = 'Traduction IA disponible.';
          } else if (data.api_configured) {
            status.textContent = 'Gemini est configure, mais la reponse API est indisponible. Verifiez Internet ou les restrictions de la cle.';
          } else {
            status.textContent = 'Configurez GEMINI_API_KEY pour une vraie traduction IA.';
          }
        }
        callback();
      })
      .catch((error) => {
        if (status) {
          status.textContent = error.message;
          status.style.color = '#ef4444';
        }
      });
  }

  buttons.forEach(function (button) {
    button.addEventListener('click', function () {
      const lang = button.dataset.lang;

      loadTranslations(function () {
        if (lang === 'ar') {
          setDescription(translations.description_ar || originalDescription, 'ar');
        } else if (lang === 'en') {
          setDescription(translations.description_en || originalDescription, 'en');
        } else {
          setDescription(translations.description_fr || originalDescription, 'fr');
        }
      });
    });
  });
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
