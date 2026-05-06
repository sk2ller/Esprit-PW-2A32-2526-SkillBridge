<?php
$pageTitle = 'Offres Job - Admin SkillBridge';
include __DIR__ . '/sidebar.php';
$filterStatut = $_GET['filter'] ?? 'all';
$sort = $_GET['sort'] ?? 'recent';
$search = $_GET['search'] ?? '';
?>

<style>
.admin-content {
  max-width: 1480px;
}

.admin-content .stats-grid {
  gap: 0.9rem;
  margin-bottom: 1.1rem;
}

.admin-content .stat-widget {
  min-height: 118px;
  padding: 1rem 1.1rem;
  border-radius: 18px;
}

.admin-analytics-grid {
  grid-template-columns: 1fr !important;
  gap: 1rem !important;
}

.admin-table-wrap {
  border-radius: 18px !important;
  overflow: hidden;
}

.admin-table-header {
  padding: 1.05rem 1.35rem !important;
  min-height: auto !important;
}

.admin-table-title {
  font-size: 1.18rem !important;
}

.analytics-panel {
  padding: 1rem 1.35rem !important;
  display: grid;
  gap: 0.8rem;
}

.admin-filter-row {
  gap: 0.5rem !important;
  margin: 0 !important;
}

.filter-pill {
  padding: 0.55rem 0.95rem !important;
  font-size: 0.86rem !important;
  border-radius: 999px !important;
}

.admin-search-form {
  display: grid !important;
  grid-template-columns: 220px minmax(260px, 1fr) auto !important;
  gap: 0.75rem !important;
  align-items: center;
  margin: 0 !important;
}

.admin-search-form .form-control,
.admin-search-form .topbar-btn {
  min-height: 46px !important;
}

.admin-table-scroll {
  overflow-x: visible !important;
}

.offres-table {
  width: 100%;
  table-layout: fixed;
}

.offres-table colgroup {
  display: none;
}

.offres-table th,
.offres-table td {
  padding: 0.95rem 0.8rem !important;
  vertical-align: middle !important;
  font-size: 0.9rem;
}

.offres-table th {
  font-size: 0.72rem !important;
  letter-spacing: 0.05em;
  color: #8b8791 !important;
}

.offres-table td:nth-child(1) { width: 58px; }
.offres-table td:nth-child(2) { width: 32%; }
.offres-table td:nth-child(3) { width: 17%; }
.offres-table td:nth-child(4) { width: 100px; }
.offres-table td:nth-child(5) { width: 120px; }
.offres-table td:nth-child(6) { width: 110px; }
.offres-table td:nth-child(7) { width: 92px; }
.offres-table td:nth-child(8) { width: 190px; }

.table-service-name {
  line-height: 1.35;
}

.table-service-meta {
  max-width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-top: 0.28rem;
}

.offres-table td:nth-child(3) .table-service-meta {
  display: none;
}

.badge {
  white-space: nowrap;
}

.action-stack {
  display: flex !important;
  flex-wrap: wrap;
  gap: 0.42rem !important;
}

.action-stack .admin-btn {
  min-height: 32px;
  padding: 0.42rem 0.62rem !important;
  border-radius: 9px !important;
  font-size: 0.76rem !important;
}

.analytics-side-list {
  padding: 1rem 1.25rem 1.25rem !important;
}

.analytics-toggle-bar {
  max-width: 360px;
  margin-bottom: 0.9rem !important;
}

.chart-card.compact {
  max-width: 520px;
  padding: 1.15rem !important;
}

.chart-card.compact canvas {
  max-height: 260px !important;
}

.admin-panel-view[data-admin-panel-view="table"] {
  max-width: 640px;
}

