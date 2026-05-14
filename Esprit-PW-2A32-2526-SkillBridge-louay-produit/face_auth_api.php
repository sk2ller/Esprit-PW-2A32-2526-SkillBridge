<?php
/**
 * face_auth_api.php — SkillBridge
 * Endpoint AJAX pour l'authentification faciale
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$email = $input['email'] ?? '';
$descriptor = $input['descriptor'] ?? null;
$password = $input['password'] ?? ''; // Only needed for registration to verify identity

if (empty($action) || empty($email) || empty($descriptor)) {
    echo json_encode(['success' => false, 'error' => 'Données manquantes (action, email, ou descriptor).']);
    exit;
}

$authCtrl = new AuthController();

if ($action === 'register') {
    if (empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Le mot de passe est requis pour enregistrer un visage.']);
        exit;
    }
    
    // First, verify credentials
    $loginResult = $authCtrl->login($email, $password);
    if (!$loginResult['success']) {
        echo json_encode(['success' => false, 'error' => implode(" ", $loginResult['errors'])]);
        exit;
    }

    // If valid, save the descriptor
    $descriptorJson = is_array($descriptor) ? json_encode($descriptor) : $descriptor;
    $result = $authCtrl->registerFace($email, $descriptorJson);
    
    echo json_encode($result);
    exit;

} elseif ($action === 'login') {
    // Descriptor should be JSON string or array
    $descriptorJson = is_array($descriptor) ? json_encode($descriptor) : $descriptor;
    
    $result = $authCtrl->loginFace($email, $descriptorJson);
    
    if ($result['success']) {
        $authCtrl->createSession($result['user']);
        $url = AuthController::getDashboardUrl($result['user']->getRole());
        echo json_encode(['success' => true, 'redirect' => $url]);
    } else {
        echo json_encode(['success' => false, 'error' => $result['error']]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Action inconnue.']);
