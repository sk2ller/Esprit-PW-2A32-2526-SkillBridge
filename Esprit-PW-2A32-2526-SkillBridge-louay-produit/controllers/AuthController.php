<?php
/**
 * AuthController — SkillBridge
 * Gère l'authentification : inscription, connexion, déconnexion
 * Suit le même pattern que ProduitController.php
 */
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../models/User.php');

class AuthController
{
    // =========================================================
    // INSCRIPTION CLIENT
    // =========================================================
    /**
     * Inscrit un nouveau client
     * Champs requis : name, email, password, confirm_password
     * @return array ['success' => bool, 'errors' => [], 'user' => User|null]
     */
    public function registerClient($data)
    {
        $errors = [];

        // --- Validation côté serveur ---
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        if (empty($name)) {
            $errors[] = "Le nom est requis.";
        } elseif (strlen($name) < 2) {
            $errors[] = "Le nom doit contenir au moins 2 caractères.";
        }

        if (empty($email)) {
            $errors[] = "L'email est requis.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email n'est pas valide.";
        }

        if (empty($password)) {
            $errors[] = "Le mot de passe est requis.";
        } elseif (strlen($password) < 6) {
            $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
        }

        if ($password !== $confirmPassword) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        // Vérifier si l'email existe déjà
        if (empty($errors) && $this->emailExists($email)) {
            $errors[] = "Cet email est déjà utilisé.";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors, 'user' => null];
        }

