-- Ajouter la colonne etat et id_client à la table projet
ALTER TABLE `projet`
  ADD COLUMN `etat` ENUM('en_attente_validation','publie','refuse') NOT NULL DEFAULT 'en_attente_validation' AFTER `statut`,
  ADD COLUMN `id_client` int(11) DEFAULT NULL AFTER `etat`;

-- Les projets existants sont publiés directement
UPDATE `projet` SET `etat` = 'publie';
