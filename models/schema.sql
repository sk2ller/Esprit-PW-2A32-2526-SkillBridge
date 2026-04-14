CREATE TABLE IF NOT EXISTS Projet (
    id_projet INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    budget FLOAT NOT NULL,
    date_creation DATE NOT NULL
);
