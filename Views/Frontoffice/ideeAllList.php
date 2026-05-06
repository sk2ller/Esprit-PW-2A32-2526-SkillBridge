<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toutes les Idees - SkillBridge</title>
    <link rel="stylesheet" href="Views/assets/css/skillbridge.css">
    <link rel="stylesheet" href="Views/assets/css/enhanced-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
    <style id="brainstorming-dashboard-fix">
        .front-page.brainstorming-dashboard {
            max-width: 1440px;
            width: 100%;
            padding: 1.6rem 2rem 2.4rem;
        }

        .brainstorming-dashboard > .dashboard-shell {
            display: grid !important;
            grid-template-columns: 250px minmax(0, 1fr) !important;
            gap: 1.5rem;
            align-items: start;
            width: 100%;
        }

        .brainstorming-sidebar {
            grid-column: 1;
            width: 250px;
            position: sticky;
            top: calc(var(--nav-height) + 1rem);
            align-self: start;
            background: #1f1f23;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 18px;
            box-shadow: 0 14px 34px rgba(31, 31, 35, .14);
            overflow: hidden;
        }

        .brainstorming-sidebar-head {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .brainstorming-sidebar-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: rgba(224, 112, 32, .18);
            color: #f08a3b;
        }

        .brainstorming-sidebar-title {
            color: #fff;
            font-weight: 800;
            line-height: 1.1;
        }

        .brainstorming-sidebar-subtitle {
            color: rgba(255, 255, 255, .58);
            font-size: .82rem;
            margin-top: .18rem;
        }

        .brainstorming-nav {
            display: grid;
            gap: .35rem;
            padding: .75rem;
        }

        .brainstorming-nav-link {
            min-height: 44px;
            display: grid;
            grid-template-columns: 22px minmax(0, 1fr);
            align-items: center;
            gap: .65rem;
            padding: .72rem .8rem;
            border-radius: 12px;
            color: rgba(255, 255, 255, .72);
            text-decoration: none;
            font-weight: 700;
            line-height: 1.2;
        }

        .brainstorming-nav-link:hover,
        .brainstorming-nav-link.active {
            background: #fff8ef;
            color: #1f1f23;
        }

        .brainstorming-nav-link i {
            color: inherit;
            text-align: center;
        }

        .brainstorming-dashboard .feature-main {
            grid-column: 2;
            min-width: 0;
            width: 100%;
        }

        .brainstorming-dashboard .page-shell {
            min-height: auto;
            width: 100%;
        }

        .brainstorming-dashboard .page-hero {
            border-radius: 22px;
            padding: 2rem;
            margin: 0 0 1.35rem;
            overflow: hidden;
        }

        .brainstorming-dashboard .page-section {
            padding: 0;
        }

        .brainstorming-dashboard .container {
            max-width: none !important;
            width: 100%;
            padding-left: 0;
            padding-right: 0;
        }

        .brainstorming-dashboard .section-toolbar {
            gap: 1rem;
            flex-wrap: wrap;
        }

        .brainstorming-dashboard .front-table-wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
        }

        .brainstorming-dashboard .front-table {
            width: 100%;
            min-width: 760px;
        }

        .brainstorming-dashboard .idea-grid {
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }

        .brainstorming-dashboard .idea-card,
        .brainstorming-dashboard .empty-state,
        .brainstorming-dashboard .front-table-wrap {
            min-width: 0;
        }

        .idea-edit-dialog {
            width: min(880px, calc(100vw - 2rem));
            border: 1px solid rgba(223, 209, 189, .95);
            border-radius: 18px;
            padding: 0;
            background: #fffaf4;
            color: #1f1f23;
            box-shadow: 0 26px 70px rgba(31, 31, 35, .22);
        }

        .idea-edit-dialog::backdrop {
            background: rgba(22, 20, 18, .54);
        }

        .idea-dialog-head,
        .idea-dialog-body,
        .idea-dialog-foot {
            padding: 1.2rem 1.4rem;
        }

        .idea-dialog-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(223, 209, 189, .9);
        }

        .idea-dialog-title {
            font-weight: 800;
            font-size: 1.1rem;
        }

        .idea-dialog-foot {
            display: flex;
            justify-content: flex-end;
            gap: .7rem;
            border-top: 1px solid rgba(223, 209, 189, .9);
        }

        .idea-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .idea-field {
            display: flex;
            flex-direction: column;
            gap: .35rem;
        }

        .idea-field.full {
            grid-column: 1 / -1;
        }

        .idea-field label {
            font-weight: 800;
            font-size: .86rem;
        }

        .idea-field input,
        .idea-field select,
        .idea-field textarea {
            border: 1px solid #dfd1bd;
            border-radius: 14px;
            padding: .8rem .9rem;
            background: #fff;
            color: #1f1f23;
            font: inherit;
        }

        .idea-field textarea {
            min-height: 140px;
            resize: vertical;
        }

        .field-error {
            color: #b42318;
            font-size: .78rem;
            font-weight: 800;
            line-height: 1.35;
            min-height: 1rem;
        }

        .idea-field.has-error input,
        .idea-field.has-error select,
        .idea-field.has-error textarea {
            border-color: #d92d20;
            box-shadow: 0 0 0 3px rgba(217, 45, 32, .12);
        }

        .front-inline-alert {
            margin-bottom: 1rem;
            border-radius: 14px;
            padding: .85rem 1rem;
            font-weight: 800;
        }

        .front-inline-alert.success {
            background: #ecfdf3;
            color: #027a48;
        }

        .front-inline-alert.danger {
            background: #fef3f2;
            color: #b42318;
        }

        .moderation-alert {
            display: grid;
            grid-template-columns: 40px minmax(0, 1fr);
            gap: .8rem;
            align-items: start;
            border: 1px solid rgba(217, 45, 32, .18);
            border-left: 5px solid #d92d20;
            border-radius: 14px;
            background: #fff7f5;
            color: #7a271a;
            padding: .9rem 1rem;
            margin-bottom: 1rem;
        }

        .moderation-alert-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            background: #fee4e2;
            color: #b42318;
        }

        .moderation-alert-title {
            font-weight: 900;
            margin-bottom: .15rem;
        }

        .moderation-alert-text {
            margin: 0;
            color: #912018;
            line-height: 1.45;
        }

        .idea-score-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .idea-score-card {
            border: 1px solid rgba(223, 209, 189, .9);
            border-radius: 14px;
            padding: .9rem;
            background: rgba(255, 255, 255, .72);
            text-align: center;
        }

        .idea-score-label {
            color: #6f665c;
            font-size: .72rem;
            text-transform: uppercase;
            font-weight: 800;
        }

        .idea-score-value {
            font-size: 1.35rem;
            font-weight: 900;
            margin-top: .2rem;
        }

        .idea-score-list {
            margin: .45rem 0 0;
            padding-left: 1.1rem;
            color: #6f665c;
        }

        .translation-result {
            display: grid;
            gap: .8rem;
        }

        .translation-card {
            border: 1px solid rgba(223, 209, 189, .9);
            border-radius: 14px;
            background: #fff;
            padding: 1rem;
        }

        .translation-label {
            color: #6f665c;
            font-size: .76rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: .3rem;
        }

        .suggestion-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .8rem;
        }

        .suggestion-card {
            border: 1px solid rgba(223, 209, 189, .9);
            border-radius: 14px;
            background: #fff;
            padding: 1rem;
        }

        .suggestion-card.full {
            grid-column: 1 / -1;
        }

        .suggestion-label {
            color: #6f665c;
            font-size: .76rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: .3rem;
        }

        .suggestion-list {
            margin: .4rem 0 0;
            padding-left: 1.1rem;
            color: #3b3631;
        }

        @media (max-width: 980px) {
            .front-page.brainstorming-dashboard {
                padding: 1.25rem;
            }

            .brainstorming-dashboard > .dashboard-shell {
                grid-template-columns: 1fr !important;
            }

            .brainstorming-sidebar,
            .brainstorming-dashboard .feature-main {
                grid-column: 1;
                width: 100%;
            }

            .brainstorming-sidebar {
                position: static;
            }

            .brainstorming-dashboard .page-hero {
                padding: 1.5rem;
            }

            .brainstorming-dashboard .front-table {
                min-width: 680px;
            }

            .idea-score-grid {
                grid-template-columns: 1fr;
            }

            .suggestion-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="skillbridge-front">
<?php include __DIR__ . '/partials/front_navbar.php'; ?>
<div class="front-page brainstorming-dashboard"><div class="dashboard-shell brainstorming-layout">
    <?php include __DIR__ . '/partials/brainstorming_sidebar.php'; ?>
    <main class="page-shell feature-main">
    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">Catalogue des idees</span>
            <h1>Toutes les idees</h1>
            <p>Une vue plus propre et plus lisible pour parcourir les propositions sans exposer les actions sensibles aux mauvais profils.</p>
        </div>
    </section>

    <section class="page-section">
        <div class="container">
            <!-- Filter and Search Section -->
            <div style="background: #f8f8f8; border-radius: 12px; padding: 1.2rem; margin-bottom: 1.5rem;">
                <form method="GET" action="index.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; align-items: flex-end;">
                    <input type="hidden" name="action" value="all_idees">
                    <div style="display: flex; flex-direction: column; gap: 0.4rem; grid-column: 1 / -1;">
                        <label style="font-weight: 600; font-size: 0.9rem;">Rechercher</label>
                        <input type="text" name="search" placeholder="Titre, description, categorie, brainstorming..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="padding: 0.6rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem; width: 100%;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                        <label style="font-weight: 600; font-size: 0.9rem;">Statut</label>
                        <select name="status" style="padding: 0.6rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem;">
                            <option value="">Tous les statuts</option>
                            <option value="proposee" <?= ($_GET['status'] ?? '') === 'proposee' ? 'selected' : '' ?>>Proposee</option>
                            <option value="en_etude" <?= ($_GET['status'] ?? '') === 'en_etude' ? 'selected' : '' ?>>En etude</option>
                            <option value="approuvee" <?= ($_GET['status'] ?? '') === 'approuvee' ? 'selected' : '' ?>>Approuvee</option>
                            <option value="rejetee" <?= ($_GET['status'] ?? '') === 'rejetee' ? 'selected' : '' ?>>Rejetee</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                        <label style="font-weight: 600; font-size: 0.9rem;">Priorite</label>
                        <select name="priorite" style="padding: 0.6rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem;">
                            <option value="">Toutes les priorites</option>
                            <option value="faible" <?= ($_GET['priorite'] ?? '') === 'faible' ? 'selected' : '' ?>>Faible</option>
                            <option value="moyenne" <?= ($_GET['priorite'] ?? '') === 'moyenne' ? 'selected' : '' ?>>Moyenne</option>
                            <option value="haute" <?= ($_GET['priorite'] ?? '') === 'haute' ? 'selected' : '' ?>>Haute</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                        <label style="font-weight: 600; font-size: 0.9rem;">Categorie</label>
                        <select name="categorie" style="padding: 0.6rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem;">
                            <option value="">Toutes les categories</option>
                            <?php
                            // Get unique categories from all available ideas
                            $categoriesSet = [];
                            if (isset($idees) && is_array($idees)) {
                                foreach ($idees as $idee) {
                                    $cat = $idee['categorie'] ?? 'General';
                                    $categoriesSet[$cat] = true;
                                }
                            }
                            $categories = array_keys($categoriesSet);
                            sort($categories);
                            foreach ($categories as $cat):
                            ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= ($_GET['categorie'] ?? '') === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display: flex; gap: 0.5rem; grid-column: 1 / -1;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">Filtrer</button>
                        <a href="index.php?action=all_idees" class="btn btn-secondary page-btn-secondary" style="flex: 1; text-align: center;">Reinitialiser</a>
                    </div>
                </form>
            </div>

            <?php if (empty($idees)): ?>
                <div class="empty-state">
                    <h3>Aucune idee disponible</h3>
                    <p>Les idees apparaitront ici des qu elles seront ajoutees.</p>
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
                                <span>Brainstorming : <?= htmlspecialchars($idee['brainstorming_titre']) ?></span>
                            </div>
                            <div class="action-row">
                                <a href="?action=brainstorming_list" class="btn btn-secondary page-btn-secondary">Voir le brainstorming</a>
                                <button type="button" class="btn btn-primary" onclick="scoreAllIdee(<?= (int) $idee['id'] ?>)">Score idee</button>
                                <button type="button" class="btn btn-secondary page-btn-secondary" onclick="suggestAllIdee(<?= (int) $idee['id'] ?>)">Suggestions</button>
                                <button type="button" class="btn btn-secondary page-btn-secondary" onclick="translateAllIdee(<?= (int) $idee['id'] ?>)">Traduire</button>
                                <?php if ($canEditIdea): ?>
                                    <button type="button" class="btn btn-primary" onclick="openAllIdeeEdit(<?= (int) $idee['id'] ?>)">Modifier</button>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
</div></div>


<dialog class="idea-edit-dialog" id="allIdeeEditDialog">
    <form id="allIdeeEditForm">
        <input type="hidden" name="id" id="all_edit_id">
        <div class="idea-dialog-head">
            <div class="idea-dialog-title">Modifier l idee</div>
            <button class="btn btn-secondary page-btn-secondary" type="button" onclick="closeAllIdeeEdit()">Fermer</button>
        </div>
        <div class="idea-dialog-body">
            <div id="allIdeeEditAlert"></div>
            <div class="idea-form-grid">
                <div class="idea-field"><label>Titre</label><input name="titre" id="all_edit_titre"><div class="field-error" data-error-for="titre"></div></div>
                <div class="idea-field"><label>Categorie</label><input name="categorie" id="all_edit_categorie"><div class="field-error" data-error-for="categorie"></div></div>
                <div class="idea-field"><label>Brainstorming</label><select name="brainstorming_id" id="all_edit_brainstorming_id"><?php foreach ($brainstormings as $brainstorming): ?><option value="<?= (int) $brainstorming['id'] ?>"><?= htmlspecialchars($brainstorming['titre']) ?></option><?php endforeach; ?></select><div class="field-error" data-error-for="brainstorming_id"></div></div>
                <div class="idea-field"><label>Priorite</label><select name="priorite" id="all_edit_priorite"><option value="faible">Faible</option><option value="moyenne">Moyenne</option><option value="haute">Haute</option></select><div class="field-error" data-error-for="priorite"></div></div>
                <?php if ($isAdmin): ?>
                    <div class="idea-field"><label>Statut</label><select name="statut" id="all_edit_statut"><option value="proposee">Proposee</option><option value="en_etude">En etude</option><option value="approuvee">Approuvee</option><option value="rejetee">Rejetee</option></select><div class="field-error" data-error-for="statut"></div></div>
                    <div class="idea-field"><label>Votes</label><input name="votes" id="all_edit_votes" type="number" min="0"><div class="field-error" data-error-for="votes"></div></div>
                <?php endif; ?>
                <div class="idea-field full"><label>Contenu</label><textarea name="contenu" id="all_edit_contenu"></textarea><div class="field-error" data-error-for="contenu"></div></div>
            </div>
        </div>
        <div class="idea-dialog-foot">
            <button class="btn btn-secondary page-btn-secondary" type="button" onclick="closeAllIdeeEdit()">Annuler</button>
            <button class="btn btn-primary" type="submit">Enregistrer</button>
        </div>
    </form>
</dialog>

<dialog class="idea-edit-dialog" id="allIdeeScoreDialog">
    <div class="idea-dialog-head">
        <div class="idea-dialog-title">Score de l idee</div>
        <button class="btn btn-secondary page-btn-secondary" type="button" onclick="closeAllIdeeScore()">Fermer</button>
    </div>
    <div class="idea-dialog-body" id="allIdeeScoreContent">Chargement...</div>
</dialog>

<dialog class="idea-edit-dialog" id="allIdeeSuggestionDialog">
    <div class="idea-dialog-head">
        <div>
            <div class="idea-dialog-title">Suggestions d amelioration</div>
            <div id="suggestionSource" style="color:#6f665c; font-size:.85rem; margin-top:.2rem;">Analyse de l idee</div>
        </div>
        <button class="btn btn-secondary page-btn-secondary" type="button" onclick="closeAllIdeeSuggestions()">Fermer</button>
    </div>
    <div class="idea-dialog-body" id="suggestionContent">Chargement...</div>
</dialog>

<dialog class="idea-edit-dialog" id="allIdeeTranslationDialog">
    <div class="idea-dialog-head">
        <div>
            <div class="idea-dialog-title">Traduction de l idee</div>
            <div id="translationSource" style="color:#6f665c; font-size:.85rem; margin-top:.2rem;">Service de traduction</div>
        </div>
        <button class="btn btn-secondary page-btn-secondary" type="button" onclick="closeAllIdeeTranslation()">Fermer</button>
    </div>
    <div class="idea-dialog-body">
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

<script>
function escapeHtml(value) {
    return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}
function readJsonResponse(response) {
    return response.text().then(text => JSON.parse(text.replace(/^\uFEFF+/, '').trim()));
}
function clearAllIdeeErrors() {
    const form = document.getElementById('allIdeeEditForm');
    document.getElementById('allIdeeEditAlert').innerHTML = '';
    form.querySelectorAll('.idea-field').forEach(field => field.classList.remove('has-error'));
    form.querySelectorAll('.field-error').forEach(box => box.textContent = '');
}
function isModerationMessage(message) {
    return String(message || '').toLowerCase().includes('contenu bloque');
}
function moderationAlert(title = 'Contenu refuse') {
    return '<div class="moderation-alert"><div class="moderation-alert-icon"><i class="fas fa-shield-halved"></i></div><div><div class="moderation-alert-title">' + escapeHtml(title) + '</div><p class="moderation-alert-text">Le texte contient un contenu non autorise. Retirez les insultes, menaces ou elements de spam, puis reessayez.</p></div></div>';
}
function showAllIdeeFieldErrors(errors) {
    clearAllIdeeErrors();
    if (!errors || Array.isArray(errors)) return false;
    let shown = false;
    Object.entries(errors).forEach(([field, message]) => {
        const box = document.querySelector('#allIdeeEditForm [data-error-for="' + field + '"]');
        if (box) {
            box.textContent = isModerationMessage(message) ? 'Contenu refuse par la moderation automatique.' : message;
            const wrapper = box.closest('.idea-field');
            if (wrapper) wrapper.classList.add('has-error');
            shown = true;
        }
    });
    return shown;
}
function closeAllIdeeEdit() {
    document.getElementById('allIdeeEditDialog').close();
}
function closeAllIdeeScore() {
    document.getElementById('allIdeeScoreDialog').close();
}
function closeAllIdeeTranslation() {
    document.getElementById('allIdeeTranslationDialog').close();
}
function closeAllIdeeSuggestions() {
    document.getElementById('allIdeeSuggestionDialog').close();
}
function renderScoreList(items) {
    if (!Array.isArray(items) || items.length === 0) return '<p style="color:#6f665c; margin:0;">Aucun element detaille.</p>';
    return '<ul class="idea-score-list">' + items.map(item => '<li>' + escapeHtml(item) + '</li>').join('') + '</ul>';
}
function scoreCard(score, label) {
    return '<div class="idea-score-card"><div class="idea-score-label">' + escapeHtml(label) + '</div><div class="idea-score-value">' + escapeHtml(score) + '/100</div></div>';
}
function scoreAllIdee(id) {
    const dialog = document.getElementById('allIdeeScoreDialog');
    const content = document.getElementById('allIdeeScoreContent');
    content.innerHTML = '<p style="margin:0;">Analyse en cours...</p>';
    dialog.showModal();
    const formData = new FormData();
    formData.append('action', 'score_idee_ai');
    formData.append('id', id);
    fetch('?action=all_idees', { method: 'POST', body: formData })
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
let currentTranslationId = null;
function renderTranslation(data) {
    const cards = Object.entries(data.translated || {}).map(([field, value]) => {
        const label = field === 'contenu' ? 'Contenu' : 'Titre';
        return '<div class="translation-card"><div class="translation-label">' + escapeHtml(label) + '</div><div>' + escapeHtml(value).replaceAll('\n', '<br>') + '</div></div>';
    }).join('');
    document.getElementById('translationSource').textContent = data.source || 'Service de traduction';
    document.getElementById('translationContent').innerHTML = '<div class="translation-result">' + cards + '</div>';
}
function renderSuggestionList(items) {
    if (!Array.isArray(items) || items.length === 0) return '<p style="margin:0; color:#6f665c;">Aucune suggestion detaillee.</p>';
    return '<ul class="suggestion-list">' + items.map(item => '<li>' + escapeHtml(item) + '</li>').join('') + '</ul>';
}
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
function suggestAllIdee(id) {
    const dialog = document.getElementById('allIdeeSuggestionDialog');
    const content = document.getElementById('suggestionContent');
    document.getElementById('suggestionSource').textContent = 'Gemini API';
    content.innerHTML = 'Generation des suggestions...';
    dialog.showModal();
    const formData = new FormData();
    formData.append('action', 'suggest_idee_improvements');
    formData.append('id', id);
    fetch('?action=all_idees', { method: 'POST', body: formData })
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
function translateAllIdee(id) {
    currentTranslationId = id;
    const dialog = document.getElementById('allIdeeTranslationDialog');
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
    if (currentTranslationId) translateAllIdee(currentTranslationId);
});
function openAllIdeeEdit(id) {
    clearAllIdeeErrors();
    fetch('?action=all_idees&get_details=' + encodeURIComponent(id))
        .then(readJsonResponse)
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Idee introuvable.');
            const idee = data.idee;
            document.getElementById('all_edit_id').value = idee.id;
            document.getElementById('all_edit_titre').value = idee.titre || '';
            document.getElementById('all_edit_categorie').value = idee.categorie || 'General';
            document.getElementById('all_edit_brainstorming_id').value = idee.brainstorming_id || '';
            document.getElementById('all_edit_priorite').value = idee.priorite || 'moyenne';
            const statut = document.getElementById('all_edit_statut');
            if (statut) statut.value = idee.statut || 'proposee';
            const votes = document.getElementById('all_edit_votes');
            if (votes) votes.value = idee.votes || 0;
            document.getElementById('all_edit_contenu').value = idee.contenu || '';
            document.getElementById('allIdeeEditDialog').showModal();
        })
        .catch(error => alert(error.message || 'Erreur pendant le chargement.'));
}
document.getElementById('allIdeeEditForm').addEventListener('submit', event => {
    event.preventDefault();
    clearAllIdeeErrors();
    const formData = new FormData(event.currentTarget);
    formData.append('action', 'edit_idee_front');
    fetch('?action=all_idees', { method: 'POST', body: formData })
        .then(readJsonResponse)
        .then(data => {
            if (!data.success) {
                if (!showAllIdeeFieldErrors(data.errors)) {
                    document.getElementById('allIdeeEditAlert').innerHTML = isModerationMessage(data.message) ? moderationAlert('Modification refusee') : '<div class="front-inline-alert danger">' + escapeHtml(data.message || 'Erreur formulaire.') + '</div>';
                }
                return;
            }
            document.getElementById('allIdeeEditAlert').innerHTML = '<div class="front-inline-alert success">' + escapeHtml(data.message || 'Idee modifiee avec succes.') + '</div>';
            setTimeout(() => location.reload(), 650);
        })
        .catch(() => {
            document.getElementById('allIdeeEditAlert').innerHTML = '<div class="front-inline-alert danger">Erreur reseau pendant la modification.</div>';
        });
});
</script>
<footer class="footer">
    <p>&copy; 2026 SkillBridge</p>
</footer>
</body>
</html>
