<?php
require_once(__DIR__ . '/../config.php');

class InteractionController
{
    // ── ADD LIKE ──────────────────────────────────────────────────────
    public function addInteraction($id_user_from, $id_user_to, $type = 'like')
    {
        if ($id_user_from == $id_user_to) {
            return false; // Cannot like/dislike self
        }
        
        $sql = "INSERT INTO Interaction (id_user_from, id_user_to, type)
                VALUES (:id_user_from, :id_user_to, :type)
                ON DUPLICATE KEY UPDATE type=:type";
        
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':id_user_from' => $id_user_from,
                ':id_user_to'   => $id_user_to,
                ':type'         => $type,
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // ── REMOVE INTERACTION ────────────────────────────────────────────
    public function removeInteraction($id_user_from, $id_user_to)
    {
        $sql = "DELETE FROM Interaction WHERE id_user_from=:from AND id_user_to=:to";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':from' => $id_user_from,
                ':to'   => $id_user_to,
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // ── GET INTERACTION ───────────────────────────────────────────────
    public function getInteraction($id_user_from, $id_user_to)
    {
        $sql = "SELECT type FROM Interaction WHERE id_user_from=:from AND id_user_to=:to";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':from' => $id_user_from,
                ':to'   => $id_user_to,
            ]);
            $row = $query->fetch();
            return $row ? $row['type'] : null;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return null;
        }
    }

    // ── GET LIKES FOR USER ────────────────────────────────────────────
    public function getLikesCount($id_user)
    {
        $sql = "SELECT COUNT(*) as count FROM Interaction WHERE id_user_to=:user AND type='like'";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':user' => $id_user]);
            $row = $query->fetch();
            return $row['count'] ?? 0;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return 0;
        }
    }

    // ── GET DISLIKES FOR USER ─────────────────────────────────────────
    public function getDislikesCount($id_user)
    {
        $sql = "SELECT COUNT(*) as count FROM Interaction WHERE id_user_to=:user AND type='dislike'";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':user' => $id_user]);
            $row = $query->fetch();
            return $row['count'] ?? 0;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return 0;
        }
    }

    // ── GET RATING ────────────────────────────────────────────────────
    public function calculateRating($id_user)
    {
        $sql = "SELECT 
                SUM(CASE WHEN type='like' THEN 1 ELSE -1 END) as score,
                COUNT(*) as total
                FROM Interaction WHERE id_user_to=:user";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':user' => $id_user]);
            $row = $query->fetch();
            
            $total = $row['total'] ?? 0;
            if ($total == 0) return 0;
            
            $score = $row['score'] ?? 0;
            $rating = (($score + $total) / (2 * $total)) * 5; // Convert to 0-5 scale
            return max(0, min(5, $rating)); // Ensure 0-5 range
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return 0;
        }
    }
}
