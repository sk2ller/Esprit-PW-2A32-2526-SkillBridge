<?php
/**
 * chat_api.php — SkillBridge
 * Secured REST API for client-freelancer messaging
 */
session_start();
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

// ── Security: require authenticated session ──
if (empty($_SESSION['user']['email'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié. Veuillez vous connecter.']);
    exit;
}

$db = Config::getConnexion();
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$sessionEmail = $_SESSION['user']['email']; // Trusted source

switch ($action) {

    // Send a message
    case 'send':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'POST required']); exit;
        }
        // Use session email as sender — prevents impersonation
        $sender = $sessionEmail;
        $receiver = trim($_POST['receiver_email'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $idProduit = !empty($_POST['id_produit']) ? (int)$_POST['id_produit'] : null;

        if (empty($receiver) || empty($message)) {
            echo json_encode(['error' => 'Missing fields']); exit;
        }

        // Limit message length (max 2000 chars)
        if (mb_strlen($message) > 2000) {
            echo json_encode(['error' => 'Message trop long (max 2000 caractères).']); exit;
        }

        // Prevent sending to yourself
        if ($sender === $receiver) {
            echo json_encode(['error' => 'Vous ne pouvez pas vous envoyer un message.']); exit;
        }

        try {
            $sql = "INSERT INTO chat_messages (sender_email, receiver_email, id_produit, message) VALUES (:s, :r, :p, :m)";
            $q = $db->prepare($sql);
            $q->execute([':s' => $sender, ':r' => $receiver, ':p' => $idProduit, ':m' => htmlspecialchars($message)]);
            echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erreur serveur lors de l\'envoi.']);
        }
        break;

    // Fetch messages between two users (optionally for a product)
    case 'fetch':
        $user1 = $sessionEmail; // Always use session email
        $user2 = $_GET['user2'] ?? '';
        $idProduit = $_GET['id_produit'] ?? null;
        $after = (int)($_GET['after'] ?? 0);

        if (empty($user2)) {
            echo json_encode(['error' => 'Destinataire manquant.']); exit;
        }

        $sql = "SELECT * FROM chat_messages 
                WHERE ((sender_email = :u1 AND receiver_email = :u2) OR (sender_email = :u2b AND receiver_email = :u1b))";
        $params = [':u1' => $user1, ':u2' => $user2, ':u2b' => $user2, ':u1b' => $user1];

        if ($idProduit) {
            $sql .= " AND id_produit = :pid";
            $params[':pid'] = (int)$idProduit;
        }
        if ($after > 0) {
            $sql .= " AND id > :after";
            $params[':after'] = $after;
        }
        $sql .= " ORDER BY created_at ASC";

        try {
            $q = $db->prepare($sql);
            $q->execute($params);
            $msgs = $q->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'messages' => $msgs]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erreur serveur.']);
        }
        break;

    // Get all conversations for current user (grouped by other user + product)
    case 'conversations':
        $vendorEmail = $sessionEmail; // Always use session email
        try {
            $sql = "SELECT cm.sender_email, cm.receiver_email, cm.id_produit, p.nom as nom_produit,
                           MAX(cm.created_at) as last_msg_time,
                           (SELECT message FROM chat_messages cm2 WHERE 
                            ((cm2.sender_email = cm.sender_email AND cm2.receiver_email = cm.receiver_email) OR
                             (cm2.sender_email = cm.receiver_email AND cm2.receiver_email = cm.sender_email))
                            AND cm2.id_produit = cm.id_produit
                            ORDER BY cm2.created_at DESC LIMIT 1) as last_message,
                           SUM(CASE WHEN cm.receiver_email = :ve AND cm.is_read = 0 THEN 1 ELSE 0 END) as unread_count
                    FROM chat_messages cm
                    LEFT JOIN produit p ON cm.id_produit = p.id_produit
                    WHERE cm.sender_email = :ve2 OR cm.receiver_email = :ve3
                    GROUP BY LEAST(cm.sender_email, cm.receiver_email), GREATEST(cm.sender_email, cm.receiver_email), cm.id_produit
                    ORDER BY last_msg_time DESC";
            $q = $db->prepare($sql);
            $q->execute([':ve' => $vendorEmail, ':ve2' => $vendorEmail, ':ve3' => $vendorEmail]);
            $convos = $q->fetchAll(PDO::FETCH_ASSOC);
            
            // Determine the "other" person for each convo
            foreach ($convos as &$c) {
                $c['client_email'] = ($c['sender_email'] === $vendorEmail) ? $c['receiver_email'] : $c['sender_email'];
            }
            echo json_encode(['success' => true, 'conversations' => $convos]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erreur serveur.']);
        }
        break;

    // Mark messages as read
    case 'mark_read':
        $myEmail = $sessionEmail; // Always use session email
        $otherEmail = $_POST['other_email'] ?? '';
        $idProduit = $_POST['id_produit'] ?? null;

        if (empty($otherEmail)) {
            echo json_encode(['error' => 'Destinataire manquant.']); exit;
        }

        try {
            $sql = "UPDATE chat_messages SET is_read = 1 WHERE receiver_email = :me AND sender_email = :other";
            $params = [':me' => $myEmail, ':other' => $otherEmail];
            if ($idProduit) {
                $sql .= " AND id_produit = :pid";
                $params[':pid'] = (int)$idProduit;
            }
            $q = $db->prepare($sql);
            $q->execute($params);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erreur serveur.']);
        }
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}
?>
