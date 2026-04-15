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
            <h1>Your Rating & Feedback</h1>
            <p>Track your professional reputation and client satisfaction</p>
        </div>
    </div>

    <main class="container" style="padding: 0 2rem; margin-bottom: 2rem;">
        <!-- MAIN RATING & STATS -->
        <div class="rating-grid">
            <!-- Overall Rating -->
            <div class="rating-card">
                <p class="rating-label">Your Current Rating</p>
                <div class="rating-stars">
                    <?php
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                    
                    for ($i = 0; $i < 5; $i++) {
                        if ($i < $fullStars) {
                            echo '★';
                        } elseif ($i == $fullStars && $hasHalfStar) {
                            echo '½';
                        } else {
                            echo '☆';
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
                <p class="rating-label">Feedback Statistics</p>
                
                <div style="margin: 1.5rem 0;">
                    <div style="display: flex; justify-content: space-around; margin-bottom: 2rem;">
                        <div>
                            <div style="font-size: 2.5rem; font-weight: 700; color: var(--success);"><?= $likes ?></div>
                            <div style="color: var(--text-light); font-size: 0.9rem;">Positive</div>
                        </div>
                        <div>
                            <div style="font-size: 2.5rem; font-weight: 700; color: var(--warning);"><?= $dislikes ?></div>
                            <div style="color: var(--text-light); font-size: 0.9rem;">Negative</div>
                        </div>
                    </div>
                </div>

                <div>
                    <div style="color: var(--text-light); font-size: 0.85rem; margin-bottom: 0.5rem;">Satisfaction Rate</div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill" style="width: <?= $likePercentage ?>%"></div>
                    </div>
                    <div style="text-align: center; color: var(--primary); font-weight: 700; margin-top: 0.5rem;">
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
