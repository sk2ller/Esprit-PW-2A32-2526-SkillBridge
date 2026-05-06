-- Base de données: offrjob
CREATE DATABASE IF NOT EXISTS offrjob CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE offrjob;

-- Garder uniquement la table Offres Job
DROP TABLE IF EXISTS candidatures;
DROP TABLE IF EXISTS offres;

CREATE TABLE offres (
    id_offre INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(250) NOT NULL,
    description TEXT NOT NULL,
    budget DECIMAL(12,2) NOT NULL,
    delai_publication INT DEFAULT 30 COMMENT 'en jours',
    niveau_requis ENUM('debutant','intermediaire','expert') DEFAULT 'intermediaire',
    competences_requises TEXT COMMENT 'competences separees par des virgules',
    statut ENUM('actif','suspendu','en_attente','ferme') DEFAULT 'en_attente',
    id_client INT DEFAULT NULL,
    nom_client VARCHAR(120) DEFAULT 'Client Anonyme',
    email_client VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE candidatures (
    id_candidature INT AUTO_INCREMENT PRIMARY KEY,
    id_offre INT NOT NULL,
    id_freelancer INT DEFAULT NULL,
    nom_freelancer VARCHAR(120) NOT NULL,
    email_freelancer VARCHAR(120) NOT NULL,
    message TEXT NOT NULL,
    tarif_propose DECIMAL(12,2) DEFAULT 0.00,
    cv_path VARCHAR(255) DEFAULT NULL,
    portfolio_path VARCHAR(255) DEFAULT NULL,
    statut ENUM('en_attente','acceptee','refusee') DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_candidature_offre FOREIGN KEY (id_offre) REFERENCES offres(id_offre) ON DELETE CASCADE
);

-- Donnees de test - Offres Job
INSERT INTO offres (titre, description, budget, delai_publication, niveau_requis, competences_requises, statut, id_client, nom_client, email_client) VALUES
('Developpeur React Senior pour SaaS', 'Nous recherchons un developpeur React experimente pour rejoindre notre equipe et developper notre plateforme SaaS.', 2500.00, 30, 'expert', 'React, TypeScript, REST API, Next.js, Material-UI', 'actif', 1, 'Ahmed Benmohammed', 'ahmed@techstartup.tn'),
('Designer UI/UX pour Application Mobile', 'Creer les designs d une application mobile de gestion financiere.', 1500.00, 30, 'intermediaire', 'Figma, Prototyping, Mobile Design, User Research', 'actif', 2, 'Fatma Zardi', 'fatma@digitalagency.tn'),
('Specialiste SEO et Content Marketing', 'Besoin d un expert SEO pour optimiser notre presence en ligne.', 1200.00, 45, 'intermediaire', 'SEO, Google Analytics, Content Writing, Keyword Research', 'actif', 3, 'Mohammed Ben Ali', 'mbenali@consulting.tn'),
('Developpeur Full Stack Node.js + React', 'Mission complete pour developper une plateforme de marketplace.', 3000.00, 60, 'expert', 'Node.js, Express, React, MongoDB, Docker, Jest', 'en_attente', 1, 'Ahmed Benmohammed', 'ahmed@techstartup.tn'),
('Webmaster PHP/MySQL (Maintenance)', 'Maintenance et support technique pour site e-commerce existant.', 800.00, 30, 'debutant', 'PHP, MySQL, WordPress, HTML/CSS', 'suspendu', 2, 'Fatma Zardi', 'fatma@digitalagency.tn');

