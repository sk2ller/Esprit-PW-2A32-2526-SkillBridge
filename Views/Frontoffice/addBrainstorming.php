<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?action=login');
    exit;
}
require_once __DIR__ . '/../../Controllers/BrainstormingController.php';

$brainstormController = new BrainstormingController();
$error = '';
$success = '';
$validationErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'titre' => $_POST['titre'] ?? '',
        'description' => $_POST['description'] ?? '',
        'date_debut' => $_POST['date_debut'] ?? '',
        'user_id' => $_SESSION['user_id']
    ];

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
    <title>Ajouter Brainstorming - SkillBridge</title>
    <link rel="stylesheet" href="Views/assets/css/skillbridge.css">
    <link rel="stylesheet" href="Views/assets/css/enhanced-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
    <script src="Views/assets/js/brainstorming-validation.js"></script>
<style id="brainstorming-dashboard-fix">
        .front-page.brainstorming-dashboard {
            max-width: 1440px;
            width: 100%;
            padding: 1.6rem 2rem 2.4rem;
        }

        .brainstorming-dashboard > .dashboard-shell {
            display: grid !important;
            grid-template-columns: 250px minmax(0, 1fr) !important;
            gap: 1.5rem;
            align-items: start;
            width: 100%;
        }

        .brainstorming-sidebar {
            grid-column: 1;
            width: 250px;
            position: sticky;
            top: calc(var(--nav-height) + 1rem);
            align-self: start;
            background: #1f1f23;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 18px;
            box-shadow: 0 14px 34px rgba(31, 31, 35, .14);
            overflow: hidden;
        }

        .brainstorming-sidebar-head {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .brainstorming-sidebar-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: rgba(224, 112, 32, .18);
            color: #f08a3b;
        }

        .brainstorming-sidebar-title {
            color: #fff;
            font-weight: 800;
            line-height: 1.1;
        }

        .brainstorming-sidebar-subtitle {
            color: rgba(255, 255, 255, .58);
            font-size: .82rem;
            margin-top: .18rem;
        }

        .brainstorming-nav {
            display: grid;
            gap: .35rem;
            padding: .75rem;
        }

        .brainstorming-nav-link {
            min-height: 44px;
            display: grid;
            grid-template-columns: 22px minmax(0, 1fr);
            align-items: center;
            gap: .65rem;
            padding: .72rem .8rem;
            border-radius: 12px;
            color: rgba(255, 255, 255, .72);
            text-decoration: none;
            font-weight: 700;
            line-height: 1.2;
        }

        .brainstorming-nav-link:hover,
        .brainstorming-nav-link.active {
            background: #fff8ef;
            color: #1f1f23;
        }

        .brainstorming-nav-link i {
            color: inherit;
            text-align: center;
        }

        .brainstorming-dashboard .feature-main {
            grid-column: 2;
            min-width: 0;
            width: 100%;
        }

        .brainstorming-dashboard .page-shell {
            min-height: auto;
            width: 100%;
        }

        .brainstorming-dashboard .page-hero {
            border-radius: 22px;
            padding: 2rem;
            margin: 0 0 1.35rem;
            overflow: hidden;
        }

        .brainstorming-dashboard .page-section {
            padding: 0;
        }

        .brainstorming-dashboard .container {
            max-width: none !important;
            width: 100%;
            padding-left: 0;
            padding-right: 0;
        }

        .brainstorming-dashboard .section-toolbar {
            gap: 1rem;
            flex-wrap: wrap;
        }

        .brainstorming-dashboard .front-table-wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
        }

        .brainstorming-dashboard .front-table {
            width: 100%;
            min-width: 760px;
        }

        .brainstorming-dashboard .idea-grid {
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }

        .brainstorming-dashboard .idea-card,
        .brainstorming-dashboard .empty-state,
        .brainstorming-dashboard .front-table-wrap {
            min-width: 0;
        }

        @media (max-width: 980px) {
            .front-page.brainstorming-dashboard {
                padding: 1.25rem;
            }

            .brainstorming-dashboard > .dashboard-shell {
                grid-template-columns: 1fr !important;
            }

            .brainstorming-sidebar,
            .brainstorming-dashboard .feature-main {
                grid-column: 1;
                width: 100%;
            }

            .brainstorming-sidebar {
                position: static;
            }

            .brainstorming-dashboard .page-hero {
                padding: 1.5rem;
            }

            .brainstorming-dashboard .front-table {
                min-width: 680px;
            }
        }
    </style>
