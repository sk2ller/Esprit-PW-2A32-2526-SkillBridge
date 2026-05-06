<?php
require_once(__DIR__ . '/../config.php');

class AiIdeaScoringService
{
    public function scoreIdea(array $idea): array
    {
        $apiKey = Config::getAiApiKey();
        if ($apiKey === '') {
            return $this->scoreLocally($idea);
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
                'responseSchema' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'clarity' => ['type' => 'INTEGER'],
                        'innovation' => ['type' => 'INTEGER'],
                        'feasibility' => ['type' => 'INTEGER'],
                        'positivity' => ['type' => 'INTEGER'],
                        'confidence' => ['type' => 'INTEGER'],
                        'global_score' => ['type' => 'INTEGER'],
                        'summary' => ['type' => 'STRING'],
                        'strengths' => [
                            'type' => 'ARRAY',
                            'items' => ['type' => 'STRING'],
                        ],
                        'risks' => [
                            'type' => 'ARRAY',
                            'items' => ['type' => 'STRING'],
                        ],
                        'recommendation' => ['type' => 'STRING'],
                    ],
                    'required' => ['clarity', 'innovation', 'feasibility', 'positivity', 'confidence', 'global_score', 'summary', 'strengths', 'risks', 'recommendation'],
                ],
                'temperature' => 0.2,
                'maxOutputTokens' => 1200,
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
            if ($this->isQuotaError($apiMessage)) {
                $fallback = $this->scoreLocally($idea);
                if (isset($fallback['scoring'])) {
                    $fallback['scoring']['source'] = 'Analyse locale gratuite (quota Gemini atteint)';
                    $fallback['scoring']['warning'] = 'Gemini a atteint son quota gratuit temporaire. Score genere localement pour garder la demo fonctionnelle.';
                }
                return $fallback;
            }

