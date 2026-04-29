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
    <title>Admin - Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="Views/assets/css/skillbridge-admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600;700&display=swap">
</head>
<body class="skillbridge-admin">
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <div class="topbar-title">Services</div>
                <div class="topbar-bread"><a href="?action=statistics">Dashboard</a> / Gestion des services</div>
            </div>
            <div class="topbar-actions">
                <a class="topbar-btn topbar-btn-outline" href="?action=home">Home</a>
            </div>
        </div>

        <div class="admin-content">
            <div class="stats-grid">
                <div class="stat-widget">
                    <div class="sw-icon"><i class="fas fa-briefcase"></i></div>
                    <div class="sw-value"><?= (int)($serviceStats['total'] ?? 0) ?></div>
                    <div class="sw-label">Total services</div>
                </div>
                <div class="stat-widget green">
                    <div class="sw-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="sw-value"><?= (int)($serviceStats['actifs'] ?? 0) ?></div>
                    <div class="sw-label">Services actifs</div>
                </div>
                <div class="stat-widget orange">
                    <div class="sw-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="sw-value"><?= (int)($serviceStats['attente'] ?? 0) ?></div>
                    <div class="sw-label">En attente</div>
                </div>
                <div class="stat-widget blue">
                    <div class="sw-icon"><i class="fas fa-ban"></i></div>
                    <div class="sw-value"><?= (int)($serviceStats['suspendus'] ?? 0) ?></div>
                    <div class="sw-label">Suspendus</div>
                </div>
                <div class="stat-widget">
                    <div class="sw-icon"><i class="fas fa-wallet"></i></div>
                    <div class="sw-value"><?= number_format((float)($serviceStats['prix_moyen'] ?? 0), 0) ?></div>
                    <div class="sw-label">Prix moyen (DT)</div>
                </div>
                <div class="stat-widget green">
                    <div class="sw-icon"><i class="fas fa-image"></i></div>
                    <div class="sw-value"><?= (int)($serviceStats['avec_thumbnail'] ?? 0) ?></div>
                    <div class="sw-label">Avec miniature</div>
                </div>
                <div class="stat-widget orange">
                    <div class="sw-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="sw-value" style="font-size:1.25rem;"><?= htmlspecialchars($serviceStats['categorie_top'] ?? 'Aucune') ?></div>
                    <div class="sw-label">Categorie dominante</div>
                </div>
            </div>

            <div class="admin-card admin-filter-bar-card">
                <form method="get" class="admin-filter-bar">
                    <input type="hidden" name="action" value="services_admin">
                    <div class="admin-filter-bar-title">
                        <i class="fas fa-sliders"></i>
                        <span>Filtres services</span>
                    </div>
                    <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Recherche service, categorie ou freelancer">
                    <select name="status">
                        <option value="all" <?= ($_GET['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>Tous les statuts</option>
                        <option value="actif" <?= ($_GET['status'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
                        <option value="en_attente" <?= ($_GET['status'] ?? '') === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                        <option value="suspendu" <?= ($_GET['status'] ?? '') === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                    </select>
                    <select name="category">
                        <option value="0">Toutes les categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int)$category['id_categorie'] ?>" <?= (int)($_GET['category'] ?? 0) === (int)$category['id_categorie'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['nom_categorie']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="sort">
                        <option value="recent" <?= ($_GET['sort'] ?? 'recent') === 'recent' ? 'selected' : '' ?>>Plus recents</option>
                        <option value="oldest" <?= ($_GET['sort'] ?? '') === 'oldest' ? 'selected' : '' ?>>Plus anciens</option>
                        <option value="price_asc" <?= ($_GET['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Prix croissant</option>
                        <option value="price_desc" <?= ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Prix decroissant</option>
                        <option value="title_asc" <?= ($_GET['sort'] ?? '') === 'title_asc' ? 'selected' : '' ?>>Titre A-Z</option>
                        <option value="title_desc" <?= ($_GET['sort'] ?? '') === 'title_desc' ? 'selected' : '' ?>>Titre Z-A</option>
                    </select>
                    <button class="admin-btn admin-btn-primary" type="submit">Filtrer</button>
                    <a class="admin-btn admin-btn-outline" href="?action=services_admin">Reset</a>
                </form>
            </div>

            <div class="admin-split-grid">
                <div class="admin-table-wrap">
                    <div class="admin-table-header">
                        <div class="admin-table-title">Liste des services</div>
                        <div class="sw-label"><?= (int)count($services) ?> resultat(s)</div>
                    </div>

                    <table class="admin-table admin-service-table">
                        <colgroup>
                            <col class="service-col-main">
                            <col class="service-col-freelancer">
                            <col class="service-col-category">
                            <col class="service-col-price">
                            <col class="service-col-status">
                            <col class="service-col-actions">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Freelancer</th>
                                <th>Categorie</th>
                                <th>Prix</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($services)): ?>
                            <tr>
                                <td colspan="6">Aucun service ne correspond aux filtres choisis.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($services as $s): ?>
                            <tr>
                                <td>
                                    <div class="service-cell-main">
                                        <div class="service-thumb">
                                            <?php if (!empty($s['thumbnail'])): ?>
                                                <img src="/Views/assets/uploads/<?= htmlspecialchars($s['thumbnail']) ?>" alt="<?= htmlspecialchars($s['titre']) ?>" style="width:100%; height:100%; object-fit:cover;">
                                            <?php else: ?>
                                                <i class="fas fa-image" style="color:rgba(255,255,255,0.45);"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="table-service-name"><?= htmlspecialchars($s['titre']) ?></div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($s['prenom'] . ' ' . $s['nom']) ?></td>
                                <td><?= htmlspecialchars($s['nom_categorie']) ?></td>
                                <td><?= number_format((float)$s['prix'], 2) ?> DT</td>
                                <td>
                                    <?php $badgeClass = $s['statut'] === 'actif' ? 'badge-actif' : ($s['statut'] === 'suspendu' ? 'badge-suspendu' : 'badge-pending'); ?>
                                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($s['statut']) ?></span>
                                </td>
                                <td>
                                    <div class="admin-stack-actions service-actions-stack">
                                        <a class="admin-btn admin-btn-danger admin-btn-sm js-service-action" href="?action=service_delete_admin&id=<?= (int)$s['id_service'] ?>" data-title="Supprimer ce service ?" data-text="Cette suppression est definitive.">Delete</a>
                                        <a class="admin-btn admin-btn-success admin-btn-sm js-service-action" href="?action=service_status&id=<?= (int)$s['id_service'] ?>&status=actif" data-title="Activer ce service ?" data-text="Le service sera visible pour les clients.">Activer</a>
                                        <a class="admin-btn admin-btn-warning admin-btn-sm js-service-action" href="?action=service_status&id=<?= (int)$s['id_service'] ?>&status=en_attente" data-title="Passer ce service en attente ?" data-text="Le service ne sera plus actif tant qu il n est pas revalide.">Attente</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <aside class="admin-card">
                    <div class="admin-table-header" style="margin-bottom:1rem;">
                        <div class="admin-table-title">Sidebar metier</div>
                    </div>
                    <div class="admin-stack-actions" style="margin-bottom:1rem;">
                        <button type="button" class="admin-btn admin-btn-primary admin-sidebar-toggle active" data-service-panel="service-chart-panel">Graphique</button>
                        <button type="button" class="admin-btn admin-btn-outline admin-sidebar-toggle" data-service-panel="service-summary-panel">Autre</button>
                        <button type="button" class="admin-btn admin-btn-outline admin-sidebar-toggle" data-service-panel="service-table-panel">Tableau</button>
                    </div>

                    <div id="service-chart-panel" class="service-side-panel">
                        <div class="sw-label" style="margin-bottom:.75rem;">Repartition des services</div>
                        <div style="position:relative; min-height:250px;">
                            <canvas id="servicesStatusChart"></canvas>
                        </div>
                    </div>

                    <div id="service-summary-panel" class="service-side-panel" style="display:none;">
                        <div class="sw-label" style="margin-bottom:.75rem;">Lecture rapide</div>
                        <div class="admin-list-panel">
                            <div class="admin-list-item">
                                <div class="admin-list-icon"><i class="fas fa-layer-group"></i></div>
                                <div>
                                    <strong>Categorie dominante</strong>
                                    <div class="admin-note-text"><?= htmlspecialchars($serviceStats['categorie_top'] ?? 'Aucune') ?></div>
                                </div>
                            </div>
                            <div class="admin-list-item">
                                <div class="admin-list-icon"><i class="fas fa-image"></i></div>
                                <div>
                                    <strong>Miniatures</strong>
                                    <div class="admin-note-text"><?= (int)($serviceStats['avec_thumbnail'] ?? 0) ?> service(s) avec image.</div>
                                </div>
                            </div>
                            <?php foreach (($serviceInsights['top_freelancers'] ?? []) as $freelancerName => $count): ?>
                                <div class="admin-list-item">
                                    <div class="admin-list-icon"><i class="fas fa-user-tie"></i></div>
                                    <div>
                                        <strong><?= htmlspecialchars($freelancerName) ?></strong>
                                        <div class="admin-note-text"><?= (int)$count ?> service(s) publie(s).</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div id="service-table-panel" class="service-side-panel" style="display:none;">
                        <div class="sw-label" style="margin-bottom:.75rem;">Statistiques par freelancer</div>
                        <div style="overflow-x:auto; margin-bottom:1rem;">
                            <table class="admin-table" style="min-width:100%; font-size:.88rem;">
                                <thead>
                                    <tr>
                                        <th>Freelancer</th>
                                        <th>Services</th>
                                        <th>Actifs</th>
                                        <th>Prix moy.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach (($serviceInsights['freelancer_stats'] ?? []) as $freelancerName => $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($freelancerName) ?></td>
                                        <td><?= (int)$row['count'] ?></td>
                                        <td><?= (int)$row['active'] ?></td>
                                        <td><?= number_format($row['count'] > 0 ? ((float)$row['total_price'] / (int)$row['count']) : 0, 0) ?> DT</td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="sw-label" style="margin-bottom:.75rem;">Statistiques par categorie</div>
                        <div style="overflow-x:auto;">
                            <table class="admin-table" style="min-width:100%; font-size:.88rem;">
                                <thead>
                                    <tr>
                                        <th>Categorie</th>
                                        <th>Services</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach (($serviceInsights['category_rows'] ?? []) as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= (int)$row['count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const statusChartCanvas = document.getElementById('servicesStatusChart');
    if (statusChartCanvas) {
        new Chart(statusChartCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Actifs', 'En attente', 'Suspendus'],
                datasets: [{
                    data: [
                        <?= (int)($serviceStats['actifs'] ?? 0) ?>,
                        <?= (int)($serviceStats['attente'] ?? 0) ?>,
                        <?= (int)($serviceStats['suspendus'] ?? 0) ?>
                    ],
                    backgroundColor: ['#257a4b', '#e07020', '#ba4b44'],
                    borderColor: ['#ffffff', '#ffffff', '#ffffff'],
                    borderWidth: 3,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            color: '#62606a',
                            font: {
                                family: 'DM Sans',
                                size: 12,
                                weight: '600'
                            }
                        }
                    }
                },
                cutout: '64%'
            }
        });
    }

    const statusMessages = {
        '1': 'Service ajoute avec succes.',
        '2': 'Service modifie avec succes.',
        '3': 'Service supprime avec succes.'
    };
    const status = new URLSearchParams(window.location.search).get('success');
    const error = new URLSearchParams(window.location.search).get('error');

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

    if (error === 'admin_service_form_disabled') {
        Swal.fire({
            icon: 'info',
            title: 'Action non autorisee',
            text: 'L admin peut gerer le statut ou supprimer un service, mais ne peut pas ajouter ou modifier un service.',
            confirmButtonColor: '#e07020',
            background: '#fffaf4',
            color: '#1f1f23'
        });
    }

    document.querySelectorAll('.js-service-action').forEach((link) => {
        link.addEventListener('click', async (event) => {
            event.preventDefault();
            const result = await Swal.fire({
                icon: 'question',
                title: link.dataset.title || 'Confirmer cette action ?',
                text: link.dataset.text || 'Cette action va modifier les donnees.',
                showCancelButton: true,
                confirmButtonText: 'Confirmer',
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

    document.querySelectorAll('.admin-sidebar-toggle').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.admin-sidebar-toggle').forEach((item) => {
                item.classList.remove('active', 'admin-btn-primary');
                item.classList.add('admin-btn-outline');
            });
            button.classList.add('active', 'admin-btn-primary');
            button.classList.remove('admin-btn-outline');

            document.querySelectorAll('.service-side-panel').forEach((panel) => {
                panel.style.display = 'none';
            });

            const target = document.getElementById(button.dataset.servicePanel);
            if (target) {
                target.style.display = 'block';
            }
        });
    });
});
</script>
</body>
</html>
