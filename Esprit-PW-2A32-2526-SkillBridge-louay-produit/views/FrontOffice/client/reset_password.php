<?php
/**
 * reset_password.php — SkillBridge
 * Page de réinitialisation de mot de passe
 */
require_once __DIR__ . '/../../../controllers/AuthController.php';
$csrfToken = AuthController::generateCsrfToken();

$forgotSuccess = $_SESSION['forgot_success'] ?? '';
$forgotError = $_SESSION['forgot_error'] ?? '';
$resetError = $_SESSION['reset_error'] ?? '';
$token = $_SESSION['reset_token_display'] ?? ($_GET['token'] ?? '');
$email = $_SESSION['reset_email_display'] ?? ($_GET['email'] ?? '');

// Clear flash
unset($_SESSION['forgot_success'], $_SESSION['forgot_error'], $_SESSION['reset_error'],
      $_SESSION['reset_token_display'], $_SESSION['reset_email_display']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Réinitialiser le mot de passe — SkillBridge</title>
  <link rel="stylesheet" href="views/assets/css/front.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(145deg, #faf6f0 0%, #f5efe6 50%, #faf6f0 100%);
      font-family: 'DM Sans', sans-serif;
      padding: 2rem;
    }
    .reset-card {
      background: linear-gradient(180deg, rgba(255,255,255,.92), #fffdf9);
      border: 1px solid rgba(224,112,32,.1);
      border-radius: 24px;
      padding: 2.5rem;
      max-width: 460px;
      width: 100%;
      box-shadow: 0 20px 50px rgba(30,30,32,.06);
    }
    .reset-card h1 {
      font-family: 'Playfair Display', serif;
      font-size: 1.6rem;
      color: #1e1e20;
      margin-bottom: 0.3rem;
    }
    .reset-card .subtitle {
      color: #8a8a8e;
      font-size: 0.88rem;
      margin-bottom: 1.5rem;
    }
    .reset-field {
      margin-bottom: 1.2rem;
    }
    .reset-field label {
      display: block;
      font-weight: 600;
      font-size: 0.82rem;
      color: #1e1e20;
      margin-bottom: 6px;
    }
    .reset-input-wrap {
      position: relative;
      display: flex;
      align-items: center;
    }
    .reset-input-wrap i {
      position: absolute;
      left: 14px;
      color: #b0b0b4;
      font-size: 0.85rem;
    }
    .reset-input-wrap input {
      width: 100%;
      padding: 12px 14px 12px 42px;
      border: 1px solid rgba(224,112,32,.15);
      border-radius: 14px;
      font-size: 0.9rem;
      font-family: inherit;
      background: #fff;
      color: #1e1e20;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .reset-input-wrap input:focus {
      border-color: #e07020;
      box-shadow: 0 0 0 3px rgba(224,112,32,.1);
    }
    .reset-btn {
      width: 100%;
      padding: 13px;
      border: none;
      border-radius: 14px;
      background: linear-gradient(135deg, #e07020, #f08a3b);
      color: white;
      font-weight: 700;
      font-size: 0.92rem;
      cursor: pointer;
      font-family: inherit;
      transition: transform 0.2s, box-shadow 0.2s;
      box-shadow: 0 8px 24px rgba(224,112,32,.25);
    }
    .reset-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 30px rgba(224,112,32,.35);
    }
    .alert {
      padding: 12px 16px;
      border-radius: 12px;
      font-size: 0.85rem;
      margin-bottom: 1.2rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .alert-error {
      background: rgba(239,68,68,.08);
      border: 1px solid rgba(239,68,68,.2);
      color: #dc2626;
    }
    .alert-success {
      background: rgba(34,197,94,.08);
      border: 1px solid rgba(34,197,94,.2);
      color: #16a34a;
    }
    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #e07020;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.85rem;
      margin-top: 1.2rem;
      transition: opacity 0.2s;
    }
    .back-link:hover { opacity: 0.7; }
    .divider {
      border: none;
      border-top: 1px solid rgba(224,112,32,.1);
      margin: 1.5rem 0;
    }
    .step-indicator {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 1.5rem;
    }
    .step {
      width: 32px; height: 32px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.75rem; font-weight: 700;
    }
    .step-active {
      background: linear-gradient(135deg, #e07020, #f08a3b);
      color: white;
    }
    .step-done {
      background: rgba(34,197,94,.15);
      color: #16a34a;
    }
    .step-pending {
      background: rgba(0,0,0,.05);
      color: #b0b0b4;
    }
    .step-line {
      flex: 1;
      height: 2px;
      background: rgba(224,112,32,.15);
    }
  </style>
</head>
<body>

<div class="reset-card">
  <!-- Logo -->
  <div style="text-align:center; margin-bottom:1.5rem;">
    <div style="font-family:'Playfair Display',serif; font-size:1.3rem; font-weight:700; color:#1e1e20;">
      <i class="fas fa-bolt" style="color:#e07020;"></i> SkillBridge
    </div>
  </div>

  <?php if (!empty($token) && !empty($email) && empty($forgotError)): ?>
    <!-- ============ STEP 2: Reset Password Form ============ -->
    <div class="step-indicator">
      <div class="step step-done"><i class="fas fa-check"></i></div>
      <div class="step-line"></div>
      <div class="step step-active">2</div>
    </div>

    <h1><i class="fas fa-lock" style="color:#e07020; font-size:1.3rem;"></i> Nouveau mot de passe</h1>
    <p class="subtitle">Choisissez un nouveau mot de passe pour <strong><?= htmlspecialchars($email) ?></strong></p>

    <?php if ($resetError): ?>
      <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($resetError) ?></div>
    <?php endif; ?>

    <?php if ($forgotSuccess): ?>
      <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($forgotSuccess) ?></div>
    <?php endif; ?>

    <form action="index.php?page=reset_password" method="POST">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
      <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

      <div class="reset-field">
        <label for="newPass">Nouveau mot de passe</label>
        <div class="reset-input-wrap">
          <i class="fas fa-lock"></i>
          <input type="password" id="newPass" name="new_password" placeholder="Min. 6 caractères" required minlength="6">
        </div>
      </div>

      <div class="reset-field">
        <label for="confirmPass">Confirmer le mot de passe</label>
        <div class="reset-input-wrap">
          <i class="fas fa-lock"></i>
          <input type="password" id="confirmPass" name="confirm_password" placeholder="Retapez le mot de passe" required>
        </div>
      </div>

      <button type="submit" class="reset-btn">
        <i class="fas fa-key"></i> Réinitialiser le mot de passe
      </button>
    </form>

  <?php else: ?>
    <!-- ============ STEP 1: Request Reset ============ -->
    <div class="step-indicator">
      <div class="step step-active">1</div>
      <div class="step-line"></div>
      <div class="step step-pending">2</div>
    </div>

    <h1><i class="fas fa-envelope-open-text" style="color:#e07020; font-size:1.3rem;"></i> Mot de passe oublié</h1>
    <p class="subtitle">Entrez votre adresse email pour recevoir un lien de réinitialisation.</p>

    <?php if ($forgotError): ?>
      <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($forgotError) ?></div>
    <?php endif; ?>

    <form action="index.php?page=forgot_password" method="POST">
      <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
      
      <div class="reset-field">
        <label for="resetEmail">Adresse email</label>
        <div class="reset-input-wrap">
          <i class="fas fa-envelope"></i>
          <input type="email" id="resetEmail" name="email" placeholder="votre@email.com" required 
                 value="<?= htmlspecialchars($email) ?>">
        </div>
      </div>

      <button type="submit" class="reset-btn">
        <i class="fas fa-paper-plane"></i> Envoyer le lien
      </button>
    </form>
  <?php endif; ?>

  <hr class="divider">
  <a href="index.php?page=welcome" class="back-link">
    <i class="fas fa-arrow-left"></i> Retour à la connexion
  </a>
</div>

</body>
</html>
