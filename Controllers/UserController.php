<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Models/User.php');

class UserController
{
    // ── CREATE ────────────────────────────────────────────────────────
    public function addUser(User $user)
    {
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
            $row['rating'] ?? 0.00
        );
        $user->setIdUser($row['id']);
        $user->setCreatedAt($row['created_at'] ?? null);
        $user->setUpdatedAt($row['updated_at'] ?? null);
        return $user;
    }

    // ── UPDATE ────────────────────────────────────────────────────────
    public function updateUser(User $user)
    {
        $sql = "UPDATE User SET nom=:nom, prenom=:prenom, email=:email,
                niveau=:niveau, id_role=:id_role, availability=:availability, 
                rating=:rating WHERE id=:id";
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
