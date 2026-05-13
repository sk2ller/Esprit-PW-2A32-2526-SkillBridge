<?php
if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 2) {
    header('Location: ?action=login'); exit;
}
require_once __DIR__ . '/../../Controllers/CandidatureController.php';

$cc        = new CandidatureController();
$id_tache  = (int)($_GET['id_tache'] ?? 0);
$id_client = $_SESSION['user_id'];

if (!$id_tache) { header('Location: ?action=projects&tab=mes'); exit; }

// Vérifier que la tâche appartient au client et est terminée
$db   = Config::getConnexion();
$q    = $db->prepare("SELECT t.*, p.titre AS titre_projet, u.nom AS nom_freelancer, u.prenom AS prenom_freelancer
    FROM tache t
    JOIN projet p ON p.id = t.id_projet
    JOIN user u ON u.id = t.id_freelancer
    WHERE t.id = :id AND p.id_client = :c");
$q->execute(['id' => $id_tache, 'c' => $id_client]);
$tache = $q->fetch();

if (!$tache || $tache['statut'] !== 'termine') {
    header('Location: ?action=projects&tab=mes'); exit;
}
if ($tache['payee']) {
    header('Location: ?action=projects&tab=mes'); exit;
}

// Traitement du paiement
$success = false;
$errors  = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_carte  = trim($_POST['nom_carte']  ?? '');
    $num_carte  = preg_replace('/\s+/', '', $_POST['num_carte'] ?? '');
    $expiration = trim($_POST['expiration'] ?? '');
    $cvv        = trim($_POST['cvv']        ?? '');

    if (!$nom_carte)                                    $errors[] = 'Le nom sur la carte est obligatoire.';
    if (!preg_match('/^\d{16}$/', $num_carte))          $errors[] = 'Numéro de carte invalide (16 chiffres).';
    if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiration)) $errors[] = 'Date d\'expiration invalide (MM/AA).';
    if (!preg_match('/^\d{3,4}$/', $cvv))               $errors[] = 'Cryptogramme invalide (3 ou 4 chiffres).';

    if (empty($errors)) {
        $result = $cc->payerTache($id_tache, $id_client);
        if ($result['success']) {
            $success = true;
        } else {
            $errors[] = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement sécurisé - SkillBridge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        /* ── Header ── */
        .pay-header {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.5rem;
        }
        .pay-logo {
            width: 40px; height: 40px;
            background: #1a1a2e;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 900; font-size: 1.1rem;
        }
        .pay-brand { font-size: 1.3rem; font-weight: 700; color: #1a1a2e; }

        /* ── Card ── */
        .pay-card {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }

        /* ── Résumé tâche ── */
        .pay-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #2563eb;
        }
        .pay-summary-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; font-weight: 600; margin-bottom: 0.3rem; }
        .pay-summary-projet { font-weight: 700; color: #1a1a2e; font-size: 0.95rem; }
        .pay-summary-tache { color: #4b5563; font-size: 0.88rem; margin-top: 0.15rem; }
        .pay-summary-montant { font-size: 1.4rem; font-weight: 800; color: #2563eb; margin-top: 0.5rem; }

        /* ── Titre ── */
        .pay-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .pay-secure { color: #27ae60; font-size: 1.2rem; }

        /* ── Logos cartes ── */
        .pay-cards {
            display: flex;
            gap: 0.4rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .pay-card-logo {
            height: 24px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            padding: 2px 6px;
            background: #fff;
            font-size: 0.65rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            letter-spacing: 0.02em;
        }
        .logo-mc  { color: #eb001b; }
        .logo-visa { color: #1a1f71; }
        .logo-amex { color: #007bc1; }

        /* ── Formulaire ── */
        .pay-group { margin-bottom: 1.2rem; }
        .pay-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.4rem;
        }
        .pay-group input {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 1.5px solid #d1d5db;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            color: #1a1a2e;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff;
        }
        .pay-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }
        .pay-group input.error { border-color: #ef4444; }
        .pay-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        /* ── Erreurs ── */
        .pay-errors {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.2rem;
            color: #dc2626;
            font-size: 0.85rem;
        }
        .pay-errors ul { padding-left: 1.2rem; }

        /* ── Bouton ── */
        .btn-pay {
            width: 100%;
            padding: 1rem;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: background 0.2s, transform 0.1s;
            margin-top: 0.5rem;
        }
        .btn-pay:hover { background: #1d4ed8; }
        .btn-pay:active { transform: scale(0.98); }

        /* ── Succès ── */
        .pay-success {
            text-align: center;
            padding: 1rem 0;
        }
        .pay-success-icon {
            width: 72px; height: 72px;
            background: #d1fae5;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.2rem;
        }
        .pay-success h2 { font-size: 1.4rem; color: #065f46; margin-bottom: 0.5rem; }
        .pay-success p  { color: #6b7280; font-size: 0.9rem; margin-bottom: 1.5rem; }
        .btn-retour {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #1a1a2e;
            color: #fff;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        .btn-retour:hover { background: #2563eb; }

        /* ── Footer ── */
        .pay-footer {
            margin-top: 1.2rem;
            text-align: center;
            font-size: 0.78rem;
            color: #9ca3af;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        @media (max-width: 480px) {
            .pay-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="pay-header">
    <div class="pay-logo">S</div>
    <span class="pay-brand">SkillBridge Pay</span>
</div>

<div class="pay-card">

    <?php if ($success): ?>
    <!-- ── Succès ── -->
    <div class="pay-success">
        <div class="pay-success-icon">✓</div>
        <h2>Paiement effectué !</h2>
        <p>
            La tâche <strong><?= htmlspecialchars($tache['titre']) ?></strong>
            a été payée avec succès.
            <?php if ((float)$tache['prix'] > 0): ?>
                <br>Montant : <strong><?= number_format((float)$tache['prix'], 2, ',', ' ') ?> TND</strong>
            <?php endif; ?>
        </p>
        <a href="?action=projects&tab=mes" class="btn-retour">← Retour à mes projets</a>
    </div>

    <?php else: ?>
    <!-- ── Résumé ── -->
    <div class="pay-summary">
        <div class="pay-summary-title">Détail du paiement</div>
        <div class="pay-summary-projet"><?= htmlspecialchars($tache['titre_projet']) ?></div>
        <div class="pay-summary-tache">Tâche : <?= htmlspecialchars($tache['titre']) ?> — <?= htmlspecialchars($tache['prenom_freelancer'].' '.$tache['nom_freelancer']) ?></div>
        <?php if ((float)$tache['prix'] > 0): ?>
            <div class="pay-summary-montant"><?= number_format((float)$tache['prix'], 2, ',', ' ') ?> TND</div>
        <?php endif; ?>
    </div>

    <!-- ── Titre ── -->
    <div class="pay-title">
        Payez en ligne
        <span class="pay-secure" title="Paiement sécurisé">🛡</span>
    </div>

    <!-- ── Logos cartes ── -->
    <div class="pay-cards">
        <span class="pay-card-logo logo-mc">●● MASTERCARD</span>
        <span class="pay-card-logo logo-visa">VISA</span>
        <span class="pay-card-logo logo-amex">AMEX</span>
        <span class="pay-card-logo" style="color:#4b5563;">DISCOVER</span>
    </div>

    <!-- ── Erreurs ── -->
    <?php if (!empty($errors)): ?>
    <div class="pay-errors">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- ── Formulaire ── -->
    <form method="POST" novalidate>
        <div class="pay-group">
            <label>Nom sur la carte</label>
            <input type="text" name="nom_carte" placeholder="Nom Prénom"
                value="<?= htmlspecialchars($_POST['nom_carte'] ?? '') ?>"
                autocomplete="cc-name">
        </div>
        <div class="pay-group">
            <label>N° de carte</label>
            <input type="text" name="num_carte" placeholder="•••• •••• •••• ••••"
                maxlength="19" autocomplete="cc-number"
                oninput="formatCard(this)">
        </div>
        <div class="pay-row">
            <div class="pay-group">
                <label>Date d'expiration</label>
                <input type="text" name="expiration" placeholder="MM/AA"
                    maxlength="5" autocomplete="cc-exp"
                    oninput="formatExp(this)">
            </div>
            <div class="pay-group">
                <label>Cryptogramme visuel</label>
                <input type="password" name="cvv" placeholder="•••"
                    maxlength="4" autocomplete="cc-csc">
            </div>
        </div>

        <button type="submit" class="btn-pay">
            🔒 Payer <?php if ((float)$tache['prix'] > 0): ?><?= number_format((float)$tache['prix'], 2, ',', ' ') ?> TND<?php endif; ?>
        </button>
    </form>
    <?php endif; ?>

</div>

<div class="pay-footer">
    🔒 Paiement sécurisé SSL &nbsp;·&nbsp; SkillBridge © 2026
</div>

<script>
function formatCard(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = v.replace(/(.{4})/g, '$1 ').trim();
}
function formatExp(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 4);
    if (v.length >= 3) v = v.substring(0,2) + '/' + v.substring(2);
    input.value = v;
}
</script>
</body>
</html>