@media (max-width: 980px) {
  .admin-search-form {
    grid-template-columns: 1fr !important;
  }

  .admin-table-scroll {
    overflow-x: auto !important;
  }
}
</style>

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title">Gestion des Offres Job</div>
      <div class="topbar-bread">
        <a href="index.php?page=admin_dashboard">Dashboard</a> > Offres Job
      </div>
    </div>
    <div class="topbar-actions">
      <a href="index.php?page=admin_offres_export_pdf&filter=<?= urlencode($filterStatut) ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>" class="topbar-btn topbar-btn-outline">
        <i class="fas fa-file-pdf"></i> Export PDF
      </a>
      <span style="color:var(--text-muted); font-size:0.875rem;">
        <i class="fas fa-briefcase"></i> <?= $stats['total'] ?? 0 ?> offres total
      </span>
    </div>
  </div>

  <div class="admin-content">
    <div class="stats-grid">
      <?php
      $statItems = [
        ['label' => 'Total', 'val' => $stats['total'] ?? 0, 'icon' => 'fa-briefcase'],
        ['label' => 'Actifs', 'val' => $stats['actif'] ?? 0, 'icon' => 'fa-check-circle'],
        ['label' => 'En attente', 'val' => $stats['en_attente'] ?? 0, 'icon' => 'fa-clock'],
        ['label' => 'Suspendus', 'val' => $stats['suspendu'] ?? 0, 'icon' => 'fa-ban'],
      ];
      foreach ($statItems as $si): ?>
      <div class="stat-widget purple">
        <div class="sw-icon"><i class="fas <?= $si['icon'] ?>"></i></div>
        <div class="sw-value"><?= $si['val'] ?></div>
        <div class="sw-label"><?= $si['label'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="admin-analytics-grid">
      <section class="admin-table-wrap">
        <div class="admin-table-header">
          <div class="admin-table-title">Gestion des offres</div>
        </div>
        <div class="analytics-panel">
          <div class="admin-filter-row">
            <?php $filters = ['all' => 'Toutes', 'en_attente' => 'En attente', 'actif' => 'Actives', 'suspendu' => 'Suspendues']; ?>
            <?php foreach ($filters as $fval => $flabel): ?>
            <a href="index.php?page=admin_offres&filter=<?= $fval ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>"
               class="filter-pill <?= $filterStatut === $fval ? 'active' : '' ?>"><?= $flabel ?></a>
            <?php endforeach; ?>
          </div>

          <form method="GET" class="admin-search-form">
            <input type="hidden" name="page" value="admin_offres">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filterStatut) ?>">
            <select name="sort" class="form-control">
              <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Tri: Plus recent</option>
              <option value="ancien" <?= $sort === 'ancien' ? 'selected' : '' ?>>Tri: Plus ancien</option>
              <option value="budget_asc" <?= $sort === 'budget_asc' ? 'selected' : '' ?>>Prix: croissant</option>
              <option value="budget_desc" <?= $sort === 'budget_desc' ? 'selected' : '' ?>>Prix: decroissant</option>
            </select>
            <input type="text" name="search" class="form-control" placeholder="Chercher par titre ou client..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="topbar-btn topbar-btn-primary"><i class="fas fa-search"></i> Chercher</button>
          </form>
        </div>

        <div class="admin-table-scroll">
        <table class="admin-table offres-table">
          <colgroup>
            <col style="width: 6%;">
            <col style="width: 26%;">
            <col style="width: 19%;">
            <col style="width: 11%;">
            <col style="width: 12%;">
            <col style="width: 10%;">
            <col style="width: 8%;">
            <col style="width: 16%;">
          </colgroup>
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
            <?php if (empty($offres)): ?>
            <tr><td colspan="8" style="text-align:center; padding:3rem; color:var(--text-muted);">Aucune offre trouvee</td></tr>
            <?php else: ?>
            <?php foreach ($offres as $o): ?>
            <tr>
              <td>#<?= (int) $o['id_offre'] ?></td>
              <td>
                <div class="table-service-name"><?= htmlspecialchars(substr($o['titre'], 0, 46)) ?></div>
                <div class="table-service-meta"><?= htmlspecialchars(substr($o['description'], 0, 42)) ?>...</div>
              </td>
              <td>
                <div class="table-service-name"><?= htmlspecialchars($o['nom_client'] ?? 'Client') ?></div>
                <div class="table-service-meta"><?= htmlspecialchars($o['email_client'] ?? '') ?></div>
              </td>
              <td><?= number_format((float) $o['budget'], 2) ?> DT</td>
              <td><span class="badge badge-info"><?= ucfirst($o['niveau_requis']) ?></span></td>
              <td>
                <?php
                $statusClass = match($o['statut']) {
                  'actif' => 'badge-actif',
                  'suspendu' => 'badge-suspendu',
                  default => 'badge-pending'
                };
                ?>
                <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($o['statut']) ?></span>
              </td>
              <td><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
              <td>
                <div class="action-stack">
                  <a href="index.php?page=offre_detail&id=<?= (int) $o['id_offre'] ?>" class="admin-btn admin-btn-outline admin-btn-sm" title="Voir">
                    <i class="fas fa-eye"></i> Voir
                  </a>
                  <?php if ($o['statut'] !== 'actif'): ?>
                  <button type="button"
                          class="admin-btn admin-btn-sm js-moderer-ia"
                          style="background:linear-gradient(135deg,#b84942,#e06b40);color:#fff;border:none;display:flex;align-items:center;gap:.3rem;"
                          data-id="<?= (int) $o['id_offre'] ?>"
                          data-title="<?= htmlspecialchars($o['titre'], ENT_QUOTES) ?>"
                          data-href="index.php?page=admin_offre_statut&id=<?= (int) $o['id_offre'] ?>&statut=actif">
                    <i class="fas fa-shield-halved" style="font-size:.65rem;"></i> IA
                  </button>
                  <?php endif; ?>
                  <?php if ($o['statut'] !== 'suspendu'): ?>
                  <a href="index.php?page=admin_offre_statut&id=<?= (int) $o['id_offre'] ?>&statut=suspendu" class="admin-btn admin-btn-danger admin-btn-sm js-confirm-admin-action" title="Suspendre" data-confirm-title="Suspendre cette offre ?" data-confirm-text="Elle ne sera plus visible tant qu'elle reste suspendue.">
                    <i class="fas fa-ban"></i> Stop
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

        <?php include __DIR__ . '/../partials/pagination.php'; ?>
      </section>

      <aside class="admin-table-wrap">
        <div class="admin-table-header">
          <div class="admin-table-title">Vue metier</div>
        </div>
        <div class="analytics-side-list">
          <div class="analytics-toggle-bar">
            <button type="button" class="analytics-toggle-btn active" data-admin-panel="graph">Graphique</button>
            <button type="button" class="analytics-toggle-btn" data-admin-panel="table">Tableau</button>
          </div>
          <div class="chart-card compact admin-panel-view active" data-admin-panel-view="graph">
            <h3>Offres par statut</h3>
            <canvas id="offersStatusChart" height="220"></canvas>
          </div>
          <div class="admin-panel-view" data-admin-panel-view="table">
            <table class="admin-table admin-table-compact">
              <thead>
                <tr>
                  <th>Niveau</th>
                  <th>Nb</th>
                  <th>Budget moyen</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($insights['by_level'])): ?>
                <tr><td colspan="3" style="text-align:center;">Aucune statistique</td></tr>
                <?php else: ?>
                <?php foreach ($insights['by_level'] as $row): ?>
                <tr>
                  <td><?= ucfirst($row['niveau_requis']) ?></td>
                  <td><?= (int) $row['total'] ?></td>
                  <td><?= number_format((float) $row['avg_budget'], 2) ?> DT</td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </aside>
    </div>
  </div>
