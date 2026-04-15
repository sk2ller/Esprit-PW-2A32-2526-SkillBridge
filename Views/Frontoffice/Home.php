<?php
require_once __DIR__ . '/../../Controllers/UserController.php';
$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - SkillBridge</title>
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
            color: var(--text);
            background: white;
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

        .navbar-custom .nav-link.active {
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

        /* HERO SECTION */
        .hero {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: white;
            padding: 7rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -5%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(224, 112, 32, 0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -5%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(224, 112, 32, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 650px;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.2rem;
            color: rgba(255,255,255,0.9);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-hero {
            padding: 0.75rem 2rem;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-hero-primary {
            background: var(--primary);
            color: white;
        }

        .btn-hero-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(224, 112, 32, 0.3);
            color: white;
        }

        .btn-hero-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-hero-outline:hover {
            background: white;
            color: var(--primary);
        }

        /* SECTION STYLING */
        .section-default {
            padding: 6rem 2rem;
        }

        .section-alt {
            background: var(--bg-light);
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 1.5rem;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: var(--text-light);
            margin-bottom: 3rem;
            max-width: 600px;
        }

        /* FEATURE CARDS */
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: white;
            padding: 2.5rem;
            border-radius: 10px;
            border: 1px solid var(--border);
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .feature-card:hover {
            border-color: var(--primary);
            box-shadow: 0 12px 30px rgba(224, 112, 32, 0.15);
            transform: translateY(-8px);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* DASHBOARD CARDS */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .dashboard-card {
            background: white;
            padding: 2.5rem;
            border-radius: 10px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: all 0.3s;
        }

        .dashboard-card:hover {
            border-color: var(--primary);
            box-shadow: 0 12px 30px rgba(224, 112, 32, 0.1);
        }

        .dashboard-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .dashboard-card h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.75rem;
        }

        .dashboard-card p {
            color: var(--text-light);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .btn-secondary {
            display: inline-block;
            padding: 0.6rem 1.5rem;
            background: var(--bg-light);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
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
            .hero {
                padding: 4rem 1.5rem;
            }

            .hero h1 {
                font-size: 2.2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-hero {
                width: 100%;
                text-align: center;
            }

            .section-default {
                padding: 4rem 1.5rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .feature-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <span class="nav-link">
                                <i class="fas fa-user-circle me-2"></i><?= htmlspecialchars($_SESSION['user_prenom']) ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a href="?action=profile" class="nav-link">
                                <i class="fas fa-sliders me-1"></i>Profile
                            </a>
                        </li>
                        <?php if ($role == 1): ?>
                        <li class="nav-item">
                            <a href="?action=userlist" class="nav-link">
                                <i class="fas fa-cog me-1"></i>Admin
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a href="?action=logout" class="btn-nav-primary ms-2">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="?action=login" class="nav-link">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="?action=register" class="btn-nav-primary ms-2">
                                <i class="fas fa-user-plus me-1"></i>Sign Up
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Connect With Top Talent</h1>
                <p>Discover skilled freelancers or offer your expertise. SkillBridge connects you with the perfect match for every project.</p>
                <div class="hero-buttons">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="?action=login" class="btn-hero btn-hero-primary">Get Started</a>
                        <a href="?action=register" class="btn-hero btn-hero-outline">Learn More</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="section-default">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Why Choose SkillBridge?</h2>
                <p class="section-subtitle">We make it easy to find the right talent or showcase your skills to the world.</p>
            </div>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Easy Search</h3>
                    <p>Find professionals with the exact skills you need using our advanced filtering system.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>Secure & Safe</h3>
                    <p>Your data is protected with industry-leading security measures and verified profiles.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-zap"></i>
                    </div>
                    <h3>Fast & Reliable</h3>
                    <p>Connect instantly and start collaborating on projects with minimal setup time.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- DASHBOARD PREVIEW SECTION -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <section class="section-default section-alt">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Your Dashboard</h2>
                <p class="section-subtitle">Manage your profile and explore opportunities all in one place.</p>
            </div>
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="dashboard-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>My Profile</h3>
                    <p>View and manage your profile information, skills, and availability.</p>
                    <a href="?action=profile" class="btn-secondary">View Profile</a>
                </div>
                
                <?php if ($role == 2): ?>
                <!-- FEATURES FOR CLIENTS -->
                <div class="dashboard-card">
                    <div class="dashboard-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Browse Freelancers</h3>
                    <p>Find and review talented freelancers for your projects. Compare ratings and skills.</p>
                    <a href="?action=freelancers" class="btn-secondary">Explore Freelancers</a>
                </div>
                <?php elseif ($role == 3): ?>
                <!-- FEATURES FOR FREELANCERS -->
                <div class="dashboard-card">
                    <div class="dashboard-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>My Rating</h3>
                    <p>Track your professional reputation, client feedback, and performance metrics.</p>
                    <a href="?action=myrating" class="btn-secondary">View Rating</a>
                </div>
                <?php endif; ?>
                
                <?php if ($role == 1): ?>
                <!-- FEATURES FOR ADMIN -->
                <div class="dashboard-card">
                    <div class="dashboard-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3>User Management</h3>
                    <p>Manage users, approve freelancers, and handle account administration.</p>
                    <a href="?action=userlist" class="btn-secondary">Go to Admin</a>
                </div>
                <?php endif; ?>
                
                <div class="dashboard-card">
                    <div class="dashboard-icon">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <h3>Logout</h3>
                    <p>Safely exit your account when you're done.</p>
                    <a href="?action=logout" class="btn-secondary">Sign Out</a>
                </div>
            </div>
        </div>
    </section>
    <?php else: ?>
    <section class="section-default section-alt">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Get Started Today</h2>
                <p class="section-subtitle">Join thousands of professionals and clients using SkillBridge.</p>
            </div>
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="dashboard-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3>For Clients</h3>
                    <p>Find amazing freelancers to bring your projects to life.</p>
                    <a href="?action=register" class="btn-secondary">Browse Freelancers</a>
                </div>
                <div class="dashboard-card">
                    <div class="dashboard-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>For Freelancers</h3>
                    <p>Showcase your skills and grow your client base.</p>
                    <a href="?action=register" class="btn-secondary">Get Started</a>
                </div>
                <div class="dashboard-card">
                    <div class="dashboard-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Support</h3>
                    <p>We're here to help you succeed every step of the way.</p>
                    <a href="#" class="btn-secondary">Contact Us</a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2026 SkillBridge. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
