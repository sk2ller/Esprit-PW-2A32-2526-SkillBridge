<?php
$pageTitle = 'Candidatures - Admin SkillBridge';
include __DIR__ . '/sidebar.php';
$filterStatut = $_GET['filter'] ?? 'all';
$sort = $_GET['sort'] ?? 'recent';
$search = $_GET['search'] ?? '';
?>

<style>
.admin-content {
  max-width: 1480px;
}
.stats-grid {
  grid-template-columns: repeat(4, minmax(150px, 1fr));
  gap: .85rem;
  margin-bottom: 1rem;
}
.stat-widget {
  min-height: 116px;
  padding: 1rem;
  border-radius: 18px;
}
.stat-widget .sw-icon {
  width: 38px;
  height: 38px;
  font-size: .95rem;
}
.stat-widget .sw-value {
  font-size: 1.55rem;
  margin-top: .45rem;
}
.stat-widget .sw-label {
  font-size: .78rem;
}
.admin-analytics-grid {
  grid-template-columns: 1fr !important;
  gap: 1rem !important;
  align-items: start;
}
.admin-table-wrap {
  border-radius: 18px;
  overflow: hidden;
}
.admin-table-header {
  padding: 1.1rem 1.35rem;
}
.admin-table-title {
  font-size: 1.15rem;
}
.analytics-panel {
  padding: 1.15rem 1.35rem;
}
.admin-filter-row {
  gap: .55rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}
