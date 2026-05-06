<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../pusher_config.php';

class MessageController
{
    // ── ENVOYER UN MESSAGE ────────────────────────────────────────────
    public function envoyerMessage($id_projet, $id_expediteur, $contenu)
    {
        $db = Config::getConnexion();
        try {
            $id_projet     = (int)$id_projet;
            $id_expediteur = (int)$id_expediteur;
            $contenu       = trim($contenu);

            if (!$contenu) {
                return ['success' => false, 'message' => 'Le message ne peut pas être vide.'];
            }
            if (mb_strlen($contenu) > 500) {
                return ['success' => false, 'message' => 'Message trop long (500 caractères max).'];
            }

            if (!$this->hasAccess($id_projet, $id_expediteur)) {
                return ['success' => false, 'message' => 'Accès refusé.'];
            }

            $q = $db->prepare("INSERT INTO message (id_projet, id_expediteur, contenu) VALUES (:p, :e, :c)");
            $q->execute(['p' => $id_projet, 'e' => $id_expediteur, 'c' => $contenu]);
            $newId = (int)$db->lastInsertId();

            // Récupérer le nom de l'expéditeur
            $q = $db->prepare("SELECT nom, prenom FROM user WHERE id=:id");
            $q->execute(['id' => $id_expediteur]);
            $user = $q->fetch();

            // ── Pusher : diffuser le message en temps réel ────────────
            $payload = [
                'id'          => $newId,
                'contenu'     => $contenu,
                'expediteur'  => ($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''),
                'id_expediteur' => $id_expediteur,
                'heure'       => date('d/m H:i'),
            ];
            pusherTrigger('chat-projet-' . $id_projet, 'nouveau-message', $payload);

            return ['success' => true, 'id' => $newId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    // ── RÉCUPÉRER TOUS LES MESSAGES D'UN PROJET ───────────────────────
    /**
     * Returns all messages for a project. The requesting user must be the client
     * owner OR an accepted freelancer on the project.
     */
    public function getMessages($id_projet, $id_user)
    {
        $db = Config::getConnexion();
        try {
            $id_projet = (int)$id_projet;
            $id_user   = (int)$id_user;

            if (!$this->hasAccess($id_projet, $id_user)) {
                return ['success' => false, 'message' => 'Accès refusé.'];
            }

            $q = $db->prepare(
                "SELECT m.id, m.contenu, m.created_at, m.id_expediteur,
                        u.nom AS nom_exp, u.prenom AS prenom_exp, u.id_role AS role_exp
                 FROM message m
                 JOIN user u ON u.id = m.id_expediteur
                 WHERE m.id_projet = :p
                 ORDER BY m.id ASC"
            );
            $q->execute(['p' => $id_projet]);
            $rows = $q->fetchAll(PDO::FETCH_ASSOC);

            $messages = [];
            foreach ($rows as $row) {
                $messages[] = [
                    'id'         => (int)$row['id'],
                    'contenu'    => $row['contenu'],
                    'expediteur' => $row['prenom_exp'] . ' ' . $row['nom_exp'],
                    'role'       => (int)$row['role_exp'],
                    'heure'      => date('d/m H:i', strtotime($row['created_at'])),
                    'is_mine'    => ((int)$row['id_expediteur'] === $id_user),
                ];
            }

            return ['success' => true, 'messages' => $messages];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    // ── RÉCUPÉRER LES NOUVEAUX MESSAGES (polling) ─────────────────────
    public function getNouveauxMessages($id_projet, $id_user, $depuis_id)
    {
        $db = Config::getConnexion();
        try {
            $id_projet = (int)$id_projet;
            $id_user   = (int)$id_user;
            $depuis_id = (int)$depuis_id;

            if (!$this->hasAccess($id_projet, $id_user)) {
                return ['success' => false, 'message' => 'Accès refusé.'];
            }

            $q = $db->prepare(
                "SELECT m.id, m.contenu, m.created_at, m.id_expediteur,
                        u.nom AS nom_exp, u.prenom AS prenom_exp, u.id_role AS role_exp
                 FROM message m
                 JOIN user u ON u.id = m.id_expediteur
                 WHERE m.id_projet = :p AND m.id > :d
                 ORDER BY m.id ASC"
            );
            $q->execute(['p' => $id_projet, 'd' => $depuis_id]);
            $rows = $q->fetchAll(PDO::FETCH_ASSOC);

            $messages = [];
            foreach ($rows as $row) {
                $messages[] = [
                    'id'         => (int)$row['id'],
                    'contenu'    => $row['contenu'],
                    'expediteur' => $row['prenom_exp'] . ' ' . $row['nom_exp'],
                    'role'       => (int)$row['role_exp'],
                    'heure'      => date('d/m H:i', strtotime($row['created_at'])),
                    'is_mine'    => ((int)$row['id_expediteur'] === $id_user),
                ];
            }

            return ['success' => true, 'messages' => $messages];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    // ── VÉRIFICATION D'ACCÈS ──────────────────────────────────────────
    /**
     * Returns true if $id_user is the client owner of the project
     * OR has an accepted candidature on the project.
     */
    private function hasAccess($id_projet, $id_user)
    {
        $db = Config::getConnexion();

        // Client owner?
        $q = $db->prepare("SELECT id FROM projet WHERE id = :p AND id_client = :u");
        $q->execute(['p' => $id_projet, 'u' => $id_user]);
        if ($q->fetch()) return true;

        // Accepted freelancer?
        $q = $db->prepare(
            "SELECT id FROM candidature WHERE id_projet = :p AND id_freelancer = :u AND statut = 'accepte'"
        );
        $q->execute(['p' => $id_projet, 'u' => $id_user]);
        if ($q->fetch()) return true;

        return false;
    }

    // ── NOTIFICATIONS : messages non lus ─────────────────────────────
    public function getNotifications($id_user)
    {
        $db = Config::getConnexion();
        try {
            $id_user = (int)$id_user;

            // Récupérer les projets accessibles — deux requêtes séparées pour éviter les problèmes PDO avec UNION
            $projets = [];
            $ids_vus = [];

            // Projets dont l'utilisateur est client
            $q = $db->prepare("SELECT id AS id_projet, titre AS titre_projet FROM projet WHERE id_client = :u");
            $q->execute(['u' => $id_user]);
            foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if (!in_array($row['id_projet'], $ids_vus)) {
                    $projets[] = $row;
                    $ids_vus[] = $row['id_projet'];
                }
            }

            // Projets où l'utilisateur est freelancer accepté
            $q = $db->prepare("SELECT p.id AS id_projet, p.titre AS titre_projet
                FROM projet p
                JOIN candidature c ON c.id_projet = p.id
                WHERE c.id_freelancer = :u AND c.statut = 'accepte'");
            $q->execute(['u' => $id_user]);
            foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if (!in_array($row['id_projet'], $ids_vus)) {
                    $projets[] = $row;
                    $ids_vus[] = $row['id_projet'];
                }
            }

            $notifs = [];
            $total_non_lus = 0;

            foreach ($projets as $proj) {
                // Dernier message lu par cet utilisateur
                $qLu = $db->prepare("SELECT dernier_lu_id FROM message_lu WHERE id_projet=:p AND id_user=:u");
                $qLu->execute(['p' => $proj['id_projet'], 'u' => $id_user]);
                $lu = $qLu->fetch();
                $dernier_lu = $lu ? (int)$lu['dernier_lu_id'] : 0;

                // Compter les messages non lus (pas envoyés par l'utilisateur)
                $qCount = $db->prepare("SELECT COUNT(*) AS cnt FROM message WHERE id_projet=:p AND id > :d AND id_expediteur != :u");
                $qCount->execute(['p' => $proj['id_projet'], 'd' => $dernier_lu, 'u' => $id_user]);
                $cnt = (int)$qCount->fetch()['cnt'];

                if ($cnt > 0) {
                    // Dernier message non lu
                    $qLast = $db->prepare("SELECT m.contenu, m.created_at, u.prenom, u.nom
                        FROM message m
                        JOIN user u ON u.id = m.id_expediteur
                        WHERE m.id_projet=:p AND m.id > :d AND m.id_expediteur != :u
                        ORDER BY m.id DESC LIMIT 1");
                    $qLast->execute(['p' => $proj['id_projet'], 'd' => $dernier_lu, 'u' => $id_user]);
                    $last = $qLast->fetch();
                    if (!$last) continue;

                    $notifs[] = [
                        'id_projet'    => $proj['id_projet'],
                        'titre_projet' => $proj['titre_projet'],
                        'expediteur'   => $last['prenom'] . ' ' . $last['nom'],
                        'apercu'       => mb_substr($last['contenu'], 0, 60) . (mb_strlen($last['contenu']) > 60 ? '…' : ''),
                        'heure'        => date('d/m H:i', strtotime($last['created_at'])),
                        'count'        => $cnt,
                    ];
                    $total_non_lus += $cnt;
                }
            }

            return ['success' => true, 'notifs' => $notifs, 'total' => $total_non_lus];
        } catch (Exception $e) {
            error_log('getNotifications error: ' . $e->getMessage());
            return ['success' => false, 'notifs' => [], 'total' => 0, 'error' => $e->getMessage()];
        }
    }

    // ── MARQUER LU (un projet) ────────────────────────────────────────
    public function marquerLu($id_projet, $id_user)
    {
        $db = Config::getConnexion();
        try {
            $q = $db->prepare("SELECT MAX(id) AS max_id FROM message WHERE id_projet=:p");
            $q->execute(['p' => $id_projet]);
            $row = $q->fetch();
            $max_id = (int)($row['max_id'] ?? 0);
            if (!$max_id) return ['success' => true];

            $q = $db->prepare("INSERT INTO message_lu (id_projet, id_user, dernier_lu_id)
                VALUES (:p, :u, :d)
                ON DUPLICATE KEY UPDATE dernier_lu_id = :d2");
            $q->execute(['p' => $id_projet, 'u' => $id_user, 'd' => $max_id, 'd2' => $max_id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false];
        }
    }

    // ── MARQUER TOUT LU ───────────────────────────────────────────────
    public function marquerToutLu($id_user)
    {
        $db = Config::getConnexion();
        try {
            $q = $db->prepare("
                SELECT DISTINCT m.id_projet, MAX(m.id) AS max_id
                FROM message m
                WHERE m.id_expediteur != :u
                GROUP BY m.id_projet
            ");
            $q->execute(['u' => $id_user]);
            $rows = $q->fetchAll();
            foreach ($rows as $row) {
                if (!$this->hasAccess($row['id_projet'], $id_user)) continue;
                $q2 = $db->prepare("INSERT INTO message_lu (id_projet, id_user, dernier_lu_id)
                    VALUES (:p, :u, :d)
                    ON DUPLICATE KEY UPDATE dernier_lu_id = :d2");
                $q2->execute(['p' => $row['id_projet'], 'u' => $id_user, 'd' => $row['max_id'], 'd2' => $row['max_id']]);
            }
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false];
        }
    }
}