        // --- Insertion en base ---
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'client')";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':name' => htmlspecialchars($name),
                ':email' => htmlspecialchars($email),
                ':password' => $hashedPassword
            ]);

            $userId = $db->lastInsertId();
            $user = $this->getUserById($userId);

            return ['success' => true, 'errors' => [], 'user' => $user];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['Erreur serveur: ' . $e->getMessage()], 'user' => null];
        }
    }

    // =========================================================
    // INSCRIPTION VENDEUR (FREELANCER)
    // =========================================================
    /**
     * Inscrit un nouveau vendeur / freelancer
     * Champs additionnels : skills, bio, portfolio_url
     * @return array ['success' => bool, 'errors' => [], 'user' => User|null]
     */
    public function registerFreelancer($data)
    {
        $errors = [];

        // --- Validation côté serveur ---
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        $skills = trim($data['skills'] ?? '');
        $bio = trim($data['bio'] ?? '');
        $portfolioUrl = trim($data['portfolio_url'] ?? '');

        if (empty($name)) {
            $errors[] = "Le nom est requis.";
        } elseif (strlen($name) < 2) {
            $errors[] = "Le nom doit contenir au moins 2 caractères.";
        }

        if (empty($email)) {
            $errors[] = "L'email est requis.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email n'est pas valide.";
        }

        if (empty($password)) {
            $errors[] = "Le mot de passe est requis.";
        } elseif (strlen($password) < 6) {
            $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
        }

        if ($password !== $confirmPassword) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        if (empty($skills)) {
            $errors[] = "Les compétences sont requises.";
        }

        if (empty($bio)) {
            $errors[] = "La bio est requise.";
        } elseif (strlen($bio) < 10) {
            $errors[] = "La bio doit contenir au moins 10 caractères.";
        }

        if (!empty($portfolioUrl) && !filter_var($portfolioUrl, FILTER_VALIDATE_URL)) {
            $errors[] = "L'URL du portfolio n'est pas valide.";
        }

        // Vérifier si l'email existe déjà
        if (empty($errors) && $this->emailExists($email)) {
            $errors[] = "Cet email est déjà utilisé.";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors, 'user' => null];
        }

        // --- Insertion en base ---
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password, role, skills, bio, portfolio_url)
                VALUES (:name, :email, :password, 'vendeur', :skills, :bio, :portfolio_url)";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':name' => htmlspecialchars($name),
                ':email' => htmlspecialchars($email),
                ':password' => $hashedPassword,
                ':skills' => htmlspecialchars($skills),
                ':bio' => htmlspecialchars($bio),
                ':portfolio_url' => htmlspecialchars($portfolioUrl)
            ]);

            $userId = $db->lastInsertId();
            $user = $this->getUserById($userId);

            return ['success' => true, 'errors' => [], 'user' => $user];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['Erreur serveur: ' . $e->getMessage()], 'user' => null];
        }
    }

    // =========================================================
    // CONNEXION (pour tous les rôles)
    // =========================================================
    /**
     * Connecte un utilisateur par email + mot de passe
     * Vérifie que le rôle correspond au rôle demandé
     * @param string $email
     * @param string $password
     * @param string $expectedRole — 'client', 'vendeur', ou 'admin'
     * @return array ['success' => bool, 'errors' => [], 'user' => User|null]
     */
    public function login($email, $password, $expectedRole = null)
    {
        $errors = [];

        $email = trim($email);
        if (empty($email)) {
            $errors[] = "L'email est requis.";
        }
        if (empty($password)) {
            $errors[] = "Le mot de passe est requis.";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors, 'user' => null];
        }

        // Chercher l'utilisateur par email
        $user = $this->getUserByEmail($email);

        if (!$user) {
            return ['success' => false, 'errors' => ['Aucun compte trouvé avec cet email.'], 'user' => null];
        }

        // Vérifier le mot de passe
        if (!password_verify($password, $user->getPassword())) {
            return ['success' => false, 'errors' => ['Mot de passe incorrect.'], 'user' => null];
        }

        // Vérifier le rôle si un rôle spécifique est attendu
        if ($expectedRole && $user->getRole() !== $expectedRole) {
            $roleLabels = ['client' => 'Client', 'vendeur' => 'Freelancer', 'admin' => 'Administrateur'];
            $expected = $roleLabels[$expectedRole] ?? $expectedRole;
            return ['success' => false, 'errors' => ["Ce compte n'est pas un compte $expected."], 'user' => null];
        }

        return ['success' => true, 'errors' => [], 'user' => $user];
    }

    // =========================================================
    // RECONNAISSANCE FACIALE
    // =========================================================
    /**
     * Enregistre un descripteur facial pour un utilisateur existant
     */
    public function registerFace($email, $faceDescriptor)
    {
        $user = $this->getUserByEmail($email);
        if (!$user) {
            return ['success' => false, 'error' => "Utilisateur non trouvé avec cet email."];
        }

        $sql = "UPDATE users SET face_descriptor = :descriptor WHERE email = :email";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':descriptor' => $faceDescriptor,
                ':email' => $email
            ]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()];
        }
    }

    /**
     * Tente de connecter un utilisateur via la reconnaissance faciale
     */
    public function loginFace($email, $liveDescriptorJson)
    {
        $user = $this->getUserByEmail($email);
        if (!$user) {
            return ['success' => false, 'error' => "Utilisateur non trouvé avec cet email."];
        }

        $storedDescriptorStr = $user->getFaceDescriptor();
        if (empty($storedDescriptorStr)) {
            return ['success' => false, 'error' => "Aucun visage n'est enregistré pour ce compte. Veuillez vous connecter avec votre mot de passe et l'enregistrer d'abord."];
        }

        // Convertir les descripteurs JSON en tableaux de floats
        $storedDesc = json_decode($storedDescriptorStr);
        $liveDesc = json_decode($liveDescriptorJson);

        if (!is_array($storedDesc) || !is_array($liveDesc) || count($storedDesc) !== 128 || count($liveDesc) !== 128) {
            return ['success' => false, 'error' => "Données faciales invalides."];
        }

        // Calculer la distance euclidienne
        $distance = 0.0;
        for ($i = 0; $i < 128; $i++) {
            $diff = $storedDesc[$i] - $liveDesc[$i];
            $distance += $diff * $diff;
        }
        $distance = sqrt($distance);

        // Seuil d'acceptation (généralement 0.6 pour face-api.js)
        if ($distance < 0.6) {
            return ['success' => true, 'user' => $user];
        } else {
            return ['success' => false, 'error' => "Le visage ne correspond pas. (Distance: " . round($distance, 2) . ")"];
        }
    }

    // =========================================================
    // MOT DE PASSE OUBLIÉ
    // =========================================================
    /**
     * Génère un token de réinitialisation et le stocke
     */
    public function generateResetToken($email)
    {
        if (empty($email)) {
            return ['success' => false, 'error' => "L'email est requis."];
        }

        $user = $this->getUserByEmail($email);
        if (!$user) {
            // Pour la sécurité, ne pas révéler si l'email existe
            return ['success' => false, 'error' => "Si cet email existe, un lien de réinitialisation sera généré."];
        }

        // Générer un token unique avec expiration (1 heure)
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now
        $tokenData = json_encode(['token' => $token, 'expiry' => $expiry]);

        $sql = "UPDATE users SET remember_token = :token WHERE email = :email";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':token' => $tokenData, ':email' => $email]);
            return ['success' => true, 'token' => $token];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Erreur serveur.'];
        }
    }

    /**
     * Réinitialise le mot de passe avec un token valide
     */
    public function resetPassword($email, $token, $newPassword, $confirmPassword)
    {
        if (empty($email) || empty($token)) {
            return ['success' => false, 'error' => 'Données manquantes.'];
        }
        if (empty($newPassword)) {
            return ['success' => false, 'error' => 'Le nouveau mot de passe est requis.'];
        }
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'error' => 'Le mot de passe doit contenir au moins 6 caractères.'];
        }
        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'error' => 'Les mots de passe ne correspondent pas.'];
        }

        $user = $this->getUserByEmail($email);
        if (!$user) {
            return ['success' => false, 'error' => 'Utilisateur non trouvé.'];
        }

        // Vérifier le token
        $storedTokenData = $user->getRememberToken();
        if (empty($storedTokenData)) {
            return ['success' => false, 'error' => 'Aucune demande de réinitialisation trouvée. Veuillez refaire la demande.'];
        }

        $tokenInfo = json_decode($storedTokenData, true);
        if (!$tokenInfo || !isset($tokenInfo['token']) || !isset($tokenInfo['expiry'])) {
            return ['success' => false, 'error' => 'Token invalide.'];
        }

        // Vérifier la correspondance
        if (!hash_equals($tokenInfo['token'], $token)) {
            return ['success' => false, 'error' => 'Token de réinitialisation incorrect.'];
        }

        // Vérifier l'expiration
        if (strtotime($tokenInfo['expiry']) < time()) {
            return ['success' => false, 'error' => 'Ce lien a expiré. Veuillez refaire une demande.'];
        }

        // Mettre à jour le mot de passe
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = :password, remember_token = NULL WHERE email = :email";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':password' => $hashedPassword, ':email' => $email]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Erreur serveur.'];
        }
    }

    // =========================================================
    // GESTION DE SESSION
    // =========================================================
    /**
     * Crée la session utilisateur après connexion
     */
    public function createSession(User $user)
    {
        $_SESSION['user'] = [
            'id'    => $user->getId(),
            'name'  => $user->getName(),
            'email' => $user->getEmail(),
            'role'  => $user->getRole()
        ];
        // Le rôle est aussi utilisé par l'ancien système de routing
        $_SESSION['role'] = $user->getRole();
    }

    /**
     * Détruit la session (déconnexion)
     */
    public function logout()
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Vérifie si un utilisateur est connecté
     */
    public static function isLoggedIn()
    {
        return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }

    /**
     * Retourne le rôle de l'utilisateur connecté
     */
    public static function getCurrentRole()
    {
        return $_SESSION['user']['role'] ?? null;
    }

    /**
     * Retourne le nom de l'utilisateur connecté
     */
    public static function getCurrentUserName()
    {
        return $_SESSION['user']['name'] ?? 'Invité';
    }

    /**
     * Retourne l'URL de redirection selon le rôle
     */
    public static function getDashboardUrl($role)
    {
        switch ($role) {
            case 'admin':
                return 'index.php?page=admin_dashboard';
            case 'vendeur':
                return 'index.php?page=mes_produits';
            case 'client':
            default:
                return 'index.php?page=home';
        }
    }

    // =========================================================
    // MÉTHODES INTERNES
    // =========================================================

    /**
     * Cherche un utilisateur par son ID
     */
    public function getUserById($id)
    {
        $sql = "SELECT * FROM users WHERE id_user = :id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            $row = $query->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return $this->rowToUser($row);
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Cherche un utilisateur par email
     */
    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':email' => $email]);
            $row = $query->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return $this->rowToUser($row);
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Vérifie si un email est déjà pris
     */
    private function emailExists($email)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':email' => $email]);
            return (int)$query->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Convertit une ligne PDO en objet User
     */
    private function rowToUser($row)
    {
        $user = new User();
        $user->setId($row['id_user']);
        $user->setName($row['name']);
        $user->setEmail($row['email']);
        $user->setPassword($row['password']);
        $user->setRole($row['role']);
        $user->setSkills($row['skills']);
        $user->setBio($row['bio']);
        $user->setPortfolioUrl($row['portfolio_url']);
        $user->setFaceDescriptor($row['face_descriptor'] ?? null);
        $user->setRememberToken($row['remember_token']);
        $user->setCreatedAt($row['created_at']);
        $user->setUpdatedAt($row['updated_at']);
        return $user;
    }

    // =========================================================
    // TOKEN CSRF
    // =========================================================
    /**
     * Génère un token CSRF et le stocke en session
     */
    public static function generateCsrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie un token CSRF
     */
    public static function verifyCsrfToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Statistiques utilisateurs pour le dashboard admin
     */
    public function getUserStats()
    {
        $stats = ['total' => 0, 'clients' => 0, 'vendeurs' => 0, 'admins' => 0];
        $db = Config::getConnexion();
        try {
            $query = $db->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
            $query->execute();
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $stats['total'] += (int)$row['count'];
                if ($row['role'] === 'client') $stats['clients'] = (int)$row['count'];
                if ($row['role'] === 'vendeur') $stats['vendeurs'] = (int)$row['count'];
                if ($row['role'] === 'admin') $stats['admins'] = (int)$row['count'];
            }
        } catch (Exception $e) {
            // Silently fail — stats are not critical
        }
        return $stats;
    }
}
?>