.filter-pill {
  min-height: 40px;
  padding: .65rem 1rem;
  border-radius: 999px;
  font-size: .86rem;
}
.admin-search-form {
  display: grid;
  grid-template-columns: 220px minmax(260px, 1fr) auto;
  gap: .65rem;
  align-items: center;
}
.admin-search-form .form-control,
.admin-search-form .topbar-btn {
  min-height: 44px;
  border-radius: 12px;
}
.admin-table-scroll {
  overflow-x: auto !important;
}
.candidatures-table {
  table-layout: fixed;
  width: 100%;
  min-width: 1160px;
  border-collapse: separate;
  border-spacing: 0;
}
.candidatures-table colgroup {
  display: table-column-group;
}
.candidatures-table th {
  padding: .85rem .9rem;
  font-size: .72rem;
  letter-spacing: .05em;
  white-space: normal;
  vertical-align: middle;
}
.candidatures-table td {
  padding: 1rem .9rem;
  vertical-align: middle;
  font-size: .9rem;
}
.candidatures-table tbody tr {
  transition: background .18s ease, box-shadow .18s ease;
}
.candidatures-table tbody tr:hover {
  background: rgba(249, 115, 22, .035);
}
.candidatures-table th:nth-child(1),
.candidatures-table td:nth-child(1),
.candidatures-table th:nth-child(4),
.candidatures-table td:nth-child(4),
.candidatures-table th:nth-child(6),
.candidatures-table td:nth-child(6),
.candidatures-table th:nth-child(7),
.candidatures-table td:nth-child(7),
.candidatures-table th:nth-child(8),
.candidatures-table td:nth-child(8),
.candidatures-table th:nth-child(9),
.candidatures-table td:nth-child(9) {
  text-align: center;
}
.table-service-name {
  font-size: .94rem;
  line-height: 1.35;
  font-weight: 800;
  color: var(--text-main);
}
.candidatures-table .table-service-meta {
  display: none;
}
.table-offer-title {
  max-width: 100%;
  color: var(--text-main);
  font-size: .92rem;
  font-weight: 700;
  line-height: 1.45;
}
.table-price-cell {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 96px;
  color: var(--text-main);
  font-weight: 800;
  white-space: nowrap;
}
.table-message-cell {
  max-width: 100%;
  color: var(--text-muted);
  font-size: .83rem;
  line-height: 1.45;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.table-doc-links {
  display: flex;
  flex-wrap: wrap;
  gap: .35rem;
  justify-content: center;
}
.table-doc-links a {
  display: inline-flex;
  align-items: center;
  min-height: 28px;
  padding: .32rem .55rem;
  border-radius: 999px;
  background: rgba(124, 58, 237, .08);
  color: #6d28d9;
  font-weight: 700;
  font-size: .74rem;
  text-decoration: none;
}
.candidatures-table .badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 104px;
  padding: .38rem .65rem;
  font-size: .72rem;
  white-space: nowrap;
}
.table-date-cell {
  color: var(--text-main);
  font-weight: 700;
  white-space: nowrap;
}
.candidatures-table .js-score-btn {
  min-width: 136px;
  min-height: 34px;
  justify-content: center;
  border-radius: 11px;
  box-shadow: 0 10px 20px rgba(124, 58, 237, .18);
  margin: 0 auto;
}
.analytics-side-list {
  padding: 1rem 1.35rem 1.35rem;
}
.analytics-toggle-bar {
  max-width: 520px;
}
.chart-card.compact {
  max-width: 520px;
  padding: 1rem;
}
.chart-card.compact canvas {
  max-height: 260px;
}
.admin-table-compact {
  max-width: 680px;
}
@media (max-width: 1000px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(140px, 1fr));
  }
  .admin-search-form {
    grid-template-columns: 1fr;
  }
  .admin-table-scroll {
    overflow-x: auto !important;
  }
  .candidatures-table {
    min-width: 980px;
  }
}
</style>

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title">Gestion des Candidatures</div>
      <div class="topbar-bread">
        <a href="index.php?page=admin_dashboard">Dashboard</a> > Candidatures
      </div>
    </div>
    <div class="topbar-actions">
      <a href="index.php?page=admin_candidatures_export_pdf&filter=<?= urlencode($filterStatut) ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>" class="topbar-btn topbar-btn-outline">
        <i class="fas fa-file-pdf"></i> Export PDF
      </a>
      <span style="color:var(--text-muted); font-size:0.875rem;">
        <i class="fas fa-file-signature"></i> <?= $stats['total'] ?? 0 ?> candidatures total
      </span>
    </div>
  </div>

  <div class="admin-content">
    <div class="stats-grid">
      <?php
      $statItems = [
        ['label' => 'Total', 'val' => $stats['total'] ?? 0, 'icon' => 'fa-file-signature'],
        ['label' => 'En attente', 'val' => $stats['en_attente'] ?? 0, 'icon' => 'fa-clock'],
        ['label' => 'Acceptees', 'val' => $stats['acceptee'] ?? 0, 'icon' => 'fa-check-circle'],
        ['label' => 'Refusees', 'val' => $stats['refusee'] ?? 0, 'icon' => 'fa-times-circle'],
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
          <div class="admin-table-title">Gestion des candidatures</div>
        </div>
        <div class="analytics-panel">
          <div class="admin-filter-row">
            <?php $filters = ['all' => 'Toutes', 'en_attente' => 'En attente', 'acceptee' => 'Acceptees', 'refusee' => 'Refusees']; ?>
            <?php foreach ($filters as $fval => $flabel): ?>
            <a href="index.php?page=admin_candidatures&filter=<?= $fval ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>"
               class="filter-pill <?= $filterStatut === $fval ? 'active' : '' ?>"><?= $flabel ?></a>
            <?php endforeach; ?>
          </div>

          <form method="GET" class="admin-search-form">
            <input type="hidden" name="page" value="admin_candidatures">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filterStatut) ?>">
            <select name="sort" class="form-control">
              <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Tri: Plus recent</option>
              <option value="ancien" <?= $sort === 'ancien' ? 'selected' : '' ?>>Tri: Plus ancien</option>
              <option value="tarif_asc" <?= $sort === 'tarif_asc' ? 'selected' : '' ?>>Tarif: croissant</option>
              <option value="tarif_desc" <?= $sort === 'tarif_desc' ? 'selected' : '' ?>>Tarif: decroissant</option>
            </select>
            <input type="text" name="search" class="form-control" placeholder="Chercher par freelancer ou offre..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="topbar-btn topbar-btn-primary"><i class="fas fa-search"></i> Chercher</button>
          </form>
        </div>

        <div class="admin-table-scroll">
        <table class="admin-table candidatures-table">
          <colgroup>
            <col style="width: 58px;">
            <col style="width: 18%;">
            <col style="width: 20%;">
            <col style="width: 126px;">
            <col style="width: 22%;">
            <col style="width: 118px;">
            <col style="width: 132px;">
            <col style="width: 118px;">
            <col style="width: 162px;">
          </colgroup>
          <thead>
            <tr>
              <th>#</th>
              <th>Freelancer</th>
              <th>Offre</th>
              <th>Tarif</th>
              <th>Message</th>
              <th>Documents</th>
              <th>Statut</th>
              <th>Date</th>
              <th>
                <span style="display:flex;align-items:center;gap:.35rem;">
                  <i class="fas fa-brain" style="color:#f97316;font-size:.8rem;"></i> Analyse avancee
                </span>
              </th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($candidatures)): ?>
            <tr><td colspan="9" style="text-align:center; padding:3rem; color:var(--text-muted);">Aucune candidature trouvee</td></tr>
            <?php else: ?>
            <?php foreach ($candidatures as $c): ?>
            <tr>
              <td>#<?= (int) $c['id_candidature'] ?></td>
              <td>
                <div class="table-service-name"><?= htmlspecialchars($c['nom_freelancer']) ?></div>
                <div class="table-service-meta"><?= htmlspecialchars($c['email_freelancer']) ?></div>
              </td>
              <td><div class="table-offer-title"><?= htmlspecialchars(substr($c['titre_offre'] ?? 'Offre supprimee', 0, 52)) ?></div></td>
              <td><span class="table-price-cell"><?= number_format((float) ($c['tarif_propose'] ?? 0), 2) ?> DT</span></td>
              <td><div class="table-message-cell"><?= htmlspecialchars(substr($c['message'], 0, 58)) ?>...</div></td>
              <td>
                <div class="table-doc-links">
                  <?php if (!empty($c['cv_path'])): ?><a href="<?= htmlspecialchars($c['cv_path']) ?>" target="_blank">CV</a><?php endif; ?>
                  <?php if (!empty($c['portfolio_path'])): ?><a href="<?= htmlspecialchars($c['portfolio_path']) ?>" target="_blank">Portfolio</a><?php endif; ?>
                  <?php if (empty($c['cv_path']) && empty($c['portfolio_path'])): ?><span class="table-service-meta">Aucun</span><?php endif; ?>
                </div>
              </td>
              <td>
                <?php
                $bmap = ['en_attente' => 'badge-pending', 'acceptee' => 'badge-actif', 'refusee' => 'badge-suspendu'];
                ?>
                <span class="badge <?= $bmap[$c['statut']] ?? 'badge-pending' ?>"><?= htmlspecialchars($c['statut']) ?></span>
              </td>
              <td><span class="table-date-cell"><?= date('d/m/Y', strtotime($c['created_at'])) ?></span></td>
              <td>
                <button type="button"
                        class="admin-btn admin-btn-sm js-score-btn"
                        title="Evaluer avec IA"
                        style="background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;border:none;gap:.3rem;display:flex;align-items:center;"
                        data-id="<?= (int) $c['id_candidature'] ?>"
                        data-name="<?= htmlspecialchars($c['nom_freelancer'], ENT_QUOTES) ?>"
                        data-offre="<?= htmlspecialchars($c['titre_offre'] ?? '', ENT_QUOTES) ?>">
                  <i class="fas fa-brain" style="font-size:.7rem;"></i> Analyse avancee
                </button>
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
            <h3>Candidatures par statut</h3>
            <canvas id="candidaturesStatusChart" height="220"></canvas>
          </div>
          <div class="admin-panel-view" data-admin-panel-view="table">
            <table class="admin-table admin-table-compact">
              <thead>
                <tr>
                  <th>Offre</th>
                  <th>Candidatures</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($insights['by_offer'])): ?>
                <tr><td colspan="2" style="text-align:center;">Aucune statistique</td></tr>
                <?php else: ?>
                <?php foreach ($insights['by_offer'] as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['titre_offre']) ?></td>
                  <td><?= (int) $row['total'] ?></td>
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

