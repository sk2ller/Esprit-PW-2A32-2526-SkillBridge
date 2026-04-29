<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= !empty($offre) ? 'Modifier' : 'Creer' ?> une offre job</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
</head>
<body class="skillbridge-front">
<?php include __DIR__ . '/partials/front_navbar.php'; ?>
<div class="front-page">
    <section class="section-surface">
        <div class="section-header">
            <div>
                <h1 class="section-title"><?= !empty($offre) ? 'Modifier' : 'Publier' ?> une <span>Offre Job</span></h1>
                <p class="section-subtitle">Decrivez precisement votre besoin pour recevoir des candidatures qualifiees.</p>
            </div>
        </div>
        <div class="form-shell">
            <form method="post" id="job-offer-form">
                <div style="display:grid; gap:1rem;">
                    <div><label class="form-label">Titre</label><input class="sb-control" id="job-title" name="titre" value="<?= htmlspecialchars($offre['titre'] ?? '') ?>" required></div>
                    <div><label class="form-label">Description</label><textarea class="sb-textarea" id="job-description" name="description" rows="6" required><?= htmlspecialchars($offre['description'] ?? '') ?></textarea></div>
                    <div class="dual-grid">
                        <div><label class="form-label">Budget</label><input class="sb-control" type="number" min="1" step="0.01" id="job-budget" name="budget" value="<?= htmlspecialchars($offre['budget'] ?? '') ?>" required></div>
                        <div><label class="form-label">Delai (jours)</label><input class="sb-control" type="number" min="1" id="job-delay" name="delai_jours" value="<?= htmlspecialchars($offre['delai_jours'] ?? 7) ?>" required></div>
                    </div>
                    <div class="dual-grid">
                        <div>
                            <label class="form-label">Mode de travail</label>
                            <select class="sb-select" id="job-execution-mode" name="execution_mode">
                                <option value="full_project" <?= (($offre['execution_mode'] ?? 'full_project') === 'full_project') ? 'selected' : '' ?>>Tout le projet</option>
                                <option value="milestone" <?= (($offre['execution_mode'] ?? '') === 'milestone') ? 'selected' : '' ?>>Par milestones</option>
                            </select>
                        </div>
                        <div><label class="form-label">Niveau requis</label><select class="sb-select" name="niveau_requis"><option value="debutant" <?= (($offre['niveau_requis'] ?? '') === 'debutant') ? 'selected' : '' ?>>Debutant</option><option value="intermediaire" <?= (($offre['niveau_requis'] ?? 'intermediaire') === 'intermediaire') ? 'selected' : '' ?>>Intermediaire</option><option value="expert" <?= (($offre['niveau_requis'] ?? '') === 'expert') ? 'selected' : '' ?>>Expert</option></select></div>
                    </div>
                    <div id="job-milestone-wrap" style="<?= (($offre['execution_mode'] ?? 'full_project') === 'milestone') ? '' : 'display:none;' ?>">
                        <label class="form-label">Plan milestones</label>
                        <textarea class="sb-textarea" id="job-milestone-plan" name="milestone_plan" rows="4" placeholder="Ex: 1. Maquette, 2. Integration, 3. Livraison finale"><?= htmlspecialchars($offre['milestone_plan'] ?? '') ?></textarea>
                    </div>
                    <div><label class="form-label">Competences requises</label><input class="sb-control" name="competences_requises" value="<?= htmlspecialchars($offre['competences_requises'] ?? '') ?>" placeholder="React, SEO, UI/UX..."></div>
                    <div style="display:flex; gap:1rem; justify-content:flex-end;"><a class="sb-btn-soft" href="?action=my_job_offers">Annuler</a><button class="sb-btn" type="submit">Enregistrer</button></div>
                </div>
            </form>
        </div>
    </section>
</div>
<script>
const jobOfferError = <?= json_encode($error ?? '') ?>;
if (jobOfferError) { Swal.fire({icon:'error',title:'Erreur',text:jobOfferError,confirmButtonColor:'#e07020',background:'#fffaf4',color:'#1f1f23'}); }
document.getElementById('job-offer-form').addEventListener('submit', async (event) => {
  const title = document.getElementById('job-title').value.trim();
  const description = document.getElementById('job-description').value.trim();
  const budget = parseFloat(document.getElementById('job-budget').value || '0');
  const delay = parseInt(document.getElementById('job-delay').value || '0', 10);
  const mode = document.getElementById('job-execution-mode').value;
  const milestonePlan = document.getElementById('job-milestone-plan').value.trim();
  if (!title || title.length < 5 || description.length < 20 || !(budget > 0) || !(delay > 0) || (mode === 'milestone' && milestonePlan.length < 10)) {
    event.preventDefault();
    await Swal.fire({icon:'warning',title:'Saisie invalide',text:'Verifie le titre, la description, le budget, le delai et le plan milestones si tu as choisi ce mode.',confirmButtonColor:'#e07020',background:'#fffaf4',color:'#1f1f23'});
  }
});
const executionModeControl = document.getElementById('job-execution-mode');
const milestoneWrap = document.getElementById('job-milestone-wrap');
executionModeControl.addEventListener('change', () => {
  milestoneWrap.style.display = executionModeControl.value === 'milestone' ? 'block' : 'none';
});
</script>
</body>
</html>
