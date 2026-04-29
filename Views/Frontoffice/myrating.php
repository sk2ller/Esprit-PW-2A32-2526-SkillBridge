<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header('Location: ?action=login');
    exit;
}

require_once __DIR__ . '/../../Controllers/UserController.php';
require_once __DIR__ . '/../../Controllers/InteractionController.php';

$userController = new UserController();
$interactionController = new InteractionController();

$currentUser = $userController->getUserById($_SESSION['user_id']);
$likes = $interactionController->getLikesCount($_SESSION['user_id']);
$dislikes = $interactionController->getDislikesCount($_SESSION['user_id']);
$rating = $interactionController->calculateRating($_SESSION['user_id']);

$totalVotes = $likes + $dislikes;
$likePercentage = $totalVotes > 0 ? round(($likes / $totalVotes) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rating - SkillBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Views/assets/css/skillbridge-front.css">
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
            --success: #27ae60;
            --warning: #f39c12;
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

        /* PAGE HEADER */
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

        /* RATING CARDS */
        .rating-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .rating-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .rating-label {
            color: var(--text-light);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .rating-stars {
            font-size: 2.5rem;
            color: var(--warning);
            margin: 1rem 0;
            letter-spacing: 0.2rem;
        }

        .rating-value {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0.5rem 0;
        }

        .rating-max {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .rating-total {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }

        /* STATS SECTION */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 2rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .stat-card.success {
            border-top: 4px solid var(--success);
        }

        .stat-card.warning {
            border-top: 4px solid var(--warning);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-light);
            font-weight: 500;
        }

        /* SATISFACTION METER */
        .satisfaction-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 2.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin-bottom: 3rem;
        }

        .satisfaction-card h3 {
            font-size: 1.2rem;
            color: var(--text);
            margin-bottom: 2rem;
            font-weight: 600;
        }

        .progress-bar-custom {
            height: 16px;
            background: var(--border);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--warning));
            border-radius: 8px;
            transition: width 0.3s ease;
        }

        .satisfaction-percentage {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-top: 1rem;
        }

        /* TIPS SECTION */
        .tips-section {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 2.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin-bottom: 3rem;
        }

        .tips-section h2 {
            font-size: 1.5rem;
            color: var(--text);
            margin-bottom: 2rem;
            font-weight: 600;
        }

        .tip-box {
            background: var(--bg-light);
            border-left: 4px solid var(--primary);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .tip-box:last-child {
            margin-bottom: 0;
        }

        .tip-box h4 {
            color: var(--text);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .tip-box p {
            color: var(--text-light);
            font-size: 0.95rem;
            margin: 0;
        }

        /* CTA SECTION */
        .cta-banner {
            background: linear-gradient(135deg, var(--primary) 0%, var(--warning) 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 3rem;
        }

        .cta-banner h3 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .cta-banner p {
            font-size: 1.05rem;
            margin-bottom: 1.5rem;
            color: rgba(255,255,255,0.95);
        }

        .cta-banner a {
            display: inline-block;
            background: white;
            color: var(--primary);
            padding: 0.75rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .cta-banner a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
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

            .rating-grid,
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .satisfaction-card,
            .tips-section {
                padding: 1.5rem;
            }

            .cta-banner {
                padding: 2rem 1.5rem;
            }

            .cta-banner h3 {
                font-size: 1.4rem;
            }
        }

        .container {
            max-width: 1320px;
        }

        body.skillbridge-front {
            font-family: 'DM Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top center, rgba(224,112,32,.07), transparent 26%),
                linear-gradient(180deg, #fffdf9 0%, #faf6f0 22%, #f8f1e7 100%);
            color: #1f1f23;
        }

        .page-header {
            background:
                radial-gradient(circle at top right, rgba(240,138,59,.16), transparent 30%),
                linear-gradient(135deg, #1e1e20 0%, #2d2d31 100%);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 0 0 28px 28px;
            box-shadow: 0 20px 40px rgba(30,30,32,.18);
            margin: 0 1.2rem 2.5rem;
        }

        .rating-card,
        .stat-card,
        .satisfaction-card,
        .tips-section,
        .cta-banner {
            background: rgba(255,255,255,.9);
            border: 1px solid #dfd1bd;
            border-radius: 24px;
            box-shadow: 0 16px 34px rgba(30,30,32,.08);
        }

        .rating-value,
        .satisfaction-percentage {
            color: #e07020;
        }

        .progress-bar-custom {
            background: #eadcc8;
            border-radius: 999px;
        }

        .progress-fill {
            background: linear-gradient(90deg, #e07020, #f08a3b);
        }

        .rating-page-shell {
            padding: 0 2rem 1rem;
            margin-bottom: 2rem;
        }

        .rating-page-shell .page-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(224,112,32,.14);
            border: 1px solid rgba(240,138,59,.24);
            color: #ffd8bd;
            font-weight: 800;
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .rating-grid {
            grid-template-columns: minmax(320px, 1.05fr) minmax(320px, .95fr);
            align-items: stretch;
        }

        .rating-card {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            min-height: 100%;
        }

        .rating-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(224,112,32,.16), transparent 34%),
                linear-gradient(135deg, rgba(255,255,255,.92), rgba(255,249,241,.86));
            z-index: -1;
        }

        .rating-card-main {
            border-color: rgba(224,112,32,.28);
            box-shadow: 0 22px 48px rgba(224,112,32,.12);
        }

        .rating-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: #7d624d;
            background: rgba(224,112,32,.1);
            border: 1px solid rgba(224,112,32,.14);
            border-radius: 999px;
            padding: 0.5rem 0.85rem;
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .rating-stars {
            display: flex;
            justify-content: center;
            gap: 0.35rem;
            color: #f5a524;
            font-size: 2.15rem;
            letter-spacing: 0;
            text-shadow: 0 10px 20px rgba(245,165,36,.18);
        }

        .rating-stars .empty-star {
            color: #e5d3bd;
            text-shadow: none;
        }

        .rating-value {
            font-size: clamp(3rem, 6vw, 4.6rem);
            line-height: 1;
            letter-spacing: -0.08em;
        }

        .rating-max {
            font-weight: 800;
            color: #927562;
        }

        .rating-total {
            width: min(100%, 380px);
            margin-left: auto;
            margin-right: auto;
            border-top-color: rgba(224,112,32,.18);
        }

        .feedback-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin: 1.5rem 0 2rem;
        }

        .feedback-mini {
            border-radius: 20px;
            padding: 1.35rem 1rem;
            background: #fffaf4;
            border: 1px solid #ead8c2;
        }

        .feedback-mini.positive {
            background: linear-gradient(135deg, rgba(39,174,96,.12), rgba(255,255,255,.88));
            border-color: rgba(39,174,96,.2);
        }

        .feedback-mini.negative {
            background: linear-gradient(135deg, rgba(243,156,18,.14), rgba(255,255,255,.88));
            border-color: rgba(243,156,18,.24);
        }

        .feedback-number {
            font-size: 2.55rem;
            line-height: 1;
            font-weight: 900;
            margin-bottom: 0.45rem;
        }

        .feedback-mini.positive .feedback-number {
            color: #238756;
        }

        .feedback-mini.negative .feedback-number {
            color: #d47a15;
        }

        .feedback-label {
            color: #806a58;
            font-size: 0.88rem;
            font-weight: 800;
        }

        .satisfaction-block {
            padding: 1.25rem;
            border-radius: 22px;
            background: rgba(255,250,244,.86);
            border: 1px solid #ead8c2;
        }

        .satisfaction-title {
            color: #806a58;
            font-size: 0.82rem;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.8rem;
        }

        .satisfaction-result {
            text-align: center;
            color: #e07020;
            font-weight: 900;
            margin-top: 0.65rem;
        }

        .stats-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            text-align: left;
            padding: 1.65rem;
        }

        .stat-card::after {
            content: "";
            position: absolute;
            width: 110px;
            height: 110px;
            right: -34px;
            top: -38px;
            border-radius: 50%;
            background: rgba(224,112,32,.1);
        }

        .stat-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: #fff2e7;
            color: #e07020;
            font-size: 1.2rem;
            margin-bottom: 1.25rem;
        }

        .stat-card.success .stat-icon {
            background: rgba(39,174,96,.12);
            color: #238756;
        }

        .stat-card.warning .stat-icon {
            background: rgba(243,156,18,.14);
            color: #d47a15;
        }

        .stat-number {
            color: #242126;
            letter-spacing: -0.04em;
        }

        .tips-section h2 {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .tips-section h2 i {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: #fff2e7;
            color: #e07020;
        }

        .tip-box {
            border-left: 0;
            border: 1px solid #ead8c2;
            background:
                linear-gradient(135deg, rgba(255,255,255,.92), rgba(255,248,239,.88));
            border-radius: 18px;
            transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        }

        .tip-box:hover {
            transform: translateY(-3px);
            border-color: rgba(224,112,32,.3);
            box-shadow: 0 16px 28px rgba(30,30,32,.08);
        }

        .tip-box h4 {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            color: #2b2520;
        }

        .tip-box h4 i {
            color: #e07020;
        }

        .cta-banner {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(255,255,255,.24), transparent 28%),
                linear-gradient(135deg, #201f22 0%, #3a2a20 48%, #e07020 100%);
            border: 1px solid rgba(224,112,32,.22);
            box-shadow: 0 22px 44px rgba(30,30,32,.16);
        }

        .cta-banner a {
            border-radius: 999px;
            box-shadow: 0 14px 24px rgba(0,0,0,.16);
        }

        .page-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(224,112,32,.14);
            border: 1px solid rgba(240,138,59,.24);
            color: #ffd8bd;
            font-weight: 800;
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .stat-icon,
        .tips-section h2,
        .tip-box h4,
        .cta-banner a {
            font-size: 0;
        }

        .stat-icon::before {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 1.2rem;
        }

        .stat-card.success .stat-icon::before {
            content: "\f164";
        }

        .stat-card.warning .stat-icon::before {
            content: "\f165";
        }

        .stat-card:not(.success):not(.warning) .stat-icon::before {
            content: "\f005";
        }

        .tips-section h2::before {
            content: "\f0eb";
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: #fff2e7;
            color: #e07020;
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 1.05rem;
        }

        .tips-section h2::after {
            content: "How to Improve Your Rating";
            font-size: 1.5rem;
            font-weight: 800;
            color: #2b2520;
        }

        .tip-box h4::before {
            content: "\f058";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 1rem;
            color: #e07020;
        }

        .tip-box h4::after {
            font-size: 1.05rem;
            font-weight: 800;
        }

        .tip-box:nth-of-type(1) h4::after {
            content: "Complete Your Profile";
        }

        .tip-box:nth-of-type(2) h4::after {
            content: "Be Professional";
        }

        .tip-box:nth-of-type(3) h4::after {
            content: "Communicate Clearly";
        }

        .tip-box:nth-of-type(4) h4::after {
            content: "Update Your Availability";
        }

        .tip-box:nth-of-type(5) h4::after {
            content: "Enhance Your Skills";
        }

        .cta-banner a::before {
            content: "Go to My Profile";
            font-size: 1rem;
        }

        .cta-banner a::after {
            content: "\f061";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 0.9rem;
            margin-left: 0.65rem;
        }

        footer {
            background: #1e1e20;
            border-top: 1px solid rgba(224,112,32,.18);
        }

        @media (max-width: 992px) {
            .rating-grid,
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .rating-page-shell {
                padding: 0 1rem;
            }

            .feedback-split {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="skillbridge-front">
    <?php require __DIR__ . '/partials/front_navbar.php'; ?>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="container">
            <div class="page-kicker"><i class="fas fa-chart-line"></i> Freelancer reputation</div>
            <h1>Your Rating & Feedback</h1>
            <p>Track your professional reputation and client satisfaction</p>
        </div>
    </div>

    <main class="container rating-page-shell">
        <!-- MAIN RATING & STATS -->
        <div class="rating-grid">
            <!-- Overall Rating -->
            <div class="rating-card rating-card-main">
                <p class="rating-label"><i class="fas fa-star"></i>Your Current Rating</p>
                <div class="rating-stars">
                    <?php
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                    ob_start();
                    
                    for ($i = 0; $i < 5; $i++) {
                        if ($i < $fullStars) {
                            echo '★';
                        } elseif ($i == $fullStars && $hasHalfStar) {
                            echo '½';
                        } else {
                            echo '☆';
                        }
                    }
                    ob_end_clean();

                    for ($i = 0; $i < 5; $i++) {
                        if ($i < $fullStars) {
                            echo '<i class="fas fa-star"></i>';
                        } elseif ($i == $fullStars && $hasHalfStar) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        } else {
                            echo '<i class="far fa-star empty-star"></i>';
                        }
                    }
                    ?>
                </div>
                <div class="rating-value"><?= number_format($rating, 2) ?></div>
                <div class="rating-max">out of 5.0</div>
                <div class="rating-total">
                    <?= $totalVotes ?> total feedback <?php if ($totalVotes === 0): ?>
                        <p style="margin-top: 1rem; font-size: 0.85rem; color: var(--text-light);">Start collaborating with clients to receive feedback!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistics -->
            <div class="rating-card">
                <p class="rating-label"><i class="fas fa-comments"></i>Feedback Statistics</p>
                
                <div class="feedback-split">
                    <div class="feedback-mini positive">
                        <div class="feedback-number"><?= $likes ?></div>
                        <div class="feedback-label"><i class="fas fa-thumbs-up me-1"></i>Positive</div>
                    </div>
                    <div class="feedback-mini negative">
                        <div class="feedback-number"><?= $dislikes ?></div>
                        <div class="feedback-label"><i class="fas fa-thumbs-down me-1"></i>Negative</div>
                    </div>
                </div>

                <div class="satisfaction-block">
                    <div class="satisfaction-title">Satisfaction Rate</div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill" style="width: <?= $likePercentage ?>%"></div>
                    </div>
                    <div class="satisfaction-result">
                        <?= $likePercentage ?>% Positive
                    </div>
                </div>
            </div>
        </div>

        <!-- SATISFACTION BREAKDOWN -->
        <div class="stats-grid">
            <div class="stat-card success">
                <div class="stat-icon">👍</div>
                <div class="stat-number"><?= $likes ?></div>
                <div class="stat-label">Positive Feedback</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">👎</div>
                <div class="stat-number"><?= $dislikes ?></div>
                <div class="stat-label">Negative Feedback</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-number" style="color: var(--primary);"><?= $totalVotes ?></div>
                <div class="stat-label">Total Feedbacks</div>
            </div>
        </div>

        <!-- TIPS SECTION -->
        <div class="tips-section">
            <h2>💡 How to Improve Your Rating</h2>
            
            <div class="tip-box">
                <h4>✓ Complete Your Profile</h4>
                <p>A detailed profile with your skills, experience, and availability builds client trust and increases your chances of being contacted.</p>
            </div>

            <div class="tip-box">
                <h4>✓ Be Professional</h4>
                <p>Respond quickly to requests, meet deadlines, and deliver quality work. This directly impacts your client reviews.</p>
            </div>

            <div class="tip-box">
                <h4>✓ Communicate Clearly</h4>
                <p>Maintain clear communication with clients throughout projects. Clients appreciate transparency and availability.</p>
            </div>

            <div class="tip-box">
                <h4>✓ Update Your Availability</h4>
                <p>Clearly indicate your availability and response times. Clients prefer reliable and predictable freelancers.</p>
            </div>

            <div class="tip-box">
                <h4>✓ Enhance Your Skills</h4>
                <p>The more varied and well-documented skills you have, the more projects and potential clients you'll attract.</p>
            </div>
        </div>

        <!-- CTA BANNER -->
        <div class="cta-banner">
            <h3>Ready to Boost Your Profile?</h3>
            <p>Update your profile and skills to attract more clients and improve your rating.</p>
            <a href="?action=profile">Go to My Profile →</a>
        </div>
    </main>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2026 SkillBridge. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
