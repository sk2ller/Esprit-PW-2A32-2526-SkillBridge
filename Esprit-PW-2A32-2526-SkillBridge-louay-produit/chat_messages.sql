-- =================================================
-- SkillBridge — Table Messages (Chat System)
-- À importer dans phpMyAdmin après users.sql
-- =================================================

USE skillbridge;

-- Table chat_messages pour la messagerie client-freelancer
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_email VARCHAR(150) NOT NULL,
    receiver_email VARCHAR(150) NOT NULL,
    id_produit INT DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sender (sender_email),
    INDEX idx_receiver (receiver_email),
    INDEX idx_produit (id_produit),
    INDEX idx_created (created_at),
    FOREIGN KEY (id_produit) REFERENCES produit(id_produit) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Données de test - Messages
INSERT INTO chat_messages (sender_email, receiver_email, id_produit, message, is_read) VALUES
('ahmed@test.com', 'fatma@test.com', 1, 'Bonjour, est-ce que ce template est compatible avec React ?', 1),
('fatma@test.com', 'ahmed@test.com', 1, 'Oui absolument ! Il utilise des composants React modernes avec hooks.', 1),
('ahmed@test.com', 'fatma@test.com', 1, 'Parfait, je vais l''acheter. Merci !', 0);
