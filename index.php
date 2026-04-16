<?php
// Simple Router
session_start();

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
        require 'Views/Frontoffice/login.php';
        break;
    case 'register':
        require 'Views/Frontoffice/register.php';
        break;
    case 'profile':
        require 'Views/Frontoffice/profile.php';
        break;
    case 'projects':
        require 'Views/Frontoffice/projects.php';
        break;
    case 'projectdashboard':
        require 'Views/Backoffice/projectDashboard.php';
        break;
    case 'projectlist':
        require 'Views/Backoffice/projectList.php';
        break;
    case 'userlist':
        require 'Views/Backoffice/userList.php';
        break;
    case 'adduser':
        require 'Views/Backoffice/addUser.php';
        break;
    case 'edituser':
        require 'Views/Backoffice/editUser.php';
        break;
    case 'home':
    default:
        require 'Views/Frontoffice/home.php';
        break;
}
?>
