<?php
require_once __DIR__ . '/Database.php';

class Projet
{
    public static function getAll($search = '')
    {
        try {
            $conn = Database::getConnection();
            $sql = 'SELECT id_projet, titre, description, budget, date_creation, statut FROM Projet';
            $params = array();

            if (trim($search) !== '') {
                $sql .= ' WHERE titre LIKE :search OR description LIKE :search OR statut LIKE :search';
                $params[':search'] = '%' . trim($search) . '%';
            }

            $sql .= ' ORDER BY id_projet DESC';

            if (count($params) > 0) {
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
            } else {
                $stmt = $conn->query($sql);
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return array();
        }
    }

    public static function getById($id)
    {
        try {
            $conn = Database::getConnection();
            $stmt = $conn->prepare('SELECT id_projet, titre, description, budget, date_creation, statut FROM Projet WHERE id_projet = ?');
            $stmt->execute(array($id));
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public static function create($titre, $description, $budget, $dateCreation, $statut = 'en_cours')
    {
        try {
            $conn = Database::getConnection();
            $stmt = $conn->prepare('INSERT INTO Projet (titre, description, budget, date_creation, statut) VALUES (?, ?, ?, ?, ?)');
            return $stmt->execute(array($titre, $description, $budget, $dateCreation, self::sanitizeStatut($statut)));
        } catch (Exception $e) {
            return false;
        }
    }

    public static function update($id, $titre, $description, $budget, $dateCreation, $statut = 'en_cours')
    {
        try {
            $conn = Database::getConnection();
            $stmt = $conn->prepare('UPDATE Projet SET titre = ?, description = ?, budget = ?, date_creation = ?, statut = ? WHERE id_projet = ?');
            return $stmt->execute(array($titre, $description, $budget, $dateCreation, self::sanitizeStatut($statut), $id));
        } catch (Exception $e) {
            return false;
        }
    }

    public static function delete($id)
    {
        try {
            $conn = Database::getConnection();
            $stmt = $conn->prepare('DELETE FROM Projet WHERE id_projet = ?');
            return $stmt->execute(array($id));
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getDashboardStats()
    {
        try {
            $conn = Database::getConnection();
            $stmt = $conn->query("\n                SELECT\n                    COUNT(*) AS total_projets,\n                    SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) AS projets_en_cours,\n                    SUM(CASE WHEN statut = 'termine' THEN 1 ELSE 0 END) AS projets_termines,\n                    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) AS projets_en_attente,\n                    COALESCE(SUM(budget), 0) AS budget_total\n                FROM Projet\n            ");

            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$stats) {
                return array(
                    'total_projets' => 0,
                    'projets_en_cours' => 0,
                    'projets_termines' => 0,
                    'projets_en_attente' => 0,
                    'budget_total' => 0,
                );
            }

            return $stats;
        } catch (Exception $e) {
            return array(
                'total_projets' => 0,
                'projets_en_cours' => 0,
                'projets_termines' => 0,
                'projets_en_attente' => 0,
                'budget_total' => 0,
            );
        }
    }

    private static function sanitizeStatut($statut)
    {
        $allowed = array('en_cours', 'termine', 'en_attente');

        if (in_array($statut, $allowed, true)) {
            return $statut;
        }

        return 'en_cours';
    }
}
