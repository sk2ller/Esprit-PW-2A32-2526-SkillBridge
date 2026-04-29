<?php
require_once(__DIR__ . '/../config.php');

class AiIdeaScoringService
{
    public function scoreIdea(array $idea): array
    {
        $apiKey = Config::getAiApiKey();
        if ($apiKey === '') {
            return [
                'success' => false,
                'message' => 'Aucune cle API IA n est configuree. Ajoutez GEMINI_API_KEY ou GOOGLE_API_KEY dans votre environnement PHP.'
            ];
        }

        $provider = Config::getAiProvider();
        if ($provider !== 'gemini') {
            return [
                'success' => false,
                'message' => 'Le provider IA configure n est pas encore pris en charge dans ce module.'
            ];
        }

        return $this->scoreWithGemini($idea, $apiKey, Config::getAiModel());
    }

    private function scoreWithGemini(array $idea, string $apiKey, string $model): array
    {
        $payload = [
            'contents' => [[
                'parts' => [[
                    'text' => $this->buildPrompt($idea)
                ]]
            ]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.2,
                'maxOutputTokens' => 500,
            ]
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
            return [
                'success' => false,
                'message' => 'Erreur reseau lors de l appel IA : ' . $curlError
            ];
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 400) {
            $apiMessage = $decoded['error']['message'] ?? 'Erreur API inconnue.';
            return [
                'success' => false,
                'message' => 'Erreur API IA : ' . $apiMessage
            ];
        }

        $rawText = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if ($rawText === '') {
            return [
                'success' => false,
                'message' => 'La reponse IA est vide ou invalide.'
            ];
        }

        $score = $this->decodeJsonResponse($rawText);
        if ($score === null) {
            return [
                'success' => false,
                'message' => 'Impossible d analyser la reponse JSON de l IA.'
            ];
        }

        return [
            'success' => true,
            'scoring' => [
                'clarity' => $this->normalizeScore($score['clarity'] ?? null),
                'originality' => $this->normalizeScore($score['originality'] ?? null),
                'feasibility' => $this->normalizeScore($score['feasibility'] ?? null),
                'impact' => $this->normalizeScore($score['impact'] ?? null),
                'global_score' => $this->normalizeScore($score['global_score'] ?? null),
                'summary' => trim((string) ($score['summary'] ?? '')),
                'strengths' => $this->normalizeList($score['strengths'] ?? []),
                'risks' => $this->normalizeList($score['risks'] ?? []),
                'admin_recommendation' => trim((string) ($score['admin_recommendation'] ?? '')),
            ]
        ];
    }

    private function buildPrompt(array $idea): string
    {
        return "Tu es un assistant d evaluation produit pour une plateforme de brainstorming. " .
            "Analyse l idee suivante et retourne uniquement un JSON valide sans markdown ni texte supplementaire. " .
            "Les scores doivent etre des nombres entiers entre 0 et 100. " .
            "Champs obligatoires: clarity, originality, feasibility, impact, global_score, summary, strengths, risks, admin_recommendation. " .
            "strengths et risks doivent etre des tableaux de 2 ou 3 chaines courtes. " .
            "admin_recommendation doit etre une phrase courte orientee admin.\n\n" .
            "Titre: " . ($idea['titre'] ?? '') . "\n" .
            "Categorie: " . ($idea['categorie'] ?? '') . "\n" .
            "Priorite: " . ($idea['priorite'] ?? '') . "\n" .
            "Statut actuel: " . ($idea['statut'] ?? '') . "\n" .
            "Contenu: " . ($idea['contenu'] ?? '');
    }

    private function decodeJsonResponse(string $rawText): ?array
    {
        $decoded = json_decode($rawText, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $rawText, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function normalizeScore($score): int
    {
        $value = (int) round((float) $score);
        return max(0, min(100, $value));
    }

    private function normalizeList($items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $items = array_values(array_filter(array_map(function ($item) {
            return trim((string) $item);
        }, $items)));

        return array_slice($items, 0, 3);
    }
}