</head>
<body class="skillbridge-front">
<?php include __DIR__ . '/partials/front_navbar.php'; ?>
<div class="front-page brainstorming-dashboard"><div class="dashboard-shell brainstorming-layout">
    <?php include __DIR__ . '/partials/brainstorming_sidebar.php'; ?>
    <main class="page-shell feature-main">
    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">Frontoffice</span>
            <h1>Ajouter un brainstorming</h1>
            <p>Creez d abord le brainstorming, puis rattachez-y des idees depuis leur page dediee.</p>
        </div>
    </section>

    <section class="page-section">
        <div class="container" style="max-width: 760px;">
            <div class="action-row" style="margin-bottom: 1rem;">
                <a href="?action=brainstorming_list" class="btn btn-secondary page-btn-secondary">Voir la liste</a>
                <?php if ((int) $_SESSION['user_role'] === 1): ?>
                    <a href="?action=brainstorming_admin" class="btn btn-warning">Admin Panel</a>
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

            <div class="form-card">
                <form id="brainstormingForm" method="POST" action="?action=brainstorming_add" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="titre">Titre <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="titre"
                            name="titre"
                            class="form-control"
                            value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>"
                            onblur="validateField('titre')"
                            oninput="clearError('titre')">
                        <div class="text-danger small mt-1" id="titre-error" style="display: none;"></div>
                        <div class="text-muted small mt-1">Minimum 5 caracteres, maximum 100 caracteres</div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description <span class="text-danger">*</span></label>
                        <textarea
                            id="description"
                            name="description"
                            class="form-control"
                            rows="5"
                            onblur="validateField('description')"
                            oninput="clearError('description')"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        <div class="text-danger small mt-1" id="description-error" style="display: none;"></div>
                        <div class="text-muted small mt-1">Minimum 20 caracteres, maximum 2000 caracteres</div>
                    </div>
                    <div class="form-group">
                        <label for="date_debut">Date de debut <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            id="date_debut"
                            name="date_debut"
                            class="form-control"
                            value="<?= htmlspecialchars($_POST['date_debut'] ?? '') ?>"
                            onblur="validateField('date_debut')"
                            oninput="clearError('date_debut')">
                        <div class="text-danger small mt-1" id="date_debut-error" style="display: none;"></div>
                        <div class="text-muted small mt-1">La date doit etre aujourd hui ou dans le futur</div>
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
                            error = 'Le titre doit contenir au moins 5 caracteres.';
                        } else if (titre.length > 100) {
                            error = 'Le titre ne peut pas depasser 100 caracteres.';
                        } else if (!/^[a-zA-Z0-9Ã€-Ã¿\s\-\.,!?\'"]+$/.test(titre)) {
                            error = 'Le titre contient des caracteres non autorises.';
                        }
                    } else if (fieldName === 'description') {
                        const description = field.value.trim();
                        if (!description) {
                            error = 'La description est obligatoire.';
                        } else if (description.length < 20) {
                            error = 'La description doit contenir au moins 20 caracteres.';
                        } else if (description.length > 2000) {
                            error = 'La description ne peut pas depasser 2000 caracteres.';
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
                                error = 'La date ne peut pas etre dans le passe.';
                            } else {
                                const maxDate = new Date();
                                maxDate.setFullYear(maxDate.getFullYear() + 1);
                                if (selectedDate > maxDate) {
                                    error = 'La date ne peut pas depasser un an.';
                                }
                            }
                        }
                    }

                    if (error) {
                        errorDiv.textContent = error;
                        errorDiv.style.display = 'block';
                        field.classList.add('is-invalid');
                        return false;
                    }

                    errorDiv.style.display = 'none';
                    field.classList.remove('is-invalid');
                    return true;
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
    </section>
</main>
</div></div>

<footer class="footer">
    <p>&copy; 2026 SkillBridge</p>
</footer>
</body>
</html>



