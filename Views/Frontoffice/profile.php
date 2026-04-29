<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ?action=login');
    exit;
}
require_once __DIR__ . '/../../Controllers/UserController.php';

$userController = new UserController();
$user = $userController->getUserById($_SESSION['user_id']);
$profileErrors = [];
$passwordErrors = [];
$success = "";
$aiError = "";
$isFreelancer = $user && (int) $user->getIdRole() === 3;

function isValidProfileName($value)
{
    return (bool) preg_match("/^[a-zA-ZÀ-ÿ][a-zA-ZÀ-ÿ' -]{1,49}$/u", $value);
}

function isValidPhoneNumber($value)
{
    return (bool) preg_match('/^[0-9+\s().-]{8,20}$/', $value);
}

function isValidProfilePictureValue($value)
{
    if (filter_var($value, FILTER_VALIDATE_URL)) {
        return true;
    }

    return (bool) preg_match('/^(\/?[A-Za-z0-9._\-\/]+)$/', $value);
}

function buildFreelancerProfilePrompt($answers)
{
    return "You are helping a freelancer build a professional profile for a marketplace website.
Generate valid JSON only with exactly these keys:
- skill_summary
- bio
- experience_description

Rules:
- skill_summary: max 220 characters, compact, professional, no bullet points
- bio: max 520 characters, warm and professional, first person
- experience_description: max 720 characters, concrete and credible, first person
- Do not use markdown
- Do not include extra keys
- Stay clearly below every limit

Freelancer answers:
- Main services: " . $answers['services'] . "
- Main skills/tools: " . $answers['skills'] . "
- Years of experience: " . $answers['experience_years'] . "
- Typical projects/clients: " . $answers['projects'] . "
- Strong points: " . $answers['strengths'] . "
- Preferred tone: " . $answers['tone'];
}

function trimGeneratedProfileField($text, $maxLength)
{
    $text = trim(preg_replace('/\s+/', ' ', (string) $text));

    if (mb_strlen($text) <= $maxLength) {
        return $text;
    }

    $trimmed = mb_substr($text, 0, $maxLength);
    $lastSpace = mb_strrpos($trimmed, ' ');

    if ($lastSpace !== false && $lastSpace > (int) ($maxLength * 0.6)) {
        $trimmed = mb_substr($trimmed, 0, $lastSpace);
    }

    return rtrim($trimmed, " \t\n\r\0\x0B.,;:-");
}

