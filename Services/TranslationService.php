<?php
require_once(__DIR__ . '/../config.php');

class TranslationService
{
    public function translate(string $text, string $targetLanguage): array
    {
        $text = trim(html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8'));
        $targetLanguage = strtoupper(trim($targetLanguage));

        if ($text === '') {
            return ['success' => false, 'message' => 'Aucun texte a traduire.'];
        }

        if (!in_array($targetLanguage, ['FR', 'EN', 'AR'], true)) {
            return ['success' => false, 'message' => 'Langue cible non supportee.'];
        }

        $detectedSourceLanguage = $this->detectSourceLanguage($text, $targetLanguage);
        if ($detectedSourceLanguage === strtolower($targetLanguage)) {
            return [
                'success' => true,
                'translated_text' => $text,
                'source' => 'Texte deja dans cette langue',
            ];
        }

        $apiKey = Config::getDeepLApiKey();
        if ($apiKey !== '') {
            $deepLResult = $this->translateWithDeepL($text, $targetLanguage, $apiKey);
            if ($deepLResult['success']) {
                return $deepLResult;
            }
        }

        return $this->translateWithMyMemory($text, $targetLanguage);
    }

    public function translateFields(array $fields, string $targetLanguage): array
    {
        $translated = [];
        $source = '';

        foreach ($fields as $field => $text) {
            $result = $this->translate((string) $text, $targetLanguage);
            if (!$result['success']) {
                return $result;
            }

            $translated[$field] = $result['translated_text'];
            $source = $result['source'];
        }

        return [
            'success' => true,
            'translated' => $translated,
            'target_language' => strtoupper($targetLanguage),
            'source' => $source,
        ];
    }

    private function translateWithDeepL(string $text, string $targetLanguage, string $apiKey): array
    {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'message' => 'Extension cURL indisponible.'];
        }

        $endpoint = 'https://api-free.deepl.com/v2/translate';
        $payload = http_build_query([
            'auth_key' => $apiKey,
            'text' => $text,
            'target_lang' => $targetLanguage,
        ]);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError || $httpCode >= 400) {
            return ['success' => false, 'message' => 'DeepL indisponible.'];
        }

        $decoded = json_decode($response, true);
        $translated = $decoded['translations'][0]['text'] ?? '';
        if ($translated === '') {
            return ['success' => false, 'message' => 'Reponse DeepL invalide.'];
        }

        return [
            'success' => true,
            'translated_text' => $translated,
            'source' => 'DeepL API Free',
        ];
    }

    private function translateWithMyMemory(string $text, string $targetLanguage): array
    {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'message' => 'Extension cURL indisponible pour la traduction.'];
        }

        $sourceLanguage = $this->detectSourceLanguage($text, $targetLanguage);
        $langPair = strtolower($sourceLanguage) . '|' . strtolower($targetLanguage);
        $endpoint = 'https://api.mymemory.translated.net/get?q=' . rawurlencode($text) . '&langpair=' . rawurlencode($langPair);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError || $httpCode >= 400) {
            return ['success' => false, 'message' => 'Service de traduction indisponible.'];
        }

        $decoded = json_decode($response, true);
        $responseStatus = (int) ($decoded['responseStatus'] ?? 200);
        if ($responseStatus >= 400) {
            return ['success' => false, 'message' => $decoded['responseDetails'] ?? 'Traduction indisponible pour cette langue.'];
        }

        $translated = $decoded['responseData']['translatedText'] ?? '';
        if ($translated === '' || stripos($translated, 'invalid source language') !== false) {
            return ['success' => false, 'message' => 'Traduction indisponible pour ce texte.'];
        }

        return [
            'success' => true,
            'translated_text' => html_entity_decode($translated, ENT_QUOTES, 'UTF-8'),
            'source' => 'MyMemory API',
        ];
    }

    private function detectSourceLanguage(string $text, string $targetLanguage): string
    {
        if (preg_match('/\p{Arabic}/u', $text)) {
            return $targetLanguage === 'AR' ? 'fr' : 'ar';
        }

        $lower = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
        $frenchHints = [' le ', ' la ', ' les ', ' une ', ' des ', ' et ', ' pour ', ' avec ', 'idee', 'étudiant', 'etudiant', 'plateforme', 'projet'];
        $englishHints = [' the ', ' and ', ' for ', ' with ', ' idea ', ' student', ' platform', ' project'];

        $frenchScore = $this->countHints($lower, $frenchHints);
        $englishScore = $this->countHints($lower, $englishHints);

        if ($targetLanguage === 'FR') {
            return $englishScore > $frenchScore ? 'en' : 'fr';
        }

        if ($targetLanguage === 'EN') {
            return $frenchScore >= $englishScore ? 'fr' : 'en';
        }

        return $frenchScore >= $englishScore ? 'fr' : 'en';
    }

    private function countHints(string $text, array $hints): int
    {
        $score = 0;
        $paddedText = ' ' . $text . ' ';
        foreach ($hints as $hint) {
            if (strpos($paddedText, $hint) !== false) {
                $score++;
            }
        }
        return $score;
    }
}
