-- Base de données: services_platform
CREATE DATABASE IF NOT EXISTS services_platform CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE services_platform;

-- Table Categorie
CREATE TABLE IF NOT EXISTS categorie (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom_categorie VARCHAR(100) NOT NULL,
    description TEXT,
    icone VARCHAR(50) DEFAULT 'fas fa-folder',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table Services (simplifié - sans référence à users)
CREATE TABLE IF NOT EXISTS services (
    id_service INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    delai_livraison INT NOT NULL COMMENT 'en jours',
    statut ENUM('actif','suspendu','en_attente') DEFAULT 'en_attente',
    image VARCHAR(255) DEFAULT NULL,
    id_categorie INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categorie) REFERENCES categorie(id_categorie) ON DELETE CASCADE
);

-- Données de test
INSERT INTO categorie (nom_categorie, description, icone) VALUES
('Web Development', 'Sites web, applications web, APIs', 'fas fa-code'),
('Design', 'Graphic design, UI/UX, identité visuelle', 'fas fa-palette'),
('Mobile App', 'Applications iOS et Android', 'fas fa-mobile-alt'),
('Marketing', 'SEO, réseaux sociaux, publicité', 'fas fa-bullhorn'),
('Music', 'Production musicale, mixage, mastering', 'fas fa-music'),
('Photography', 'Retouche photo, shooting, vidéo', 'fas fa-camera'),
('Business', 'Consulting, stratégie, finance', 'fas fa-briefcase'),
('IT Software', 'Sécurité, cloud, DevOps', 'fas fa-server');

INSERT INTO services (titre, description, prix, delai_livraison, statut, id_categorie) VALUES
('Création site WordPress professionnel', 'Je crée votre site WordPress sur mesure, responsive, optimisé SEO.', 299.99, 7, 'actif', 1),
('Logo + Identité visuelle complète', 'Design de logo professionnel avec charte graphique complète.', 149.00, 5, 'actif', 2),
('Application React.js moderne', 'Développement d\'une SPA React avec backend API REST.', 599.00, 14, 'actif', 1),
('SEO On-page complet', 'Audit et optimisation SEO complète de votre site.', 199.00, 10, 'en_attente', 4);
