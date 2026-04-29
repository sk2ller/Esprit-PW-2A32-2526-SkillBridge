<?php
require_once __DIR__ . '/../../Controllers/UserController.php';
$role = $_SESSION['user_role'] ?? null;
$isLoggedIn = isset($_SESSION['user_id']);
$heroVideoSrc = '/Views/assets/img/hero-showcase.mp4';
$heroVideoPoster = '/Views/assets/img/logo1.png';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - SkillBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/Views/assets/css/skillbridge-front.css">
</head>
<body class="skillbridge-front">
    <?php include __DIR__ . '/partials/front_navbar.php'; ?>

    <div class="front-page">
        <section class="hero-surface">
            <div class="hero-video-wrap">
                <video autoplay muted loop playsinline poster="<?= htmlspecialchars($heroVideoPoster) ?>">
                    <source src="<?= htmlspecialchars($heroVideoSrc) ?>" type="video/mp4">
                </video>
            </div>

            <div class="hero-content">
                <div class="hero-kicker">
                    <i class="fas fa-bolt"></i>
                    <span>Trusted talent, ready to build</span>
                </div>
                <h1 class="hero-title">Connect With <span>Top Talent</span></h1>
                <p class="hero-desc">SkillBridge relie les clients et les freelancers dans une interface moderne, claire et inspiree du style premium de votre autre projet.</p>
                <div class="hero-actions">
                    <?php if ($isLoggedIn): ?>
                        <a href="?action=profile" class="sb-btn-outline"><i class="fas fa-user"></i> Mon profil</a>
                    <?php else: ?>
                        <a href="?action=login" class="sb-btn"><i class="fas fa-right-to-bracket"></i> Login</a>
                        <a href="?action=register" class="sb-btn-outline"><i class="fas fa-user-plus"></i> Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="section-surface" id="categories">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Pourquoi <span>SkillBridge</span></h2>
                    <p class="section-subtitle">Une experience plus elegante et plus lisible pour presenter les talents, les services et l espace utilisateur.</p>
                </div>
            </div>

            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-magnifying-glass"></i></div>
                    <h3>Recherche plus claire</h3>
                    <p>Trouvez rapidement les bons profils et les bons services avec un affichage plus propre et plus moderne.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-shield-heart"></i></div>
                    <h3>Confiance & securite</h3>
                    <p>Les informations importantes sont mieux mises en avant pour rassurer les utilisateurs.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-gauge-high"></i></div>
                    <h3>Navigation plus fluide</h3>
                    <p>Le style reprend un dashboard moderne avec cartes, surfaces et contrastes plus forts.</p>
                </div>
            </div>
        </section>

        <section class="section-surface">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Votre <span>Espace</span></h2>
                    <p class="section-subtitle">Les acces rapides changent selon votre role, tout en conservant la logique actuelle du projet.</p>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="dashboard-icon"><i class="fas fa-user"></i></div>
                    <h3>Mon profil</h3>
                    <p>Consultez et mettez a jour vos informations personnelles.</p>
                    <a href="?action=profile" class="sb-btn-soft">Voir profil</a>
                </div>

                <?php if ($role == 2): ?>
                <div class="dashboard-card">
                    <div class="dashboard-icon"><i class="fas fa-user-group"></i></div>
                    <h3>Freelancers</h3>
                    <p>Parcourez les talents disponibles et choisissez le profil adapte a votre besoin.</p>
                    <a href="?action=freelancers" class="sb-btn-soft">Explorer</a>
                </div>
                <?php elseif ($role == 3): ?>
                <div class="dashboard-card">
                    <div class="dashboard-icon"><i class="fas fa-briefcase"></i></div>
                    <h3>Dashboard Freelancer</h3>
                    <p>Accedez a votre sidebar freelancer pour gerer votre CRUD services de maniere complete.</p>
                    <a href="?action=my_services" class="sb-btn-soft">Ouvrir mes services</a>
                </div>
                <?php endif; ?>

                <?php if ($role == 1): ?>
                <div class="dashboard-card">
                    <div class="dashboard-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Dashboard Admin</h3>
                    <p>Accedez a la zone d administration avec un habillage proche de project - Copy.</p>
                    <a href="?action=statistics" class="sb-btn-soft">Ouvrir</a>
                </div>
                <?php endif; ?>

                <?php if (!$isLoggedIn): ?>
                <div class="dashboard-card">
                    <div class="dashboard-icon"><i class="fas fa-rocket"></i></div>
                    <h3>Creer un compte</h3>
                    <p>Inscrivez-vous pour acceder a votre espace utilisateur, votre profil et aux fonctionnalites de la plateforme.</p>
                    <a href="?action=register" class="sb-btn-soft">S inscrire</a>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>
</html>
