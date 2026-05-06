<?php
$pageTitle = (isset($categorie) ? 'Modifier' : 'Créer') . ' une Catégorie - Admin Geeks';
$isEdit = isset($categorie) && $categorie;
include __DIR__ . '/sidebar.php';

$icones = [
  'fas fa-code' => 'Web/Code',
  'fas fa-palette' => 'Design',
  'fas fa-mobile-alt' => 'Mobile',
  'fas fa-bullhorn' => 'Marketing',
  'fas fa-music' => 'Musique',
  'fas fa-camera' => 'Photo',
  'fas fa-briefcase' => 'Business',
  'fas fa-server' => 'IT/Serveur',
  'fas fa-pen-nib' => 'Rédaction',
  'fas fa-language' => 'Traduction',
  'fas fa-chart-line' => 'Finance',
  'fas fa-graduation-cap' => 'Formation',
];
?>

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title"><?= $isEdit ? 'Modifier' : 'Créer' ?> une Catégorie</div>
      <div class="topbar-bread">
        <a href="index.php?page=admin_dashboard">Dashboard</a> ›
        <a href="index.php?page=admin_categories">Catégories</a> ›
        <?= $isEdit ? 'Modifier' : 'Nouvelle' ?>
      </div>
    </div>
    <div class="topbar-actions">
      <a href="index.php?page=admin_categories" class="topbar-btn topbar-btn-outline">
        <i class="fas fa-arrow-left"></i> Retour
      </a>
    </div>
  </div>

  <div class="admin-content">

    <?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div style="max-width:640px;">
      <div class="admin-form-card">
        <h2 style="font-family:'Space Grotesk',sans-serif; font-size:1.2rem; font-weight:700; margin-bottom:1.5rem;">
          <?= $isEdit ? '✏️ Modifier la catégorie' : '✨ Nouvelle catégorie' ?>
        </h2>

        <form method="POST" id="categoryForm">
          <div class="form-group">
            <label class="form-label">Nom de la catégorie <span style="color:#ef4444">*</span></label>
            <input type="text" id="nom_categorie" name="nom_categorie" class="form-control"
                   value="<?= htmlspecialchars($categorie['nom_categorie'] ?? '') ?>"
                   placeholder="Ex: Web Development"
                   style="<?= isset($errors['nom_categorie']) ? 'border-color: #ef4444; background-color: rgba(239, 68, 68, 0.05);' : '' ?>">
            <?php if (isset($errors['nom_categorie'])): ?>
            <div style="color: #ef4444; font-size: 0.82rem; margin-top: 6px; display: flex; align-items: center; gap: 4px;">
              <i class="fas fa-exclamation-circle"></i> <?= $errors['nom_categorie'] ?>
            </div>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3"
                      placeholder="Décrivez cette catégorie..."
                      style="<?= isset($errors['description']) ? 'border-color: #ef4444; background-color: rgba(239, 68, 68, 0.05);' : '' ?>"><?= htmlspecialchars($categorie['description'] ?? '') ?></textarea>
            <?php if (isset($errors['description'])): ?>
            <div style="color: #ef4444; font-size: 0.82rem; margin-top: 6px; display: flex; align-items: center; gap: 4px;">
              <i class="fas fa-exclamation-circle"></i> <?= $errors['description'] ?>
            </div>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label class="form-label">Icône Font Awesome</label>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:10px;">
              <?php foreach ($icones as $cls => $label): ?>
              <label style="cursor:pointer;">
                <input type="radio" name="icone" value="<?= $cls ?>"
                  <?= (($categorie['icone'] ?? 'fas fa-folder') === $cls) ? 'checked' : '' ?>
                  style="display:none;" class="icone-radio" id="ico_<?= md5($cls) ?>">
                <div onclick="selectIcon('<?= $cls ?>')"
                     id="icard_<?= md5($cls) ?>"
                     style="padding:10px; border:2px solid <?= (($categorie['icone'] ?? '') === $cls) ? 'var(--accent)' : 'var(--border)' ?>; border-radius:8px; text-align:center; transition:all 0.2s; background:var(--bg-main);">
                  <i class="<?= $cls ?>" style="font-size:1.2rem; color:var(--accent-light); display:block; margin-bottom:4px;"></i>
                  <div style="font-size:0.7rem; color:var(--text-muted);"><?= $label ?></div>
                </div>
              </label>
              <?php endforeach; ?>
            </div>
            <!-- Custom icon -->
            <input type="text" name="icone" id="icone_custom" class="form-control"
                   value="<?= htmlspecialchars($categorie['icone'] ?? 'fas fa-folder') ?>"
                   placeholder="ou entrez une classe Font Awesome manuelle">
          </div>

          <div style="display:flex; gap:1rem; justify-content:flex-end; padding-top:1rem; border-top:1px solid var(--border);">
            <a href="index.php?page=admin_categories" class="admin-btn admin-btn-outline">Annuler</a>
            <button type="submit" class="admin-btn admin-btn-primary">
              <i class="fas fa-<?= $isEdit ? 'save' : 'plus' ?>"></i>
              <?= $isEdit ? 'Enregistrer' : 'Créer la catégorie' ?>
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</main>

<script>
function selectIcon(cls) {
  // Deselect all
  document.querySelectorAll('[id^="icard_"]').forEach(el => {
    el.style.borderColor = 'var(--border)';
  });
  // Select clicked
  const key = cls.split(' ').map(s => btoa(s)).join('');
  // Update custom input
  document.getElementById('icone_custom').value = cls;
  // Highlight
  const allCards = document.querySelectorAll('[id^="icard_"]');
  allCards.forEach(c => {
    const radio = document.getElementById('ico_' + c.id.replace('icard_',''));
    if (radio && radio.value === cls) {
      c.style.borderColor = 'var(--accent)';
      radio.checked = true;
    }
  });
}
</script>

<?php 
// Load appropriate validation script
$scriptFile = $isEdit ? 'edit_categ.js' : 'add_categ.js';
echo '<script src="views/assets/js/' . $scriptFile . '"></script>';
?>
<script>
<?php if (!empty($error) || !empty($errors)): ?>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof Swal !== 'undefined') {
    Swal.fire({
      icon: 'error',
      title: 'Formulaire invalide',
      text: <?= json_encode(!empty($error) ? $error : reset($errors)) ?>,
      confirmButtonColor: '#f07c22'
    });
  }
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/footer.php'; ?>
