<?php
require_once(__DIR__ . '/../config.php');

class BrainstormingSummaryService
{
    public function summarize(array $brainstorming, array $idees): array
    {
        $apiKey = Config::getAiApiKey();
        if ($apiKey === '') {
            return [
                'success' => false,
                'message' => 'Cle API manquante. Ajoutez GEMINI_API_KEY ou GOOGLE_API_KEY pour generer le resume IA.'
            ];
        }

        if (Config::getAiProvider() !== 'gemini') {
            return [
                'success' => false,
                'message' => 'Provider IA non supporte. Utilisez SKILLBRIDGE_AI_PROVIDER=gemini.'
            ];
        }

        return $this->summarizeWithGemini($brainstorming, $idees, $apiKey, Config::getAiModel());
    }

    private function summarizeWithGemini(array $brainstorming, array $idees, string $apiKey, string $model): array
    {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'message' => 'Extension cURL indisponible.'];
        }

        $payload = [
            'contents' => [[
                'parts' => [[
                    'text' => $this->buildPrompt($brainstorming, $idees)
                ]]
            ]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'overview' => ['type' => 'STRING'],
                        'dominant_topics' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                        'strongest_ideas' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                        'risks' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                        'recommendations' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                        'decision' => ['type' => 'STRING'],
                    ],
                    'required' => ['overview', 'dominant_topics', 'strongest_ideas', 'risks', 'recommendations', 'decision'],
                ],
                'temperature' => 0.2,
                'maxOutputTokens' => 1600,
            ],
        ];

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($apiKey);
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 30,
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
            $apiMessage = $decoded['error']['message'] ?? 'Erreur inconnue.';
            if ($this->isQuotaError($apiMessage)) {
                $fallback = $this->summarizeLocally($brainstorming, $idees);
                $fallback['source'] = 'Resume local gratuit (quota Gemini atteint)';
                $fallback['warning'] = 'Gemini a atteint son quota gratuit temporaire. Resume genere localement pour garder la demo fonctionnelle.';
                return $fallback;
            }

            return ['success' => false, 'message' => 'Erreur Gemini : ' . $apiMessage];
        }

        $rawText = $this->extractGeminiText($decoded);
        if ($rawText === '') {
            $reason = $decoded['candidates'][0]['finishReason'] ?? $decoded['promptFeedback']['blockReason'] ?? 'reponse vide';
            return ['success' => false, 'message' => 'Gemini n a pas renvoye de resume exploitable (' . $reason . ').'];
        }

        $summary = $this->decodeJsonResponse($rawText);
        if ($summary === null) {
            return ['success' => false, 'message' => 'Gemini a renvoye un format non JSON. Reessayez.'];
        }

        return [
            'success' => true,
            'summary' => $this->normalizeSummary($summary),
            'source' => 'Gemini API',
        ];
    }

    private function summarizeLocally(array $brainstorming, array $idees): array
    {
        $ideaCount = count($idees);
        $categories = [];
        $statuses = [];
        $topIdeas = $idees;

        foreach ($idees as $idee) {
            $category = trim((string) ($idee['categorie'] ?? 'General'));
            $status = trim((string) ($idee['statut'] ?? 'proposee'));
            $categories[$category] = ($categories[$category] ?? 0) + 1;
            $statuses[$status] = ($statuses[$status] ?? 0) + 1;
        }

        arsort($categories);
        usort($topIdeas, function ($a, $b) {
            return ((int) ($b['votes'] ?? 0)) <=> ((int) ($a['votes'] ?? 0));
        });

        $dominantTopics = array_slice(array_map(function ($category, $count) {
            return $category . ' (' . $count . ' idee' . ($count > 1 ? 's' : '') . ')';
        }, array_keys($categories), array_values($categories)), 0, 4);

        $strongestIdeas = array_slice(array_map(function ($idee) {
            return ($idee['titre'] ?? 'Idee') . ' - ' . (int) ($idee['votes'] ?? 0) . ' vote(s)';
        }, $topIdeas), 0, 4);

        if (empty($dominantTopics)) {
            $dominantTopics = ['Aucun theme dominant detecte.'];
        }

        if (empty($strongestIdeas)) {
            $strongestIdeas = ['Aucune idee liee pour le moment.'];
        }

        return [
            'success' => true,
            'summary' => [
                'overview' => 'Ce brainstorming contient ' . $ideaCount . ' idee' . ($ideaCount > 1 ? 's' : '') . '. Les tendances sont deduites localement a partir des categories, statuts et votes.',
                'dominant_topics' => $dominantTopics,
                'strongest_ideas' => $strongestIdeas,
                'risks' => [
                    $ideaCount === 0 ? 'Pas encore assez d idees pour prendre une decision.' : 'Analyse locale moins riche que Gemini.',
                    'Verifier manuellement les idees avant validation finale.',
                ],
                'recommendations' => [
                    'Ajouter ou clarifier les idees les plus prometteuses.',
                    'Prioriser les idees avec votes et categorie claire.',
                    'Relancer Gemini quand le quota API redevient disponible.',
                ],
                'decision' => $ideaCount > 0 ? 'Poursuivre l evaluation des meilleures idees.' : 'Collecter plus d idees avant decision.',
            ],
            'source' => 'Resume local gratuit',
        ];
    }

    private function buildPrompt(array $brainstorming, array $idees): string
    {
        $ideaLines = [];
        foreach (array_slice($idees, 0, 25) as $index => $idee) {
            $ideaLines[] = ($index + 1) . '. ' .
                'Titre: ' . ($idee['titre'] ?? '') . ' | ' .
                'Categorie: ' . ($idee['categorie'] ?? '') . ' | ' .
                'Statut: ' . ($idee['statut'] ?? '') . ' | ' .
                'Votes: ' . ($idee['votes'] ?? 0) . ' | ' .
                'Contenu: ' . ($idee['contenu'] ?? '');
        }

        if (empty($ideaLines)) {
            $ideaLines[] = 'Aucune idee liee pour le moment.';
        }

        return "Tu es un assistant produit pour une plateforme de brainstorming. " .
            "Resume le brainstorming suivant et ses idees. Retourne uniquement un JSON valide sans markdown. " .
            "Le resume doit etre utile pour un admin qui doit comprendre les tendances et decider les prochaines actions. " .
            "Champs obligatoires: overview, dominant_topics, strongest_ideas, risks, recommendations, decision. " .
            "dominant_topics, strongest_ideas, risks et recommendations sont des tableaux de 2 a 4 chaines courtes.\n\n" .
            "Brainstorming: " . ($brainstorming['titre'] ?? '') . "\n" .
            "Description: " . ($brainstorming['description'] ?? '') . "\n" .
            "Date debut: " . ($brainstorming['date_debut'] ?? '') . "\n\n" .
            "Idees:\n" . implode("\n", $ideaLines);
    }

    private function decodeJsonResponse(string $rawText): ?array
    {
        $rawText = trim($rawText);
        $rawText = preg_replace('/^```(?:json)?\s*/i', '', $rawText);
        $rawText = preg_replace('/\s*```$/', '', $rawText);

        $decoded = json_decode($rawText, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $rawText, $matches)) {
            $decoded = json_decode($matches[0], true);
            return is_array($decoded) ? $decoded : null;
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

    private function normalizeSummary(array $summary): array
    {
        return [
            'overview' => trim((string) ($summary['overview'] ?? '')),
            'dominant_topics' => $this->normalizeList($summary['dominant_topics'] ?? []),
            'strongest_ideas' => $this->normalizeList($summary['strongest_ideas'] ?? []),
            'risks' => $this->normalizeList($summary['risks'] ?? []),
            'recommendations' => $this->normalizeList($summary['recommendations'] ?? []),
            'decision' => trim((string) ($summary['decision'] ?? '')),
        ];
    }

    private function normalizeList($items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $items = array_values(array_filter(array_map(fn($item) => trim((string) $item), $items)));
        return array_slice($items, 0, 4);
    }
}