function generateFreelancerProfileWithAI($answers)
{
    $apiKey = Config::getOpenRouterApiKey();
    if (!$apiKey) {
        return ['success' => false, 'message' => 'OPENROUTER_API_KEY is missing on the server.'];
    }

    $payload = [
        'model' => 'openrouter/free',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a professional profile writer. Return JSON only.'
            ],
            [
                'role' => 'user',
                'content' => buildFreelancerProfilePrompt($answers)
            ]
        ],
        'temperature' => 0.7
    ];

    $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: http://localhost:8000',
            'X-Title: SkillBridge'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 45
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return ['success' => false, 'message' => 'AI request failed: ' . $curlError];
    }

    $decoded = json_decode($response, true);
    $content = $decoded['choices'][0]['message']['content'] ?? '';

    if ($httpCode >= 400 || $content === '') {
        $message = $decoded['error']['message'] ?? 'The AI service returned an unexpected response.';
        return ['success' => false, 'message' => $message];
    }

    $json = json_decode(trim($content), true);
    if (!is_array($json)) {
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $json = json_decode($matches[0], true);
        }
    }

    if (!is_array($json)) {
        return ['success' => false, 'message' => 'The AI response could not be parsed.'];
    }

    return [
        'success' => true,
        'data' => [
            'skill_summary' => trimGeneratedProfileField($json['skill_summary'] ?? '', 220),
            'bio' => trimGeneratedProfileField($json['bio'] ?? '', 520),
            'experience_description' => trimGeneratedProfileField($json['experience_description'] ?? '', 720)
        ]
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'generate_ai_profile' && $isFreelancer) {
        header('Content-Type: application/json');

        $answers = [
            'services' => trim($_POST['ai_services'] ?? ''),
            'skills' => trim($_POST['ai_skills'] ?? ''),
            'experience_years' => trim($_POST['ai_experience_years'] ?? ''),
            'projects' => trim($_POST['ai_projects'] ?? ''),
            'strengths' => trim($_POST['ai_strengths'] ?? ''),
            'tone' => trim($_POST['ai_tone'] ?? '')
        ];

        foreach ($answers as $key => $value) {
            if ($value === '') {
                echo json_encode(['success' => false, 'message' => 'Please answer all AI questions before generating.']);
                exit;
            }
        }

        $result = generateFreelancerProfileWithAI($answers);
        echo json_encode($result);
        exit;
    } elseif ($action === 'update_profile') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $profilePicture = trim($_POST['profile_picture'] ?? '');
        $skillSummary = trim($_POST['skill_summary'] ?? '');
        $experienceDescription = trim($_POST['experience_description'] ?? '');

        if (!$nom) {
            $profileErrors['nom'] = "Last name is required.";
        } elseif (!isValidProfileName($nom)) {
            $profileErrors['nom'] = "Last name cannot contain numbers. Use only letters, spaces, apostrophes, or hyphens.";
        }
        if (!$prenom) {
            $profileErrors['prenom'] = "First name is required.";
        } elseif (!isValidProfileName($prenom)) {
            $profileErrors['prenom'] = "First name cannot contain numbers. Use only letters, spaces, apostrophes, or hyphens.";
        }
        if (!$email) {
            $profileErrors['email'] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $profileErrors['email'] = "Please enter a valid email address.";
        } elseif (strlen($email) > 100) {
            $profileErrors['email'] = "Email address is too long.";
        } elseif ($userController->emailExists($email, $user->getIdUser())) {
            $profileErrors['email'] = "This email is already in use.";
        }

        if ($isFreelancer) {
            if ($phone !== '' && !isValidPhoneNumber($phone)) {
                $profileErrors['phone'] = "Phone must contain 8 to 20 valid characters.";
            }
            if ($bio !== '' && mb_strlen($bio) > 600) {
                $profileErrors['bio'] = "Bio must stay under 600 characters.";
            }
            if ($profilePicture !== '') {
                if (strlen($profilePicture) > 255) {
                    $profileErrors['profile_picture'] = "Profile picture path is too long.";
                } elseif (!isValidProfilePictureValue($profilePicture)) {
                    $profileErrors['profile_picture'] = "Use a valid image URL or image path.";
                }
            }
            if ($skillSummary !== '' && mb_strlen($skillSummary) > 255) {
                $profileErrors['skill_summary'] = "Skill summary must stay under 255 characters.";
            }
            if ($experienceDescription !== '' && mb_strlen($experienceDescription) > 800) {
                $profileErrors['experience_description'] = "Experience description must stay under 800 characters.";
            }
        }

        if (empty($profileErrors)) {
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);

            if ($isFreelancer) {
                $user->setPhone($phone === '' ? null : $phone);
                $user->setBio($bio === '' ? null : $bio);
                $user->setProfilePicture($profilePicture === '' ? null : $profilePicture);
                $user->setSkillSummary($skillSummary === '' ? null : $skillSummary);
                $user->setExperienceDescription($experienceDescription === '' ? null : $experienceDescription);
            }

            $userController->updateUser($user);
            $success = "Profile updated successfully.";
            $user = $userController->getUserById($_SESSION['user_id']);
            $isFreelancer = $user && (int) $user->getIdRole() === 3;
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($current === '') {
            $passwordErrors['current_password'] = "Current password is required.";
        } elseif (!password_verify($current, $user->getMotDePasse())) {
            $passwordErrors['current_password'] = "Current password is incorrect.";
        }
        if ($new === '') {
            $passwordErrors['new_password'] = "New password is required.";
        } elseif (strlen($new) < 8) {
            $passwordErrors['new_password'] = "Password must be at least 8 characters.";
        } elseif (strlen($new) > 72) {
            $passwordErrors['new_password'] = "Password is too long.";
        } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,72}$/', $new)) {
            $passwordErrors['new_password'] = "Password must contain at least one letter and one number.";
        }
        if ($confirm === '') {
            $passwordErrors['confirm_password'] = "Password confirmation is required.";
        } elseif ($new !== '' && $new !== $confirm) {
            $passwordErrors['confirm_password'] = "New passwords do not match.";
        }

        if (empty($passwordErrors)) {
            $userController->updatePassword($user->getIdUser(), $new);
            $success = "Password changed successfully.";
        }
    }
}

