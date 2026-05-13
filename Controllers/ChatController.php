<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/ServiceController.php';

class ChatController
{
    private $db;
    private $serviceController;

    public function __construct()
    {
        $this->db = Config::getConnexion();
        $this->serviceController = new ServiceController();
        $this->ensureChatSchema();
    }

    private function ensureChatSchema()
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS conversations (
                id_conversation INT AUTO_INCREMENT PRIMARY KEY,
                id_service INT NULL,
                id_offre INT NULL,
                conversation_type ENUM('service', 'job') DEFAULT 'service',
                id_client INT NOT NULL,
                id_freelancer INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_conversation (id_service, id_client, id_freelancer),
                CONSTRAINT fk_conversation_service FOREIGN KEY (id_service) REFERENCES services(id_service) ON DELETE CASCADE,
                CONSTRAINT fk_conversation_client FOREIGN KEY (id_client) REFERENCES User(id) ON DELETE CASCADE,
                CONSTRAINT fk_conversation_freelancer FOREIGN KEY (id_freelancer) REFERENCES User(id) ON DELETE CASCADE
            )
        ");

        $this->ensureConversationColumn('id_service', "ALTER TABLE conversations MODIFY id_service INT NULL");
        $this->ensureConversationColumn('id_offre', "ALTER TABLE conversations ADD COLUMN id_offre INT NULL AFTER id_service");
        $this->ensureConversationColumn('conversation_type', "ALTER TABLE conversations ADD COLUMN conversation_type ENUM('service', 'job') DEFAULT 'service' AFTER id_offre");

