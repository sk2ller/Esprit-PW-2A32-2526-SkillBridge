<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/ServiceController.php');

class ChatController
{
    private $serviceController;

    public function __construct()
    {
        $this->serviceController = new ServiceController();
        $this->ensureChatSchema();
    }

    private function ensureChatSchema()
    {
        $db = getDB();

        $db->query("
            CREATE TABLE IF NOT EXISTS conversations (
                id_conversation INT AUTO_INCREMENT PRIMARY KEY,
                id_service INT NOT NULL,
                client_name VARCHAR(120) NOT NULL,
                freelancer_name VARCHAR(120) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id_service) REFERENCES services(id_service) ON DELETE CASCADE
            )
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS messages (
                id_message INT AUTO_INCREMENT PRIMARY KEY,
                id_conversation INT NOT NULL,
                sender_role VARCHAR(30) NOT NULL,
                sender_name VARCHAR(120) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_conversation) REFERENCES conversations(id_conversation) ON DELETE CASCADE
            )
        ");
    }

    private function getCurrentRole()
    {
        return $_SESSION['role'] ?? 'client';
    }

    private function getDefaultNameForRole($role)
    {
        return $role === 'freelancer' ? 'Freelancer Demo' : 'Client Demo';
    }

    private function getCurrentDisplayName()
    {
        $role = $this->getCurrentRole();
        return trim($_SESSION['profile_name'] ?? $this->getDefaultNameForRole($role));
    }

    private function saveDisplayNameFromRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profile_name'])) {
            $name = trim($_POST['profile_name']);
            if ($name !== '') {
                $_SESSION['profile_name'] = mb_substr($name, 0, 120);
            }
        }
    }

    private function getConversationById($conversationId)
    {
        $db = getDB();
        $sql = "SELECT c.*, s.titre, s.thumbnail
                FROM conversations c
                JOIN services s ON s.id_service = c.id_service
                WHERE c.id_conversation = ?";
        $query = $db->prepare($sql);
        $query->bind_param("i", $conversationId);
        $query->execute();
        return $query->get_result()->fetch_assoc();
    }

    private function getMessagesByConversation($conversationId)
    {
        $db = getDB();
        $sql = "SELECT * FROM messages WHERE id_conversation = ? ORDER BY created_at ASC, id_message ASC";
        $query = $db->prepare($sql);
        $query->bind_param("i", $conversationId);
        $query->execute();
        return $query->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function getInboxConversations($role, $displayName)
    {
        $db = getDB();

        if ($role === 'freelancer') {
            $sql = "SELECT c.*, s.titre, s.thumbnail,
                           (SELECT message FROM messages m WHERE m.id_conversation = c.id_conversation ORDER BY m.id_message DESC LIMIT 1) AS last_message,
                           (SELECT created_at FROM messages m WHERE m.id_conversation = c.id_conversation ORDER BY m.id_message DESC LIMIT 1) AS last_message_at
                    FROM conversations c
                    JOIN services s ON s.id_service = c.id_service
                    WHERE c.freelancer_name = ?
                    ORDER BY COALESCE(last_message_at, c.updated_at) DESC";
        } else {
            $sql = "SELECT c.*, s.titre, s.thumbnail,
                           (SELECT message FROM messages m WHERE m.id_conversation = c.id_conversation ORDER BY m.id_message DESC LIMIT 1) AS last_message,
                           (SELECT created_at FROM messages m WHERE m.id_conversation = c.id_conversation ORDER BY m.id_message DESC LIMIT 1) AS last_message_at
                    FROM conversations c
                    JOIN services s ON s.id_service = c.id_service
                    WHERE c.client_name = ?
                    ORDER BY COALESCE(last_message_at, c.updated_at) DESC";
        }

        $query = $db->prepare($sql);
        $query->bind_param("s", $displayName);
        $query->execute();
        return $query->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function findOrCreateConversation($serviceId, $clientName, $freelancerName)
    {
        $db = getDB();
        $sql = "SELECT * FROM conversations WHERE id_service = ? AND client_name = ? LIMIT 1";
        $query = $db->prepare($sql);
        $query->bind_param("is", $serviceId, $clientName);
        $query->execute();
        $conversation = $query->get_result()->fetch_assoc();

        if ($conversation) {
            return $conversation;
        }

        $insert = $db->prepare("INSERT INTO conversations (id_service, client_name, freelancer_name) VALUES (?, ?, ?)");
        $insert->bind_param("iss", $serviceId, $clientName, $freelancerName);
        $insert->execute();

        return $this->getConversationById($db->insert_id);
    }

    public function chatPage($serviceId = null, $conversationId = null)
    {
        $this->saveDisplayNameFromRequest();

        $role = $this->getCurrentRole();
        $displayName = $this->getCurrentDisplayName();
        $selectedConversation = null;

        if ($role === 'client') {
            $service = $this->serviceController->getServiceById((int) $serviceId);
            if (!$service) {
                header("Location: index.php?page=services");
                exit;
            }

            $freelancerName = $service->getFreelancerName() ?: 'Freelancer Demo';
            $selectedConversation = $this->findOrCreateConversation($service->getId(), $displayName, $freelancerName);
        } elseif ($conversationId) {
            $selectedConversation = $this->getConversationById((int) $conversationId);
        }

        $conversations = $this->getInboxConversations($role, $displayName);

        if (!$selectedConversation && !empty($conversations)) {
            $selectedConversation = $conversations[0];
        }

        if ($selectedConversation) {
            $messages = $this->getMessagesByConversation((int) $selectedConversation['id_conversation']);
        } else {
            $messages = [];
        }

        $pageTitle = 'Chat - Geeks';
        require_once(__DIR__ . '/../views/FrontOffice/chat.php');
    }

    public function sendMessage()
    {
        header('Content-Type: application/json; charset=utf-8');

        $conversationId = (int) ($_POST['conversation_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $role = $this->getCurrentRole();
        $senderName = $this->getCurrentDisplayName();

        if ($conversationId <= 0 || $message === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Message invalide.']);
            return;
        }

        $db = getDB();
        $sql = "INSERT INTO messages (id_conversation, sender_role, sender_name, message) VALUES (?, ?, ?, ?)";
        $query = $db->prepare($sql);
        $query->bind_param("isss", $conversationId, $role, $senderName, $message);
        $query->execute();

        $messageData = [
            'id_message' => $db->insert_id,
            'id_conversation' => $conversationId,
            'sender_role' => $role,
            'sender_name' => $senderName,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ];

        echo json_encode(['success' => true, 'messageData' => $messageData]);
    }

    public function messagesJson($conversationId)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->getMessagesByConversation((int) $conversationId));
    }
}
