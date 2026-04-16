<?php
$pageTitle = 'Offres Job - Admin SkillBridge';
include __DIR__ . '/../partials/sidebar.php';
?>

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title">Gestion des Offres Job</div>
      <div class="topbar-bread">
        <a href="index.php?page=admin_dashboard">Dashboard</a> › Offres Job
      </div>
    </div>
    <div class="topbar-actions">
      <span style="color:var(--text-muted); font-size:0.875rem;">
        <i class="fas fa-briefcase"></i> <?= $stats['total'] ?? 0 ?> offres total
      </span>
    </div>
  </div>

  <div class="admin-content">

    <?php if (!empty($_GET['success'])): ?>
    <div class="admin-alert admin-alert-success"><i class="fas fa-check-circle"></i> Statut de l'offre mis à jour.</div>
    <?php endif; ?>

    <!-- Stats mini -->
    <div style="display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap;">
      <?php
      $statItems = [
        ['label'=>'Total','val'=>$stats['total']??0,'color'=>'var(--accent-light)','icon'=>'fa-briefcase'],
        ['label'=>'Actifs','val'=>$stats['actif']??0,'color'=>'var(--success)','icon'=>'fa-check-circle'],
        ['label'=>'En attente','val'=>$stats['en_attente']??0,'color'=>'var(--warning)','icon'=>'fa-clock'],
        ['label'=>'Suspendus','val'=>$stats['suspendu']??0,'color'=>'var(--danger)','icon'=>'fa-ban'],
      ];
      foreach ($statItems as $si): ?>
      <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:1rem 1.5rem; display:flex; align-items:center; gap:12px; min-width:160px;">
        <i class="fas <?= $si['icon'] ?>" style="color:<?= $si['color'] ?>; font-size:1.2rem;"></i>
        <div>
          <div style="font-size:1.4rem; font-weight:800; color:<?= $si['color'] ?>;"><?= $si['val'] ?></div>
          <div style="font-size:0.78rem; color:var(--text-muted);"><?= $si['label'] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Filter tabs -->
    <div style="display:flex; gap:4px; margin-bottom:1.5rem; background:var(--bg-card); padding:4px; border-radius:10px; width:fit-content; border:1px solid var(--border);">
      <?php
      $filterStatut = $_GET['filter'] ?? 'all';
      $filters = ['all'=>'Toutes','en_attente'=>'En attente','actif'=>'Actives','suspendu'=>'Suspendues'];
      foreach ($filters as $fval => $flabel): ?>
      <a href="index.php?page=admin_offres&filter=<?= $fval ?>"
         style="padding:7px 16px; border-radius:7px; font-size:0.82rem; font-weight:600; text-decoration:none; transition:all 0.2s; color:<?= $filterStatut === $fval ? 'white' : 'var(--text-muted)' ?>; background:<?= $filterStatut === $fval ? 'var(--accent)' : 'transparent' ?>;">
        <?= $flabel ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Search -->
    <div style="margin-bottom:1.5rem;">
      <form method="GET" style="display:flex; gap:0.5rem;">
        <input type="hidden" name="page" value="admin_offres">
        <input type="hidden" name="filter" value="<?= $filterStatut ?>">
        <input type="text" name="search" placeholder="Chercher par titre ou client..." 
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
               style="flex:1; padding:10px 16px; border:1px solid var(--border); border-radius:8px;">
        <button type="submit" style="padding:10px 20px; background:var(--accent); color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600;">
          <i class="fas fa-search"></i> Chercher
        </button>
      </form>
    </div>

    <div class="admin-table-wrap">
      <div class="admin-table-header">
        <div class="admin-table-title">Liste des Offres Job</div>
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Offre</th>
            <th>Client</th>
            <th>Budget</th>
            <th>Niveau</th>
            <th>Statut</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $filtered = $offres;
          if ($filterStatut !== 'all') {
              $filtered = array_filter($offres, fn($o) => $o['statut'] === $filterStatut);
          }
          if (empty($filtered)): ?>
          <tr><td colspan="8" style="text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:0.5rem;"></i>
            Aucune offre dans cette catégorie
          </td></tr>
          <?php else: ?>
          <?php foreach ($filtered as $o): ?>
          <tr>
            <td style="color:var(--text-muted); font-size:0.8rem;">#<?= $o['id_offre'] ?></td>
            <td>
              <div class="table-service-name"><?= htmlspecialchars(substr($o['titre'], 0, 40)) ?>...</div>
              <div class="table-service-meta"><?= htmlspecialchars(substr($o['description'], 0, 50)) ?>...</div>
            </td>
            <td>
              <div style="font-weight:600;"><?= htmlspecialchars($o['nom_client'] ?? 'Client') ?></div>
              <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($o['email_client'] ?? '') ?></div>
            </td>
            <td style="font-weight:700; color:var(--success);"><?= number_format($o['budget'], 2) ?> DT</td>
            <td>
              <span style="background:var(--bg-light); padding:3px 10px; border-radius:20px; font-size:0.75rem;">
                <?= ucfirst($o['niveau_requis']) ?>
              </span>
            </td>
            <td>
              <?php 
              $statusClass = match($o['statut']) {
                'actif' => 'status-active',
                'en_attente' => 'status-pending',
                'suspendu' => 'status-suspended',
                default => 'status-pending'
              };
              $statusIcon = match($o['statut']) {
                'actif' => 'check-circle',
                'en_attente' => 'hourglass-half',
                'suspendu' => 'ban',
                default => 'hourglass-half'
              };
              $statusText = match($o['statut']) {
                'actif' => 'Actif',
                'en_attente' => 'En attente',
                'suspendu' => 'Suspendu',
                default => 'En attente'
              };
              ?>
              <span class="<?= $statusClass ?>" style="padding:5px 12px; border-radius:20px; font-size:0.75rem; font-weight:600;">
                <i class="fas fa-<?= $statusIcon ?>"></i> <?= $statusText ?>
              </span>
            </td>
            <td style="color:var(--text-muted); font-size:0.8rem;">
              <?= date('d/m/Y', strtotime($o['created_at'])) ?>
            </td>
            <td>
              <div style="display:flex; gap:6px;">
                <!-- View -->
                <a href="index.php?page=offre_detail&id=<?= $o['id_offre'] ?>" 
                   style="padding:6px 10px; background:var(--accent-glow); color:var(--accent-light); border:none; border-radius:6px; cursor:pointer; font-size:0.75rem; text-decoration:none; display:inline-flex; align-items:center; gap:4px; transition:all 0.2s;"
                   onmouseover="this.style.background='var(--accent-light)'; this.style.color='white';"
                   onmouseout="this.style.background='var(--accent-glow)'; this.style.color='var(--accent-light)';">
                  <i class="fas fa-eye"></i> Voir
                </a>

                <!-- Approve -->
                <?php if ($o['statut'] !== 'actif'): ?>
                <a href="index.php?page=admin_offre_statut&id=<?= $o['id_offre'] ?>&statut=actif" 
                   style="padding:6px 10px; background:rgba(16,185,129,0.15); color:var(--accent-green); border:none; border-radius:6px; cursor:pointer; font-size:0.75rem; text-decoration:none; display:inline-flex; align-items:center; gap:4px; transition:all 0.2s;"
                   onmouseover="this.style.background='var(--accent-green)'; this.style.color='white';"
                   onmouseout="this.style.background='rgba(16,185,129,0.15)'; this.style.color='var(--accent-green)';">
                  <i class="fas fa-check"></i> Approuver
                </a>
                <?php endif; ?>

                <!-- Suspend -->
                <?php if ($o['statut'] !== 'suspendu'): ?>
                <a href="index.php?page=admin_offre_statut&id=<?= $o['id_offre'] ?>&statut=suspendu" 
                   style="padding:6px 10px; background:rgba(239,68,68,0.15); color:var(--danger); border:none; border-radius:6px; cursor:pointer; font-size:0.75rem; text-decoration:none; display:inline-flex; align-items:center; gap:4px; transition:all 0.2s;"
                   onmouseover="this.style.background='var(--danger)'; this.style.color='white';"
                   onmouseout="this.style.background='rgba(239,68,68,0.15)'; this.style.color='var(--danger)';"
                   onclick="return confirm('Suspendre cette offre ?');">
                  <i class="fas fa-ban"></i> Suspendre
                </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
