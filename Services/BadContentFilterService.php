<?php
require_once(__DIR__ . '/../config.php');

class BadContentFilterService
{
    private $insultPatterns = [
        '/\b(idiot|stupid|dumb|moron|imbecile|insulte|con|connard|connasse|debile|abruti|nul)\b/i',
        '/\b(merde|fuck|shit|bitch|asshole)\b/i',
    ];

    private $threatPatterns = [
        '/\b(kill|hurt|attack|menace|frapper|tuer|detruire|violence|mort)\b/i',
        '/\b(je vais te|i will)\s+(tuer|frapper|kill|hurt|attack)\b/i',
    ];

    private $spamPatterns = [
        '/https?:\/\/\S+/i',
        '/\b(www\.|\.com|\.net|\.org|\.tn)\b/i',
        '/\b(gratuit|free money|promo|bitcoin|crypto|casino|viagra|loan|click here)\b/i',
        '/\b(free money|promo|bitcoin|crypto|casino)\b.*\b(free money|promo|bitcoin|crypto|casino)\b/i',
        '/(.)\1{7,}/',
    ];

    public function analyze(string $text): array
    {
        $text = trim($text);

        $apiKey = Config::getPerspectiveApiKey();
        if ($apiKey !== '') {
            $apiResult = $this->analyzeWithPerspective($text, $apiKey);
            if ($apiResult !== null) {
                return $apiResult;
            }
        }

        return $this->analyzeLocally($text);
    }

    private function analyzeLocally(string $text): array
    {
        $issues = [];
        $score = 0;

        if ($text === '') {
            return [
                'blocked' => false,
                'score' => 0,
                'issues' => [],
                'message' => '',
            ];
        }

        $this->scanPatterns($text, $this->insultPatterns, 'insultes ou langage offensant', 45, $issues, $score);
        $this->scanPatterns($text, $this->threatPatterns, 'menace ou violence', 80, $issues, $score);
        $this->scanPatterns($text, $this->spamPatterns, 'spam ou contenu promotionnel suspect', 35, $issues, $score);

        if ($this->hasTooManyUppercaseWords($text)) {
            $issues[] = 'texte en majuscules excessives';
            $score += 20;
        }

        if ($this->hasRepeatedWords($text)) {
            $issues[] = 'repetition excessive';
            $score += 25;
        }

        $score = min(100, $score);
        $blocked = $score >= 70 || in_array('menace ou violence', $issues, true);

        return [
            'blocked' => $blocked,
            'score' => $score,
            'issues' => array_values(array_unique($issues)),
            'message' => $blocked
                ? 'Contenu bloque : veuillez supprimer les insultes, menaces ou spam avant de sauvegarder.'
                : '',
            'source' => 'Filtre local gratuit',
        ];
    }

    private function analyzeWithPerspective(string $text, string $apiKey): ?array
    {
        if ($text === '' || !function_exists('curl_init')) {
            return null;
        }

        $payload = [
            'comment' => ['text' => $text],
            'languages' => ['fr', 'en'],
            'requestedAttributes' => [
                'TOXICITY' => new stdClass(),
                'SEVERE_TOXICITY' => new stdClass(),
                'INSULT' => new stdClass(),
                'PROFANITY' => new stdClass(),
                'THREAT' => new stdClass(),
            ],
            'doNotStore' => true,
        ];

        $endpoint = 'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key=' . rawurlencode($apiKey);
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 12,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError || $httpCode >= 400) {
            return null;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || empty($decoded['attributeScores'])) {
            return null;
        }

        $scores = [];
        foreach ($decoded['attributeScores'] as $attribute => $data) {
            $scores[$attribute] = (float) ($data['summaryScore']['value'] ?? 0);
        }

        $issues = [];
        $thresholds = [
            'TOXICITY' => 0.78,
            'SEVERE_TOXICITY' => 0.65,
            'INSULT' => 0.72,
            'PROFANITY' => 0.78,
            'THREAT' => 0.55,
        ];

        foreach ($thresholds as $attribute => $threshold) {
            if (($scores[$attribute] ?? 0) >= $threshold) {
                $issues[] = strtolower(str_replace('_', ' ', $attribute));
            }
        }

        $maxScore = empty($scores) ? 0 : max($scores);
        $blocked = !empty($issues);

        return [
            'blocked' => $blocked,
            'score' => (int) round($maxScore * 100),
            'issues' => $issues,
            'message' => $blocked
                ? 'Contenu bloque par moderation API : veuillez supprimer les insultes, menaces ou spam avant de sauvegarder.'
                : '',
            'source' => 'Perspective API',
            'raw_scores' => $scores,
        ];
    }

    public function validateFields(array $fields): array
    {
        $errors = [];
        $maxScore = 0;
        $allIssues = [];

        foreach ($fields as $field => $value) {
            $result = $this->analyze((string) $value);
            $maxScore = max($maxScore, (int) $result['score']);
            $allIssues = array_merge($allIssues, $result['issues']);

            if ($result['blocked']) {
                $errors[$field] = $result['message'];
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'score' => $maxScore,
            'issues' => array_values(array_unique($allIssues)),
        ];
    }

    private function scanPatterns(string $text, array $patterns, string $label, int $weight, array &$issues, int &$score): void
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                $issues[] = $label;
                $score += $weight;
            }
        }
    }

    private function hasTooManyUppercaseWords(string $text): bool
    {
        preg_match_all('/\b[A-Z]{4,}\b/', $text, $matches);
        $uppercaseWords = count($matches[0]);
        $totalWords = max(1, str_word_count($text));

        return $uppercaseWords >= 4 && ($uppercaseWords / $totalWords) > 0.45;
    }

    private function hasRepeatedWords(string $text): bool
    {
        return (bool) preg_match('/\b(\w+)\b(?:\s+\1\b){3,}/i', $text);
    }
}
