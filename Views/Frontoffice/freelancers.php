<?php
require_once __DIR__ . '/../../Controllers/UserController.php';
require_once __DIR__ . '/../../Controllers/InteractionController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ?action=login');
    exit;
}

$userController = new UserController();
$interactionController = new InteractionController();
$currentUserId = $_SESSION['user_id'];
$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

// Get filters from GET
$search = $_GET['search'] ?? '';
$niveau = $_GET['niveau'] ?? '';
$availability = $_GET['availability'] ?? '';

$filters = [];
if (!empty($search)) $filters['search'] = $search;
if (!empty($niveau)) $filters['niveau'] = $niveau;
if (!empty($availability)) $filters['availability'] = $availability;

// Get freelancers
$freelancers = $userController->getFreelancers($filters);

// Handle like/dislike via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    $freelancer_id = (int)$_POST['freelancer_id'];
    
    if ($action === 'like') {
        $result = $interactionController->addInteraction($currentUserId, $freelancer_id, 'like');
        $type = 'like';
    } elseif ($action === 'dislike') {
        $result = $interactionController->addInteraction($currentUserId, $freelancer_id, 'dislike');
        $type = 'dislike';
    } elseif ($action === 'remove') {
        $result = $interactionController->removeInteraction($currentUserId, $freelancer_id);
        $type = 'none';
    }
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'type' => $type,
            'likes' => $interactionController->getLikesCount($freelancer_id),
            'dislikes' => $interactionController->getDislikesCount($freelancer_id)
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Freelancers - SkillBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: #e07020;
            --primary-dark: #c85a14;
            --secondary: #1a1a1a;
            --text: #2d3436;
            --text-light: #636e72;
            --border: #dfe6e9;
            --bg-light: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            color: var(--text);
        }

        /* NAVBAR */
        .navbar-custom {
            background:
                linear-gradient(90deg, #b84f12 0%, #e07020 16%, #f3a25a 30%, #ffffff 46%, #ffffff 100%);
            border-bottom: 1px solid var(--border);
            padding: 1rem 0;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }

        .navbar-brand {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.15rem 0;
            margin-left: -1.6rem;
        }

        .navbar-brand img {
            height: 58px;
            filter: drop-shadow(0 10px 22px rgba(0, 0, 0, 0.18));
        }

        .navbar-custom .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: color 0.3s;
            font-size: 0.95rem;
        }

        .navbar-custom .nav-link:hover {
            color: var(--primary) !important;
        }

        .btn-nav-primary {
            background: var(--primary);
            color: white !important;
            border-radius: 6px;
            padding: 0.5rem 1.25rem;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
            border: none;
            font-weight: 500;
        }

        .btn-nav-primary:hover {
            background: var(--primary-dark);
            color: white !important;
        }

        /* PAGE CONTENT */
        .page-header {
            background: linear-gradient(135deg, var(--secondary) 0%, #2d2d2d 100%);
            color: white;
            padding: 3rem 2rem;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.9);
        }

        /* FILTERS */
        .filters-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .filter-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .filter-group input,
        .filter-group select {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(224, 112, 32, 0.1);
            outline: none;
        }

        .filter-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-search,
        .btn-reset {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-search {
            background: var(--primary);
            color: white;
        }

        .btn-search:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(224, 112, 32, 0.3);
        }

        .btn-reset {
            background: var(--bg-light);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-reset:hover {
            background: var(--border);
        }

        /* FREELANCERS GRID */
        .freelancers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .freelancer-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .freelancer-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(224, 112, 32, 0.15);
            border-color: var(--primary);
        }

        .freelancer-header {
            background: linear-gradient(135deg, var(--primary) 0%, #f5a962 100%);
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .freelancer-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1rem;
            color: var(--primary);
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .freelancer-name {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .freelancer-role {
            font-size: 0.95rem;
            opacity: 0.9;
            text-transform: capitalize;
        }

        .freelancer-body {
            padding: 1.5rem;
            flex: 1;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.95rem;
            color: var(--text);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-light);
            font-weight: 500;
        }

        .rating-stars {
            color: var(--primary);
            font-weight: 600;
        }

        .freelancer-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 0.75rem;
        }

        .btn-interact {
            flex: 1;
            padding: 0.75rem;
            border: 2px solid var(--border);
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s;
            font-weight: 500;
            color: var(--text-light);
        }

        .btn-interact:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: scale(1.05);
        }

        .btn-interact.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .empty-state h2 {
            color: var(--text);
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }

        .empty-state p {
            color: var(--text-light);
        }

        /* FOOTER */
        footer {
            background: var(--secondary);
            color: white;
            padding: 3rem 2rem 1.5rem;
            text-align: center;
            margin-top: 5rem;
        }

        footer p {
            margin: 0;
            font-size: 0.9rem;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .page-header {
                padding: 2rem 1rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .freelancers-grid {
                grid-template-columns: 1fr;
            }

            .filter-buttons {
                flex-direction: column;
            }

            .btn-search,
            .btn-reset {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="?action=home">
                <img src="/Views/assets/img/logo1.png" alt="SkillBridge">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user-circle me-2"></i><?= htmlspecialchars($_SESSION['user_prenom']) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a href="?action=home" class="nav-link">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=profile" class="nav-link">
                            <i class="fas fa-user me-1"></i>Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=logout" class="btn-nav-primary ms-2">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="container">
            <h1>Find Freelancers</h1>
            <p>Discover and collaborate with talented professionals</p>
        </div>
    </div>

    <main class="container" style="padding: 0 2rem;">
        <!-- FILTERS -->
        <div class="filters-section">
            <form method="GET" action="?action=freelancers">
                <div class="filter-group">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search by name..." 
                        value="<?= htmlspecialchars($search) ?>"
                    >
                    
                    <select name="niveau">
                        <option value="">-- Experience Level --</option>
                        <option value="débutant" <?= ($niveau === 'débutant') ? 'selected' : '' ?>>Beginner</option>
                        <option value="intermédiaire" <?= ($niveau === 'intermédiaire') ? 'selected' : '' ?>>Intermediate</option>
                        <option value="expert" <?= ($niveau === 'expert') ? 'selected' : '' ?>>Expert</option>
                    </select>
                    
                    <select name="availability">
                        <option value="">-- Availability --</option>
                        <option value="available" <?= ($availability === 'available') ? 'selected' : '' ?>>Available</option>
                        <option value="part-time" <?= ($availability === 'part-time') ? 'selected' : '' ?>>Part-time</option>
                        <option value="full-time" <?= ($availability === 'full-time') ? 'selected' : '' ?>>Full-time</option>
                    </select>
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search me-2"></i>Search
                    </button>
                    <button type="button" class="btn-reset" onclick="window.location='?action=freelancers'">
                        <i class="fas fa-redo me-2"></i>Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- FREELANCERS DISPLAY -->
        <?php if (empty($freelancers)): ?>
            <div class="empty-state">
                <h2>No Freelancers Found</h2>
                <p>Try adjusting your search filters or check back later</p>
            </div>
        <?php else: ?>
            <div class="freelancers-grid">
                <?php foreach ($freelancers as $freelancer): 
                    $rating = $interactionController->calculateRating($freelancer->getIdUser());
                    $likes = $interactionController->getLikesCount($freelancer->getIdUser());
                    $dislikes = $interactionController->getDislikesCount($freelancer->getIdUser());
                    $currentInteraction = $interactionController->getInteraction($currentUserId, $freelancer->getIdUser());
                    $avatar = strtoupper(substr($freelancer->getPrenom(), 0, 1) . substr($freelancer->getNom(), 0, 1));
                ?>
                    <div class="freelancer-card">
                        <div class="freelancer-header">
                            <div class="freelancer-avatar"><?= $avatar ?></div>
                            <div class="freelancer-name"><?= htmlspecialchars($freelancer->getPrenom() . ' ' . $freelancer->getNom()) ?></div>
                            <div class="freelancer-role"><?= ucfirst($freelancer->getNiveau()) ?></div>
                        </div>
                        
                        <div class="freelancer-body">
                            <div class="info-item">
                                <span class="info-label">⭐ Rating</span>
                                <span class="rating-stars"><?= number_format($rating, 1) ?>/5</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">📅 Availability</span>
                                <span><?= ucfirst($freelancer->getAvailability()) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Feedback</span>
                                <span><span style="color: var(--primary);">👍 <?= $likes ?></span> <span style="margin-left: 0.5rem; color: #e74c3c;">👎 <?= $dislikes ?></span></span>
                            </div>
                        </div>
                        
                        <div class="freelancer-footer">
                            <button class="btn-interact like-btn <?= ($currentInteraction === 'like') ? 'active' : '' ?>" 
                                    data-freelancer="<?= $freelancer->getIdUser() ?>" 
                                    onclick="toggleInteraction(<?= $freelancer->getIdUser() ?>, 'like')"
                                    title="Like this freelancer">
                                👍
                            </button>
                            <button class="btn-interact dislike-btn <?= ($currentInteraction === 'dislike') ? 'active' : '' ?>" 
                                    data-freelancer="<?= $freelancer->getIdUser() ?>" 
                                    onclick="toggleInteraction(<?= $freelancer->getIdUser() ?>, 'dislike')"
                                    title="Dislike this freelancer">
                                👎
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2026 SkillBridge. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleInteraction(freelancerId, type) {
            const formData = new FormData();
            formData.append('action', type);
            formData.append('freelancer_id', freelancerId);
            
            fetch('?action=freelancers', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
