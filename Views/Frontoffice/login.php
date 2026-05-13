<?php
require_once __DIR__ . '/../../Controllers/UserController.php';
require_once __DIR__ . '/../../Controllers/JWT.php';
require_once __DIR__ . '/../../Controllers/EmailJsMailer.php';

$userController = new UserController();
$errors = [];
$loginHeading = 'Welcome Back';
$loginSubtitle = 'Sign in to your SkillBridge account';

if (isset($_GET['cancel_2fa'])) {
    unset($_SESSION['pending_2fa_user_id'], $_SESSION['pending_2fa_email']);
    header('Location: ?action=login');
    exit;
}

$twoFactorMode = isset($_SESSION['pending_2fa_user_id']);
$twoFactorEmail = $_SESSION['pending_2fa_email'] ?? '';
if ($twoFactorMode) {
    $loginHeading = 'Check Your Email';
    $loginSubtitle = 'Enter the 6-digit login code we sent you';
}

function completeSkillBridgeLogin($user, $userController)
{
    $jwt = JWT::generateToken($user->getIdUser(), $user->getEmail(), $user->getIdRole());

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user->getIdUser();
    $_SESSION['user_nom'] = $user->getNom();
    $_SESSION['user_prenom'] = $user->getPrenom();
    $_SESSION['user_email'] = $user->getEmail();
    $_SESSION['user_role'] = $user->getIdRole();
    $_SESSION['jwt'] = $jwt;
    $_SESSION['show_welcome_assistant'] = 1;
    unset($_SESSION['pending_2fa_user_id'], $_SESSION['pending_2fa_email']);

    $userController->recordLoginSuccess($user->getIdUser());
    $userController->logSecurityEvent($user->getIdUser(), $user->getEmail(), 'login_success', 'success', 'User completed login');

    setcookie('jwt', $jwt, time() + 86400, '/', '', false, true);
    header('Location: ?action=home');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'verify_2fa') {
    $code = trim($_POST['two_factor_code'] ?? '');
    $pendingUserId = (int)($_SESSION['pending_2fa_user_id'] ?? 0);
    $user = $pendingUserId ? $userController->getUserById($pendingUserId) : null;

    if (!$user) {
        $errors['two_factor_code'] = 'Your login session expired. Please sign in again.';
        unset($_SESSION['pending_2fa_user_id'], $_SESSION['pending_2fa_email']);
        $twoFactorMode = false;
    } elseif (!preg_match('/^\d{6}$/', $code)) {
        $errors['two_factor_code'] = 'Enter the 6-digit code.';
        $twoFactorMode = true;
    } elseif (!$userController->verifyTwoFactorCode($pendingUserId, $code)) {
        $errors['two_factor_code'] = 'Invalid or expired 2FA code.';
        $userController->logSecurityEvent($pendingUserId, $user->getEmail(), '2fa_failed', 'error', 'Invalid or expired code');
        $twoFactorMode = true;
    } else {
        $userController->logSecurityEvent($pendingUserId, $user->getEmail(), '2fa_verified', 'success', 'Email code accepted');
        completeSkillBridgeLogin($user, $userController);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'face_login_verify') {
    header('Content-Type: application/json');
    $email = trim($_POST['email'] ?? '');
    try {
        $descriptor = json_decode($_POST['descriptor'] ?? '', true);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Enter your email first.');
        }
        if (!is_array($descriptor) || count($descriptor) < 100) {
            throw new RuntimeException('No clear face detected. Try again with better light.');
        }

        $faceUser = $userController->getFaceVerificationByEmail($email);
        if (!$faceUser) {
            throw new RuntimeException('Face Verification is not enabled for this email.');
        }
        if ((int)($faceUser['email_verified'] ?? 1) !== 1) {
            throw new RuntimeException('Verify your email before using Face Verification.');
        }
        if ((int)($faceUser['is_banned'] ?? 0) === 1) {
            throw new RuntimeException('This account is banned.');
        }
        if ((int)($faceUser['id_role'] ?? 0) === 3 && (int)($faceUser['is_approved'] ?? 0) !== 1) {
            throw new RuntimeException('Your freelancer account is still pending admin approval.');
        }

        $storedDescriptor = json_decode($faceUser['face_descriptor'] ?? '', true);
        $distance = $userController->faceDescriptorDistance(array_map('floatval', $storedDescriptor ?: []), array_map('floatval', $descriptor));
        if ($distance > 0.56) {
            throw new RuntimeException('Face does not match this account. Distance: ' . number_format($distance, 2));
        }

        $jwt = JWT::generateToken($faceUser['id'], $faceUser['email'], $faceUser['id_role']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $faceUser['id'];
        $_SESSION['user_nom'] = $faceUser['nom'];
        $_SESSION['user_prenom'] = $faceUser['prenom'];
        $_SESSION['user_email'] = $faceUser['email'];
        $_SESSION['user_role'] = $faceUser['id_role'];
        $_SESSION['jwt'] = $jwt;
        $_SESSION['show_welcome_assistant'] = 1;
        setcookie('jwt', $jwt, time() + 86400, '/', '', false, true);
        $userController->recordLoginSuccess($faceUser['id']);
        $userController->logSecurityEvent($faceUser['id'], $faceUser['email'], 'face_login_success', 'success', 'Distance: ' . number_format($distance, 2));

        echo json_encode(['success' => true, 'redirect' => '?action=home', 'distance' => $distance]);
    } catch (Throwable $exception) {
        $userController->logSecurityEvent(null, $email, 'face_login_failed', 'error', $exception->getMessage());
        echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
    }
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        $user = $userController->getUserByEmail($email);

        if ($user && password_verify($password, $user->getMotDePasse())) {
            $securityState = $userController->getUserSecurityState($user->getIdUser());
            if ((int)($securityState['email_verified'] ?? 1) !== 1) {
                $verificationCode = $userController->createSecurityCode();
                $userController->setEmailVerificationCode($user->getIdUser(), $verificationCode);
                $mailResult = EmailJsMailer::sendSecurityCode($user->getEmail(), trim($user->getPrenom() . ' ' . $user->getNom()), $verificationCode, 'verify');
                $userController->logSecurityEvent($user->getIdUser(), $user->getEmail(), 'email_verification_sent', $mailResult['success'] ? 'success' : 'error', $mailResult['success'] ? 'Code sent during login' : $mailResult['message']);
                $errors['email'] = $mailResult['success']
                    ? 'Please verify your email first. We sent you a new code.'
                    : 'Please verify your email first. EmailJS could not send the code: ' . $mailResult['message'];
            } elseif ($user->getIsBanned()) {
                $errors['email'] = 'This account is banned.';
                $userController->logSecurityEvent($user->getIdUser(), $user->getEmail(), 'login_blocked', 'error', 'Banned account');
            } elseif ($user->getIdRole() == 3 && !$user->getIsApproved()) {
                $errors['email'] = 'Your account is pending approval by an administrator.';
                $userController->logSecurityEvent($user->getIdUser(), $user->getEmail(), 'login_blocked', 'error', 'Freelancer pending approval');
            } else {
                $twoFactorIsActive = (int)($securityState['two_factor_enabled'] ?? 0) === 1
                    && !empty($securityState['two_factor_setup_at']);

                if ($twoFactorIsActive) {
                    $twoFactorCode = $userController->createSecurityCode();
                    $userController->setTwoFactorCode($user->getIdUser(), $twoFactorCode);
                    $mailResult = EmailJsMailer::sendSecurityCode($user->getEmail(), trim($user->getPrenom() . ' ' . $user->getNom()), $twoFactorCode, '2fa');
                    $userController->logSecurityEvent($user->getIdUser(), $user->getEmail(), '2fa_sent', $mailResult['success'] ? 'success' : 'error', $mailResult['success'] ? 'Login code sent' : $mailResult['message']);

                    if ($mailResult['success']) {
                        $_SESSION['pending_2fa_user_id'] = $user->getIdUser();
                        $_SESSION['pending_2fa_email'] = $user->getEmail();
                        $twoFactorMode = true;
                        $twoFactorEmail = $user->getEmail();
                        $loginHeading = 'Check Your Email';
                        $loginSubtitle = 'Enter the 6-digit login code we sent you';
                    } else {
                        $errors['password'] = 'Could not send 2FA code: ' . $mailResult['message'];
                    }
                } else {
                    completeSkillBridgeLogin($user, $userController);
                }
            }
        } else {
            $errors['password'] = 'Invalid email or password.';
            $userController->logSecurityEvent($user ? $user->getIdUser() : null, $email, 'login_failed', 'error', 'Invalid email or password');
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
    <link rel="stylesheet" href="/Views/assets/css/skillbridge-front.css">
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

        body.skillbridge-front {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .auth-page {
            min-height: calc(100vh - var(--nav-height));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
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

        .field-error {
            color: #c33;
            font-size: 0.82rem;
            margin-top: 0.45rem;
        }

        .forgot-password-link {
            margin-top: 0.55rem;
            text-align: right;
        }

        .forgot-password-link a {
            color: var(--primary-dark);
            font-size: 0.86rem;
            font-weight: 700;
            text-decoration: none;
        }

        .forgot-password-link a:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        .two-factor-hint {
            padding: 0.95rem;
            border-radius: 14px;
            background: #fff7ed;
            border: 1px solid #ead8c2;
            color: #7b6654;
            font-weight: 700;
            margin-bottom: 1.2rem;
            text-align: center;
        }

        .two-factor-code {
            text-align: center;
            letter-spacing: .35rem;
            font-size: 1.35rem !important;
            font-weight: 900;
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

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #8b6f58;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin: 1.35rem 0 0.4rem;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: "";
            height: 1px;
            flex: 1;
            background: #ead8c2;
        }

        .face-login-status {
            margin-top: 0.75rem;
            color: #7b6654;
            font-size: 0.86rem;
            font-weight: 700;
            text-align: center;
        }

        .face-login-status.error {
            color: #ba4b44;
        }

        .btn-face-login {
            width: 100%;
            border: 1px solid rgba(224,112,32,.24);
            border-radius: 14px;
            padding: 0.9rem;
            color: #fff;
            background: linear-gradient(135deg, #e07020, #f5a04f);
            font-weight: 800;
            box-shadow: 0 14px 26px rgba(224,112,32,.22);
            transition: transform .22s ease, box-shadow .22s ease;
        }

        .btn-face-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 32px rgba(224,112,32,.3);
        }

        .face-login-modal {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background:
                radial-gradient(circle at top left, rgba(240,138,59,.24), transparent 28%),
                rgba(35, 28, 22, .72);
            backdrop-filter: blur(16px);
        }

        .face-login-modal.is-open {
            display: flex;
        }

        .face-login-card {
            width: min(420px, 100%);
            border-radius: 34px;
            padding: 1.4rem;
            color: #fff;
            background:
                radial-gradient(circle at 20% 0%, rgba(255, 209, 150, .24), transparent 34%),
                radial-gradient(circle at 88% 18%, rgba(224,112,32,.22), transparent 32%),
                linear-gradient(150deg, rgba(45,33,25,.96), rgba(17,18,22,.98));
            border: 1px solid rgba(255,236,211,.18);
            box-shadow: 0 30px 80px rgba(52, 34, 22, .45);
            text-align: center;
        }

        .face-login-frame {
            position: relative;
            width: min(290px, 80vw);
            height: min(290px, 80vw);
            margin: 1rem auto;
            border-radius: 42%;
            overflow: hidden;
            background: #100d0a;
            box-shadow:
                inset 0 0 0 2px rgba(255,236,211,.14),
                0 20px 48px rgba(0,0,0,.38),
                0 0 0 10px rgba(255,255,255,.035);
        }

        .face-login-frame video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }

        .face-login-frame canvas {
            position: absolute;
            inset: 0;
            z-index: 3;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
            pointer-events: none;
            opacity: .92;
            mix-blend-mode: screen;
        }

        .face-login-ring {
            position: absolute;
            inset: 14px;
            border-radius: 42%;
            border: 2px solid rgba(255,236,211,.28);
            box-shadow:
                0 0 30px rgba(240,138,59,.2),
                inset 0 0 32px rgba(255,255,255,.06);
            pointer-events: none;
            z-index: 4;
        }

        .face-login-ring::after {
            content: "";
            position: absolute;
            inset: -5px;
            border-radius: inherit;
            border: 3px solid transparent;
            border-top-color: #f5a04f;
            border-right-color: rgba(255,236,211,.75);
            filter: drop-shadow(0 0 16px rgba(245,160,79,.55));
            animation: faceLoginSpin 1.8s cubic-bezier(.45,.05,.2,.95) infinite;
        }

        @keyframes faceLoginSpin {
            to { transform: rotate(360deg); }
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
<body class="skillbridge-front">
    <?php include __DIR__ . '/partials/front_navbar.php'; ?>

    <div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="/Views/assets/img/logo1.png" alt="SkillBridge">
                <h1><?= htmlspecialchars($loginHeading) ?></h1>
                <p><?= htmlspecialchars($loginSubtitle) ?></p>
            </div>

            <?php if ($twoFactorMode): ?>
            <div class="two-factor-hint">
                <i class="fas fa-envelope-circle-check me-2"></i>
                Code sent to <?= htmlspecialchars($twoFactorEmail) ?>
            </div>
            <form method="POST" action="?action=login">
                <input type="hidden" name="action" value="verify_2fa">
                <div class="form-group">
                    <label for="two_factor_code">2FA Email Code</label>
                    <input
                        class="two-factor-code"
                        type="text"
                        id="two_factor_code"
                        name="two_factor_code"
                        maxlength="6"
                        inputmode="numeric"
                        placeholder="000000"
                        autofocus
                    >
                    <?php if (!empty($errors['two_factor_code'])): ?><div class="field-error"><?= htmlspecialchars($errors['two_factor_code']) ?></div><?php endif; ?>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-shield-halved me-2"></i>Verify Code
                </button>
            </form>
            <div class="auth-footer">
                <p><a href="?action=login&cancel_2fa=1">Use another account</a></p>
            </div>
            <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="text"
                        id="email"
                        name="email"
                        placeholder="you@example.com"
                        value="<?= htmlspecialchars($email ?? '') ?>"
                        autofocus
                    >
                    <?php if (!empty($errors['email'])): ?><div class="field-error"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="........"
                    >
                    <?php if (!empty($errors['password'])): ?><div class="field-error"><?= htmlspecialchars($errors['password']) ?></div><?php endif; ?>
                    <div class="forgot-password-link">
                        <a href="?action=forgot_password">Forgot password?</a>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>

            <div class="auth-divider">or</div>
            <button type="button" class="btn-face-login" id="faceLoginBtn">
                <i class="fas fa-camera me-2"></i>Login with Face Verification
            </button>
            <div class="face-login-status" id="faceLoginStatus">Enter your email, then verify with your webcam.</div>

            <div class="auth-footer">
                <p>Don't have an account? <a href="?action=register">Sign up here</a></p>
                <?php if (!empty($email)): ?>
                    <p class="mt-2"><a href="?action=verify_email&email=<?= urlencode($email) ?>">Verify email</a></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    </div>

    <div class="face-login-modal" id="faceLoginModal" aria-hidden="true">
        <div class="face-login-card">
            <h3 id="faceLoginTitle">Face Verification</h3>
            <p id="faceLoginMessage">Preparing camera...</p>
            <div class="face-login-frame">
                <video id="faceLoginVideo" autoplay muted playsinline></video>
                <canvas id="faceLoginCanvas"></canvas>
                <div class="face-login-ring"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        function setFaceLoginStatus(message, type) {
            const status = document.getElementById('faceLoginStatus');
            status.textContent = message;
            status.className = 'face-login-status' + (type ? ' ' + type : '');
        }

        const faceModelUrl = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@0.22.2/weights';
        let faceModelsLoaded = false;
        let faceLoginStream = null;
        let faceLoginEyeTrackingActive = false;
        let faceLoginEyeFrame = null;

        function wait(ms) {
            return new Promise(function(resolve) { setTimeout(resolve, ms); });
        }

        async function loadFaceModels() {
            if (faceModelsLoaded) return;
            if (!window.faceapi) {
                await wait(600);
            }
            if (!window.faceapi) {
                throw new Error('Face recognition library could not be loaded.');
            }
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(faceModelUrl),
                faceapi.nets.faceLandmark68Net.loadFromUri(faceModelUrl),
                faceapi.nets.faceRecognitionNet.loadFromUri(faceModelUrl)
            ]);
            faceModelsLoaded = true;
        }

        function setFaceLoginStep(title, message) {
            document.getElementById('faceLoginTitle').textContent = title;
            document.getElementById('faceLoginMessage').textContent = message;
        }

        function closeFaceLoginModal() {
            stopFaceLoginEyeTracking(document.getElementById('faceLoginCanvas'));
            document.getElementById('faceLoginModal').classList.remove('is-open');
            document.getElementById('faceLoginModal').setAttribute('aria-hidden', 'true');
            if (faceLoginStream) {
                faceLoginStream.getTracks().forEach(function(track) { track.stop(); });
                faceLoginStream = null;
            }
        }

        function clearFaceLoginCanvas(canvas) {
            if (!canvas) return;
            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height);
        }

        function drawLoginEyeGuide(context, points, color) {
            if (!points || !points.length) return;
            const center = points.reduce(function(total, point) {
                return { x: total.x + point.x, y: total.y + point.y };
            }, { x: 0, y: 0 });
            center.x /= points.length;
            center.y /= points.length;

            context.beginPath();
            points.forEach(function(point, index) {
                if (index === 0) {
                    context.moveTo(point.x, point.y);
                } else {
                    context.lineTo(point.x, point.y);
                }
            });
            context.closePath();
            context.strokeStyle = color;
            context.lineWidth = 2;
            context.shadowColor = color;
            context.shadowBlur = 18;
            context.stroke();

            context.beginPath();
            context.arc(center.x, center.y, 5, 0, Math.PI * 2);
            context.fillStyle = '#fff1d8';
            context.shadowColor = '#f08a3b';
            context.shadowBlur = 20;
            context.fill();
        }

        async function runFaceLoginEyeTracker(video, canvas) {
            if (!faceLoginEyeTrackingActive || !canvas) return;

            const width = video.videoWidth || canvas.clientWidth || 320;
            const height = video.videoHeight || canvas.clientHeight || 320;
            if (canvas.width !== width || canvas.height !== height) {
                canvas.width = width;
                canvas.height = height;
            }

            const context = canvas.getContext('2d');
            context.clearRect(0, 0, width, height);

            try {
                const detection = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.45 }))
                    .withFaceLandmarks();

                if (!faceLoginEyeTrackingActive) return;
                if (detection) {
                    const box = detection.detection.box;
                    const landmarks = detection.landmarks;
                    const leftEye = landmarks.getLeftEye();
                    const rightEye = landmarks.getRightEye();

                    context.save();
                    context.strokeStyle = 'rgba(255, 224, 179, .34)';
                    context.lineWidth = 2;
                    context.shadowColor = 'rgba(240, 138, 59, .65)';
                    context.shadowBlur = 24;
                    context.beginPath();
                    context.ellipse(box.x + box.width / 2, box.y + box.height / 2, box.width * .52, box.height * .62, 0, 0, Math.PI * 2);
                    context.stroke();

                    drawLoginEyeGuide(context, leftEye, 'rgba(255, 224, 179, .9)');
                    drawLoginEyeGuide(context, rightEye, 'rgba(240, 138, 59, .95)');
                    context.restore();
                }
            } catch (error) {
                context.clearRect(0, 0, width, height);
            }

            faceLoginEyeFrame = requestAnimationFrame(function() {
                runFaceLoginEyeTracker(video, canvas);
            });
        }

        function startFaceLoginEyeTracking(video, canvas) {
            if (!canvas) return;
            faceLoginEyeTrackingActive = true;
            runFaceLoginEyeTracker(video, canvas);
        }

        function stopFaceLoginEyeTracking(canvas) {
            faceLoginEyeTrackingActive = false;
            if (faceLoginEyeFrame) {
                cancelAnimationFrame(faceLoginEyeFrame);
                faceLoginEyeFrame = null;
            }
            clearFaceLoginCanvas(canvas);
        }

        async function captureFaceDescriptor(video) {
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.55 }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) {
                throw new Error('No clear face detected. Center your face and try again.');
            }

            return Array.from(detection.descriptor);
        }

        const faceLoginBtn = document.getElementById('faceLoginBtn');
        if (faceLoginBtn) {
            faceLoginBtn.addEventListener('click', async function() {
                const emailInput = document.getElementById('email');
                const email = emailInput.value.trim();
                const modal = document.getElementById('faceLoginModal');
                const video = document.getElementById('faceLoginVideo');
                const canvas = document.getElementById('faceLoginCanvas');

                if (!email) {
                    setFaceLoginStatus('Enter your email first.', 'error');
                    emailInput.focus();
                    return;
                }

                try {
                    faceLoginBtn.disabled = true;
                    setFaceLoginStatus('Starting webcam face verification...', '');
                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                    setFaceLoginStep('Preparing Scan', 'Loading face recognition models...');

                    await loadFaceModels();
                    faceLoginStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                    video.srcObject = faceLoginStream;
                    await video.play();

                    startFaceLoginEyeTracking(video, canvas);
                    setFaceLoginStep('Hold Still', 'Keep your eyes inside the glowing guide.');
                    await wait(1200);
                    stopFaceLoginEyeTracking(canvas);
                    const descriptor = await captureFaceDescriptor(video);

                    const formData = new FormData();
                    formData.append('action', 'face_login_verify');
                    formData.append('email', email);
                    formData.append('descriptor', JSON.stringify(descriptor));
                    const response = await fetch('?action=login', { method: 'POST', body: formData });
                    const data = await response.json();
                    if (!data.success) {
                        throw new Error(data.message || 'Face verification failed.');
                    }

                    setFaceLoginStep('Verified', 'Face matched. Redirecting...');
                    setFaceLoginStatus('Face verified. Redirecting...', '');
                    await wait(700);
                    window.location.href = data.redirect || '?action=home';
                } catch (error) {
                    setFaceLoginStatus(error.message || 'Face verification failed.', 'error');
                    setFaceLoginStep('Verification Failed', error.message || 'Please try again.');
                    await wait(1200);
                    closeFaceLoginModal();
                } finally {
                    faceLoginBtn.disabled = false;
                }
            });
        }
    </script>
</body>
</html>
