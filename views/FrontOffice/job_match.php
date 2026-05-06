<?php
$pageTitle = 'Job Matching IA - SkillBridge';

if (($_SESSION['role'] ?? '') !== 'freelancer') {
    header('Location: index.php?page=offres');
    exit;
}

include __DIR__ . '/navbar.php';
?>

<style>
.match-hero {
  background: linear-gradient(135deg, var(--charcoal) 0%, #2e1f0e 100%);
  color: var(--text-light);
  padding: 3.5rem 2rem 2.5rem;
  text-align: center;
  border-bottom: 1px solid var(--border);
}
.match-hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(1.8rem,4vw,2.8rem); margin-bottom:.5rem; }
.match-hero p  { color: #c9b89a; font-size: 1rem; }

.match-badge {
  display: inline-flex; align-items: center; gap: .4rem;
  background: var(--amber-glow); border: 1px solid var(--amber-light);
  color: var(--amber-light); padding: .3rem .85rem; border-radius: 999px;
  font-size: .78rem; font-weight: 700; letter-spacing: .04em;
  margin-bottom: 1rem; text-transform: uppercase;
}

.match-layout {
  display: grid;
  grid-template-columns: 340px 1fr;
  gap: 2rem;
  max-width: 1400px;
  margin: 2.5rem auto;
  padding: 0 1.5rem 4rem;
  align-items: start;
}
@media (max-width: 900px) { .match-layout { grid-template-columns: 1fr; } }

.profile-card {
  background: var(--paper);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 1.75rem;
  box-shadow: var(--shadow);
  position: sticky;
  top: calc(var(--nav-height) + 1rem);
}
.profile-card h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.2rem;
  margin-bottom: 1.4rem;
  display: flex; align-items: center; gap: .5rem;
  color: var(--text-primary);
}

.form-group { margin-bottom: 1.1rem; }
.form-group label {
  display: block;
  font-size: .8rem;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: .3rem;
  text-transform: uppercase;
  letter-spacing: .04em;
}
.form-group input,
.form-group select,
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
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--amber);
  background: #fff;
}
.form-group input.input-error,
.form-group textarea.input-error {
  border-color: var(--danger) !important;
  background: rgba(184,73,66,.04);
}
.form-group textarea { resize: vertical; min-height: 72px; }

.btn-match {
  width: 100%;
  background: linear-gradient(135deg, var(--amber) 0%, var(--amber-light) 100%);
  color: #fff;
  border: none;
  border-radius: var(--radius);
  padding: .85rem;
  font-size: .95rem;
  font-weight: 700;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: .5rem;
  transition: opacity .2s, transform .15s;
  margin-top: .5rem;
}
.btn-match:hover   { opacity: .92; transform: translateY(-1px); }
.btn-match:disabled { opacity: .6; cursor: not-allowed; transform: none; }

.results-panel { min-height: 400px; }
.results-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 1.2rem; flex-wrap: wrap; gap: .5rem;
}
.results-header h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.35rem;
  color: var(--text-primary);
}
.results-count {
  background: var(--sand);
  border: 1px solid var(--border);
  border-radius: 999px;
  padding: .25rem .75rem;
  font-size: .8rem;
  font-weight: 700;
  color: var(--text-secondary);
}

.placeholder-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-muted);
}
.placeholder-state .icon { font-size: 3rem; margin-bottom: 1rem; opacity: .5; }
.placeholder-state h3 { font-size: 1.1rem; color: var(--text-secondary); margin-bottom: .4rem; }

.spinner-wrap {
  display: none;
  text-align: center;
  padding: 4rem 2rem;
}
.spinner {
  width: 44px; height: 44px;
  border: 3px solid var(--border);
  border-top-color: var(--amber);
  border-radius: 50%;
  animation: spin .7s linear infinite;
  margin: 0 auto 1rem;
}
@keyframes spin { to { transform: rotate(360deg); } }

.match-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.25rem;
}

.match-card {
  background: var(--paper);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 1.4rem;
  display: flex;
  flex-direction: column;
  gap: .85rem;
  box-shadow: var(--shadow);
  transition: box-shadow .25s, border-color .25s, transform .2s;
  animation: fadeUp .35s ease both;
}
.match-card:hover {
  border-color: var(--amber-light);
  box-shadow: 0 12px 32px rgba(224,112,32,.18);
  transform: translateY(-2px);
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

.card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: .5rem; }
.card-title { font-weight: 700; font-size: .95rem; line-height: 1.35; color: var(--text-primary); }
.card-client { font-size: .75rem; color: var(--text-muted); margin-top:.15rem; }