</main>

<script>
const adminOfferParams = new URLSearchParams(window.location.search);
if (adminOfferParams.get('success') && window.SkillBridgeAlerts) {
  window.SkillBridgeAlerts.toast('success', "Le statut de l'offre a ete mis a jour.");
}
if (adminOfferParams.get('error') && window.SkillBridgeAlerts) {
  window.SkillBridgeAlerts.dialog('error', 'Action invalide', 'Le statut demande est invalide.');
}
// ── Modération IA — auto-fetch par ID ────────────────────────────────────────
let _modActivateHref = null;

function closeModModal() {
  document.getElementById('modOverlay').classList.remove('open');
  _modActivateHref = null;
}
function doActivate() {
  if (_modActivateHref) window.location.href = _modActivateHref;
}
function _esc(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }

function modLoadingHtml() {
  return `<div style="text-align:center;padding:3rem 1.5rem;">
    <div style="width:50px;height:50px;border:4px solid #fce8e8;border-top-color:#b84942;
                border-radius:50%;animation:modSpin .7s linear infinite;margin:0 auto 1.2rem;"></div>
    <p style="color:#777;font-size:.92rem;font-weight:500;">Gemini analyse le contenu de l'offre…</p>
    <p style="color:#bbb;font-size:.78rem;margin-top:.3rem;">Quelques secondes suffisent.</p>
  </div>
  <style>@keyframes modSpin{to{transform:rotate(360deg)}}</style>`;
}

