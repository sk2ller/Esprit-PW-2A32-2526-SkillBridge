USE `skillbridge`;

SET @add_is_banned = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'User'
        AND COLUMN_NAME = 'is_banned'
    ),
    'SELECT 1',
    'ALTER TABLE `User` ADD COLUMN `is_banned` TINYINT(1) DEFAULT 0 AFTER `is_approved`'
  )
);
PREPARE stmt FROM @add_is_banned;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_availability = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'User'
        AND COLUMN_NAME = 'availability'
    ),
    'SELECT 1',
    "ALTER TABLE `User` ADD COLUMN `availability` varchar(50) DEFAULT 'available' AFTER `is_banned`"
  )
);
PREPARE stmt FROM @add_availability;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_rating = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'User'
        AND COLUMN_NAME = 'rating'
    ),
    'SELECT 1',
    "ALTER TABLE `User` ADD COLUMN `rating` decimal(3,2) DEFAULT 0.00 AFTER `availability`"
  )
);
PREPARE stmt FROM @add_rating;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_phone = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'User'
        AND COLUMN_NAME = 'phone'
    ),
    'SELECT 1',
    'ALTER TABLE `User` ADD COLUMN `phone` varchar(30) DEFAULT NULL AFTER `rating`'
  )
);
PREPARE stmt FROM @add_phone;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_bio = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'User'
        AND COLUMN_NAME = 'bio'
    ),
    'SELECT 1',
    'ALTER TABLE `User` ADD COLUMN `bio` text DEFAULT NULL AFTER `phone`'
  )
);
PREPARE stmt FROM @add_bio;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_profile_picture = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'User'
        AND COLUMN_NAME = 'profile_picture'
    ),
    'SELECT 1',
    'ALTER TABLE `User` ADD COLUMN `profile_picture` varchar(255) DEFAULT NULL AFTER `bio`'
  )
);
PREPARE stmt FROM @add_profile_picture;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_skill_summary = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'User'
        AND COLUMN_NAME = 'skill_summary'
    ),
    'SELECT 1',
    'ALTER TABLE `User` ADD COLUMN `skill_summary` varchar(255) DEFAULT NULL AFTER `profile_picture`'
  )
);
PREPARE stmt FROM @add_skill_summary;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_experience_description = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'User'
        AND COLUMN_NAME = 'experience_description'
    ),
    'SELECT 1',
    'ALTER TABLE `User` ADD COLUMN `experience_description` text DEFAULT NULL AFTER `skill_summary`'
  )
);
PREPARE stmt FROM @add_experience_description;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

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

SET @add_offer_execution_mode = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'offre_job'
        AND COLUMN_NAME = 'execution_mode'
    ),
    'SELECT 1',
    'ALTER TABLE `offre_job` ADD COLUMN `execution_mode` ENUM(''full_project'',''milestone'') DEFAULT ''full_project'' AFTER `delai_jours`'
  )
);
PREPARE stmt FROM @add_offer_execution_mode;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_offer_milestone_plan = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'offre_job'
        AND COLUMN_NAME = 'milestone_plan'
    ),
    'SELECT 1',
    'ALTER TABLE `offre_job` ADD COLUMN `milestone_plan` TEXT DEFAULT NULL AFTER `execution_mode`'
  )
);
PREPARE stmt FROM @add_offer_milestone_plan;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_cv_url = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'candidature_offre'
        AND COLUMN_NAME = 'cv_url'
    ),
    'SELECT 1',
    'ALTER TABLE `candidature_offre` ADD COLUMN `cv_url` VARCHAR(255) DEFAULT NULL AFTER `disponibilite_jours`'
  )
);
PREPARE stmt FROM @add_cv_url;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_application_execution_mode = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'candidature_offre'
        AND COLUMN_NAME = 'execution_mode'
    ),
    'SELECT 1',
    'ALTER TABLE `candidature_offre` ADD COLUMN `execution_mode` ENUM(''full_project'',''milestone'') DEFAULT ''full_project'' AFTER `disponibilite_jours`'
  )
);
PREPARE stmt FROM @add_application_execution_mode;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_application_milestone_plan = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'candidature_offre'
        AND COLUMN_NAME = 'milestone_plan'
    ),
    'SELECT 1',
    'ALTER TABLE `candidature_offre` ADD COLUMN `milestone_plan` TEXT DEFAULT NULL AFTER `execution_mode`'
  )
);
PREPARE stmt FROM @add_application_milestone_plan;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_portfolio_url = (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'candidature_offre'
        AND COLUMN_NAME = 'portfolio_url'
    ),
    'SELECT 1',
    'ALTER TABLE `candidature_offre` ADD COLUMN `portfolio_url` VARCHAR(255) DEFAULT NULL AFTER `cv_url`'
  )
);
PREPARE stmt FROM @add_portfolio_url;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

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

ALTER TABLE `User` AUTO_INCREMENT = 5;
ALTER TABLE `categorie` AUTO_INCREMENT = 6;
ALTER TABLE `services` AUTO_INCREMENT = 4;
ALTER TABLE `conversations` AUTO_INCREMENT = 1;
ALTER TABLE `messages` AUTO_INCREMENT = 1;


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
