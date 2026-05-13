<?php
require_once __DIR__ . '/../../Controllers/UserController.php';
require_once __DIR__ . '/../../Controllers/EmailJsMailer.php';

$userController = new UserController();
$email = trim($_POST['email'] ?? ($_GET['email'] ?? ''));
$code = trim($_POST['code'] ?? '');
$errors = [];
$success = isset($_GET['sent']) ? 'We sent a 6-digit verification code to your email.' : '';
$debugError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'verify';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    if ($action === 'resend' && empty($errors)) {
        $user = $userController->getUserByEmail($email);
        if ($user) {
            $newCode = $userController->createSecurityCode();
            $userController->setEmailVerificationCode($user->getIdUser(), $newCode);
            $mailResult = EmailJsMailer::sendSecurityCode($user->getEmail(), trim($user->getPrenom() . ' ' . $user->getNom()), $newCode, 'verify');
            $userController->logSecurityEvent($user->getIdUser(), $user->getEmail(), 'email_verification_resent', $mailResult['success'] ? 'success' : 'error', $mailResult['success'] ? 'Verification code resent' : $mailResult['message']);
            $success = $mailResult['success'] ? 'A new verification code was sent.' : '';
            $debugError = $mailResult['success'] ? '' : $mailResult['message'];
        } else {
            $success = 'If this email exists, a new verification code was sent.';
        }
    }

    if ($action === 'verify') {
        if ($code === '') {
            $errors['code'] = 'Verification code is required.';
        } elseif (!preg_match('/^\d{6}$/', $code)) {
            $errors['code'] = 'Enter the 6-digit code.';
        }

        if (empty($errors)) {
            $userId = $userController->verifyEmailCode($email, $code);
            if ($userId) {
                $userController->markEmailVerified($userId);
                $userController->logSecurityEvent($userId, $email, 'email_verified', 'success', 'Email address verified');
                $success = 'Email verified successfully. You can now sign in.';
                $code = '';
            } else {
                $errors['code'] = 'Invalid or expired verification code.';
                $userController->logSecurityEvent(null, $email, 'email_verification_failed', 'error', 'Invalid or expired code');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - SkillBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Views/assets/css/skillbridge-front.css">
    <style>
        body.skillbridge-front {
            min-height: 100vh;
            background:
                radial-gradient(circle at 18% 12%, rgba(224,112,32,.18), transparent 28%),
                linear-gradient(135deg, #fff8ef 0%, #f4e2ce 42%, #d7772c 100%);
        }

        .verify-page {
            min-height: calc(100vh - var(--nav-height));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .verify-card {
            width: min(450px, 100%);
            background: rgba(255,255,255,.94);
            border: 1px solid #ead8c2;
            border-radius: 24px;
            padding: 2.4rem;
            box-shadow: 0 24px 70px rgba(93,57,31,.22);
        }

        .verify-icon {
            width: 72px;
            height: 72px;
            display: grid;
            place-items: center;
            margin: 0 auto 1.1rem;
            color: #fff;
            background: linear-gradient(135deg, #e07020, #f5a04f);
            border-radius: 24px;
            font-size: 1.75rem;
            box-shadow: 0 16px 34px rgba(224,112,32,.28);
        }

        .verify-card h1 {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 900;
            color: #2f2925;
        }

        .verify-card p {
            text-align: center;
            color: #7b6654;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            color: #2f2925;
            font-weight: 800;
            margin-bottom: .55rem;
        }

        .form-group input {
            width: 100%;
            border: 1px solid #ead8c2;
            border-radius: 14px;
            padding: .95rem 1rem;
            outline: none;
        }

        .form-group input:focus {
            border-color: #e07020;
            box-shadow: 0 0 0 4px rgba(224,112,32,.12);
        }

        .code-input {
            text-align: center;
            font-size: 1.35rem;
            letter-spacing: .35rem;
            font-weight: 900;
        }

        .field-error {
            color: #ba4b44;
            font-size: .86rem;
            margin-top: .45rem;
            font-weight: 700;
        }

        .btn-verify,
        .btn-resend {
            width: 100%;
            border: none;
            border-radius: 14px;
            padding: .95rem;
            font-weight: 900;
        }

        .btn-verify {
            color: #fff;
            background: linear-gradient(135deg, #e07020, #f5a04f);
            box-shadow: 0 14px 28px rgba(224,112,32,.25);
        }

        .btn-resend {
            margin-top: .75rem;
            color: #7b3d12;
            background: #fff3e5;
            border: 1px solid #ead8c2;
        }

        .back-link {
            display: block;
            margin-top: 1.2rem;
            text-align: center;
            color: #b84f12;
            font-weight: 800;
            text-decoration: none;
        }
    </style>
</head>
<body class="skillbridge-front">
    <?php include __DIR__ . '/partials/front_navbar.php'; ?>

    <main class="verify-page">
        <section class="verify-card">
            <div class="verify-icon"><i class="fas fa-envelope-circle-check"></i></div>
            <h1>Email Verification</h1>
            <p>Enter the 6-digit code sent to your email.</p>

            <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
            <?php if ($debugError): ?><div class="alert alert-danger"><?= htmlspecialchars($debugError) ?></div><?php endif; ?>

            <form method="POST" action="?action=verify_email">
                <input type="hidden" name="action" value="verify">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="you@example.com" required>
                    <?php if (!empty($errors['email'])): ?><div class="field-error"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="code">Verification Code</label>
                    <input class="code-input" type="text" id="code" name="code" value="<?= htmlspecialchars($code) ?>" inputmode="numeric" maxlength="6" placeholder="000000" required>
                    <?php if (!empty($errors['code'])): ?><div class="field-error"><?= htmlspecialchars($errors['code']) ?></div><?php endif; ?>
                </div>
                <button class="btn-verify" type="submit"><i class="fas fa-check me-2"></i>Verify Email</button>
            </form>

            <form method="POST" action="?action=verify_email">
                <input type="hidden" name="action" value="resend">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <button class="btn-resend" type="submit"><i class="fas fa-rotate me-2"></i>Resend Code</button>
            </form>

            <a class="back-link" href="?action=login">Back to login</a>
        </section>
    </main>
</body>
</html>
