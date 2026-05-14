<?php
/**
 * SkillBridge — Welcome & Authentication Page
 * Entry point for unauthenticated users
 * Contains: Hero section, role cards, auth modals (login/register)
 */

// Retrieve any flash messages set by AuthController
$authErrors = $_SESSION['auth_errors'] ?? [];
$authSuccess = $_SESSION['auth_success'] ?? '';
$authRole = $_SESSION['auth_role'] ?? '';
$authTab = $_SESSION['auth_tab'] ?? 'login';
$oldInput = $_SESSION['old_input'] ?? [];

// Clear flash data after reading
unset($_SESSION['auth_errors'], $_SESSION['auth_success'], $_SESSION['auth_role'], $_SESSION['auth_tab'], $_SESSION['old_input']);

// Generate CSRF token
require_once __DIR__ . '/../controllers/AuthController.php';
$csrfToken = AuthController::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bienvenue — SkillBridge</title>
  <meta name="description" content="SkillBridge — La marketplace de produits numériques. Achetez ou vendez des templates, e-books, plugins et formations en ligne.">
  <link rel="stylesheet" href="views/assets/css/front.css">
  <link rel="stylesheet" href="views/assets/css/welcome.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="welcome-wrapper">
  <!-- Animated background orbs -->
  <div class="welcome-bg-orb welcome-bg-orb--1"></div>
  <div class="welcome-bg-orb welcome-bg-orb--2"></div>
  <div class="welcome-bg-orb welcome-bg-orb--3"></div>

  <!-- ========== NAVBAR ========== -->
  <nav class="welcome-nav">
    <a href="index.php?page=welcome" class="welcome-nav-brand">
      <img src="logo.png" alt="SkillBridge Logo">
      <span class="welcome-nav-brand-text">Skill<span>Bridge</span></span>
    </a>
  </nav>

  <!-- ========== HERO SECTION ========== -->
  <main class="welcome-hero">
    <div class="welcome-hero-badge">
      <i class="fas fa-rocket"></i> Marketplace de Produits Numériques
    </div>

    <h1 class="welcome-hero-title">
      Bienvenue sur <span class="highlight">SkillBridge</span>
    </h1>

    <p class="welcome-hero-subtitle">
      La plateforme qui connecte les créateurs talentueux avec les clients exigeants.
      Templates, plugins, formations — tout pour réussir en ligne.
    </p>

    <!-- ========== ROLE SELECTION CARDS ========== -->
    <div class="welcome-roles">
      <!-- Client Card -->
      <div class="role-card role-card--client" onclick="openAuthModal('client')" id="roleCardClient">
        <div class="role-card-icon">
          <i class="fas fa-shopping-bag"></i>
        </div>
        <h2 class="role-card-title">Client</h2>
        <p class="role-card-desc">
          Découvrez et achetez des produits numériques de qualité pour vos projets.
        </p>
        <button class="role-card-btn" type="button">
          <i class="fas fa-arrow-right"></i> Commencer
        </button>
      </div>

      <!-- Freelancer Card -->
      <div class="role-card role-card--freelancer" onclick="openAuthModal('freelancer')" id="roleCardFreelancer">
        <div class="role-card-icon">
          <i class="fas fa-palette"></i>
        </div>
        <h2 class="role-card-title">Freelancer</h2>
        <p class="role-card-desc">
          Vendez vos créations numériques et développez votre activité freelance.
        </p>
        <button class="role-card-btn" type="button">
          <i class="fas fa-arrow-right"></i> Commencer
        </button>
      </div>
    </div>

    <!-- Feature highlights -->
    <div class="welcome-features">
      <div class="welcome-feature">
        <span class="welcome-feature-icon welcome-feature-icon--green"><i class="fas fa-check"></i></span>
        Produits Vérifiés
      </div>
      <div class="welcome-feature">
        <span class="welcome-feature-icon welcome-feature-icon--orange"><i class="fas fa-bolt"></i></span>
        Téléchargement Instant
      </div>
      <div class="welcome-feature">
        <span class="welcome-feature-icon welcome-feature-icon--blue"><i class="fas fa-shield-halved"></i></span>
        Paiement Sécurisé
      </div>
    </div>
  </main>

  <!-- ========== FOOTER ========== -->
  <footer class="welcome-footer">
    <div class="welcome-footer-left">
      © <?= date('Y') ?> SkillBridge. Tous droits réservés.
    </div>
    <div class="welcome-footer-right">
      <a href="#" class="welcome-footer-link">Conditions</a>
      <a href="#" class="welcome-footer-link">Confidentialité</a>
      <!-- Discreet admin access -->
      <a href="javascript:void(0)" onclick="openAuthModal('admin')" class="admin-access-link" title="Accès administrateur">
        <i class="fas fa-lock"></i> Admin
      </a>
    </div>
  </footer>
