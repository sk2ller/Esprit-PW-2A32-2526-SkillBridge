-- =================================================
-- SkillBridge — Table Utilisateurs (Auth System)
-- À importer dans phpMyAdmin après produit.sql
-- =================================================

USE skillbridge;

-- Table Users pour l'authentification
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('client', 'vendeur', 'admin') NOT NULL DEFAULT 'client',
    skills TEXT DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    portfolio_url VARCHAR(255) DEFAULT NULL,
    face_descriptor LONGTEXT DEFAULT NULL,
    remember_token VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Index pour accélérer les recherches par email
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);

-- Utilisateur admin par défaut (mot de passe: admin123)
-- Le hash est généré avec password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (name, email, password, role) VALUES
('Administrateur', 'admin@skillbridge.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Utilisateurs de test
INSERT INTO users (name, email, password, role, skills, bio, portfolio_url) VALUES
('Ahmed Ben Ali', 'ahmed@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', NULL, NULL, NULL),
('Fatma Trabelsi', 'fatma@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendeur', 'HTML,CSS,JavaScript,React,UI Design', 'Développeuse web passionnée spécialisée dans le design moderne.', 'https://fatma-portfolio.com');
