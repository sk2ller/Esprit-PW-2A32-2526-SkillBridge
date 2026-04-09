<?php
declare(strict_types=1);

session_start();

// Routing simple
$space = $_GET['space'] ?? 'front';
$action = $_GET['action'] ?? 'list';

// Inclusion du modèle et du contrôleur
require_once __DIR__ . '/../modele/Database.php';
require_once __DIR__ . '/../modele/Commande.php';
require_once __DIR__ . '/../modele/CommandeRepository.php';
require_once __DIR__ . '/../modele/CommandeValidator.php';
require_once __DIR__ . '/CommandeController.php';

use App\Modele\Database;
use App\Modele\CommandeRepository;
use App\Controller\CommandeController;

// Initialisation DB et contrôleur
$db = Database::getInstance();
$repository = new CommandeRepository($db);
$controller = new CommandeController($repository);

// Routage actions
match ($action) {
    'list' => $controller->list($space),
    'create' => $controller->create($space),
    'store' => $controller->store($space, $_POST),
    'edit' => $controller->edit($space, (int)($_GET['id'] ?? 0)),
    'update' => $controller->update($space, (int)($_GET['id'] ?? 0), $_POST),
    'delete' => $controller->delete($space, (int)($_GET['id'] ?? 0), $_POST),
    default => $controller->list($space),
};
