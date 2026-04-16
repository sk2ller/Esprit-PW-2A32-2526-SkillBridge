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
                        <a href="?action=projectdashboard" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Dashboard Projets</a>
                        <a href="?action=userlist" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Admin</a>
                    <?php endif; ?>
                    <a href="?action=projects" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Projets</a>
                    <a href="?action=logout" class="btn btn-logout">Déconnexion</a>
                <?php else: ?>
                    <a href="?action=projects" class="btn btn-secondary">Projets</a>
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
                <h1>Bienvenue sur SkillBridge</h1>
                <p>Connectez-vous avec vos compétences, bâtissez votre avenir</p>

                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="hero-buttons">
                        <a href="?action=login" class="btn btn-primary">
                            Se Connecter
                        </a>
                        <a href="?action=register" class="btn btn-secondary">
                            Créer un Compte
                        </a>
                    </div>
                <?php else: ?>
                    <div class="container" style="margin-top: 2.5rem; max-width: 640px;">
                        <div class="card">
                            <h2>Bonjour, <?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></h2>
                            <p>Vous êtes connecté avec succès. Explorez votre tableau de bord personnel.</p>
                            <div class="hero-buttons" style="justify-content: flex-start;">
                                <a href="?action=profile" class="btn btn-primary">Mon Profil</a>
                                <a href="?action=projects" class="btn btn-secondary">Voir les projets</a>
                                <?php if ($_SESSION['user_role'] == 1): ?>
                                    <a href="?action=projectdashboard" class="btn btn-secondary">Dashboard Projets</a>
                                    <a href="?action=userlist" class="btn btn-secondary">Gestion des Utilisateurs</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Features Section (only for non-logged-in users) -->
        <?php if (!isset($_SESSION['user_id'])): ?>
        <section class="features-section">
            <div class="container">
                <h2>Pourquoi Rejoindre SkillBridge ?</h2>
                <p class="section-subtitle">Une plateforme pensée pour les freelances ambitieux</p>
                <div class="section-divider"></div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
                    <div class="feature-card">
                        <h3>Parcourez Vos Compétences</h3>
                        <p>Créez un profil complet et présentez vos compétences de manière professionnelle.</p>
                    </div>
                    <div class="feature-card">
                        <h3>Connectez-vous</h3>
                        <p>Entrez en contact avec d'autres professionnels et développez votre réseau.</p>
                    </div>
                    <div class="feature-card">
                        <h3>Évoluez</h3>
                        <p>Accédez à des formations et améliorez continuellement vos compétences.</p>
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