function modBuildHtml(data) {
  const approved = data.is_approved;
  const sev = data.severity || 'none';
  const sevCfg = {
    none:   { label:'Aucun',  color:'#059669', bg:'rgba(5,150,105,.1)'  },
    low:    { label:'Faible', color:'#d97706', bg:'rgba(217,119,6,.1)'  },
    medium: { label:'Moyen',  color:'#d97706', bg:'rgba(217,119,6,.1)'  },
    high:   { label:'Élevé',  color:'#dc2626', bg:'rgba(220,38,38,.1)'  },
  };
  const sc = sevCfg[sev] || sevCfg.none;

  const verdictBox = approved
    ? `<div style="text-align:center;padding:1.5rem;background:rgba(5,150,105,.07);
                   border-radius:14px;border:1.5px solid rgba(5,150,105,.22);margin-bottom:1.25rem;">
        <div style="font-size:2.8rem;margin-bottom:.35rem;">✅</div>
        <div style="font-size:1.2rem;font-weight:800;color:#059669;">Offre Conforme</div>
        <div style="font-size:.84rem;color:#666;margin-top:.3rem;">Cette offre respecte les standards de la plateforme.</div>
      </div>`
    : `<div style="text-align:center;padding:1.5rem;background:rgba(220,38,38,.07);
                   border-radius:14px;border:1.5px solid rgba(220,38,38,.22);margin-bottom:1.25rem;">
        <div style="font-size:2.8rem;margin-bottom:.35rem;">❌</div>
        <div style="font-size:1.2rem;font-weight:800;color:#dc2626;">Offre Non Conforme</div>
        <div style="font-size:.84rem;color:#666;margin-top:.3rem;">Des problèmes ont été détectés.</div>
      </div>`;

  let details = `<div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.1rem;">
    <span style="font-size:.72rem;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:.06em;">Sévérité :</span>
    <span style="padding:.22rem .7rem;border-radius:999px;font-size:.76rem;font-weight:700;background:${sc.bg};color:${sc.color};">${sc.label}</span>
  </div>`;

  if (data.flagged_words && data.flagged_words.length) {
    const badges = data.flagged_words.map(w =>
      `<span style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.25);color:#dc2626;
                    padding:.2rem .5rem;border-radius:6px;font-size:.76rem;font-weight:700;
                    display:inline-block;margin:.15rem;">${_esc(w)}</span>`).join(' ');
    details += `<div style="margin-bottom:1.1rem;">
      <div style="font-size:.71rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#dc2626;margin-bottom:.45rem;">
        <i class="fas fa-flag"></i> Éléments signalés
      </div>
      <div style="line-height:2;">${badges}</div>
    </div>`;
  }

  if (data.reason) {
    details += `<div style="padding:.9rem 1rem;background:#fff8f8;border-left:3px solid #dc2626;
                             border-radius:0 8px 8px 0;margin-bottom:1rem;">
      <div style="font-size:.71rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#dc2626;margin-bottom:.35rem;">
        <i class="fas fa-circle-info"></i> Raison
      </div>
      <p style="font-size:.86rem;color:#444;line-height:1.6;margin:0;">${_esc(data.reason)}</p>
    </div>`;
  }

  if (data.suggestion) {
    details += `<div style="padding:.9rem 1rem;background:#f8fff8;border-left:3px solid #059669;border-radius:0 8px 8px 0;">
      <div style="font-size:.71rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#059669;margin-bottom:.35rem;">
        <i class="fas fa-lightbulb"></i> Suggestion
      </div>
      <p style="font-size:.86rem;color:#444;line-height:1.6;margin:0;">${_esc(data.suggestion)}</p>
    </div>`;
  }

  return verdictBox + details;
}

