<?php
$pageTitle = (isset($service) ? 'Modifier' : 'Créer') . ' un Service - Geeks';
include __DIR__ . '/navbar.php';
$isEdit = isset($service) && $service;
?>

<div class="page-top">
<div class="container" style="padding-top:2rem; max-width:700px;">

  <div style="margin-bottom:1.5rem;">
    <a href="index.php?page=my_services" style="color:var(--accent-purple-light); text-decoration:none; font-size:0.875rem;">
      <i class="fas fa-arrow-left"></i> Mes Services
    </a>
  </div>

  <h1 style="font-family:'Space Grotesk',sans-serif; font-size:1.8rem; font-weight:800; margin-bottom:0.5rem;">
    <?= $isEdit ? '✏️ Modifier le service' : '✨ Créer un service' ?>
  </h1>
  <p style="color:var(--text-muted); margin-bottom:2rem;">
    <?= $isEdit ? 'Modifiez les informations de votre service.' : 'Créez un nouveau service. Il sera soumis à approbation.' ?>
  </p>

  <div class="form-wrapper" style="max-width:100%;">
    <?php if (!empty($error)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label class="form-label">Titre du service <span style="color:#ef4444">*</span></label>
        <input type="text" id="titre" name="titre" class="form-control"
               value="<?= htmlspecialchars($service['titre'] ?? '') ?>"
               placeholder="Ex: Création site WordPress professionnel">
        <div style="color:var(--text-muted); font-size:0.78rem; margin-top:4px;">Soyez précis et attractif</div>
      </div>

      <div class="form-group">
        <label class="form-label">Catégorie <span style="color:#ef4444">*</span></label>
        <select id="id_categorie" name="id_categorie" class="form-control">
          <option value="">Choisir une catégorie...</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id_categorie'] ?>"
            <?= (($service['id_categorie'] ?? '') == $cat['id_categorie']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['nom_categorie']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Description <span style="color:#ef4444">*</span></label>
        <textarea id="description" name="description" class="form-control" rows="6"
                  placeholder="Décrivez en détail votre service, ce que vous offrez, vos compétences..."><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        <div class="form-group">
          <label class="form-label">Prix (DT) <span style="color:#ef4444">*</span></label>
          <input type="number" id="prix" name="prix" class="form-control" min="1" step="0.01"
                 value="<?= $service['prix'] ?? '' ?>" placeholder="Ex: 99.00">
        </div>
        <div class="form-group">
          <label class="form-label">Délai de livraison (jours) <span style="color:#ef4444">*</span></label>
          <input type="number" id="delai_livraison" name="delai_livraison" class="form-control" min="1"
                 value="<?= $service['delai_livraison'] ?? '' ?>" placeholder="Ex: 7">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Thumbnail (optionnel)</label>
        <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept=".jpg,.jpeg,.png,.webp">
        <div style="color:var(--text-muted); font-size:0.78rem; margin-top:4px;">
          Formats autorisés : JPG, JPEG, PNG, WEBP
        </div>

        <?php if ($isEdit && !empty($service['thumbnail'])): ?>
        <div style="margin-top:10px;">
          <img src="views/assets/uploads/<?= htmlspecialchars($service['thumbnail']) ?>" alt="Thumbnail"
               style="width:140px; height:90px; object-fit:cover; border-radius:10px; border:1px solid var(--border);">
        </div>
        <?php endif; ?>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        <div class="form-group">
          <label class="form-label">CV (optionnel)</label>
          <input type="file" id="cv" name="cv" class="form-control" accept=".pdf,.doc,.docx">
          <div style="color:var(--text-muted); font-size:0.78rem; margin-top:4px;">
            Formats autorisés : PDF, DOC, DOCX
          </div>

          <?php if ($isEdit && !empty($service['cv'])): ?>
          <div style="margin-top:6px; font-size:0.8rem;">
            Fichier actuel :
            <a href="views/assets/uploads/<?= htmlspecialchars($service['cv']) ?>" target="_blank">
              Voir le CV
            </a>
          </div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label">Portfolio (optionnel)</label>
          <input type="file" id="portfolio" name="portfolio" class="form-control" accept=".pdf,.zip,.jpg,.jpeg,.png">
          <div style="color:var(--text-muted); font-size:0.78rem; margin-top:4px;">
            Formats autorisés : PDF, ZIP, JPG, JPEG, PNG
          </div>

          <?php if ($isEdit && !empty($service['portfolio'])): ?>
          <div style="margin-top:6px; font-size:0.8rem;">
            Fichier actuel :
            <a href="views/assets/uploads/<?= htmlspecialchars($service['portfolio']) ?>" target="_blank">
              Voir le portfolio
            </a>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1rem;">
        <a href="index.php?page=my_services" class="btn-outline">Annuler</a>
        <button type="submit" class="btn-primary">
          <i class="fas fa-<?= $isEdit ? 'save' : 'paper-plane' ?>"></i>
          <?= $isEdit ? 'Enregistrer' : 'Soumettre le service' ?>
        </button>
      </div>
    </form>
  </div>

</div>
</div>

<?php
$scriptFile = $isEdit ? 'edit_service.js' : 'add_service.js';
echo '<script src="views/assets/js/' . $scriptFile . '"></script>';
?>

<?php include __DIR__ . '/footer.php'; ?>
