<?php
require_once __DIR__ . '/../../Controllers/UserController.php';

$userController = new UserController();
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    $user = $userController->authenticate($email, $password);
    if ($user) {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user->getIdUser();
        $_SESSION['user_nom']   = $user->getNom();
        $_SESSION['user_prenom']= $user->getPrenom();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_role']  = $user->getIdRole();

        if ($user->getIdRole() == 1) {
            header('Location: ?action=userlist');
        } else {
            header('Location: ?action=profile');
        }
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — SkillBridge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/Views/assets/css/skillbridge.css">
    <style>
        .auth-layout {
            min-height: calc(100vh - 68px - 65px);
            display: flex;
            align-items: center;
            background: var(--charcoal);
            position: relative;
            padding: 3rem 1.5rem;
            overflow: hidden;
        }
        .auth-layout::before {
            content: '';
            position: absolute;
            top: -200px;
            right: -100px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(224,112,32,0.08) 0%, transparent 65%);
            pointer-events: none;
        }
        .auth-layout::after {
            content: '';
            position: absolute;
            bottom: -150px;
            left: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(224,112,32,0.05) 0%, transparent 65%);
            pointer-events: none;
        }
        .auth-box {
            width: 100%;
            max-width: 460px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--white);
            letter-spacing: -0.03em;
            margin-bottom: 0.5rem;
        }
        .auth-header p {
            color: rgba(255,255,255,0.4);
            font-size: 0.95rem;
            font-weight: 300;
        }
        .auth-card {
            background: var(--white);
            border-radius: 14px;
            padding: 2.5rem;
            box-shadow: 0 24px 64px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .auth-card .btn-primary {
            width: 100%;
            padding: 0.9rem;
            font-size: 0.95rem;
            margin-top: 0.5rem;
            letter-spacing: 0.04em;
        }
        .auth-footer-note {
            text-align: center;
            margin-top: 1.5rem;
            color: rgba(255,255,255,0.35);
            font-size: 0.88rem;
        }
        .auth-footer-note a {
            color: var(--amber-light);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }
        .auth-footer-note a:hover {
            color: var(--white);
        }
        .auth-divider {
            width: 32px;
            height: 2px;
            background: var(--amber);
            margin: 0 auto 1.5rem;
        }
    </style>
</head>
<body>

<nav class="navbar-top">
    <div class="container">
        <a href="?action=home" class="logo"><img src="/Views/assets/img/logo1.png" alt="SkillBridge" style="height: 50px; width: auto;"></a>
        <div class="nav-buttons">
            <a href="?action=login" class="btn btn-secondary">Connexion</a>
            <a href="?action=register" class="btn btn-primary">S'inscrire</a>
        </div>
    </div>
</nav>

<main>
    <div class="auth-layout">
        <div class="auth-box">
            <div class="auth-header">
                <h1>Connexion</h1>
                <div class="auth-divider"></div>
                <p>Bienvenue — connectez-vous à votre compte SkillBridge.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="margin-bottom: 1.25rem;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="auth-card">
                <form method="POST" action="?action=login">
                    <div class="form-group">
                        <label>Adresse Email</label>
                        <input type="email" name="email" placeholder="exemple@mail.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.75rem;">
                        <label>Mot de Passe</label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        Se Connecter
                    </button>
                </form>
            </div>

            <div class="auth-footer-note">
                Pas encore de compte ?
                <a href="?action=register">S'inscrire</a>
            </div>
        </div>
    </div>
</main>

<footer class="footer">
    <p>&copy; 2026 SkillBridge. Tous droits réservés.</p>
</footer>

</body>
</html>