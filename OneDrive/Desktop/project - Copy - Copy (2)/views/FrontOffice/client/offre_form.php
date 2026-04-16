<?php
$pageTitle = (isset($offre) ? 'Modifier' : 'Publier') . ' une Offre Job - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
$isEdit = isset($offre) && $offre;
$id_client = $id_client ?? 1;
?>

<div class="page-top">
<div class="container" style="padding-top:2rem; max-width:700px;">

  <div style="margin-bottom:1.5rem;">
    <a href="index.php?page=mes_offres&id_client=<?= $id_client ?>" style="color:var(--accent-purple-light); text-decoration:none; font-size:0.875rem;">
      <i class="fas fa-arrow-left"></i> Mes Offres
    </a>
  </div>

  <h1 style="font-family:'Space Grotesk',sans-serif; font-size:1.8rem; font-weight:800; margin-bottom:0.5rem;">
    <?= $isEdit ? '✏️ Modifier l\'offre' : '📢 Publier une offre job' ?>
  </h1>
  <p style="color:var(--text-muted); margin-bottom:2rem;">
    <?= $isEdit ? 'Modifiez les détails de votre offre job.' : 'Publiez une offre job pour trouver les meilleurs freelancers.' ?>
  </p>

  <div class="form-wrapper" style="max-width:100%;">
    <?php if (!empty($error)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="offreForm" onsubmit="return validateOffreForm();">
      
      <!-- Titre -->
      <div class="form-group">
        <label class="form-label">Titre de l'offre <span style="color:#ef4444">*</span></label>
        <input type="text" id="titre" name="titre" class="form-control"
               value="<?= htmlspecialchars($offre['titre'] ?? '') ?>"
               placeholder="Ex: Développeur React pour application mobile">
        <div id="titre-error" style="color:#ef4444; font-size:0.78rem; margin-top:4px;"></div>
        <div style="color:var(--text-muted); font-size:0.78rem; margin-top:4px;">Soyez précis et attrayant</div>
      </div>

      <!-- Description -->
      <div class="form-group">
        <label class="form-label">Description de l'offre <span style="color:#ef4444">*</span></label>
        <textarea id="description" name="description" class="form-control" rows="6"
                  placeholder="Décrivez en détail le projet, vos attentes, les tâches principales..."><?= htmlspecialchars($offre['description'] ?? '') ?></textarea>
        <div id="description-error" style="color:#ef4444; font-size:0.78rem; margin-top:4px;"></div>
        <div style="color:var(--text-muted); font-size:0.78rem; margin-top:4px;">Minimum 20 caractères</div>
      </div>

      <!-- Budget et Niveau -->
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        
        <div class="form-group">
          <label class="form-label">Budget (DT) <span style="color:#ef4444">*</span></label>
          <input type="number" id="budget" name="budget" class="form-control" min="1" step="0.01"
                 value="<?= $offre['budget'] ?? '' ?>" placeholder="Ex: 500.00">
          <div id="budget-error" style="color:#ef4444; font-size:0.78rem; margin-top:4px;"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Niveau requis <span style="color:#ef4444">*</span></label>
          <select id="niveau_requis" name="niveau_requis" class="form-control">
            <option value="">-- Sélectionnez un niveau --</option>
            <option value="debutant" <?= (($offre['niveau_requis'] ?? '') === 'debutant') ? 'selected' : '' ?>>Débutant</option>
            <option value="intermediaire" <?= (($offre['niveau_requis'] ?? '') === 'intermediaire') ? 'selected' : '' ?>>Intermédiaire</option>
            <option value="expert" <?= (($offre['niveau_requis'] ?? '') === 'expert') ? 'selected' : '' ?>>Expert</option>
          </select>
          <div id="niveau-error" style="color:#ef4444; font-size:0.78rem; margin-top:4px;"></div>
        </div>
      </div>

      <!-- Compétences requises -->
      <div class="form-group">
        <label class="form-label">Compétences requises (séparées par des virgules)</label>
        <input type="text" id="competences_requises" name="competences_requises" class="form-control"
               value="<?= htmlspecialchars($offre['competences_requises'] ?? '') ?>"
               placeholder="Ex: React, Node.js, MongoDB, REST API">
        <div id="competences-error" style="color:#ef4444; font-size:0.78rem; margin-top:4px;"></div>
        <div style="color:var(--text-muted); font-size:0.78rem; margin-top:4px;">Listez les compétences principales requises</div>
      </div>

      <!-- Délai de publication -->
      <div class="form-group">
        <label class="form-label">Durée de publication (jours)</label>
        <input type="number" id="delai_publication" name="delai_publication" class="form-control" min="1" max="365"
               value="<?= $offre['delai_publication'] ?? 30 ?>" placeholder="Ex: 30">
        <div style="color:var(--text-muted); font-size:0.78rem; margin-top:4px;">Par défaut: 30 jours</div>
      </div>

      <!-- Buttons -->
      <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:2rem;">
        <a href="index.php?page=mes_offres&id_client=<?= $id_client ?>" class="btn-outline">Annuler</a>
        <button type="submit" class="btn-primary">
          <i class="fas fa-<?= $isEdit ? 'save' : 'paper-plane' ?>"></i>
          <?= $isEdit ? 'Enregistrer les modifications' : 'Publier l\'offre' ?>
        </button>
      </div>

    </form>
  </div>

</div>
</div>

<script src="views/assets/js/add_offre.js"></script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
