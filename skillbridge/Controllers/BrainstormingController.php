<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Models/Brainstorming.php');

class BrainstormingController
{
    /**
     * Valide et ajoute un nouveau brainstorming
     * @param array $data Données brutes du formulaire
     * @return array Résultat avec 'success' et 'message' ou 'errors'
     */
    public function createBrainstorming($data)
    {
        // Nettoyer les données d'entrée
        $cleanData = Brainstorming::sanitizeData($data);

        // Créer l'objet Brainstorming
        $brainstorm = new Brainstorming(
            $cleanData['titre'] ?? null,
            $cleanData['description'] ?? null,
            $cleanData['date_debut'] ?? null,
            $cleanData['user_id'] ?? null
        );

        // Valider les données
        if (!$brainstorm->validate()) {
            return [
                'success' => false,
                'errors' => $brainstorm->getValidationErrors(),
                'message' => $brainstorm->getFirstValidationError()
            ];
        }

        // Tenter d'ajouter en base
        try {
            $this->addBrainstorming($brainstorm);
            return [
                'success' => true,
                'message' => 'Votre brainstorming a été soumis avec succès. Un administrateur pourra l\'accepter.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Valide les données d'un brainstorming sans l'enregistrer
     * @param array $data Données à valider
     * @return array Résultat de validation
     */
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
        } else {
            return [
                'valid' => false,
                'errors' => $brainstorm->getValidationErrors()
            ];
        }
    }

    public function addBrainstorming(Brainstorming $brainstorm)
    {
        $sql = "INSERT INTO Brainstorming (titre, description, date_debut, accepted, user_id)
                VALUES (:titre, :description, :date_debut, :accepted, :user_id)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre'       => $brainstorm->getTitre(),
                'description' => $brainstorm->getDescription(),
                'date_debut'  => $brainstorm->getDateDebut(),
                'accepted'    => $brainstorm->getAccepted(),
                'user_id'     => $brainstorm->getUserId(),
            ]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de l\'insertion en base de données: ' . $e->getMessage());
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
        $sql = "SELECT * FROM Brainstorming WHERE id = :id";
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

    /**
     * Met à jour un brainstorming avec validation
     * @param int $id ID du brainstorming
     * @param array $data Données à mettre à jour
     * @return array Résultat avec 'success' et 'message' ou 'errors'
     */
    public function updateBrainstormingWithValidation($id, $data)
    {
        // Nettoyer les données d'entrée
        $cleanData = Brainstorming::sanitizeData($data);

        // Créer un objet temporaire pour validation
        $brainstorm = new Brainstorming(
            $cleanData['titre'] ?? null,
            $cleanData['description'] ?? null,
            $cleanData['date_debut'] ?? null,
            1 // user_id temporaire, pas utilisé pour la validation
        );

        // Valider les données
        if (!$brainstorm->validate()) {
            return [
                'success' => false,
                'errors' => $brainstorm->getValidationErrors(),
                'message' => $brainstorm->getFirstValidationError()
            ];
        }

        // Tenter de mettre à jour en base
        try {
            $result = $this->updateBrainstorming($id, $cleanData['titre'], $cleanData['description'], $cleanData['date_debut']);
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Brainstorming modifié avec succès'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la modification'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la modification: ' . $e->getMessage()
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
            throw new Exception('Erreur lors de la mise à jour: ' . $e->getMessage());
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
}
