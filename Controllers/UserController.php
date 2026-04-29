<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Models/User.php');

class UserController
{
    private function ensureUserProfileColumns()
    {
        $db = Config::getConnexion();
        $columns = [
            'availability' => "ALTER TABLE `User` ADD COLUMN `availability` varchar(50) DEFAULT 'available' AFTER `is_banned`",
            'rating' => "ALTER TABLE `User` ADD COLUMN `rating` decimal(3,2) DEFAULT 0.00 AFTER `availability`",
            'phone' => "ALTER TABLE `User` ADD COLUMN `phone` varchar(30) DEFAULT NULL AFTER `rating`",
            'bio' => "ALTER TABLE `User` ADD COLUMN `bio` text DEFAULT NULL AFTER `phone`",
            'profile_picture' => "ALTER TABLE `User` ADD COLUMN `profile_picture` varchar(255) DEFAULT NULL AFTER `bio`",
            'skill_summary' => "ALTER TABLE `User` ADD COLUMN `skill_summary` varchar(255) DEFAULT NULL AFTER `profile_picture`",
            'experience_description' => "ALTER TABLE `User` ADD COLUMN `experience_description` text DEFAULT NULL AFTER `skill_summary`",
        ];

        foreach ($columns as $columnName => $sql) {
            $check = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'User' AND COLUMN_NAME = ?");
            $check->execute([$columnName]);
            if ((int) $check->fetchColumn() === 0) {
                $db->exec($sql);
            }
        }
    }
    // ── CREATE ────────────────────────────────────────────────────────
    public function addUser(User $user)
    {
        $this->ensureUserProfileColumns();
        $sql = "INSERT INTO User (nom, prenom, email, mot_de_passe, niveau, id_role, is_approved)
                VALUES (:nom, :prenom, :email, :mot_de_passe, :niveau, :id_role, :is_approved)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom'         => $user->getNom(),
                'prenom'      => $user->getPrenom(),
                'email'       => $user->getEmail(),
                'mot_de_passe'=> password_hash($user->getMotDePasse(), PASSWORD_BCRYPT),
                'niveau'      => $user->getNiveau(),
                'id_role'     => $user->getIdRole(),
                'is_approved' => $user->getIsApproved(),
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // ── READ ALL ──────────────────────────────────────────────────────
    public function listUsers()
    {
        $this->ensureUserProfileColumns();
        $sql = "SELECT * FROM User ORDER BY id DESC";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            $rows = $query->fetchAll();

            $users = [];
            foreach ($rows as $row) {
                $users[] = $this->hydrationUser($row);
            }
            return $users;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    // ── READ ONE ──────────────────────────────────────────────────────
    public function getUserById($id)
    {
        $this->ensureUserProfileColumns();
        $sql = "SELECT * FROM User WHERE id = :id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            $row = $query->fetch();
            if ($row) {
                return $this->hydrationUser($row);
            }
            return null;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return null;
        }
    }

    // ── READ BY EMAIL ─────────────────────────────────────────────────
    public function getUserByEmail($email)
    {
        $this->ensureUserProfileColumns();
        $sql = "SELECT * FROM User WHERE email = :email";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['email' => $email]);
            $row = $query->fetch();
            if ($row) {
                return $this->hydrationUser($row);
            }
            return null;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return null;
        }
    }

    // ── Helper Method: Hydrate User Object ─────────────────────────────
    private function hydrationUser($row)
    {
        $user = new User(
            $row['nom'] ?? null, $row['prenom'] ?? null, $row['email'] ?? null,
            $row['mot_de_passe'] ?? null, $row['niveau'] ?? 'débutant', 
            $row['id_role'] ?? 2, $row['badge_verifie'] ?? 0,
            $row['is_approved'] ?? 0, $row['is_banned'] ?? 0, $row['availability'] ?? 'available',
            $row['rating'] ?? 0.00, $row['phone'] ?? null, $row['bio'] ?? null,
            $row['profile_picture'] ?? null, $row['skill_summary'] ?? null,
            $row['experience_description'] ?? null
        );
        $user->setIdUser($row['id']);
        $user->setCreatedAt($row['created_at'] ?? null);
        $user->setUpdatedAt($row['updated_at'] ?? null);
        return $user;
    }

    // ── UPDATE ────────────────────────────────────────────────────────
    public function updateUser(User $user)
    {
        $this->ensureUserProfileColumns();
        $sql = "UPDATE User SET nom=:nom, prenom=:prenom, email=:email,
                niveau=:niveau, id_role=:id_role, availability=:availability,
                rating=:rating, phone=COALESCE(:phone, phone), bio=COALESCE(:bio, bio),
                profile_picture=COALESCE(:profile_picture, profile_picture),
                skill_summary=COALESCE(:skill_summary, skill_summary),
                experience_description=COALESCE(:experience_description, experience_description)
                WHERE id=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom'          => $user->getNom(),
                'prenom'       => $user->getPrenom(),
                'email'        => $user->getEmail(),
                'niveau'       => $user->getNiveau(),
                'id_role'      => $user->getIdRole(),
                'availability' => $user->getAvailability(),
                'rating'       => $user->getRating(),
                'phone'        => $user->getPhone(),
                'bio'          => $user->getBio(),
                'profile_picture' => $user->getProfilePicture(),
                'skill_summary' => $user->getSkillSummary(),
                'experience_description' => $user->getExperienceDescription(),
                'id'           => $user->getIdUser(),
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // ── UPDATE PASSWORD ───────────────────────────────────────────────
    public function updatePassword($id, $newPassword)
    {
        $this->ensureUserProfileColumns();
        $sql = "UPDATE User SET mot_de_passe=:mdp WHERE id=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'mdp' => password_hash($newPassword, PASSWORD_BCRYPT),
                'id'  => $id,
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // ── BADGE ─────────────────────────────────────────────────────────
    public function updateBadge($id, $status)
    {
        $this->ensureUserProfileColumns();
        $sql = "UPDATE User SET badge_verifie=:b WHERE id=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['b' => $status, 'id' => $id]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // ── EMAIL VERIFICATION ────────────────────────────────────────────
    // (Removed - using admin approval only)

    // ── APPROVE / DISAPPROVE USER ─────────────────────────────────────
    public function approveUser($id, $approved = 1)
    {
        $this->ensureUserProfileColumns();
        $sql = "UPDATE User SET is_approved=:approved WHERE id=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['approved' => $approved, 'id' => $id]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // ── BAN / UNBAN USER ──────────────────────────────────────────────
    public function banUser($id, $isBanned = 1)
    {
        $this->ensureUserProfileColumns();
        $sql = "UPDATE User SET is_banned=:is_banned WHERE id=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['is_banned' => $isBanned, 'id' => $id]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // ── GET FREELANCERS ───────────────────────────────────────────────
    public function getFreelancers($filters = [])
    {
        $this->ensureUserProfileColumns();
        $sql = "SELECT * FROM User WHERE id_role = 3 AND is_approved = 1 AND is_banned = 0";
        $params = [];
        
        // Apply filters
        if (!empty($filters['niveau'])) {
            $sql .= " AND niveau = :niveau";
            $params[':niveau'] = $filters['niveau'];
        }
        if (!empty($filters['availability'])) {
            $sql .= " AND availability = :availability";
            $params[':availability'] = $filters['availability'];
        }
        if (isset($filters['min_rating']) && $filters['min_rating'] !== '') {
            $sql .= " AND rating >= :min_rating";
            $params[':min_rating'] = (float) $filters['min_rating'];
        }
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (nom LIKE :search OR prenom LIKE :search)";
            $params[':search'] = $search;
        }
        
        $sql .= " ORDER BY rating DESC";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            $rows = $query->fetchAll();
            
            $freelancers = [];
            foreach ($rows as $row) {
                $freelancers[] = $this->hydrationUser($row);
            }
            return $freelancers;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    // ── GET PENDING USERS (For Admin Approval) ───────────────────────
    public function getPendingUsers()
    {
        $this->ensureUserProfileColumns();
        $sql = "SELECT * FROM User WHERE is_approved = 0 ORDER BY created_at ASC";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            $rows = $query->fetchAll();
            
            $users = [];
            foreach ($rows as $row) {
                $users[] = $this->hydrationUser($row);
            }
            return $users;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    // ── SEARCH USERS (For Admin Search/Filter) ────────────────────────
    public function searchUsers($filters = [])
    {
        $this->ensureUserProfileColumns();
        $sql = "SELECT * FROM User WHERE 1=1";
        $params = [];
        
        // Apply name search
        if (!empty($filters['search'])) {
            $sql .= " AND (nom LIKE :search OR prenom LIKE :search OR email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        // Apply role filter
        if (!empty($filters['id_role']) && $filters['id_role'] !== 'all') {
            $sql .= " AND id_role = :id_role";
            $params[':id_role'] = (int)$filters['id_role'];
        }
        
        // Apply approval status filter
        if (!empty($filters['is_approved']) && $filters['is_approved'] !== 'all') {
            $sql .= " AND is_approved = :is_approved";
            $params[':is_approved'] = ($filters['is_approved'] === '1') ? 1 : 0;
        }
        
        // Apply niveau filter
        if (!empty($filters['niveau']) && $filters['niveau'] !== 'all') {
            $sql .= " AND niveau = :niveau";
            $params[':niveau'] = $filters['niveau'];
        }
        
        $sql .= " ORDER BY id DESC";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            $rows = $query->fetchAll();
            
            $users = [];
            foreach ($rows as $row) {
                $users[] = $this->hydrationUser($row);
            }
            return $users;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    // ── DELETE ────────────────────────────────────────────────────────
    public function deleteUser($id)
    {
        $this->ensureUserProfileColumns();
        $sql = "DELETE FROM User WHERE id=:id";
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

    // ── LOGIN ─────────────────────────────────────────────────────────
    public function authenticate($email, $password)
    {
        $user = $this->getUserByEmail($email);
        if ($user && password_verify($password, $user->getMotDePasse())) {
            // Only freelancers need to be approved before they can login
            if ($user->getIdRole() == 3 && !$user->getIsApproved()) {
                return false; // Unapproved freelancer
            }
            return $user;
        }
        return false;
    }

    // ── EMAIL EXISTS ──────────────────────────────────────────────────
    public function emailExists($email, $excludeId = null)
    {
        $this->ensureUserProfileColumns();
        $db = Config::getConnexion();
        if ($excludeId) {
            $q = $db->prepare("SELECT COUNT(*) FROM User WHERE email=:e AND id!=:id");
            $q->execute(['e' => $email, 'id' => $excludeId]);
        } else {
            $q = $db->prepare("SELECT COUNT(*) FROM User WHERE email=:e");
            $q->execute(['e' => $email]);
        }
        return $q->fetchColumn() > 0;
    }

    // ── GENERATE VERIFICATION TOKEN ───────────────────────────────────
    // (Removed - using admin approval only)
}
