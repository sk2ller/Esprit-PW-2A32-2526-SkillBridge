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
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Brainstorming` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date_debut` date NOT NULL,
  `accepted` tinyint(1) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `brainstorming_user_fk` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Données de la table `User` (Admin par défaut)
--
INSERT INTO `User` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `niveau`, `id_role`) VALUES
(1, 'Admin', 'SkillBridge', 'admin@skillbridge.com', '$2y$10$gpYAc9sIXyB1mZIUf1iN0OOSyuE4oZjPN8HTzJVDnlUT2M2XZfknS', 'expert', 1),
(2, 'Freelancer', 'SkillBridge', 'freelancer@skillbridge.com', '$2y$10$Vg.rQoz7e4iOgxMh5QTL4O6Ac3J8xtmxcMA2jeFXPI8Cs69//Yawa', 'intermediaire', 3);

--
-- AUTO_INCREMENT pour la table `User`
--
ALTER TABLE `User` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

COMMIT;
