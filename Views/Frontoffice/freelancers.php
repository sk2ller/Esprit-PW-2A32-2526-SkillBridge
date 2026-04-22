<?php
require_once __DIR__ . '/../../Controllers/UserController.php';
require_once __DIR__ . '/../../Controllers/InteractionController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ?action=login');
    exit;
}

$userController = new UserController();
$interactionController = new InteractionController();
$currentUserId = $_SESSION['user_id'];
$role = $_SESSION['user_role'] ?? null;

$search = $_GET['search'] ?? '';
$niveau = $_GET['niveau'] ?? '';
$availability = $_GET['availability'] ?? '';
$minRating = $_GET['min_rating'] ?? '';

$filters = [];
if (!empty($search)) {
    $filters['search'] = $search;
}
if (!empty($niveau)) {
    $filters['niveau'] = $niveau;
}
if (!empty($availability)) {
    $filters['availability'] = $availability;
}
if ($minRating !== '') {
    $filters['min_rating'] = $minRating;
}

$freelancers = $userController->getFreelancers($filters);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'];
    $freelancerId = (int) ($_POST['freelancer_id'] ?? 0);

    if ($action === 'like') {
        $result = $interactionController->addInteraction($currentUserId, $freelancerId, 'like');
        $type = 'like';
    } elseif ($action === 'dislike') {
        $result = $interactionController->addInteraction($currentUserId, $freelancerId, 'dislike');
        $type = 'dislike';
    } elseif ($action === 'remove') {
        $result = $interactionController->removeInteraction($currentUserId, $freelancerId);
        $type = 'none';
    } else {
        $result = false;
        $type = 'none';
    }

    if ($result) {
        echo json_encode([
            'success' => true,
            'type' => $type,
            'likes' => $interactionController->getLikesCount($freelancerId),
            'dislikes' => $interactionController->getDislikesCount($freelancerId)
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

        body.modal-open-soft {
            overflow: hidden;
        }

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

        .freelancers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }

        .freelancer-card {
            background: white;
            border-radius: 18px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
            display: flex;
            flex-direction: column;
        }

        .freelancer-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 34px rgba(224, 112, 32, 0.12);
            border-color: rgba(224, 112, 32, 0.4);
        }

        .freelancer-card.is-active {
            border-color: var(--primary);
            box-shadow: 0 18px 34px rgba(224, 112, 32, 0.16);
        }

        .freelancer-header {
            background: linear-gradient(135deg, var(--primary) 0%, #f5a962 100%);
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .freelancer-avatar {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1rem;
            color: var(--primary);
            font-weight: bold;
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .freelancer-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            gap: 1rem;
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
            font-weight: 700;
        }

        .summary-chip {
            margin-top: 1rem;
            padding: 1rem 1.05rem;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(224, 112, 32, 0.08) 0%, rgba(245, 169, 98, 0.14) 100%);
            color: var(--text);
            font-size: 0.92rem;
            line-height: 1.6;
            min-height: 90px;
        }

        .card-actions {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            margin-top: 1.15rem;
        }

        .expand-toggle {
            flex: 1;
            padding: 0.9rem 1rem;
            border: 1px solid rgba(224, 112, 32, 0.22);
            background: #fff7f1;
            color: var(--primary-dark);
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .expand-toggle:hover {
            background: #fff1e5;
            border-color: rgba(224, 112, 32, 0.45);
            transform: translateY(-1px);
        }

        .mini-feedback {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.75rem 0.95rem;
            border-radius: 999px;
            background: var(--bg-light);
            color: var(--text-light);
            font-size: 0.88rem;
            white-space: nowrap;
        }

        .freelancer-modal {
            position: fixed;
            inset: 0;
            background: rgba(12, 16, 24, 0.6);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.28s ease, visibility 0.28s ease;
            z-index: 1055;
        }

        .freelancer-modal.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .freelancer-modal-dialog {
            width: min(980px, 100%);
            max-height: 90vh;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 28px 70px rgba(0,0,0,0.28);
            transform: translateY(28px) scale(0.97);
            transition: transform 0.32s ease;
            display: flex;
            flex-direction: column;
        }

        .freelancer-modal.is-visible .freelancer-modal-dialog {
            transform: translateY(0) scale(1);
        }

        .modal-topbar {
            display: flex;
            justify-content: flex-end;
            padding: 1rem 1rem 0;
        }

        .modal-close {
            width: 42px;
            height: 42px;
            border: none;
            border-radius: 50%;
            background: rgba(26, 26, 26, 0.08);
            color: var(--secondary);
            font-size: 1rem;
            transition: all 0.25s ease;
        }

        .modal-close:hover {
            background: rgba(224, 112, 32, 0.14);
            color: var(--primary-dark);
            transform: rotate(90deg);
        }

        .modal-content-wrap {
            overflow-y: auto;
            padding: 0 2rem 2rem;
        }

        .modal-hero {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 1.5rem;
            align-items: stretch;
            margin-top: 0.2rem;
        }

        .modal-profile-card {
            background: linear-gradient(160deg, #1f232a 0%, #343a40 100%);
            color: white;
            border-radius: 22px;
            padding: 1.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .modal-avatar {
            width: 112px;
            height: 112px;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 1rem;
            background: white;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.4rem;
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(0,0,0,0.2);
        }

        .modal-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-profile-card h2 {
            font-size: 1.65rem;
            margin-bottom: 0.35rem;
        }

        .modal-profile-card p {
            margin-bottom: 0.55rem;
            color: rgba(255,255,255,0.82);
        }

        .modal-badges {
            width: 100%;
            display: grid;
            gap: 0.7rem;
            margin-top: 1.2rem;
        }

        .modal-badge {
            padding: 0.85rem 1rem;
            border-radius: 14px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .modal-badge span {
            display: block;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255,255,255,0.6);
            margin-bottom: 0.2rem;
        }

        .modal-badge strong {
            font-size: 1rem;
        }

        .modal-main {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .modal-intro {
            background: linear-gradient(135deg, rgba(224, 112, 32, 0.08) 0%, rgba(245, 169, 98, 0.16) 100%);
            border: 1px solid rgba(224, 112, 32, 0.12);
            border-radius: 22px;
            padding: 1.5rem;
        }

        .modal-intro h3 {
            margin-bottom: 0.45rem;
            font-size: 1.2rem;
            color: var(--secondary);
        }

        .modal-intro p {
            margin: 0;
            color: var(--text-light);
            line-height: 1.7;
        }

        .modal-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .modal-block {
            background: white;
            border-radius: 18px;
            border: 1px solid var(--border);
            padding: 1.25rem 1.2rem;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }

        .modal-block h4 {
            margin-bottom: 0.65rem;
            font-size: 1rem;
            color: var(--secondary);
        }

        .modal-block p,
        .modal-block span,
        .modal-block a {
            margin: 0;
            color: var(--text-light);
            line-height: 1.7;
            word-break: break-word;
        }

        .modal-block.is-wide {
            grid-column: 1 / -1;
        }

        .modal-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-top: 0.25rem;
        }

        .modal-feedback-count {
            color: var(--text-light);
            font-size: 0.92rem;
            font-weight: 600;
        }

        .feedback-actions {
            display: flex;
            gap: 0.9rem;
            margin-left: auto;
        }

        .btn-interact {
            min-width: 150px;
            padding: 0.95rem 1rem;
            border: 1px solid var(--border);
            background: white;
            border-radius: 14px;
            cursor: pointer;
            font-size: 0.98rem;
            transition: all 0.25s ease;
            font-weight: 700;
            color: var(--text-light);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
        }

        .btn-interact:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(224, 112, 32, 0.14);
        }

        .btn-interact.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

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

        @media (max-width: 991px) {
            .modal-hero {
                grid-template-columns: 1fr;
            }

            .modal-grid {
                grid-template-columns: 1fr;
            }
        }

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

            .card-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .mini-feedback {
                justify-content: center;
            }

            .freelancer-modal {
                padding: 0.85rem;
            }

            .modal-content-wrap {
                padding: 0 1rem 1rem;
            }

            .modal-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .feedback-actions {
                margin-left: 0;
                width: 100%;
            }

            .btn-interact {
                min-width: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
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

    <div class="page-header">
        <div class="container">
            <h1>Find Freelancers</h1>
            <p>Discover and collaborate with talented professionals</p>
        </div>
    </div>

    <main class="container" style="padding: 0 2rem;">
        <div class="filters-section">
            <form id="freelancerFilterForm" method="GET" action="?action=freelancers">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($search) ?>">

                    <select name="niveau">
                        <option value="">-- Experience Level --</option>
                        <option value="dÃ©butant" <?= ($niveau === 'dÃ©butant') ? 'selected' : '' ?>>Beginner</option>
                        <option value="intermÃ©diaire" <?= ($niveau === 'intermÃ©diaire') ? 'selected' : '' ?>>Intermediate</option>
                        <option value="expert" <?= ($niveau === 'expert') ? 'selected' : '' ?>>Expert</option>
                    </select>

                    <select name="availability">
                        <option value="">-- Availability --</option>
                        <option value="available" <?= ($availability === 'available') ? 'selected' : '' ?>>Available</option>
                        <option value="part-time" <?= ($availability === 'part-time') ? 'selected' : '' ?>>Part-time</option>
                        <option value="full-time" <?= ($availability === 'full-time') ? 'selected' : '' ?>>Full-time</option>
                    </select>

                    <select name="min_rating">
                        <option value="">-- Minimum Rating --</option>
                        <option value="1" <?= ($minRating === '1') ? 'selected' : '' ?>>1+ / 5</option>
                        <option value="2" <?= ($minRating === '2') ? 'selected' : '' ?>>2+ / 5</option>
                        <option value="3" <?= ($minRating === '3') ? 'selected' : '' ?>>3+ / 5</option>
                        <option value="4" <?= ($minRating === '4') ? 'selected' : '' ?>>4+ / 5</option>
                        <option value="4.5" <?= ($minRating === '4.5') ? 'selected' : '' ?>>4.5+ / 5</option>
                    </select>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search me-2"></i>Search
                    </button>
                    <button type="button" class="btn-reset" id="resetFiltersBtn">
                        <i class="fas fa-redo me-2"></i>Reset
                    </button>
                </div>
            </form>
        </div>

        <?php if (empty($freelancers)): ?>
            <div class="empty-state" id="emptyState">
                <h2>No Freelancers Found</h2>
                <p>Try adjusting your search filters or check back later</p>
            </div>
        <?php else: ?>
            <div class="freelancers-grid" id="freelancersGrid">
                <?php foreach ($freelancers as $freelancer): ?>
                    <?php
                    $rating = $interactionController->calculateRating($freelancer->getIdUser());
                    $likes = $interactionController->getLikesCount($freelancer->getIdUser());
                    $dislikes = $interactionController->getDislikesCount($freelancer->getIdUser());
                    $currentInteraction = $interactionController->getInteraction($currentUserId, $freelancer->getIdUser());
                    $avatar = strtoupper(mb_substr($freelancer->getPrenom(), 0, 1) . mb_substr($freelancer->getNom(), 0, 1));
                    $profilePicture = $freelancer->getProfilePicture();
                    $summaryText = trim((string) $freelancer->getSkillSummary());
                    $bioText = trim((string) $freelancer->getBio());
                    $previewText = $summaryText !== '' ? $summaryText : ($bioText !== '' ? mb_strimwidth($bioText, 0, 110, '...') : 'This freelancer is ready to collaborate on new projects.');
                    ?>
                    <div class="freelancer-card"
                         data-id="<?= $freelancer->getIdUser() ?>"
                         data-name="<?= htmlspecialchars($freelancer->getPrenom() . ' ' . $freelancer->getNom(), ENT_QUOTES) ?>"
                         data-name-search="<?= htmlspecialchars(strtolower($freelancer->getPrenom() . ' ' . $freelancer->getNom()), ENT_QUOTES) ?>"
                         data-niveau="<?= htmlspecialchars($freelancer->getNiveau(), ENT_QUOTES) ?>"
                         data-availability="<?= htmlspecialchars($freelancer->getAvailability(), ENT_QUOTES) ?>"
                         data-rating="<?= htmlspecialchars(number_format($rating, 1, '.', ''), ENT_QUOTES) ?>"
                         data-phone="<?= htmlspecialchars($freelancer->getPhone() ?: 'Not provided yet', ENT_QUOTES) ?>"
                         data-skill-summary="<?= htmlspecialchars($freelancer->getSkillSummary() ?: 'No skill summary added yet', ENT_QUOTES) ?>"
                         data-bio="<?= htmlspecialchars($freelancer->getBio() ?: 'This freelancer has not added a bio yet.', ENT_QUOTES) ?>"
                         data-experience="<?= htmlspecialchars($freelancer->getExperienceDescription() ?: 'Experience details will appear here once completed.', ENT_QUOTES) ?>"
                         data-profile-picture="<?= htmlspecialchars($profilePicture ?: '', ENT_QUOTES) ?>"
                         data-avatar="<?= htmlspecialchars($avatar, ENT_QUOTES) ?>"
                         data-likes="<?= $likes ?>"
                         data-dislikes="<?= $dislikes ?>"
                         data-current-interaction="<?= htmlspecialchars($currentInteraction ?: 'none', ENT_QUOTES) ?>">
                        <div class="freelancer-header">
                            <div class="freelancer-avatar">
                                <?php if (!empty($profilePicture)): ?>
                                    <img src="<?= htmlspecialchars($profilePicture) ?>" alt="<?= htmlspecialchars($freelancer->getPrenom() . ' ' . $freelancer->getNom()) ?>">
                                <?php else: ?>
                                    <?= htmlspecialchars($avatar) ?>
                                <?php endif; ?>
                            </div>
                            <div class="freelancer-name"><?= htmlspecialchars($freelancer->getPrenom() . ' ' . $freelancer->getNom()) ?></div>
                            <div class="freelancer-role"><?= htmlspecialchars(ucfirst($freelancer->getNiveau())) ?></div>
                        </div>

                        <div class="freelancer-body">
                            <div class="info-item">
                                <span class="info-label">Rating</span>
                                <span class="rating-stars"><?= number_format($rating, 1) ?>/5</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Availability</span>
                                <span><?= htmlspecialchars(ucfirst($freelancer->getAvailability())) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Feedback</span>
                                <span><span style="color: var(--primary);">👍 <?= $likes ?></span> <span style="margin-left: 0.5rem; color: #e74c3c;">👎 <?= $dislikes ?></span></span>
                            </div>

                            <div class="summary-chip">
                                <?= htmlspecialchars($previewText) ?>
                            </div>

                            <div class="card-actions">
                                <button type="button" class="expand-toggle" onclick="openFreelancerModal(this)">
                                    Voir freelancer
                                </button>
                                <div class="mini-feedback">
                                    <span>👍 <?= $likes ?></span>
                                    <span>👎 <?= $dislikes ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <div class="freelancer-modal" id="freelancerModal" aria-hidden="true">
        <div class="freelancer-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalFreelancerName">
            <div class="modal-topbar">
                <button type="button" class="modal-close" id="closeFreelancerModal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-content-wrap">
                <div class="modal-hero">
                    <div class="modal-profile-card">
                        <div class="modal-avatar" id="modalFreelancerAvatar"></div>
                        <h2 id="modalFreelancerName"></h2>
                        <p id="modalFreelancerRole"></p>
                        <div class="modal-badges">
                            <div class="modal-badge">
                                <span>Availability</span>
                                <strong id="modalFreelancerAvailability"></strong>
                            </div>
                            <div class="modal-badge">
                                <span>Rating</span>
                                <strong id="modalFreelancerRating"></strong>
                            </div>
                        </div>
                    </div>

                    <div class="modal-main">
                        <div class="modal-intro">
                            <h3>Professional overview</h3>
                            <p id="modalFreelancerSummary"></p>
                        </div>

                        <div class="modal-grid">
                            <div class="modal-block">
                                <h4>Phone</h4>
                                <span id="modalFreelancerPhone"></span>
                            </div>
                            <div class="modal-block">
                                <h4>Skill summary</h4>
                                <span id="modalFreelancerSkillSummary"></span>
                            </div>
                            <div class="modal-block is-wide">
                                <h4>Bio</h4>
                                <p id="modalFreelancerBio"></p>
                            </div>
                            <div class="modal-block is-wide">
                                <h4>Experience description</h4>
                                <p id="modalFreelancerExperience"></p>
                            </div>
                        </div>

                        <div class="modal-actions">
                            <div class="modal-feedback-count" id="modalFreelancerFeedback"></div>
                            <div class="feedback-actions">
                                <button class="btn-interact" id="modalLikeButton" type="button">
                                    <span>👍</span>
                                    <span>Like</span>
                                </button>
                                <button class="btn-interact" id="modalDislikeButton" type="button">
                                    <span>👎</span>
                                    <span>Dislike</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 SkillBridge. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const freelancerFilterForm = document.getElementById('freelancerFilterForm');
        const freelancersGrid = document.getElementById('freelancersGrid');
        const emptyState = document.getElementById('emptyState');
        const resetFiltersBtn = document.getElementById('resetFiltersBtn');
        const freelancerModal = document.getElementById('freelancerModal');
        const closeFreelancerModalButton = document.getElementById('closeFreelancerModal');
        const modalFreelancerAvatar = document.getElementById('modalFreelancerAvatar');
        const modalFreelancerName = document.getElementById('modalFreelancerName');
        const modalFreelancerRole = document.getElementById('modalFreelancerRole');
        const modalFreelancerAvailability = document.getElementById('modalFreelancerAvailability');
        const modalFreelancerRating = document.getElementById('modalFreelancerRating');
        const modalFreelancerSummary = document.getElementById('modalFreelancerSummary');
        const modalFreelancerPhone = document.getElementById('modalFreelancerPhone');
        const modalFreelancerSkillSummary = document.getElementById('modalFreelancerSkillSummary');
        const modalFreelancerBio = document.getElementById('modalFreelancerBio');
        const modalFreelancerExperience = document.getElementById('modalFreelancerExperience');
        const modalFreelancerFeedback = document.getElementById('modalFreelancerFeedback');
        const modalLikeButton = document.getElementById('modalLikeButton');
        const modalDislikeButton = document.getElementById('modalDislikeButton');

        let activeFreelancerCard = null;

        function formatTextWithBreaks(value) {
            return value.replace(/\n/g, '<br>');
        }

        function applyFreelancerFilters() {
            if (!freelancerFilterForm || !freelancersGrid) {
                return;
            }

            const cards = freelancersGrid.querySelectorAll('.freelancer-card');
            const searchValue = freelancerFilterForm.elements['search'].value.trim().toLowerCase();
            const niveauValue = freelancerFilterForm.elements['niveau'].value;
            const availabilityValue = freelancerFilterForm.elements['availability'].value;
            const minRatingValue = freelancerFilterForm.elements['min_rating'].value;
            const minimumRating = minRatingValue === '' ? null : parseFloat(minRatingValue);
            let visibleCount = 0;

            cards.forEach(function(card) {
                const matchesSearch = searchValue === '' || (card.dataset.nameSearch || '').includes(searchValue);
                const matchesNiveau = niveauValue === '' || (card.dataset.niveau || '') === niveauValue;
                const matchesAvailability = availabilityValue === '' || (card.dataset.availability || '') === availabilityValue;
                const matchesRating = minimumRating === null || parseFloat(card.dataset.rating || '0') >= minimumRating;
                const isVisible = matchesSearch && matchesNiveau && matchesAvailability && matchesRating;

                card.style.display = isVisible ? '' : 'none';
                if (isVisible) {
                    visibleCount++;
                }
            });

            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        if (freelancerFilterForm) {
            freelancerFilterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                applyFreelancerFilters();
            });

            freelancerFilterForm.elements['search'].addEventListener('input', applyFreelancerFilters);
            freelancerFilterForm.elements['niveau'].addEventListener('change', applyFreelancerFilters);
            freelancerFilterForm.elements['availability'].addEventListener('change', applyFreelancerFilters);
            freelancerFilterForm.elements['min_rating'].addEventListener('change', applyFreelancerFilters);
        }

        if (resetFiltersBtn && freelancerFilterForm) {
            resetFiltersBtn.addEventListener('click', function() {
                freelancerFilterForm.reset();
                applyFreelancerFilters();
            });
        }

        function updateModalInteractionState(card) {
            const currentInteraction = card.dataset.currentInteraction || 'none';
            modalLikeButton.classList.toggle('active', currentInteraction === 'like');
            modalDislikeButton.classList.toggle('active', currentInteraction === 'dislike');
        }

        function openFreelancerModal(button) {
            const card = button.closest('.freelancer-card');
            activeFreelancerCard = card;

            document.querySelectorAll('.freelancer-card.is-active').forEach(function(activeCard) {
                activeCard.classList.remove('is-active');
            });
            card.classList.add('is-active');

            const imagePath = card.dataset.profilePicture || '';
            if (imagePath !== '') {
                modalFreelancerAvatar.innerHTML = '<img src="' + imagePath + '" alt="' + card.dataset.name + '">';
            } else {
                modalFreelancerAvatar.textContent = card.dataset.avatar || '';
            }

            modalFreelancerName.textContent = card.dataset.name || '';
            modalFreelancerRole.textContent = card.dataset.niveau || '';
            modalFreelancerAvailability.textContent = card.dataset.availability || '';
            modalFreelancerRating.textContent = (card.dataset.rating || '0') + '/5';
            modalFreelancerSummary.textContent = card.dataset.skillSummary || '';
            modalFreelancerPhone.textContent = card.dataset.phone || '';
            modalFreelancerSkillSummary.textContent = card.dataset.skillSummary || '';
            modalFreelancerBio.innerHTML = formatTextWithBreaks(card.dataset.bio || '');
            modalFreelancerExperience.innerHTML = formatTextWithBreaks(card.dataset.experience || '');
            modalFreelancerFeedback.textContent = 'Feedback: ' + (card.dataset.likes || '0') + ' likes • ' + (card.dataset.dislikes || '0') + ' dislikes';

            modalLikeButton.dataset.freelancer = card.dataset.id || '';
            modalDislikeButton.dataset.freelancer = card.dataset.id || '';
            updateModalInteractionState(card);

            freelancerModal.classList.add('is-visible');
            freelancerModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open-soft');
        }

        function closeFreelancerModal() {
            freelancerModal.classList.remove('is-visible');
            freelancerModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open-soft');

            if (activeFreelancerCard) {
                activeFreelancerCard.classList.remove('is-active');
                activeFreelancerCard = null;
            }
        }

        if (closeFreelancerModalButton) {
            closeFreelancerModalButton.addEventListener('click', closeFreelancerModal);
        }

        if (freelancerModal) {
            freelancerModal.addEventListener('click', function(event) {
                if (event.target === freelancerModal) {
                    closeFreelancerModal();
                }
            });
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && freelancerModal.classList.contains('is-visible')) {
                closeFreelancerModal();
            }
        });

        applyFreelancerFilters();

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

        if (modalLikeButton) {
            modalLikeButton.addEventListener('click', function() {
                if (this.dataset.freelancer) {
                    toggleInteraction(this.dataset.freelancer, 'like');
                }
            });
        }

        if (modalDislikeButton) {
            modalDislikeButton.addEventListener('click', function() {
                if (this.dataset.freelancer) {
                    toggleInteraction(this.dataset.freelancer, 'dislike');
                }
            });
        }
    </script>
</body>
</html>
