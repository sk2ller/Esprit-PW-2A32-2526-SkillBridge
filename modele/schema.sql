-- Schema SQLite pour les commandes

CREATE TABLE IF NOT EXISTS commandes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    statut TEXT NOT NULL CHECK(statut IN ('en_attente', 'en_cours', 'livree', 'annulee')),
    prix_total REAL NOT NULL CHECK(prix_total > 0),
    date_creation TEXT NOT NULL,
    date_livraison TEXT NOT NULL,
    id_service INTEGER NOT NULL,
    id_client INTEGER NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

-- Données de test
INSERT INTO commandes (statut, prix_total, date_creation, date_livraison, id_service, id_client) VALUES
('en_cours', 850.00, '2026-04-02', '2026-04-15', 4, 12),
('en_attente', 350.00, '2026-04-07', '2026-04-19', 3, 8),
('livree', 1200.00, '2026-04-01', '2026-04-11', 2, 21),
('en_cours', 600.00, '2026-04-05', '2026-04-18', 6, 19);