function modUnavailableHtml(data) {
  const configured = data && data.gemini_key_configured;
  const title = configured ? 'API Gemini indisponible' : 'Clé API Gemini manquante';
  const help = configured
    ? `<p style="font-size:.88rem;color:#555;line-height:1.7;margin:0;">La clé Gemini est configurée, mais l'appel API n'a pas répondu. Vérifiez Internet, les restrictions de la clé ou le quota Google AI Studio.</p>`
    : `<ol style="font-size:.85rem;color:#555;line-height:1.9;margin:0;padding-left:1.1rem;">
      <li>Allez sur <a href="https://aistudio.google.com/apikey" target="_blank" style="color:#d97706;font-weight:600;">aistudio.google.com/apikey</a> <span style="color:#999">(gratuit)</span></li>
      <li>Créez une clé et copiez-la</li>
      <li>Dans <code>config.php</code>, remplacez <code>VOTRE_CLE_GEMINI_ICI</code></li>
    </ol>`;

  return `<div style="background:rgba(217,119,6,.07);border:1px solid rgba(217,119,6,.3);border-radius:12px;padding:1.25rem;">
    <div style="font-weight:700;color:#92400e;margin-bottom:.6rem;"><i class="fas fa-key"></i> ${title}</div>
    ${help}
  </div>`;
}

document.querySelectorAll('.js-moderer-ia').forEach(function(btn) {
  btn.addEventListener('click', async function() {
    const id    = this.dataset.id;
    const title = this.dataset.title;
    const href  = this.dataset.href;
    _modActivateHref = href;

    document.getElementById('modTitle').textContent = title;
    document.getElementById('modBody').innerHTML    = modLoadingHtml();
    document.getElementById('modActivateBtn').style.display = 'none';
    document.getElementById('modOverlay').classList.add('open');

    try {
      const fd = new FormData();
      fd.append('id_offre', id);
      const res  = await fetch('index.php?page=moderation_by_id_api', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.gemini_unavailable) {
        document.getElementById('modBody').innerHTML = modUnavailableHtml(data);
        const ab = document.getElementById('modActivateBtn');
        ab.style.cssText += 'background:linear-gradient(135deg,#d97706,#f59e0b);display:flex;';
        ab.innerHTML = '<i class="fas fa-exclamation-triangle"></i>&nbsp; Activer sans vérification';
        return;
      }
      if (data.error) {
        document.getElementById('modBody').innerHTML = `<div style="color:#dc2626;padding:1rem;border:1px solid rgba(220,38,38,.25);border-radius:10px;">${_esc(data.error)}</div>`;
        return;
      }

      document.getElementById('modBody').innerHTML = modBuildHtml(data);
      const ab = document.getElementById('modActivateBtn');
      ab.style.display = 'flex';
      if (!data.is_approved && data.severity === 'high') {
        ab.style.background = 'linear-gradient(135deg,#d97706,#f59e0b)';
        ab.innerHTML = '<i class="fas fa-exclamation-triangle"></i>&nbsp; Activer quand même';
      } else {
        ab.style.background = 'linear-gradient(135deg,#059669,#34d399)';
        ab.innerHTML = '<i class="fas fa-check"></i>&nbsp; Activer l\'offre';
      }
    } catch(e) {
      document.getElementById('modBody').innerHTML = `<div style="color:#dc2626;">Erreur : ${_esc(e.message)}</div>`;
    }
  });
});

