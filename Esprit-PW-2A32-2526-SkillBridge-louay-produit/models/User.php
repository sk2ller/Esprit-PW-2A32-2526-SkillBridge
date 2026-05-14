<?php
/**
 * Modèle User — SkillBridge
 * Représente un utilisateur avec gestion des rôles (client, vendeur, admin)
 * Suit le même pattern que Produit.php et Commande.php
 */
class User
{
    // --- Propriétés privées ---
    private $id;
    private $name;
    private $email;
    private $password;
    private $role;       // 'client', 'vendeur', 'admin'
    private $skills;     // Compétences (vendeur uniquement), stockées en CSV
    private $bio;        // Biographie courte (vendeur uniquement)
    private $portfolioUrl;
    private $faceDescriptor;
    private $rememberToken;
    private $createdAt;
    private $updatedAt;

    // --- Getters ---
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }
    public function getSkills() { return $this->skills; }
    public function getBio() { return $this->bio; }
    public function getPortfolioUrl() { return $this->portfolioUrl; }
    public function getFaceDescriptor() { return $this->faceDescriptor; }
    public function getRememberToken() { return $this->rememberToken; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }

    // --- Setters ---
    public function setId($id) { $this->id = $id; }
    public function setName($name) { $this->name = $name; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { $this->password = $password; }
    public function setRole($role) { $this->role = $role; }
    public function setSkills($skills) { $this->skills = $skills; }
    public function setBio($bio) { $this->bio = $bio; }
    public function setPortfolioUrl($url) { $this->portfolioUrl = $url; }
    public function setFaceDescriptor($descriptor) { $this->faceDescriptor = $descriptor; }
    public function setRememberToken($token) { $this->rememberToken = $token; }
    public function setCreatedAt($date) { $this->createdAt = $date; }
    public function setUpdatedAt($date) { $this->updatedAt = $date; }

    /**
     * Retourne les compétences sous forme de tableau
     * Les skills sont stockées en CSV dans la base (ex: "HTML,CSS,JS")
     */
    public function getSkillsArray()
    {
        if (empty($this->skills)) {
            return [];
        }
        return array_map('trim', explode(',', $this->skills));
    }

    /**
     * Retourne les initiales du nom (pour l'avatar)
     * Ex: "Ahmed Ben Ali" → "AB"
     */
    public function getInitials()
    {
        $parts = explode(' ', $this->name);
        $initials = '';
        foreach ($parts as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        return $initials ?: '?';
    }

    /**
     * Retourne le label du rôle en français
     */
    public function getRoleLabel()
    {
        $labels = [
            'client'  => 'Client',
            'vendeur' => 'Freelancer',
            'admin'   => 'Administrateur'
        ];
        return $labels[$this->role] ?? 'Inconnu';
    }
}
?>
