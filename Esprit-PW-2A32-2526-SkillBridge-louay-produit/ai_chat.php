<?php
/**
 * ai_chat.php — SkillBridge AI Chat Endpoint
 * API endpoint pour le widget de chat IA
 * Reçoit : POST { message, role, context, history }
 * Retourne : JSON { success, response, error }
 */

// Headers pour l'API JSON
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// N'accepter que les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'response' => '', 'error' => 'Method not allowed']);
    exit;
}

// Démarrer la session pour vérifier l'authentification (optionnel)
session_start();

// Charger le contrôleur AI
require_once __DIR__ . '/controllers/AiAssistantController.php';

// Lire le corps de la requête JSON
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// Valider l'entrée
if (!$input || empty($input['message'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'response' => '', 'error' => 'Message is required']);
    exit;
}

// Extraire les données
$message = trim($input['message']);
$role = $input['role'] ?? 'client';
$context = $input['context'] ?? '';
$history = $input['history'] ?? [];

// Validation du rôle
$validRoles = ['client', 'vendeur', 'admin'];
if (!in_array($role, $validRoles)) {
    $role = 'client';
}

// Limiter la taille du message (prévenir les abus)
if (strlen($message) > 2000) {
    $message = substr($message, 0, 2000);
}

// Limiter l'historique à 10 messages
if (count($history) > 10) {
    $history = array_slice($history, -10);
}

// Convertir le contexte en chaîne si c'est un objet/tableau
if (is_array($context)) {
    $context = json_encode($context, JSON_UNESCAPED_UNICODE);
}

// Appeler le contrôleur AI
$aiCtrl = new AiAssistantController();
$result = $aiCtrl->chat($message, $role, $context, $history);

// Retourner la réponse
echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>
