<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail offre job - SkillBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
</head>
<body class="skillbridge-front">
<?php include __DIR__ . '/partials/front_navbar.php'; ?>
<div class="front-page">
    <div class="detail-shell">
        <div>
            <div class="detail-banner">
                <span class="service-badge"><?= htmlspecialchars($offre['niveau_requis']) ?></span>
                <h1 class="section-title"><?= htmlspecialchars($offre['titre']) ?></h1>
                <p class="section-subtitle">Client: <?= htmlspecialchars($offre['prenom'] . ' ' . $offre['nom']) ?> | Budget: <?= number_format((float)$offre['budget'], 2) ?> DT</p>
            </div>
            <div class="detail-main-card">
                <h3>Description</h3>
                <p class="muted-copy"><?= nl2br(htmlspecialchars($offre['description'])) ?></p>
                <div style="margin-top:1rem;"><strong>Mode de travail:</strong> <span class="muted-copy"><?= ($offre['execution_mode'] ?? 'full_project') === 'milestone' ? 'Par milestones' : 'Tout le projet' ?></span></div>
                <?php if (($offre['execution_mode'] ?? 'full_project') === 'milestone' && !empty($offre['milestone_plan'])): ?>
                    <div style="margin-top:1rem;"><strong>Plan milestones:</strong> <span class="muted-copy"><?= nl2br(htmlspecialchars($offre['milestone_plan'])) ?></span></div>
                <?php endif; ?>
                <?php if (!empty($offre['competences_requises'])): ?>
                    <div style="margin-top:1.5rem;"><strong>Competences:</strong> <span class="muted-copy"><?= htmlspecialchars($offre['competences_requises']) ?></span></div>
                <?php endif; ?>
            </div>
            <?php if (!empty($applications)): ?>
                <div class="section-surface">
                    <div class="section-header"><div><h2 class="section-title">Candidatures <span>Recues</span></h2></div></div>
                    <div class="dashboard-grid">
                        <?php foreach ($applications as $application): ?>
                            <div class="surface-card">
                                <h3><?= htmlspecialchars($application['prenom'] . ' ' . $application['nom']) ?></h3>
                                <p class="muted-copy"><?= htmlspecialchars($application['message']) ?></p>
                                <div class="service-meta">Budget propose: <?= number_format((float)$application['budget_propose'], 2) ?> DT</div>
                                <div class="service-meta">Disponibilite: <?= (int)$application['disponibilite_jours'] ?> jours</div>
                                <div class="service-meta">Mode propose: <?= ($application['execution_mode'] ?? 'full_project') === 'milestone' ? 'Par milestones' : 'Tout le projet' ?></div>
                                <div class="service-meta">Statut: <?= htmlspecialchars($application['statut']) ?></div>
                                <?php if (($application['execution_mode'] ?? 'full_project') === 'milestone' && !empty($application['milestone_plan'])): ?>
                                    <div class="service-meta">Milestones: <?= nl2br(htmlspecialchars($application['milestone_plan'])) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($application['cv_url'])): ?>
                                    <div class="service-meta">CV: <a href="<?= htmlspecialchars($application['cv_url']) ?>" target="_blank" rel="noopener">Voir le CV</a></div>
                                <?php endif; ?>
                                <?php if (!empty($application['portfolio_url'])): ?>
                                    <div class="service-meta">Portfolio: <a href="<?= htmlspecialchars($application['portfolio_url']) ?>" target="_blank" rel="noopener">Voir le portfolio</a></div>
                                <?php endif; ?>
                                <div style="display:flex; gap:.6rem; margin-top:1rem; flex-wrap:wrap;">
                                    <?php if (!empty($isOwner) && $application['statut'] === 'en_attente'): ?>
                                        <a class="sb-btn" href="?action=job_application_client_status&id=<?= (int)$application['id_candidature'] ?>&status=acceptee">Accepter</a>
                                        <a class="sb-btn-danger" href="?action=job_application_client_status&id=<?= (int)$application['id_candidature'] ?>&status=refusee">Refuser</a>
                                    <?php endif; ?>
                                    <?php if (!empty($isOwner)): ?>
                                        <a class="sb-btn-soft" href="?action=chat&offer_id=<?= (int)$offre['id_offre'] ?>&freelancer_id=<?= (int)$application['id_freelancer'] ?>"><i class="fas fa-comments"></i> Chat</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <aside class="detail-side-card">
            <div class="detail-price"><?= number_format((float)$offre['budget'], 2) ?> DT</div>
            <div class="service-meta" style="margin-top:1rem;">Delai souhaite: <?= (int)$offre['delai_jours'] ?> jours</div>
            <div class="service-meta">Statut: <?= htmlspecialchars($offre['statut']) ?></div>
            <?php if ((int)($_SESSION['user_role'] ?? 0) === 3): ?>
                <a class="sb-btn-soft" href="?action=chat&offer_id=<?= (int)$offre['id_offre'] ?>" style="width:100%; justify-content:center; margin-top:1rem;"><i class="fas fa-comments"></i> Contacter le client</a>
                <?php if ($hasApplied): ?>
                    <div class="inline-alert success" style="margin-top:1rem;">Vous avez deja candidate a cette offre.</div>
                <?php else: ?>
                    <form method="post" action="?action=job_offer_apply&id=<?= (int)$offre['id_offre'] ?>" id="apply-form" style="display:none;">
                        <input type="hidden" name="message" id="apply-message-hidden">
                        <input type="hidden" name="budget_propose" id="apply-budget-hidden">
                        <input type="hidden" name="disponibilite_jours" id="apply-days-hidden">
                        <input type="hidden" name="execution_mode" id="apply-mode-hidden">
                        <input type="hidden" name="milestone_plan" id="apply-milestone-hidden">
                        <input type="hidden" name="cv_url" id="apply-cv-hidden">
                        <input type="hidden" name="portfolio_url" id="apply-portfolio-hidden">
                    </form>
                    <button class="sb-btn" type="button" id="open-apply-modal" style="width:100%; margin-top:1rem;">Candidater</button>
                <?php endif; ?>
            <?php endif; ?>
        </aside>
    </div>
