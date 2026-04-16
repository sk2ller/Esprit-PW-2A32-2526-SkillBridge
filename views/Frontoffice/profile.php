<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ?action=login');
    exit;
}
require_once __DIR__ . '/../../Controllers/UserController.php';

$userController = new UserController();
$user    = $userController->getUserById($_SESSION['user_id']);
$error   = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $nom    = trim($_POST['nom']    ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email  = trim($_POST['email']  ?? '');

        if (!$nom || !$prenom || !$email) {
            $error = "Tous les champs sont obligatoires.";
        } elseif ($userController->emailExists($email, $user->getIdUser())) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $userController->updateUser($user);
            $success = "Profil mis à jour avec succès.";
            $user = $userController->getUserById($_SESSION['user_id']);
        }

    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user->getMotDePasse())) {
            $error = "Mot de passe actuel incorrect.";
        } elseif ($new !== $confirm) {
            $error = "Les nouveaux mots de passe ne correspondent pas.";
        } elseif (strlen($new) < 8) {
            $error = "Minimum 8 caractères.";
        } else {
            $userController->updatePassword($user->getIdUser(), $new);
            $success = "Mot de passe modifié avec succès.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil — SkillBridge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/Views/assets/css/skillbridge.css">
    <style>
        .profile-layout {
            background: var(--creme);
            padding: 3rem 1.5rem 5rem;
            min-height: calc(100vh - 68px - 65px);
        }
        .profile-header {
            max-width: 680px;
            margin: 0 auto 2.5rem;
            padding-bottom: 1.75rem;
            border-bottom: 1px solid var(--beige-border);
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .profile-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--charcoal);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--amber);
            flex-shrink: 0;
            border: 2px solid var(--beige-border);
        }
        .profile-header-text h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--charcoal);
            letter-spacing: -0.02em;
            margin-bottom: 0.2rem;
        }
        .profile-header-text p {
            color: var(--text-light);
            font-size: 0.88rem;
        }
        .profile-section {
            max-width: 680px;
            margin: 0 auto;
        }
        .profile-card {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--beige-border);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .profile-card-header {
            padding: 1.25rem 2rem;
            border-bottom: 1px solid var(--beige-border);
            background: var(--creme);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .profile-card-header .indicator {
            width: 3px;
            height: 18px;
            background: var(--amber);
            border-radius: 2px;
            flex-shrink: 0;
        }
        .profile-card-header h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
            font-weight: 600;
            color: var(--charcoal);
            letter-spacing: -0.01em;
        }
        .profile-card-body {
            padding: 2rem;
        }
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .input-readonly {
            background: var(--creme) !important;
            color: var(--text-light) !important;
            cursor: not-allowed;
            border-color: var(--beige-border) !important;
        }
        .btn-full {
            width: 100%;
            padding: 0.85rem;
            font-size: 0.92rem;
            margin-top: 0.5rem;
        }
        .profile-card .btn-secondary {
            background: var(--creme);
            color: var(--charcoal-soft);
            border: 1px solid var(--beige-border);
        }
        .profile-card .btn-secondary:hover {
            background: var(--charcoal);
            color: var(--white);
            border-color: var(--charcoal);
            transform: translateY(-1px);
        }
        @media (max-width: 520px) {
            .two-col { grid-template-columns: 1fr; }
            .profile-card-body { padding: 1.5rem; }
            .profile-header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<nav class="navbar-top">
    <div class="container">
        <a href="?action=home" class="logo"><img src="/Views/assets/img/logo1.png" alt="SkillBridge" style="height: 50px; width: auto;"></a>
        <div class="nav-buttons">
            <span><?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></span>
            <a href="?action=logout" class="btn btn-logout">Déconnexion</a>
        </div>
    </div>
</nav>

<main>
    <div class="profile-layout">

        <?php if (!empty($error)): ?>
            <div style="max-width: 680px; margin: 0 auto 1.25rem;">
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div style="max-width: 680px; margin: 0 auto 1.25rem;">
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            </div>
        <?php endif; ?>

        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(mb_substr($user->getPrenom(), 0, 1) . mb_substr($user->getNom(), 0, 1)) ?>
            </div>
            <div class="profile-header-text">
                <h1><?= htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()) ?></h1>
                <p><?= htmlspecialchars($user->getEmail()) ?></p>
            </div>
        </div>

        <div class="profile-section">

            <!-- Update profile -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <div class="indicator"></div>
                    <h3>Modifier mes informations</h3>
                </div>
                <div class="profile-card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="two-col">
                            <div class="form-group">
                                <label>Nom</label>
                                <input type="text" name="nom"
                                       value="<?= htmlspecialchars($user->getNom()) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Prénom</label>
                                <input type="text" name="prenom"
                                       value="<?= htmlspecialchars($user->getPrenom()) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email"
                                   value="<?= htmlspecialchars($user->getEmail()) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Niveau</label>
                            <input type="text" value="<?= htmlspecialchars($user->getNiveau()) ?>"
                                   readonly class="input-readonly">
                        </div>

                        <button type="submit" class="btn btn-primary btn-full">
                            Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change password -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <div class="indicator"></div>
                    <h3>Sécurité — Changer le mot de passe</h3>
                </div>
                <div class="profile-card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-group">
                            <label>Mot de Passe Actuel</label>
                            <input type="password" name="current_password" placeholder="••••••••" required>
                        </div>

                        <div class="two-col">
                            <div class="form-group">
                                <label>Nouveau Mot de Passe</label>
                                <input type="password" name="new_password" placeholder="Min. 8 caractères" required>
                            </div>
                            <div class="form-group">
                                <label>Confirmer</label>
                                <input type="password" name="confirm_password" placeholder="••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-secondary btn-full">
                            Mettre à jour le mot de passe
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

<footer class="footer">
    <p>&copy; 2026 SkillBridge. Tous droits réservés.</p>
</footer>

</body>
</html>