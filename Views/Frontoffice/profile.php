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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
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
        }
    </style>
</head>
<body>
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
</body>
</html>
