<?php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil — SkillBridge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/Views/assets/css/skillbridge.css">
    <link rel="stylesheet" href="/Views/assets/css/enhanced-styles.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar-top">
        <div class="container">
            <a href="/index.php" class="logo">
                <img src="/Views/assets/img/logo1.png" alt="SkillBridge" style="height: 50px; width: auto;">
            </a>
            <div class="nav-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span style="margin-right: 0.5rem;">
                        Bienvenue, <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong>
                    </span>
                    <a href="?action=profile" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Mon Profil</a>
                    <?php if ($_SESSION['user_role'] == 1): ?>
                        <a href="?action=brainstorming_admin" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Admin</a>
                    <?php endif; ?>
                    <a href="?action=logout" class="btn btn-logout">Déconnexion</a>
                <?php else: ?>
                    <a href="?action=login" class="btn btn-secondary">Connexion</a>
                    <a href="?action=register" class="btn btn-primary">S'inscrire</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Développez Vos Compétences, Construisez Votre Avenir</h1>
                    <p class="hero-subtitle">Rejoignez une communauté de professionnels passionnés et accédez à des opportunités uniques pour faire évoluer votre carrière.</p>

                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div class="hero-buttons">
                            <a href="?action=register" class="btn btn-primary btn-lg">
                                <i class="fas fa-rocket"></i> Commencer Maintenant
                            </a>
                            <a href="?action=login" class="btn btn-secondary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Se Connecter
                            </a>
                        </div>
                        <div class="hero-stats">
                            <div class="stat-item">
                                <span class="stat-number">500+</span>
                                <span class="stat-label">Professionnels</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">50+</span>
                                <span class="stat-label">Projets Réalisés</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">4.8/5</span>
                                <span class="stat-label">Satisfaction</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="welcome-card">
                            <div class="welcome-header">
                                <div class="welcome-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div>
                                    <h2>Bonjour, <?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?> !</h2>
                                    <p class="welcome-role">
                                        <?php
                                        switch($_SESSION['user_role']) {
                                            case 1: echo 'Administrateur'; break;
                                            case 2: echo 'Utilisateur'; break;
                                            case 3: echo 'Freelancer'; break;
                                            default: echo 'Membre';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                            <p class="welcome-message">Prêt à explorer de nouvelles opportunités et à développer vos compétences ?</p>
                            <div class="dashboard-actions">
                                <a href="?action=profile" class="btn btn-primary">
                                    <i class="fas fa-user"></i> Mon Profil
                                </a>
                                <a href="?action=brainstorming_add" class="btn btn-success">
                                    <i class="fas fa-lightbulb"></i> Nouveau Brainstorming
                                </a>
                                <a href="?action=brainstorming_list" class="btn btn-info">
                                    <i class="fas fa-list"></i> Voir les Idées
                                </a>
                                <?php if ($_SESSION['user_role'] == 1): ?>
                                    <a href="?action=brainstorming_admin" class="btn btn-secondary">
                                        <i class="fas fa-cog"></i> Admin Dashboard
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="hero-visual">
                    <div class="hero-illustration">
                        <i class="fas fa-users"></i>
                        <div class="floating-elements">
                            <div class="floating-card card-1">
                                <i class="fas fa-code"></i>
                            </div>
                            <div class="floating-card card-2">
                                <i class="fas fa-palette"></i>
                            </div>
                            <div class="floating-card card-3">
                                <i class="fas fa-rocket"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section (only for non-logged-in users) -->
        <?php if (!isset($_SESSION['user_id'])): ?>
        <section class="features-section">
            <div class="container">
                <div class="section-header">
                    <h2>Pourquoi Choisir SkillBridge ?</h2>
                    <p class="section-subtitle">Une plateforme pensée pour les freelances ambitieux et les entreprises innovantes</p>
                </div>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <h3>Développement Compétences</h3>
                        <p>Accédez à des formations personnalisées et développez vos compétences techniques dans un environnement collaboratif.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3>Réseau Professionnel</h3>
                        <p>Connectez-vous avec d'autres professionnels, partagez vos idées et collaborez sur des projets innovants.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3>Brainstorming Collaboratif</h3>
                        <p>Participez à des sessions de brainstorming pour générer des idées créatives et trouver des solutions innovantes.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3>Reconnaissance</h3>
                        <p>Obtenez de la visibilité pour vos contributions et gagnez en crédibilité auprès de la communauté.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials-section">
            <div class="container">
                <div class="section-header">
                    <h2>Ce Que Disent Nos Membres</h2>
                    <p class="section-subtitle">Découvrez les expériences de notre communauté</p>
                </div>
                <div class="testimonials-grid">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"SkillBridge m'a permis de développer mes compétences et de trouver des opportunités incroyables. La communauté est très supportive !"</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h4>Marie Dubois</h4>
                                <span>Développeuse Web</span>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Les sessions de brainstorming sont une vraie source d'inspiration. J'ai pu concrétiser plusieurs idées grâce à la plateforme."</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h4>Jean Martin</h4>
                                <span>Designer UX/UI</span>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Une plateforme professionnelle qui facilite vraiment les échanges entre freelances et entreprises."</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h4>Sophie Leroy</h4>
                                <span>Chef de Projet</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Prêt à Rejoindre Notre Communauté ?</h2>
                    <p>Commencez votre voyage vers l'excellence professionnelle dès aujourd'hui.</p>
                    <a href="?action=register" class="btn btn-primary btn-xl">
                        <i class="fas fa-rocket"></i> S'inscrire Gratuitement
                    </a>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 SkillBridge. Tous droits réservés.</p>
    </footer>
</body>
</html>