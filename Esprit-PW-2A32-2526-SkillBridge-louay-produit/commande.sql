-- Base de données : skillbridge (module Commande)
-- À importer dans phpMyAdmin

USE skillbridge;

-- Table Commande
CREATE TABLE IF NOT EXISTS commande (
    id_commande INT AUTO_INCREMENT PRIMARY KEY,
    nom_client VARCHAR(100) NOT NULL,
    email_client VARCHAR(150) NOT NULL,
    telephone VARCHAR(20) DEFAULT NULL,
    adresse TEXT NOT NULL,
    id_produit INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    prix_total DECIMAL(10,2) NOT NULL,
    statut ENUM('en_attente','confirmee','expediee','livree','annulee') DEFAULT 'en_attente',
    note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produit) REFERENCES produit(id_produit) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Données de test - Commandes
INSERT INTO commande (nom_client, email_client, telephone, adresse, id_produit, quantite, prix_total, statut, note) VALUES
('Ahmed Ben Ali', 'ahmed.benali@email.com', '20123456', '15 Rue de la République, Tunis', 1, 2, 99.98, 'livree', 'Livraison rapide svp'),
('Fatma Trabelsi', 'fatma.trabelsi@email.com', '55987654', '8 Avenue Habib Bourguiba, Sousse', 2, 1, 29.00, 'confirmee', NULL),
('Mohamed Sassi', 'mohamed.sassi@email.com', '98765432', '22 Rue Ibn Khaldoun, Sfax', 3, 3, 59.97, 'expediee', 'Urgent'),
('Sarra Hamdi', 'sarra.hamdi@email.com', '27654321', '5 Boulevard du 14 Janvier, Bizerte', 4, 1, 15.00, 'en_attente', NULL),
('Youssef Maalej', 'youssef.maalej@email.com', '50112233', '30 Rue de Marseille, Tunis', 1, 1, 49.99, 'annulee', 'Changement d''avis'),
('Ines Bouaziz', 'ines.bouaziz@email.com', '23445566', '12 Avenue de Carthage, La Marsa', 6, 2, 49.98, 'livree', 'Super produit'),
('Karim Gharbi', 'karim.gharbi@email.com', '99887766', '7 Rue de Palestine, Tunis', 8, 1, 34.99, 'confirmee', NULL),
('Amira Jebali', 'amira.jebali@email.com', '21334455', '18 Rue Tahar Haddad, Monastir', 5, 1, 39.00, 'en_attente', 'Première commande');