<!-- ══════════════════════════════════════════════════════════════
     SCORE MODAL (centered overlay)
══════════════════════════════════════════════════════════════ -->
<style>
.sc-overlay {
  position:fixed;inset:0;
  background:rgba(10,5,20,.68);
  backdrop-filter:blur(7px);
  z-index:1050;
  display:flex;align-items:center;justify-content:center;
  padding:1.5rem;
  opacity:0;pointer-events:none;
  transition:opacity .25s;
}
.sc-overlay.open { opacity:1;pointer-events:all; }
.sc-modal {
  background:#fff;
  border-radius:20px;
  width:100%;max-width:680px;
  max-height:92vh;
  box-shadow:0 36px 110px rgba(0,0,0,.32);
  overflow:hidden;
  display:flex;flex-direction:column;
  transform:scale(.93) translateY(14px);
  transition:transform .32s cubic-bezier(.34,1.56,.64,1);
}
.sc-overlay.open .sc-modal { transform:scale(1) translateY(0); }
.sc-body { overflow-y:auto;flex:1; }
</style>

<div id="scOverlay" class="sc-overlay" onclick="if(event.target===this)closeScore()">
  <div class="sc-modal">
    <div style="padding:1.25rem 1.5rem;background:linear-gradient(135deg,#160d2e,#2a1548);
                display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
      <div>
        <div style="font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;
                    color:rgba(255,255,255,.42);margin-bottom:.2rem;">
          <i class="fas fa-brain" style="color:#a855f7;"></i>&nbsp; Évaluation IA — Candidature
        </div>
        <div id="scModalSub" style="font-size:.9rem;font-weight:600;color:#fff;line-height:1.3;"></div>
      </div>
      <button onclick="closeScore()"
              style="background:rgba(255,255,255,.1);border:none;color:#fff;
                     width:34px;height:34px;border-radius:50%;cursor:pointer;font-size:.95rem;
                     display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-left:1rem;">
        <i class="fas fa-xmark"></i>
      </button>
    </div>
    <div id="scModalBody" class="sc-body"></div>
  </div>
