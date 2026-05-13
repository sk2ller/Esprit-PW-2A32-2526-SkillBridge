<?php
class Brainstorming
{
    private $id;
    private $titre;
    private $description;
    private $date_debut;
    private $accepted;
    private $user_id;
    private $created_at;

    // Propriétés de validation
    private $validationErrors = [];

    public function __construct(
        $titre = null,
        $description = null,
        $date_debut = null,
        $user_id = null,
        $accepted = 0
    ) {
        $this->titre       = $titre;
        $this->description = $description;
        $this->date_debut  = $date_debut;
        $this->user_id     = $user_id;
        $this->accepted    = $accepted;
    }

    // Getters et Setters existants
    public function getId()             { return $this->id; }
    public function setId($id)          { $this->id = $id; }
    public function getTitre()          { return $this->titre; }
    public function setTitre($titre)    { $this->titre = $titre; }
    public function getDescription()    { return $this->description; }
    public function setDescription($d)  { $this->description = $d; }
    public function getDateDebut()      { return $this->date_debut; }
    public function setDateDebut($d)    { $this->date_debut = $d; }
    public function getAccepted()       { return $this->accepted; }
    public function setAccepted($a)     { $this->accepted = $a; }
    public function getUserId()         { return $this->user_id; }
    public function setUserId($uid)     { $this->user_id = $uid; }
    public function getCreatedAt()      { return $this->created_at; }
    public function setCreatedAt($date) { $this->created_at = $date; }

    /**
     * Valide les données du brainstorming
     * @return bool True si valide, false sinon
     */
    public function validate()
    {
        $this->validationErrors = [];

        // Validation du titre
        $this->validateTitre();

        // Validation de la description
        $this->validateDescription();

        // Validation de la date
        $this->validateDateDebut();

        // Validation de l'user_id
        $this->validateUserId();

        return empty($this->validationErrors);
    }

    /**
     * Valide le titre
     */
    private function validateTitre()
    {
        $titre = trim($this->titre);

        if (empty($titre)) {
            $this->validationErrors['titre'] = "Le titre est obligatoire.";
            return;
        }

        if (strlen($titre) < 5) {
            $this->validationErrors['titre'] = "Le titre doit contenir au moins 5 caractères.";
            return;
        }

        if (strlen($titre) > 100) {
            $this->validationErrors['titre'] = "Le titre ne peut pas dépasser 100 caractères.";
            return;
        }

        // Vérifier les caractères autorisés (lettres, chiffres, espaces, ponctuation basique)
        if (!preg_match('/^[a-zA-Z0-9À-ÿ\s\-\.,!?\'"]+$/u', $titre)) {
            $this->validationErrors['titre'] = "Le titre contient des caractères non autorisés.";
        }
    }

    /**
     * Valide la description
     */
    private function validateDescription()
    {
        $description = trim($this->description);

        if (empty($description)) {
            $this->validationErrors['description'] = "La description est obligatoire.";
            return;
        }

        if (strlen($description) < 20) {
            $this->validationErrors['description'] = "La description doit contenir au moins 20 caractères.";
            return;
        }

        if (strlen($description) > 2000) {
            $this->validationErrors['description'] = "La description ne peut pas dépasser 2000 caractères.";
            return;
        }
    }

    /**
     * Valide la date de début
     */
    private function validateDateDebut()
    {
        if (empty($this->date_debut)) {
            $this->validationErrors['date_debut'] = "La date de début est obligatoire.";
            return;
        }

        // Vérifier le format YYYY-MM-DD
        $dateTime = DateTime::createFromFormat('Y-m-d', $this->date_debut);
        if (!$dateTime || $dateTime->format('Y-m-d') !== $this->date_debut) {
            $this->validationErrors['date_debut'] = "Format de date invalide (utilisez YYYY-MM-DD).";
            return;
        }

        // Vérifier que la date n'est pas dans le passé
        $today = new DateTime();
        $today->setTime(0, 0, 0, 0);
        if ($dateTime < $today) {
            $this->validationErrors['date_debut'] = "La date de début ne peut pas être dans le passé.";
            return;
        }

        // Vérifier que la date n'est pas trop lointaine (max 1 an)
        $maxDate = new DateTime('+1 year');
        if ($dateTime > $maxDate) {
            $this->validationErrors['date_debut'] = "La date de début ne peut pas être supérieure à un an.";
        }
    }

    /**
     * Valide l'ID utilisateur
     */
    private function validateUserId()
    {
        if (empty($this->user_id) || !is_numeric($this->user_id)) {
            $this->validationErrors['user_id'] = "ID utilisateur invalide.";
            return;
        }

        $userId = (int)$this->user_id;
        if ($userId <= 0) {
            $this->validationErrors['user_id'] = "ID utilisateur doit être un nombre positif.";
        }
    }

    /**
     * Nettoie et sécurise les données d'entrée
     * @param array $data Données brutes du formulaire
     * @return array Données nettoyées
     */
    public static function sanitizeData($data)
    {
        $sanitized = [];

        if (isset($data['titre'])) {
            $sanitized['titre'] = htmlspecialchars(strip_tags(trim($data['titre'])), ENT_QUOTES, 'UTF-8');
        }

        if (isset($data['description'])) {
            // Pour la description, on permet quelques balises HTML basiques
            $sanitized['description'] = strip_tags(trim($data['description']), '<p><br><strong><em><u>');
            $sanitized['description'] = htmlspecialchars($sanitized['description'], ENT_QUOTES, 'UTF-8');
        }

        if (isset($data['date_debut'])) {
            $sanitized['date_debut'] = trim($data['date_debut']);
        }

        if (isset($data['user_id'])) {
            $sanitized['user_id'] = (int)$data['user_id'];
        }

        return $sanitized;
    }

    /**
     * Retourne les erreurs de validation
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    /**
     * Retourne la première erreur de validation
     * @return string|null
     */
    public function getFirstValidationError()
    {
        return !empty($this->validationErrors) ? reset($this->validationErrors) : null;
    }

    /**
     * Vérifie si il y a des erreurs de validation
     * @return bool
     */
    public function hasValidationErrors()
    {
        return !empty($this->validationErrors);
    }

    /**
     * Définit les propriétés depuis un tableau de données
     * @param array $data
     */
    public function setFromArray($data)
    {
        if (isset($data['titre'])) $this->titre = $data['titre'];
        if (isset($data['description'])) $this->description = $data['description'];
        if (isset($data['date_debut'])) $this->date_debut = $data['date_debut'];
        if (isset($data['user_id'])) $this->user_id = $data['user_id'];
        if (isset($data['accepted'])) $this->accepted = $data['accepted'];
        if (isset($data['id'])) $this->id = $data['id'];
        if (isset($data['created_at'])) $this->created_at = $data['created_at'];
    }
}