</div>
<script>
const applySuccess = <?= json_encode($_GET['success'] ?? '') ?>;
const applyError = <?= json_encode($_GET['error'] ?? '') ?>;
if (applySuccess === '1') Swal.fire({icon:'success',title:'Candidature envoyee',text:'Votre candidature a bien ete transmise au client.',confirmButtonColor:'#e07020',background:'#fffaf4',color:'#1f1f23'});
if (applySuccess === '2') Swal.fire({icon:'success',title:'Decision enregistree',text:'La candidature a bien ete mise a jour par le client.',confirmButtonColor:'#e07020',background:'#fffaf4',color:'#1f1f23'});
if (applyError === '2') Swal.fire({icon:'info',title:'Deja candidate',text:'Vous avez deja candidate a cette offre.',confirmButtonColor:'#e07020',background:'#fffaf4',color:'#1f1f23'});
if (applyError === '3') Swal.fire({icon:'error',title:'Saisie invalide',text:'Verifiez votre message, budget, disponibilite, mode de travail et milestones si necessaire.',confirmButtonColor:'#e07020',background:'#fffaf4',color:'#1f1f23'});
const applyForm = document.getElementById('apply-form');
const openApplyModal = document.getElementById('open-apply-modal');
if (openApplyModal && applyForm) {
  openApplyModal.addEventListener('click', async () => {
    const result = await Swal.fire({
      title: 'Envoyer une candidature',
      width: 720,
      confirmButtonText: 'Envoyer la candidature',
      confirmButtonColor: '#e07020',
      showCancelButton: true,
      cancelButtonText: 'Annuler',
      background: '#fffaf4',
      color: '#1f1f23',
      html: `
        <div style="display:grid; gap:12px; text-align:left;">
          <textarea id="swal-apply-message" class="swal2-textarea" placeholder="Presentez votre proposition..." style="display:block; width:100%; min-height:120px;"></textarea>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <input id="swal-apply-budget" class="swal2-input" type="number" min="1" step="0.01" placeholder="Budget propose">
            <input id="swal-apply-days" class="swal2-input" type="number" min="1" placeholder="Disponibilite (jours)">
          </div>
          <select id="swal-apply-mode" class="swal2-select">
            <option value="full_project">Je fais tout le projet</option>
            <option value="milestone">Je propose un travail par milestones</option>
          </select>
          <textarea id="swal-apply-milestones" class="swal2-textarea" placeholder="Detaillez vos milestones..." style="display:none; width:100%; min-height:100px;"></textarea>
          <input id="swal-apply-cv" class="swal2-input" type="url" placeholder="Lien de votre CV">
          <input id="swal-apply-portfolio" class="swal2-input" type="url" placeholder="Lien de votre portfolio">
        </div>
      `,
      didOpen: () => {
        const modeSelect = document.getElementById('swal-apply-mode');
        const milestoneField = document.getElementById('swal-apply-milestones');
        modeSelect.addEventListener('change', () => {
          milestoneField.style.display = modeSelect.value === 'milestone' ? 'block' : 'none';
        });
      },
      preConfirm: () => {
        const message = document.getElementById('swal-apply-message').value.trim();
        const budget = document.getElementById('swal-apply-budget').value.trim();
        const days = document.getElementById('swal-apply-days').value.trim();
        const mode = document.getElementById('swal-apply-mode').value;
        const milestones = document.getElementById('swal-apply-milestones').value.trim();
        const cv = document.getElementById('swal-apply-cv').value.trim();
        const portfolio = document.getElementById('swal-apply-portfolio').value.trim();
        const isValidOptionalUrl = (value) => value === '' || /^https?:\/\//i.test(value);

        if (message.length < 20 || !(parseFloat(budget) > 0) || !(parseInt(days, 10) > 0)) {
          Swal.showValidationMessage('Ajoutez un message valide, un budget et une disponibilite.');
          return false;
        }
        if (mode === 'milestone' && milestones.length < 10) {
          Swal.showValidationMessage('Detaillez votre plan milestones.');
          return false;
        }
        if (!isValidOptionalUrl(cv) || !isValidOptionalUrl(portfolio)) {
          Swal.showValidationMessage('Les liens CV et portfolio doivent commencer par http:// ou https://');
          return false;
        }

        return { message, budget, days, mode, milestones, cv, portfolio };
      }
    });

    if (!result.isConfirmed || !result.value) {
      return;
    }

    document.getElementById('apply-message-hidden').value = result.value.message;
    document.getElementById('apply-budget-hidden').value = result.value.budget;
    document.getElementById('apply-days-hidden').value = result.value.days;
    document.getElementById('apply-mode-hidden').value = result.value.mode;
    document.getElementById('apply-milestone-hidden').value = result.value.mode === 'milestone' ? result.value.milestones : '';
    document.getElementById('apply-cv-hidden').value = result.value.cv;
    document.getElementById('apply-portfolio-hidden').value = result.value.portfolio;
    applyForm.submit();
  });
}
</script>
</body>
</html>
