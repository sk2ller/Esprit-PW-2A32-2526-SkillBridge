<?php
// Simple Router
session_start();

$request = $_GET['action'] ?? 'home';
$method = $_GET['method'] ?? 'view';

// Handle logout
if ($request === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    setcookie('jwt', '', time() - 3600, '/');
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
    case 'myrating':
        require 'Views/Frontoffice/myrating.php';
        break;
    case 'userlist':
        require 'Views/Backoffice/userList.php';
        break;
    case 'statistics':
        require 'Views/Backoffice/statistics.php';
        break;
    case 'freelancers':
        require 'Views/Frontoffice/freelancers.php';
        break;
    case 'home':
    default:
        require 'Views/Frontoffice/Home.php';
        break;
}
?>
