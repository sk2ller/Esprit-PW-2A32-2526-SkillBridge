<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ?action=login');
    exit;
}
require_once __DIR__ . '/../../Controllers/UserController.php';

$userController = new UserController();
$id   = (int)($_GET['id'] ?? 0);
$user = $userController->getUserById($id);

if (!$user) {
    header('Location: /index.php?page=admin_users');
    exit;
}

$error   = "";
$success = "";

function sanitizeInput(array $data): array {
    $filtered = filter_var_array($data, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    return $filtered ? array_map('trim', $filtered) : [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyer les données d'entrée
    $data = sanitizeInput($_POST);

    $nom    = $data['nom']    ?? '';
    $prenom = $data['prenom'] ?? '';
    $email  = $data['email']  ?? '';
    $niveau = $data['niveau']      ?? 'débutant';
    $role   = (int)($data['id_role'] ?? 2);
    $newPass = $_POST['new_password'] ?? ''; // Ne pas nettoyer le mot de passe

    // Validation des données
    if (!$nom || !$prenom || !$email) {
        $error = "Nom, prénom et email sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } elseif (!empty($newPass) && strlen($newPass) < 8) {
        $error = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
    } else {
        // Vérifier si l'email existe déjà (en excluant l'utilisateur actuel)
        if ($userController->emailExists($email, $id)) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setNiveau($niveau);
            $user->setIdRole($role);
            $userController->updateUser($user);

            // Optional new password
            if (!empty($newPass)) {
                $userController->updatePassword($id, $newPass);
            }

            header('Location: /index.php?page=admin_users&msg=updated');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Modifier Utilisateur — Admin SkillBridge</title>
    <script src="/Views/assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: { families: ["Font Awesome 5 Solid","Font Awesome 5 Regular","Font Awesome 5 Brands","simple-line-icons"], urls: ["/Views/assets/css/fonts.min.css"] },
            active: function() { sessionStorage.fonts = true; }
        });
    </script>
    <link rel="stylesheet" href="/Views/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/Views/assets/css/plugins.min.css">
    <link rel="stylesheet" href="/Views/assets/css/kaiadmin.min.css">
</head>
<body>
<div class="wrapper">

    <!-- Sidebar -->
    <div class="sidebar" data-background-color="dark">
        <div class="sidebar-logo">
            <div class="logo-header" data-background-color="dark">
                <a href="/index.php" class="logo">
                    <img src="/Views/assets/img/logo1.png" alt="SkillBridge" style="height: 30px; width: auto;">
                </a>
                <div class="nav-toggle">
                    <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                    <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
                </div>
                <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
            </div>
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
            <div class="sidebar-content">
                <ul class="nav nav-secondary">
                    <li class="nav-section"><h4 class="text-section">Principal</h4></li>
                    <li class="nav-item active">
                        <a href="/index.php?page=admin_users"><i class="fas fa-users"></i><p>Utilisateurs</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/index.php?page=admin_services"><i class="fas fa-briefcase"></i><p>Services</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/index.php?page=admin_orders"><i class="fas fa-shopping-bag"></i><p>Commandes</p></a>
                    </li>
                    <li class="nav-section"><h4 class="text-section">Compte</h4></li>
                    <li class="nav-item">
                        <a href="/index.php?page=logout"><i class="fas fa-sign-out-alt"></i><p>Déconnexion</p></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="main-panel">
        <div class="main-header">
            <div class="main-header-logo">
                <div class="logo-header" data-background-color="dark">
                    <span class="text-white fw-bold">SkillBridge</span>
                    <div class="nav-toggle">
                        <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                        <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
                    </div>
                    <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
                </div>
            </div>
            <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
                <div class="container-fluid"></div>
            </nav>
        </div>

        <div class="container">
            <div class="page-inner">

                <div class="pt-2 pb-4">
                    <h3 class="fw-bold mb-3">Modifier l'utilisateur</h3>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-7">

                        <div class="d-flex align-items-center mb-4">
                            <h5 class="mb-0">
                                <i class="fas fa-user-edit me-2"></i>
                                <?= htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()) ?>
                            </h5>
                            <a href="/index.php?page=admin_users" class="btn btn-outline-secondary btn-sm ms-auto">
                                <i class="fas fa-list me-1"></i>Liste
                            </a>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <div class="card card-round border">
                            <div class="card-body p-4">
                                <form method="POST" action="">
                                    <div class="row g-3">

                                        <div class="col-md-6">
                                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                                            <input type="text" name="nom" class="form-control"
                                                   value="<?= htmlspecialchars($_POST['nom'] ?? $user->getNom()) ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                            <input type="text" name="prenom" class="form-control"
                                                   value="<?= htmlspecialchars($_POST['prenom'] ?? $user->getPrenom()) ?>" required>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control"
                                                   value="<?= htmlspecialchars($_POST['email'] ?? $user->getEmail()) ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Rôle</label>
                                            <select name="id_role" class="form-select">
                                                <?php
                                                    $roles = [1 => 'Administrateur', 2 => 'Client', 3 => 'Freelance'];
                                                    $current = $_POST['id_role'] ?? $user->getIdRole();
                                                    foreach ($roles as $k => $v):
                                                ?>
                                                    <option value="<?= $k ?>" <?= $current == $k ? 'selected' : '' ?>><?= $v ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Niveau</label>
                                            <select name="niveau" class="form-select">
                                                <?php foreach (['débutant','intermédiaire','avancé','expert'] as $lvl): ?>
                                                    <option value="<?= $lvl ?>"
                                                        <?= (($_POST['niveau'] ?? $user->getNiveau()) === $lvl) ? 'selected' : '' ?>>
                                                        <?= ucfirst($lvl) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <hr>
                                            <label class="form-label text-muted">
                                                Nouveau mot de passe <small>(laisser vide pour ne pas changer)</small>
                                            </label>
                                            <input type="password" name="new_password" class="form-control"
                                                   placeholder="Minimum 8 caractères">
                                        </div>

                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fas fa-save me-1"></i>Enregistrer
                                        </button>
                                        <a href="/index.php?page=admin_users" class="btn btn-secondary px-4 ms-2">Annuler</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <footer class="footer">
            <div class="container-fluid">
                <div class="copyright">SkillBridge Admin — 2025</div>
            </div>
        </footer>
    </div>
</div>

<script src="/Views/assets/js/core/jquery-3.7.1.min.js"></script>
<script src="/Views/assets/js/core/popper.min.js"></script>
<script src="/Views/assets/js/core/bootstrap.min.js"></script>
<script src="/Views/assets/js/kaiadmin.min.js"></script>
</body>
</html>