</div>

<script>
const adminCandidatureParams = new URLSearchParams(window.location.search);
if (adminCandidatureParams.get('success') && window.SkillBridgeAlerts) {
  window.SkillBridgeAlerts.toast('success', 'Le statut de la candidature a ete mis a jour.');
}
if (adminCandidatureParams.get('error') && window.SkillBridgeAlerts) {
  window.SkillBridgeAlerts.dialog('error', 'Action invalide', 'Le statut demande est invalide.');
}
document.addEventListener('DOMContentLoaded', function () {
  if (window.Chart) {
    new Chart(document.getElementById('candidaturesStatusChart'), {
      type: 'bar',
      data: {
        labels: ['En attente', 'Acceptees', 'Refusees'],
        datasets: [{
          data: [<?= (int) ($stats['en_attente'] ?? 0) ?>, <?= (int) ($stats['acceptee'] ?? 0) ?>, <?= (int) ($stats['refusee'] ?? 0) ?>],
          backgroundColor: ['#e07020', '#2f7d57', '#b84942'],
          borderRadius: 10
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
      }
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

  // ── Score modal buttons ──────────────────────────────────────────────────
  document.querySelectorAll('.js-score-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      openScore(this.dataset.id, this.dataset.name, this.dataset.offre);
    });
  });
});

// ── Score Modal helpers ───────────────────────────────────────────────────────
function openScore(id, name, offre) {
  document.getElementById('scModalSub').textContent = name + (offre ? ' — ' + offre : '');
  document.getElementById('scModalBody').innerHTML  = buildLoadingHtml();
  document.getElementById('scOverlay').classList.add('open');
  fetchScore(id);
}
function closeScore() {
  document.getElementById('scOverlay').classList.remove('open');
}
async function fetchScore(id) {
  try {
    const fd = new FormData();
    fd.append('id_candidature', id);
    const res  = await fetch('index.php?page=candidature_score_api', { method: 'POST', body: fd });
    const data = await res.json();
    const body = document.getElementById('scModalBody');
    if (data.gemini_unavailable)      body.innerHTML = buildUnavailableHtml();
    else if (data.error)              body.innerHTML = `<div style="padding:1.5rem;"><div style="color:#dc2626;padding:1rem;border:1px solid rgba(220,38,38,.25);border-radius:10px;">${esc(data.error)}</div></div>`;
    else                              body.innerHTML = buildScoreHtml(data);
  } catch (e) {
    document.getElementById('scModalBody').innerHTML = `<div style="padding:1.5rem;color:#dc2626;">Erreur : ${esc(e.message)}</div>`;
  }
}

function esc(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }
function scoreColor(v) { return v >= 75 ? '#059669' : v >= 50 ? '#d97706' : '#dc2626'; }
function recConfig(rec) {
  return ({
    'Highly Recommended': { bg:'rgba(5,150,105,.12)',  color:'#059669', border:'rgba(5,150,105,.3)',  icon:'⭐' },
    'Recommended':        { bg:'rgba(37,99,235,.1)',   color:'#2563eb', border:'rgba(37,99,235,.25)', icon:'👍' },
    'Maybe':              { bg:'rgba(217,119,6,.1)',   color:'#d97706', border:'rgba(217,119,6,.25)', icon:'🤔' },
    'Not Recommended':    { bg:'rgba(220,38,38,.1)',   color:'#dc2626', border:'rgba(220,38,38,.25)', icon:'❌' },
  })[rec] || { bg:'rgba(217,119,6,.1)', color:'#d97706', border:'rgba(217,119,6,.25)', icon:'🤔' };
}

function buildLoadingHtml() {
  return `<div style="text-align:center;padding:4rem 2rem;">
    <div style="width:54px;height:54px;border:4px solid #f0ecfa;border-top-color:#a855f7;
                border-radius:50%;animation:scSpin .7s linear infinite;margin:0 auto 1.2rem;"></div>
    <p style="color:#777;font-size:.93rem;font-weight:500;">Gemini analyse la candidature…</p>
    <p style="color:#bbb;font-size:.78rem;margin-top:.3rem;">Quelques secondes suffisent.</p>
  </div>
  <style>@keyframes scSpin{to{transform:rotate(360deg)}}</style>`;
}

function buildUnavailableHtml() {
  return `<div style="padding:1.75rem;">
    <div style="background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.3);border-radius:14px;padding:1.5rem;">
      <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
        <div style="width:42px;height:42px;border-radius:50%;background:rgba(245,158,11,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="fas fa-key" style="color:#d97706;"></i>
        </div>
        <div>
          <div style="font-weight:700;color:#92400e;font-size:.95rem;">Clé API Gemini manquante</div>
          <div style="font-size:.78rem;color:#b45309;">Configuration requise pour l'évaluation IA</div>
        </div>
      </div>
      <ol style="font-size:.85rem;color:#555;line-height:2;margin:0;padding-left:1.1rem;">
        <li>Allez sur <a href="https://aistudio.google.com/apikey" target="_blank" style="color:#d97706;font-weight:600;">aistudio.google.com/apikey</a></li>
        <li>Créez une clé et copiez-la</li>
        <li>Dans <code style="background:#f5f0e8;padding:.1rem .35rem;border-radius:4px;">config.php</code>, remplacez <code style="background:#f5f0e8;padding:.1rem .35rem;border-radius:4px;">VOTRE_CLE_GEMINI_ICI</code></li>
      </ol>
    </div>
  </div>`;
}

function buildScoreHtml(d) {
  const overall = d.overall_score ?? 0;
  const c       = d.criteria_scores ?? {};
  const rec     = d.recommendation ?? 'Maybe';
  const rcfg    = recConfig(rec);
  const col     = scoreColor(overall);
  const R       = 54, circ = 2 * Math.PI * R, offset = circ * (1 - overall / 100);

  const criteria = [
    { label:'Compétences',      key:'skills_match',         icon:'fa-code',     accent:'#7c3aed' },
    { label:'Expérience',       key:'experience_match',     icon:'fa-briefcase',accent:'#2563eb' },
    { label:'Lettre de motiv.', key:'cover_letter_quality', icon:'fa-pen-nib',  accent:'#059669' },
  ].map(cr => {
    const v = c[cr.key] ?? 0, vc = scoreColor(v);
    return `<div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.38rem;">
        <div style="display:flex;align-items:center;gap:.4rem;font-size:.81rem;font-weight:600;color:#444;">
          <span style="width:22px;height:22px;border-radius:6px;background:${cr.accent}18;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas ${cr.icon}" style="font-size:.58rem;color:${cr.accent};"></i>
          </span>
          ${esc(cr.label)}
        </div>
        <span style="font-size:.88rem;font-weight:800;color:${vc};">${v}<span style="font-size:.62rem;color:#ddd;font-weight:400;">/100</span></span>
      </div>
      <div style="background:#f3f0fa;border-radius:999px;height:7px;overflow:hidden;">
        <div style="width:${v}%;height:100%;background:${vc};border-radius:999px;transition:width .85s cubic-bezier(.4,0,.2,1);"></div>
      </div>
    </div>`;
  }).join('');

  const mkItem = (text, color, ico) =>
    `<div style="display:flex;gap:.45rem;align-items:flex-start;margin-bottom:.5rem;">
      <span style="width:18px;height:18px;border-radius:50%;background:${color}18;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.1rem;">
        <i class="fas ${ico}" style="font-size:.5rem;color:${color};"></i>
      </span>
      <span style="font-size:.82rem;color:#333;line-height:1.55;">${esc(text)}</span>
    </div>`;

  const pts = (d.strengths  || []).map(s => mkItem(s,'#059669','fa-check')).join('') || '<p style="font-size:.78rem;color:#ccc;font-style:italic;margin:0;">Aucun point fort identifié.</p>';
  const wks = (d.weaknesses || []).map(w => mkItem(w,'#dc2626','fa-xmark')).join('') || '<p style="font-size:.78rem;color:#ccc;font-style:italic;margin:0;">Aucun point faible identifié.</p>';

  return `
  <div style="display:grid;grid-template-columns:160px 1fr;gap:1.5rem;align-items:center;
              padding:1.75rem;background:linear-gradient(135deg,#faf8ff,#fff8f4);border-bottom:1px solid #f0ecf8;">
    <div style="text-align:center;">
      <svg width="148" height="148" viewBox="0 0 148 148">
        <defs><filter id="glowSc"><feGaussianBlur stdDeviation="3" result="cb"/><feMerge><feMergeNode in="cb"/><feMergeNode in="SourceGraphic"/></feMerge></filter></defs>
        <circle cx="74" cy="74" r="${R}" fill="none" stroke="#f0ecfa" stroke-width="12"/>
        <circle cx="74" cy="74" r="${R}" fill="none" stroke="${col}" stroke-width="12"
                stroke-dasharray="${circ.toFixed(2)}" stroke-dashoffset="${offset.toFixed(2)}"
                stroke-linecap="round" transform="rotate(-90 74 74)" filter="url(#glowSc)"
                style="transition:stroke-dashoffset 1s cubic-bezier(.4,0,.2,1);"/>
        <text x="74" y="70" text-anchor="middle" font-size="30" font-weight="800"
              fill="${col}" font-family="DM Sans,sans-serif">${overall}</text>
        <text x="74" y="88" text-anchor="middle" font-size="12" fill="#ccc"
              font-family="DM Sans,sans-serif">/100</text>
      </svg>
      <div style="font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#bbb;">Score global</div>
    </div>
    <div>
      <div style="font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#bbb;margin-bottom:.5rem;">Recommandation</div>
      <span style="display:inline-flex;align-items:center;gap:.45rem;padding:.5rem 1.1rem;border-radius:999px;
                   background:${rcfg.bg};color:${rcfg.color};border:1.5px solid ${rcfg.border};
                   font-size:.9rem;font-weight:800;letter-spacing:.02em;margin-bottom:.9rem;">
        ${rcfg.icon} ${esc(rec)}
      </span>
      ${d.summary ? `<p style="font-size:.83rem;color:#555;line-height:1.65;margin:0;padding:.85rem;background:#fff;border-radius:10px;border:1px solid #f0ecf8;">${esc(d.summary)}</p>` : ''}
    </div>
  </div>
  <div style="padding:1.5rem;border-bottom:1px solid #faf8ff;display:grid;gap:.85rem;">
    <div style="font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#bbb;">
      <i class="fas fa-chart-bar" style="color:#a855f7;"></i> Évaluation détaillée
    </div>
    ${criteria}
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;">
    <div style="padding:1.25rem 1.5rem;border-right:1px solid #faf8ff;">
      <div style="font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#059669;margin-bottom:.75rem;">
        <i class="fas fa-circle-check"></i> Points forts
      </div>
      ${pts}
    </div>
    <div style="padding:1.25rem 1.5rem;">
      <div style="font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#dc2626;margin-bottom:.75rem;">
        <i class="fas fa-circle-xmark"></i> Points faibles
      </div>
      ${wks}
    </div>
  </div>`;
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
