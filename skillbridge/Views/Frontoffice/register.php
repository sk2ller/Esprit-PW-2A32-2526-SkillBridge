<?php
require_once __DIR__ . '/../../Controllers/UserController.php';

$userController = new UserController();
$error   = "";
$success = "";

function sanitizeInput(array $data): array {
    $filtered = filter_var_array($data, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    return $filtered ? array_map('trim', $filtered) : [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyer les données d'entrée
    $data = sanitizeInput($_POST);

    $nom     = $data['nom']     ?? '';
    $prenom  = $data['prenom']  ?? '';
    $email   = $data['email']   ?? '';
    $pass    = $_POST['password']  ?? ''; // Ne pas nettoyer le mot de passe
    $confirm = $_POST['confirm']   ?? '';
    $role    = (int)($data['id_role'] ?? 2);

    // Validation des données
    if (!$nom || !$prenom || !$email || !$pass || !$confirm) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } elseif ($pass !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($pass) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } else {
        // Vérifier si l'email existe déjà
        if ($userController->emailExists($email)) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $user = new User($nom, $prenom, $email, $pass, 'débutant', $role);
            $userController->addUser($user);
            $success = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S'inscrire — SkillBridge</title>
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
            left: -150px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(224,112,32,0.05) 0%, transparent 65%);
            pointer-events: none;
        }
        .auth-box {
            width: 100%;
            max-width: 560px;
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
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .auth-card .btn-primary {
            width: 100%;
            padding: 0.9rem;
            font-size: 0.95rem;
            margin-top: 0.75rem;
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
        .success-box {
            background: var(--white);
            border-radius: 14px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 24px 64px rgba(0,0,0,0.3);
        }
        .success-box .checkmark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(39, 174, 96, 0.1);
            border: 1px solid rgba(39, 174, 96, 0.3);
            color: #27ae60;
            font-size: 1.4rem;
            margin-bottom: 1.25rem;
        }
        .success-box h2 {
            font-family: 'Playfair Display', serif;
            color: var(--charcoal);
            margin-bottom: 0.5rem;
        }
        .success-box p {
            color: var(--text-mid);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        @media (max-width: 520px) {
            .two-col { grid-template-columns: 1fr; }
            .auth-card { padding: 1.75rem; }
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

            <?php if (!empty($success)): ?>
                <div class="success-box">
                    <div class="checkmark">&#10003;</div>
                    <h2>Compte créé</h2>
                    <p><?= htmlspecialchars($success) ?></p>
                    <a href="?action=login" class="btn btn-primary" style="padding: 0.8rem 2rem;">Se Connecter</a>
                </div>
            <?php else: ?>

                <div class="auth-header">
                    <h1>Rejoignez SkillBridge</h1>
                    <div class="auth-divider"></div>
                    <p>Créez votre compte en quelques minutes.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" style="margin-bottom: 1.25rem;"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="auth-card">
                    <form method="POST" action="?action=register">

                        <div class="two-col">
                            <div class="form-group">
                                <label>Nom</label>
                                <input type="text" name="nom" placeholder="Dupont"
                                       value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Prénom</label>
                                <input type="text" name="prenom" placeholder="Jean"
                                       value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Adresse Email</label>
                            <input type="email" name="email" placeholder="jean@exemple.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>

                        <div class="two-col">
                            <div class="form-group">
                                <label>Mot de Passe</label>
                                <input type="password" name="password" placeholder="Min. 8 caractères" required>
                            </div>
                            <div class="form-group">
                                <label>Confirmer</label>
                                <input type="password" name="confirm" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Profil</label>
                            <select name="id_role" required>
                                <option value="">-- Sélectionnez --</option>
                                <option value="2" <?= (($_POST['id_role'] ?? 2) == 2) ? 'selected' : '' ?>>Chercheur d'Emploi</option>
                                <option value="3" <?= (($_POST['id_role'] ?? '') == 3) ? 'selected' : '' ?>>Prestataire / Freelance</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Créer mon Compte
                        </button>

                    </form>
                </div>

                <div class="auth-footer-note">
                    Vous avez déjà un compte ?
                    <a href="?action=login">Se connecter</a>
                </div>

            <?php endif; ?>
        </div>
    </div>
</main>

<footer class="footer">
    <p>&copy; 2026 SkillBridge. Tous droits réservés.</p>
</footer>

</body>
</html>