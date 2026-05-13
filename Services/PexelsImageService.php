<?php
require_once(__DIR__ . '/../config.php');

class PexelsImageService
{
    public function findBrainstormingImage(array $brainstorming): array
    {
        $apiKey = Config::getPexelsApiKey();
        if ($apiKey === '') {
            return [
                'success' => false,
                'message' => 'Cle API Pexels manquante. Ajoutez PEXELS_API_KEY pour generer une image de brainstorming.'
            ];
        }

        if (!function_exists('curl_init')) {
            return ['success' => false, 'message' => 'Extension cURL indisponible.'];
        }

        $query = $this->buildSearchQuery($brainstorming);
        $endpoint = 'https://api.pexels.com/v1/search?query=' . rawurlencode($query) . '&per_page=1&orientation=landscape&locale=fr-FR';
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $apiKey],
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError) {
            return ['success' => false, 'message' => 'Erreur reseau Pexels : ' . $curlError];
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 400) {
            return ['success' => false, 'message' => 'Erreur Pexels : ' . ($decoded['error'] ?? 'requete refusee.')];
        }

        $photo = $decoded['photos'][0] ?? null;
        if (!$photo) {
            return ['success' => false, 'message' => 'Aucune image trouvee pour ce brainstorming.'];
        }

        return [
            'success' => true,
            'image' => [
                'query' => $query,
                'url' => $photo['src']['large'] ?? ($photo['src']['medium'] ?? ''),
                'original_url' => $photo['url'] ?? '',
                'photographer' => $photo['photographer'] ?? 'Pexels',
                'photographer_url' => $photo['photographer_url'] ?? 'https://www.pexels.com',
                'alt' => $photo['alt'] ?? ($brainstorming['titre'] ?? 'Brainstorming'),
            ],
            'source' => 'Pexels API',
        ];
    }

    private function buildSearchQuery(array $brainstorming): string
    {
        $text = trim(($brainstorming['titre'] ?? '') . ' ' . ($brainstorming['description'] ?? ''));
        $lower = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);

        $map = [
            'ai education' => ['ia', 'ai', 'intelligence artificielle', 'education', 'etudiant', 'student', 'learning'],
            'startup brainstorming' => ['startup', 'business', 'entreprise', 'projet'],
            'team collaboration' => ['collaboration', 'equipe', 'team', 'communication'],
            'web development' => ['web', 'application', 'site', 'dashboard', 'php', 'mobile'],
            'creative design' => ['design', 'ux', 'ui', 'creative', 'logo'],
            'innovation technology' => ['innovation', 'technologie', 'digital', 'solution'],
        ];

        foreach ($map as $query => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($lower, $keyword) !== false) {
                    return $query;
                }
            }
        }

        return 'brainstorming innovation';
    }
}
