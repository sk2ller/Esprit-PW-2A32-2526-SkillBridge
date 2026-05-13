<?php
if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 1) {
    header('Location: ?action=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Categories</title>
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
                <div class="topbar-title">Categories</div>
                <div class="topbar-bread"><a href="?action=statistics">Dashboard</a> / Gestion des categories</div>
            </div>
            <div class="topbar-actions">
                <a class="topbar-btn topbar-btn-outline" href="?action=services_admin">Services</a>
                <a class="topbar-btn topbar-btn-primary" href="?action=category_create">Nouvelle categorie</a>
            </div>
        </div>

        <div class="admin-content">
            <div class="admin-table-wrap">
                <div class="admin-table-header">
                    <div class="admin-table-title">Liste des categories</div>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Services actifs</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><div class="table-service-name"><?= htmlspecialchars($c['nom_categorie']) ?></div></td>
                            <td><?= htmlspecialchars($c['description']) ?></td>
                            <td><?= (int)$c['nb_services'] ?></td>
                            <td>
                                <div class="admin-stack-actions">
                                    <a class="admin-btn admin-btn-outline admin-btn-sm" href="?action=category_edit&id=<?= (int)$c['id_categorie'] ?>">Edit</a>
                                    <a class="admin-btn admin-btn-danger admin-btn-sm js-category-delete" href="?action=category_delete&id=<?= (int)$c['id_categorie'] ?>">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const statusMessages = {
        '1': 'Categorie ajoutee avec succes.',
        '2': 'Categorie modifiee avec succes.',
        '3': 'Categorie supprimee avec succes.'
    };
    const status = new URLSearchParams(window.location.search).get('success');

    if (status && statusMessages[status]) {
        Swal.fire({
            icon: 'success',
            title: 'Operation reussie',
            text: statusMessages[status],
            confirmButtonColor: '#e07020',
            background: '#fffaf4',
            color: '#1f1f23'
        });
    }

    document.querySelectorAll('.js-category-delete').forEach((link) => {
        link.addEventListener('click', async (event) => {
            event.preventDefault();
            const result = await Swal.fire({
                icon: 'warning',
                title: 'Supprimer cette categorie ?',
                text: 'Cette action peut aussi affecter les services lies a cette categorie.',
                showCancelButton: true,
                confirmButtonText: 'Supprimer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#e07020',
                cancelButtonColor: '#6b7280',
                background: '#fffaf4',
                color: '#1f1f23'
            });

            if (result.isConfirmed) {
                window.location.href = link.href;
            }
        });
    });
});
</script>
</body>
</html>
