<?php
$pageTitle = 'Candidatures Recues - SkillBridge';
include __DIR__ . '/navbar.php';
$id_client = $_GET['id_client'] ?? 1;
$filterStatut = $_GET['filter'] ?? 'all';
?>

<div class="page-top">
<div class="page-container">

  <div class="dashboard-grid">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-file-signature"></i></div>
      <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
      <div class="stat-label">Total candidatures</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(245,158,11,0.15); color:var(--accent-orange);"><i class="fas fa-clock"></i></div>
      <div class="stat-value"><?= $stats['en_attente'] ?? 0 ?></div>
      <div class="stat-label">En attente</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(16,185,129,0.15); color:var(--accent-green);"><i class="fas fa-check-circle"></i></div>
      <div class="stat-value"><?= $stats['acceptee'] ?? 0 ?></div>
      <div class="stat-label">Acceptees</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(239,68,68,0.15); color:var(--danger);"><i class="fas fa-times-circle"></i></div>
      <div class="stat-value"><?= $stats['refusee'] ?? 0 ?></div>
      <div class="stat-label">Refusees</div>
    </div>
  </div>

  <div class="section-header">
    <h1 class="section-title">Candidatures <span>Recues</span></h1>
    <a href="index.php?page=mes_offres&id_client=<?= (int) $id_client ?>" class="btn-outline" style="text-decoration:none;">
      <i class="fas fa-arrow-left"></i> Mes Offres
    </a>
  </div>

  <div style="display:flex; gap:4px; margin-bottom:1.2rem; background:var(--bg-card); padding:4px; border-radius:10px; width:fit-content; border:1px solid var(--border);">
    <?php
    $filters = ['all' => 'Toutes', 'en_attente' => 'En attente', 'acceptee' => 'Acceptees', 'refusee' => 'Refusees'];
    foreach ($filters as $fval => $flabel): ?>
    <a href="index.php?page=client_candidatures&id_client=<?= (int) $id_client ?>&filter=<?= $fval ?>"
       style="padding:7px 16px; border-radius:7px; font-size:0.82rem; font-weight:600; text-decoration:none; color:<?= $filterStatut === $fval ? 'white' : 'var(--text-muted)' ?>; background:<?= $filterStatut === $fval ? 'var(--accent-purple)' : 'transparent' ?>;">
      <?= $flabel ?>
    </a>
    <?php endforeach; ?>
  </div>

  <div style="margin-bottom:1.2rem;">
    <form method="GET" style="display:flex; gap:0.5rem;">
      <input type="hidden" name="page" value="client_candidatures">
      <input type="hidden" name="id_client" value="<?= (int) $id_client ?>">
      <input type="hidden" name="filter" value="<?= htmlspecialchars($filterStatut) ?>">
      <input type="text" name="search" placeholder="Chercher par freelancer, email ou offre..."
             value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
             style="flex:1; padding:10px 16px; border:1px solid var(--border); border-radius:8px;">
      <button type="submit" class="btn-primary" style="padding:10px 20px;">
        <i class="fas fa-search"></i> Chercher
      </button>
    </form>
  </div>

  <?php if (empty($candidatures)): ?>
    <div class="empty-state">
      <div class="icon">Aucun</div>
      <h3>Aucune candidature</h3>
      <p>Les candidatures a vos offres apparaitront ici.</p>
    </div>
  <?php else: ?>
    <div style="display:grid; gap:1rem;">
      <?php foreach ($candidatures as $c): ?>
      <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:1.25rem;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:0.8rem;">
          <div>
            <div style="font-weight:700; font-size:1rem; margin-bottom:2px;"><?= htmlspecialchars($c['nom_freelancer']) ?></div>
            <div style="font-size:0.82rem; color:var(--text-muted);"><?= htmlspecialchars($c['email_freelancer']) ?></div>
            <div style="font-size:0.82rem; color:var(--accent-purple-light); margin-top:4px;">
              <i class="fas fa-briefcase"></i> <?= htmlspecialchars($c['titre_offre'] ?? 'Offre') ?>
            </div>
          </div>
          <?php
          $statusStyles = [
            'en_attente' => ['bg' => 'rgba(245,158,11,0.15)', 'color' => 'var(--accent-orange)', 'text' => 'En attente'],
            'acceptee' => ['bg' => 'rgba(16,185,129,0.15)', 'color' => 'var(--accent-green)', 'text' => 'Acceptee'],
            'refusee' => ['bg' => 'rgba(239,68,68,0.15)', 'color' => 'var(--danger)', 'text' => 'Refusee'],
          ];
          $st = $statusStyles[$c['statut']] ?? $statusStyles['en_attente'];
          ?>
          <span style="background:<?= $st['bg'] ?>; color:<?= $st['color'] ?>; padding:6px 11px; border-radius:20px; font-size:0.75rem; font-weight:700;">
            <?= $st['text'] ?>
          </span>
        </div>

        <div style="display:flex; gap:1.25rem; font-size:0.85rem; margin-bottom:0.7rem; flex-wrap:wrap;">
          <span><i class="fas fa-money-bill-wave"></i> Tarif propose: <strong><?= number_format((float) ($c['tarif_propose'] ?? 0), 2) ?> DT</strong></span>
          <span style="color:var(--text-muted);"><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($c['created_at'])) ?></span>
          <?php if (!empty($c['cv_path'])): ?>
          <a href="<?= htmlspecialchars($c['cv_path']) ?>" target="_blank" style="text-decoration:none; color:var(--accent-purple-light); font-weight:600;">
            <i class="fas fa-file-pdf"></i> CV
          </a>
          <?php endif; ?>
          <?php if (!empty($c['portfolio_path'])): ?>
          <a href="<?= htmlspecialchars($c['portfolio_path']) ?>" target="_blank" style="text-decoration:none; color:var(--accent-purple-light); font-weight:600;">
            <i class="fas fa-folder-open"></i> Portfolio
          </a>
          <?php endif; ?>
        </div>

        <div style="background:var(--bg-hover); border:1px solid var(--border); border-radius:8px; padding:0.85rem; font-size:0.9rem; color:var(--text-secondary); line-height:1.5; margin-bottom:0.9rem;">
          <?= nl2br(htmlspecialchars($c['message'])) ?>
        </div>

        <div style="display:flex; gap:0.5rem; justify-content:flex-end; flex-wrap:wrap; align-items:center;">

          <!-- ── Évaluation IA ── -->
          <button type="button"
                  class="js-score-btn"
                  style="background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;border:none;
                         padding:8px 14px;border-radius:8px;font-size:0.82rem;font-weight:700;
                         cursor:pointer;display:flex;align-items:center;gap:.4rem;"
                  data-id="<?= (int) $c['id_candidature'] ?>"
                  data-name="<?= htmlspecialchars($c['nom_freelancer'], ENT_QUOTES) ?>"
                  data-offre="<?= htmlspecialchars($c['titre_offre'] ?? '', ENT_QUOTES) ?>">
            <i class="fas fa-brain" style="font-size:.75rem;"></i> Analyse
          </button>

          <?php if (($c['statut'] ?? '') !== 'acceptee'): ?>
          <a href="index.php?page=client_candidature_statut&id=<?= (int) $c['id_candidature'] ?>&id_client=<?= (int) $id_client ?>&statut=acceptee"
             class="btn-primary js-confirm-candidature" data-confirm-title="Accepter cette candidature ?" data-confirm-text="Le freelancer sera marque comme accepte." style="text-decoration:none; padding:8px 14px; font-size:0.82rem;">
            <i class="fas fa-check"></i> Accepter
          </a>
          <?php endif; ?>
          <?php if (($c['statut'] ?? '') !== 'refusee'): ?>
          <a href="index.php?page=client_candidature_statut&id=<?= (int) $c['id_candidature'] ?>&id_client=<?= (int) $id_client ?>&statut=refusee"
             class="btn-outline js-confirm-candidature" data-confirm-title="Refuser cette candidature ?" data-confirm-text="Le freelancer sera marque comme refuse." style="text-decoration:none; padding:8px 14px; font-size:0.82rem; color:var(--danger); border:1px solid var(--danger);">
            <i class="fas fa-ban"></i> Refuser
          </a>
          <?php endif; ?>
          <?php if (($c['statut'] ?? '') !== 'en_attente'): ?>
          <a href="index.php?page=client_candidature_statut&id=<?= (int) $c['id_candidature'] ?>&id_client=<?= (int) $id_client ?>&statut=en_attente"
             class="btn-outline js-confirm-candidature" data-confirm-title="Remettre en attente ?" data-confirm-text="La candidature repassera en attente." style="text-decoration:none; padding:8px 14px; font-size:0.82rem;">
            <i class="fas fa-undo"></i> En attente
          </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php include __DIR__ . '/../partials/pagination.php'; ?>

