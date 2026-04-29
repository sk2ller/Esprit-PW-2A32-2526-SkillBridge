CREATE DATABASE IF NOT EXISTS `skillbridge`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE `skillbridge`;

CREATE TABLE IF NOT EXISTS `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `mot_de_passe` varchar(255) NOT NULL,
  `niveau` varchar(50) DEFAULT 'debutant',
  `id_role` int(11) DEFAULT 2,
  `badge_verifie` tinyint(1) DEFAULT 0,
  `is_approved` tinyint(1) DEFAULT 0,
  `is_banned` tinyint(1) DEFAULT 0,
  `availability` varchar(50) DEFAULT 'available',
  `rating` decimal(3,2) DEFAULT 0.00,
  `phone` varchar(30) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `skill_summary` varchar(255) DEFAULT NULL,
  `experience_description` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `User` (
  `id`, `nom`, `prenom`, `email`, `mot_de_passe`, `niveau`, `id_role`,
  `badge_verifie`, `is_approved`, `is_banned`, `availability`, `rating`,
  `phone`, `bio`, `skill_summary`, `experience_description`
) VALUES
(
  1, 'Admin', 'SkillBridge', 'admin@skillbridge.com',
  '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86.jsUGkmH',
  'expert', 1, 1, 1, 0, 'available', 5.00, NULL,
  'Compte administrateur principal.',
  'Gestion complete de la plateforme',
  'Administration de la plateforme'
),
(
  2, 'Client', 'Demo', 'client@skillbridge.com',
  '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86.jsUGkmH',
  'intermediaire', 2, 0, 1, 0, 'available', 0.00, '20000000',
  'Client de demonstration pour tester la consultation et les interactions.',
  NULL, NULL
),
(
  3, 'Mohamed', 'Freelancer', 'freelancer@skillbridge.com',
  '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86.jsUGkmH',
  'expert', 3, 1, 1, 0, 'available', 4.80, '21000000',
  'Freelancer specialise en developpement web et UI.',
  'React, PHP, UI Design',
  '5 ans d experience sur des projets web'
),
(
  4, 'Sarra', 'Creative', 'sarra@skillbridge.com',
  '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86.jsUGkmH',
  'avance', 3, 1, 1, 0, 'busy', 4.50, '22000000',
  'Freelancer specialisee en branding et design graphique.',
  'Branding, Photoshop, Illustrator',
  'Direction artistique et identite visuelle'
)
ON DUPLICATE KEY UPDATE
  `nom` = VALUES(`nom`),
  `prenom` = VALUES(`prenom`),
  `email` = VALUES(`email`);

CREATE TABLE IF NOT EXISTS `Interaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user_from` int(11) NOT NULL,
  `id_user_to` int(11) NOT NULL,
  `type` enum('like', 'dislike') DEFAULT 'like',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_interaction` (`id_user_from`, `id_user_to`),
  CONSTRAINT `fk_interaction_from`
    FOREIGN KEY (`id_user_from`) REFERENCES `User`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_interaction_to`
    FOREIGN KEY (`id_user_to`) REFERENCES `User`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `categorie` (
  `id_categorie` int(11) NOT NULL AUTO_INCREMENT,
  `nom_categorie` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icone` varchar(50) DEFAULT 'fas fa-folder',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `services` (
  `id_service` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `delai_livraison` int(11) NOT NULL COMMENT 'en jours',
  `statut` enum('actif','suspendu','en_attente') DEFAULT 'en_attente',
  `thumbnail` varchar(255) DEFAULT NULL,
  `id_categorie` int(11) NOT NULL,
  `id_freelancer` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_service`),
  KEY `idx_services_categorie` (`id_categorie`),
  KEY `idx_services_freelancer` (`id_freelancer`),
  CONSTRAINT `fk_services_categorie`
    FOREIGN KEY (`id_categorie`) REFERENCES `categorie`(`id_categorie`) ON DELETE CASCADE,
  CONSTRAINT `fk_services_freelancer`
    FOREIGN KEY (`id_freelancer`) REFERENCES `User`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `conversations` (
  `id_conversation` int(11) NOT NULL AUTO_INCREMENT,
  `id_service` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `id_freelancer` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_conversation`),
  UNIQUE KEY `unique_conversation` (`id_service`, `id_client`, `id_freelancer`),
  CONSTRAINT `fk_conversation_service`
    FOREIGN KEY (`id_service`) REFERENCES `services`(`id_service`) ON DELETE CASCADE,
  CONSTRAINT `fk_conversation_client`
    FOREIGN KEY (`id_client`) REFERENCES `User`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_conversation_freelancer`
    FOREIGN KEY (`id_freelancer`) REFERENCES `User`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `messages` (
  `id_message` int(11) NOT NULL AUTO_INCREMENT,
  `id_conversation` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_role` varchar(30) NOT NULL,
  `sender_name` varchar(120) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_message`),
  CONSTRAINT `fk_message_conversation`
    FOREIGN KEY (`id_conversation`) REFERENCES `conversations`(`id_conversation`) ON DELETE CASCADE,
  CONSTRAINT `fk_message_sender`
    FOREIGN KEY (`sender_id`) REFERENCES `User`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `offre_job` (
  `id_offre` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `budget` decimal(10,2) NOT NULL,
  `delai_jours` int(11) NOT NULL DEFAULT 7,
  `execution_mode` enum('full_project','milestone') DEFAULT 'full_project',
  `milestone_plan` text DEFAULT NULL,
  `niveau_requis` enum('debutant','intermediaire','expert') DEFAULT 'intermediaire',
  `competences_requises` text DEFAULT NULL,
  `statut` enum('actif','suspendu','en_attente','fermee') DEFAULT 'en_attente',
  `id_client` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_offre`),
  KEY `idx_offre_client` (`id_client`),
  CONSTRAINT `fk_offre_client_user`
    FOREIGN KEY (`id_client`) REFERENCES `User`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `candidature_offre` (
  `id_candidature` int(11) NOT NULL AUTO_INCREMENT,
  `id_offre` int(11) NOT NULL,
  `id_freelancer` int(11) NOT NULL,
  `message` text NOT NULL,
  `budget_propose` decimal(10,2) NOT NULL,
  `disponibilite_jours` int(11) NOT NULL,
  `execution_mode` enum('full_project','milestone') DEFAULT 'full_project',
  `milestone_plan` text DEFAULT NULL,
  `cv_url` varchar(255) DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `statut` enum('en_attente','acceptee','refusee') DEFAULT 'en_attente',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_candidature`),
  UNIQUE KEY `uniq_offre_freelancer` (`id_offre`,`id_freelancer`),
  KEY `idx_candidature_freelancer` (`id_freelancer`),
  CONSTRAINT `fk_candidature_offre_job`
    FOREIGN KEY (`id_offre`) REFERENCES `offre_job`(`id_offre`) ON DELETE CASCADE,
  CONSTRAINT `fk_candidature_freelancer_user`
    FOREIGN KEY (`id_freelancer`) REFERENCES `User`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categorie` (`id_categorie`, `nom_categorie`, `description`, `icone`) VALUES
(1, 'Web Development', 'Sites web, applications web et APIs.', 'fas fa-code'),
(2, 'Design', 'Logo, UI UX et identite visuelle.', 'fas fa-palette'),
(3, 'Mobile App', 'Applications Android et iOS.', 'fas fa-mobile-alt'),
(4, 'Marketing', 'SEO, publicite et reseaux sociaux.', 'fas fa-bullhorn'),
(5, 'Photography', 'Shooting, retouche et contenu visuel.', 'fas fa-camera')
ON DUPLICATE KEY UPDATE
  `nom_categorie` = VALUES(`nom_categorie`),
  `description` = VALUES(`description`),
  `icone` = VALUES(`icone`);

INSERT INTO `services` (
  `id_service`, `titre`, `description`, `prix`, `delai_livraison`,
  `statut`, `thumbnail`, `id_categorie`, `id_freelancer`
) VALUES
(
  1, 'Developpement Site Web React',
  'Creation d un site moderne, responsive et optimise pour votre activite.',
  450.00, 7, 'actif', NULL, 1, 3
),
(
  2, 'Logo et identite visuelle',
  'Conception de logo, palette de couleurs et kit de marque complet.',
  180.00, 4, 'actif', NULL, 2, 4
),
(
  3, 'Audit SEO complet',
  'Analyse technique et optimisation de contenu pour mieux se positionner.',
  220.00, 5, 'en_attente', NULL, 4, 3
)
ON DUPLICATE KEY UPDATE
  `titre` = VALUES(`titre`),
  `description` = VALUES(`description`),
  `prix` = VALUES(`prix`),
  `delai_livraison` = VALUES(`delai_livraison`),
  `statut` = VALUES(`statut`),
  `id_categorie` = VALUES(`id_categorie`),
  `id_freelancer` = VALUES(`id_freelancer`);

INSERT INTO `offre_job` (
  `id_offre`, `titre`, `description`, `budget`, `delai_jours`,
  `execution_mode`, `milestone_plan`, `niveau_requis`, `competences_requises`, `statut`, `id_client`
) VALUES
(
  1, 'Developpement dashboard React pour startup',
  'Nous recherchons un freelancer capable de construire un dashboard React responsive avec consommation API et design moderne.',
  1800.00, 12, 'milestone', '1. Maquette dashboard 2. Integration API 3. Tests et livraison', 'expert', 'React, API REST, UI Design', 'actif', 2
),
(
  2, 'Refonte UI/UX application mobile',
  'Mission pour revoir l experience utilisateur de notre application mobile et produire de nouvelles maquettes Figma.',
  950.00, 9, 'full_project', NULL, 'intermediaire', 'Figma, UX Research, Mobile UI', 'actif', 2
)
ON DUPLICATE KEY UPDATE
  `titre` = VALUES(`titre`),
  `description` = VALUES(`description`),
  `budget` = VALUES(`budget`),
  `delai_jours` = VALUES(`delai_jours`);

INSERT INTO `candidature_offre` (
  `id_candidature`, `id_offre`, `id_freelancer`, `message`, `budget_propose`,
  `disponibilite_jours`, `execution_mode`, `milestone_plan`, `cv_url`, `portfolio_url`, `statut`
) VALUES
(
  1, 1, 3,
  'Je peux prendre en charge le dashboard React complet avec composants reutilisables, integration API et finition responsive.',
  1700.00, 10, 'milestone', '1. Architecture UI 2. Integration endpoints 3. Recette finale', 'https://example.com/cv-react.pdf', 'https://portfolio.example.com/react', 'en_attente'
),
(
  2, 2, 4,
  'Je peux assurer la refonte UX et la livraison des maquettes haute fidelite avec prototype interactif.',
  900.00, 8, 'full_project', NULL, 'https://example.com/cv-design.pdf', 'https://portfolio.example.com/design', 'acceptee'
)
ON DUPLICATE KEY UPDATE
  `message` = VALUES(`message`),
  `budget_propose` = VALUES(`budget_propose`),
  `disponibilite_jours` = VALUES(`disponibilite_jours`),
  `cv_url` = VALUES(`cv_url`),
  `portfolio_url` = VALUES(`portfolio_url`);

ALTER TABLE `User` AUTO_INCREMENT = 5;
ALTER TABLE `categorie` AUTO_INCREMENT = 6;
ALTER TABLE `services` AUTO_INCREMENT = 4;
ALTER TABLE `conversations` AUTO_INCREMENT = 1;
ALTER TABLE `messages` AUTO_INCREMENT = 1;
ALTER TABLE `offre_job` AUTO_INCREMENT = 3;
ALTER TABLE `candidature_offre` AUTO_INCREMENT = 3;


CREATE TABLE IF NOT EXISTS `Brainstorming` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date_debut` date NOT NULL,
  `accepted` tinyint(1) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `brainstorming_user_idx` (`user_id`),
  CONSTRAINT `brainstorming_user_fk` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `Idee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `categorie` varchar(120) NOT NULL DEFAULT 'General',
  `priorite` enum('faible','moyenne','haute') NOT NULL DEFAULT 'moyenne',
  `statut` enum('proposee','en_etude','approuvee','rejetee') NOT NULL DEFAULT 'proposee',
  `votes` int(11) NOT NULL DEFAULT 0,
  `brainstorming_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idee_brainstorming_idx` (`brainstorming_id`),
  KEY `idee_user_idx` (`user_id`),
  CONSTRAINT `idee_brainstorming_fk` FOREIGN KEY (`brainstorming_id`) REFERENCES `Brainstorming` (`id`) ON DELETE CASCADE,
  CONSTRAINT `idee_user_fk` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