        try {
            $this->db->exec("ALTER TABLE conversations ADD CONSTRAINT fk_conversation_offer FOREIGN KEY (id_offre) REFERENCES offre_job(id_offre) ON DELETE CASCADE");
        } catch (PDOException $e) {
        }

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS messages (
                id_message INT AUTO_INCREMENT PRIMARY KEY,
                id_conversation INT NOT NULL,
                sender_id INT NOT NULL,
                sender_role VARCHAR(30) NOT NULL,
                sender_name VARCHAR(120) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_message_conversation FOREIGN KEY (id_conversation) REFERENCES conversations(id_conversation) ON DELETE CASCADE,
                CONSTRAINT fk_message_sender FOREIGN KEY (sender_id) REFERENCES User(id) ON DELETE CASCADE
            )
        ");
    }

    private function ensureConversationColumn($column, $sql)
    {
        $stmt = $this->db->prepare("SHOW COLUMNS FROM conversations LIKE ?");
        $stmt->execute([$column]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->db->exec($sql);
        } elseif ($column === 'id_service') {
            try {
                $this->db->exec($sql);
            } catch (PDOException $e) {
            }
        }
    }

    private function requireChatAccess()
    {
        $role = (int)($_SESSION['user_role'] ?? 0);
        if (!isset($_SESSION['user_id']) || !in_array($role, [2, 3], true)) {
            header('Location: ?action=login');
            exit;
        }
    }

    private function getRoleKey()
    {
        return (int)$_SESSION['user_role'] === 3 ? 'freelancer' : 'client';
    }

    private function getDisplayName()
    {
        return trim(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? ''));
    }

    private function getConversationById($conversationId)
    {
        $sql = "SELECT c.*,
                       COALESCE(s.titre, o.titre) AS titre,
                       s.thumbnail,
                       s.id_service,
                       o.id_offre,
                       client.nom AS client_nom, client.prenom AS client_prenom,
                       freelancer.nom AS freelancer_nom, freelancer.prenom AS freelancer_prenom
                FROM conversations c
                LEFT JOIN services s ON s.id_service = c.id_service
                LEFT JOIN offre_job o ON o.id_offre = c.id_offre
                JOIN User client ON client.id = c.id_client
                JOIN User freelancer ON freelancer.id = c.id_freelancer
                WHERE c.id_conversation = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$conversationId]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$conversation) {
            return null;
        }

        $currentUserId = (int)$_SESSION['user_id'];
        if ($currentUserId !== (int)$conversation['id_client'] && $currentUserId !== (int)$conversation['id_freelancer']) {
            return null;
        }

        return $conversation;
    }

    private function getMessagesByConversation($conversationId)
    {
        $stmt = $this->db->prepare("SELECT * FROM messages WHERE id_conversation = ? ORDER BY created_at ASC, id_message ASC");
        $stmt->execute([$conversationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getInboxConversations()
    {
        $currentUserId = (int)$_SESSION['user_id'];
        $role = $this->getRoleKey();

        if ($role === 'freelancer') {
            $sql = "SELECT c.*, COALESCE(s.titre, o.titre) AS titre, s.thumbnail,
                           client.nom AS client_nom, client.prenom AS client_prenom,
                           freelancer.nom AS freelancer_nom, freelancer.prenom AS freelancer_prenom,
                           (SELECT message FROM messages m WHERE m.id_conversation = c.id_conversation ORDER BY m.id_message DESC LIMIT 1) AS last_message,
                           (SELECT created_at FROM messages m WHERE m.id_conversation = c.id_conversation ORDER BY m.id_message DESC LIMIT 1) AS last_message_at
                    FROM conversations c
                    LEFT JOIN services s ON s.id_service = c.id_service
                    LEFT JOIN offre_job o ON o.id_offre = c.id_offre
                    JOIN User client ON client.id = c.id_client
                    JOIN User freelancer ON freelancer.id = c.id_freelancer
                    WHERE c.id_freelancer = ?
                    ORDER BY COALESCE(last_message_at, c.updated_at) DESC";
        } else {
            $sql = "SELECT c.*, COALESCE(s.titre, o.titre) AS titre, s.thumbnail,
                           client.nom AS client_nom, client.prenom AS client_prenom,
                           freelancer.nom AS freelancer_nom, freelancer.prenom AS freelancer_prenom,
                           (SELECT message FROM messages m WHERE m.id_conversation = c.id_conversation ORDER BY m.id_message DESC LIMIT 1) AS last_message,
                           (SELECT created_at FROM messages m WHERE m.id_conversation = c.id_conversation ORDER BY m.id_message DESC LIMIT 1) AS last_message_at
                    FROM conversations c
                    LEFT JOIN services s ON s.id_service = c.id_service
                    LEFT JOIN offre_job o ON o.id_offre = c.id_offre
                    JOIN User client ON client.id = c.id_client
                    JOIN User freelancer ON freelancer.id = c.id_freelancer
                    WHERE c.id_client = ?
                    ORDER BY COALESCE(last_message_at, c.updated_at) DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$currentUserId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function findOrCreateConversation($serviceId)
    {
        $currentUserId = (int)$_SESSION['user_id'];
        $role = $this->getRoleKey();

        if ($role !== 'client') {
            return null;
        }

        $serviceResult = $this->serviceController->getServiceById($serviceId);
        $service = $serviceResult['data'] ?? null;
        if (!$service) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT * FROM conversations WHERE id_service = ? AND id_client = ? AND id_freelancer = ? LIMIT 1");
        $stmt->execute([$serviceId, $currentUserId, $service['id_freelancer']]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($conversation) {
            return $this->getConversationById($conversation['id_conversation']);
        }

        $insert = $this->db->prepare("INSERT INTO conversations (id_service, id_client, id_freelancer) VALUES (?, ?, ?)");
        $insert->execute([$serviceId, $currentUserId, $service['id_freelancer']]);

        return $this->getConversationById((int)$this->db->lastInsertId());
    }

    private function findOrCreateJobConversation($offerId, $freelancerId = null)
    {
        $currentUserId = (int)$_SESSION['user_id'];
        $role = $this->getRoleKey();

        $offerStmt = $this->db->prepare("SELECT * FROM offre_job WHERE id_offre = ?");
        $offerStmt->execute([(int)$offerId]);
        $offer = $offerStmt->fetch(PDO::FETCH_ASSOC);
        if (!$offer) {
            return null;
        }

        if ($role === 'freelancer') {
            $freelancerId = $currentUserId;
            $check = $this->db->prepare("SELECT id_candidature FROM candidature_offre WHERE id_offre = ? AND id_freelancer = ?");
            $check->execute([(int)$offerId, $freelancerId]);
            if (!$check->fetch(PDO::FETCH_ASSOC)) {
                return null;
            }
        } else {
            if ($currentUserId !== (int)$offer['id_client'] || !$freelancerId) {
                return null;
            }
        }

        $stmt = $this->db->prepare("SELECT * FROM conversations WHERE conversation_type = 'job' AND id_offre = ? AND id_client = ? AND id_freelancer = ? LIMIT 1");
        $stmt->execute([(int)$offerId, (int)$offer['id_client'], (int)$freelancerId]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($conversation) {
            return $this->getConversationById($conversation['id_conversation']);
        }

        $insert = $this->db->prepare("INSERT INTO conversations (id_service, id_offre, conversation_type, id_client, id_freelancer) VALUES (NULL, ?, 'job', ?, ?)");
        $insert->execute([(int)$offerId, (int)$offer['id_client'], (int)$freelancerId]);

        return $this->getConversationById((int)$this->db->lastInsertId());
    }

    public function chatPage($serviceId = null, $conversationId = null, $offerId = null, $freelancerId = null)
    {
        $this->requireChatAccess();

        $role = $this->getRoleKey();
        $displayName = $this->getDisplayName();
        $selectedConversation = null;

        if ($serviceId && $role === 'client') {
            $selectedConversation = $this->findOrCreateConversation((int)$serviceId);
        } elseif ($offerId) {
            $selectedConversation = $this->findOrCreateJobConversation((int)$offerId, $freelancerId ? (int)$freelancerId : null);
        } elseif ($conversationId) {
            $selectedConversation = $this->getConversationById((int)$conversationId);
        }

        $conversations = $this->getInboxConversations();

        if (!$selectedConversation && !empty($conversations)) {
            $selectedConversation = $conversations[0];
        }

        $messages = $selectedConversation ? $this->getMessagesByConversation((int)$selectedConversation['id_conversation']) : [];

        require __DIR__ . '/../Views/Frontoffice/chat.php';
    }

    public function sendMessage()
    {
        $this->requireChatAccess();

        header('Content-Type: application/json; charset=utf-8');

        $conversationId = (int)($_POST['conversation_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $conversation = $this->getConversationById($conversationId);

        if (!$conversation || $message === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Message invalide.']);
            return;
        }

        $senderRole = $this->getRoleKey();
        $senderName = $this->getDisplayName();
        $senderId = (int)$_SESSION['user_id'];

        $stmt = $this->db->prepare("INSERT INTO messages (id_conversation, sender_id, sender_role, sender_name, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$conversationId, $senderId, $senderRole, $senderName, $message]);

        $messageData = [
            'id_message' => (int)$this->db->lastInsertId(),
            'id_conversation' => $conversationId,
            'sender_id' => $senderId,
            'sender_role' => $senderRole,
            'sender_name' => $senderName,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ];

        echo json_encode(['success' => true, 'messageData' => $messageData]);
    }

    public function messagesJson($conversationId)
    {
        $this->requireChatAccess();
        header('Content-Type: application/json; charset=utf-8');

        $conversation = $this->getConversationById((int)$conversationId);
        if (!$conversation) {
            http_response_code(404);
            echo json_encode([]);
            return;
        }

        echo json_encode($this->getMessagesByConversation((int)$conversationId));
    }
}
?>
