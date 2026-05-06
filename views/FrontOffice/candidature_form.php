<?php
$pageTitle = 'Candidater - ' . htmlspecialchars($offre['titre']);
include __DIR__ . '/navbar.php';
?>

<div class="page-top">
<div class="container" style="padding-top:2rem; max-width:800px;">

  <div style="margin-bottom:1.5rem;">
    <a href="index.php?page=offre_detail&id=<?= (int) $offre['id_offre'] ?>" style="color:var(--accent-purple-light); text-decoration:none; font-size:0.875rem;">
      <i class="fas fa-arrow-left"></i> Retour a l'offre
    </a>
  </div>

  <h1 style="font-family:'Space Grotesk',sans-serif; font-size:1.8rem; font-weight:800; margin-bottom:0.5rem;">
    Candidater a cette offre
  </h1>
  <p style="color:var(--text-muted); margin-bottom:1.5rem;">
    <strong><?= htmlspecialchars($offre['titre']) ?></strong>
  </p>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="form-wrapper" style="max-width:100%;">
    <form method="POST" enctype="multipart/form-data" id="candidatureForm">
      <div class="form-group">
        <label class="form-label">Nom complet <span style="color:#ef4444">*</span></label>
        <input type="text" id="nom_freelancer" name="nom_freelancer" class="form-control"
               value="<?= htmlspecialchars($_POST['nom_freelancer'] ?? '') ?>"
               placeholder="Votre nom et prenom">
        <div id="nom_freelancer-error" style="color:#ef4444; font-size:0.78rem; margin-top:4px;"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Email <span style="color:#ef4444">*</span></label>
        <input type="email" id="email_freelancer" name="email_freelancer" class="form-control"
               value="<?= htmlspecialchars($_POST['email_freelancer'] ?? '') ?>"
               placeholder="votre@email.com">
        <div id="email_freelancer-error" style="color:#ef4444; font-size:0.78rem; margin-top:4px;"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Tarif propose (DT)</label>
        <input type="number" id="tarif_propose" name="tarif_propose" class="form-control" step="0.01"
               value="<?= htmlspecialchars($_POST['tarif_propose'] ?? '') ?>"
               placeholder="Ex: 1200">
        <div id="tarif_propose-error" style="color:#ef4444; font-size:0.78rem; margin-top:4px;"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Message de candidature <span style="color:#ef4444">*</span></label>
        <textarea id="message" name="message" class="form-control" rows="6"
                  placeholder="Presentez votre profil, vos competences et pourquoi vous etes le bon freelancer pour cette offre."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        <div id="message-error" style="color:#ef4444; font-size:0.78rem; margin-top:4px;"></div>
      </div>

      <div class="form-group">
        <label class="form-label">CV (PDF/DOC/DOCX)</label>
        <input type="file" id="cv_file" name="cv_file" class="form-control" accept=".pdf,.doc,.docx">
        <div style="color:var(--text-muted); font-size:0.78rem; margin-top:4px;">Taille max: 5MB</div>
        <div id="cv_file-error" style="color:#ef4444; font-size:0.78rem; margin-top:4px;"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Portfolio (PDF/DOC/DOCX/ZIP)</label>
        <input type="file" id="portfolio_file" name="portfolio_file" class="form-control" accept=".pdf,.doc,.docx,.zip">
        <div style="color:var(--text-muted); font-size:0.78rem; margin-top:4px;">Taille max: 10MB</div>
        <div id="portfolio_file-error" style="color:#ef4444; font-size:0.78rem; margin-top:4px;"></div>
      </div>

      <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1.5rem;">
        <a href="index.php?page=offre_detail&id=<?= (int) $offre['id_offre'] ?>" class="btn-outline">Annuler</a>
        <button type="submit" class="btn-primary">
          <i class="fas fa-paper-plane"></i> Envoyer ma candidature
        </button>
      </div>
    </form>
  </div>

</div>
</div>

<script src="views/assets/js/candidature.js"></script>
<script>
<?php if (!empty($error)): ?>
window.SkillBridgeAlerts?.dialog('error', 'Candidature refusee', <?= json_encode($error) ?>);
<?php endif; ?>
</script>
<?php include __DIR__ . '/footer.php'; ?>
