-- Base de données : skillbridge (module Produit)
-- À importer dans phpMyAdmin

USE skillbridge;

-- Table Catégorie Produit
CREATE TABLE IF NOT EXISTS categorie_produit (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom_categorie VARCHAR(100) NOT NULL,
    description TEXT,
    icone VARCHAR(50) DEFAULT 'fas fa-folder',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table Produit
CREATE TABLE IF NOT EXISTS produit (
    id_produit INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    statut ENUM('disponible','rupture','en_attente') DEFAULT 'en_attente',
    image VARCHAR(255) DEFAULT NULL,
    id_categorie INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categorie) REFERENCES categorie_produit(id_categorie) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Données de test - Catégories Produit
INSERT INTO categorie_produit (nom_categorie, description, icone) VALUES
('Templates Web', 'Templates HTML/CSS, thèmes WordPress, kits UI', 'fas fa-laptop-code'),
('E-books', 'Livres numériques, guides, tutoriels PDF', 'fas fa-book'),
('Graphisme', 'Logos, illustrations, mockups, icônes', 'fas fa-paint-brush'),
('Audio & Musique', 'Beats, effets sonores, musique libre de droits', 'fas fa-headphones'),
('Plugins & Scripts', 'Extensions, plugins WordPress, scripts JS/PHP', 'fas fa-puzzle-piece'),
('Photos & Vidéos', 'Photos stock, vidéos, animations', 'fas fa-image'),
('Formations', 'Cours en ligne, certifications, workshops', 'fas fa-graduation-cap'),
('Outils Business', 'Modèles Excel, présentations, business plans', 'fas fa-chart-bar');

-- Données de test - Produits
INSERT INTO produit (nom, description, prix, quantite, statut, id_categorie) VALUES
('Template Dashboard Admin Pro', 'Un template dashboard moderne et responsive avec mode sombre, graphiques interactifs et plus de 50 composants UI prêts à l''emploi.', 49.99, 100, 'disponible', 1),
('Guide Complet React.js 2025', 'E-book de 350 pages couvrant React.js de A à Z : hooks, context, Redux, Next.js et déploiement. Exemples pratiques inclus.', 29.00, 50, 'disponible', 2),
('Pack 500 Icônes SVG Premium', 'Collection de 500 icônes vectorielles optimisées pour le web. Formats SVG, PNG et Figma inclus.', 19.99, 200, 'disponible', 3),
('Beats Lo-Fi Pack Vol.1', '10 beats lo-fi originaux libres de droits pour vos projets créatifs. Format WAV haute qualité.', 15.00, 30, 'disponible', 4),
('Plugin SEO Analyzer WordPress', 'Plugin WordPress pour analyser et optimiser le SEO de vos pages. Score en temps réel et suggestions.', 39.00, 75, 'en_attente', 5),
('Pack 200 Photos Nature HD', '200 photos haute définition de paysages naturels. Usage commercial autorisé.', 24.99, 150, 'disponible', 6),
('Formation Figma Avancée', 'Cours vidéo de 12h sur Figma : prototypage, auto-layout, composants avancés, design systems.', 59.00, 40, 'en_attente', 7),
('Kit Business Plan Startup', 'Modèle complet de business plan avec projections financières, analyse SWOT et pitch deck.', 34.99, 60, 'disponible', 8);
