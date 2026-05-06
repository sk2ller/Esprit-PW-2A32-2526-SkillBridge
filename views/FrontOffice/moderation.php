<?php
$pageTitle = 'Modération de Contenu - SkillBridge';

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: index.php?page=home');
    exit;
}

include __DIR__ . '/navbar.php';
?>

<style>
.mod-hero {
  background: linear-gradient(135deg, var(--charcoal) 0%, #1a0e05 100%);
  color: var(--text-light);
  padding: 3.5rem 2rem 2.5rem;
  text-align: center;
  border-bottom: 1px solid var(--border);
}
.mod-hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(1.8rem,4vw,2.8rem); margin-bottom:.5rem; }
.mod-hero p  { color: #c9b89a; font-size: 1rem; }

.mod-badge {
  display: inline-flex; align-items: center; gap: .4rem;
  background: rgba(184,73,66,.18); border: 1px solid rgba(184,73,66,.5);
  color: #e06060; padding: .3rem .85rem; border-radius: 999px;
  font-size: .78rem; font-weight: 700; letter-spacing: .04em;
  margin-bottom: 1rem; text-transform: uppercase;
}

.mod-layout {
  display: grid;
  grid-template-columns: 360px 1fr;
  gap: 2rem;
  max-width: 1400px;
  margin: 2.5rem auto;
  padding: 0 1.5rem 4rem;
  align-items: start;
}
@media (max-width: 900px) { .mod-layout { grid-template-columns: 1fr; } }

.input-card {
  background: var(--paper);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 1.75rem;
  box-shadow: var(--shadow);
  position: sticky;
  top: calc(var(--nav-height) + 1rem);
}
.input-card h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.2rem;
  margin-bottom: 1.4rem;
  display: flex; align-items: center; gap: .5rem;
  color: var(--text-primary);
}

.form-group { margin-bottom: 1.1rem; }
.form-group label {
  display: block;
  font-size: .8rem; font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: .3rem;
  text-transform: uppercase; letter-spacing: .04em;
}
.form-group input,
.form-group textarea {
  width: 100%;
  padding: .65rem .85rem;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  background: var(--paper-soft);
  color: var(--text-primary);
  font-family: 'DM Sans', sans-serif;
  font-size: .9rem;
  transition: border-color .2s;
}
.form-group input:focus,
.form-group textarea:focus {
  outline: none; border-color: var(--amber); background: #fff;
}
.form-group input.input-error,
.form-group textarea.input-error {
  border-color: var(--danger) !important;
  background: rgba(184,73,66,.04);
}
.form-group textarea { resize: vertical; min-height: 200px; }
.char-counter { font-size: .72rem; color: var(--text-muted); text-align: right; margin-top: .25rem; }
.char-counter.warn { color: var(--danger); }

.btn-moderate {
  width: 100%;
  background: linear-gradient(135deg, #b84942 0%, #d96b5a 100%);
  color: #fff; border: none;
  border-radius: var(--radius);
  padding: .85rem; font-size: .95rem; font-weight: 700;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: .5rem;
  transition: opacity .2s, transform .15s;
  margin-top: .5rem;
}
.btn-moderate:hover    { opacity: .92; transform: translateY(-1px); }
.btn-moderate:disabled { opacity: .6; cursor: not-allowed; transform: none; }

/* ── Results ── */
.results-panel { min-height: 400px; }
.results-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 1.2rem; flex-wrap: wrap; gap: .5rem;
}
.results-header h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.35rem; color: var(--text-primary);
}

.placeholder-state {
  text-align: center; padding: 4rem 2rem; color: var(--text-muted);
}
.placeholder-state .icon { font-size: 3rem; margin-bottom: 1rem; opacity: .5; }
.placeholder-state h3 { font-size: 1.1rem; color: var(--text-secondary); margin-bottom: .4rem; }

.spinner-wrap { display:none; text-align:center; padding:4rem 2rem; }
.spinner {
  width: 44px; height: 44px;
  border: 3px solid var(--border); border-top-color: #b84942;
  border-radius: 50%;
  animation: spin .7s linear infinite;
  margin: 0 auto 1rem;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Verdict card ── */
.verdict-card {
  background: var(--paper);
  border: 2px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow);
  animation: fadeUp .35s ease both;
}
@keyframes fadeUp {
  from { opacity:0; transform:translateY(16px); }
  to   { opacity:1; transform:translateY(0); }
}

.verdict-header {
  padding: 1.75rem 1.75rem 1.25rem;
  display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
}
.verdict-icon {
  width: 60px; height: 60px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.6rem; flex-shrink: 0;
}
.verdict-icon.approved { background: rgba(47,125,87,.15); }
.verdict-icon.rejected { background: rgba(184,73,66,.12); }

