<?php
$pageTitle = (isset($produit) ? 'Modifier' : 'Ajouter') . ' un Produit - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
$isEdit = isset($produit) && $produit;
?>

<div class="page-top">
<div class="container" style="padding-top:2rem; max-width:700px;">

  <div style="margin-bottom:1.5rem;">
    <a href="index.php?page=mes_produits" style="color:var(--accent-purple-light); text-decoration:none; font-size:0.875rem;">
      <i class="fas fa-arrow-left"></i> Mes Produits
    </a>
  </div>

  <h1 style="font-family:'Space Grotesk',sans-serif; font-size:1.8rem; font-weight:800; margin-bottom:0.5rem;">
    <?= $isEdit ? '✏️ Modifier le produit' : '✨ Ajouter un produit' ?>
  </h1>
  <p style="color:var(--text-muted); margin-bottom:2rem;">
    <?= $isEdit ? 'Modifiez les informations de votre produit.' : 'Ajoutez un nouveau produit. Il sera soumis à approbation.' ?>
  </p>

  <div class="form-wrapper" style="max-width:100%;">
    <?php if (!empty($error)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="produitForm">
      <div class="form-group">
        <label class="form-label">Nom du produit <span style="color:#ef4444">*</span></label>
        <input type="text" id="nom" name="nom" class="form-control"
               value="<?= htmlspecialchars($produit['nom'] ?? '') ?>"
               placeholder="Ex: Template Dashboard Admin Pro">
        <div style="color:var(--text-muted); font-size:0.78rem; margin-top:4px;">Soyez précis et attractif</div>
      </div>

      <div class="form-group">
        <label class="form-label">Catégorie <span style="color:#ef4444">*</span></label>
        <select id="id_categorie" name="id_categorie" class="form-control">
          <option value="">Choisir une catégorie...</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id_categorie'] ?>"
            <?= (($produit['id_categorie'] ?? '') == $cat['id_categorie']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['nom_categorie']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Description <span style="color:#ef4444">*</span></label>
        <textarea id="description" name="description" class="form-control" rows="6"
                  placeholder="Décrivez en détail votre produit, ses fonctionnalités, ce qui est inclus..."><?= htmlspecialchars($produit['description'] ?? '') ?></textarea>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        <div class="form-group">
          <label class="form-label">Prix (DT) <span style="color:#ef4444">*</span></label>
          <input type="number" id="prix" name="prix" class="form-control" min="1" step="0.01"
                 value="<?= $produit['prix'] ?? '' ?>" placeholder="Ex: 49.99">
        </div>
        <div class="form-group">
          <label class="form-label">Quantité en stock <span style="color:#ef4444">*</span></label>
          <input type="number" id="quantite" name="quantite" class="form-control" min="1"
                 value="<?= $produit['quantite'] ?? '' ?>" placeholder="Ex: 100">
        </div>
      </div>

      <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1rem;">
        <a href="index.php?page=mes_produits" class="btn-outline">Annuler</a>
        <button type="submit" class="btn-primary">
          <i class="fas fa-<?= $isEdit ? 'save' : 'paper-plane' ?>"></i>
          <?= $isEdit ? 'Enregistrer' : 'Soumettre le produit' ?>
        </button>
      </div>
    </form>
  </div>

</div>
</div>

<?php 
$scriptFile = $isEdit ? 'edit_produit.js' : 'add_produit.js';
echo '<script src="views/assets/js/' . $scriptFile . '"></script>';
?>

<?php include __DIR__ . '/../partials/footer.php'; ?>
