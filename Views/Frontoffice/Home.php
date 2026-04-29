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
    <link rel="stylesheet" href="<?= BASE_URL ?>/Views/assets/css/skillbridge.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar-top">
        <div class="container">
            <a href="<?= BASE_URL ?>/index.php" class="logo">
                <img src="<?= BASE_URL ?>/Views/assets/img/logo1.png" alt="SkillBridge" class="logo-img">
            </a>
            <div class="nav-buttons">
                <a href="?action=projects" class="btn btn-secondary btn-compact">Projets</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="welcome-text">
                        Bienvenue, <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong>
                    </span>
                    <a href="?action=profile" class="btn btn-secondary btn-compact">Mon Profil</a>
                    <?php if ($_SESSION['user_role'] == 1): ?>
                        <a href="?action=userlist" class="btn btn-secondary btn-compact">Admin</a>
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
        <section class="hero hero-home">
            <div class="container">
                <div class="home-hero-grid">
                    <div class="hero-copy">
                        <p class="hero-kicker">Plateforme collaborative pour talents digitaux</p>
                        <h1>Transformez vos compétences en opportunites concrètes</h1>
                        <p class="hero-lead">SkillBridge relie freelances, etudiants et porteurs de projets pour créer des equipes efficaces, apprendre ensemble et livrer plus vite.</p>

                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <div class="hero-buttons">
                                <a href="?action=register" class="btn btn-primary">
                                    Commencer gratuitement
                                </a>
                                <a href="?action=login" class="btn btn-secondary hero-outline-btn">
                                    J'ai deja un compte
                                </a>
                            </div>
                            <div class="hero-metrics">
                                <article class="metric-card">
                                    <p class="metric-value">250+</p>
                                    <p class="metric-label">Profils actifs</p>
                                </article>
                                <article class="metric-card">
                                    <p class="metric-value">90%</p>
                                    <p class="metric-label">Match de competences</p>
                                </article>
                                <article class="metric-card">
                                    <p class="metric-value">48h</p>
                                    <p class="metric-label">Delai moyen de connexion</p>
                                </article>
                            </div>
                        <?php else: ?>
                            <div class="hero-buttons hero-buttons-connected">
                                <a href="?action=profile" class="btn btn-primary">Mon Profil</a>
                                <a href="?action=projects" class="btn btn-secondary hero-outline-btn">Voir les Projets</a>
                                <?php if ($_SESSION['user_role'] == 1): ?>
                                    <a href="?action=userlist" class="btn btn-secondary hero-outline-btn">Gestion des Utilisateurs</a>
                                    <a href="?action=projectlist" class="btn btn-secondary hero-outline-btn">Gestion des Projets</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <aside class="hero-panel">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="card home-welcome-card">
                                <p class="welcome-badge">Session active</p>
                                <h2>Bonjour, <?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></h2>
                                <p>Votre espace est pret. Mettez votre profil a jour, decouvrez les opportunites et suivez vos collaborations.</p>
                            </div>
                        <?php else: ?>
                            <div class="card home-welcome-card">
                                <p class="welcome-badge">Ce que vous gagnez</p>
                                <h2>Un tableau de bord centre sur vos objectifs</h2>
                                <ul class="hero-list">
                                    <li>Publiez vos competences en quelques minutes</li>
                                    <li>Trouvez des projets aligns a votre niveau</li>
                                    <li>Construisez un reseau fiable et durable</li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </aside>
                </div>

            </div>
        </section>

        <!-- Features Section (only for non-logged-in users) -->
        <?php if (!isset($_SESSION['user_id'])): ?>
        <section class="features-section features-home">
            <div class="container">
                <h2>Pourquoi rejoindre SkillBridge ?</h2>
                <p class="section-subtitle">Une experience claire, rapide et orientee resultats</p>
                <div class="section-divider"></div>
                <div class="home-features-grid">
                    <div class="feature-card">
                        <h3>Profil dynamique</h3>
                        <p>Mettez en avant vos points forts, vos experiences et vos objectifs avec une presentation professionnelle.</p>
                    </div>
                    <div class="feature-card">
                        <h3>Connexions qualifiees</h3>
                        <p>Entrez en relation avec des profils complementaires pour former des collaborations solides.</p>
                    </div>
                    <div class="feature-card">
                        <h3>Progression continue</h3>
                        <p>Avancez avec des opportunites concretes et developpez vos competences projet apres projet.</p>
                    </div>
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