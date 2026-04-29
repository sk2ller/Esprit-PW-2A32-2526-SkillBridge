<?php
// Simple Router
session_start();
require_once 'config.php';

$request = $_GET['action'] ?? 'home';
$method = $_GET['method'] ?? 'view';

// Handle logout
if ($request === 'logout') {
    session_destroy();
    header('Location: ?action=home');
    exit;
}

// Route handling
switch ($request) {
    case 'login':
        // Si déjà connecté, rediriger vers la bonne page
        if (isset($_SESSION['user_id'])) {
            if ($_SESSION['user_role'] == 1)      header('Location: ?action=userlist');
            elseif ($_SESSION['user_role'] == 3)  header('Location: ?action=mes_projets');
            else                                   header('Location: ?action=mes_projets_client');
            exit;
        }
        require 'Views/Frontoffice/login.php';
        break;
    case 'projects':
        require 'Views/Frontoffice/projects.php';
        break;
    case 'paiement':
        require 'Views/Frontoffice/paiement.php';
        break;
    case 'register':
        require 'Views/Frontoffice/register.php';
        break;
    case 'profile':
        require 'Views/Frontoffice/profile.php';
        break;
    case 'mes_projets':
        require 'Views/Frontoffice/mes_projets.php';
        break;
    case 'mes_projets_client':
        require 'Views/Frontoffice/mes_projets_client.php';
        break;
    case 'candidatures':
        require 'Views/Backoffice/candidatures.php';
        break;
    case 'userlist':
        require 'Views/Backoffice/userList.php';
        break;
    case 'projectlist':
        require 'Views/Backoffice/projectList.php';
        break;
    case 'adduser':
        require 'Views/Backoffice/addUser.php';
        break;
    case 'edituser':
        require 'Views/Backoffice/editUser.php';
        break;
    case 'home':
    default:
        if (isset($_SESSION['user_id'])) {
            if ($_SESSION['user_role'] == 1)     { header('Location: ?action=userlist'); exit; }
            else                                  { header('Location: ?action=projects&tab=mes'); exit; }
        }
        require 'Views/Frontoffice/home.php';
        break;
}
?>
