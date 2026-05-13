-- Ajouter la colonne prix à la table tache
ALTER TABLE `tache`
  ADD COLUMN `prix` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `statut`;
