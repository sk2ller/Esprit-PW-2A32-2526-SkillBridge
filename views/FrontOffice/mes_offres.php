<?php
$pageTitle = 'Mes Offres - SkillBridge';
include __DIR__ . '/navbar.php';
$id_client = $_GET['id_client'] ?? 1;
$currentSort = $_GET['sort'] ?? 'recent';
?>

<div class="page-top">
<div class="page-container">

  <div class="dashboard-grid">
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(124,58,237,0.15); color:var(--accent-purple-light);"><i class="fas fa-briefcase"></i></div>
      <div class="stat-value"><?= $stats['total_offres'] ?? 0 ?></div>
      <div class="stat-label">Total Offres</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(16,185,129,0.15); color:var(--accent-green);"><i class="fas fa-check-circle"></i></div>
      <div class="stat-value"><?= $stats['offres_actives'] ?? 0 ?></div>
      <div class="stat-label">Offres Actives</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(245,158,11,0.15); color:var(--accent-orange);"><i class="fas fa-clock"></i></div>
      <div class="stat-value"><?= $stats['en_attente'] ?? 0 ?></div>
      <div class="stat-label">En Attente</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(239,68,68,0.15); color:var(--danger);"><i class="fas fa-ban"></i></div>
      <div class="stat-value"><?= $stats['suspendues'] ?? 0 ?></div>
      <div class="stat-label">Suspendues</div>
    </div>
  </div>

  <div class="section-header">
    <h1 class="section-title">Mes <span>Offres Job</span></h1>
    <div style="display:flex; gap:0.6rem; flex-wrap:wrap;">
      <a href="index.php?page=client_candidatures&id_client=<?= (int) $id_client ?>" class="btn-outline" style="font-size:0.875rem; padding:10px 18px; text-decoration:none; background:rgba(224,112,32,0.14); color:var(--accent-purple-light); border:1px solid rgba(224,112,32,0.45); font-weight:700;">
        <i class="fas fa-file-signature"></i> Candidatures recues
      </a>
      <a href="index.php?page=create_offre&id_client=<?= (int) $id_client ?>" class="btn-primary" style="font-size:0.875rem; padding:10px 20px;">
        <i class="fas fa-plus"></i> Nouvelle Offre
      </a>
    </div>
  </div>

  <form method="GET" style="display:flex; gap:0.75rem; margin-bottom:1.25rem; flex-wrap:wrap;">
    <input type="hidden" name="page" value="mes_offres">
    <input type="hidden" name="id_client" value="<?= (int) $id_client ?>">
    <select name="sort" class="form-control" style="max-width:240px;">
      <option value="recent" <?= $currentSort === 'recent' ? 'selected' : '' ?>>Tri: plus recentes</option>
      <option value="ancien" <?= $currentSort === 'ancien' ? 'selected' : '' ?>>Tri: plus anciennes</option>
      <option value="budget_asc" <?= $currentSort === 'budget_asc' ? 'selected' : '' ?>>Tri: budget croissant</option>
      <option value="budget_desc" <?= $currentSort === 'budget_desc' ? 'selected' : '' ?>>Tri: budget decroissant</option>
    </select>
    <button type="submit" class="btn-primary" style="padding:10px 18px;">
      <i class="fas fa-filter"></i> Trier
    </button>
  </form>

  <?php if (empty($offres)): ?>
  <div class="empty-state">
    <div class="icon">Publier</div>
    <h3>Aucune offre publiee</h3>
    <p>Commencez par publier votre premiere offre job pour trouver des freelancers.</p>
    <a href="index.php?page=create_offre&id_client=<?= (int) $id_client ?>" class="btn-primary" style="display:inline-flex; margin-top:1rem;">
      <i class="fas fa-plus"></i> Publier une offre
    </a>
  </div>
  <?php else: ?>

  <div style="display:grid; gap:1rem;">
    <?php foreach ($offres as $offre): ?>
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:1.5rem; transition:all 0.3s;">
      <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:1rem; gap:1rem;">
        <div>
          <h3 style="font-size:1.1rem; font-weight:700; margin-bottom:0.25rem;">
            <?= htmlspecialchars(substr($offre['titre'], 0, 60)) ?>
          </h3>
          <div style="display:flex; gap:1rem; font-size:0.85rem; color:var(--text-muted); flex-wrap:wrap;">
            <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($offre['created_at'])) ?></span>
            <span><i class="fas fa-money-bill"></i> <?= number_format($offre['budget'], 2) ?> DT</span>
          </div>
        </div>

        <?php
        $statusColors = [
          'actif' => ['bg' => 'rgba(16,185,129,0.15)', 'color' => 'var(--accent-green)', 'text' => 'Actif'],
          'en_attente' => ['bg' => 'rgba(245,158,11,0.15)', 'color' => 'var(--accent-orange)', 'text' => 'En attente'],
          'suspendu' => ['bg' => 'rgba(239,68,68,0.15)', 'color' => 'var(--danger)', 'text' => 'Suspendu']
        ];
        $status = $statusColors[$offre['statut']] ?? $statusColors['en_attente'];
        ?>
        <span style="background:<?= $status['bg'] ?>; color:<?= $status['color'] ?>; padding:6px 12px; border-radius:20px; font-size:0.8rem; font-weight:600;">
          <?= $status['text'] ?>
        </span>
      </div>

      <p style="color:var(--text-muted); margin-bottom:1rem; line-height:1.5;">
        <?= htmlspecialchars(substr($offre['description'], 0, 150)) ?>...
      </p>

      <div style="display:flex; gap:1rem; margin-bottom:1rem; flex-wrap:wrap; font-size:0.85rem;">
        <span style="background:var(--bg-hover); padding:4px 10px; border-radius:6px;">
          <i class="fas fa-user-graduate"></i> <?= ucfirst($offre['niveau_requis']) ?>
        </span>
        <span style="background:var(--bg-hover); padding:4px 10px; border-radius:6px;">
          <i class="fas fa-list"></i> <?= count(array_filter(array_map('trim', explode(',', $offre['competences_requises'])))) ?> competences
        </span>
      </div>

      <div style="display:flex; gap:0.5rem; justify-content:flex-end; flex-wrap:wrap;">
        <a href="index.php?page=offre_detail&id=<?= (int) $offre['id_offre'] ?>" class="btn-outline" style="padding:8px 16px; font-size:0.85rem;">
          <i class="fas fa-eye"></i> Voir
        </a>
        <a href="index.php?page=edit_offre&id=<?= (int) $offre['id_offre'] ?>" class="btn-outline" style="padding:8px 16px; font-size:0.85rem; border:1px solid var(--accent-purple-light); color:var(--accent-purple-light);">
          <i class="fas fa-pen"></i> Modifier
        </a>
        <a href="index.php?page=delete_offre&id=<?= (int) $offre['id_offre'] ?>" class="btn-danger js-confirm-delete-offre" style="padding:8px 16px; font-size:0.85rem; color:var(--danger); border: 1px solid var(--danger);">
          <i class="fas fa-trash"></i> Supprimer
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>

</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const offreMessages = {
    '1': "Offre publiee. Elle est maintenant en attente d'approbation.",
    '2': "Offre modifiee. Elle a ete remise en attente d'approbation.",
    '3': "Offre supprimee avec succes."
  };
  const offreParams = new URLSearchParams(window.location.search);
  if (offreParams.get('success') && window.SkillBridgeAlerts && offreMessages[offreParams.get('success')]) {
    window.SkillBridgeAlerts.toast('success', offreMessages[offreParams.get('success')]);
  }
  window.SkillBridgeAlerts?.bindConfirm('.js-confirm-delete-offre', {
    title: 'Supprimer cette offre ?',
    text: 'Cette action est definitive.',
    icon: 'warning',
    confirmText: 'Oui, supprimer',
    confirmColor: '#b84942'
  });
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
