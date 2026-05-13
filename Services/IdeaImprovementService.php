<?php
require_once(__DIR__ . '/../config.php');

class IdeaImprovementService
{
    public function suggest(array $idea): array
    {
        $apiKey = Config::getAiApiKey();
        if ($apiKey === '') {
            return [
                'success' => false,
                'message' => 'Cle API manquante. Ajoutez GEMINI_API_KEY ou GOOGLE_API_KEY dans l environnement PHP pour generer les suggestions avec IA.'
            ];
        }

        if (Config::getAiProvider() !== 'gemini') {
            return [
                'success' => false,
                'message' => 'Provider IA non supporte pour les suggestions. Utilisez SKILLBRIDGE_AI_PROVIDER=gemini.'
            ];
        }

        return $this->suggestWithGemini($idea, $apiKey, Config::getAiModel());
    }

    private function suggestWithGemini(array $idea, string $apiKey, string $model): array
    {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'message' => 'Extension cURL indisponible.'];
        }

        $payload = [
            'contents' => [[
                'parts' => [[
                    'text' => $this->buildPrompt($idea)
                ]]
            ]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'improved_title' => ['type' => 'STRING'],
                        'improved_summary' => ['type' => 'STRING'],
                        'target_user' => ['type' => 'STRING'],
                        'problem' => ['type' => 'STRING'],
                        'value_proposition' => ['type' => 'STRING'],
                        'next_steps' => [
                            'type' => 'ARRAY',
                            'items' => ['type' => 'STRING'],
                        ],
                        'questions' => [
                            'type' => 'ARRAY',
                            'items' => ['type' => 'STRING'],
                        ],
                    ],
                    'required' => ['improved_title', 'improved_summary', 'target_user', 'problem', 'value_proposition', 'next_steps', 'questions'],
                ],
                'temperature' => 0.25,
                'maxOutputTokens' => 1400,
            ],
        ];

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($apiKey);
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 25,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError) {
            return ['success' => false, 'message' => 'Erreur reseau Gemini : ' . $curlError];
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 400) {
            $apiMessage = $decoded['error']['message'] ?? 'Erreur Gemini inconnue.';
            if ($this->isQuotaError($apiMessage)) {
                $fallback = $this->suggestLocally($idea);
                $fallback['source'] = 'Suggestions locales gratuites (quota Gemini atteint)';
                $fallback['warning'] = 'Gemini a atteint son quota gratuit temporaire. Suggestions generees localement pour garder la demo fonctionnelle.';
                return $fallback;
            }

            return ['success' => false, 'message' => 'Erreur Gemini : ' . $apiMessage];
        }

        $rawText = $this->extractGeminiText($decoded);
        if ($rawText === '') {
            $reason = $decoded['candidates'][0]['finishReason']
                ?? $decoded['promptFeedback']['blockReason']
                ?? 'reponse vide';
            return ['success' => false, 'message' => 'Gemini n a pas renvoye de texte exploitable (' . $reason . '). Reessayez avec une idee plus detaillee.'];
        }

        $suggestions = $this->decodeJsonResponse($rawText);

        if ($suggestions === null) {
            return ['success' => false, 'message' => 'Gemini a renvoye un format non JSON. Reessayez ou reduisez le texte de l idee.'];
        }

        return [
            'success' => true,
            'suggestions' => $this->normalizeSuggestions($suggestions),
            'source' => 'Gemini API',
        ];
    }

    private function suggestLocally(array $idea): array
    {
        $title = trim((string) ($idea['titre'] ?? ''));
        $content = trim((string) ($idea['contenu'] ?? ''));
        $category = trim((string) ($idea['categorie'] ?? 'General'));
        $wordCount = str_word_count($title . ' ' . $content);
        $lower = function_exists('mb_strtolower') ? mb_strtolower($content, 'UTF-8') : strtolower($content);

        $nextSteps = [];
        $questions = [];

        if (!preg_match('/\b(utilisateur|client|etudiant|freelancer|admin|user|student)\b/i', $lower)) {
            $nextSteps[] = 'Preciser le public cible de l idee.';
            $questions[] = 'Qui va utiliser cette solution en premier ?';
        }

        if (!preg_match('/\b(probleme|besoin|difficulte|pain|objectif)\b/i', $lower)) {
            $nextSteps[] = 'Ajouter le probleme exact que cette idee resout.';
            $questions[] = 'Quel probleme concret cette idee elimine-t-elle ?';
        }

        if (!preg_match('/\b(api|interface|dashboard|notification|mobile|web|base de donnees|prototype)\b/i', $lower)) {
            $nextSteps[] = 'Decrire une premiere version realisable techniquement.';
            $questions[] = 'Quelle fonctionnalite minimale peut etre developpee en premier ?';
        }

        if ($wordCount < 35) {
            $nextSteps[] = 'Developper la description avec un exemple d utilisation.';
            $questions[] = 'A quoi ressemble un cas d usage simple ?';
        }

        $nextSteps = array_slice(array_values(array_unique(array_merge($nextSteps, [
            'Definir un critere de succes mesurable.',
            'Lister les ressources necessaires pour tester l idee.',
            'Identifier un risque principal avant validation.',
        ]))), 0, 3);

        $questions = array_slice(array_values(array_unique(array_merge($questions, [
            'Comment mesurer si cette idee fonctionne ?',
            'Quelle partie doit etre simplifiee pour une premiere version ?',
            'Quelle valeur apporte-t-elle par rapport a une solution classique ?',
        ]))), 0, 3);

        return [
            'success' => true,
            'suggestions' => [
                'improved_title' => $this->buildImprovedTitle($title, $category),
                'improved_summary' => $this->buildImprovedSummary($content),
                'target_user' => preg_match('/\b(etudiant|student)\b/i', $lower) ? 'Etudiants' : 'Utilisateur cible a preciser',
                'problem' => preg_match('/\b(probleme|besoin|difficulte)\b/i', $lower) ? 'Probleme mentionne dans la description.' : 'Probleme principal a formuler plus clairement.',
                'value_proposition' => 'Rendre l idee plus utile, plus claire et plus facile a tester.',
                'next_steps' => $nextSteps,
                'questions' => $questions,
            ],
            'source' => 'Suggestions locales gratuites',
        ];
    }

    private function buildImprovedTitle(string $title, string $category): string
    {
        $title = trim($title);
        if ($title === '') {
            return 'Nouvelle idee ' . $category;
        }

        if (strlen($title) >= 18) {
            return $title;
        }

        return $title . ' - proposition a clarifier';
    }

    private function buildImprovedSummary(string $content): string
    {
        $content = trim($content);
        if ($content === '') {
            return 'Cette idee doit etre completee avec une cible, un probleme et une solution proposee.';
        }

        return 'Cette idee peut etre renforcee en precisant la cible, le probleme resolu, la valeur apportee et une premiere version testable.';
    }

    private function buildPrompt(array $idea): string
    {
        return "Tu es un coach produit pour une plateforme de brainstorming. " .
            "Analyse l idee et retourne uniquement un JSON valide sans markdown. " .
            "Champs obligatoires: improved_title, improved_summary, target_user, problem, value_proposition, next_steps, questions. " .
            "next_steps et questions doivent etre des tableaux de 3 chaines courtes.\n\n" .
            "Titre: " . ($idea['titre'] ?? '') . "\n" .
            "Categorie: " . ($idea['categorie'] ?? '') . "\n" .
            "Priorite: " . ($idea['priorite'] ?? '') . "\n" .
            "Contenu: " . ($idea['contenu'] ?? '');
    }

    private function decodeJsonResponse(string $rawText): ?array
    {
        $rawText = trim($rawText);
        $rawText = preg_replace('/^```(?:json)?\s*/i', '', $rawText);
        $rawText = preg_replace('/\s*```$/', '', $rawText);

        $decoded = json_decode($rawText, true);
        if (is_array($decoded)) {
            return isset($decoded['suggestions']) && is_array($decoded['suggestions'])
                ? $decoded['suggestions']
                : $decoded;
        }

        if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $rawText, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return isset($decoded['suggestions']) && is_array($decoded['suggestions'])
                    ? $decoded['suggestions']
                    : $decoded;
            }
        }

        return null;
    }

    private function isQuotaError(string $message): bool
    {
        $message = strtolower($message);
        return strpos($message, 'quota') !== false
            || strpos($message, 'rate limit') !== false
            || strpos($message, 'exceeded') !== false
            || strpos($message, 'resource_exhausted') !== false;
    }

    private function extractGeminiText(array $decoded): string
    {
        $parts = $decoded['candidates'][0]['content']['parts'] ?? [];
        if (!is_array($parts)) {
            return '';
        }

        $texts = [];
        foreach ($parts as $part) {
            if (isset($part['text']) && is_string($part['text'])) {
                $texts[] = $part['text'];
            }
        }

        return trim(implode("\n", $texts));
    }

    private function normalizeSuggestions(array $suggestions): array
    {
        return [
            'improved_title' => trim((string) ($suggestions['improved_title'] ?? '')),
            'improved_summary' => trim((string) ($suggestions['improved_summary'] ?? '')),
            'target_user' => trim((string) ($suggestions['target_user'] ?? '')),
            'problem' => trim((string) ($suggestions['problem'] ?? '')),
            'value_proposition' => trim((string) ($suggestions['value_proposition'] ?? '')),
            'next_steps' => $this->normalizeList($suggestions['next_steps'] ?? []),
            'questions' => $this->normalizeList($suggestions['questions'] ?? []),
        ];
    }

    private function normalizeList($items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $items = array_values(array_filter(array_map(fn($item) => trim((string) $item), $items)));
        return array_slice($items, 0, 3);
    }
}
