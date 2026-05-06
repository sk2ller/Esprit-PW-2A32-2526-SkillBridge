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
            'profile_picture' => "ALTER TABLE `User` ADD COLUMN `profile_picture` varchar(1024) DEFAULT NULL AFTER `bio`",
            'skill_summary' => "ALTER TABLE `User` ADD COLUMN `skill_summary` varchar(255) DEFAULT NULL AFTER `profile_picture`",
            'experience_description' => "ALTER TABLE `User` ADD COLUMN `experience_description` text DEFAULT NULL AFTER `skill_summary`",
            'face_descriptor' => "ALTER TABLE `User` ADD COLUMN `face_descriptor` text DEFAULT NULL AFTER `experience_description`",
            'face_verification_enabled' => "ALTER TABLE `User` ADD COLUMN `face_verification_enabled` tinyint(1) DEFAULT 0 AFTER `face_descriptor`",
            'face_verification_setup_at' => "ALTER TABLE `User` ADD COLUMN `face_verification_setup_at` datetime DEFAULT NULL AFTER `face_verification_enabled`",
            'email_verified' => "ALTER TABLE `User` ADD COLUMN `email_verified` tinyint(1) DEFAULT 1 AFTER `is_banned`",
            'email_verification_code' => "ALTER TABLE `User` ADD COLUMN `email_verification_code` varchar(255) DEFAULT NULL AFTER `email_verified`",
            'email_verification_expires_at' => "ALTER TABLE `User` ADD COLUMN `email_verification_expires_at` datetime DEFAULT NULL AFTER `email_verification_code`",
            'two_factor_enabled' => "ALTER TABLE `User` ADD COLUMN `two_factor_enabled` tinyint(1) DEFAULT 0 AFTER `email_verification_expires_at`",
            'two_factor_setup_at' => "ALTER TABLE `User` ADD COLUMN `two_factor_setup_at` datetime DEFAULT NULL AFTER `two_factor_enabled`",
            'two_factor_code' => "ALTER TABLE `User` ADD COLUMN `two_factor_code` varchar(255) DEFAULT NULL AFTER `two_factor_enabled`",
            'two_factor_expires_at' => "ALTER TABLE `User` ADD COLUMN `two_factor_expires_at` datetime DEFAULT NULL AFTER `two_factor_code`",
            'last_login_at' => "ALTER TABLE `User` ADD COLUMN `last_login_at` datetime DEFAULT NULL AFTER `two_factor_expires_at`",
        ];

        foreach ($columns as $columnName => $sql) {
            $check = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'User' AND COLUMN_NAME = ?");
            $check->execute([$columnName]);
            if ((int) $check->fetchColumn() === 0) {
                $db->exec($sql);
            }
        }

        $pictureColumn = $db->prepare("SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'User' AND COLUMN_NAME = 'profile_picture'");
        $pictureColumn->execute();
        $pictureLength = (int) $pictureColumn->fetchColumn();
        if ($pictureLength > 0 && $pictureLength < 1024) {
            $db->exec("ALTER TABLE `User` MODIFY COLUMN `profile_picture` varchar(1024) DEFAULT NULL");
        }

        $db->exec("ALTER TABLE `User` MODIFY COLUMN `two_factor_enabled` tinyint(1) DEFAULT 0");
    }

    private function ensureUserSecurityLogTable()
    {
        $db = Config::getConnexion();
        $db->exec("
            CREATE TABLE IF NOT EXISTS `user_security_log` (
                `id_log` int(11) NOT NULL AUTO_INCREMENT,
                `id_user` int(11) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `event_type` varchar(80) NOT NULL,
                `status` varchar(30) NOT NULL DEFAULT 'info',
                `details` varchar(255) DEFAULT NULL,
                `ip_address` varchar(64) DEFAULT NULL,
                `user_agent` varchar(255) DEFAULT NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_log`),
                KEY `idx_security_user` (`id_user`),
                CONSTRAINT `fk_security_user`
                    FOREIGN KEY (`id_user`) REFERENCES `User`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }

    public function saveFaceDescriptor($userId, array $descriptor)
    {
        $this->ensureUserProfileColumns();
        $db = Config::getConnexion();
        $stmt = $db->prepare("UPDATE User SET face_descriptor = :descriptor, face_verification_enabled = 1, face_verification_setup_at = NOW() WHERE id = :id");
        return $stmt->execute([
            'descriptor' => json_encode(array_values($descriptor)),
            'id' => (int) $userId
        ]);
    }

    public function disableFaceVerification($userId)
    {
        $this->ensureUserProfileColumns();
        $db = Config::getConnexion();
        $stmt = $db->prepare("UPDATE User SET face_descriptor = NULL, face_verification_enabled = 0, face_verification_setup_at = NULL WHERE id = :id");
        return $stmt->execute(['id' => (int) $userId]);
    }

    public function getFaceVerificationByEmail($email)
    {
        $this->ensureUserProfileColumns();
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT id, nom, prenom, email, id_role, is_approved, is_banned, email_verified, face_descriptor FROM User WHERE email = :email AND face_verification_enabled = 1 AND face_descriptor IS NOT NULL");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function faceDescriptorDistance(array $first, array $second)
    {
        if (count($first) !== count($second) || count($first) === 0) {
            return PHP_FLOAT_MAX;
        }

        $sum = 0.0;
        foreach ($first as $index => $value) {
            $diff = (float)$value - (float)$second[$index];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
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
    public function createSecurityCode()
    {
        return (string) random_int(100000, 999999);
    }

    public function setEmailVerificationCode($userId, $code, $minutes = 20)
    {
        $this->ensureUserProfileColumns();
        $db = Config::getConnexion();
        $minutes = max(1, (int) $minutes);
        $stmt = $db->prepare("UPDATE User SET email_verified = 0, email_verification_code = :code, email_verification_expires_at = DATE_ADD(NOW(), INTERVAL {$minutes} MINUTE) WHERE id = :id");
        return $stmt->execute([
            'code' => password_hash($code, PASSWORD_BCRYPT),
            'id' => (int) $userId
        ]);
    }

    public function verifyEmailCode($email, $code)
    {
        $this->ensureUserProfileColumns();
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT id, email_verification_code, email_verification_expires_at, NOW() AS db_now FROM User WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || empty($row['email_verification_code'])) {
            return null;
        }
        if (!empty($row['email_verification_expires_at']) && $row['email_verification_expires_at'] < $row['db_now']) {
            return null;
        }
        if (!password_verify($code, $row['email_verification_code'])) {
            return null;
        }

        return (int) $row['id'];
    }

    public function markEmailVerified($userId)
    {
        $this->ensureUserProfileColumns();
        $db = Config::getConnexion();
        $stmt = $db->prepare("UPDATE User SET email_verified = 1, email_verification_code = NULL, email_verification_expires_at = NULL WHERE id = :id");
        return $stmt->execute(['id' => (int) $userId]);
    }

    public function setTwoFactorCode($userId, $code, $minutes = 10)
    {
        $this->ensureUserProfileColumns();
        $db = Config::getConnexion();
        $minutes = max(1, (int) $minutes);
        $stmt = $db->prepare("UPDATE User SET two_factor_code = :code, two_factor_expires_at = DATE_ADD(NOW(), INTERVAL {$minutes} MINUTE) WHERE id = :id");
        return $stmt->execute([
            'code' => password_hash($code, PASSWORD_BCRYPT),
            'id' => (int) $userId
        ]);
    }

    public function setTwoFactorEnabled($userId, $enabled)
    {
        $this->ensureUserProfileColumns();
        $db = Config::getConnexion();
        if ((int) $enabled === 1) {
            $stmt = $db->prepare("UPDATE User SET two_factor_enabled = 1, two_factor_setup_at = COALESCE(two_factor_setup_at, NOW()) WHERE id = :id");
        } else {
            $stmt = $db->prepare("UPDATE User SET two_factor_enabled = 0, two_factor_setup_at = NULL, two_factor_code = NULL, two_factor_expires_at = NULL WHERE id = :id");
        }

        return $stmt->execute(['id' => (int) $userId]);
    }

    public function verifyTwoFactorCode($userId, $code)
    {
        $this->ensureUserProfileColumns();
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT two_factor_code, two_factor_expires_at, NOW() AS db_now FROM User WHERE id = :id");
        $stmt->execute(['id' => (int) $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || empty($row['two_factor_code'])) {
            return false;
        }
        if (!empty($row['two_factor_expires_at']) && $row['two_factor_expires_at'] < $row['db_now']) {
            return false;
        }
        if (!password_verify($code, $row['two_factor_code'])) {
            return false;
        }

        $clear = $db->prepare("UPDATE User SET two_factor_code = NULL, two_factor_expires_at = NULL WHERE id = :id");
        $clear->execute(['id' => (int) $userId]);
        return true;
    }

    public function getUserSecurityState($userId)
    {
        $this->ensureUserProfileColumns();
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT email_verified, two_factor_enabled, two_factor_setup_at, last_login_at, face_verification_enabled, face_verification_setup_at FROM User WHERE id = :id");
        $stmt->execute(['id' => (int) $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function recordLoginSuccess($userId)
    {
        $this->ensureUserProfileColumns();
        $db = Config::getConnexion();
        $stmt = $db->prepare("UPDATE User SET last_login_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => (int) $userId]);
    }

    public function logSecurityEvent($userId, $email, $eventType, $status = 'info', $details = null)
    {
        $this->ensureUserSecurityLogTable();
        $db = Config::getConnexion();
        $stmt = $db->prepare("INSERT INTO user_security_log (id_user, email, event_type, status, details, ip_address, user_agent) VALUES (:id_user, :email, :event_type, :status, :details, :ip_address, :user_agent)");
        return $stmt->execute([
            'id_user' => $userId ? (int) $userId : null,
            'email' => $email,
            'event_type' => $eventType,
            'status' => $status,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'local',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255)
        ]);
    }

    public function getRecentSecurityLogs($userId, $limit = 8)
    {
        $this->ensureUserSecurityLogTable();
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT event_type, status, details, ip_address, user_agent, created_at FROM user_security_log WHERE id_user = :id ORDER BY created_at DESC LIMIT " . (int) $limit);
        $stmt->execute(['id' => (int) $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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