.verdict-title { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; }
.verdict-title.approved { color: var(--success); }
.verdict-title.rejected { color: var(--danger); }
.verdict-subtitle { font-size: .85rem; color: var(--text-muted); margin-top: .2rem; }

.severity-badge {
  padding: .35rem .9rem; border-radius: 999px;
  font-size: .75rem; font-weight: 800;
  text-transform: uppercase; letter-spacing: .06em;
  margin-left: auto;
}
.sev-none   { background: rgba(47,125,87,.15);  color: var(--success); }
.sev-low    { background: rgba(224,112,32,.15); color: var(--amber); }
.sev-medium { background: rgba(245,158,11,.18); color: #b07800; }
.sev-high   { background: rgba(184,73,66,.15);  color: var(--danger); }

.verdict-body { padding: 0 1.75rem 1.75rem; display: flex; flex-direction: column; gap: 1.25rem; }

.section-title-sm {
  font-size: .78rem; font-weight: 700; color: var(--text-muted);
  text-transform: uppercase; letter-spacing: .06em;
  margin-bottom: .5rem;
  display: flex; align-items: center; gap: .4rem;
}

.flagged-list {
  display: flex; flex-wrap: wrap; gap: .4rem;
}
.flagged-tag {
  background: rgba(184,73,66,.1);
  border: 1px solid rgba(184,73,66,.25);
  color: var(--danger);
  padding: .25rem .65rem;
  border-radius: 6px;
  font-size: .8rem; font-weight: 600;
}

.reason-box {
  background: var(--sand);
  border-left: 3px solid var(--amber);
  border-radius: 0 var(--radius) var(--radius) 0;
  padding: .85rem 1rem;
  font-size: .88rem; color: var(--text-secondary); line-height: 1.6;
}

.suggestion-box {
  background: rgba(47,125,87,.06);
  border-left: 3px solid var(--success);
  border-radius: 0 var(--radius) var(--radius) 0;
  padding: .85rem 1rem;
  font-size: .88rem; color: var(--text-secondary); line-height: 1.6;
}

.approved-banner {
  background: rgba(47,125,87,.08);
  border: 1px solid rgba(47,125,87,.2);
  border-radius: var(--radius);
  padding: 1rem 1.25rem;
  display: flex; align-items: center; gap: .75rem;
  font-size: .9rem; color: var(--success); font-weight: 600;
}

.server-error {
  background: rgba(184,73,66,.08);
  border: 1px solid rgba(184,73,66,.25);
  border-radius: var(--radius);
  padding: 1.2rem 1.5rem;
  color: var(--danger); font-size: .9rem;
  display: flex; align-items: center; gap: .6rem;
}
</style>

<div class="mod-hero">
  <div class="mod-badge"><i class="fas fa-shield-halved"></i> Modération IA</div>
  <h1>Modération de Contenu</h1>
  <p>Analysez une offre d'emploi pour détecter tout contenu inapproprié, discriminatoire ou non professionnel.</p>
</div>

<div class="mod-layout">

  <!-- ── Input Form ── -->
  <div class="input-card">
    <h2><i class="fas fa-magnifying-glass" style="color:var(--danger)"></i> Texte à analyser</h2>

    <form id="modForm" novalidate>

      <div class="form-group">
        <label for="offer_title">
          <i class="fas fa-heading"></i> Titre de l'offre <span style="color:var(--danger)">*</span>
        </label>
        <input type="text" id="offer_title" name="offer_title"
               placeholder="Ex : Développeur PHP Senior" autocomplete="off"
               maxlength="200">
      </div>

      <div class="form-group">
        <label for="offer_text">
          <i class="fas fa-align-left"></i> Contenu de l'offre <span style="color:var(--danger)">*</span>
        </label>
        <textarea id="offer_text" name="offer_text"
                  placeholder="Collez ici le texte complet de l'offre à analyser..."
                  maxlength="5000"></textarea>
        <div class="char-counter" id="charCounter">0 / 5000 caractères</div>
      </div>

      <button type="submit" class="btn-moderate" id="modBtn">
        <i class="fas fa-shield-halved"></i> Analyser le contenu
      </button>
    </form>
  </div>

  <!-- ── Results Panel ── -->
  <div class="results-panel">
    <div class="results-header">
      <h2><i class="fas fa-flag" style="color:var(--danger); font-size:1rem"></i> Résultat de l'analyse</h2>
    </div>

    <div class="placeholder-state" id="placeholderState">
      <div class="icon">🔍</div>
      <h3>En attente d'analyse</h3>
      <p>Collez le texte d'une offre à gauche et lancez l'analyse pour détecter tout contenu problématique.</p>
    </div>

    <div class="spinner-wrap" id="spinnerWrap">
      <div class="spinner"></div>
      <p style="color:var(--text-muted); font-size:.9rem">Analyse en cours...</p>
    </div>

    <div class="server-error" id="serverError" style="display:none">
      <i class="fas fa-exclamation-circle"></i>
      <span id="serverErrorMsg">Une erreur est survenue.</span>
    </div>

    <div id="verdictWrap" style="display:none"></div>
  </div>

</div>

<script>
(function () {

  /* ── Helpers ──────────────────────────────────────────── */
  function esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
  }

  function swalError(title, focusId) {
    Swal.fire({
      icon: 'error',
      title: title,
      confirmButtonColor: '#b84942',
      background: '#fffaf4',
      color: '#1f1f23',
      confirmButtonText: 'Ok'
    }).then(() => {
      if (focusId) {
        const el = document.getElementById(focusId);
        if (el) { el.focus(); el.classList.add('input-error'); }
      }
    });
  }

  function clearErrors() {
    document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
  }

  /* ── Char counter ─────────────────────────────────────── */
  const textarea    = document.getElementById('offer_text');
  const charCounter = document.getElementById('charCounter');
  textarea.addEventListener('input', () => {
    const len = textarea.value.length;
    charCounter.textContent = len + ' / 5000 caractères';
    charCounter.classList.toggle('warn', len > 4500);
  });

  /* ── JS Validation ────────────────────────────────────── */
  function validateForm() {
    clearErrors();

    const title = document.getElementById('offer_title').value.trim();
    const text  = document.getElementById('offer_text').value.trim();

    if (title === '') {
      swalError('Titre requis', 'offer_title');
      return false;
    }
    if (title.length < 5) {
      swalError('Titre trop court — minimum 5 caractères', 'offer_title');
      return false;
    }
    if (title.length > 200) {
      swalError('Titre trop long — maximum 200 caractères', 'offer_title');
      return false;
    }

    if (text === '') {
      swalError('Contenu requis', 'offer_text');
      return false;
    }
    if (text.length < 30) {
      swalError('Contenu trop court — minimum 30 caractères', 'offer_text');
      return false;
    }
    if (text.length > 5000) {
      swalError('Contenu trop long — maximum 5 000 caractères', 'offer_text');
      return false;
    }

    return true;
  }

  /* ── Gemini unavailable warning card ─────────────────── */
  function buildUnavailableCard() {
    const card = document.createElement('div');
    card.className = 'verdict-card';
    card.style.borderColor = 'rgba(224,112,32,.4)';
    card.innerHTML = `
      <div class="verdict-header">
        <div class="verdict-icon" style="background:rgba(224,112,32,.15); font-size:1.6rem;">⚠️</div>
        <div>
          <div class="verdict-title" style="color:var(--amber)">Clé API Gemini manquante</div>
          <div class="verdict-subtitle">Configurez votre clé pour activer l'analyse IA.</div>
        </div>
        <span class="severity-badge sev-low">Non vérifié</span>
      </div>
      <div class="verdict-body">
        <div style="background:rgba(224,112,32,.08); border:1px solid rgba(224,112,32,.25);
                    border-radius:var(--radius); padding:1rem 1.2rem;">
          <div style="font-size:.82rem; font-weight:700; color:var(--amber); margin-bottom:.6rem; text-transform:uppercase; letter-spacing:.04em;">
            <i class="fas fa-screwdriver-wrench"></i> Configuration requise
          </div>
          <ol style="font-size:.88rem; color:var(--text-secondary); line-height:1.8; margin:0; padding-left:1.2rem;">
            <li>Allez sur <a href="https://aistudio.google.com/apikey" target="_blank"
                style="color:var(--amber); font-weight:600; text-decoration:underline;">
                aistudio.google.com/apikey</a> <span style="color:var(--text-muted)">(gratuit, aucune carte bancaire)</span></li>
            <li>Créez une clé API et copiez-la</li>
            <li>Ouvrez <code style="background:rgba(0,0,0,.06);padding:.1rem .3rem;border-radius:3px;">config.php</code>
                à la racine du projet</li>
            <li>Remplacez <code style="background:rgba(0,0,0,.06);padding:.1rem .3rem;border-radius:3px;">VOTRE_CLE_GEMINI_ICI</code>
                par votre clé (<code>AIzaSy…</code>)</li>
          </ol>
        </div>
        <div style="margin-top:.75rem; padding:.7rem 1rem; background:var(--paper-soft);
                    border-radius:var(--radius); font-size:.8rem; color:var(--text-muted);
                    font-family:monospace; word-break:break-all;">
          define('GEMINI_API_KEY', '<span style="color:var(--amber); font-weight:700;">AIzaSyVotreVraieCleIci</span>');
        </div>
      </div>`;
    return card;
  }

  /* ── Build verdict card ───────────────────────────────── */
  function buildVerdict(data, title) {
    const approved  = data.is_approved;
    const severity  = data.severity;
    const flags     = data.flagged_words || [];
    const reason    = data.reason    || '';
    const suggest   = data.suggestion || '';

    const sevLabel = { none: 'Aucun', low: 'Faible', medium: 'Moyen', high: 'Élevé' };
    const sevClass = { none: 'sev-none', low: 'sev-low', medium: 'sev-medium', high: 'sev-high' };

    const flagsHtml = flags.length
      ? `<div class="flagged-list">
           ${flags.map(f => `<span class="flagged-tag"><i class="fas fa-flag" style="font-size:.65rem"></i> ${esc(f)}</span>`).join('')}
         </div>`
      : `<p style="font-size:.85rem; color:var(--text-muted); font-style:italic">Aucun mot problématique détecté.</p>`;

    const bodyHtml = approved && severity === 'none'
      ? `<div class="approved-banner">
           <i class="fas fa-circle-check" style="font-size:1.4rem"></i>
           Ce contenu est propre, professionnel et conforme aux standards de la plateforme. Prêt à être publié.
         </div>`
      : `
        ${reason ? `
        <div>
          <div class="section-title-sm"><i class="fas fa-triangle-exclamation"></i> Motif</div>
          <div class="reason-box">${esc(reason)}</div>
        </div>` : ''}

        <div>
          <div class="section-title-sm"><i class="fas fa-flag"></i> Éléments détectés (${flags.length})</div>
          ${flagsHtml}
        </div>

        ${suggest ? `
        <div>
          <div class="section-title-sm"><i class="fas fa-lightbulb"></i> Suggestion</div>
          <div class="suggestion-box">${esc(suggest)}</div>
        </div>` : ''}
      `;

    const card = document.createElement('div');
    card.className = 'verdict-card';
    card.style.borderColor = approved ? 'rgba(47,125,87,.35)' : 'rgba(184,73,66,.35)';

    card.innerHTML = `
      <div class="verdict-header">
        <div class="verdict-icon ${approved ? 'approved' : 'rejected'}">
          ${approved ? '✅' : '🚫'}
        </div>
        <div>
          <div class="verdict-title ${approved ? 'approved' : 'rejected'}">
            ${approved ? 'Contenu Approuvé' : 'Contenu Rejeté'}
          </div>
          <div class="verdict-subtitle">
            Offre analysée : <strong>${esc(title)}</strong>
          </div>
        </div>
        <span class="severity-badge ${sevClass[severity] || 'sev-none'}">
          Sévérité : ${sevLabel[severity] || severity}
        </span>
      </div>
      <div class="verdict-body">${bodyHtml}</div>
    `;

    return card;
  }

  /* ── Form submit ──────────────────────────────────────── */
  const form        = document.getElementById('modForm');
  const btn         = document.getElementById('modBtn');
  const spinner     = document.getElementById('spinnerWrap');
  const placeholder = document.getElementById('placeholderState');
  const serverError = document.getElementById('serverError');
  const serverMsg   = document.getElementById('serverErrorMsg');
  const verdictWrap = document.getElementById('verdictWrap');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    if (!validateForm()) return;

    const title = document.getElementById('offer_title').value.trim();

    placeholder.style.display  = 'none';
    serverError.style.display  = 'none';
    verdictWrap.style.display  = 'none';
    verdictWrap.innerHTML      = '';
    spinner.style.display      = 'block';
    btn.disabled               = true;
    btn.innerHTML              = '<i class="fas fa-spinner fa-spin"></i> Analyse...';

    try {
      const res  = await fetch('index.php?page=moderation_api', {
        method: 'POST',
        body:   new FormData(form)
      });

      if (!res.ok) throw new Error('Erreur serveur ' + res.status);

      const json = await res.json();
      spinner.style.display = 'none';

      if (json.error) {
        serverMsg.textContent     = json.error;
        serverError.style.display = 'flex';
        return;
      }

      const card = json.gemini_unavailable
        ? buildUnavailableCard()
        : buildVerdict(json, title);
      verdictWrap.appendChild(card);
      verdictWrap.style.display = 'block';

    } catch (err) {
      spinner.style.display     = 'none';
      serverMsg.textContent     = 'Erreur de connexion : ' + err.message;
      serverError.style.display = 'flex';
    } finally {
      btn.disabled  = false;
      btn.innerHTML = '<i class="fas fa-shield-halved"></i> Analyser le contenu';
    }
  });

  /* ── Clear red border on change ───────────────────────── */
  document.querySelectorAll('#modForm input, #modForm textarea').forEach(el => {
    el.addEventListener('input',  () => el.classList.remove('input-error'));
    el.addEventListener('change', () => el.classList.remove('input-error'));
  });

})();
</script>

<?php include __DIR__ . '/footer.php'; ?>
