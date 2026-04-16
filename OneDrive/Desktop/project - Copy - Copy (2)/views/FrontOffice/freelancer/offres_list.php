<?php
$pageTitle = 'Offres Job - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
?>

<div class="page-top">
<div class="page-container">

  <div class="section-header">
    <h1 class="section-title">Offres<span> Job</span></h1>
    <div style="display:flex; gap:1rem;">
      <form method="GET" style="display:flex; gap:0.5rem;">
        <input type="hidden" name="page" value="offres">
        <input type="text" name="search" placeholder="Rechercher une offre..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
               style="padding:10px 16px; border:1px solid var(--border); border-radius:8px; width:300px;">
        <button type="submit" class="btn-primary" style="padding:10px 20px;">
          <i class="fas fa-search"></i> Chercher
        </button>
      </form>
    </div>
  </div>

  <!-- Stats overview -->
  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:2rem;">
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:1.5rem; text-align:center;">
      <div style="font-size:1.8rem; font-weight:800; color:var(--accent-purple-light); margin-bottom:0.5rem;">
        <?= count($offres) ?>
      </div>
      <div style="font-size:0.85rem; color:var(--text-muted);">Offres disponibles</div>
    </div>
  </div>

  <?php if (empty($offres)): ?>
  <div class="empty-state" style="grid-column:1/-1;">
    <div class="icon">📭</div>
    <h3>Aucune offre trouvée</h3>
    <p>Revenez plus tard pour découvrir de nouvelles opportunités passionnantes.</p>
  </div>
  <?php else: ?>

  <!-- Offers Grid -->
  <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:1.5rem;">
    <?php foreach ($offres as $offre): ?>
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:1.5rem; transition:all 0.3s; cursor:pointer; display:flex; flex-direction:column;"
         onmouseover="this.style.borderColor='var(--accent-light)'; this.style.boxShadow='0 8px 24px rgba(124,58,237,0.15)';"
         onmouseout="this.style.borderColor='var(--border)'; this.style.boxShadow='none';">
      
      <!-- Header -->
      <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:1rem;">
        <div>
          <h3 style="font-size:1rem; font-weight:700; margin:0 0 0.25rem 0; line-height:1.4;">
            <?= htmlspecialchars(substr($offre['titre'], 0, 45)) ?>
          </h3>
          <div style="font-size:0.8rem; color:var(--text-muted);">
            <?= htmlspecialchars(substr($offre['nom_client'] ?? 'Client', 0, 30)) ?>
          </div>
        </div>
      </div>

      <!-- Status Badge -->
      <?php 
      $statusColors = [
        'actif' => ['bg' => 'rgba(16,185,129,0.15)', 'color' => 'var(--accent-green)', 'text' => '✓ Actif'],
        'en_attente' => ['bg' => 'rgba(245,158,11,0.15)', 'color' => 'var(--accent-orange)', 'text' => '⏳ En attente'],
        'suspendu' => ['bg' => 'rgba(239,68,68,0.15)', 'color' => 'var(--danger)', 'text' => '⛔ Suspendu']
      ];
      $status = $statusColors[$offre['statut']] ?? $statusColors['en_attente'];
      ?>
      <span style="background:<?= $status['bg'] ?>; color:<?= $status['color'] ?>; padding:4px 8px; border-radius:6px; font-size:0.7rem; font-weight:600; align-self:flex-start; margin-bottom:0.75rem;">
        <?= $status['text'] ?>
      </span>

      <!-- Description preview -->
      <p style="color:var(--text-muted); font-size:0.9rem; margin:0 0 1rem 0; line-height:1.5; flex-grow:1;">
        <?= htmlspecialchars(substr($offre['description'], 0, 120)) ?>...
      </p>

      <!-- Competences quick view -->
      <?php if (!empty($offre['competences_requises'])): ?>
      <div style="margin-bottom:1rem;">
        <?php 
        $comps = array_slice(array_filter(array_map('trim', explode(',', $offre['competences_requises']))), 0, 2);
        foreach ($comps as $comp):
        ?>
        <span style="background:var(--bg-light); color:var(--accent-light); padding:3px 8px; border-radius:4px; font-size:0.7rem; margin-right:4px; display:inline-block;">
          <?= htmlspecialchars($comp) ?>
        </span>
        <?php endforeach; ?>
        <?php if (count(array_filter(array_map('trim', explode(',', $offre['competences_requises'])))) > 2): ?>
        <span style="color:var(--text-muted); font-size:0.7rem;">
          +<?= count(array_filter(array_map('trim', explode(',', $offre['competences_requises'])))) - 2 ?> plus
        </span>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Meta Info -->
      <div style="border-top:1px solid var(--border); padding-top:1rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
          <div>
            <div style="font-size:0.75rem; color:var(--text-muted);">Budget</div>
            <div style="font-size:1.1rem; font-weight:700; color:var(--accent-green);">
              <?= number_format($offre['budget'], 0) ?> DT
            </div>
          </div>
          <div style="text-align:right;">
            <div style="font-size:0.75rem; color:var(--text-muted);">Niveau</div>
            <div style="font-size:0.9rem; font-weight:600; color:var(--accent-light);">
              <?= ucfirst($offre['niveau_requis']) ?>
            </div>
          </div>
        </div>

        <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:var(--text-muted);">
          <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($offre['created_at'])) ?></span>
          <span><i class="fas fa-hourglass-end"></i> <?= $offre['delai_publication'] ?> jours</span>
        </div>
      </div>

      <!-- Action -->
      <div style="margin-top:1rem;">
        <a href="index.php?page=offre_detail&id=<?= $offre['id_offre'] ?>" class="btn-primary" style="width:100%; text-align:center; text-decoration:none; padding:10px; border-radius:8px; display:inline-block;">
          Voir l'offre
        </a>
      </div>

    </div>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>

</div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