$profilePicture = $user->getProfilePicture();
$avatarText = strtoupper(mb_substr($user->getPrenom(), 0, 1) . mb_substr($user->getNom(), 0, 1));
$freelancerFields = [
    $user->getPhone(),
    $user->getBio(),
    $user->getProfilePicture(),
    $user->getSkillSummary(),
    $user->getExperienceDescription()
];
$completedFreelancerFields = 0;
foreach ($freelancerFields as $field) {
    if (!empty($field)) {
        $completedFreelancerFields++;
    }
}
$completionPercentage = $isFreelancer ? (int) round(($completedFreelancerFields / 5) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - SkillBridge</title>
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

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            color: var(--text);
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

        .profile-info-card,
        .form-card,
        .freelancer-prompt {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .profile-info-card {
            padding: 2.5rem;
            margin-bottom: 2rem;
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .profile-avatar {
            width: 108px;
            height: 108px;
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
            overflow: hidden;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-details h2 {
            font-size: 1.8rem;
            color: var(--text);
            margin-bottom: 0.25rem;
        }

        .profile-details p {
            color: var(--text-light);
            font-size: 0.95rem;
            margin-bottom: 0.35rem;
        }

        .freelancer-prompt {
            padding: 1.4rem 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary);
            background: linear-gradient(135deg, rgba(224, 112, 32, 0.08) 0%, rgba(245, 169, 98, 0.12) 100%);
        }

        .freelancer-prompt h3 {
            margin: 0 0 0.35rem;
            font-size: 1.15rem;
        }

        .freelancer-prompt p {
            margin: 0;
            color: var(--text-light);
        }

        .completion-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.85rem;
            padding: 0.55rem 0.8rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.85);
            font-weight: 600;
            color: var(--secondary);
        }

        .form-card {
            margin-bottom: 2rem;
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

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: white;
            font-family: inherit;
            resize: vertical;
        }

        .form-group textarea {
            min-height: 120px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
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

        .field-hint {
            display: block;
            margin-top: 0.45rem;
            color: var(--text-light);
            font-size: 0.82rem;
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

        .field-error {
            color: #c33;
            font-size: 0.82rem;
            margin-top: 0.45rem;
        }

        .profile-tools {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            margin-top: 1.5rem;
        }

        .btn-ai-assist {
            border: none;
            border-radius: 999px;
            padding: 0.8rem 1.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1f232a 0%, #3f4652 100%);
            color: white;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .btn-ai-assist:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(31, 35, 42, 0.22);
        }

        .ai-helper-note {
            color: var(--text-light);
            font-size: 0.88rem;
        }

        .ai-panel {
            display: none;
            margin-top: 1.5rem;
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        }

        .ai-panel.is-open {
            display: block;
        }

        .ai-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1.15rem 1.25rem;
            background: linear-gradient(135deg, rgba(224, 112, 32, 0.08) 0%, rgba(245, 169, 98, 0.14) 100%);
            border-bottom: 1px solid var(--border);
        }

        .ai-panel-header h4 {
            margin: 0;
            font-size: 1.05rem;
        }

        .ai-close-btn {
            border: none;
            background: transparent;
            font-size: 1.1rem;
            color: var(--text-light);
        }

        .ai-panel-body {
            padding: 1.25rem;
            background: white;
        }

        .ai-question-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .ai-question-grid .full {
            grid-column: 1 / -1;
        }

        .ai-panel textarea,
        .ai-panel input,
        .ai-panel select {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: inherit;
        }

        .ai-panel textarea {
            min-height: 110px;
            resize: vertical;
        }

        .ai-panel textarea:focus,
        .ai-panel input:focus,
        .ai-panel select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(224, 112, 32, 0.1);
            outline: none;
        }

        .ai-panel-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-top: 1.25rem;
        }

        .ai-status {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .ai-status.error {
            color: #c33;
        }

        .ai-status.success {
            color: #198754;
        }

        .btn-ai-generate {
            border: none;
            border-radius: 10px;
            padding: 0.85rem 1.25rem;
            background: var(--primary);
            color: white;
            font-weight: 700;
        }

        .btn-ai-generate:disabled {
            opacity: 0.7;
            cursor: wait;
        }

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
            .form-card-body,
            .freelancer-prompt {
                padding: 1.5rem;
            }

            .ai-question-grid {
                grid-template-columns: 1fr;
            }

            .ai-panel-actions,
            .profile-tools {
                flex-direction: column;
                align-items: stretch;
            }
        }

        .container {
            max-width: 1320px;
        }

        body.skillbridge-front {
            font-family: 'DM Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top center, rgba(224,112,32,.07), transparent 26%),
                linear-gradient(180deg, #fffdf9 0%, #faf6f0 22%, #f8f1e7 100%);
            color: #1f1f23;
        }

        .page-header {
            background:
                radial-gradient(circle at top right, rgba(240,138,59,.16), transparent 30%),
                linear-gradient(135deg, #1e1e20 0%, #2d2d31 100%);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 0 0 28px 28px;
            box-shadow: 0 20px 40px rgba(30,30,32,.18);
            margin: 0 1.2rem 2.5rem;
        }

        .profile-info-card,
        .form-card,
        .freelancer-prompt {
            background: rgba(255,255,255,.9);
            border: 1px solid #dfd1bd;
            border-radius: 24px;
            box-shadow: 0 16px 34px rgba(30,30,32,.08);
            backdrop-filter: blur(10px);
        }

        .profile-avatar {
            width: 118px;
            height: 118px;
            box-shadow: 0 18px 34px rgba(224,112,32,.24);
        }

        .profile-details h2,
        .form-card-header h3 {
            font-family: 'Playfair Display', serif;
        }

        .form-card-header {
            background: linear-gradient(180deg, #fffaf4 0%, #f8f1e7 100%);
        }

        .form-group input,
        .form-group textarea,
        .ai-panel input,
        .ai-panel textarea,
        .ai-panel select {
            border-radius: 14px;
            border-color: #d9c6ad;
            background: #fffdf9;
        }

        .btn-update {
            border-radius: 14px;
            background: linear-gradient(135deg, #e07020, #f08a3b);
            box-shadow: 0 14px 26px rgba(224,112,32,.2);
        }

        .btn-ai-assist {
            border-radius: 14px;
            text-decoration: none;
        }

        .profile-top-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .btn-rating-shortcut {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 14px;
            padding: 0.78rem 1.15rem;
            font-weight: 800;
            text-decoration: none;
            color: #073f38;
            background: linear-gradient(135deg, #c9fff2 0%, #69dec8 100%);
            border: 1px solid rgba(33, 150, 130, 0.32);
            box-shadow: 0 14px 24px rgba(33, 150, 130, 0.16);
            transition: transform 0.25s ease, box-shadow 0.25s ease, filter 0.25s ease;
        }

        .btn-rating-shortcut:hover {
            color: #052f2a;
            filter: saturate(1.08);
            transform: translateY(-2px);
            box-shadow: 0 18px 30px rgba(33, 150, 130, 0.24);
        }

        .completion-pill {
            background: rgba(224,112,32,.1);
            border: 1px solid rgba(224,112,32,.18);
        }
    </style>
</head>
<body class="skillbridge-front">
    <?php require __DIR__ . '/partials/front_navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>My Profile</h1>
            <p>Manage your account settings and preferences</p>
        </div>
    </div>

    <main class="container" style="padding: 0 2rem; margin-bottom: 2rem;">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <div class="profile-info-card">
            <div class="profile-info">
                <div class="profile-avatar">
                    <?php if (!empty($profilePicture)): ?>
                        <img src="<?= htmlspecialchars($profilePicture) ?>" alt="<?= htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()) ?>">
                    <?php else: ?>
                        <?= htmlspecialchars($avatarText) ?>
                    <?php endif; ?>
                </div>
                <div class="profile-details">
                    <h2><?= htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()) ?></h2>
                    <p><?= htmlspecialchars($user->getEmail()) ?></p>
                    <p style="font-size: 0.85rem; margin-top: 0.5rem; text-transform: capitalize;">Experience: <strong><?= htmlspecialchars($user->getNiveau()) ?></strong></p>
                    <?php if ($isFreelancer && !empty($user->getSkillSummary())): ?>
                        <p><strong>Skill summary:</strong> <?= htmlspecialchars($user->getSkillSummary()) ?></p>
                    <?php endif; ?>
                    <?php if ($isFreelancer): ?>
                        <div class="profile-top-actions">
                            <a href="?action=myrating" class="btn-rating-shortcut">
                                <i class="fas fa-star"></i>My Rating
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($isFreelancer): ?>
            <div class="freelancer-prompt">
                <h3>Let’s upgrade your profile to get more attention</h3>
                <p>Clients trust complete freelancer profiles faster. Add your key details so your card stands out in the freelancer list.</p>
                <div class="completion-pill">
                    <i class="fas fa-bolt"></i>
                    <span>Profile completion: <?= $completionPercentage ?>%</span>
                </div>
            </div>
        <?php endif; ?>

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
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user->getNom()) ?>">
                            <?php if (!empty($profileErrors['nom'])): ?><div class="field-error"><?= htmlspecialchars($profileErrors['nom']) ?></div><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="prenom">First Name</label>
                            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user->getPrenom()) ?>">
                            <?php if (!empty($profileErrors['prenom'])): ?><div class="field-error"><?= htmlspecialchars($profileErrors['prenom']) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="text" id="email" name="email" value="<?= htmlspecialchars($user->getEmail()) ?>">
                        <?php if (!empty($profileErrors['email'])): ?><div class="field-error"><?= htmlspecialchars($profileErrors['email']) ?></div><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="niveau">Experience Level</label>
                        <input type="text" id="niveau" value="<?= htmlspecialchars($user->getNiveau()) ?>" readonly disabled>
                    </div>

                    <?php if ($isFreelancer): ?>
                        <div class="profile-tools">
                            <button type="button" class="btn-ai-assist" id="openAiPanelBtn">
                                <i class="fas fa-wand-magic-sparkles me-2"></i>Generate With AI
                            </button>
                            <span class="ai-helper-note">Answer a few questions, let AI draft the text, then review and save it yourself.</span>
                        </div>

                        <div class="ai-panel" id="aiProfilePanel">
                            <div class="ai-panel-header">
                                <div>
                                    <h4>AI Profile Assistant</h4>
                                    <p style="margin: 0.35rem 0 0; color: var(--text-light);">Tell the assistant about your work and it will draft your profile fields automatically.</p>
                                </div>
                                <button type="button" class="ai-close-btn" id="closeAiPanelBtn">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="ai-panel-body">
                                <div class="ai-question-grid">
                                    <div class="form-group">
                                        <label for="ai_services">What services do you offer?</label>
                                        <input type="text" id="ai_services" placeholder="Web development, UI/UX design, branding...">
                                    </div>
                                    <div class="form-group">
                                        <label for="ai_skills">Main skills or tools</label>
                                        <input type="text" id="ai_skills" placeholder="PHP, MySQL, Figma, Laravel, Bootstrap...">
                                    </div>
                                    <div class="form-group">
                                        <label for="ai_experience_years">Years of experience</label>
                                        <input type="text" id="ai_experience_years" placeholder="2 years, 5 years, beginner with strong projects...">
                                    </div>
                                    <div class="form-group">
                                        <label for="ai_tone">Preferred tone</label>
                                        <select id="ai_tone">
                                            <option value="professional and confident">Professional and confident</option>
                                            <option value="friendly and professional">Friendly and professional</option>
                                            <option value="clear and direct">Clear and direct</option>
                                        </select>
                                    </div>
                                    <div class="form-group full">
                                        <label for="ai_projects">What kind of projects or clients do you usually work with?</label>
                                        <textarea id="ai_projects" placeholder="Small business websites, dashboards, mobile-friendly landing pages, startup branding..."></textarea>
                                    </div>
                                    <div class="form-group full">
                                        <label for="ai_strengths">What are your strongest points?</label>
                                        <textarea id="ai_strengths" placeholder="Fast delivery, clean code, clear communication, attention to detail..."></textarea>
                                    </div>
                                </div>

                                <div class="ai-panel-actions">
                                    <div class="ai-status" id="aiStatus">The generated content will fill your profile fields automatically, then you can validate it before saving.</div>
                                    <button type="button" class="btn-ai-generate" id="generateAiProfileBtn">
                                        <i class="fas fa-bolt me-2"></i>Generate Draft
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user->getPhone() ?? '') ?>" placeholder="+216 12 345 678">
                                <span class="field-hint">Optional. Use a valid phone number format.</span>
                                <?php if (!empty($profileErrors['phone'])): ?><div class="field-error"><?= htmlspecialchars($profileErrors['phone']) ?></div><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="profile_picture">Profile Picture</label>
                                <input type="text" id="profile_picture" name="profile_picture" value="<?= htmlspecialchars($user->getProfilePicture() ?? '') ?>" placeholder="https://... or /uploads/my-photo.jpg">
                                <span class="field-hint">Paste an image URL or an existing image path.</span>
                                <?php if (!empty($profileErrors['profile_picture'])): ?><div class="field-error"><?= htmlspecialchars($profileErrors['profile_picture']) ?></div><?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="skill_summary">Skill Summary</label>
                            <input type="text" id="skill_summary" name="skill_summary" value="<?= htmlspecialchars($user->getSkillSummary() ?? '') ?>" placeholder="UI design, PHP MVC, Laravel, branding...">
                            <?php if (!empty($profileErrors['skill_summary'])): ?><div class="field-error"><?= htmlspecialchars($profileErrors['skill_summary']) ?></div><?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea id="bio" name="bio" placeholder="Tell clients who you are and the kind of work you do."><?= htmlspecialchars($user->getBio() ?? '') ?></textarea>
                            <?php if (!empty($profileErrors['bio'])): ?><div class="field-error"><?= htmlspecialchars($profileErrors['bio']) ?></div><?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="experience_description">Experience Description</label>
                            <textarea id="experience_description" name="experience_description" placeholder="Describe your practical experience, projects, and what clients can expect from you."><?= htmlspecialchars($user->getExperienceDescription() ?? '') ?></textarea>
                            <?php if (!empty($profileErrors['experience_description'])): ?><div class="field-error"><?= htmlspecialchars($profileErrors['experience_description']) ?></div><?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn-update">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>

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
                        <input type="password" id="current_password" name="current_password" placeholder="Current password">
                        <?php if (!empty($passwordErrors['current_password'])): ?><div class="field-error"><?= htmlspecialchars($passwordErrors['current_password']) ?></div><?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" placeholder="Min. 8 characters">
                            <?php if (!empty($passwordErrors['new_password'])): ?><div class="field-error"><?= htmlspecialchars($passwordErrors['new_password']) ?></div><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat new password">
                            <?php if (!empty($passwordErrors['confirm_password'])): ?><div class="field-error"><?= htmlspecialchars($passwordErrors['confirm_password']) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn-update">
                        <i class="fas fa-shield-alt me-2"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 SkillBridge. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($isFreelancer): ?>
    <script>
        const aiPanel = document.getElementById('aiProfilePanel');
        const openAiPanelBtn = document.getElementById('openAiPanelBtn');
        const closeAiPanelBtn = document.getElementById('closeAiPanelBtn');
        const generateAiProfileBtn = document.getElementById('generateAiProfileBtn');
        const aiStatus = document.getElementById('aiStatus');

        function setAiStatus(message, type) {
            aiStatus.textContent = message;
            aiStatus.className = 'ai-status' + (type ? ' ' + type : '');
        }

        if (openAiPanelBtn) {
            openAiPanelBtn.addEventListener('click', function() {
                aiPanel.classList.add('is-open');
                setAiStatus('Answer the questions and generate your draft.', '');
            });
        }

        if (closeAiPanelBtn) {
            closeAiPanelBtn.addEventListener('click', function() {
                aiPanel.classList.remove('is-open');
            });
        }

        if (generateAiProfileBtn) {
            generateAiProfileBtn.addEventListener('click', function() {
                const formData = new FormData();
                formData.append('action', 'generate_ai_profile');
                formData.append('ai_services', document.getElementById('ai_services').value.trim());
                formData.append('ai_skills', document.getElementById('ai_skills').value.trim());
                formData.append('ai_experience_years', document.getElementById('ai_experience_years').value.trim());
                formData.append('ai_projects', document.getElementById('ai_projects').value.trim());
                formData.append('ai_strengths', document.getElementById('ai_strengths').value.trim());
                formData.append('ai_tone', document.getElementById('ai_tone').value);

                generateAiProfileBtn.disabled = true;
                setAiStatus('Generating your profile draft...', '');

                fetch('?action=profile', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (!data.success) {
                        setAiStatus(data.message || 'The AI draft could not be generated.', 'error');
                        return;
                    }

                    document.getElementById('skill_summary').value = data.data.skill_summary || '';
                    document.getElementById('bio').value = data.data.bio || '';
                    document.getElementById('experience_description').value = data.data.experience_description || '';
                    setAiStatus('Draft generated. Review the text, adjust anything you want, then click Save Changes.', 'success');
                })
                .catch(function() {
                    setAiStatus('A network or server error occurred while contacting the AI service.', 'error');
                })
                .finally(function() {
                    generateAiProfileBtn.disabled = false;
                });
            });
        }
    </script>
    <?php endif; ?>
</body>
</html>
