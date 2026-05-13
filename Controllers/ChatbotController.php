<?php
require_once __DIR__ . '/../config.php';

class ChatbotController
{
    const GEMINI_API_KEY       = 'AIzaSyApZHjYH3f0ku_5hdX3WdFWVNusZRJo_CQ';
    const GEMINI_API_URL       = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    const MAX_MESSAGE_LENGTH   = 1000;
    const MAX_RESPONSE_WORDS   = 300;

    // ── Point d'entrée AJAX principal ─────────────────────────────────
    public static function handleRequest(): void
    {
        header('Content-Type: application/json');

        // Tâche 2.1 — Vérification de session et contrôle d'accès
        if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_role'] !== 2) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
            exit;
        }

        // Tâche 2.3 — Validation du message
        $message = trim($_POST['message'] ?? '');
        if ($message === '' || mb_strlen($message) > self::MAX_MESSAGE_LENGTH) {
            echo json_encode(['success' => false, 'message' => 'Message invalide.']);
            exit;
        }

        // Tâche 2.5 — Flux principal
        $historyRaw = $_POST['history'] ?? '';
        $history    = [];
        if ($historyRaw) {
            $decoded = json_decode($historyRaw, true);
            if (is_array($decoded)) {
                $history = $decoded;
            }
        }

        $userId  = (int)$_SESSION['user_id'];
        $prenom  = $_SESSION['user_prenom'] ?? $_SESSION['prenom'] ?? 'Client';

        $context      = self::getClientContext($userId);
        $systemPrompt = self::buildSystemPrompt($context, $prenom);
        $result       = self::callGeminiApi($systemPrompt, $history, $message);

