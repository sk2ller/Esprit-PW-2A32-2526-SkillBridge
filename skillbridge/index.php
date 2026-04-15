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
    case 'brainstorming_add':
        require 'Views/Frontoffice/addBrainstorming.php';
        break;
    case 'brainstorming_list':
        require 'Views/Frontoffice/brainstormingList.php';
        break;
    case 'brainstorming_admin':
        require 'Views/Backoffice/brainstormingAdmin.php';
        break;
    case 'export_brainstorming_excel':
        require 'Views/Backoffice/exportBrainstormingExcel.php';
        break;
    case 'home':
    default:
        require 'Views/Frontoffice/home.php';
        break;
}
?>
