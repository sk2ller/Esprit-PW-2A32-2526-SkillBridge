<?php
if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 1) {
    header('Location: ?action=login');
    exit;
}
$isEdit = isset($categorie) && $categorie;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Modifier categorie' : 'Nouvelle categorie' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="Views/assets/css/skillbridge-admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600;700&display=swap">
</head>
<body class="skillbridge-admin">
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <div class="topbar-title"><?= $isEdit ? 'Modifier categorie' : 'Nouvelle categorie' ?></div>
                <div class="topbar-bread"><a href="?action=statistics">Dashboard</a> / <a href="?action=categories_admin">Categories</a> / Formulaire</div>
            </div>
            <div class="topbar-actions">
                <a class="topbar-btn topbar-btn-outline" href="?action=categories_admin">Retour</a>
            </div>
        </div>

        <div class="admin-form-wrap">
            <form method="post" class="admin-form-grid" id="category-form">
                <div class="admin-form-group">
                    <label>Nom</label>
                    <input name="nom_categorie" id="category-name" value="<?= htmlspecialchars($categorie['nom_categorie'] ?? '') ?>" required>
                </div>

                <div class="admin-form-group">
                    <label>Icone FontAwesome</label>
                    <input name="icone" id="category-icon" value="<?= htmlspecialchars($categorie['icone'] ?? 'fas fa-folder') ?>">
                </div>

                <div class="admin-form-group admin-form-group-full">
                    <label>Description</label>
                    <textarea name="description" id="category-description" rows="5"><?= htmlspecialchars($categorie['description'] ?? '') ?></textarea>
                </div>

                <div class="admin-form-actions admin-form-group-full">
                    <a class="admin-btn admin-btn-outline" href="?action=categories_admin">Annuler</a>
                    <button class="admin-btn admin-btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('category-form');

    form.addEventListener('submit', async (event) => {
        const name = document.getElementById('category-name').value.trim();
        const icon = document.getElementById('category-icon').value.trim();

        if (!name) {
            event.preventDefault();
            await Swal.fire({
                icon: 'warning',
                title: 'Nom obligatoire',
                text: 'Le nom de la categorie est obligatoire.',
                confirmButtonColor: '#e07020',
                background: '#fffaf4',
                color: '#1f1f23'
            });
            return;
        }

        if (name.length < 3) {
            event.preventDefault();
            await Swal.fire({
                icon: 'warning',
                title: 'Nom trop court',
                text: 'Le nom de la categorie doit contenir au moins 3 caracteres.',
                confirmButtonColor: '#e07020',
                background: '#fffaf4',
                color: '#1f1f23'
            });
            return;
        }

        if (icon && !/^fa[srlbd]?\s+fa-[a-z0-9-]+$/i.test(icon)) {
            event.preventDefault();
            await Swal.fire({
                icon: 'warning',
                title: 'Icone invalide',
                text: 'Utilise un format FontAwesome valide comme "fas fa-folder".',
                confirmButtonColor: '#e07020',
                background: '#fffaf4',
                color: '#1f1f23'
            });
            return;
        }

        event.preventDefault();
        const result = await Swal.fire({
            icon: 'question',
            title: 'Confirmer l enregistrement ?',
            text: 'La categorie sera enregistree dans la base.',
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
