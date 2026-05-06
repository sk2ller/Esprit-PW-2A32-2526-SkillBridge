<?php
require_once(__DIR__ . '/../config.php');

/**
 * Shared Gemini API client with automatic retry and exponential backoff.
 *
 * Usage:
 *   $client = new GeminiClient();
 *   if (!$client->isAvailable()) { ... }
 *   $result = $client->generateContent($contents, $generationConfig);
 */
class GeminiClient
{
    private string $apiUrl;
    private string $apiKey;
    private int    $maxRetries;
    private int    $baseDelayMs;

    public function __construct(int $maxRetries = 3, int $baseDelayMs = 1000)
    {
        $this->apiUrl      = GEMINI_API_URL;
        $this->apiKey      = GEMINI_API_KEY;
        $this->maxRetries  = $maxRetries;
        $this->baseDelayMs = $baseDelayMs;
    }

    /**
     * Returns true if the API key is configured and non-empty.
     */
    public function isAvailable(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'VOTRE_CLE_GEMINI_ICI';
    }

    /**
     * Send a generateContent request to Gemini.
     *
     * Retries automatically on:
     *   - Network/cURL errors
     *   - HTTP 429 (rate limited)
     *   - HTTP 5xx (server errors)
     *
     * Uses exponential backoff between retries: 1 s → 2 s → 4 s …
     *
     * @param  array      $contents         Gemini "contents" array (role + parts)
     * @param  array      $generationConfig Override any generationConfig fields
     * @return array|null Decoded JSON result or null on failure
     */
    public function generateContent(array $contents, array $generationConfig = []): ?array
    {
        if (!$this->isAvailable()) {
            return null;
        }

        $defaultConfig = [
            'responseMimeType' => 'application/json',
            'maxOutputTokens'  => 8192,
            'temperature'      => 0.1,
        ];

        $payload = json_encode([
            'contents'         => $contents,
            'generationConfig' => array_merge($defaultConfig, $generationConfig),
        ], JSON_UNESCAPED_UNICODE);

        $endpoint = rtrim($this->apiUrl, '/') . ':generateContent';

        for ($attempt = 0; $attempt <= $this->maxRetries; $attempt++) {

            if ($attempt > 0) {
                $delayMs = $this->baseDelayMs * (2 ** ($attempt - 1));
                usleep($delayMs * 1000);
            }

            $httpCode = 0;
            $response = $this->postWithStream($endpoint, $payload, $httpCode);

            if (!$response) {
                continue;
            }

            if ($httpCode === 429 || $httpCode >= 500) {
                continue;
            }

            if ($httpCode !== 200) {
                return null;
            }

            $decoded  = json_decode($response, true);
            $jsonText = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if (!$jsonText) {
                return null;
            }

            $result = json_decode(trim($jsonText), true);
            return (json_last_error() === JSON_ERROR_NONE) ? $result : null;
        }

        return null;
    }

    private function postWithStream(string $endpoint, string $payload, int &$httpCode): ?string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => 12,
                'ignore_errors' => true,
                'header' => "Content-Type: application/json\r\nx-goog-api-key: {$this->apiKey}\r\n",
                'content' => $payload
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        $response = @file_get_contents($endpoint, false, $context);
        $statusLine = $http_response_header[0] ?? '';

        if (preg_match('/\s(\d{3})\s/', $statusLine, $matches)) {
            $httpCode = (int) $matches[1];
        }

        return $response === false ? null : $response;
    }
}
