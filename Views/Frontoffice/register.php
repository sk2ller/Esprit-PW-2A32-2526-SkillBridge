<?php
require_once __DIR__ . '/../../Controllers/UserController.php';
require_once __DIR__ . '/../../Models/User.php';

$userController = new UserController();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $niveau = $_POST['niveau'] ?? 'débutant';
    $role = (int)($_POST['id_role'] ?? 2);

    // Validation
    if (!$nom || !$prenom || !$email || !$password || !$confirmPassword) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif ($userController->emailExists($email)) {
        $error = 'This email is already in use.';
    } else {
        // Create user with role-based approval
        // Clients auto-approved, Freelancers need admin approval
        $isApproved = ($role == 2) ? 1 : 0;
        
        $user = new User($nom, $prenom, $email, $password, $niveau, $role, 0, $isApproved);
        $userController->addUser($user);
        
        if ($role == 3) {
            $success = 'Sign up successful! Your profile is pending approval. We will get back to you within 24-48 hours.';
        } else {
            $success = 'Sign up successful! You can now sign in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - SkillBridge</title>
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
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .auth-container {
            width: 100%;
            max-width: 480px;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-row .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.6rem;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: white;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(224, 112, 32, 0.1);
            outline: none;
        }

        .form-group input::placeholder {
            color: var(--text-light);
        }

        /* ROLE SELECTOR */
        .role-selector {
            margin-bottom: 1.5rem;
        }

        .role-label-title {
            display: block;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .role-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .role-option {
            padding: 1.2rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
            position: relative;
        }

        .role-option input {
            display: none;
        }

        .role-option:hover {
            border-color: var(--primary);
            background: rgba(224, 112, 32, 0.05);
        }

        .role-option input:checked + .role-content {
            color: var(--primary);
        }

        .role-option.active {
            border-color: var(--primary);
            background: rgba(224, 112, 32, 0.05);
        }

        .role-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .role-name {
            display: block;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.25rem;
        }

        .role-desc {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .btn-register {
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

        .btn-register:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(224, 112, 32, 0.3);
        }

        .btn-register:active {
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

        .alert-success {
            background: #efe;
            border: 1px solid #cfc;
            color: #333;
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

        .success-message {
            text-align: center;
            margin-top: 2rem;
        }

        .success-message a {
            display: inline-block;
            padding: 0.7rem 1.5rem;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 1rem;
        }

        .success-message a:hover {
            background: var(--primary-dark);
        }

        @media (max-width: 576px) {
            .auth-card {
                padding: 2.5rem 1.5rem;
            }

            .auth-header h1 {
                font-size: 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .role-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="/Views/assets/img/logo1.png" alt="SkillBridge">
                <h1>Create Account</h1>
                <p>Join SkillBridge and start your journey</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle mt-1"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle mt-1"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
            <div class="success-message">
                <p>You can now sign in with your credentials.</p>
                <a href="?action=login">Go to Sign In</a>
            </div>
            <?php else: ?>

            <form method="POST" action="">
                <!-- Names -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Last Name</label>
                        <input type="text" id="nom" name="nom" placeholder="Your last name">
                    </div>
                    <div class="form-group">
                        <label for="prenom">First Name</label>
                        <input type="text" id="prenom" name="prenom" placeholder="Your first name">
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="text" id="email" name="email" placeholder="you@example.com">
                </div>

                <!-- Role Selection -->
                <div class="role-selector">
                    <label class="role-label-title">I want to</label>
                    <div class="role-options">
                        <label class="role-option active">
                            <input type="radio" name="id_role" value="2" checked onchange="updateRole(this)">
                            <div class="role-content">
                                <div class="role-icon">👤</div>
                                <span class="role-name">Client</span>
                                <div class="role-desc">Find freelancers</div>
                            </div>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="id_role" value="3" onchange="updateRole(this)">
                            <div class="role-content">
                                <div class="role-icon">💼</div>
                                <span class="role-name">Freelancer</span>
                                <div class="role-desc">Offer services</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Experience Level -->
                <div class="form-group">
                    <label for="niveau">Experience Level</label>
                    <select id="niveau" name="niveau">
                        <option value="débutant">Beginner</option>
                        <option value="intermédiaire">Intermediate</option>
                        <option value="expert">Expert</option>
                    </select>
                </div>

                <!-- Passwords -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Min. 8 characters">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password">
                    </div>
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </form>

            <?php endif; ?>

            <?php if (!$success): ?>
            <div class="auth-footer">
                <p>Already have an account? <a href="?action=login">Sign in here</a></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function updateRole(radio) {
            const options = document.querySelectorAll('.role-option');
            options.forEach(opt => opt.classList.remove('active'));
            radio.closest('.role-option').classList.add('active');
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