.label-badge {
  padding: .25rem .65rem;
  border-radius: 999px;
  font-size: .7rem;
  font-weight: 700;
  white-space: nowrap;
  flex-shrink: 0;
}
.label-excellent { background: rgba(47,125,87,.15);  color: var(--success); }
.label-good      { background: rgba(224,112,32,.15); color: var(--amber); }
.label-partial   { background: rgba(184,73,66,.12);  color: var(--danger); }

.score-bar-wrap { }
.score-top {
  display: flex; justify-content: space-between; align-items: center;
  font-size: .78rem; font-weight: 600; color: var(--text-secondary);
  margin-bottom: .35rem;
}
.score-number { font-size: 1.25rem; font-weight: 800; color: var(--amber); }
.score-bar-bg {
  background: var(--sand);
  border-radius: 999px;
  height: 7px;
  overflow: hidden;
}
.score-bar-fill {
  height: 100%;
  border-radius: 999px;
  transition: width 1s cubic-bezier(.4,0,.2,1);
}
.fill-excellent { background: linear-gradient(90deg, #2f7d57, #4aad7a); }
.fill-good      { background: linear-gradient(90deg, var(--amber), var(--amber-light)); }
.fill-partial   { background: linear-gradient(90deg, #b84942, #d96b5a); }

.reasons-list { list-style: none; display: flex; flex-direction: column; gap: .35rem; }
.reasons-list li {
  display: flex; align-items: flex-start; gap: .45rem;
  font-size: .8rem; color: var(--text-secondary); line-height: 1.4;
}
.reasons-list li::before {
  content: '+';
  color: var(--success);
  font-weight: 700;
  flex-shrink: 0;
  margin-top: .05rem;
}

.card-meta {
  display: flex; gap: 1rem; flex-wrap: wrap;
  border-top: 1px solid var(--border);
  padding-top: .75rem;
  font-size: .78rem;
}
.meta-item { display: flex; flex-direction: column; gap: .1rem; }
.meta-label { color: var(--text-muted); font-size: .7rem; text-transform: uppercase; font-weight: 600; letter-spacing: .04em; }
.meta-value { color: var(--text-primary); font-weight: 700; }

.card-actions { display: flex; gap: .6rem; }
.btn-view {
  flex: 1; text-align: center; text-decoration: none;
  background: var(--amber); color: #fff;
  padding: .55rem; border-radius: var(--radius);
  font-size: .82rem; font-weight: 700;
  transition: opacity .2s;
}
.btn-view:hover { opacity: .88; }
.btn-apply {
  flex: 1; text-align: center; text-decoration: none;
  border: 1px solid var(--amber); color: var(--amber);
  padding: .55rem; border-radius: var(--radius);
  font-size: .82rem; font-weight: 700;
  transition: background .2s, color .2s;
}
.btn-apply:hover { background: var(--amber-glow); }

.skills-tags { display: flex; flex-wrap: wrap; gap: .35rem; }
.skill-tag {
  background: var(--sand); color: var(--text-secondary);
  padding: .2rem .6rem; border-radius: 6px;
  font-size: .7rem; font-weight: 600;
}

.server-error {
  background: rgba(184,73,66,.08);
  border: 1px solid rgba(184,73,66,.25);
  border-radius: var(--radius);
  padding: 1.2rem 1.5rem;
  color: var(--danger);
  font-size: .9rem;
  display: flex; align-items: center; gap: .6rem;
}

.match-hero {
  position: relative;
  overflow: hidden;
  background:
    radial-gradient(circle at 16% 20%, rgba(224,112,32,.26), transparent 28%),
    radial-gradient(circle at 86% 12%, rgba(247,185,92,.18), transparent 24%),
    linear-gradient(135deg, #24130b 0%, #3a160b 48%, #120d0a 100%);
}
.match-hero::after {
  content: '';
  position: absolute;
  left: 50%;
  bottom: -42px;
  width: min(920px, 92vw);
  height: 86px;
  transform: translateX(-50%);
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 999px;
  filter: blur(.2px);
}
.match-hero > * { position: relative; z-index: 1; }
.match-hero h1 {
  letter-spacing: 0;
  text-shadow: 0 12px 34px rgba(0,0,0,.28);
}
.match-badge {
  box-shadow: 0 12px 32px rgba(224,112,32,.22);
  backdrop-filter: blur(8px);
}
.profile-card,
.results-panel {
  border-radius: 22px;
}
.profile-card {
  background:
    linear-gradient(180deg, rgba(255,255,255,.96), rgba(255,250,244,.92));
  box-shadow: 0 22px 55px rgba(60,35,16,.11);
}
.profile-card h2 {
  background: linear-gradient(135deg, var(--charcoal), #8a4216);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}
.results-panel {
  background: rgba(255,255,255,.54);
  border: 1px solid rgba(194,164,118,.26);
  padding: 1.25rem;
  box-shadow: 0 24px 70px rgba(60,35,16,.08);
}
.match-card {
  position: relative;
  overflow: hidden;
  border: 1px solid rgba(194,164,118,.34);
  background:
    linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,249,239,.96));
  box-shadow: 0 18px 45px rgba(52,31,12,.10);
}
.match-card::before {
  content: '';
  position: absolute;
  inset: 0 0 auto 0;
  height: 5px;
  background: linear-gradient(90deg, var(--amber), var(--amber-light), #f4d7a1);
}
.match-card::after {
  content: '';
  position: absolute;
  right: -54px;
  top: -54px;
  width: 145px;
  height: 145px;
  border-radius: 50%;
  background: rgba(224,112,32,.08);
}
.card-top,
.skills-tags,
.score-bar-wrap,
.reasons-list,
.card-meta,
.card-actions {
  position: relative;
  z-index: 1;
}
.card-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.08rem;
}
.card-client {
  display: none;
}
.label-badge {
  border: 1px solid rgba(255,255,255,.62);
  box-shadow: 0 8px 22px rgba(0,0,0,.07);
}
.score-bar-bg {
  height: 10px;
  background: rgba(62,39,22,.08);
}
.skills-tags {
  padding: .55rem;
  background: rgba(250,244,235,.72);
  border: 1px solid rgba(194,164,118,.22);
  border-radius: 14px;
}
.skill-tag {
  border-radius: 999px;
  background: #fff;
  border: 1px solid rgba(194,164,118,.32);
}
.card-meta {
  background: rgba(255,255,255,.64);
  border: 1px solid rgba(194,164,118,.22);
  border-radius: 14px;
  padding: .8rem;
}
.btn-view,
.btn-apply {
  border-radius: 12px;
}
.btn-view {
  background: linear-gradient(135deg, var(--amber), var(--amber-light));
  box-shadow: 0 10px 22px rgba(224,112,32,.20);
}
</style>

<div class="match-hero">
  <div class="match-badge"><i class="fas fa-brain"></i> Matching Intelligent</div>
  <h1>Trouvez votre offre ideale</h1>
  <p>Renseignez votre profil et notre moteur analyse toutes les offres actives pour vous.</p>
</div>

<div class="match-layout">

  <!-- Profile Form -->
  <div class="profile-card">
    <h2><i class="fas fa-user-circle" style="color:var(--amber)"></i> Votre Profil</h2>

    <form id="matchForm" novalidate>

      <div class="form-group">
        <label for="skills">
          <i class="fas fa-code"></i> Competences <span style="color:var(--danger)">*</span>
        </label>
        <textarea id="skills" name="skills"
                  placeholder="Ex : PHP, JavaScript, React, MySQL, Docker"></textarea>
        <div style="font-size:.72rem; color:var(--text-muted); margin-top:.25rem;">
          Separez vos competences par des virgules
        </div>
      </div>

      <div class="form-group">
        <label for="experience_years">
          <i class="fas fa-briefcase"></i> Annees d'experience <span style="color:var(--danger)">*</span>
        </label>
        <select id="experience_years" name="experience_years">
          <option value="">-- Selectionnez --</option>
          <option value="0">Moins de 1 an</option>
          <option value="1">1 an</option>
          <option value="2">2 ans</option>
          <option value="3">3 ans</option>
          <option value="4">4 ans</option>
          <option value="5">5 ans</option>
          <option value="7">6 - 8 ans</option>
          <option value="10">9 ans et plus</option>
        </select>
      </div>

      <div class="form-group">
        <label for="preferred_domain">
          <i class="fas fa-layer-group"></i> Domaine prefere
        </label>
        <input type="text" id="preferred_domain" name="preferred_domain"
               placeholder="Ex : Developpement Web, Design, Data Science" autocomplete="off">
      </div>

      <button type="submit" class="btn-match" id="matchBtn">
        <i class="fas fa-magic"></i> Analyser mes correspondances
      </button>
    </form>
  </div>

  <!-- Results Panel -->
  <div class="results-panel">
    <div class="results-header">
      <h2><i class="fas fa-star" style="color:var(--amber); font-size:1rem"></i> Offres correspondantes</h2>
      <span class="results-count" id="resultsCount" style="display:none">0 resultat</span>
    </div>

    <div class="placeholder-state" id="placeholderState">
      <div class="icon"><i class="fas fa-bullseye"></i></div>
      <h3>Votre analyse attend</h3>
      <p>Remplissez votre profil a gauche et lancez la recherche pour voir vos offres classees par compatibilite.</p>
    </div>

    <div class="spinner-wrap" id="spinnerWrap">
      <div class="spinner"></div>
      <p style="color:var(--text-muted); font-size:.9rem">Analyse en cours...</p>
    </div>

    <div class="server-error" id="serverError" style="display:none">
      <i class="fas fa-exclamation-circle"></i>
      <span id="serverErrorMsg">Une erreur est survenue.</span>
    </div>

    <div class="match-grid" id="resultsGrid"></div>
  </div>

</div>

<script>
(function () {

  /* Helpers */
  function esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
  }

  function swalError(title, text, focusId) {
    Swal.fire({
      icon: 'error',
      title: title,
      text: text,
      confirmButtonColor: '#e07020',
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

  /* JS Validation */
  function validateForm() {
    clearErrors();

    const skills = document.getElementById('skills').value.trim();
    const exp    = document.getElementById('experience_years').value;
    const domain = document.getElementById('preferred_domain').value.trim();


    // Competences
    if (skills === '') {
      swalError('Competences requises', '', 'skills');
      return false;
    }
    const skillList = skills.split(',').map(s => s.trim()).filter(Boolean);
    if (skillList.length === 0) {
      swalError('Competences invalides', '', 'skills');
      return false;
    }
    for (const sk of skillList) {
      if (sk.length < 2) {
        swalError('Competence trop courte', `La competence "${sk}" doit faire au moins 2 caracteres.`, 'skills');
        return false;
      }
      if (sk.length > 50) {
        swalError('Competence trop longue', `La competence "${sk}" ne peut pas depasser 50 caracteres.`, 'skills');
        return false;
      }
    }

    // Experience
    if (exp === '') {
      swalError('Experience requise', "Veuillez selectionner votre nombre d'annees d'experience.", 'experience_years');
      return false;
    }

    // Domaine prefere
    if (domain !== '' && domain.length < 3) {
      swalError('Domaine invalide', 'Le domaine prefere doit contenir au moins 3 caracteres.', 'preferred_domain');
      return false;
    }
    if (domain.length > 100) {
      swalError('Domaine trop long', 'Le domaine prefere ne peut pas depasser 100 caracteres.', 'preferred_domain');
      return false;
    }

    return true;
  }

  /* Card builder */
  function labelClass(label) {
    if (label === 'Excellent Match') return 'label-excellent';
    if (label === 'Good Match')      return 'label-good';
    return 'label-partial';
  }
  function fillClass(label) {
    if (label === 'Excellent Match') return 'fill-excellent';
    if (label === 'Good Match')      return 'fill-good';
    return 'fill-partial';
  }
  function labelFr(label) {
    if (label === 'Excellent Match') return 'Excellent';
    if (label === 'Good Match')      return 'Bon match';
    return 'Partiel';
  }

  function buildCard(offer, index) {
    const skills = (offer.competences_requises || '')
      .split(',').map(s => s.trim()).filter(Boolean)
      .slice(0, 4)
      .map(s => `<span class="skill-tag">${esc(s)}</span>`)
      .join('');

    const reasons = offer.match_reasons.map(r => `<li>${esc(r)}</li>`).join('');

    const card = document.createElement('div');
    card.className = 'match-card';
    card.style.animationDelay = (index * 60) + 'ms';

    card.innerHTML = `
      <div class="card-top">
        <div>
          <div class="card-title">${esc(offer.titre)}</div>
          <div class="card-client"><i class="fas fa-user" style="font-size:.7rem"></i> ${esc(offer.nom_client)}</div>
        </div>
        <span class="label-badge ${labelClass(offer.recommendation_label)}">${labelFr(offer.recommendation_label)}</span>
      </div>

      ${skills ? `<div class="skills-tags">${skills}</div>` : ''}

      <div class="score-bar-wrap">
        <div class="score-top">
          <span>Score de compatibilite</span>
          <span class="score-number" id="scoreNum_${offer.offer_id}">0%</span>
        </div>
        <div class="score-bar-bg">
          <div class="score-bar-fill ${fillClass(offer.recommendation_label)}"
               id="scoreBar_${offer.offer_id}" style="width:0%"></div>
        </div>
      </div>

      <ul class="reasons-list">${reasons}</ul>

      <div class="card-meta">
        <div class="meta-item">
          <span class="meta-label">Budget</span>
          <span class="meta-value">${Number(offer.budget).toLocaleString('fr-FR')} DT</span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Niveau</span>
          <span class="meta-value" style="text-transform:capitalize">${esc(offer.niveau_requis)}</span>
        </div>
        <div class="meta-item">
          <span class="meta-label">Delai</span>
          <span class="meta-value">${offer.delai_publication} jours</span>
        </div>
      </div>

      <div class="card-actions">
        <a href="index.php?page=offre_detail&id=${offer.offer_id}" class="btn-view">
          <i class="fas fa-eye"></i> Voir l'offre
        </a>
        <a href="index.php?page=candidater&id=${offer.offer_id}" class="btn-apply">
          Candidater
        </a>
      </div>
    `;

    return card;
  }

  function animateScore(numId, barId, target, delay) {
    setTimeout(() => {
      let current = 0;
      const step  = Math.ceil(target / 40);
      const iv    = setInterval(() => {
        current = Math.min(current + step, target);
        const n = document.getElementById(numId);
        const b = document.getElementById(barId);
        if (n) n.textContent = current + '%';
        if (b) b.style.width = current + '%';
        if (current >= target) clearInterval(iv);
      }, 18);
    }, delay);
  }

  /* Form submit */
  const form        = document.getElementById('matchForm');
  const btn         = document.getElementById('matchBtn');
  const grid        = document.getElementById('resultsGrid');
  const spinner     = document.getElementById('spinnerWrap');
  const placeholder = document.getElementById('placeholderState');
  const serverError = document.getElementById('serverError');
  const serverMsg   = document.getElementById('serverErrorMsg');
  const countEl     = document.getElementById('resultsCount');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    if (!validateForm()) return;

    placeholder.style.display = 'none';
    serverError.style.display = 'none';
    grid.innerHTML = '';
    countEl.style.display = 'none';
    spinner.style.display = 'block';
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyse...';

    try {
      const res  = await fetch('index.php?page=job_match_api', {
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

      if (!Array.isArray(json) || json.length === 0) {
        placeholder.style.display = 'block';
        placeholder.innerHTML = `
          <div class="icon"><i class="fas fa-inbox"></i></div>
          <h3>Aucune offre active trouvee</h3>
          <p>Il n'y a pas encore d'offres actives a analyser. Revenez plus tard !</p>
        `;
        return;
      }

      countEl.textContent   = json.length + ' resultat' + (json.length > 1 ? 's' : '');
      countEl.style.display = 'inline-block';

      const scoreData = [];
      json.forEach((offer, i) => {
        const card = buildCard(offer, i);
        grid.appendChild(card);
        scoreData.push({
          numId:  'scoreNum_' + offer.offer_id,
          barId:  'scoreBar_' + offer.offer_id,
          target: offer.match_score,
          delay:  i * 80
        });
      });

      setTimeout(() => {
        scoreData.forEach(({ numId, barId, target, delay }) => animateScore(numId, barId, target, delay));
      }, 100);

    } catch (err) {
      spinner.style.display     = 'none';
      serverMsg.textContent     = 'Erreur de connexion : ' + err.message;
      serverError.style.display = 'flex';
    } finally {
      btn.disabled  = false;
      btn.innerHTML = '<i class="fas fa-magic"></i> Analyser mes correspondances';
    }
  });

  // Clear red border on input change
  document.querySelectorAll('#matchForm input, #matchForm textarea, #matchForm select').forEach(el => {
    el.addEventListener('input', () => el.classList.remove('input-error'));
    el.addEventListener('change', () => el.classList.remove('input-error'));
  });

})();
</script>

<?php include __DIR__ . '/footer.php'; ?>
