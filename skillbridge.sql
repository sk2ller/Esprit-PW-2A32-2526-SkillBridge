-- Base de données : `skillbridge`

CREATE DATABASE IF NOT EXISTS `skillbridge` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `skillbridge`;

-- --------------------------------------------------------

--
-- Structure de la table `User`
--

CREATE TABLE `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `mot_de_passe` varchar(255) NOT NULL,
  `niveau` varchar(50) DEFAULT 'débutant',
  `id_role` int(11) DEFAULT 2,
  `badge_verifie` tinyint(1) DEFAULT 0,
  `is_approved` tinyint(1) DEFAULT 0,
  `availability` varchar(50) DEFAULT 'available',
  `rating` decimal(3,2) DEFAULT 0.00,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Données de la table `User` (Admin par défaut)
--
INSERT INTO `User` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `niveau`, `id_role`, `is_approved`) VALUES
(1, 'Admin', 'SkillBridge', 'admin@skillbridge.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86.jsUGkmH', 'expert', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `Interaction` (Likes/Dislikes)
--

CREATE TABLE IF NOT EXISTS `Interaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user_from` int(11) NOT NULL,
  `id_user_to` int(11) NOT NULL,
  `type` enum('like', 'dislike') DEFAULT 'like',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_interaction` (`id_user_from`, `id_user_to`),
  FOREIGN KEY (`id_user_from`) REFERENCES `User`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_user_to`) REFERENCES `User`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Fix AUTO_INCREMENT
--
ALTER TABLE `User` AUTO_INCREMENT = 1;

COMMIT;
