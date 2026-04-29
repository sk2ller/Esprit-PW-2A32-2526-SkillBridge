<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= isset($service) && $service ? 'Modifier' : 'Creer' ?> service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
</head>
<body class="skillbridge-front">
    <?php include __DIR__ . '/partials/front_navbar.php'; ?>

    <div class="front-page">
        <div class="dashboard-shell">
            <?php include __DIR__ . '/partials/freelancer_sidebar.php'; ?>

            <section class="section-surface">
                <div class="section-header">
                    <div>
                        <h1 class="section-title"><?= isset($service) && $service ? 'Modifier' : 'Creer' ?> un <span>Service</span></h1>
                        <p class="section-subtitle">Formulaire CRUD freelancer relie au dashboard et a la base `services`.</p>
                    </div>
                </div>

                <div class="form-shell">
                    <form method="post" enctype="multipart/form-data" id="service-form">
                        <div style="display:grid; gap:1rem;">
                            <div>
                                <label class="form-label">Titre du service</label>
                                <input class="sb-control" id="service-title" name="titre" value="<?= htmlspecialchars($service['titre'] ?? '') ?>" required>
                            </div>

                            <div>
                                <label class="form-label">Description</label>
                                <textarea class="sb-textarea" id="service-description" name="description" rows="6" required><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
                            </div>

                            <div class="dual-grid">
                                <div>
                                    <label class="form-label">Prix</label>
                                    <input type="number" min="0.01" step="0.01" class="sb-control" id="service-price" name="prix" value="<?= htmlspecialchars($service['prix'] ?? '') ?>" required>
                                </div>
                                <div>
                                    <label class="form-label">Delai de livraison</label>
                                    <input type="number" min="1" class="sb-control" id="service-delay" name="delai_livraison" value="<?= htmlspecialchars($service['delai_livraison'] ?? 1) ?>" required>
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Categorie</label>
                                <select class="sb-select" id="service-category" name="id_categorie" required>
                                    <option value="">Choisir une categorie</option>
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= (int)$c['id_categorie'] ?>" <?= ((int)($service['id_categorie'] ?? 0) === (int)$c['id_categorie']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['nom_categorie']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Thumbnail</label>
                                <input type="file" class="sb-control" id="service-thumbnail" name="thumbnail" accept=".jpg,.jpeg,.png,.webp,.gif">
                                <?php if (!empty($service['thumbnail'])): ?>
                                    <div style="margin-top:.75rem;">
                                        <div class="service-meta">Miniature actuelle</div>
                                        <img src="/Views/assets/uploads/<?= htmlspecialchars($service['thumbnail']) ?>" alt="Thumbnail" style="width:180px; height:120px; object-fit:cover; border-radius:14px; border:1px solid var(--border);">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:.5rem;">
                                <a class="sb-btn-soft" href="?action=my_services">Annuler</a>
                                <button class="sb-btn" type="submit">Enregistrer</button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const serverError = <?= json_encode($error ?? '') ?>;
    const form = document.getElementById('service-form');

    if (serverError) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: serverError,
            confirmButtonColor: '#e07020',
            background: '#fffaf4',
            color: '#1f1f23'
        });
    }

    form.addEventListener('submit', async (event) => {
        const title = document.getElementById('service-title').value.trim();
        const description = document.getElementById('service-description').value.trim();
        const price = parseFloat(document.getElementById('service-price').value || '0');
        const delay = parseInt(document.getElementById('service-delay').value || '0', 10);
        const category = document.getElementById('service-category').value;
        const thumbnail = document.getElementById('service-thumbnail').files[0];

        if (!title || !description || !category) {
            event.preventDefault();
            await Swal.fire({
                icon: 'warning',
                title: 'Champs obligatoires',
                text: 'Le titre, la description et la categorie sont obligatoires.',
                confirmButtonColor: '#e07020',
                background: '#fffaf4',
                color: '#1f1f23'
            });
            return;
        }

        if (title.length < 5) {
            event.preventDefault();
            await Swal.fire({
                icon: 'warning',
                title: 'Titre trop court',
                text: 'Le titre du service doit contenir au moins 5 caracteres.',
                confirmButtonColor: '#e07020',
                background: '#fffaf4',
                color: '#1f1f23'
            });
            return;
        }

        if (description.length < 20) {
            event.preventDefault();
            await Swal.fire({
                icon: 'warning',
                title: 'Description trop courte',
                text: 'La description du service doit contenir au moins 20 caracteres.',
                confirmButtonColor: '#e07020',
                background: '#fffaf4',
                color: '#1f1f23'
            });
            return;
        }

        if (!(price > 0) || delay < 1) {
            event.preventDefault();
            await Swal.fire({
                icon: 'warning',
                title: 'Valeurs invalides',
                text: 'Le prix doit etre superieur a 0 et le delai doit etre au moins 1 jour.',
                confirmButtonColor: '#e07020',
                background: '#fffaf4',
                color: '#1f1f23'
            });
            return;
        }

        if (thumbnail) {
            const extension = thumbnail.name.split('.').pop().toLowerCase();
            if (!['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(extension)) {
                event.preventDefault();
                await Swal.fire({
                    icon: 'warning',
                    title: 'Image invalide',
                    text: 'La miniature doit etre au format JPG, PNG, WEBP ou GIF.',
                    confirmButtonColor: '#e07020',
                    background: '#fffaf4',
                    color: '#1f1f23'
                });
                return;
            }
        }

        event.preventDefault();
        const result = await Swal.fire({
            icon: 'question',
            title: 'Confirmer l enregistrement ?',
            text: 'Le service sera enregistre avec ces informations.',
            showCancelButton: true,
            confirmButtonText: 'Enregistrer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#e07020',
            cancelButtonColor: '#6b7280',
            background: '#fffaf4',
            color: '#1f1f23'
        });

        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>
</body>
</html>
