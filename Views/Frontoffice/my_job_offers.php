<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes offres job - SkillBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
    <style>
        .skillbridge-offer-popup {
            border-radius: 32px !important;
            padding: 0 !important;
            overflow: hidden !important;
            background:
                radial-gradient(circle at top right, rgba(240, 138, 59, .14), transparent 28%),
                linear-gradient(180deg, #fffdf9 0%, #fff8ef 100%) !important;
            border: 1px solid rgba(223, 209, 189, .95) !important;
            box-shadow: 0 28px 70px rgba(31, 31, 35, .18) !important;
        }

        .skillbridge-offer-popup .swal2-title {
            font-family: 'Playfair Display', serif !important;
            font-size: 2.15rem !important;
            font-weight: 700 !important;
            letter-spacing: -.03em !important;
            color: #1f1f23 !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .skillbridge-offer-popup .swal2-html-container {
            margin: 0 !important;
            padding: 0 !important;
        }

        .skillbridge-offer-popup .swal2-actions {
            margin: 0 !important;
            padding: 1.35rem 2rem 2rem !important;
            justify-content: flex-end !important;
            gap: .85rem !important;
            background: linear-gradient(180deg, rgba(255, 248, 239, 0), rgba(255, 248, 239, .96)) !important;
        }

        .skillbridge-offer-popup .swal2-confirm,
        .skillbridge-offer-popup .swal2-cancel {
            border-radius: 14px !important;
            padding: .92rem 1.35rem !important;
            font-family: 'DM Sans', sans-serif !important;
            font-weight: 700 !important;
            box-shadow: none !important;
        }

        .offer-popup-shell {
            text-align: left;
        }

        .offer-popup-hero {
            padding: 2rem 2rem 1.35rem;
            border-bottom: 1px solid rgba(223, 209, 189, .9);
            background:
                linear-gradient(135deg, rgba(31, 31, 35, .96), rgba(58, 45, 39, .92)),
                radial-gradient(circle at top right, rgba(240, 138, 59, .22), transparent 32%);
            color: #fff;
        }

        .offer-popup-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(240, 138, 59, .35);
            color: #f7d5ba;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .offer-popup-subtitle {
            max-width: 620px;
            color: rgba(255, 255, 255, .74);
            font-size: .98rem;
            line-height: 1.65;
            margin-top: .65rem;
        }

        .offer-popup-body {
            padding: 1.7rem 2rem 0;
            display: grid;
            gap: 1rem;
        }

        .offer-popup-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .offer-popup-field {
            display: grid;
            gap: .45rem;
        }

        .offer-popup-field-full {
            grid-column: 1 / -1;
        }

        .offer-popup-label {
            font-size: .8rem;
            font-weight: 800;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: #6a656f;
        }

        .offer-popup-card {
            padding: 1rem;
            border: 1px solid rgba(223, 209, 189, .92);
            border-radius: 18px;
            background: rgba(255, 255, 255, .74);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.55);
        }

        .offer-popup-card-title {
            font-weight: 700;
            color: #1f1f23;
            margin-bottom: .3rem;
        }

        .offer-popup-card-copy {
            color: #8b8791;
            font-size: .9rem;
            line-height: 1.55;
        }

        .skillbridge-offer-popup .swal2-input,
        .skillbridge-offer-popup .swal2-select,
        .skillbridge-offer-popup .swal2-textarea {
            width: 100% !important;
            margin: 0 !important;
            min-height: 56px;
            border-radius: 16px !important;
            border: 1px solid #dfd1bd !important;
            background: rgba(255, 252, 247, .94) !important;
            color: #1f1f23 !important;
            font-family: 'DM Sans', sans-serif !important;
            font-size: .97rem !important;
            padding: .95rem 1rem !important;
            box-shadow: none !important;
        }

        .skillbridge-offer-popup .swal2-textarea {
            min-height: 130px;
            resize: vertical;
        }

        .skillbridge-offer-popup .swal2-input:focus,
        .skillbridge-offer-popup .swal2-select:focus,
        .skillbridge-offer-popup .swal2-textarea:focus {
            border-color: #e07020 !important;
            box-shadow: 0 0 0 4px rgba(224,112,32,.13) !important;
            background: #fff !important;
        }

        .skillbridge-offer-popup .swal2-validation-message {
            margin: .6rem 2rem 0 !important;
            border-radius: 14px !important;
            background: rgba(184, 73, 66, .08) !important;
            color: #b84942 !important;
            padding: .9rem 1rem !important;
        }

        @media (max-width: 768px) {
            .offer-popup-hero,
            .offer-popup-body,
            .skillbridge-offer-popup .swal2-actions {
                padding-left: 1.2rem !important;
                padding-right: 1.2rem !important;
            }

            .offer-popup-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="skillbridge-front">
<?php include __DIR__ . '/partials/front_navbar.php'; ?>
<div class="front-page">
    <section class="section-surface">
        <div class="section-header">
            <div>
                <h1 class="section-title">Mes <span>Offres Job</span></h1>
                <p class="section-subtitle">Publiez vos besoins, modifiez vos annonces et suivez les candidatures des freelancers.</p>
            </div>
            <button type="button" id="open-job-offer-modal" class="sb-btn"><i class="fas fa-plus"></i> Nouvelle offre</button>
        </div>

        <form method="post" action="?action=job_offer_create" id="job-offer-popup-form" style="display:none;">
            <input type="hidden" name="titre" id="popup-job-title">
            <input type="hidden" name="description" id="popup-job-description">
            <input type="hidden" name="budget" id="popup-job-budget">
            <input type="hidden" name="delai_jours" id="popup-job-delay">
            <input type="hidden" name="execution_mode" id="popup-job-mode">
            <input type="hidden" name="milestone_plan" id="popup-job-milestones">
            <input type="hidden" name="niveau_requis" id="popup-job-level">
            <input type="hidden" name="competences_requises" id="popup-job-skills">
        </form>

        <div class="mini-stats">
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-bullhorn"></i></div><div class="service-price"><?= (int)($stats['total_offres'] ?? 0) ?></div><div class="muted-copy">Offres publiees</div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-circle-check"></i></div><div class="service-price"><?= (int)($stats['offres_actives'] ?? 0) ?></div><div class="muted-copy">Offres actives</div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-paper-plane"></i></div><div class="service-price"><?= (int)($stats['total_candidatures'] ?? 0) ?></div><div class="muted-copy">Candidatures recues</div></div>
        </div>

        <?php if (empty($offres)): ?>
            <div class="empty-state"><div class="icon"><i class="fas fa-bullhorn"></i></div><h3>Aucune offre publiee</h3><p>Commencez par publier votre premiere offre job.</p></div>
        <?php else: ?>
            <div class="service-grid">
                <?php foreach ($offres as $offre): ?>
                    <article class="service-card">
                        <div class="service-card-top"><i class="fas fa-bullhorn" style="font-size:3rem; color:rgba(255,255,255,.22);"></i></div>
                        <div class="service-card-body">
                            <span class="service-badge"><?= htmlspecialchars($offre['niveau_requis']) ?></span>
                            <h3 class="service-title"><?= htmlspecialchars($offre['titre']) ?></h3>
                            <div class="service-meta">Budget: <?= number_format((float)$offre['budget'], 2) ?> DT</div>
                            <div class="service-meta">Candidatures: <?= (int)$offre['candidature_count'] ?></div>
                            <div class="service-copy"><?= htmlspecialchars(mb_strimwidth($offre['description'], 0, 110, '...')) ?></div>
                        </div>
                        <div class="service-card-footer">
                            <div class="service-price"><?= htmlspecialchars($offre['statut']) ?></div>
                            <div style="display:flex; gap:.5rem;">
                                <a class="sb-btn-soft" href="?action=job_offer_detail&id=<?= (int)$offre['id_offre'] ?>"><i class="fas fa-eye"></i></a>
                                <a class="sb-btn-soft" href="?action=job_offer_edit&id=<?= (int)$offre['id_offre'] ?>"><i class="fas fa-pen"></i></a>
                                <a class="sb-btn-danger" href="?action=job_offer_delete&id=<?= (int)$offre['id_offre'] ?>"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<script>
const offerStatus = <?= json_encode($_GET['success'] ?? '') ?>;
if (offerStatus === '1') Swal.fire({icon:'success',title:'Offre ajoutee',text:'Votre nouvelle offre a bien ete enregistree.',confirmButtonColor:'#e07020',background:'#fffaf4',color:'#1f1f23'});
if (offerStatus === '2') Swal.fire({icon:'success',title:'Offre modifiee',text:'Votre offre a bien ete mise a jour.',confirmButtonColor:'#e07020',background:'#fffaf4',color:'#1f1f23'});
if (offerStatus === '3') Swal.fire({icon:'success',title:'Offre supprimee',text:'Votre offre a bien ete supprimee.',confirmButtonColor:'#e07020',background:'#fffaf4',color:'#1f1f23'});

const openOfferModalBtn = document.getElementById('open-job-offer-modal');
const offerPopupForm = document.getElementById('job-offer-popup-form');

if (openOfferModalBtn && offerPopupForm) {
  openOfferModalBtn.addEventListener('click', async () => {
    const result = await Swal.fire({
      title: 'Nouvelle offre job',
      width: 920,
      customClass: {
        popup: 'skillbridge-offer-popup'
      },
      confirmButtonText: 'Publier l offre',
      confirmButtonColor: '#e07020',
      showCancelButton: true,
      cancelButtonText: 'Annuler',
      buttonsStyling: true,
      background: '#fffaf4',
      color: '#1f1f23',
      html: `
        <div class="offer-popup-shell">
          <div class="offer-popup-hero">
            <div class="offer-popup-kicker"><i class="fas fa-bullhorn"></i> Publication Client</div>
            <div class="offer-popup-subtitle">Transformez votre besoin en annonce claire, attractive et bien structuree pour recevoir des candidatures plus qualifiees.</div>
          </div>
          <div class="offer-popup-body">
            <div class="offer-popup-grid">
              <div class="offer-popup-field offer-popup-field-full">
                <label class="offer-popup-label" for="swal-offer-title">Titre de l'offre</label>
                <input id="swal-offer-title" class="swal2-input" placeholder="Ex: Dashboard React pour startup SaaS">
              </div>
              <div class="offer-popup-field offer-popup-field-full">
                <label class="offer-popup-label" for="swal-offer-description">Description</label>
                <textarea id="swal-offer-description" class="swal2-textarea" placeholder="Expliquez le contexte, les livrables attendus et les priorites du projet."></textarea>
              </div>
              <div class="offer-popup-field">
                <label class="offer-popup-label" for="swal-offer-budget">Budget</label>
                <input id="swal-offer-budget" class="swal2-input" type="number" min="1" step="0.01" placeholder="Budget en DT">
              </div>
              <div class="offer-popup-field">
                <label class="offer-popup-label" for="swal-offer-delay">Delai</label>
                <input id="swal-offer-delay" class="swal2-input" type="number" min="1" placeholder="Delai (jours)">
              </div>
              <div class="offer-popup-field">
                <label class="offer-popup-label" for="swal-offer-mode">Mode de travail</label>
                <select id="swal-offer-mode" class="swal2-select">
                  <option value="full_project">Tout le projet</option>
                  <option value="milestone">Par milestones</option>
                </select>
              </div>
              <div class="offer-popup-field">
                <label class="offer-popup-label" for="swal-offer-level">Niveau requis</label>
                <select id="swal-offer-level" class="swal2-select">
                  <option value="debutant">Debutant</option>
                  <option value="intermediaire" selected>Intermediaire</option>
                  <option value="expert">Expert</option>
                </select>
              </div>
              <div class="offer-popup-field offer-popup-field-full">
                <div class="offer-popup-card">
                  <div class="offer-popup-card-title">Conseil SkillBridge</div>
                  <div class="offer-popup-card-copy">Le mode <strong>milestones</strong> est ideal si vous voulez decouper le projet en livrables progressifs avec des validations intermediaires.</div>
                </div>
              </div>
              <div class="offer-popup-field offer-popup-field-full">
                <div id="swal-offer-milestones-wrap" style="display:none;">
                  <label class="offer-popup-label" for="swal-offer-milestones">Plan milestones</label>
                  <textarea id="swal-offer-milestones" class="swal2-textarea" placeholder="Ex: 1. Wireframe, 2. UI finale, 3. Integration, 4. Recette"></textarea>
                </div>
              </div>
              <div class="offer-popup-field offer-popup-field-full">
                <label class="offer-popup-label" for="swal-offer-skills">Competences requises</label>
                <input id="swal-offer-skills" class="swal2-input" placeholder="React, SEO, UI/UX, Laravel...">
              </div>
            </div>
          </div>
        </div>
      `,
      didOpen: () => {
        const modeSelect = document.getElementById('swal-offer-mode');
        const milestoneField = document.getElementById('swal-offer-milestones-wrap');
        modeSelect.addEventListener('change', () => {
          milestoneField.style.display = modeSelect.value === 'milestone' ? 'block' : 'none';
        });
      },
      preConfirm: () => {
        const title = document.getElementById('swal-offer-title').value.trim();
        const description = document.getElementById('swal-offer-description').value.trim();
        const budget = document.getElementById('swal-offer-budget').value.trim();
        const delay = document.getElementById('swal-offer-delay').value.trim();
        const mode = document.getElementById('swal-offer-mode').value;
        const level = document.getElementById('swal-offer-level').value;
        const milestones = document.getElementById('swal-offer-milestones').value.trim();
        const skills = document.getElementById('swal-offer-skills').value.trim();

        if (title.length < 5 || description.length < 20 || !(parseFloat(budget) > 0) || !(parseInt(delay, 10) > 0)) {
          Swal.showValidationMessage('Verifiez le titre, la description, le budget et le delai.');
          return false;
        }

        if (mode === 'milestone' && milestones.length < 10) {
          Swal.showValidationMessage('Ajoutez un plan milestones plus detaille.');
          return false;
        }

        return { title, description, budget, delay, mode, level, milestones, skills };
      }
    });

    if (!result.isConfirmed || !result.value) {
      return;
    }

    document.getElementById('popup-job-title').value = result.value.title;
    document.getElementById('popup-job-description').value = result.value.description;
    document.getElementById('popup-job-budget').value = result.value.budget;
    document.getElementById('popup-job-delay').value = result.value.delay;
    document.getElementById('popup-job-mode').value = result.value.mode;
    document.getElementById('popup-job-milestones').value = result.value.mode === 'milestone' ? result.value.milestones : '';
    document.getElementById('popup-job-level').value = result.value.level;
    document.getElementById('popup-job-skills').value = result.value.skills;
    offerPopupForm.submit();
  });
}
</script>
</body>
</html>
