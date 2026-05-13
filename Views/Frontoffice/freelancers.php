<?php
require_once __DIR__ . '/../../Controllers/UserController.php';
require_once __DIR__ . '/../../Controllers/InteractionController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ?action=login');
    exit;
}

$userController = new UserController();
$interactionController = new InteractionController();
$currentUserId = $_SESSION['user_id'];
$role = $_SESSION['user_role'] ?? null;

$search = $_GET['search'] ?? '';
$niveau = $_GET['niveau'] ?? '';
$availability = $_GET['availability'] ?? '';
$minRating = $_GET['min_rating'] ?? '';

$filters = [];
if (!empty($search)) {
    $filters['search'] = $search;
}
if (!empty($niveau)) {
    $filters['niveau'] = $niveau;
}
if (!empty($availability)) {
    $filters['availability'] = $availability;
}
if ($minRating !== '') {
    $filters['min_rating'] = $minRating;
}

$freelancers = $userController->getFreelancers($filters);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'];
    $freelancerId = (int) ($_POST['freelancer_id'] ?? 0);

    if ($action === 'like') {
        $result = $interactionController->addInteraction($currentUserId, $freelancerId, 'like');
        $type = 'like';
    } elseif ($action === 'dislike') {
        $result = $interactionController->addInteraction($currentUserId, $freelancerId, 'dislike');
        $type = 'dislike';
    } elseif ($action === 'remove') {
        $result = $interactionController->removeInteraction($currentUserId, $freelancerId);
        $type = 'none';
    } else {
        $result = false;
        $type = 'none';
    }

    if ($result) {
        echo json_encode([
            'success' => true,
            'type' => $type,
            'likes' => $interactionController->getLikesCount($freelancerId),
            'dislikes' => $interactionController->getDislikesCount($freelancerId)
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Freelancers - SkillBridge</title>
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

        body.modal-open-soft {
            overflow: hidden;
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

        .filters-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .filter-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .filter-group input,
        .filter-group select {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(224, 112, 32, 0.1);
            outline: none;
        }

        .filter-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-search,
        .btn-reset {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-search {
            background: var(--primary);
            color: white;
        }

        .btn-search:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(224, 112, 32, 0.3);
        }

        .btn-reset {
            background: var(--bg-light);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-reset:hover {
            background: var(--border);
        }

        .freelancers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }

        .freelancer-card {
            background: white;
            border-radius: 18px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
            display: flex;
            flex-direction: column;
        }

        .freelancer-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 34px rgba(224, 112, 32, 0.12);
            border-color: rgba(224, 112, 32, 0.4);
        }

        .freelancer-card.ai-top-match {
            border-color: rgba(224,112,32,.72);
            box-shadow:
                0 22px 46px rgba(224,112,32,.18),
                0 0 0 4px rgba(224,112,32,.08);
        }

        .match-rank-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .45rem .75rem;
            border-radius: 999px;
            color: #fff;
            background: linear-gradient(135deg, #1f1f23, #e07020);
            font-size: .78rem;
            font-weight: 950;
            box-shadow: 0 12px 24px rgba(0,0,0,.18);
        }

        .freelancer-card.is-active {
            border-color: var(--primary);
            box-shadow: 0 18px 34px rgba(224, 112, 32, 0.16);
        }

        .freelancer-header {
            background: linear-gradient(135deg, var(--primary) 0%, #f5a962 100%);
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .freelancer-avatar {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1rem;
            color: var(--primary);
            font-weight: bold;
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .freelancer-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .freelancer-name {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .freelancer-role {
            font-size: 0.95rem;
            opacity: 0.9;
            text-transform: capitalize;
        }

        .freelancer-body {
            padding: 1.5rem;
            flex: 1;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.95rem;
            color: var(--text);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-light);
            font-weight: 500;
        }

        .rating-stars {
            color: var(--primary);
            font-weight: 700;
        }

        .summary-chip {
            margin-top: 1rem;
            padding: 1rem 1.05rem;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(224, 112, 32, 0.08) 0%, rgba(245, 169, 98, 0.14) 100%);
            color: var(--text);
            font-size: 0.92rem;
            line-height: 1.6;
            min-height: 90px;
        }

        .card-actions {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            margin-top: 1.15rem;
        }

        .expand-toggle {
            flex: 1;
            padding: 0.9rem 1rem;
            border: 1px solid rgba(224, 112, 32, 0.22);
            background: #fff7f1;
            color: var(--primary-dark);
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .expand-toggle:hover {
            background: #fff1e5;
            border-color: rgba(224, 112, 32, 0.45);
            transform: translateY(-1px);
        }

        .mini-feedback {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.75rem 0.95rem;
            border-radius: 999px;
            background: var(--bg-light);
            color: var(--text-light);
            font-size: 0.88rem;
            white-space: nowrap;
        }

        .freelancer-modal {
            position: fixed;
            inset: 0;
            background: rgba(12, 16, 24, 0.6);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.28s ease, visibility 0.28s ease;
            z-index: 1055;
        }

        .freelancer-modal.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .freelancer-modal-dialog {
            width: min(980px, 100%);
            max-height: 90vh;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 28px 70px rgba(0,0,0,0.28);
            transform: translateY(28px) scale(0.97);
            transition: transform 0.32s ease;
            display: flex;
            flex-direction: column;
        }

        .freelancer-modal.is-visible .freelancer-modal-dialog {
            transform: translateY(0) scale(1);
        }

        .modal-topbar {
            display: flex;
            justify-content: flex-end;
            padding: 1rem 1rem 0;
        }

        .modal-close {
            width: 42px;
            height: 42px;
            border: none;
            border-radius: 50%;
            background: rgba(26, 26, 26, 0.08);
            color: var(--secondary);
            font-size: 1rem;
            transition: all 0.25s ease;
        }

        .modal-close:hover {
            background: rgba(224, 112, 32, 0.14);
            color: var(--primary-dark);
            transform: rotate(90deg);
        }

        .modal-content-wrap {
            overflow-y: auto;
            padding: 0 2rem 2rem;
        }

        .modal-hero {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 1.5rem;
            align-items: stretch;
            margin-top: 0.2rem;
        }

        .modal-profile-card {
            background: linear-gradient(160deg, #1f232a 0%, #343a40 100%);
            color: white;
            border-radius: 22px;
            padding: 1.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .modal-avatar {
            width: 112px;
            height: 112px;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 1rem;
            background: white;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.4rem;
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(0,0,0,0.2);
        }

        .modal-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-profile-card h2 {
            font-size: 1.65rem;
            margin-bottom: 0.35rem;
        }

        .modal-profile-card p {
            margin-bottom: 0.55rem;
            color: rgba(255,255,255,0.82);
        }

        .modal-badges {
            width: 100%;
            display: grid;
            gap: 0.7rem;
            margin-top: 1.2rem;
        }

        .modal-badge {
            padding: 0.85rem 1rem;
            border-radius: 14px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .modal-badge span {
            display: block;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255,255,255,0.6);
            margin-bottom: 0.2rem;
        }

        .modal-badge strong {
            font-size: 1rem;
        }

        .modal-main {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .modal-intro {
            background: linear-gradient(135deg, rgba(224, 112, 32, 0.08) 0%, rgba(245, 169, 98, 0.16) 100%);
            border: 1px solid rgba(224, 112, 32, 0.12);
            border-radius: 22px;
            padding: 1.5rem;
        }

        .modal-intro h3 {
            margin-bottom: 0.45rem;
            font-size: 1.2rem;
            color: var(--secondary);
        }

        .modal-intro p {
            margin: 0;
            color: var(--text-light);
            line-height: 1.7;
        }

        .modal-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .modal-block {
            background: white;
            border-radius: 18px;
            border: 1px solid var(--border);
            padding: 1.25rem 1.2rem;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }

        .modal-block h4 {
            margin-bottom: 0.65rem;
            font-size: 1rem;
            color: var(--secondary);
        }

        .modal-block p,
        .modal-block span,
        .modal-block a {
            margin: 0;
            color: var(--text-light);
            line-height: 1.7;
            word-break: break-word;
        }

        .modal-block.is-wide {
            grid-column: 1 / -1;
        }

        .modal-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-top: 0.25rem;
        }

        .modal-feedback-count {
            color: var(--text-light);
            font-size: 0.92rem;
            font-weight: 600;
        }

        .feedback-actions {
            display: flex;
            gap: 0.9rem;
            margin-left: auto;
        }

        .btn-interact {
            min-width: 150px;
            padding: 0.95rem 1rem;
            border: 1px solid var(--border);
            background: white;
            border-radius: 14px;
            cursor: pointer;
            font-size: 0.98rem;
            transition: all 0.25s ease;
            font-weight: 700;
            color: var(--text-light);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
        }

        .btn-interact:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(224, 112, 32, 0.14);
        }

        .btn-interact.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .empty-state h2 {
            color: var(--text);
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }

        .empty-state p {
            color: var(--text-light);
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

        @media (max-width: 991px) {
            .modal-hero {
                grid-template-columns: 1fr;
            }

            .modal-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 2rem 1rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .freelancers-grid {
                grid-template-columns: 1fr;
            }

            .filter-buttons {
                flex-direction: column;
            }

            .btn-search,
            .btn-reset {
                width: 100%;
            }

            .card-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .mini-feedback {
                justify-content: center;
            }

            .freelancer-modal {
                padding: 0.85rem;
            }

            .modal-content-wrap {
                padding: 0 1rem 1rem;
            }

            .modal-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .feedback-actions {
                margin-left: 0;
                width: 100%;
            }

            .btn-interact {
                min-width: 0;
                width: 100%;
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

        .filters-section,
        .freelancer-card,
        .modal-shell,
        .empty-state {
            background: rgba(255,255,255,.9);
            border: 1px solid #dfd1bd;
            border-radius: 24px;
            box-shadow: 0 16px 34px rgba(30,30,32,.08);
            backdrop-filter: blur(10px);
        }

        .filter-group input,
        .filter-group select {
            border-radius: 14px;
            border-color: #d9c6ad;
            background: #fffdf9;
        }

        .btn-search,
        .btn-reset,
        .btn-interact {
            border-radius: 14px;
            font-weight: 700;
        }

        .btn-search {
            background: linear-gradient(135deg, #e07020, #f08a3b);
            box-shadow: 0 14px 26px rgba(224,112,32,.2);
        }

        .btn-reset {
            background: rgba(224,112,32,.08);
            border: 1px solid rgba(224,112,32,.2);
            color: #e07020;
        }

        .freelancer-card {
            box-shadow: 0 16px 28px rgba(30,30,32,.07);
        }

        .freelancers-shell {
            padding: 0 2rem;
            margin-bottom: 2rem;
        }

        .page-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(224,112,32,.14);
            border: 1px solid rgba(240,138,59,.24);
            color: #ffd8bd;
            font-weight: 800;
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .filters-section {
            position: relative;
            overflow: hidden;
            padding: 1.35rem;
            margin-bottom: 2.35rem;
        }

        .filters-section::before {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            right: -90px;
            top: -120px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(224,112,32,.18), transparent 68%);
            pointer-events: none;
        }

        .filters-heading {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.2rem;
        }

        .filters-heading h2 {
            margin: 0;
            color: #292321;
            font-size: 1.25rem;
            font-weight: 900;
            letter-spacing: -0.02em;
        }

        .filters-heading span {
            color: #8a705b;
            font-size: 0.92rem;
            font-weight: 700;
        }

        .filter-group {
            position: relative;
            grid-template-columns: 1.25fr repeat(3, minmax(170px, 0.75fr));
            gap: 0.9rem;
        }

        .filter-group input,
        .filter-group select {
            min-height: 52px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
        }

        .filter-group input::placeholder {
            color: #9b8370;
        }

        .filter-buttons {
            position: relative;
            justify-content: flex-end;
        }

        .ai-match-section {
            position: relative;
            overflow: hidden;
            margin: -0.6rem 0 2.35rem;
            padding: 1rem 1.15rem;
            border-radius: 26px;
            border: 1px solid rgba(224,112,32,.2);
            background:
                radial-gradient(circle at 8% 12%, rgba(245,160,79,.2), transparent 26%),
                radial-gradient(circle at 100% 0%, rgba(30,30,32,.12), transparent 28%),
                linear-gradient(135deg, rgba(255,255,255,.96), rgba(255,246,235,.9));
            box-shadow: 0 18px 38px rgba(30,30,32,.08);
            transition: padding .3s ease, box-shadow .3s ease, border-color .3s ease;
            cursor: pointer;
        }

        .ai-match-section.is-open {
            padding: 1.45rem;
            border-color: rgba(224,112,32,.34);
            box-shadow: 0 24px 52px rgba(224,112,32,.12);
            cursor: default;
        }

        .ai-match-section::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            right: -70px;
            bottom: -90px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(224,112,32,.22), transparent 68%);
            pointer-events: none;
        }

        .ai-match-header {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.2rem;
            margin-bottom: 0;
        }

        .ai-match-section.is-open .ai-match-header {
            margin-bottom: 1.15rem;
        }

        .ai-match-kicker {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .55rem;
            padding: .35rem .72rem;
            border-radius: 999px;
            color: #8a3b0f;
            background: #fff1e5;
            border: 1px solid rgba(224,112,32,.18);
            font-size: .78rem;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .ai-match-header h2 {
            margin: 0;
            color: #292321;
            font-size: 1.35rem;
            font-weight: 950;
            letter-spacing: -.02em;
        }

        .ai-match-header p {
            max-width: 680px;
            margin: .4rem 0 0;
            color: #7a6656;
            font-weight: 650;
            line-height: 1.6;
        }

        .ai-match-expand {
            position: relative;
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            border: none;
            border-radius: 999px;
            padding: .72rem 1rem;
            color: #fff;
            background: linear-gradient(135deg, #1f1f23, #e07020);
            font-weight: 900;
            box-shadow: 0 14px 26px rgba(224,112,32,.22);
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .ai-match-expand:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 32px rgba(224,112,32,.3);
        }

        .ai-match-expand i {
            transition: transform .25s ease;
        }

        .ai-match-section.is-open .ai-match-expand i {
            transform: rotate(180deg);
        }

        .ai-match-grid {
            position: relative;
            display: none;
            grid-template-columns: minmax(0, 1.35fr) repeat(2, minmax(170px, .45fr));
            gap: .9rem;
            align-items: stretch;
        }

        .ai-match-section.is-open .ai-match-grid {
            display: grid;
        }

        .ai-match-grid textarea,
        .ai-match-grid select {
            min-height: 54px;
            border: 1px solid #d9c6ad;
            border-radius: 16px;
            background: #fffdf9;
            color: #352a22;
            padding: .9rem 1rem;
            font: inherit;
            font-weight: 650;
            outline: none;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.72);
            transition: border-color .25s ease, box-shadow .25s ease;
        }

        .ai-match-grid textarea {
            min-height: 112px;
            resize: vertical;
            grid-row: span 2;
        }

        .ai-match-grid textarea:focus,
        .ai-match-grid select:focus {
            border-color: #e07020;
            box-shadow: 0 0 0 4px rgba(224,112,32,.12);
        }

        .ai-match-actions {
            display: flex;
            gap: .75rem;
            align-items: stretch;
        }

        .btn-ai-match,
        .btn-ai-clear {
            border: none;
            border-radius: 16px;
            padding: .92rem 1rem;
            font-weight: 900;
            transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
        }

        .btn-ai-match {
            flex: 1;
            color: white;
            background: linear-gradient(135deg, #1f1f23, #3b2a20 50%, #e07020);
            box-shadow: 0 16px 30px rgba(224,112,32,.2);
        }

        .btn-ai-match:hover,
        .btn-ai-clear:hover {
            transform: translateY(-2px);
        }

        .btn-ai-clear {
            color: #8a3b0f;
            background: #fff1e5;
            border: 1px solid rgba(224,112,32,.18);
        }

        .ai-match-results {
            position: relative;
            display: none;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(224,112,32,.16);
        }

        .ai-match-results.is-visible {
            display: block;
        }

        .search-ai-hint {
            position: absolute;
            z-index: 20;
            display: none;
            align-items: flex-start;
            gap: .65rem;
            width: min(360px, calc(100vw - 2rem));
            padding: .85rem .95rem;
            border-radius: 18px;
            color: #4f3a2c;
            background: rgba(255,255,255,.96);
            border: 1px solid rgba(224,112,32,.22);
            box-shadow: 0 18px 36px rgba(30,30,32,.14);
            backdrop-filter: blur(10px);
        }

        .search-ai-hint.is-visible {
            display: flex;
        }

        .search-ai-hint i {
            color: #e07020;
            margin-top: .2rem;
        }

        .search-ai-hint strong {
            display: block;
            color: #2b241f;
            font-weight: 950;
            margin-bottom: .15rem;
        }

        .search-ai-hint span {
            display: block;
            color: #7a6656;
            font-size: .9rem;
            font-weight: 650;
            line-height: 1.45;
        }

        .ai-robot-guide {
            position: absolute;
            z-index: 22;
            display: none;
            align-items: flex-end;
            gap: .75rem;
            pointer-events: none;
            transform-origin: bottom center;
            animation: robotPop .34s cubic-bezier(.2, 1.15, .35, 1) both;
        }

        .ai-robot-guide.is-visible {
            display: flex;
        }

        .ai-robot-bubble {
            position: relative;
            max-width: 210px;
            padding: .78rem .9rem;
            border-radius: 18px 18px 18px 6px;
            color: #3a2b22;
            background: rgba(255,255,255,.97);
            border: 1px solid rgba(224,112,32,.22);
            box-shadow: 0 16px 34px rgba(30,30,32,.14);
            font-size: .9rem;
            font-weight: 900;
            line-height: 1.35;
        }

        .ai-robot-bubble::after {
            content: "";
            position: absolute;
            right: -7px;
            bottom: 16px;
            width: 14px;
            height: 14px;
            background: inherit;
            border-right: 1px solid rgba(224,112,32,.22);
            border-bottom: 1px solid rgba(224,112,32,.22);
            transform: rotate(-45deg);
        }

        .ai-robot-body {
            position: relative;
            width: 72px;
            height: 86px;
            border-radius: 24px 24px 20px 20px;
            background:
                radial-gradient(circle at 28% 26%, rgba(255,255,255,.95), transparent 12%),
                linear-gradient(145deg, #fffaf4, #ffd8b4);
            border: 2px solid rgba(224,112,32,.26);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.9),
                0 18px 34px rgba(224,112,32,.18);
            animation: robotFloat 2.2s ease-in-out infinite;
        }

        .ai-robot-body::before {
            content: "";
            position: absolute;
            left: 50%;
            top: -16px;
            width: 3px;
            height: 17px;
            background: #e07020;
            transform: translateX(-50%);
            border-radius: 999px;
        }

        .ai-robot-body::after {
            content: "";
            position: absolute;
            left: 50%;
            top: -24px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #f5a04f;
            transform: translateX(-50%);
            box-shadow: 0 0 18px rgba(245,160,79,.85);
        }

        .ai-robot-face {
            position: absolute;
            left: 12px;
            right: 12px;
            top: 16px;
            height: 32px;
            border-radius: 16px;
            background: linear-gradient(135deg, #1f1f23, #3b2a20);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.12);
        }

        .ai-robot-face::before,
        .ai-robot-face::after {
            content: "";
            position: absolute;
            top: 11px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ffd8b4;
            box-shadow: 0 0 12px rgba(245,160,79,.95);
            animation: robotBlink 4s infinite;
        }

        .ai-robot-face::before {
            left: 12px;
        }

        .ai-robot-face::after {
            right: 12px;
        }

        .ai-robot-smile {
            position: absolute;
            left: 29px;
            top: 53px;
            width: 16px;
            height: 8px;
            border-bottom: 3px solid #8a3b0f;
            border-radius: 0 0 999px 999px;
        }

        .ai-robot-arm {
            position: absolute;
            right: -25px;
            top: 40px;
            width: 34px;
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(90deg, #ffd8b4, #e07020);
            transform-origin: left center;
            transform: rotate(-26deg);
            animation: robotPoint 1.1s ease-in-out infinite;
        }

        .ai-robot-arm::after {
            content: "";
            position: absolute;
            right: -6px;
            top: -4px;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: #e07020;
            box-shadow: 0 0 14px rgba(224,112,32,.45);
        }

        .ai-robot-shadow {
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: -9px;
            height: 10px;
            border-radius: 50%;
            background: rgba(104,72,44,.18);
            filter: blur(3px);
            animation: robotShadow 2.2s ease-in-out infinite;
        }

        .ai-robot-guide.is-celebrating .ai-robot-arm {
            right: -14px;
            transform: rotate(-72deg);
            animation: robotWave .55s ease-in-out infinite;
        }

        @keyframes robotPop {
            from {
                opacity: 0;
                transform: translateY(12px) scale(.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes robotFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-7px); }
        }

        @keyframes robotShadow {
            0%, 100% { transform: scaleX(1); opacity: .2; }
            50% { transform: scaleX(.78); opacity: .12; }
        }

        @keyframes robotPoint {
            0%, 100% { transform: rotate(-26deg) translateX(0); }
            50% { transform: rotate(-20deg) translateX(4px); }
        }

        @keyframes robotWave {
            0%, 100% { transform: rotate(-72deg); }
            50% { transform: rotate(-38deg); }
        }

        @keyframes robotBlink {
            0%, 44%, 48%, 100% { transform: scaleY(1); }
            46% { transform: scaleY(.15); }
        }

        .ai-match-results-title {
            display: flex;
            align-items: center;
            gap: .55rem;
            margin-bottom: .8rem;
            color: #2b241f;
            font-weight: 950;
        }

        .ai-match-list {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .85rem;
        }

        .ai-match-result-card {
            border-radius: 18px;
            padding: 1rem;
            background: rgba(255,255,255,.78);
            border: 1px solid #ead8c2;
            box-shadow: 0 10px 20px rgba(30,30,32,.06);
        }

        .ai-match-result-card strong {
            display: block;
            color: #2b241f;
            font-weight: 950;
            margin-bottom: .35rem;
        }

        .ai-match-result-card span {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin-bottom: .45rem;
            padding: .28rem .55rem;
            border-radius: 999px;
            color: #8a3b0f;
            background: #fff1e5;
            font-size: .82rem;
            font-weight: 900;
        }

        .ai-match-result-card p {
            margin: 0;
            color: #705949;
            font-size: .9rem;
            font-weight: 650;
            line-height: 1.55;
        }

        .ai-match-empty {
            display: none;
            color: #7a6656;
            font-weight: 750;
        }

        .ai-match-empty.is-visible {
            display: block;
        }

        .btn-search,
        .btn-reset {
            min-height: 48px;
            padding-inline: 1.35rem;
        }

        .btn-reset:hover {
            background: rgba(224,112,32,.14);
            color: #c85a14;
            border-color: rgba(224,112,32,.32);
            transform: translateY(-2px);
        }

        .freelancers-grid {
            grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
            gap: 1.45rem;
        }

        .freelancer-card {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            border-color: #ead8c2;
            background:
                linear-gradient(145deg, rgba(255,255,255,.96), rgba(255,248,239,.9));
        }

        .freelancer-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(224,112,32,.16), transparent 34%);
            z-index: -1;
        }

        .freelancer-header {
            position: relative;
            background:
                radial-gradient(circle at top right, rgba(255,255,255,.24), transparent 28%),
                linear-gradient(135deg, #242126 0%, #3b2a20 48%, #e07020 100%);
            padding: 1.75rem 1.4rem 1.55rem;
        }

        .freelancer-header::after {
            content: "";
            position: absolute;
            left: 1.25rem;
            right: 1.25rem;
            bottom: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.34), transparent);
        }

        .freelancer-avatar {
            width: 88px;
            height: 88px;
            border: 3px solid rgba(255,255,255,.84);
            box-shadow: 0 16px 30px rgba(0,0,0,.22);
        }

        .freelancer-name {
            font-size: 1.25rem;
            font-weight: 900;
            letter-spacing: -0.02em;
        }

        .freelancer-role {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 0.45rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.16);
            font-size: 0.82rem;
            font-weight: 800;
        }

        .freelancer-body {
            padding: 1.35rem;
        }

        .info-item {
            border-bottom-color: #ead8c2;
        }

        .info-label {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 800;
            color: #8a705b;
        }

        .rating-stars {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: #e07020;
            background: #fff2e7;
            border: 1px solid rgba(224,112,32,.16);
            border-radius: 999px;
            padding: 0.35rem 0.7rem;
        }

        .rating-stars::before {
            content: "\f005";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            color: #f5a524;
        }

        .feedback-inline {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            color: #7a6656;
            font-weight: 800;
        }

        .feedback-inline .like-count {
            color: #238756;
        }

        .feedback-inline .dislike-count {
            color: #d47a15;
        }

        .feedback-inline + span,
        .mini-feedback span:not(.clean-feedback),
        .clean-like-action > span:first-of-type,
        .clean-dislike-action > span:first-of-type {
            display: none;
        }

        .summary-chip {
            border: 1px solid rgba(224,112,32,.16);
            background:
                radial-gradient(circle at top right, rgba(224,112,32,.12), transparent 34%),
                linear-gradient(135deg, rgba(255,250,244,.94), rgba(255,244,230,.86));
            color: #554133;
            font-weight: 600;
        }

        .expand-toggle {
            background: linear-gradient(135deg, #e07020, #f08a3b);
            color: white;
            border-color: rgba(224,112,32,.28);
            box-shadow: 0 14px 24px rgba(224,112,32,.18);
        }

        .expand-toggle:hover {
            background: linear-gradient(135deg, #c85a14, #e07020);
            color: white;
            box-shadow: 0 18px 30px rgba(224,112,32,.26);
        }

        .mini-feedback {
            background: #fff8f1;
            border: 1px solid #ead8c2;
            color: #705949;
            font-weight: 800;
        }

        .mini-feedback i.fa-thumbs-up,
        .modal-feedback-count i.fa-thumbs-up {
            color: #238756;
        }

        .mini-feedback i.fa-thumbs-down,
        .modal-feedback-count i.fa-thumbs-down {
            color: #d47a15;
        }

        .freelancer-modal {
            background: rgba(23, 20, 18, 0.64);
        }

        .freelancer-modal-dialog {
            border: 1px solid rgba(224,112,32,.2);
            background:
                radial-gradient(circle at top right, rgba(224,112,32,.08), transparent 30%),
                #fffaf4;
        }

        .modal-profile-card {
            background:
                radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 32%),
                linear-gradient(160deg, #201f22 0%, #3a2a20 58%, #e07020 100%);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.12);
        }

        .modal-intro,
        .modal-block {
            border-color: #ead8c2;
            background: rgba(255,255,255,.82);
        }

        .modal-block h4 {
            color: #292321;
            font-weight: 900;
        }

        .modal-feedback-count {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.75rem 1rem;
            border-radius: 999px;
            background: #fff8f1;
            border: 1px solid #ead8c2;
            color: #705949;
        }

        .btn-interact.active {
            background: linear-gradient(135deg, #e07020, #f08a3b);
            border-color: rgba(224,112,32,.4);
            box-shadow: 0 14px 24px rgba(224,112,32,.2);
        }

        .empty-state {
            padding: 4.5rem 2rem;
            background:
                radial-gradient(circle at top right, rgba(224,112,32,.12), transparent 30%),
                rgba(255,255,255,.9);
        }

        .empty-state h2 {
            font-weight: 900;
        }

        footer {
            background: #1e1e20;
            border-top: 1px solid rgba(224,112,32,.18);
        }

        @media (max-width: 1100px) {
            .filter-group {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ai-match-grid,
            .ai-match-list {
                grid-template-columns: 1fr;
            }

            .ai-match-grid textarea {
                grid-row: auto;
            }
        }

        @media (max-width: 768px) {
            .freelancers-shell {
                padding: 0 1rem;
            }

            .filters-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .filter-group {
                grid-template-columns: 1fr;
            }

            .ai-match-header {
                flex-direction: column;
                align-items: stretch;
            }

            .ai-match-actions {
                flex-direction: column;
            }

            .ai-match-expand {
                justify-content: center;
                width: 100%;
            }
        }
    </style>
</head>
<body class="skillbridge-front">
    <?php require __DIR__ . '/partials/front_navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <div class="page-kicker"><i class="fas fa-user-group"></i> Talent marketplace</div>
            <h1>Find Freelancers</h1>
            <p>Discover and collaborate with talented professionals</p>
        </div>
    </div>

    <main class="container freelancers-shell">
        <div class="filters-section">
            <form id="freelancerFilterForm" method="GET" action="?action=freelancers">
                <div class="filters-heading">
                    <div>
                        <h2>Find the right profile faster</h2>
                        <span>Search by name, experience, availability, or minimum rating.</span>
                    </div>
                </div>
                <div class="filter-group">
                    <input type="text" name="search" id="freelancerSearchInput" placeholder="Search by name..." value="<?= htmlspecialchars($search) ?>">

                    <select name="niveau">
                        <option value="">-- Experience Level --</option>
                        <option value="dÃ©butant" <?= ($niveau === 'dÃ©butant') ? 'selected' : '' ?>>Beginner</option>
                        <option value="intermÃ©diaire" <?= ($niveau === 'intermÃ©diaire') ? 'selected' : '' ?>>Intermediate</option>
                        <option value="expert" <?= ($niveau === 'expert') ? 'selected' : '' ?>>Expert</option>
                    </select>

                    <select name="availability">
                        <option value="">-- Availability --</option>
                        <option value="available" <?= ($availability === 'available') ? 'selected' : '' ?>>Available</option>
                        <option value="part-time" <?= ($availability === 'part-time') ? 'selected' : '' ?>>Part-time</option>
                        <option value="full-time" <?= ($availability === 'full-time') ? 'selected' : '' ?>>Full-time</option>
                    </select>

                    <select name="min_rating">
                        <option value="">-- Minimum Rating --</option>
                        <option value="1" <?= ($minRating === '1') ? 'selected' : '' ?>>1+ / 5</option>
                        <option value="2" <?= ($minRating === '2') ? 'selected' : '' ?>>2+ / 5</option>
                        <option value="3" <?= ($minRating === '3') ? 'selected' : '' ?>>3+ / 5</option>
                        <option value="4" <?= ($minRating === '4') ? 'selected' : '' ?>>4+ / 5</option>
                        <option value="4.5" <?= ($minRating === '4.5') ? 'selected' : '' ?>>4.5+ / 5</option>
                    </select>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search me-2"></i>Search
                    </button>
                    <button type="button" class="btn-reset" id="resetFiltersBtn">
                        <i class="fas fa-redo me-2"></i>Reset
                    </button>
                </div>
            </form>
        </div>

        <?php if (!empty($freelancers)): ?>
            <section class="ai-match-section" id="aiMatchSection" aria-labelledby="aiMatchTitle">
                <div class="ai-match-header">
                    <div>
                        <div class="ai-match-kicker"><i class="fas fa-wand-magic-sparkles"></i> Smart match helper</div>
                        <h2 id="aiMatchTitle">Let AI suggest the best freelancer</h2>
                        <p>Describe the project and SkillBridge will rank visible freelancers by skills, experience text, availability, level, rating, and client feedback.</p>
                    </div>
                    <button type="button" class="ai-match-expand" id="aiMatchToggle" aria-expanded="false">
                        Open assistant <i class="fas fa-chevron-down"></i>
                    </button>
                </div>

                <div class="ai-match-grid">
                    <textarea id="aiProjectBrief" placeholder="Example: I need a responsive React dashboard with API integration, clean UI, charts, and delivery in one week."></textarea>
                    <select id="aiPreferredLevel">
                        <option value="">Any level</option>
                        <option value="expert">Prefer expert</option>
                        <option value="interm">Prefer intermediate</option>
                        <option value="beginner">Open to beginner</option>
                    </select>
                    <select id="aiUrgency">
                        <option value="">Normal urgency</option>
                        <option value="fast">Fast delivery</option>
                        <option value="quality">Quality first</option>
                        <option value="budget">Budget friendly</option>
                    </select>
                    <div class="ai-match-actions">
                        <button type="button" class="btn-ai-match" id="runAiMatchBtn">
                            <i class="fas fa-bolt me-2"></i>Find best matches
                        </button>
                        <button type="button" class="btn-ai-clear" id="clearAiMatchBtn">
                            Clear
                        </button>
                    </div>
                </div>

                <div class="ai-match-results" id="aiMatchResults">
                    <div class="ai-match-results-title">
                        <i class="fas fa-ranking-star"></i>
                        Recommended freelancers
                    </div>
                    <div class="ai-match-empty" id="aiMatchEmpty">Add more project details so the assistant can make a confident match.</div>
                    <div class="ai-match-list" id="aiMatchList"></div>
                </div>
            </section>
        <?php endif; ?>

        <div class="search-ai-hint" id="searchAiHint" role="status">
            <i class="fas fa-wand-magic-sparkles"></i>
            <div>
                <strong>Need a better match?</strong>
                <span>You can use the AI helper to describe your project and find the best freelancer faster.</span>
            </div>
        </div>

        <div class="ai-robot-guide" id="aiRobotGuide" aria-hidden="true">
            <div class="ai-robot-bubble" id="aiRobotBubble">Let me help you pick</div>
            <div class="ai-robot-body">
                <div class="ai-robot-face"></div>
                <div class="ai-robot-smile"></div>
                <div class="ai-robot-arm"></div>
                <div class="ai-robot-shadow"></div>
            </div>
        </div>

        <?php if (empty($freelancers)): ?>
            <div class="empty-state" id="emptyState">
                <h2>No Freelancers Found</h2>
                <p>Try adjusting your search filters or check back later</p>
            </div>
        <?php else: ?>
            <div class="freelancers-grid" id="freelancersGrid">
                <?php foreach ($freelancers as $freelancer): ?>
                    <?php
                    $rating = $interactionController->calculateRating($freelancer->getIdUser());
                    $likes = $interactionController->getLikesCount($freelancer->getIdUser());
                    $dislikes = $interactionController->getDislikesCount($freelancer->getIdUser());
                    $currentInteraction = $interactionController->getInteraction($currentUserId, $freelancer->getIdUser());
                    $avatar = strtoupper(mb_substr($freelancer->getPrenom(), 0, 1) . mb_substr($freelancer->getNom(), 0, 1));
                    $profilePicture = $freelancer->getProfilePicture();
                    $summaryText = trim((string) $freelancer->getSkillSummary());
                    $bioText = trim((string) $freelancer->getBio());
                    $previewText = $summaryText !== '' ? $summaryText : ($bioText !== '' ? mb_strimwidth($bioText, 0, 110, '...') : 'This freelancer is ready to collaborate on new projects.');
                    ?>
                    <div class="freelancer-card"
                         data-id="<?= $freelancer->getIdUser() ?>"
                         data-name="<?= htmlspecialchars($freelancer->getPrenom() . ' ' . $freelancer->getNom(), ENT_QUOTES) ?>"
                         data-name-search="<?= htmlspecialchars(strtolower($freelancer->getPrenom() . ' ' . $freelancer->getNom()), ENT_QUOTES) ?>"
                         data-niveau="<?= htmlspecialchars($freelancer->getNiveau(), ENT_QUOTES) ?>"
                         data-availability="<?= htmlspecialchars($freelancer->getAvailability(), ENT_QUOTES) ?>"
                         data-rating="<?= htmlspecialchars(number_format($rating, 1, '.', ''), ENT_QUOTES) ?>"
                         data-phone="<?= htmlspecialchars($freelancer->getPhone() ?: 'Not provided yet', ENT_QUOTES) ?>"
                         data-skill-summary="<?= htmlspecialchars($freelancer->getSkillSummary() ?: 'No skill summary added yet', ENT_QUOTES) ?>"
                         data-bio="<?= htmlspecialchars($freelancer->getBio() ?: 'This freelancer has not added a bio yet.', ENT_QUOTES) ?>"
                         data-experience="<?= htmlspecialchars($freelancer->getExperienceDescription() ?: 'Experience details will appear here once completed.', ENT_QUOTES) ?>"
                         data-profile-picture="<?= htmlspecialchars($profilePicture ?: '', ENT_QUOTES) ?>"
                         data-avatar="<?= htmlspecialchars($avatar, ENT_QUOTES) ?>"
                         data-likes="<?= $likes ?>"
                         data-dislikes="<?= $dislikes ?>"
                         data-current-interaction="<?= htmlspecialchars($currentInteraction ?: 'none', ENT_QUOTES) ?>">
                        <div class="freelancer-header">
                            <div class="freelancer-avatar">
                                <?php if (!empty($profilePicture)): ?>
                                    <img src="<?= htmlspecialchars($profilePicture) ?>" alt="<?= htmlspecialchars($freelancer->getPrenom() . ' ' . $freelancer->getNom()) ?>">
                                <?php else: ?>
                                    <?= htmlspecialchars($avatar) ?>
                                <?php endif; ?>
                            </div>
                            <div class="freelancer-name"><?= htmlspecialchars($freelancer->getPrenom() . ' ' . $freelancer->getNom()) ?></div>
                            <div class="freelancer-role"><?= htmlspecialchars(ucfirst($freelancer->getNiveau())) ?></div>
                        </div>

                        <div class="freelancer-body">
                            <div class="info-item">
                                <span class="info-label">Rating</span>
                                <span class="rating-stars"><?= number_format($rating, 1) ?>/5</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Availability</span>
                                <span><?= htmlspecialchars(ucfirst($freelancer->getAvailability())) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Feedback</span>
                                <span class="feedback-inline">
                                    <span class="like-count"><i class="fas fa-thumbs-up"></i> <?= $likes ?></span>
                                    <span class="dislike-count"><i class="fas fa-thumbs-down"></i> <?= $dislikes ?></span>
                                </span>
                                <span><span style="color: var(--primary);">👍 <?= $likes ?></span> <span style="margin-left: 0.5rem; color: #e74c3c;">👎 <?= $dislikes ?></span></span>
                            </div>

                            <div class="summary-chip">
                                <?= htmlspecialchars($previewText) ?>
                            </div>

                            <div class="card-actions">
                                <button type="button" class="expand-toggle" onclick="openFreelancerModal(this)">
                                    View profile <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                                <div class="mini-feedback">
                                    <span class="clean-feedback"><i class="fas fa-thumbs-up"></i> <?= $likes ?></span>
                                    <span class="clean-feedback"><i class="fas fa-thumbs-down"></i> <?= $dislikes ?></span>
                                    <span>👍 <?= $likes ?></span>
                                    <span>👎 <?= $dislikes ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <div class="freelancer-modal" id="freelancerModal" aria-hidden="true">
        <div class="freelancer-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalFreelancerName">
            <div class="modal-topbar">
                <button type="button" class="modal-close" id="closeFreelancerModal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-content-wrap">
                <div class="modal-hero">
                    <div class="modal-profile-card">
                        <div class="modal-avatar" id="modalFreelancerAvatar"></div>
                        <h2 id="modalFreelancerName"></h2>
                        <p id="modalFreelancerRole"></p>
                        <div class="modal-badges">
                            <div class="modal-badge">
                                <span>Availability</span>
                                <strong id="modalFreelancerAvailability"></strong>
                            </div>
                            <div class="modal-badge">
                                <span>Rating</span>
                                <strong id="modalFreelancerRating"></strong>
                            </div>
                        </div>
                    </div>

                    <div class="modal-main">
                        <div class="modal-intro">
                            <h3>Professional overview</h3>
                            <p id="modalFreelancerSummary"></p>
                        </div>

                        <div class="modal-grid">
                            <div class="modal-block">
                                <h4>Phone</h4>
                                <span id="modalFreelancerPhone"></span>
                            </div>
                            <div class="modal-block">
                                <h4>Skill summary</h4>
                                <span id="modalFreelancerSkillSummary"></span>
                            </div>
                            <div class="modal-block is-wide">
                                <h4>Bio</h4>
                                <p id="modalFreelancerBio"></p>
                            </div>
                            <div class="modal-block is-wide">
                                <h4>Experience description</h4>
                                <p id="modalFreelancerExperience"></p>
                            </div>
                        </div>

                        <div class="modal-actions">
                            <div class="modal-feedback-count" id="modalFreelancerFeedback"></div>
                            <div class="feedback-actions">
                                <button class="btn-interact clean-like-action" id="modalLikeButton" type="button">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span>👍</span>
                                    <span>Like</span>
                                </button>
                                <button class="btn-interact clean-dislike-action" id="modalDislikeButton" type="button">
                                    <i class="fas fa-thumbs-down"></i>
                                    <span>👎</span>
                                    <span>Dislike</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 SkillBridge. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const freelancerFilterForm = document.getElementById('freelancerFilterForm');
        const freelancersGrid = document.getElementById('freelancersGrid');
        const emptyState = document.getElementById('emptyState');
        const resetFiltersBtn = document.getElementById('resetFiltersBtn');
        const freelancerModal = document.getElementById('freelancerModal');
        const closeFreelancerModalButton = document.getElementById('closeFreelancerModal');
        const modalFreelancerAvatar = document.getElementById('modalFreelancerAvatar');
        const modalFreelancerName = document.getElementById('modalFreelancerName');
        const modalFreelancerRole = document.getElementById('modalFreelancerRole');
        const modalFreelancerAvailability = document.getElementById('modalFreelancerAvailability');
        const modalFreelancerRating = document.getElementById('modalFreelancerRating');
        const modalFreelancerSummary = document.getElementById('modalFreelancerSummary');
        const modalFreelancerPhone = document.getElementById('modalFreelancerPhone');
        const modalFreelancerSkillSummary = document.getElementById('modalFreelancerSkillSummary');
        const modalFreelancerBio = document.getElementById('modalFreelancerBio');
        const modalFreelancerExperience = document.getElementById('modalFreelancerExperience');
        const modalFreelancerFeedback = document.getElementById('modalFreelancerFeedback');
        const modalLikeButton = document.getElementById('modalLikeButton');
        const modalDislikeButton = document.getElementById('modalDislikeButton');
        const aiProjectBrief = document.getElementById('aiProjectBrief');
        const aiPreferredLevel = document.getElementById('aiPreferredLevel');
        const aiUrgency = document.getElementById('aiUrgency');
        const aiMatchSection = document.getElementById('aiMatchSection');
        const aiMatchToggle = document.getElementById('aiMatchToggle');
        const runAiMatchBtn = document.getElementById('runAiMatchBtn');
        const clearAiMatchBtn = document.getElementById('clearAiMatchBtn');
        const aiMatchResults = document.getElementById('aiMatchResults');
        const aiMatchList = document.getElementById('aiMatchList');
        const aiMatchEmpty = document.getElementById('aiMatchEmpty');
        const freelancerSearchInput = document.getElementById('freelancerSearchInput');
        const searchAiHint = document.getElementById('searchAiHint');
        const aiRobotGuide = document.getElementById('aiRobotGuide');
        const aiRobotBubble = document.getElementById('aiRobotBubble');

        let activeFreelancerCard = null;
        let originalFreelancerOrder = [];
        let aiAssistantWasOpened = false;

        function formatTextWithBreaks(value) {
            return value.replace(/\n/g, '<br>');
        }

        function applyFreelancerFilters() {
            if (!freelancerFilterForm || !freelancersGrid) {
                return;
            }

            const cards = freelancersGrid.querySelectorAll('.freelancer-card');
            const searchValue = freelancerFilterForm.elements['search'].value.trim().toLowerCase();
            const niveauValue = freelancerFilterForm.elements['niveau'].value;
            const availabilityValue = freelancerFilterForm.elements['availability'].value;
            const minRatingValue = freelancerFilterForm.elements['min_rating'].value;
            const minimumRating = minRatingValue === '' ? null : parseFloat(minRatingValue);
            let visibleCount = 0;

            cards.forEach(function(card) {
                const matchesSearch = searchValue === '' || (card.dataset.nameSearch || '').includes(searchValue);
                const matchesNiveau = niveauValue === '' || (card.dataset.niveau || '') === niveauValue;
                const matchesAvailability = availabilityValue === '' || (card.dataset.availability || '') === availabilityValue;
                const matchesRating = minimumRating === null || parseFloat(card.dataset.rating || '0') >= minimumRating;
                const isVisible = matchesSearch && matchesNiveau && matchesAvailability && matchesRating;

                card.style.display = isVisible ? '' : 'none';
                if (isVisible) {
                    visibleCount++;
                }
            });

            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        if (freelancerFilterForm) {
            freelancerFilterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                applyFreelancerFilters();
            });

            freelancerFilterForm.elements['search'].addEventListener('input', applyFreelancerFilters);
            freelancerFilterForm.elements['niveau'].addEventListener('change', applyFreelancerFilters);
            freelancerFilterForm.elements['availability'].addEventListener('change', applyFreelancerFilters);
            freelancerFilterForm.elements['min_rating'].addEventListener('change', applyFreelancerFilters);
        }

        if (resetFiltersBtn && freelancerFilterForm) {
            resetFiltersBtn.addEventListener('click', function() {
                freelancerFilterForm.reset();
                applyFreelancerFilters();
            });
        }

        function positionAiRobotNearAssistant() {
            if (!aiRobotGuide || !aiMatchToggle) {
                return;
            }

            const toggleRect = aiMatchToggle.getBoundingClientRect();
            const robotWidth = aiRobotGuide.offsetWidth || 300;
            const pageX = window.scrollX || document.documentElement.scrollLeft;
            const pageY = window.scrollY || document.documentElement.scrollTop;
            const left = Math.max(16, toggleRect.left + pageX - robotWidth - 18);
            const top = Math.max(16, toggleRect.top + pageY - 38);
            aiRobotGuide.style.left = left + 'px';
            aiRobotGuide.style.top = top + 'px';
        }

        function showAiRobot(message, celebrating) {
            if (!aiRobotGuide || !aiRobotBubble) {
                return;
            }

            aiRobotBubble.textContent = message;
            aiRobotGuide.classList.toggle('is-celebrating', Boolean(celebrating));
            aiRobotGuide.classList.add('is-visible');
            positionAiRobotNearAssistant();
        }

        function hideAiRobot(delay) {
            if (!aiRobotGuide) {
                return;
            }

            setTimeout(function() {
                aiRobotGuide.classList.remove('is-visible', 'is-celebrating');
            }, delay || 0);
        }

        function setAiMatchOpen(isOpen) {
            if (!aiMatchSection || !aiMatchToggle) {
                return;
            }

            aiMatchSection.classList.toggle('is-open', isOpen);
            aiMatchToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            aiMatchToggle.innerHTML = isOpen
                ? 'Close assistant <i class="fas fa-chevron-down"></i>'
                : 'Open assistant <i class="fas fa-chevron-down"></i>';

            if (isOpen && aiProjectBrief) {
                aiAssistantWasOpened = true;
                showAiRobot('Here we go', true);
                setTimeout(function() {
                    aiProjectBrief.focus();
                }, 160);
            } else {
                hideAiRobot(0);
            }
        }

        if (aiMatchSection && aiMatchToggle) {
            aiMatchToggle.addEventListener('click', function(event) {
                event.stopPropagation();
                setAiMatchOpen(!aiMatchSection.classList.contains('is-open'));
            });

            aiMatchSection.addEventListener('click', function(event) {
                if (!aiMatchSection.classList.contains('is-open') && !event.target.closest('button, input, select, textarea, a')) {
                    setAiMatchOpen(true);
                }
            });
        }

        function positionSearchAiHint() {
            if (!freelancerSearchInput || !searchAiHint) {
                return;
            }

            const inputRect = freelancerSearchInput.getBoundingClientRect();
            const pageX = window.scrollX || document.documentElement.scrollLeft;
            const pageY = window.scrollY || document.documentElement.scrollTop;
            searchAiHint.style.left = Math.max(16, inputRect.left + pageX) + 'px';
            searchAiHint.style.top = (inputRect.bottom + pageY + 10) + 'px';
        }

        if (freelancerSearchInput && searchAiHint) {
            freelancerSearchInput.addEventListener('focus', function() {
                positionSearchAiHint();
                searchAiHint.classList.add('is-visible');
                showAiRobot('Let me help you pick', false);
            });

            freelancerSearchInput.addEventListener('blur', function() {
                setTimeout(function() {
                    searchAiHint.classList.remove('is-visible');
                }, 220);
                if (!aiAssistantWasOpened) {
                    hideAiRobot(900);
                }
            });

            window.addEventListener('resize', positionSearchAiHint);
            window.addEventListener('resize', positionAiRobotNearAssistant);
            window.addEventListener('scroll', function() {
                if (searchAiHint.classList.contains('is-visible')) {
                    positionSearchAiHint();
                }
                if (aiRobotGuide && aiRobotGuide.classList.contains('is-visible')) {
                    positionAiRobotNearAssistant();
                }
            }, { passive: true });

            searchAiHint.addEventListener('mousedown', function(event) {
                event.preventDefault();
                setAiMatchOpen(true);
                searchAiHint.classList.remove('is-visible');
                if (aiMatchSection) {
                    aiMatchSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }

        function normalizeMatchText(value) {
            return (value || '')
                .toString()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase();
        }

        function tokenizeMatchText(value) {
            const stopWords = new Set([
                'the', 'and', 'for', 'with', 'that', 'this', 'from', 'into', 'need', 'needs', 'want', 'wants',
                'project', 'freelancer', 'service', 'work', 'make', 'build', 'create', 'good', 'best', 'please',
                'une', 'des', 'les', 'avec', 'pour', 'dans', 'mon', 'ma', 'mes', 'un', 'le', 'la', 'de', 'du',
                'je', 'veux', 'besoin', 'projet', 'site', 'application'
            ]);

            return normalizeMatchText(value)
                .split(/[^a-z0-9+#.]+/i)
                .filter(function(token) {
                    return token.length > 2 && !stopWords.has(token);
                });
        }

        function getCardMatchText(card) {
            return [
                card.dataset.name,
                card.dataset.niveau,
                card.dataset.availability,
                card.dataset.skillSummary,
                card.dataset.bio,
                card.dataset.experience
            ].join(' ');
        }

        function scoreFreelancerCard(card, projectTokens, preferredLevel, urgency) {
            const skillText = normalizeMatchText(card.dataset.skillSummary || '');
            const bioText = normalizeMatchText(card.dataset.bio || '');
            const experienceText = normalizeMatchText(card.dataset.experience || '');
            const allText = normalizeMatchText(getCardMatchText(card));
            const niveauText = normalizeMatchText(card.dataset.niveau || '');
            const availabilityText = normalizeMatchText(card.dataset.availability || '');
            const rating = parseFloat(card.dataset.rating || '0');
            const likes = parseInt(card.dataset.likes || '0', 10);
            const dislikes = parseInt(card.dataset.dislikes || '0', 10);
            const matchedTerms = [];
            let score = 0;

            projectTokens.forEach(function(token) {
                if (skillText.includes(token)) {
                    score += 12;
                    matchedTerms.push(token);
                } else if (experienceText.includes(token)) {
                    score += 8;
                    matchedTerms.push(token);
                } else if (bioText.includes(token)) {
                    score += 6;
                    matchedTerms.push(token);
                } else if (allText.includes(token)) {
                    score += 3;
                    matchedTerms.push(token);
                }
            });

            if (preferredLevel === 'expert' && niveauText.includes('expert')) score += 16;
            if (preferredLevel === 'interm' && (niveauText.includes('inter') || niveauText.includes('avance'))) score += 14;
            if (preferredLevel === 'beginner' && (niveauText.includes('debut') || niveauText.includes('début'))) score += 10;

            if (urgency === 'fast' && (availabilityText.includes('available') || availabilityText.includes('full'))) score += 14;
            if (urgency === 'quality') score += Math.min(20, rating * 4);
            if (urgency === 'budget' && !niveauText.includes('expert')) score += 8;

            score += Math.min(18, rating * 3);
            score += Math.min(10, likes * 1.5);
            score -= Math.min(8, dislikes * 1.2);

            const uniqueTerms = Array.from(new Set(matchedTerms)).slice(0, 5);
            const confidence = Math.max(0, Math.min(98, Math.round(score)));
            const reasons = [];

            if (uniqueTerms.length > 0) {
                reasons.push('Matched: ' + uniqueTerms.join(', '));
            }
            if (rating >= 4) {
                reasons.push('strong rating');
            }
            if (availabilityText.includes('available') || availabilityText.includes('full')) {
                reasons.push('good availability');
            }
            if (preferredLevel && score > 0) {
                reasons.push('level fit');
            }

            return {
                card: card,
                score: score,
                confidence: confidence,
                reasons: reasons.length ? reasons.join(' • ') : 'Closest available profile based on rating and profile completeness.'
            };
        }

        function clearAiMatchHighlights(restoreOrder) {
            if (freelancersGrid) {
                freelancersGrid.querySelectorAll('.freelancer-card').forEach(function(card) {
                    card.classList.remove('ai-top-match');
                    card.querySelectorAll('.match-rank-badge').forEach(function(badge) {
                        badge.remove();
                    });
                });

                if (restoreOrder) {
                    originalFreelancerOrder.forEach(function(card) {
                        freelancersGrid.appendChild(card);
                    });
                }
            }

            if (aiMatchResults) {
                aiMatchResults.classList.remove('is-visible');
            }
            if (aiMatchList) {
                aiMatchList.innerHTML = '';
            }
            if (aiMatchEmpty) {
                aiMatchEmpty.classList.remove('is-visible');
            }
        }

        function runAiMatchAssistant() {
            if (!freelancersGrid || !aiProjectBrief || !aiMatchResults || !aiMatchList) {
                return;
            }

            const projectText = aiProjectBrief.value.trim();
            const projectTokens = tokenizeMatchText(projectText);
            const visibleCards = Array.from(freelancersGrid.querySelectorAll('.freelancer-card'))
                .filter(function(card) {
                    return card.style.display !== 'none';
                });

            clearAiMatchHighlights(false);
            aiMatchResults.classList.add('is-visible');

            if (projectTokens.length < 2 || visibleCards.length === 0) {
                aiMatchEmpty.classList.add('is-visible');
                showAiRobot('Tell me a bit more about the project first', false);
                return;
            }

            const preferredLevel = aiPreferredLevel ? aiPreferredLevel.value : '';
            const urgency = aiUrgency ? aiUrgency.value : '';
            const ranked = visibleCards
                .map(function(card) {
                    return scoreFreelancerCard(card, projectTokens, preferredLevel, urgency);
                })
                .sort(function(a, b) {
                    return b.score - a.score;
                });

            const topMatches = ranked.filter(function(item) {
                return item.score > 0;
            }).slice(0, 3);

            if (topMatches.length === 0) {
                aiMatchEmpty.classList.add('is-visible');
                showAiRobot('I need a little more detail to find a confident match', false);
                return;
            }

            showAiRobot('I found your top 3 matches', true);

            ranked.forEach(function(item) {
                freelancersGrid.appendChild(item.card);
            });

            topMatches.forEach(function(match, index) {
                const badge = document.createElement('div');
                badge.className = 'match-rank-badge';
                badge.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i> AI Pick #' + (index + 1);
                match.card.prepend(badge);
                match.card.classList.add('ai-top-match');

                const resultCard = document.createElement('button');
                resultCard.type = 'button';
                resultCard.className = 'ai-match-result-card text-start';
                const resultName = document.createElement('strong');
                resultName.textContent = match.card.dataset.name || 'Freelancer';
                const resultScore = document.createElement('span');
                const resultIcon = document.createElement('i');
                resultIcon.className = 'fas fa-gauge-high';
                resultScore.appendChild(resultIcon);
                resultScore.appendChild(document.createTextNode(match.confidence + '% match'));
                const resultReason = document.createElement('p');
                resultReason.textContent = match.reasons;
                resultCard.appendChild(resultName);
                resultCard.appendChild(resultScore);
                resultCard.appendChild(resultReason);
                resultCard.addEventListener('click', function() {
                    match.card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    match.card.classList.add('is-active');
                    setTimeout(function() {
                        match.card.classList.remove('is-active');
                    }, 1200);
                });
                aiMatchList.appendChild(resultCard);
            });
        }

        if (freelancersGrid) {
            originalFreelancerOrder = Array.from(freelancersGrid.querySelectorAll('.freelancer-card'));
        }

        if (runAiMatchBtn) {
            runAiMatchBtn.addEventListener('click', function() {
                setAiMatchOpen(true);
                runAiMatchAssistant();
            });
        }

        if (clearAiMatchBtn) {
            clearAiMatchBtn.addEventListener('click', function() {
                if (aiProjectBrief) aiProjectBrief.value = '';
                if (aiPreferredLevel) aiPreferredLevel.value = '';
                if (aiUrgency) aiUrgency.value = '';
                clearAiMatchHighlights(true);
                applyFreelancerFilters();
            });
        }

        function updateModalInteractionState(card) {
            const currentInteraction = card.dataset.currentInteraction || 'none';
            modalLikeButton.classList.toggle('active', currentInteraction === 'like');
            modalDislikeButton.classList.toggle('active', currentInteraction === 'dislike');
        }

        function openFreelancerModal(button) {
            const card = button.closest('.freelancer-card');
            activeFreelancerCard = card;

            document.querySelectorAll('.freelancer-card.is-active').forEach(function(activeCard) {
                activeCard.classList.remove('is-active');
            });
            card.classList.add('is-active');

            const imagePath = card.dataset.profilePicture || '';
            if (imagePath !== '') {
                modalFreelancerAvatar.innerHTML = '<img src="' + imagePath + '" alt="' + card.dataset.name + '">';
            } else {
                modalFreelancerAvatar.textContent = card.dataset.avatar || '';
            }

            modalFreelancerName.textContent = card.dataset.name || '';
            modalFreelancerRole.textContent = card.dataset.niveau || '';
            modalFreelancerAvailability.textContent = card.dataset.availability || '';
            modalFreelancerRating.textContent = (card.dataset.rating || '0') + '/5';
            modalFreelancerSummary.textContent = card.dataset.skillSummary || '';
            modalFreelancerPhone.textContent = card.dataset.phone || '';
            modalFreelancerSkillSummary.textContent = card.dataset.skillSummary || '';
            modalFreelancerBio.innerHTML = formatTextWithBreaks(card.dataset.bio || '');
            modalFreelancerExperience.innerHTML = formatTextWithBreaks(card.dataset.experience || '');
            modalFreelancerFeedback.textContent = 'Feedback: ' + (card.dataset.likes || '0') + ' likes • ' + (card.dataset.dislikes || '0') + ' dislikes';

            modalFreelancerFeedback.innerHTML = '<i class="fas fa-thumbs-up"></i> ' + (card.dataset.likes || '0') + ' likes <i class="fas fa-thumbs-down ms-2"></i> ' + (card.dataset.dislikes || '0') + ' dislikes';
            modalLikeButton.dataset.freelancer = card.dataset.id || '';
            modalDislikeButton.dataset.freelancer = card.dataset.id || '';
            updateModalInteractionState(card);

            freelancerModal.classList.add('is-visible');
            freelancerModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open-soft');
        }

        function closeFreelancerModal() {
            freelancerModal.classList.remove('is-visible');
            freelancerModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open-soft');

            if (activeFreelancerCard) {
                activeFreelancerCard.classList.remove('is-active');
                activeFreelancerCard = null;
            }
        }

        if (closeFreelancerModalButton) {
            closeFreelancerModalButton.addEventListener('click', closeFreelancerModal);
        }

        if (freelancerModal) {
            freelancerModal.addEventListener('click', function(event) {
                if (event.target === freelancerModal) {
                    closeFreelancerModal();
                }
            });
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && freelancerModal.classList.contains('is-visible')) {
                closeFreelancerModal();
            }
        });

        applyFreelancerFilters();

        function toggleInteraction(freelancerId, type) {
            const formData = new FormData();
            formData.append('action', type);
            formData.append('freelancer_id', freelancerId);

            fetch('?action=freelancers', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        if (modalLikeButton) {
            modalLikeButton.addEventListener('click', function() {
                if (this.dataset.freelancer) {
                    toggleInteraction(this.dataset.freelancer, 'like');
                }
            });
        }

        if (modalDislikeButton) {
            modalDislikeButton.addEventListener('click', function() {
                if (this.dataset.freelancer) {
                    toggleInteraction(this.dataset.freelancer, 'dislike');
                }
            });
        }
    </script>
</body>
</html>
