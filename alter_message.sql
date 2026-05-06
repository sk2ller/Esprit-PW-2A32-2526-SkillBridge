CREATE TABLE IF NOT EXISTS `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_projet` int(11) NOT NULL,
  `id_expediteur` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_projet` (`id_projet`),
  KEY `idx_expediteur` (`id_expediteur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