        echo json_encode($result);
        exit;
    }

    // ── Récupère les projets du client avec leurs tâches ──────────────
    private static function getClientContext(int $userId): array
    {
        try {
            $db = Config::getConnexion();

            $qProjets = $db->prepare(
                'SELECT id, titre, statut, etat, budget, avancement, date_creation
                 FROM projet
                 WHERE id_client = :user_id
                 ORDER BY id DESC'
            );
            $qProjets->execute(['user_id' => $userId]);
            $projets = $qProjets->fetchAll(PDO::FETCH_ASSOC);

            $context = [];
            foreach ($projets as $projet) {
                $qTaches = $db->prepare(
                    'SELECT t.titre, t.statut, t.prix, t.payee,
                            u.nom AS nom_freelancer, u.prenom AS prenom_freelancer
                     FROM tache t
                     JOIN user u ON u.id = t.id_freelancer
                     WHERE t.id_projet = :id_projet
                     ORDER BY t.id ASC'
                );
                $qTaches->execute(['id_projet' => $projet['id']]);
                $taches = $qTaches->fetchAll(PDO::FETCH_ASSOC);

                $context[] = [
                    'projet' => $projet,
                    'taches' => $taches,
                ];
            }

            return $context;

        } catch (PDOException $e) {
            error_log('[ChatbotController] Erreur PDO getClientContext user_id=' . $userId . ' : ' . $e->getMessage());
            return [];
        }
    }

    // ── Construit le prompt système en français ───────────────────────
    private static function buildSystemPrompt(array $context, string $prenom): string
    {
        // Tâche 4.1 — Instructions obligatoires
        $prompt  = "Tu es un assistant de gestion de projet pour la plateforme SkillBridge.\n";
        $prompt .= "Tu t'adresses au client " . $prenom . ".\n\n";

        $prompt .= "INSTRUCTIONS OBLIGATOIRES :\n";
        $prompt .= "1. Réponds EXCLUSIVEMENT en français.\n";
        $prompt .= "2. Limite tes réponses aux sujets suivants uniquement : statut et avancement des projets du client, détail des tâches, budget et paiements, conseils de gestion de projet, et fonctionnalités de la plateforme SkillBridge.\n";
        $prompt .= "3. Si une question est hors sujet (non liée aux projets ou à la gestion de projet), refuse poliment et redirige le client vers les sujets pertinents.\n";
        $prompt .= "4. Limite chaque réponse à 300 mots maximum pour rester concis et lisible.\n\n";

        // Tâche 4.3 — Intégration du contexte projet
        if (empty($context)) {
            $prompt .= "CONTEXTE PROJETS :\n";
            $prompt .= "Le client n'a encore créé aucun projet sur SkillBridge. ";
            $prompt .= "Propose-lui de l'aider à créer son premier projet et explique-lui comment démarrer sur la plateforme.\n";
        } else {
            $prompt .= "CONTEXTE PROJETS DU CLIENT :\n";
            foreach ($context as $item) {
                $p = $item['projet'];
                $prompt .= "\n--- Projet : " . $p['titre'] . " ---\n";
                $prompt .= "  Statut       : " . $p['statut'] . "\n";
                $prompt .= "  État         : " . $p['etat'] . "\n";
                $prompt .= "  Budget       : " . number_format((float)$p['budget'], 2, '.', '') . " TND\n";
                $prompt .= "  Avancement   : " . (int)$p['avancement'] . "%\n";
                $prompt .= "  Créé le      : " . $p['date_creation'] . "\n";

                if (!empty($item['taches'])) {
                    $prompt .= "  Tâches :\n";
                    foreach ($item['taches'] as $t) {
                        $payee     = !empty($t['payee']) ? 'Oui' : 'Non';
                        $freelancer = trim($t['prenom_freelancer'] . ' ' . $t['nom_freelancer']);
                        $prompt .= "    - " . $t['titre']
                            . " | Statut: " . $t['statut']
                            . " | Prix: " . number_format((float)$t['prix'], 2, '.', '') . " TND"
                            . " | Payée: " . $payee
                            . " | Freelancer: " . $freelancer . "\n";
                    }
                } else {
                    $prompt .= "  Tâches : Aucune tâche assignée.\n";
                }
            }
        }

        return $prompt;
    }

    // ── Appelle l'API Gemini via curl ─────────────────────────────────
    private static function callGeminiApi(string $systemPrompt, array $history, string $userMessage): array
    {
        $contents   = $history;
        $contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];

        $payload = [
            'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
            'contents'           => $contents,
            'generationConfig'   => ['maxOutputTokens' => 500],
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => self::GEMINI_API_URL,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-goog-api-key: ' . self::GEMINI_API_KEY,
            ],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlErrno !== 0) {
            error_log('[ChatbotController] Erreur curl : ' . $curlError);
            return ['success' => false, 'message' => 'Service IA temporairement indisponible.'];
        }

        if ($httpCode >= 400) {
            error_log('[ChatbotController] Erreur HTTP ' . $httpCode . ' : ' . substr($response, 0, 500));
            return ['success' => false, 'message' => 'Service IA temporairement indisponible.'];
        }

        $data = json_decode($response, true);

        if (!isset($data['candidates'][0]['content']['parts'][0]['text']) ||
            !is_string($data['candidates'][0]['content']['parts'][0]['text'])) {
            error_log('[ChatbotController] Réponse malformée : ' . substr($response, 0, 500));
            return ['success' => false, 'message' => 'Service IA temporairement indisponible.'];
        }

        $text = trim($data['candidates'][0]['content']['parts'][0]['text']);

        // Détecter l'action CREATE_TASK dans la réponse de Gemini
        if (strpos($text, 'ACTION:CREATE_TASK:') === 0) {
            $taskData = json_decode(substr($text, strlen('ACTION:CREATE_TASK:')), true);

            if (is_array($taskData) && !empty($taskData['titre'])) {
                $statuts    = ['a_faire' => 'À faire', 'en_cours' => 'En cours', 'termine' => 'Terminé'];
                $confirmMsg = "✅ Je vais créer la tâche suivante :\n";
                $confirmMsg .= "• Titre : " . ($taskData['titre'] ?? '') . "\n";
                if (!empty($taskData['description'])) {
                    $confirmMsg .= "• Description : " . $taskData['description'] . "\n";
                }
                $confirmMsg .= "• Prix : " . number_format((float)($taskData['prix'] ?? 0), 2, ',', ' ') . " TND\n";
                $confirmMsg .= "• Statut : " . ($statuts[$taskData['statut'] ?? 'a_faire'] ?? 'À faire') . "\n";

                return [
                    'success'   => true,
                    'reply'     => $confirmMsg,
                    'action'    => 'CREATE_TASK',
                    'task_data' => $taskData,
                ];
            }
        }

        return ['success' => true, 'reply' => $text];
    }
}
