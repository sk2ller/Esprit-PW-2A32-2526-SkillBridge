<?php

class BadwordModerationService
{
    private const API_URL = 'https://www.purgomalum.com/service/containsprofanity';

    private $fallbackWords = [
        'fuck',
        'shit',
        'bitch',
        'asshole',
        'bastard',
        'damn',
        'pute',
        'merde',
        'salope',
        'connard',
        'con',
        'batard'
    ];

    public function validateDescription($description)
    {
        $text = trim((string) $description);

        if ($text === '') {
            return [
                'allowed' => true,
                'source' => 'none'
            ];
        }

        $apiResult = $this->checkWithApi($text);
        if ($apiResult !== null) {
            return [
                'allowed' => !$apiResult,
                'source' => 'api'
            ];
        }

        return [
            'allowed' => !$this->containsFallbackBadword($text),
            'source' => 'local'
        ];
    }

    private function checkWithApi($text)
    {
        $url = self::API_URL . '?text=' . urlencode($text);
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 4,
                'ignore_errors' => true
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return null;
        }

        $normalized = strtolower(trim($response));
        if ($normalized === 'true') {
            return true;
        }

        if ($normalized === 'false') {
            return false;
        }

        return null;
    }

    private function containsFallbackBadword($text)
    {
        $normalized = strtolower($text);

        foreach ($this->fallbackWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/u', $normalized)) {
                return true;
            }
        }

        return false;
    }
}
