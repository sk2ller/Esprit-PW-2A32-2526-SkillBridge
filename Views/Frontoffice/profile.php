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

function isValidProfileName($value)
{
    return (bool) preg_match("/^[a-zA-ZÀ-ÿ][a-zA-ZÀ-ÿ' -]{1,49}$/u", $value);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $nom    = trim($_POST['nom']    ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email  = trim($_POST['email']  ?? '');

        if (!$nom || !$prenom || !$email) {
            $error = "All fields are required.";
        } elseif (!isValidProfileName($nom)) {
            $error = "Last name must contain only letters, spaces, apostrophes, or hyphens.";
        } elseif (!isValidProfileName($prenom)) {
            $error = "First name must contain only letters, spaces, apostrophes, or hyphens.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (strlen($email) > 100) {
            $error = "Email address is too long.";
        } elseif ($userController->emailExists($email, $user->getIdUser())) {
            $error = "This email is already in use.";
        } else {
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $userController->updateUser($user);
            $success = "Profile updated successfully.";
            $user = $userController->getUserById($_SESSION['user_id']);
        }

    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user->getMotDePasse())) {
            $error = "Current password is incorrect.";
        } elseif ($new !== $confirm) {
            $error = "New passwords do not match.";
        } elseif (strlen($new) < 8) {
            $error = "Password must be at least 8 characters.";
        } elseif (strlen($new) > 72) {
            $error = "Password is too long.";
        } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,72}$/', $new)) {
            $error = "Password must contain at least one letter and one number.";
        } else {
            $userController->updatePassword($user->getIdUser(), $new);
            $success = "Password changed successfully.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - SkillBridge</title>
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

        /* PROFILE HEADER */
        .profile-info-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, #f5a962 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            font-weight: bold;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(224, 112, 32, 0.3);
        }

        .profile-details h2 {
            font-size: 1.8rem;
            color: var(--text);
            margin-bottom: 0.25rem;
        }

        .profile-details p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        /* FORM CARDS */
        .form-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        .form-card-header {
            background: var(--bg-light);
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .form-card-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text);
            margin: 0;
        }

        .form-card-header i {
            color: var(--primary);
            font-size: 1.3rem;
        }

        .form-card-body {
            padding: 2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.6rem;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: white;
            font-family: inherit;
        }

        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(224, 112, 32, 0.1);
            outline: none;
        }

        .form-group input:disabled,
        .form-group input[readonly] {
            background: var(--bg-light);
            color: var(--text-light);
            cursor: not-allowed;
            border-color: var(--border);
        }

        .btn-update {
            width: 100%;
            padding: 0.9rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }

        .btn-update:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(224, 112, 32, 0.3);
        }

        .btn-update:active {
            transform: translateY(0);
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.9rem;
        }

        .alert-danger {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }

        .alert-success {
            background: #efe;
            border: 1px solid #cfc;
            color: #333;
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

            .profile-info {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .profile-info-card,
            .form-card-body {
                padding: 1.5rem;
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
            <h1>My Profile</h1>
            <p>Manage your account settings and preferences</p>
        </div>
    </div>

    <main class="container" style="padding: 0 2rem; margin-bottom: 2rem;">
        <!-- ALERTS -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <!-- PROFILE INFO CARD -->
        <div class="profile-info-card">
            <div class="profile-info">
                <div class="profile-avatar">
                    <?= strtoupper(mb_substr($user->getPrenom(), 0, 1) . mb_substr($user->getNom(), 0, 1)) ?>
                </div>
                <div class="profile-details">
                    <h2><?= htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()) ?></h2>
                    <p><?= htmlspecialchars($user->getEmail()) ?></p>
                    <p style="font-size: 0.85rem; margin-top: 0.5rem; text-transform: capitalize;">Experience: <strong><?= htmlspecialchars($user->getNiveau()) ?></strong></p>
                </div>
            </div>
        </div>

        <!-- UPDATE PROFILE FORM -->
        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-user"></i>
                <h3>Update Profile Information</h3>
            </div>
            <div class="form-card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Last Name</label>
                            <input type="text" id="nom" name="nom" 
                                   value="<?= htmlspecialchars($user->getNom()) ?>">
                        </div>
                        <div class="form-group">
                            <label for="prenom">First Name</label>
                            <input type="text" id="prenom" name="prenom" 
                                   value="<?= htmlspecialchars($user->getPrenom()) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="text" id="email" name="email" value="<?= htmlspecialchars($user->getEmail()) ?>">
                    </div>

                    <div class="form-group">
                        <label for="niveau">Experience Level</label>
                        <input type="text" id="niveau" 
                               value="<?= htmlspecialchars($user->getNiveau()) ?>" 
                               readonly disabled>
                    </div>

                    <button type="submit" class="btn-update">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- CHANGE PASSWORD FORM -->
        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-lock"></i>
                <h3>Change Password</h3>
            </div>
            <div class="form-card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" placeholder="••••••••">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" placeholder="Min. 8 characters">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit" class="btn-update">
                        <i class="fas fa-shield-alt me-2"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2026 SkillBridge. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

