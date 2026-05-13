-- Ajouter la colonne payee à la table tache
ALTER TABLE `tache`
  ADD COLUMN `payee` tinyint(1) NOT NULL DEFAULT 0 AFTER `prix`;
