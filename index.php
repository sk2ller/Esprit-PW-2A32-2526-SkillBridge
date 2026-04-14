<?php
require_once __DIR__ . '/controllers/ProjetController.php';

$controller = new ProjetController();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

switch ($action) {
    case 'list':
        $controller->listAction();
        break;
    case 'add':
        $controller->addAction();
        break;
    case 'edit':
        $controller->editAction();
        break;
    case 'delete':
        $controller->deleteAction();
        break;
    default:
        $controller->listAction();
        break;
}
