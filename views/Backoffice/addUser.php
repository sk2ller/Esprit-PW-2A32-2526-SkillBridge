<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ?action=login');
    exit;
}
require_once __DIR__ . '/../../Controllers/UserController.php';

$userController = new UserController();
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom']    ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email']  ?? '');
    $pass   = $_POST['password']    ?? '';
    $niveau = $_POST['niveau']      ?? 'débutant';
    $role   = (int)($_POST['id_role'] ?? 2);

    if (!$nom || !$prenom || !$email || !$pass) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($userController->emailExists($email)) {
        $error = "Cet email est déjà utilisé.";
    } else {
        $user = new User($nom, $prenom, $email, $pass, $niveau, $role);
        $userController->addUser($user);
        header('Location: /index.php?page=admin_users&msg=added');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Ajouter Utilisateur — Admin SkillBridge</title>
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
                    <span class="text-white fw-bold fs-5">SkillBridge</span>
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
                    <span class="text-white fw-bold"><img src="/Views/assets/img/logo1.png" alt="SkillBridge" style="height: 30px; width: auto;">
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
                    <h3 class="fw-bold mb-3">Ajouter un utilisateur</h3>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-7">

                        <div class="d-flex align-items-center mb-4">
                            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Nouvel utilisateur</h5>
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
                                                   placeholder="Dupont"
                                                   value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                            <input type="text" name="prenom" class="form-control"
                                                   placeholder="Jean"
                                                   value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control"
                                                   placeholder="jean@exemple.com"
                                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                            <input type="password" name="password" class="form-control"
                                                   placeholder="Min. 8 caractères" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Rôle</label>
                                            <select name="id_role" class="form-select">
                                                <option value="2" <?= (($_POST['id_role'] ?? 2) == 2) ? 'selected' : '' ?>>Client</option>
                                                <option value="3" <?= (($_POST['id_role'] ?? '') == 3) ? 'selected' : '' ?>>Freelance</option>
                                                <option value="1" <?= (($_POST['id_role'] ?? '') == 1) ? 'selected' : '' ?>>Administrateur</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Niveau</label>
                                            <select name="niveau" class="form-select">
                                                <?php foreach (['débutant','intermédiaire','avancé','expert'] as $lvl): ?>
                                                    <option value="<?= $lvl ?>"
                                                        <?= (($_POST['niveau'] ?? 'débutant') === $lvl) ? 'selected' : '' ?>>
                                                        <?= ucfirst($lvl) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fas fa-plus me-1"></i>Ajouter
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