window.SkillBridgeAlerts?.bindConfirm('.js-confirm-admin-action', {
  title: 'Confirmer cette action',
  text: "Le statut de l'offre va etre modifie.",
  icon: 'warning',
  confirmText: 'Confirmer'
});
document.querySelectorAll('.js-confirm-admin-action').forEach((link) => {
  link.addEventListener('click', function (event) {
    event.preventDefault();
    if (!window.Swal) {
      window.location.href = this.href;
      return;
    }
    Swal.fire({
      title: this.dataset.confirmTitle || 'Confirmer cette action ?',
      text: this.dataset.confirmText || '',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#e07020',
      cancelButtonColor: '#8b8791',
      confirmButtonText: 'Confirmer',
      cancelButtonText: 'Annuler',
      background: '#fffaf4',
      color: '#1f1f23'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = this.href;
      }
    });
  });
});
document.addEventListener('DOMContentLoaded', function () {
  if (window.Chart) {
    new Chart(document.getElementById('offersStatusChart'), {
      type: 'doughnut',
      data: {
        labels: ['Actif', 'En attente', 'Suspendu'],
        datasets: [{
          data: [<?= (int) ($stats['actif'] ?? 0) ?>, <?= (int) ($stats['en_attente'] ?? 0) ?>, <?= (int) ($stats['suspendu'] ?? 0) ?>],
          backgroundColor: ['#2f7d57', '#e07020', '#b84942'],
          borderWidth: 0
        }]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  }

  document.querySelectorAll('.analytics-toggle-btn').forEach((button) => {
    button.addEventListener('click', function () {
      const target = this.dataset.adminPanel;
      document.querySelectorAll('.analytics-toggle-btn').forEach((btn) => btn.classList.toggle('active', btn === this));
      document.querySelectorAll('.admin-panel-view').forEach((panel) => {
        panel.classList.toggle('active', panel.dataset.adminPanelView === target);
      });
    });
  });
});
</script>

<!-- ══════════════════════════════════════════════════════════
     MODÉRATION IA MODAL
══════════════════════════════════════════════════════════ -->
<style>
.mod-overlay {
  position:fixed;inset:0;
  background:rgba(10,5,20,.72);
  backdrop-filter:blur(7px);
  z-index:1050;
  display:flex;align-items:center;justify-content:center;
  padding:1.5rem;
  opacity:0;pointer-events:none;
  transition:opacity .25s;
}
.mod-overlay.open { opacity:1;pointer-events:all; }
.mod-card {
  background:#fff;
  border-radius:20px;
  width:100%;max-width:560px;
  box-shadow:0 32px 100px rgba(0,0,0,.35);
  overflow:hidden;
  transform:scale(.93) translateY(14px);
  transition:transform .32s cubic-bezier(.34,1.56,.64,1);
}
.mod-overlay.open .mod-card { transform:scale(1) translateY(0); }
</style>

<div id="modOverlay" class="mod-overlay" onclick="if(event.target===this)closeModModal()">
  <div class="mod-card">

    <!-- Header -->
    <div style="padding:1.25rem 1.5rem;background:linear-gradient(135deg,#1a0812,#2e0d1e);
                display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;">
      <div>
        <div style="font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;
                    color:rgba(255,255,255,.45);margin-bottom:.3rem;">
          <i class="fas fa-shield-halved" style="color:#e06060;"></i>&nbsp; Modération IA — Analyse automatique
        </div>
        <div id="modTitle" style="font-family:'Playfair Display',serif;font-size:1rem;
                                   font-weight:700;color:#fff;line-height:1.35;max-width:400px;"></div>
      </div>
      <button onclick="closeModModal()"
              style="background:rgba(255,255,255,.1);border:none;color:#fff;
                     width:34px;height:34px;border-radius:50%;cursor:pointer;font-size:.95rem;
                     display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="fas fa-xmark"></i>
      </button>
    </div>

    <!-- Body -->
    <div id="modBody" style="padding:1.5rem;max-height:58vh;overflow-y:auto;"></div>

    <!-- Footer -->
    <div style="padding:1rem 1.5rem;border-top:1px solid #f3f3f3;background:#fafafa;
                display:flex;gap:.75rem;align-items:center;">
      <button id="modActivateBtn" onclick="doActivate()"
              style="display:none;flex:1;padding:.75rem 1rem;border-radius:11px;
                     font-size:.88rem;font-weight:700;cursor:pointer;border:none;
                     color:#fff;align-items:center;justify-content:center;gap:.4rem;">
      </button>
      <button onclick="closeModModal()"
              style="flex:1;padding:.75rem 1rem;border-radius:11px;font-size:.88rem;
                     font-weight:700;cursor:pointer;border:1.5px solid #e0e0e0;
                     background:#fff;color:#666;">
        <i class="fas fa-times"></i>&nbsp; Fermer
      </button>
    </div>

  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
