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
-- Structure de la table `Project`
--

CREATE TABLE IF NOT EXISTS `Project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `budget` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date_creation` date NOT NULL,
  `statut` enum('en_cours','termine','en_attente') NOT NULL DEFAULT 'en_attente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Données d'exemple de la table `Project`
--
INSERT INTO `Project` (`titre`, `description`, `budget`, `date_creation`, `statut`) VALUES
('Création site vitrine', 'Développement d\'un site vitrine responsive pour un client local.', 1800.00, '2026-04-01', 'en_cours'),
('Audit UX application mobile', 'Analyse ergonomique complète et recommandations UI/UX.', 950.00, '2026-03-20', 'termine'),
('Intégration API paiement', 'Connexion d\'une API de paiement sécurisée avec tests.', 1250.00, '2026-04-10', 'en_attente');

COMMIT;
