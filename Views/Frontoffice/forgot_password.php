<?php
require_once __DIR__ . '/../../Controllers/UserController.php';

$userController = new UserController();
$email = trim($_POST['email'] ?? '');
$errors = [];
$success = '';
$debugError = '';

function generateTemporaryPassword()
{
    $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    $numbers = '23456789';
    $symbols = '@#$%';
    $password = '';

    for ($i = 0; $i < 7; $i++) {
        $password .= $letters[random_int(0, strlen($letters) - 1)];
    }
    $password .= $numbers[random_int(0, strlen($numbers) - 1)];
    $password .= $symbols[random_int(0, strlen($symbols) - 1)];
    $password .= $numbers[random_int(0, strlen($numbers) - 1)];

    return str_shuffle($password);
}

function emailJsLooksConfigured($config)
{
    return !empty($config['service_id'])
        && !empty($config['template_id'])
        && !empty($config['public_key'])
        && strpos($config['service_id'], 'YOUR_') !== 0
        && strpos($config['template_id'], 'YOUR_') !== 0
        && strpos($config['public_key'], 'YOUR_') !== 0;
}

function sendTemporaryPasswordWithEmailJs($toEmail, $toName, $temporaryPassword)
{
    $config = Config::getEmailJsConfig();
    if (!emailJsLooksConfigured($config)) {
        return [
            'success' => false,
            'message' => 'EmailJS is not configured yet. Add service id, template id and public key in config.php.'
        ];
    }

    $payload = [
        'service_id' => $config['service_id'],
        'template_id' => $config['template_id'],
        'user_id' => $config['public_key'],
        'template_params' => [
            'to_email' => $toEmail,
            'to_name' => $toName,
            'user_email' => $toEmail,
            'temporary_password' => $temporaryPassword,
            'password' => $temporaryPassword,
            'login_url' => 'http://localhost:8000/?action=login',
            'app_name' => 'SkillBridge'
        ]
    ];

    if (!empty($config['access_token'])) {
        $payload['accessToken'] = $config['access_token'];
    }

    $ch = curl_init('https://api.emailjs.com/api/v1.0/email/send');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 25
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return ['success' => false, 'message' => 'EmailJS request failed: ' . $curlError];
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        return ['success' => false, 'message' => 'EmailJS error: ' . trim((string) $response)];
    }

    return ['success' => true];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    if (empty($errors)) {
        $user = $userController->getUserByEmail($email);
        if ($user) {
            $temporaryPassword = generateTemporaryPassword();
            $fullName = trim($user->getPrenom() . ' ' . $user->getNom());
            $emailResult = sendTemporaryPasswordWithEmailJs($user->getEmail(), $fullName ?: 'SkillBridge user', $temporaryPassword);

            if ($emailResult['success']) {
                if ($userController->updatePassword($user->getIdUser(), $temporaryPassword)) {
                    $success = 'A temporary password was sent to your email. Use it to log in, then change it from your profile.';
                    $email = '';
                } else {
                    $debugError = 'The email was sent, but the password could not be updated. Please try again.';
                }
            } else {
                $debugError = $emailResult['message'];
            }
        } else {
            $success = 'If this email exists in SkillBridge, a temporary password will be sent.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SkillBridge</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/Views/assets/css/skillbridge-front.css">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        :root {
            --primary: #e07020;
            --primary-dark: #b84f12;
            --text: #2f2925;
            --text-light: #7b6654;
            --border: #ead8c2;
        }

        body.skillbridge-front {
            min-height: 100vh;
            background:
                radial-gradient(circle at 18% 12%, rgba(224,112,32,.18), transparent 28%),
                linear-gradient(135deg, #fff8ef 0%, #f4e2ce 42%, #d7772c 100%);
            font-family: 'DM Sans', 'Segoe UI', sans-serif;
        }

        .auth-page {
            min-height: calc(100vh - var(--nav-height));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .reset-card {
            width: min(440px, 100%);
            background: rgba(255,255,255,.92);
            border: 1px solid rgba(234,216,194,.95);
            border-radius: 22px;
            padding: 2.4rem;
            box-shadow: 0 24px 70px rgba(93, 57, 31, .22);
            backdrop-filter: blur(14px);
            position: relative;
            overflow: hidden;
        }

        .reset-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 6px;
            background: linear-gradient(90deg, #b84f12, #e07020, #f5a04f);
        }

        .reset-icon {
            width: 70px;
            height: 70px;
            display: grid;
            place-items: center;
            margin: 0 auto 1.15rem;
            color: #fff;
            background: linear-gradient(135deg, #e07020, #f5a04f);
            border-radius: 22px;
            box-shadow: 0 16px 34px rgba(224,112,32,.28);
            font-size: 1.7rem;
        }

        .reset-card h1 {
            color: var(--text);
            text-align: center;
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: .55rem;
        }

        .reset-card p {
            color: var(--text-light);
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            color: var(--text);
            font-weight: 700;
            margin-bottom: .55rem;
        }

        .form-group input {
            width: 100%;
            padding: .9rem 1rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            outline: none;
        }

        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(224,112,32,.12);
        }

        .field-error {
            color: #ba4b44;
            font-size: .86rem;
            margin-top: .45rem;
            font-weight: 700;
        }

        .btn-reset {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: .95rem;
            color: #fff;
            background: linear-gradient(135deg, #e07020, #f5a04f);
            font-weight: 800;
            box-shadow: 0 14px 28px rgba(224,112,32,.25);
            transition: transform .22s ease, box-shadow .22s ease;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 34px rgba(224,112,32,.32);
        }

        .reset-note {
            margin-top: 1rem;
            padding: .9rem;
            border-radius: 14px;
            background: #fff7ed;
            color: #7b6654;
            font-size: .9rem;
            font-weight: 650;
        }

        .back-link {
            display: block;
            margin-top: 1.25rem;
            text-align: center;
            color: var(--primary-dark);
            font-weight: 800;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .reset-card {
                padding: 2rem 1.35rem;
            }
        }
    </style>
</head>
<body class="skillbridge-front">
    <?php include __DIR__ . '/partials/front_navbar.php'; ?>

    <main class="auth-page">
        <section class="reset-card">
            <div class="reset-icon"><i class="fas fa-key"></i></div>
            <h1>Reset Password</h1>
            <p>Enter your account email. We will send a temporary password using EmailJS.</p>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($debugError): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($debugError) ?></div>
            <?php endif; ?>

            <form method="POST" action="?action=forgot_password">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="you@example.com" autofocus>
                    <?php if (!empty($errors['email'])): ?><div class="field-error"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
                </div>

                <button type="submit" class="btn-reset">
                    <i class="fas fa-paper-plane me-2"></i>Send Temporary Password
                </button>
            </form>

            <div class="reset-note">
                For security, SkillBridge never sends your old password. It creates a new temporary password and stores it hashed.
            </div>

            <a class="back-link" href="?action=login">Back to login</a>
        </section>
    </main>
</body>
</html>
