-- Table pour tracker les messages lus par utilisateur/projet
CREATE TABLE IF NOT EXISTS `message_lu` (
  `id_projet` int(11) NOT NULL,
  `id_user`   int(11) NOT NULL,
  `dernier_lu_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_projet`, `id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
