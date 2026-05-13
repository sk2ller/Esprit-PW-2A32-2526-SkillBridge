<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Services/BadContentFilterService.php');

class Idee
{
    private $validationErrors = [];

    public function getAll($orderBy = 'created_at', $orderDirection = 'DESC')
    {
        $allowedColumns = ['id', 'titre', 'categorie', 'priorite', 'statut', 'votes', 'created_at'];
        $allowedDirections = ['ASC', 'DESC'];

        $orderBy = in_array($orderBy, $allowedColumns) ? $orderBy : 'created_at';
        $orderDirection = in_array(strtoupper($orderDirection), $allowedDirections) ? strtoupper($orderDirection) : 'DESC';

        $sql = "SELECT i.*, b.titre AS brainstorming_titre, b.accepted AS brainstorming_accepted,
                       u.nom AS user_nom, u.prenom AS user_prenom
                FROM Idee AS i
                INNER JOIN Brainstorming AS b ON i.brainstorming_id = b.id
                INNER JOIN User AS u ON i.user_id = u.id
                ORDER BY i.$orderBy $orderDirection";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($query, $orderBy = 'created_at', $orderDirection = 'DESC')
    {
        $allowedColumns = ['id', 'titre', 'categorie', 'priorite', 'statut', 'votes', 'created_at'];
        $allowedDirections = ['ASC', 'DESC'];

        $orderBy = in_array($orderBy, $allowedColumns) ? $orderBy : 'created_at';
        $orderDirection = in_array(strtoupper($orderDirection), $allowedDirections) ? strtoupper($orderDirection) : 'DESC';

        $searchTerm = '%' . $query . '%';
        $sql = "SELECT i.*, b.titre AS brainstorming_titre, b.accepted AS brainstorming_accepted,
                       u.nom AS user_nom, u.prenom AS user_prenom
                FROM Idee AS i
                INNER JOIN Brainstorming AS b ON i.brainstorming_id = b.id
                INNER JOIN User AS u ON i.user_id = u.id
                WHERE i.titre LIKE :query
                   OR i.contenu LIKE :query
                   OR i.categorie LIKE :query
                   OR CONCAT(u.prenom, ' ', u.nom) LIKE :query
                   OR b.titre LIKE :query
                ORDER BY i.$orderBy $orderDirection";
        $db = Config::getConnexion();
        $stmt = $db->prepare($sql);
        $stmt->execute(['query' => $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllByBrainstorming($brainstormingId)
    {
        $sql = "SELECT i.*, b.titre AS brainstorming_titre, b.accepted AS brainstorming_accepted,
                       u.nom AS user_nom, u.prenom AS user_prenom
                FROM Idee AS i
                INNER JOIN Brainstorming AS b ON i.brainstorming_id = b.id
                INNER JOIN User AS u ON i.user_id = u.id
                WHERE i.brainstorming_id = :brainstorming_id
                ORDER BY i.created_at DESC";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['brainstorming_id' => $brainstormingId]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT i.*, b.titre AS brainstorming_titre,
                       b.description AS brainstorming_description,
                       b.date_debut AS brainstorming_date_debut,
                       b.created_at AS brainstorming_created_at,
                       b.accepted AS brainstorming_accepted,
                       u.nom AS user_nom, u.prenom AS user_prenom
                FROM Idee AS i
                INNER JOIN Brainstorming AS b ON i.brainstorming_id = b.id
                INNER JOIN User AS u ON i.user_id = u.id
                WHERE i.id = :id";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id' => $id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function createIdeeWithValidation($data)
    {
        $cleanData = self::sanitizeData($data);

        if (!$this->validate($cleanData)) {
            return [
                'success' => false,
                'message' => $this->getFirstValidationError(),
                'errors' => $this->validationErrors
            ];
        }

        $sql = "INSERT INTO Idee (titre, contenu, categorie, priorite, statut, votes, brainstorming_id, user_id)
                VALUES (:titre, :contenu, :categorie, :priorite, :statut, :votes, :brainstorming_id, :user_id)";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute($cleanData);
            return [
                'success' => true,
                'brainstorming_id' => $cleanData['brainstorming_id']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l enregistrement : ' . $e->getMessage()
            ];
        }
    }

    public function updateIdeeWithValidation($id, $data)
    {
        $cleanData = self::sanitizeData($data);

        if (!$this->validate($cleanData, false)) {
            return [
                'success' => false,
                'message' => $this->getFirstValidationError(),
                'errors' => $this->validationErrors
            ];
        }

        $sql = "UPDATE Idee
                SET titre = :titre,
                    contenu = :contenu,
                    categorie = :categorie,
                    priorite = :priorite,
                    statut = :statut,
                    votes = :votes,
                    brainstorming_id = :brainstorming_id,
                    user_id = :user_id
                WHERE id = :id";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute($cleanData + ['id' => $id]);
            return [
                'success' => true,
                'brainstorming_id' => $cleanData['brainstorming_id']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise a jour : ' . $e->getMessage()
            ];
        }
    }

    public function updateStatus($id, $status)
    {
        $sql = "UPDATE Idee SET statut = :statut WHERE id = :id";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        return $query->execute([
            'statut' => $status,
            'id' => $id
        ]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM Idee WHERE id = :id";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        return $query->execute(['id' => $id]);
    }

    public static function sanitizeData($data)
    {
        return [
            'titre' => htmlspecialchars(strip_tags(trim($data['titre'] ?? '')), ENT_QUOTES, 'UTF-8'),
            'contenu' => htmlspecialchars(strip_tags(trim($data['contenu'] ?? '')), ENT_QUOTES, 'UTF-8'),
            'categorie' => htmlspecialchars(strip_tags(trim($data['categorie'] ?? 'General')), ENT_QUOTES, 'UTF-8'),
            'priorite' => trim($data['priorite'] ?? 'moyenne'),
            'statut' => trim($data['statut'] ?? 'proposee'),
            'votes' => max(0, (int) ($data['votes'] ?? 0)),
            'brainstorming_id' => (int) ($data['brainstorming_id'] ?? 0),
            'user_id' => (int) ($data['user_id'] ?? 0),
        ];
    }

    public function validate($data, $validateUser = true)
    {
        $this->validationErrors = [];
        $badContentFilter = new BadContentFilterService();

        if (strlen($data['titre']) < 4 || strlen($data['titre']) > 120) {
            $this->validationErrors['titre'] = 'Le titre doit contenir entre 4 et 120 caracteres.';
        }

        if (strlen($data['contenu']) < 10 || strlen($data['contenu']) > 3000) {
            $this->validationErrors['contenu'] = 'Le contenu doit contenir entre 10 et 3000 caracteres.';
        }

        $moderation = $badContentFilter->validateFields([
            'titre' => $data['titre'] ?? '',
            'contenu' => $data['contenu'] ?? '',
        ]);

        if (!$moderation['valid']) {
            foreach ($moderation['errors'] as $field => $message) {
                $this->validationErrors[$field] = $message;
            }
        }

        if (!in_array($data['priorite'], ['faible', 'moyenne', 'haute'], true)) {
            $this->validationErrors['priorite'] = 'La priorite choisie est invalide.';
        }

        if (!in_array($data['statut'], ['proposee', 'en_etude', 'approuvee', 'rejetee'], true)) {
            $this->validationErrors['statut'] = 'Le statut choisi est invalide.';
        }

        if ($data['brainstorming_id'] <= 0) {
            $this->validationErrors['brainstorming_id'] = 'Veuillez choisir un brainstorming valide.';
        }

        if ($validateUser && $data['user_id'] <= 0) {
            $this->validationErrors['user_id'] = 'Utilisateur invalide.';
        }

        return empty($this->validationErrors);
    }

    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    public function getFirstValidationError()
    {
        return !empty($this->validationErrors) ? reset($this->validationErrors) : 'Erreur de validation.';
    }
}
