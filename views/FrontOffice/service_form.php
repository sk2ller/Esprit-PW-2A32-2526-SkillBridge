<?php
$pageTitle = (isset($service) ? 'Modifier' : 'Creer') . ' un Service - Geeks';
include __DIR__ . '/navbar.php';
$isEdit = isset($service) && $service;
?>

<div class="freelancer-dashboard">
  <?php include __DIR__ . '/freelancer_sidebar.php'; ?>

  <div class="freelancer-main">
    <div class="freelancer-hero">
      <div>
        <h1><?= $isEdit ? 'Modifier un service' : 'Creer un service' ?></h1>
        <p><?= $isEdit ? 'Mettez a jour votre offre depuis votre dashboard freelancer.' : 'Ajoutez une nouvelle offre avec une presentation claire et professionnelle.' ?></p>
      </div>
      <a href="index.php?page=my_services" class="btn-outline">
        <i class="fas fa-arrow-left"></i> Retour
      </a>
    </div>

    <div class="freelancer-surface">
      <div class="form-wrapper" style="max-width:100%; margin:0; padding:0; background:transparent; border:none; box-shadow:none;">
        <?php if (!empty($error)): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label class="form-label">Titre du service <span style="color:#ef4444">*</span></label>
            <input type="text" id="titre" name="titre" class="form-control"
                   value="<?= htmlspecialchars($service['titre'] ?? '') ?>"
                   placeholder="Ex: Creation site WordPress professionnel"
                   style="<?= isset($errors['titre']) ? 'border-color: #ef4444; background-color: rgba(239, 68, 68, 0.05);' : '' ?>">
            <?php if (isset($errors['titre'])): ?>
            <div style="color: #ef4444; font-size: 0.82rem; margin-top: 6px; display: flex; align-items: center; gap: 4px;">
              <i class="fas fa-exclamation-circle"></i> <?= $errors['titre'] ?>
            </div>
            <?php endif; ?>
            <div style="color:var(--text-muted); font-size:0.78rem; margin-top:4px;">Soyez precis et attractif</div>
          </div>

          <div class="form-group">
            <label class="form-label">Categorie <span style="color:#ef4444">*</span></label>
            <select id="id_categorie" name="id_categorie" class="form-control"
                    style="<?= isset($errors['id_categorie']) ? 'border-color: #ef4444; background-color: rgba(239, 68, 68, 0.05);' : '' ?>">
              <option value="">Choisir une categorie...</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id_categorie'] ?>"
                <?= (($service['id_categorie'] ?? '') == $cat['id_categorie']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['nom_categorie']) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($errors['id_categorie'])): ?>
            <div style="color: #ef4444; font-size: 0.82rem; margin-top: 6px; display: flex; align-items: center; gap: 4px;">
              <i class="fas fa-exclamation-circle"></i> <?= $errors['id_categorie'] ?>
            </div>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label class="form-label">Description <span style="color:#ef4444">*</span></label>
            <textarea id="description" name="description" class="form-control" rows="6"
                      placeholder="Decrivez en detail votre service, ce que vous offrez, vos competences..."
                      style="<?= isset($errors['description']) ? 'border-color: #ef4444; background-color: rgba(239, 68, 68, 0.05);' : '' ?>"><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
            <?php if (isset($errors['description'])): ?>
            <div style="color: #ef4444; font-size: 0.82rem; margin-top: 6px; display: flex; align-items: center; gap: 4px;">
              <i class="fas fa-exclamation-circle"></i> <?= $errors['description'] ?>
            </div>
            <?php endif; ?>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
              <label class="form-label">Prix (DT) <span style="color:#ef4444">*</span></label>
              <input type="number" id="prix" name="prix" class="form-control" min="1" step="0.01"
                     value="<?= $service['prix'] ?? '' ?>" placeholder="Ex: 99.00"
                     style="<?= isset($errors['prix']) ? 'border-color: #ef4444; background-color: rgba(239, 68, 68, 0.05);' : '' ?>">
              <?php if (isset($errors['prix'])): ?>
              <div style="color: #ef4444; font-size: 0.82rem; margin-top: 6px; display: flex; align-items: center; gap: 4px;">
                <i class="fas fa-exclamation-circle"></i> <?= $errors['prix'] ?>
              </div>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label class="form-label">Delai de livraison (jours) <span style="color:#ef4444">*</span></label>
              <input type="number" id="delai_livraison" name="delai_livraison" class="form-control" min="1"
                     value="<?= $service['delai_livraison'] ?? '' ?>" placeholder="Ex: 7"
                     style="<?= isset($errors['delai_livraison']) ? 'border-color: #ef4444; background-color: rgba(239, 68, 68, 0.05);' : '' ?>">
              <?php if (isset($errors['delai_livraison'])): ?>
              <div style="color: #ef4444; font-size: 0.82rem; margin-top: 6px; display: flex; align-items: center; gap: 4px;">
                <i class="fas fa-exclamation-circle"></i> <?= $errors['delai_livraison'] ?>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Miniature (optionnel)</label>
            <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept=".jpg,.jpeg,.png,.webp"
                   style="<?= isset($errors['thumbnail']) ? 'border-color: #ef4444; background-color: rgba(239, 68, 68, 0.05);' : '' ?>">
            <?php if (isset($errors['thumbnail'])): ?>
            <div style="color: #ef4444; font-size: 0.82rem; margin-top: 6px; display: flex; align-items: center; gap: 4px;">
              <i class="fas fa-exclamation-circle"></i> <?= $errors['thumbnail'] ?>
            </div>
            <?php endif; ?>
            <div style="color:var(--text-muted); font-size:0.78rem; margin-top:4px;">
              Formats autorises : JPG, JPEG, PNG, WEBP
            </div>

            <?php if ($isEdit && !empty($service['thumbnail'])): ?>
            <div style="margin-top:10px;">
              <img src="views/assets/uploads/<?= rawurlencode($service['thumbnail']) ?>" alt="Thumbnail"
                   style="width:140px; height:90px; object-fit:cover; border-radius:10px; border:1px solid var(--border);">
            </div>
            <?php endif; ?>
          </div>

          <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:14px; padding:1rem 1.1rem; margin-top:0.25rem;">
            <div style="display:flex; align-items:flex-start; gap:10px;">
              <i class="fas fa-circle-info" style="color:var(--accent-purple-light); margin-top:2px;"></i>
              <div>
                <div style="font-weight:700; margin-bottom:4px;">CV et portfolio retires</div>
                <div style="color:var(--text-muted); font-size:0.84rem; line-height:1.6;">
                  Ce formulaire ne demande plus de CV ni de portfolio. Seule la miniature du service reste disponible.
                </div>
              </div>
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
</div>

<?php
$scriptFile = $isEdit ? 'edit_service.js' : 'add_service.js';
echo '<script src="views/assets/js/' . $scriptFile . '"></script>';
?>

<?php include __DIR__ . '/footer.php'; ?>
