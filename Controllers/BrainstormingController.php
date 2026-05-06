<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Models/Brainstorming.php');

class BrainstormingController
{
    public function createBrainstorming($data)
    {
        $cleanData = Brainstorming::sanitizeData($data);

        $brainstorm = new Brainstorming(
            $cleanData['titre'] ?? null,
            $cleanData['description'] ?? null,
            $cleanData['date_debut'] ?? null,
            $cleanData['user_id'] ?? null
        );

        if (!$brainstorm->validate()) {
            return [
                'success' => false,
                'errors' => $brainstorm->getValidationErrors(),
                'message' => $brainstorm->getFirstValidationError()
            ];
        }

        try {
            $this->addBrainstorming($brainstorm);
            return [
                'success' => true,
                'message' => 'Votre brainstorming a ete soumis avec succes. Un administrateur pourra le valider.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l enregistrement : ' . $e->getMessage()
            ];
        }
    }

    public function validateBrainstormingData($data)
    {
        $cleanData = Brainstorming::sanitizeData($data);

        $brainstorm = new Brainstorming(
            $cleanData['titre'] ?? null,
            $cleanData['description'] ?? null,
            $cleanData['date_debut'] ?? null,
            $cleanData['user_id'] ?? null
        );

        if ($brainstorm->validate()) {
            return ['valid' => true];
        }

        return [
            'valid' => false,
            'errors' => $brainstorm->getValidationErrors()
        ];
    }

    public function addBrainstorming(Brainstorming $brainstorm)
    {
        $sql = "INSERT INTO Brainstorming (titre, description, date_debut, accepted, user_id)
                VALUES (:titre, :description, :date_debut, :accepted, :user_id)";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $brainstorm->getTitre(),
                'description' => $brainstorm->getDescription(),
                'date_debut' => $brainstorm->getDateDebut(),
                'accepted' => $brainstorm->getAccepted(),
                'user_id' => $brainstorm->getUserId(),
            ]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de l insertion en base de donnees : ' . $e->getMessage());
        }
    }

    public function listAll()
    {
        $sql = "SELECT b.*, u.nom AS user_nom, u.prenom AS user_prenom, u.email AS user_email
                FROM Brainstorming AS b
                LEFT JOIN User AS u ON b.user_id = u.id
                ORDER BY b.created_at DESC";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function listVisibleForUser($userId = null, $isAdmin = false)
    {
        $brainstormings = $this->listAll();

        if ($isAdmin) {
            return $brainstormings;
        }

        return array_values(array_filter($brainstormings, function ($brainstorming) {
            return (int) $brainstorming['accepted'] === 1;
        }));
    }

    public function listByUser($userId)
    {
        $sql = "SELECT * FROM Brainstorming WHERE user_id = :user_id ORDER BY created_at DESC";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['user_id' => $userId]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function getById($id)
    {
        $sql = "SELECT b.*, u.nom AS user_nom, u.prenom AS user_prenom, u.email AS user_email
                FROM Brainstorming AS b
                LEFT JOIN User AS u ON b.user_id = u.id
                WHERE b.id = :id";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function updateAccepted($id, $accepted)
    {
        $sql = "UPDATE Brainstorming SET accepted = :accepted WHERE id = :id";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['accepted' => $accepted, 'id' => $id]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function updateBrainstormingWithValidation($id, $data)
    {
        $cleanData = Brainstorming::sanitizeData($data);

        $brainstorm = new Brainstorming(
            $cleanData['titre'] ?? null,
            $cleanData['description'] ?? null,
            $cleanData['date_debut'] ?? null,
            1
        );

        if (!$brainstorm->validate()) {
            return [
                'success' => false,
                'errors' => $brainstorm->getValidationErrors(),
                'message' => $brainstorm->getFirstValidationError()
            ];
        }

        try {
            $result = $this->updateBrainstorming($id, $cleanData['titre'], $cleanData['description'], $cleanData['date_debut']);
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Brainstorming modifie avec succes.'
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la modification.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la modification : ' . $e->getMessage()
            ];
        }
    }

    public function updateBrainstorming($id, $titre, $description, $date_debut)
    {
        $sql = "UPDATE Brainstorming SET titre = :titre, description = :description, date_debut = :date_debut WHERE id = :id";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $titre,
                'description' => $description,
                'date_debut' => $date_debut,
                'id' => $id
            ]);
            return true;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la mise a jour : ' . $e->getMessage());
        }
    }

    public function deleteBrainstorming($id)
    {
        $sql = "DELETE FROM Brainstorming WHERE id = :id";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function listVisibleForUserWithFilters($userId = null, $isAdmin = false, $filters = [])
    {
        $brainstormings = $this->listAll();

        if (!$isAdmin) {
            $brainstormings = array_values(array_filter($brainstormings, function ($brainstorming) {
                return (int) $brainstorming['accepted'] === 1;
            }));
        }

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== '') {
            $status = (int) $filters['status'];
            $brainstormings = array_values(array_filter($brainstormings, function ($brainstorming) use ($status) {
                return (int) $brainstorming['accepted'] === $status;
            }));
        }

        // Apply search filter
        if (isset($filters['search']) && $filters['search'] !== '') {
            $searchTerm = strtolower($filters['search']);
            $brainstormings = array_values(array_filter($brainstormings, function ($brainstorming) use ($searchTerm) {
                $titre = strtolower($brainstorming['titre'] ?? '');
                $description = strtolower($brainstorming['description'] ?? '');
                return strpos($titre, $searchTerm) !== false || strpos($description, $searchTerm) !== false;
            }));
        }

        return $brainstormings;
    }
}


