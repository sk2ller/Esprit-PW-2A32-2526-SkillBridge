<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Models/User.php');

class UserController
{
    // ── CREATE ────────────────────────────────────────────────────────
    public function addUser(User $user)
    {
        $sql = "INSERT INTO User (nom, prenom, email, mot_de_passe, niveau, id_role)
                VALUES (:nom, :prenom, :email, :mot_de_passe, :niveau, :id_role)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom'          => $user->getNom(),
                'prenom'       => $user->getPrenom(),
                'email'        => $user->getEmail(),
                'mot_de_passe' => password_hash($user->getMotDePasse(), PASSWORD_BCRYPT),
                'niveau'       => $user->getNiveau(),
                'id_role'      => $user->getIdRole(),
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
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
                $user = new User(
                    $row['nom'], $row['prenom'], $row['email'],
                    $row['mot_de_passe'], $row['niveau'], $row['id_role'],
                    $row['badge_verifie'] ?? 0
                );
                $user->setIdUser($row['id']);
                $users[] = $user;
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
                $user = new User(
                    $row['nom'], $row['prenom'], $row['email'],
                    $row['mot_de_passe'], $row['niveau'], $row['id_role'],
                    $row['badge_verifie'] ?? 0
                );
                $user->setIdUser($row['id']);
                return $user;
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
                $user = new User(
                    $row['nom'], $row['prenom'], $row['email'],
                    $row['mot_de_passe'], $row['niveau'], $row['id_role'],
                    $row['badge_verifie'] ?? 0
                );
                $user->setIdUser($row['id']);
                return $user;
            }
            return null;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return null;
        }
    }

    // ── UPDATE ────────────────────────────────────────────────────────
    public function updateUser(User $user)
    {
        $sql = "UPDATE User SET nom=:nom, prenom=:prenom, email=:email,
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
        $sql = "UPDATE User SET mot_de_passe=:mdp WHERE id=:id";
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
        $sql = "UPDATE User SET badge_verifie=:b WHERE id=:id";
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
}
