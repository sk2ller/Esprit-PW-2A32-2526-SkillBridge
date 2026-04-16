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

-- Table Clients (pour les offres job)
CREATE TABLE IF NOT EXISTS clients (
    id_client INT AUTO_INCREMENT PRIMARY KEY,
    nom_client VARCHAR(100) NOT NULL,
    email_client VARCHAR(100) NOT NULL UNIQUE,
    phone_client VARCHAR(20),
    company_name VARCHAR(150),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table Offres Job
CREATE TABLE IF NOT EXISTS offres (
    id_offre INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(250) NOT NULL,
    description TEXT NOT NULL,
    budget DECIMAL(12,2) NOT NULL,
    delai_publication INT DEFAULT 30 COMMENT 'en jours',
    niveau_requis ENUM('debutant','intermediaire','expert') DEFAULT 'intermediaire',
    competences_requises TEXT COMMENT 'compétences séparées par des virgules',
    statut ENUM('actif','suspendu','en_attente','ferme') DEFAULT 'en_attente',
    id_client INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES clients(id_client) ON DELETE SET NULL
);

-- Données de test - Categories
INSERT INTO categorie (nom_categorie, description, icone) VALUES
('Web Development', 'Sites web, applications web, APIs', 'fas fa-code'),
('Design', 'Graphic design, UI/UX, identité visuelle', 'fas fa-palette'),
('Mobile App', 'Applications iOS et Android', 'fas fa-mobile-alt'),
('Marketing', 'SEO, réseaux sociaux, publicité', 'fas fa-bullhorn'),
('Music', 'Production musicale, mixage, mastering', 'fas fa-music'),
('Photography', 'Retouche photo, shooting, vidéo', 'fas fa-camera'),
('Business', 'Consulting, stratégie, finance', 'fas fa-briefcase'),
('IT Software', 'Sécurité, cloud, DevOps', 'fas fa-server');

-- Données de test - Services
INSERT INTO services (titre, description, prix, delai_livraison, statut, id_categorie) VALUES
('Création site WordPress professionnel', 'Je crée votre site WordPress sur mesure, responsive, optimisé SEO.', 299.99, 7, 'actif', 1),
('Logo + Identité visuelle complète', 'Design de logo professionnel avec charte graphique complète.', 149.00, 5, 'actif', 2),
('Application React.js moderne', 'Développement d\'une SPA React avec backend API REST.', 599.00, 14, 'actif', 1),
('SEO On-page complet', 'Audit et optimisation SEO complète de votre site.', 199.00, 10, 'en_attente', 4);

-- Données de test - Clients
INSERT INTO clients (nom_client, email_client, company_name) VALUES
('Ahmed Benmohammed', 'ahmed@techstartup.tn', 'TechStartup'),
('Fatma Zardi', 'fatma@digitalagency.tn', 'Digital Agency Pro'),
('Mohammed Ben Ali', 'mbenali@consulting.tn', 'Strategic Consulting');

-- Données de test - Offres Job
INSERT INTO offres (titre, description, budget, delai_publication, niveau_requis, competences_requises, statut, id_client) VALUES
('Développeur React Senior pour SaaS', 'Nous recherchons un développeur React expérimenté pour rejoindre notre équipe et développer notre plateforme SaaS. Vous travaillerez sur l\'architecture frontend, les performances et l\'expérience utilisateur.\n\nRequis:\n- 3+ ans d\'expérience React\n- Maîtrise de TypeScript\n- Expérience REST API et GraphQL\n- Git et CI/CD', 2500.00, 30, 'expert', 'React, TypeScript, REST API, Next.js, Material-UI', 'actif', 1),
('Designer UI/UX pour Application Mobile', 'Créer les designs d\'une application mobile de gestion financière. Vous serez responsable de la conception des écrans, des interactions et de l\'expérience utilisateur globale.\n\nVous aurez accès à nos wireframes et user flows existants. Cette mission est idéale pour construire un portfolio de travaux de qualité.', 1500.00, 30, 'intermediaire', 'Figma, Prototyping, Mobile Design, User Research', 'actif', 2),
('Spécialiste SEO & Content Marketing', 'Besoin d\'un expert SEO pour optimiser notre présence en ligne et augmenter notre trafic organique. Vous gèrerez les campagnes de contenu, l\'optimisation technique et le link building.\n\nBudget flexible selon expérience et résultats attendus.', 1200.00, 45, 'intermediaire', 'SEO, Google Analytics, Content Writing, Keyword Research', 'actif', 3),
('Développeur Full Stack Node.js + React', 'Mission complète pour développer une plateforme de marketplace. Backend avec Node.js/Express et frontend React. Durée: 3-4 mois avec possibilité d\'extension.\n\nSkills requis:\n- Node.js + Express\n- React\n- MongoDB\n- Docker\n- Testing (Jest, Cypress)', 3000.00, 60, 'expert', 'Node.js, Express, React, MongoDB, Docker, Jest', 'en_attente', 1),
('Webmaster PHP/MySQL (Maintenance)', 'Maintenance et support technique pour site e-commerce existant en PHP/MySQL. Corrections de bugs, mises à jour sécurité, ajout de petites fonctionnalités. Engagement à long terme possible.', 800.00, 30, 'debutant', 'PHP, MySQL, WordPress, HTML/CSS', 'suspendu', 2);