</div>

<!-- ============================================================ -->
<!-- AUTH MODAL OVERLAY -->
<!-- ============================================================ -->
<div class="auth-overlay" id="authOverlay">
  <div class="auth-modal" id="authModal">

    <!-- Modal Header -->
    <div class="auth-modal-header">
      <span class="auth-modal-role-badge" id="authRoleBadge">👤 Client</span>
      <button class="auth-modal-close" onclick="closeAuthModal()" type="button" aria-label="Fermer">
        <i class="fas fa-xmark"></i>
      </button>
    </div>

    <!-- Tabs (Login / Register / Face Auth) — hidden for admin -->
    <div class="auth-tabs" id="authTabs">
      <button class="auth-tab active" id="loginTab" type="button">Connexion</button>
      <button class="auth-tab" id="registerTab" type="button">Inscription</button>
      <button class="auth-tab" id="faceAuthTab" type="button"><i class="fas fa-camera"></i> Face ID</button>
    </div>

    <div class="auth-form-wrapper">

      <!-- ============================== -->
      <!-- CLIENT LOGIN FORM -->
      <!-- ============================== -->
      <div class="auth-form" id="loginFormClient">
        <h3 class="auth-form-title">Connexion Client</h3>
        <p class="auth-form-subtitle">Accédez à votre espace d'achat</p>

        <?php if (!empty($authErrors) && $authRole === 'client' && $authTab === 'login'): ?>
        <div class="auth-alert auth-alert--error">
          <i class="fas fa-circle-exclamation"></i>
          <div><?php foreach ($authErrors as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
        </div>
        <?php endif; ?>

        <form action="index.php?page=login" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
          <input type="hidden" name="role" value="client">

          <div class="auth-field">
            <label class="auth-label" for="clientLoginEmail">Email</label>
            <div class="auth-input-wrap">
              <i class="fas fa-envelope auth-input-icon"></i>
              <input type="email" class="auth-input" id="clientLoginEmail" name="email"
                     placeholder="votre@email.com" data-validate="required|email"
                     value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>">
            </div>
            <div class="auth-field-error"></div>
          </div>

          <div class="auth-field">
            <label class="auth-label" for="clientLoginPass">Mot de passe</label>
            <div class="auth-input-wrap">
              <i class="fas fa-lock auth-input-icon"></i>
              <input type="password" class="auth-input" id="clientLoginPass" name="password"
                     placeholder="••••••••" data-validate="required">
              <button type="button" class="auth-input-toggle"><i class="fas fa-eye"></i></button>
            </div>
            <div class="auth-field-error"></div>
          </div>

          <div class="auth-options">
            <label class="auth-remember">
              <input type="checkbox" name="remember"> Se souvenir de moi
            </label>
            <a href="index.php?page=reset_password_form" class="auth-forgot">Mot de passe oublié ?</a>
          </div>

          <button type="submit" class="auth-submit auth-submit--client">
            <span class="btn-text"><i class="fas fa-sign-in-alt"></i> Se connecter</span>
            <span class="spinner"></span>
          </button>
        </form>
      </div>

      <!-- ============================== -->
      <!-- CLIENT REGISTER FORM -->
      <!-- ============================== -->
      <div class="auth-form" id="registerFormClient">
        <h3 class="auth-form-title">Inscription Client</h3>
        <p class="auth-form-subtitle">Créez votre compte en quelques secondes</p>

        <?php if (!empty($authErrors) && $authRole === 'client' && $authTab === 'register'): ?>
        <div class="auth-alert auth-alert--error">
          <i class="fas fa-circle-exclamation"></i>
          <div><?php foreach ($authErrors as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
        </div>
        <?php endif; ?>

        <form action="index.php?page=register" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
          <input type="hidden" name="role" value="client">

          <div class="auth-field">
            <label class="auth-label" for="clientRegName">Nom complet</label>
            <div class="auth-input-wrap">
              <i class="fas fa-user auth-input-icon"></i>
              <input type="text" class="auth-input" id="clientRegName" name="name"
                     placeholder="Votre nom" data-validate="required|min:2"
                     value="<?= htmlspecialchars($oldInput['name'] ?? '') ?>">
            </div>
            <div class="auth-field-error"></div>
          </div>

          <div class="auth-field">
            <label class="auth-label" for="clientRegEmail">Email</label>
            <div class="auth-input-wrap">
              <i class="fas fa-envelope auth-input-icon"></i>
              <input type="email" class="auth-input" id="clientRegEmail" name="email"
                     placeholder="votre@email.com" data-validate="required|email"
                     value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>">
            </div>
            <div class="auth-field-error"></div>
          </div>

          <div class="auth-field">
            <label class="auth-label" for="clientRegPass">Mot de passe</label>
            <div class="auth-input-wrap">
              <i class="fas fa-lock auth-input-icon"></i>
              <input type="password" class="auth-input" id="clientRegPass" name="password"
                     placeholder="Min. 6 caractères" data-validate="required|min:6">
              <button type="button" class="auth-input-toggle"><i class="fas fa-eye"></i></button>
            </div>
            <div class="auth-field-error"></div>
          </div>

          <div class="auth-field">
            <label class="auth-label" for="clientRegPassConfirm">Confirmer le mot de passe</label>
            <div class="auth-input-wrap">
              <i class="fas fa-lock auth-input-icon"></i>
              <input type="password" class="auth-input" id="clientRegPassConfirm" name="confirm_password"
                     placeholder="Retapez le mot de passe" data-validate="required|match:clientRegPass">
              <button type="button" class="auth-input-toggle"><i class="fas fa-eye"></i></button>
            </div>
            <div class="auth-field-error"></div>
          </div>

          <button type="submit" class="auth-submit auth-submit--client">
            <span class="btn-text"><i class="fas fa-user-plus"></i> Créer mon compte</span>
            <span class="spinner"></span>
          </button>
        </form>
      </div>

      <!-- ============================== -->
      <!-- FREELANCER LOGIN FORM -->
      <!-- ============================== -->
      <div class="auth-form" id="loginFormFreelancer">
        <h3 class="auth-form-title">Connexion Freelancer</h3>
        <p class="auth-form-subtitle">Accédez à votre espace vendeur</p>

        <?php if (!empty($authErrors) && $authRole === 'freelancer' && $authTab === 'login'): ?>
        <div class="auth-alert auth-alert--error">
          <i class="fas fa-circle-exclamation"></i>
          <div><?php foreach ($authErrors as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
        </div>
        <?php endif; ?>

        <form action="index.php?page=login" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
          <input type="hidden" name="role" value="freelancer">

          <div class="auth-field">
            <label class="auth-label" for="freelancerLoginEmail">Email</label>
            <div class="auth-input-wrap">
              <i class="fas fa-envelope auth-input-icon"></i>
              <input type="email" class="auth-input" id="freelancerLoginEmail" name="email"
                     placeholder="votre@email.com" data-validate="required|email"
                     value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>">
            </div>
            <div class="auth-field-error"></div>
          </div>

          <div class="auth-field">
            <label class="auth-label" for="freelancerLoginPass">Mot de passe</label>
            <div class="auth-input-wrap">
              <i class="fas fa-lock auth-input-icon"></i>
              <input type="password" class="auth-input" id="freelancerLoginPass" name="password"
                     placeholder="••••••••" data-validate="required">
              <button type="button" class="auth-input-toggle"><i class="fas fa-eye"></i></button>
            </div>
            <div class="auth-field-error"></div>
          </div>

          <div class="auth-options">
            <label class="auth-remember">
              <input type="checkbox" name="remember"> Se souvenir de moi
            </label>
            <a href="index.php?page=reset_password_form" class="auth-forgot">Mot de passe oublié ?</a>
          </div>

          <button type="submit" class="auth-submit auth-submit--freelancer">
            <span class="btn-text"><i class="fas fa-sign-in-alt"></i> Se connecter</span>
            <span class="spinner"></span>
          </button>
        </form>
      </div>

      <!-- ============================== -->
      <!-- FREELANCER REGISTER FORM -->
      <!-- ============================== -->
      <div class="auth-form" id="registerFormFreelancer">
        <h3 class="auth-form-title">Inscription Freelancer</h3>
        <p class="auth-form-subtitle">Rejoignez la communauté de créateurs</p>

        <?php if (!empty($authErrors) && $authRole === 'freelancer' && $authTab === 'register'): ?>
        <div class="auth-alert auth-alert--error">
          <i class="fas fa-circle-exclamation"></i>
          <div><?php foreach ($authErrors as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
        </div>
        <?php endif; ?>

        <form action="index.php?page=register" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
          <input type="hidden" name="role" value="freelancer">

          <div class="auth-field">
            <label class="auth-label" for="freelancerRegName">Nom complet</label>
            <div class="auth-input-wrap">
              <i class="fas fa-user auth-input-icon"></i>
              <input type="text" class="auth-input" id="freelancerRegName" name="name"
                     placeholder="Votre nom" data-validate="required|min:2"
                     value="<?= htmlspecialchars($oldInput['name'] ?? '') ?>">
            </div>
            <div class="auth-field-error"></div>
          </div>

          <div class="auth-field">
            <label class="auth-label" for="freelancerRegEmail">Email</label>
            <div class="auth-input-wrap">
              <i class="fas fa-envelope auth-input-icon"></i>
              <input type="email" class="auth-input" id="freelancerRegEmail" name="email"
                     placeholder="votre@email.com" data-validate="required|email"
                     value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>">
            </div>
            <div class="auth-field-error"></div>
          </div>

          <div class="auth-field">
            <label class="auth-label" for="freelancerRegPass">Mot de passe</label>
            <div class="auth-input-wrap">
              <i class="fas fa-lock auth-input-icon"></i>
              <input type="password" class="auth-input" id="freelancerRegPass" name="password"
                     placeholder="Min. 6 caractères" data-validate="required|min:6">
              <button type="button" class="auth-input-toggle"><i class="fas fa-eye"></i></button>
            </div>
            <div class="auth-field-error"></div>
          </div>

          <div class="auth-field">
            <label class="auth-label" for="freelancerRegPassConfirm">Confirmer le mot de passe</label>
            <div class="auth-input-wrap">
              <i class="fas fa-lock auth-input-icon"></i>
              <input type="password" class="auth-input" id="freelancerRegPassConfirm" name="confirm_password"
                     placeholder="Retapez le mot de passe" data-validate="required|match:freelancerRegPass">
              <button type="button" class="auth-input-toggle"><i class="fas fa-eye"></i></button>
            </div>
            <div class="auth-field-error"></div>
          </div>

          <div class="auth-field">
            <label class="auth-label">Compétences</label>
            <div class="tags-input-wrapper" id="skillsTagsWrapper">
              <input type="text" class="tags-input-field" id="skillsTagsField"
                     placeholder="Tapez une compétence + Entrée" data-validate="required">
            </div>
            <input type="hidden" name="skills" id="skillsHidden"
                   value="<?= htmlspecialchars($oldInput['skills'] ?? '') ?>">
            <div class="tags-hint">Appuyez sur Entrée ou virgule pour ajouter</div>
            <div class="auth-field-error"></div>
          </div>

          <div class="auth-field">
            <label class="auth-label" for="freelancerRegBio">Bio courte</label>
            <textarea class="auth-textarea" id="freelancerRegBio" name="bio"
                      placeholder="Décrivez-vous en quelques lignes..."
                      data-validate="required|min:10"><?= htmlspecialchars($oldInput['bio'] ?? '') ?></textarea>
            <div class="auth-field-error"></div>
          </div>

          <div class="auth-field">
            <label class="auth-label" for="freelancerRegPortfolio">Portfolio URL <span style="color:var(--text-muted);font-weight:400">(optionnel)</span></label>
            <div class="auth-input-wrap">
              <i class="fas fa-globe auth-input-icon"></i>
              <input type="url" class="auth-input" id="freelancerRegPortfolio" name="portfolio_url"
                     placeholder="https://votre-portfolio.com" data-validate="url"
                     value="<?= htmlspecialchars($oldInput['portfolio_url'] ?? '') ?>">
            </div>
            <div class="auth-field-error"></div>
          </div>

          <button type="submit" class="auth-submit auth-submit--freelancer">
            <span class="btn-text"><i class="fas fa-user-plus"></i> Créer mon compte</span>
            <span class="spinner"></span>
          </button>
        </form>
      </div>

      <!-- ============================== -->
      <!-- ADMIN LOGIN FORM -->
      <!-- ============================== -->
      <div class="auth-form" id="loginFormAdmin">
        <h3 class="auth-form-title">Accès Administrateur</h3>
        <p class="auth-form-subtitle">Espace réservé au personnel autorisé</p>

        <?php if (!empty($authErrors) && $authRole === 'admin'): ?>
        <div class="auth-alert auth-alert--error">
          <i class="fas fa-circle-exclamation"></i>
          <div><?php foreach ($authErrors as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
        </div>
        <?php endif; ?>

        <form action="index.php?page=login" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
          <input type="hidden" name="role" value="admin">

          <div class="auth-field">
            <label class="auth-label" for="adminLoginEmail">Email administrateur</label>
            <div class="auth-input-wrap">
              <i class="fas fa-envelope auth-input-icon"></i>
              <input type="email" class="auth-input" id="adminLoginEmail" name="email"
                     placeholder="admin@skillbridge.com" data-validate="required|email"
                     value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>">
            </div>
            <div class="auth-field-error"></div>
          </div>

          <div class="auth-field">
            <label class="auth-label" for="adminLoginPass">Mot de passe</label>
            <div class="auth-input-wrap">
              <i class="fas fa-lock auth-input-icon"></i>
              <input type="password" class="auth-input" id="adminLoginPass" name="password"
                     placeholder="••••••••" data-validate="required">
              <button type="button" class="auth-input-toggle"><i class="fas fa-eye"></i></button>
            </div>
            <div class="auth-field-error"></div>
          </div>

          <button type="submit" class="auth-submit auth-submit--admin">
            <span class="btn-text"><i class="fas fa-shield-halved"></i> Connexion Admin</span>
            <span class="spinner"></span>
          </button>
        </form>
      </div>

      <!-- ============================== -->
      <!-- FACE AUTH FORM -->
      <!-- ============================== -->
      <div class="auth-form" id="faceAuthForm">
        <h3 class="auth-form-title">Reconnaissance Faciale</h3>
        <p class="auth-form-subtitle">Connectez-vous ou enregistrez votre visage.</p>

        <div class="auth-field">
          <label class="auth-label" for="faceEmail">Votre Email</label>
          <div class="auth-input-wrap">
            <i class="fas fa-envelope auth-input-icon"></i>
            <input type="email" class="auth-input" id="faceEmail" placeholder="votre@email.com">
          </div>
        </div>

        <div class="auth-field" id="facePasswordGroup" style="display:none;">
          <label class="auth-label" for="facePassword">Mot de passe (pour l'enregistrement)</label>
          <div class="auth-input-wrap">
            <i class="fas fa-lock auth-input-icon"></i>
            <input type="password" class="auth-input" id="facePassword" placeholder="••••••••">
            <button type="button" class="auth-input-toggle"><i class="fas fa-eye"></i></button>
          </div>
        </div>

        <!-- Camera Area -->
        <div class="face-camera-container">
          <video id="faceVideo" width="320" height="240" autoplay muted playsinline></video>
          <canvas id="faceCanvas" width="320" height="240"></canvas>
          <div id="faceLoading" class="face-overlay-message">Chargement de la caméra...</div>
        </div>

        <div class="auth-alert" id="faceAlert" style="display:none;"></div>

        <div class="face-actions">
          <button type="button" class="auth-submit auth-submit--freelancer" id="btnFaceLogin" style="flex:1;">
            <i class="fas fa-unlock"></i> Se connecter
          </button>
          <button type="button" class="auth-submit auth-submit--client" id="btnFaceRegister" style="flex:1; background:#10b981;">
            <i class="fas fa-user-check"></i> Enregistrer
          </button>
        </div>
      </div>

    </div><!-- /.auth-form-wrapper -->
  </div><!-- /.auth-modal -->
</div><!-- /.auth-overlay -->

<!-- Welcome page JS -->
<script src="views/assets/js/face-api.min.js"></script>
<script src="views/assets/js/face-auth.js"></script>
<script src="views/assets/js/welcome.js"></script>

<?php
// Auto-open modal if redirected back with errors
if (!empty($authErrors) && $authRole):
?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    openAuthModal('<?= htmlspecialchars($authRole) ?>');
    <?php if ($authTab === 'register'): ?>
    document.getElementById('registerTab').click();
    <?php endif; ?>
  });
</script>
<?php endif; ?>

<?php if ($authSuccess): ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    openAuthModal('<?= htmlspecialchars($authRole) ?>');
  });
</script>
<?php endif; ?>

</body>
</html>