</div>
</div>

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
const candidatureParams = new URLSearchParams(window.location.search);
if (candidatureParams.get('success') && window.SkillBridgeAlerts) {
  window.SkillBridgeAlerts.toast('success', 'Le statut de la candidature a bien ete mis a jour.');
}
if (candidatureParams.get('error') && window.SkillBridgeAlerts) {
  window.SkillBridgeAlerts.dialog('error', 'Action impossible', 'Cette candidature ne peut pas etre modifiee avec les parametres actuels.');
}
document.querySelectorAll('.js-confirm-candidature').forEach((link) => {
  link.addEventListener('click', function (event) {
    event.preventDefault();
    if (!window.Swal) { window.location.href = this.href; return; }
    Swal.fire({
      title: this.dataset.confirmTitle || 'Confirmer cette action ?',
      text: this.dataset.confirmText || '',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#e07020',
      cancelButtonColor: '#8b8791',
      confirmButtonText: 'Confirmer',
      cancelButtonText: 'Annuler',
      background: '#fffaf4',
      color: '#1f1f23'
    }).then((result) => { if (result.isConfirmed) window.location.href = this.href; });
  });
});

// ── Score modal ───────────────────────────────────────────────────────────────
document.querySelectorAll('.js-score-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    openScore(this.dataset.id, this.dataset.name, this.dataset.offre);
  });
});

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
    const fd = new FormData(); fd.append('id_candidature', id);
    const res  = await fetch('index.php?page=candidature_score_api', { method: 'POST', body: fd });
    const data = await res.json();
    const body = document.getElementById('scModalBody');
    if (data.gemini_unavailable)  body.innerHTML = buildUnavailableHtml();
    else if (data.error)          body.innerHTML = `<div style="padding:1.5rem;"><div style="color:#dc2626;padding:1rem;border:1px solid rgba(220,38,38,.25);border-radius:10px;">${esc(data.error)}</div></div>`;
    else                          body.innerHTML = buildScoreHtml(data);
  } catch(e) {
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
          <div style="font-size:.78rem;color:#b45309;">Configuration requise</div>
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
        <defs><filter id="glowSc2"><feGaussianBlur stdDeviation="3" result="cb"/><feMerge><feMergeNode in="cb"/><feMergeNode in="SourceGraphic"/></feMerge></filter></defs>
        <circle cx="74" cy="74" r="${R}" fill="none" stroke="#f0ecfa" stroke-width="12"/>
        <circle cx="74" cy="74" r="${R}" fill="none" stroke="${col}" stroke-width="12"
                stroke-dasharray="${circ.toFixed(2)}" stroke-dashoffset="${offset.toFixed(2)}"
                stroke-linecap="round" transform="rotate(-90 74 74)" filter="url(#glowSc2)"
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
