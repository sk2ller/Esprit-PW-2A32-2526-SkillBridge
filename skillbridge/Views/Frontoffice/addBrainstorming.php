<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ?action=login');
    exit;
}
require_once __DIR__ . '/../../Controllers/BrainstormingController.php';

$brainstormController = new BrainstormingController();
$error = '';
$success = '';
$validationErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Préparer les données pour le controller
    $data = [
        'titre' => $_POST['titre'] ?? '',
        'description' => $_POST['description'] ?? '',
        'date_debut' => $_POST['date_debut'] ?? '',
        'user_id' => $_SESSION['user_id']
    ];

    // Utiliser le controller pour créer le brainstorming avec validation
    $result = $brainstormController->createBrainstorming($data);

    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
        $validationErrors = $result['errors'] ?? [];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Brainstorming — SkillBridge</title>
    <link rel="stylesheet" href="/Views/assets/css/skillbridge.css">
    <script src="/Views/assets/js/brainstorming-validation.js"></script>
</head>
<body>
<nav class="navbar-top">
    <div class="container">
        <a href="?action=home" class="logo"><img src="/Views/assets/img/logo1.png" alt="SkillBridge" style="height: 50px; width: auto;"></a>
        <div class="nav-buttons">
            <span>Bonjour, <?= htmlspecialchars($_SESSION['user_prenom']) ?></span>
            <a href="?action=brainstorming_list" class="btn btn-secondary">Liste des Brainstormings</a>
            <a href="?action=profile" class="btn btn-secondary">Mon Profil</a>
            <a href="?action=logout" class="btn btn-logout">Déconnexion</a>
        </div>
    </div>
</nav>
<main class="page-content" style="padding: 4rem 1.5rem;">
    <div class="container" style="max-width: 760px;">
        <h1>Ajouter un nouveau brainstorming</h1>
        <p>Soumettez vos idées avec un titre, une description et une date de début.</p>
        <div style="margin-bottom: 1rem;">
            <a href="?action=brainstorming_list" class="btn btn-outline-secondary">Voir la liste</a>
            <?php if ($_SESSION['user_role'] == 1): ?>
                <a href="?action=brainstorming_admin" class="btn btn-outline-warning">Admin Panel</a>
            <?php endif; ?>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
                <?php if (!empty($validationErrors)): ?>
                    <ul>
                        <?php foreach ($validationErrors as $field => $message): ?>
                            <li><?= htmlspecialchars(is_int($field) ? $message : "$field : $message") ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="card" style="padding: 2rem;">
            <form id="brainstormingForm" method="POST" action="?action=brainstorming_add" onsubmit="return validateForm()">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="titre">Titre <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        id="titre"
                        name="titre" 
                        class="form-control" 
                        value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" 
                        minlength="5"
                        maxlength="100"
                        pattern="[a-zA-Z0-9À-ÿ\s\-\.,!?\']+"
                        required
                        onblur="validateField('titre')"
                        oninput="clearError('titre')">
                    <div class="text-danger small mt-1" id="titre-error" style="display: none;"></div>
                    <div class="text-muted small mt-1">Minimum 5 caractères, maximum 100 caractères</div>
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="description">Description <span class="text-danger">*</span></label>
                    <textarea 
                        id="description"
                        name="description" 
                        class="form-control" 
                        rows="5" 
                        minlength="20"
                        maxlength="2000"
                        required
                        onblur="validateField('description')"
                        oninput="clearError('description')"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <div class="text-danger small mt-1" id="description-error" style="display: none;"></div>
                    <div class="text-muted small mt-1">Minimum 20 caractères, maximum 2000 caractères</div>
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="date_debut">Date de début <span class="text-danger">*</span></label>
                    <input 
                        type="date" 
                        id="date_debut"
                        name="date_debut" 
                        class="form-control" 
                        value="<?= htmlspecialchars($_POST['date_debut'] ?? '') ?>" 
                        required
                        onblur="validateField('date_debut')"
                        oninput="clearError('date_debut')">
                    <div class="text-danger small mt-1" id="date_debut-error" style="display: none;"></div>
                    <div class="text-muted small mt-1">La date doit être aujourd'hui ou dans le futur</div>
                </div>
                <button type="submit" class="btn btn-primary">Soumettre le brainstorming</button>
            </form>
        </div>

        <script>
            function validateField(fieldName) {
                const field = document.getElementById(fieldName);
                const errorDiv = document.getElementById(fieldName + '-error');
                let error = '';

                if (fieldName === 'titre') {
                    const titre = field.value.trim();
                    if (!titre) {
                        error = 'Le titre est obligatoire.';
                    } else if (titre.length < 5) {
                        error = 'Le titre doit contenir au moins 5 caractères.';
                    } else if (titre.length > 100) {
                        error = 'Le titre ne peut pas dépasser 100 caractères.';
                    } else if (!/^[a-zA-Z0-9À-ÿ\s\-\.,!?\'"]+$/.test(titre)) {
                        error = 'Le titre contient des caractères non autorisés.';
                    }
                } else if (fieldName === 'description') {
                    const description = field.value.trim();
                    if (!description) {
                        error = 'La description est obligatoire.';
                    } else if (description.length < 20) {
                        error = 'La description doit contenir au moins 20 caractères.';
                    } else if (description.length > 2000) {
                        error = 'La description ne peut pas dépasser 2000 caractères.';
                    }
                } else if (fieldName === 'date_debut') {
                    const date = field.value;
                    if (!date) {
                        error = 'La date est obligatoire.';
                    } else {
                        const selectedDate = new Date(date);
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        if (selectedDate < today) {
                            error = 'La date ne peut pas être dans le passé.';
                        } else {
                            const maxDate = new Date();
                            maxDate.setFullYear(maxDate.getFullYear() + 1);
                            if (selectedDate > maxDate) {
                                error = 'La date ne peut pas dépasser un an.';
                            }
                        }
                    }
                }

                if (error) {
                    errorDiv.textContent = error;
                    errorDiv.style.display = 'block';
                    field.classList.add('is-invalid');
                    return false;
                } else {
                    errorDiv.style.display = 'none';
                    field.classList.remove('is-invalid');
                    return true;
                }
            }

            function clearError(fieldName) {
                const errorDiv = document.getElementById(fieldName + '-error');
                errorDiv.style.display = 'none';
                document.getElementById(fieldName).classList.remove('is-invalid');
            }

            function validateForm() {
                const fields = ['titre', 'description', 'date_debut'];
                let isValid = true;

                fields.forEach(field => {
                    if (!validateField(field)) {
                        isValid = false;
                    }
                });

                return isValid;
            }
        </script>
    </div>
</main>
<footer class="footer">
    <p>&copy; 2026 SkillBridge</p>
</footer>
</body>
</html>
