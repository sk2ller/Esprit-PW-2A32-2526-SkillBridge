<?php
$canManage = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Idees liees au brainstorming - SkillBridge</title>
    <link rel="stylesheet" href="Views/assets/css/skillbridge.css">
    <link rel="stylesheet" href="Views/assets/css/enhanced-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
    <style>
        .idea-score-dialog { width: min(880px, calc(100vw - 2rem)); border: 1px solid rgba(223, 209, 189, .95); border-radius: 18px; padding: 0; background: #fffaf4; color: #1f1f23; box-shadow: 0 26px 70px rgba(31, 31, 35, .22); }
        .idea-score-dialog::backdrop { background: rgba(22, 20, 18, .54); }
        .idea-score-head, .idea-score-body { padding: 1.2rem 1.4rem; }
        .idea-score-head { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(223, 209, 189, .9); }
        .idea-score-title { font-weight: 800; font-size: 1.1rem; }
        .idea-score-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .75rem; margin-bottom: 1rem; }
        .idea-score-card { border: 1px solid rgba(223, 209, 189, .9); border-radius: 14px; padding: .9rem; background: rgba(255,255,255,.72); text-align: center; }
        .idea-score-label { color: #6f665c; font-size: .72rem; text-transform: uppercase; font-weight: 800; }
        .idea-score-value { font-size: 1.35rem; font-weight: 900; margin-top: .2rem; }
        .idea-score-list { margin: .45rem 0 0; padding-left: 1.1rem; color: #6f665c; }
        .front-inline-alert { margin-bottom: 1rem; border-radius: 14px; padding: .85rem 1rem; font-weight: 800; }
        .front-inline-alert.danger { background: #fef3f2; color: #b42318; }
        .translation-result { display: grid; gap: .8rem; }
        .translation-card { border: 1px solid rgba(223, 209, 189, .9); border-radius: 14px; background: #fff; padding: 1rem; }
        .translation-label { color: #6f665c; font-size: .76rem; font-weight: 800; text-transform: uppercase; margin-bottom: .3rem; }
        .suggestion-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .8rem; }
        .suggestion-card { border: 1px solid rgba(223, 209, 189, .9); border-radius: 14px; background: #fff; padding: 1rem; }
        .suggestion-card.full { grid-column: 1 / -1; }
        .suggestion-label { color: #6f665c; font-size: .76rem; font-weight: 800; text-transform: uppercase; margin-bottom: .3rem; }
        .suggestion-list { margin: .4rem 0 0; padding-left: 1.1rem; color: #3b3631; }
        @media (max-width: 760px) { .idea-score-grid, .suggestion-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="skillbridge-front">
<?php include __DIR__ . '/partials/front_navbar.php'; ?>
<div class="front-page"><div class="dashboard-shell">
    <?php include __DIR__ . '/partials/brainstorming_sidebar.php'; ?>
    <main class="page-shell" style="flex:1;">
    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">Espace idees</span>
            <h1><?= htmlspecialchars($brainstorming['titre']) ?></h1>
            <p><?= htmlspecialchars($brainstorming['description']) ?></p>
            <div class="hero-inline-meta">
                <span class="pill">Date debut : <?= htmlspecialchars($brainstorming['date_debut']) ?></span>
                <span class="pill"><?= (int) $brainstorming['accepted'] === 1 ? 'Brainstorming accepte' : 'En attente de validation' ?></span>
                <span class="pill">Porteur : <?= htmlspecialchars(trim(($brainstorming['user_prenom'] ?? '') . ' ' . ($brainstorming['user_nom'] ?? ''))) ?></span>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="container">
            <div class="section-toolbar">
                <div>
                    <h2>Idees rattachees</h2>
                    <p>Chaque carte presente une proposition liee a ce brainstorming, avec des actions adaptees a l auteur ou a l admin.</p>
                </div>
                <?php if ($canManage): ?>
                    <a href="index.php?action=add_idee&brainstorming_id=<?= (int) $brainstorming['id'] ?>" class="btn btn-primary">Ajouter une idee</a>
                <?php endif; ?>
            </div>

            <?php if (empty($idees)): ?>
                <div class="empty-state">
                    <h3>Aucune idee pour le moment</h3>
                    <p>Commencez par ajouter la premiere idee liee a ce brainstorming.</p>
                </div>
            <?php else: ?>
                <div class="idea-grid">
                    <?php foreach ($idees as $idee): ?>
                        <?php $canEditIdea = $isAdmin || ($currentUserId > 0 && $currentUserId === (int) $idee['user_id']); ?>
                        <article class="idea-card">
                            <div class="idea-card-top">
                                <div>
                                    <span class="idea-tag"><?= htmlspecialchars($idee['categorie']) ?></span>
                                    <h3><?= htmlspecialchars($idee['titre']) ?></h3>
                                </div>
                                <span class="idea-status status-<?= htmlspecialchars($idee['statut']) ?>"><?= htmlspecialchars(str_replace('_', ' ', $idee['statut'])) ?></span>
                            </div>
                            <p><?= nl2br(htmlspecialchars($idee['contenu'])) ?></p>
                            <div class="idea-meta">
                                <span>Priorite : <?= htmlspecialchars($idee['priorite']) ?></span>
                                <span>Votes : <?= (int) $idee['votes'] ?></span>
                                <span>Auteur : <?= htmlspecialchars(trim(($idee['user_prenom'] ?? '') . ' ' . ($idee['user_nom'] ?? ''))) ?></span>
                            </div>
                            <div class="action-row">
                                <button type="button" class="btn btn-primary" onclick="scoreIdee(<?= (int) $idee['id'] ?>)">Score idee</button>
                                <button type="button" class="btn btn-secondary page-btn-secondary" onclick="suggestIdee(<?= (int) $idee['id'] ?>)">Suggestions</button>
                                <button type="button" class="btn btn-secondary page-btn-secondary" onclick="translateIdee(<?= (int) $idee['id'] ?>)">Traduire</button>
                            </div>
                            <?php if ($canEditIdea): ?>
                                <div class="action-row">
                                    <a href="index.php?action=edit_idee&id=<?= (int) $idee['id'] ?>" class="btn btn-secondary page-btn-secondary">Modifier</a>
                                    <a href="index.php?action=delete_idee&id=<?= (int) $idee['id'] ?>&brainstorming_id=<?= (int) $brainstorming['id'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer cette idee ?')">Supprimer</a>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
</div></div>

<dialog class="idea-score-dialog" id="ideaScoreDialog">
    <div class="idea-score-head">
        <div class="idea-score-title">Score de l idee</div>
        <button class="btn btn-secondary page-btn-secondary" type="button" onclick="closeScoreDialog()">Fermer</button>
    </div>
    <div class="idea-score-body" id="ideaScoreContent">Chargement...</div>
</dialog>

<dialog class="idea-score-dialog" id="ideaSuggestionDialog">
    <div class="idea-score-head">
        <div>
            <div class="idea-score-title">Suggestions d amelioration</div>
            <div id="suggestionSource" style="color:#6f665c; font-size:.85rem; margin-top:.2rem;">Analyse de l idee</div>
        </div>
        <button class="btn btn-secondary page-btn-secondary" type="button" onclick="closeSuggestionDialog()">Fermer</button>
    </div>
    <div class="idea-score-body" id="suggestionContent">Chargement...</div>
</dialog>

<dialog class="idea-score-dialog" id="ideaTranslationDialog">
    <div class="idea-score-head">
        <div>
            <div class="idea-score-title">Traduction de l idee</div>
            <div id="translationSource" style="color:#6f665c; font-size:.85rem; margin-top:.2rem;">Service de traduction</div>
        </div>
        <button class="btn btn-secondary page-btn-secondary" type="button" onclick="closeTranslationDialog()">Fermer</button>
    </div>
    <div class="idea-score-body">
        <div class="action-row" style="margin-bottom:1rem;">
            <select id="translationLanguage" class="btn btn-secondary page-btn-secondary" style="border:1px solid #dfd1bd;">
                <option value="EN">English</option>
                <option value="FR">Francais</option>
                <option value="AR">Arabic</option>
            </select>
            <button type="button" class="btn btn-primary" id="translationRetryBtn">Retraduire</button>
        </div>
        <div id="translationContent">Chargement...</div>
    </div>
</dialog>

<footer class="footer">
    <p>&copy; 2026 SkillBridge</p>
</footer>
<script>
function escapeHtml(value) { return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;'); }
function readJsonResponse(response) { return response.text().then(text => JSON.parse(text.replace(/^\uFEFF+/, '').trim())); }
function closeScoreDialog() { document.getElementById('ideaScoreDialog').close(); }
function closeTranslationDialog() { document.getElementById('ideaTranslationDialog').close(); }
function closeSuggestionDialog() { document.getElementById('ideaSuggestionDialog').close(); }
function renderScoreList(items) { if (!Array.isArray(items) || items.length === 0) return '<p style="color:#6f665c; margin:0;">Aucun element detaille.</p>'; return '<ul class="idea-score-list">' + items.map(item => '<li>' + escapeHtml(item) + '</li>').join('') + '</ul>'; }
function scoreCard(score, label) { return '<div class="idea-score-card"><div class="idea-score-label">' + escapeHtml(label) + '</div><div class="idea-score-value">' + escapeHtml(score) + '/100</div></div>'; }
function scoreIdee(id) {
    const dialog = document.getElementById('ideaScoreDialog');
    const content = document.getElementById('ideaScoreContent');
    content.innerHTML = '<p style="margin:0;">Analyse en cours...</p>';
    dialog.showModal();
    const formData = new FormData();
    formData.append('action', 'score_idee_ai');
    formData.append('id', id);
    fetch('?action=list_idees&brainstorming_id=<?= (int) $brainstorming['id'] ?>', { method: 'POST', body: formData })
        .then(readJsonResponse)
        .then(data => {
            if (!data.success) {
                content.innerHTML = '<div class="front-inline-alert danger">' + escapeHtml(data.message || 'Erreur analyse.') + '</div>';
                return;
            }
            const s = data.scoring;
            content.innerHTML = '<div class="idea-score-grid">' + scoreCard(s.clarity, 'Clarte') + scoreCard(s.innovation, 'Innovation') + scoreCard(s.feasibility, 'Faisabilite') + scoreCard(s.positivity, 'Positivite') + scoreCard(s.confidence, 'Confiance') + '</div><div class="idea-score-card" style="margin-bottom:1rem;"><div class="idea-score-label">Score global</div><div class="idea-score-value">' + escapeHtml(s.global_score) + '/100</div></div><p style="color:#6f665c; font-weight:800;">' + escapeHtml(s.source || 'Analyse') + '</p><h4>Resume</h4><p>' + escapeHtml(s.summary) + '</p><h4>Forces</h4>' + renderScoreList(s.strengths) + '<h4>Risques</h4>' + renderScoreList(s.risks) + '<h4>Recommandation</h4><p>' + escapeHtml(s.recommendation) + '</p>';
        })
        .catch(() => { content.innerHTML = '<div class="front-inline-alert danger">Erreur reseau pendant l analyse.</div>'; });
}
function renderSuggestionList(items) { if (!Array.isArray(items) || items.length === 0) return '<p style="margin:0; color:#6f665c;">Aucune suggestion detaillee.</p>'; return '<ul class="suggestion-list">' + items.map(item => '<li>' + escapeHtml(item) + '</li>').join('') + '</ul>'; }
function renderSuggestions(data) {
    const s = data.suggestions || {};
    document.getElementById('suggestionSource').textContent = data.source || 'Analyse de l idee';
    document.getElementById('suggestionContent').innerHTML = '<div class="suggestion-grid">'
        + '<div class="suggestion-card full"><div class="suggestion-label">Titre ameliore</div><div>' + escapeHtml(s.improved_title || '-') + '</div></div>'
        + '<div class="suggestion-card full"><div class="suggestion-label">Resume ameliore</div><div>' + escapeHtml(s.improved_summary || '-') + '</div></div>'
        + '<div class="suggestion-card"><div class="suggestion-label">Utilisateur cible</div><div>' + escapeHtml(s.target_user || '-') + '</div></div>'
        + '<div class="suggestion-card"><div class="suggestion-label">Probleme</div><div>' + escapeHtml(s.problem || '-') + '</div></div>'
        + '<div class="suggestion-card full"><div class="suggestion-label">Valeur proposee</div><div>' + escapeHtml(s.value_proposition || '-') + '</div></div>'
        + '<div class="suggestion-card"><div class="suggestion-label">Prochaines etapes</div>' + renderSuggestionList(s.next_steps) + '</div>'
        + '<div class="suggestion-card"><div class="suggestion-label">Questions a clarifier</div>' + renderSuggestionList(s.questions) + '</div>'
        + '</div>';
}
function suggestIdee(id) {
    const dialog = document.getElementById('ideaSuggestionDialog');
    const content = document.getElementById('suggestionContent');
    document.getElementById('suggestionSource').textContent = 'Gemini API';
    content.innerHTML = 'Generation des suggestions...';
    dialog.showModal();
    const formData = new FormData();
    formData.append('action', 'suggest_idee_improvements');
    formData.append('id', id);
    fetch('?action=list_idees&brainstorming_id=<?= (int) $brainstorming['id'] ?>', { method: 'POST', body: formData })
        .then(readJsonResponse)
        .then(data => {
            if (!data.success) {
                document.getElementById('suggestionSource').textContent = 'Configuration API requise';
                content.innerHTML = '<div class="front-inline-alert danger"><strong>Suggestions indisponibles</strong><br>' + escapeHtml(data.message || 'Ajoutez une cle Gemini API pour utiliser cette fonctionnalite.') + '</div>';
                return;
            }
            renderSuggestions(data);
        })
        .catch(() => { content.innerHTML = '<div class="front-inline-alert danger">Erreur reseau pendant la generation.</div>'; });
}
let currentTranslationId = null;
function renderTranslation(data) {
    const cards = Object.entries(data.translated || {}).map(([field, value]) => {
        const label = field === 'contenu' ? 'Contenu' : 'Titre';
        return '<div class="translation-card"><div class="translation-label">' + escapeHtml(label) + '</div><div>' + escapeHtml(value).replaceAll('\n', '<br>') + '</div></div>';
    }).join('');
    document.getElementById('translationSource').textContent = data.source || 'Service de traduction';
    document.getElementById('translationContent').innerHTML = '<div class="translation-result">' + cards + '</div>';
}
function translateIdee(id) {
    currentTranslationId = id;
    const dialog = document.getElementById('ideaTranslationDialog');
    const content = document.getElementById('translationContent');
    content.innerHTML = 'Traduction en cours...';
    if (!dialog.open) dialog.showModal();
    const formData = new FormData();
    formData.append('entity', 'idee');
    formData.append('id', id);
    formData.append('target_language', document.getElementById('translationLanguage').value);
    fetch('?action=translate_entity', { method: 'POST', body: formData })
        .then(readJsonResponse)
        .then(data => {
            if (!data.success) {
                content.innerHTML = '<div class="front-inline-alert danger">' + escapeHtml(data.message || 'Traduction impossible.') + '</div>';
                return;
            }
            renderTranslation(data);
        })
        .catch(() => { content.innerHTML = '<div class="front-inline-alert danger">Erreur reseau pendant la traduction.</div>'; });
}
document.getElementById('translationRetryBtn').addEventListener('click', function () {
    if (currentTranslationId) translateIdee(currentTranslationId);
});
</script>
</body>
</html>