            return [
                'success' => false,
                'message' => 'Erreur API IA : ' . $apiMessage
            ];
        }

        $rawText = $this->extractGeminiText($decoded);
        if ($rawText === '') {
            $reason = $decoded['candidates'][0]['finishReason']
                ?? $decoded['promptFeedback']['blockReason']
                ?? 'reponse vide';
            return [
                'success' => false,
                'message' => 'Gemini n a pas renvoye de score exploitable (' . $reason . '). Reessayez avec une idee plus detaillee.'
            ];
        }

        $score = $this->decodeJsonResponse($rawText);
        if ($score === null) {
            return [
                'success' => false,
                'message' => 'Gemini a renvoye un format non JSON. Reessayez ou reduisez le texte de l idee.'
            ];
        }

        return [
            'success' => true,
            'scoring' => [
                'clarity' => $this->normalizeScore($score['clarity'] ?? null),
                'innovation' => $this->normalizeScore($score['innovation'] ?? ($score['originality'] ?? null)),
                'feasibility' => $this->normalizeScore($score['feasibility'] ?? null),
                'positivity' => $this->normalizeScore($score['positivity'] ?? ($score['impact'] ?? null)),
                'confidence' => $this->normalizeScore($score['confidence'] ?? null),
                'global_score' => $this->normalizeScore($score['global_score'] ?? null),
                'summary' => trim((string) ($score['summary'] ?? '')),
                'strengths' => $this->normalizeList($score['strengths'] ?? []),
                'risks' => $this->normalizeList($score['risks'] ?? []),
                'recommendation' => trim((string) ($score['recommendation'] ?? ($score['admin_recommendation'] ?? ''))),
                'source' => 'Gemini API',
            ]
        ];
    }

    private function buildPrompt(array $idea): string
    {
        return "Tu es un assistant d evaluation produit pour une plateforme de brainstorming. " .
            "Analyse l idee suivante et retourne uniquement un JSON valide sans markdown ni texte supplementaire. " .
            "Les scores doivent etre des nombres entiers entre 0 et 100. " .
            "Champs obligatoires: clarity, innovation, feasibility, positivity, confidence, global_score, summary, strengths, risks, recommendation. " .
            "strengths et risks doivent etre des tableaux de 2 ou 3 chaines courtes. " .
            "recommendation doit etre une phrase courte et actionable.\n\n" .
            "Titre: " . ($idea['titre'] ?? '') . "\n" .
            "Categorie: " . ($idea['categorie'] ?? '') . "\n" .
            "Priorite: " . ($idea['priorite'] ?? '') . "\n" .
            "Statut actuel: " . ($idea['statut'] ?? '') . "\n" .
            "Contenu: " . ($idea['contenu'] ?? '');
    }

    private function scoreLocally(array $idea): array
    {
        $title = trim((string) ($idea['titre'] ?? ''));
        $content = trim((string) ($idea['contenu'] ?? ''));
        $category = trim((string) ($idea['categorie'] ?? ''));
        $priority = trim((string) ($idea['priorite'] ?? 'moyenne'));
        $text = trim($title . ' ' . $content . ' ' . $category);
        $lower = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
        $wordCount = str_word_count($text);
        $sentenceCount = max(1, preg_match_all('/[.!?]+/', $content));
        $avgWordsPerSentence = $wordCount / $sentenceCount;

        $clarity = 40;
        $clarity += min(25, max(0, $wordCount - 12));
        $clarity += strlen($title) >= 8 ? 10 : 0;
        $clarity += strlen($category) >= 3 ? 8 : 0;
        $clarity += $avgWordsPerSentence <= 26 ? 12 : -8;
        $clarity += preg_match('/\b(pour|afin|objectif|but|utilisateur|client|besoin|probleme|solution)\b/i', $lower) ? 10 : 0;

        $innovationKeywords = ['ai', 'ia', 'intelligent', 'automatique', 'smart', 'innovation', 'nouveau', 'unique', 'personnalise', 'collaboratif', 'temps reel', 'prediction'];
        $feasibilityKeywords = ['php', 'mysql', 'api', 'dashboard', 'interface', 'formulaire', 'notification', 'budget', 'temps', 'prototype', 'web', 'mobile'];
        $positiveKeywords = ['ameliorer', 'faciliter', 'aider', 'optimiser', 'efficace', 'utile', 'rapide', 'simple', 'collaboration', 'qualite', 'gain'];
        $riskKeywords = ['impossible', 'trop cher', 'aucun', 'probleme', 'risque', 'difficile', 'complexe'];

        $innovation = 45 + ($this->countKeywordHits($lower, $innovationKeywords) * 9);
        $innovation += $wordCount >= 25 ? 8 : 0;
        $innovation -= preg_match('/\b(simple|basique|normal|classique)\b/i', $lower) ? 8 : 0;

        $feasibility = 50 + ($this->countKeywordHits($lower, $feasibilityKeywords) * 7);
        $feasibility += in_array($priority, ['faible', 'moyenne'], true) ? 8 : 2;
        $feasibility += $wordCount <= 120 ? 8 : -6;

        $positivity = 52 + ($this->countKeywordHits($lower, $positiveKeywords) * 8) - ($this->countKeywordHits($lower, $riskKeywords) * 7);
        $confidence = 45 + min(35, (int) ($wordCount * 1.2));
        $confidence += preg_match('/\b(comment|qui|quoi|pourquoi|etape|objectif|resultat)\b/i', $lower) ? 10 : 0;

        $scores = [
            'clarity' => $this->normalizeScore($clarity),
            'innovation' => $this->normalizeScore($innovation),
            'feasibility' => $this->normalizeScore($feasibility),
            'positivity' => $this->normalizeScore($positivity),
            'confidence' => $this->normalizeScore($confidence),
        ];
        $scores['global_score'] = $this->normalizeScore(array_sum($scores) / count($scores));

        return [
            'success' => true,
            'scoring' => $scores + [
                'summary' => $this->buildLocalSummary($scores),
                'strengths' => $this->buildLocalStrengths($scores),
                'risks' => $this->buildLocalRisks($scores, $wordCount),
                'recommendation' => $this->buildLocalRecommendation($scores),
                'source' => 'Analyse locale gratuite',
            ],
        ];
    }

    private function countKeywordHits(string $text, array $keywords): int
    {
        $hits = 0;
        foreach ($keywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                $hits++;
            }
        }
        return $hits;
    }

    private function buildLocalSummary(array $scores): string
    {
        if ($scores['global_score'] >= 75) {
            return 'Cette idee est solide, positive et suffisamment concrete pour etre etudiee.';
        }

        if ($scores['innovation'] >= 70 && $scores['clarity'] < 65) {
            return 'Cette idee est creative mais doit mieux expliquer le probleme, la cible et le resultat attendu.';
        }

        if ($scores['feasibility'] < 60) {
            return 'Cette idee a du potentiel, mais sa faisabilite doit etre clarifiee avant validation.';
        }

        return 'Cette idee est interessante, avec quelques points a renforcer pour devenir plus convaincante.';
    }

    private function buildLocalStrengths(array $scores): array
    {
        $strengths = [];
        if ($scores['clarity'] >= 70) {
            $strengths[] = 'Presentation claire et comprehensible.';
        }
        if ($scores['innovation'] >= 70) {
            $strengths[] = 'Angle original ou differenciant.';
        }
        if ($scores['feasibility'] >= 70) {
            $strengths[] = 'Implementation realiste pour un premier prototype.';
        }
        if ($scores['positivity'] >= 70) {
            $strengths[] = 'Ton positif et oriente valeur utilisateur.';
        }

        return $strengths ?: ['Bonne base pour une discussion de brainstorming.', 'Le sujet peut etre enrichi avec plus de details.'];
    }

    private function buildLocalRisks(array $scores, int $wordCount): array
    {
        $risks = [];
        if ($wordCount < 25) {
            $risks[] = 'Description encore courte.';
        }
        if ($scores['clarity'] < 60) {
            $risks[] = 'Cible ou probleme pas assez precis.';
        }
        if ($scores['feasibility'] < 60) {
            $risks[] = 'Moyens techniques et delai a clarifier.';
        }
        if ($scores['confidence'] < 60) {
            $risks[] = 'Analyse moins fiable car l idee manque de contexte.';
        }

        return $risks ?: ['Aucun risque majeur detecte automatiquement.'];
    }

    private function buildLocalRecommendation(array $scores): string
    {
        if ($scores['clarity'] < 60) {
            return 'Ajoutez la cible, le probleme exact et un exemple d utilisation.';
        }

        if ($scores['feasibility'] < 60) {
            return 'Ajoutez les ressources necessaires et une premiere version realisable.';
        }

        if ($scores['innovation'] < 60) {
            return 'Expliquez ce qui rend cette idee differente des solutions classiques.';
        }

        return 'Bonne candidate pour passer en etude avec une mini specification.';
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
            if (is_array($decoded)) {
                return $decoded;
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
