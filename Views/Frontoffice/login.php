<?php
require_once __DIR__ . '/../../Controllers/UserController.php';
require_once __DIR__ . '/../../Controllers/JWT.php';

$userController = new UserController();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        $user = $userController->authenticate($email, $password);
        
        if ($user) {
            // Check if freelancer is approved
            if ($user->getIdRole() == 3 && !$user->getIsApproved()) {
                $error = 'Your account is pending approval by an administrator.';
            } else {
                // Generate JWT token
                $jwt = JWT::generateToken($user->getIdUser(), $user->getEmail(), $user->getIdRole());
                
                // Set session
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user->getIdUser();
                $_SESSION['user_nom'] = $user->getNom();
                $_SESSION['user_prenom'] = $user->getPrenom();
                $_SESSION['user_email'] = $user->getEmail();
                $_SESSION['user_role'] = $user->getIdRole();
                $_SESSION['jwt'] = $jwt;
                
                // Set HTTP-only cookie
                setcookie('jwt', $jwt, time() + 86400, '/', '', false, true);
                
                header('Location: ?action=home');
                exit;
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - SkillBridge</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

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

        .auth-container {
            width: 100%;
            max-width: 420px;
            margin: auto;
            padding: 2rem 1rem;
        }

        .auth-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 3.5rem 2.5rem;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .auth-header img {
            height: 72px;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #111827 0%, #374151 55%, #4b5563 100%);
            border-radius: 22px;
            padding: 0.95rem 1.45rem;
            box-shadow: 0 20px 42px rgba(17, 24, 39, 0.24);
            border: 1px solid rgba(224, 112, 32, 0.24);
        }

        .auth-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
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
        }

        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(224, 112, 32, 0.1);
            outline: none;
        }

        .form-group input::placeholder {
            color: var(--text-light);
        }

        .btn-login {
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
            margin-top: 1.5rem;
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(224, 112, 32, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            padding: 1rem;
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

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }

        .auth-footer p {
            color: var(--text-light);
            margin: 0;
            font-size: 0.9rem;
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .auth-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .auth-card {
                padding: 2.5rem 1.5rem;
            }

            .auth-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
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
                        <a href="?action=home" class="nav-link">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=register" class="btn-nav-primary ms-2">
                            <i class="fas fa-user-plus me-1"></i>Sign Up
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="/Views/assets/img/logo1.png" alt="SkillBridge">
                <h1>Welcome Back</h1>
                <p>Sign in to your SkillBridge account</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle mt-1"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="text" 
                        id="email" 
                        name="email" 
                        placeholder="you@example.com" 
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••"
                    >
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="?action=register">Sign up here</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
