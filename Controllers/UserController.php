<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Models/User.php');

class UserController
{
    // ── Helper ────────────────────────────────────────────────────────
    private function rowToUser($row)
    {
        $user = new User(
            $row['nom'], $row['prenom'], $row['email'],
            $row['mot_de_passe'], $row['niveau'], $row['id_role'],
            $row['badge_verifie'] ?? 0,
            $row['statut_compte'] ?? 'approuve'
        );
        $user->setIdUser($row['id']);
        return $user;
    }

    // ── CREATE ────────────────────────────────────────────────────────
    public function addUser(User $user)
    {
        $sql = "INSERT INTO user (nom, prenom, email, mot_de_passe, niveau, id_role, statut_compte)
                VALUES (:nom, :prenom, :email, :mot_de_passe, :niveau, :id_role, :statut_compte)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom'           => $user->getNom(),
                'prenom'        => $user->getPrenom(),
                'email'         => $user->getEmail(),
                'mot_de_passe'  => password_hash($user->getMotDePasse(), PASSWORD_BCRYPT),
                'niveau'        => $user->getNiveau(),
                'id_role'       => $user->getIdRole(),
                'statut_compte' => $user->getStatutCompte(),
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // ── READ ALL ──────────────────────────────────────────────────────
    public function listUsers()
    {
        $db = Config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM user ORDER BY id DESC");
            $query->execute();
            return array_map([$this, 'rowToUser'], $query->fetchAll());
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    // ── LIST FREELANCERS EN ATTENTE ───────────────────────────────────
    public function listPendingFreelancers()
    {
        $db = Config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM user WHERE id_role = 3 AND statut_compte = 'en_attente' ORDER BY id DESC");
            $query->execute();
            return array_map([$this, 'rowToUser'], $query->fetchAll());
        } catch (Exception $e) {
            return [];
        }
    }

    // ── APPROUVER / REFUSER COMPTE ────────────────────────────────────
    public function changerStatutCompte($id, $statut)
    {
        $db = Config::getConnexion();
        try {
            $query = $db->prepare("UPDATE user SET statut_compte = :s WHERE id = :id");
            $query->execute(['s' => $statut, 'id' => $id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ── READ ONE ──────────────────────────────────────────────────────
    public function getUserById($id)
    {
        $db = Config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM user WHERE id = :id");
            $query->execute(['id' => $id]);
            $row = $query->fetch();
            return $row ? $this->rowToUser($row) : null;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return null;
        }
    }

    // ── READ BY EMAIL ─────────────────────────────────────────────────
    public function getUserByEmail($email)
    {
        $db = Config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM user WHERE email = :email");
            $query->execute(['email' => $email]);
            $row = $query->fetch();
            return $row ? $this->rowToUser($row) : null;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return null;
        }
    }

    // ── UPDATE ────────────────────────────────────────────────────────
    public function updateUser(User $user)
    {
        $sql = "UPDATE user SET nom=:nom, prenom=:prenom, email=:email,
                niveau=:niveau, id_role=:id_role WHERE id=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom'     => $user->getNom(),
                'prenom'  => $user->getPrenom(),
                'email'   => $user->getEmail(),
                'niveau'  => $user->getNiveau(),
                'id_role' => $user->getIdRole(),
                'id'      => $user->getIdUser(),
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // ── UPDATE PASSWORD ───────────────────────────────────────────────
    public function updatePassword($id, $newPassword)
    {
        $sql = "UPDATE user SET mot_de_passe=:mdp WHERE id=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'mdp' => password_hash($newPassword, PASSWORD_BCRYPT),
                'id'  => $id,
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // ── BADGE ─────────────────────────────────────────────────────────
    public function updateBadge($id, $status)
    {
        $sql = "UPDATE user SET badge_verifie=:b WHERE id=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['b' => $status, 'id' => $id]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // ── DELETE ────────────────────────────────────────────────────────
    public function deleteUser($id)
    {
        $sql = "DELETE FROM user WHERE id=:id";
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
            if ($user->getStatutCompte() === 'en_attente') {
                return 'en_attente';
            }
            if ($user->getStatutCompte() === 'refuse') {
                return 'refuse';
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
            $q = $db->prepare("SELECT COUNT(*) FROM user WHERE email=:e AND id!=:id");
            $q->execute(['e' => $email, 'id' => $excludeId]);
        } else {
            $q = $db->prepare("SELECT COUNT(*) FROM user WHERE email=:e");
            $q->execute(['e' => $email]);
        }
        return $q->fetchColumn() > 0;
    }
}
