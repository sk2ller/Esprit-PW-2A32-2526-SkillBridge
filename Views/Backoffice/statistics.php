<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header('Location: ?action=login');
    exit;
}

require_once __DIR__ . '/../../Controllers/UserController.php';
require_once __DIR__ . '/../../Controllers/InteractionController.php';
require_once __DIR__ . '/../../config.php';

$userController = new UserController();
$interactionController = new InteractionController();
$db = Config::getConnexion();

// Get statistics
$stats = [
    'total_users' => 0,
    'total_freelancers' => 0,
    'approved_freelancers' => 0,
    'total_clients' => 0,
    'total_admins' => 0,
    'banned_users' => 0,
    'pending_approval' => 0,
    'total_interactions' => 0,
    'total_likes' => 0,
    'total_dislikes' => 0,
    'avg_rating' => 0.0
];

try {
    // Total users
    $q = $db->query("SELECT COUNT(*) FROM User");
    $stats['total_users'] = $q->fetchColumn();

    // Freelancers (role 3)
    $q = $db->query("SELECT COUNT(*) FROM User WHERE id_role = 3");
    $stats['total_freelancers'] = $q->fetchColumn();

    // Approved freelancers
    $q = $db->query("SELECT COUNT(*) FROM User WHERE id_role = 3 AND is_approved = 1");
    $stats['approved_freelancers'] = $q->fetchColumn();

    // Pending freelancers
    $q = $db->query("SELECT COUNT(*) FROM User WHERE id_role = 3 AND is_approved = 0");
    $stats['pending_approval'] = $q->fetchColumn();

    // Clients (role 2)
    $q = $db->query("SELECT COUNT(*) FROM User WHERE id_role = 2");
    $stats['total_clients'] = $q->fetchColumn();

    // Admins (role 1)
    $q = $db->query("SELECT COUNT(*) FROM User WHERE id_role = 1");
    $stats['total_admins'] = $q->fetchColumn();

    // Banned users
    $q = $db->query("SELECT COUNT(*) FROM User WHERE is_banned = 1");
    $stats['banned_users'] = $q->fetchColumn();

    // Total interactions
    $q = $db->query("SELECT COUNT(*) FROM Interaction");
    $stats['total_interactions'] = $q->fetchColumn();

    // Total likes
    $q = $db->query("SELECT COUNT(*) FROM Interaction WHERE type = 'like'");
    $stats['total_likes'] = $q->fetchColumn();

    // Total dislikes
    $q = $db->query("SELECT COUNT(*) FROM Interaction WHERE type = 'dislike'");
    $stats['total_dislikes'] = $q->fetchColumn();

    // Average rating
    $q = $db->query("SELECT AVG(rating) FROM User WHERE id_role = 3");
    $stats['avg_rating'] = (float)($q->fetchColumn() ?? 0.0);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques — SkillBridge Admin</title>
    <link rel="stylesheet" href="/Views/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --charcoal: #1a1a1a;
            --white: #ffffff;
            --amber: #e07020;
            --amber-light: #f5a962;
            --creme: #f9f7f4;
            --text-light: #666666;
            --beige-border: #e8ddd4;
            --success: #28a745;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--creme);
            color: var(--charcoal);
        }

        .navbar-top {
            background: var(--charcoal);
            color: var(--white);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-top .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .navbar-top .logo img {
            height: 45px;
            width: auto;
        }

        .nav-buttons {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .btn {
            padding: 0.6rem 1.25rem;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            font-weight: 500;
        }

        .btn-secondary {
            background: transparent;
            color: var(--amber-light);
            border: 1px solid var(--amber-light);
        }

        .btn-secondary:hover {
            background: var(--amber-light);
            color: var(--charcoal);
        }

        main {
            padding: 2rem;
            min-height: calc(100vh - 68px - 65px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 3rem;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: var(--charcoal);
        }

        .page-header p {
            color: var(--text-light);
            font-size: 1.05rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            border: 1px solid var(--beige-border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--amber);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .stat-card.primary .stat-icon { color: var(--amber); }
        .stat-card.success .stat-icon { color: var(--success); }
        .stat-card.info .stat-icon { color: var(--info); }
        .stat-card.warning .stat-icon { color: var(--warning); }
        .stat-card.danger .stat-icon { color: var(--danger); }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin: 2rem 0 1rem;
            color: var(--charcoal);
            border-bottom: 2px solid var(--amber);
            padding-bottom: 0.5rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--amber);
            text-decoration: none;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            gap: 1rem;
        }

        .footer {
            background: var(--charcoal);
            color: var(--white);
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.8rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            main {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar-top">
        <div class="container">
            <a href="?action=home" class="logo">
                <img src="/Views/assets/img/logo1.png" alt="SkillBridge">
            </a>
            <div class="nav-buttons">
                <span>👤 <?= htmlspecialchars($_SESSION['user_prenom']) ?></span>
                <a href="?action=userlist" class="btn btn-secondary">Utilisateurs</a>
                <a href="?action=logout" class="btn btn-secondary">Déconnexion</a>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            <a href="?action=home" class="back-link">
                <i class="fas fa-arrow-left"></i> Retour à l'accueil
            </a>

            <div class="page-header">
                <h1>📊 Statistiques de la Plateforme</h1>
                <p>Métriques et performances globales de SkillBridge</p>
            </div>

            <!-- Users Overview -->
            <h2 class="section-title">Utilisateurs</h2>
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?= $stats['total_users'] ?></div>
                    <div class="stat-label">Utilisateurs Totaux</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon">💼</div>
                    <div class="stat-number"><?= $stats['total_freelancers'] ?></div>
                    <div class="stat-label">Freelancers</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon">👨‍💼</div>
                    <div class="stat-number"><?= $stats['total_clients'] ?></div>
                    <div class="stat-label">Clients</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon">🔑</div>
                    <div class="stat-number"><?= $stats['total_admins'] ?></div>
                    <div class="stat-label">Administrateurs</div>
                </div>
            </div>

            <!-- Freelancer Details -->
            <h2 class="section-title">Détails Freelancers</h2>
            <div class="stats-grid">
                <div class="stat-card success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number"><?= $stats['approved_freelancers'] ?></div>
                    <div class="stat-label">Freelancers Approuvés</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-number"><?= $stats['pending_approval'] ?></div>
                    <div class="stat-label">En Attente d'Approbation</div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-icon">🚫</div>
                    <div class="stat-number"><?= $stats['banned_users'] ?></div>
                    <div class="stat-label">Utilisateurs Bannis</div>
                </div>
                <div class="stat-card primary">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-number"><?= number_format($stats['avg_rating'], 2) ?>/5.0</div>
                    <div class="stat-label">Note Moyenne</div>
                </div>
            </div>

            <!-- Interactions -->
            <h2 class="section-title">Interactions & Évaluations</h2>
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">🔗</div>
                    <div class="stat-number"><?= $stats['total_interactions'] ?></div>
                    <div class="stat-label">Interactions Totales</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon">👍</div>
                    <div class="stat-number"><?= $stats['total_likes'] ?></div>
                    <div class="stat-label">Avis Positifs</div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-icon">👎</div>
                    <div class="stat-number"><?= $stats['total_dislikes'] ?></div>
                    <div class="stat-label">Avis Négatifs</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon">📈</div>
                    <div class="stat-number"><?= $stats['total_interactions'] > 0 ? round(($stats['total_likes'] / $stats['total_interactions']) * 100) : 0 ?>%</div>
                    <div class="stat-label">Taux de Satisfaction</div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2026 SkillBridge. Tous droits réservés.</p>
    </footer>
</body>
</html>
