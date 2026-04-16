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

--
-- Données de la table `User` (Admin par défaut)
--
INSERT INTO `User` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `niveau`, `id_role`) VALUES
(1, 'Admin', 'SkillBridge', 'admin@skillbridge.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86.jsUGkmH', 'expert', 1);

--
-- AUTO_INCREMENT pour la table `User`
--
ALTER TABLE `User` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- --------------------------------------------------------

--
-- Structure de la table `Projet`
--

CREATE TABLE `Projet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `budget` decimal(12,2) NOT NULL DEFAULT 0,
  `date_creation` date NOT NULL,
  `statut` enum('en_cours','termine','en_attente') NOT NULL DEFAULT 'en_attente',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Donnees de la table `Projet`
--
INSERT INTO `Projet` (`id`, `titre`, `description`, `budget`, `date_creation`, `statut`) VALUES
(1, 'Refonte site vitrine', 'Modernisation du site vitrine avec responsive design et optimisation SEO.', 2500.00, '2026-04-01', 'en_cours'),
(2, 'Application de reservation', 'Developpement d\'une application web de reservation avec espace client.', 6200.00, '2026-03-20', 'en_attente'),
(3, 'Audit securite', 'Analyse complete de securite et recommandations de correction.', 1800.00, '2026-03-11', 'termine');

--
-- AUTO_INCREMENT pour la table `Projet`
--
ALTER TABLE `Projet` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

COMMIT;
