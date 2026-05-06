<?php
session_start();
require_once 'config.php';
require_once 'Controllers/MessageController.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non connecté', 'session' => $_SESSION]);
    exit;
}

$mc = new MessageController();
$result = $mc->getNotifications($_SESSION['user_id']);

// Aussi vérifier les messages en base
$db = Config::getConnexion();

$q = $db->prepare("SELECT m.*, u.nom, u.prenom FROM message m JOIN user u ON u.id = m.id_expediteur ORDER BY m.id DESC LIMIT 10");
$q->execute();
$messages = $q->fetchAll(PDO::FETCH_ASSOC);

$q2 = $db->prepare("SELECT * FROM message_lu WHERE id_user = :u");
$q2->execute(['u' => $_SESSION['user_id']]);
$lus = $q2->fetchAll(PDO::FETCH_ASSOC);

$q3 = $db->prepare("SELECT id, titre, id_client FROM projet LIMIT 10");
$q3->execute();
$projets = $q3->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'user_id'       => $_SESSION['user_id'],
    'user_role'     => $_SESSION['user_role'],
    'notifications' => $result,
    'messages_db'   => $messages,
    'message_lu_db' => $lus,
    'projets_db'    => $projets,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
