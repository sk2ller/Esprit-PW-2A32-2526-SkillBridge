<?php

class GeminiServiceGenerator
{
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    private const IMAGE_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-preview-image-generation:generateContent';

    public function generateServiceOffer($competences, $categorie)
    {
        $competences = trim((string) $competences);
        $categorie = trim((string) $categorie);

        if ($competences === '') {
            return [
                'success' => false,
                'message' => 'Veuillez saisir vos competences avant de generer le service.'
            ];
        }

        $apiKey = $this->getApiKey();
        if ($apiKey !== '') {
            $geminiResult = $this->generateWithGemini($apiKey, $competences, $categorie);
            if ($geminiResult !== null) {
                $geminiResult['source'] = 'gemini';
                return $geminiResult;
            }
        }

        $fallbackResult = $this->generateFallback($competences, $categorie);
        $fallbackResult['source'] = 'local';
        return $fallbackResult;
    }

    public function translateDescription($description)
    {
        $description = trim((string) $description);
        $language = $this->detectLanguage($description);

        $result = [
            'langue_detectee' => $language,
            'description_fr' => $description,
            'description_en' => $description,
            'source' => 'local'
        ];

        if ($description === '' || $language !== 'ar') {
            return $result;
        }

        $apiKey = $this->getApiKey();
        if ($apiKey === '') {
            $result['description_fr'] = 'Traduction francaise a generer avec Gemini: ' . $description;
            $result['description_en'] = 'English translation to generate with Gemini: ' . $description;
            return $result;
        }

        $prompt = "Detecte la langue et traduis cette description de service freelance en francais et en anglais. "
            . "Retourne uniquement un JSON valide avec les cles: langue_detectee, description_fr, description_en. "
            . "Texte: {$description}";

        $data = $this->generateJsonWithGemini($apiKey, $prompt);
        if (is_array($data) && !empty($data['description_fr']) && !empty($data['description_en'])) {
            return [
                'langue_detectee' => $data['langue_detectee'] ?? 'ar',
                'description_fr' => trim($data['description_fr']),
                'description_en' => trim($data['description_en']),
                'source' => 'gemini'
            ];
        }

        return $result;
    }

    public function translateToAllLanguages($description)
    {
        $description = trim((string) $description);
        $language = $this->detectLanguage($description);
        $result = [
            'langue_detectee' => $language,
            'description_ar' => $language === 'ar' ? $description : 'Traduction arabe disponible avec Gemini: ' . $description,
            'description_fr' => $language === 'fr' ? $description : 'Traduction francaise disponible avec Gemini: ' . $description,
            'description_en' => $language === 'en' ? $description : 'English translation available with Gemini: ' . $description,
            'source' => 'local'
        ];

        if ($description === '') {
            return $result;
        }

        $apiKey = $this->getApiKey();
        if ($apiKey === '') {
            return $result;
        }

        $prompt = "Traduis cette description de service freelance en trois langues: arabe, francais et anglais. "
            . "Retourne uniquement un JSON valide avec les cles: langue_detectee, description_ar, description_fr, description_en. "
            . "Garde un style professionnel et naturel pour un client qui consulte une plateforme freelance. "
            . "Texte: {$description}";

        $data = $this->generateJsonWithGemini($apiKey, $prompt);
        if (is_array($data) && !empty($data['description_ar']) && !empty($data['description_fr']) && !empty($data['description_en'])) {
            return [
                'langue_detectee' => $data['langue_detectee'] ?? $language,
                'description_ar' => trim($data['description_ar']),
                'description_fr' => trim($data['description_fr']),
                'description_en' => trim($data['description_en']),
                'source' => 'gemini'
            ];
        }

        return $result;
    }

    public function generateServiceImage($titre, $description, $categorie, $competences, $uploadDir, $style = 'modern')
    {
        $apiKey = $this->getImageApiKey();
        $stylePrompt = $this->getImageStylePrompt($style);
        $prompt = "Generate a beautiful professional marketplace thumbnail for a freelance service. "
            . "{$stylePrompt} "
            . "No written text, no logo, no watermark. "
            . "Category: {$categorie}. Title: {$titre}. Skills: {$competences}. Description: {$description}.";

        if ($apiKey !== '') {
            $imageName = $this->generateImageWithGemini($apiKey, $prompt, $uploadDir);
            if ($imageName !== null) {
                return $imageName;
            }
        }

        return $this->generateFallbackImage($titre, $categorie, $uploadDir, $style);
    }

    private function getApiKey()
    {
        if (defined('GEMINI_API_KEY') && GEMINI_API_KEY !== '') {
            return GEMINI_API_KEY;
        }

        $envKey = getenv('GEMINI_API_KEY');
        return $envKey ? $envKey : '';
    }

    private function getImageApiKey()
    {
        if (defined('GEMINI_IMAGE_API_KEY') && GEMINI_IMAGE_API_KEY !== '') {
            return GEMINI_IMAGE_API_KEY;
        }

        return '';
    }

    private function generateWithGemini($apiKey, $competences, $categorie)
    {
        $prompt = "Tu es un assistant pour une plateforme freelance tunisienne. "
            . "A partir des competences du freelancer et de la categorie, genere uniquement un JSON valide avec les cles: "
            . "titre, description, prix_suggere. "
            . "Le titre doit etre accrocheur, professionnel et court. "
            . "La description doit etre professionnelle, en francais, entre 70 et 120 mots. "
            . "Le prix_suggere doit etre un nombre en dinars tunisiens, competitif pour le marche local. "
            . "Categorie: {$categorie}. Competences: {$competences}.";

        $data = $this->generateJsonWithGemini($apiKey, $prompt);

        if (!$this->isValidGeneratedData($data)) {
            return null;
        }

        return [
            'success' => true,
            'titre' => trim($data['titre']),
            'description' => trim($data['description']),
            'prix_suggere' => $this->normalizePrice($data['prix_suggere'])
        ];
    }

    private function generateJsonWithGemini($apiKey, $prompt)
    {
        $payload = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'responseMimeType' => 'application/json'
            ]
        ]);

        $response = $this->postJsonToGemini(self::API_URL, $apiKey, $payload);
        if ($response === false) {
            return null;
        }

        $decoded = json_decode($response, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
        return $this->decodeJsonText($text);
    }

    private function postJsonToGemini($url, $apiKey, $payload)
    {
        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'x-goog-api-key: ' . $apiKey
                ],
                CURLOPT_TIMEOUT => 15
            ]);

            $response = curl_exec($curl);
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
                return $response;
            }
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => 15,
                'ignore_errors' => true,
                'header' => "Content-Type: application/json\r\nx-goog-api-key: {$apiKey}\r\n",
                'content' => $payload
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return false;
        }

        $statusLine = $http_response_header[0] ?? '';
        if ($statusLine !== '' && !preg_match('/\s2\d\d\s/', $statusLine)) {
            return false;
        }

        return $response;
    }

    private function decodeJsonText($text)
    {
        $text = trim((string) $text);
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        return json_decode($text, true);
    }

    private function isValidGeneratedData($data)
    {
        return is_array($data)
            && !empty($data['titre'])
            && !empty($data['description'])
            && isset($data['prix_suggere'])
            && is_numeric($data['prix_suggere']);
    }

    private function generateFallback($competences, $categorie)
    {
        $cleanCompetences = preg_replace('/\s+/', ' ', $competences);
        $shortSkills = mb_substr($cleanCompetences, 0, 90);
        $categoryName = $categorie !== '' ? $categorie : 'service digital';
        $price = $this->estimateLocalPrice($competences, $categorie);

        return [
            'success' => true,
            'titre' => 'Service professionnel en ' . $categoryName,
            'description' => "Je vous accompagne avec un service professionnel base sur mes competences en {$shortSkills}. "
                . "L'objectif est de livrer un resultat clair, moderne et adapte a vos besoins. "
                . "Je peux analyser votre demande, proposer une solution efficace, realiser le travail avec soin et assurer un suivi simple jusqu'a la livraison finale.",
            'prix_suggere' => $price
        ];
    }

    private function estimateLocalPrice($competences, $categorie)
    {
        $text = strtolower($competences . ' ' . $categorie);
        $price = 80;

        if (preg_match('/web|site|wordpress|php|backend|frontend|application|app|react|laravel/', $text)) {
            $price = 250;
        } elseif (preg_match('/design|logo|ui|ux|figma|graphique/', $text)) {
            $price = 150;
        } elseif (preg_match('/marketing|seo|social|facebook|instagram|ads/', $text)) {
            $price = 180;
        } elseif (preg_match('/photo|video|montage|shooting/', $text)) {
            $price = 200;
        } elseif (preg_match('/business|consulting|finance|strategie/', $text)) {
            $price = 220;
        }

        $skillCount = count(preg_split('/[,;]+/', $competences, -1, PREG_SPLIT_NO_EMPTY));
        if ($skillCount >= 4) {
            $price += 70;
        } elseif ($skillCount >= 2) {
            $price += 30;
        }

        return $this->normalizePrice($price);
    }

    private function normalizePrice($price)
    {
        $price = (float) $price;
        if ($price < 30) {
            return 30;
        }

        if ($price > 5000) {
            return 5000;
        }

        return round($price, 2);
    }

    private function detectLanguage($text)
    {
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return 'ar';
        }

        if (preg_match('/\b(the|and|with|for|service|professional|delivery|client|business)\b/i', $text)) {
            return 'en';
        }

        return 'fr';
    }

    private function generateImageWithGemini($apiKey, $prompt, $uploadDir)
    {
        $payload = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseModalities' => ['TEXT', 'IMAGE']
            ]
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => 3,
                'ignore_errors' => true,
                'header' => "Content-Type: application/json\r\nx-goog-api-key: {$apiKey}\r\n",
                'content' => $payload
            ]
        ]);

        $response = @file_get_contents(self::IMAGE_API_URL, false, $context);
        if ($response === false) {
            return null;
        }

        $decoded = json_decode($response, true);
        $parts = $decoded['candidates'][0]['content']['parts'] ?? [];

        foreach ($parts as $part) {
            $inlineData = $part['inlineData'] ?? $part['inline_data'] ?? null;
            if (!empty($inlineData['data'])) {
                $mimeType = $inlineData['mimeType'] ?? $inlineData['mime_type'] ?? 'image/png';
                $extension = strpos($mimeType, 'jpeg') !== false || strpos($mimeType, 'jpg') !== false ? 'jpg' : 'png';
                $fileName = 'ai_service_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
                $imageBytes = base64_decode($inlineData['data']);

                if ($imageBytes !== false && file_put_contents($uploadDir . $fileName, $imageBytes) !== false) {
                    return $fileName;
                }
            }
        }

        return null;
    }

    private function getImageStylePrompt($style)
    {
        $styles = [
            'modern' => 'Premium clean design, readable composition, modern dashboard/product style, strong focal point, soft depth, elegant lighting.',
            'tech' => 'Futuristic tech style, dark interface, neon accents, code-inspired visual atmosphere, premium SaaS thumbnail.',
            'minimal' => 'Minimal bright editorial style, lots of clean space, soft shapes, elegant professional marketplace thumbnail.',
            'luxury' => 'Luxury premium style, deep contrast, gold accents, refined composition, high-end service presentation.',
            'creative' => 'Creative colorful style, dynamic abstract shapes, energetic composition, professional and polished.'
        ];

        return $styles[$style] ?? $styles['modern'];
    }

    private function generateFallbackImage($titre, $categorie, $uploadDir, $style = 'modern')
    {
        $fileName = 'ai_service_' . time() . '_' . mt_rand(1000, 9999) . '.svg';
        $titleLines = $this->wrapSvgText($titre ?: 'Service freelance professionnel', 28, 3);
        $category = htmlspecialchars(mb_substr($categorie ?: 'Freelance', 0, 30), ENT_QUOTES, 'UTF-8');
        $variantSeed = time() . mt_rand(1000, 9999);
        $colorSeed = abs(crc32($titre . $categorie . $variantSeed));
        $paletteGroups = [
            'modern' => [
                ['#111827', '#f97316', '#fde68a'],
                ['#064e3b', '#14b8a6', '#ccfbf1'],
                ['#312e81', '#8b5cf6', '#ddd6fe']
            ],
            'tech' => [
                ['#020617', '#38bdf8', '#bae6fd'],
                ['#111827', '#22d3ee', '#cffafe'],
                ['#0f172a', '#818cf8', '#e0e7ff']
            ],
            'minimal' => [
                ['#334155', '#f8fafc', '#e2e8f0'],
                ['#475569', '#ffffff', '#cbd5e1'],
                ['#155e75', '#ecfeff', '#a5f3fc']
            ],
            'luxury' => [
                ['#171717', '#d97706', '#fde68a'],
                ['#1c1917', '#a16207', '#fef3c7'],
                ['#0c0a09', '#f59e0b', '#ffedd5']
            ],
            'creative' => [
                ['#3b0764', '#ec4899', '#fbcfe8'],
                ['#7f1d1d', '#ef4444', '#fecaca'],
                ['#164e63', '#22c55e', '#bbf7d0']
            ]
        ];
        $colors = $paletteGroups[$style] ?? $paletteGroups['modern'];
        $palette = $colors[$colorSeed % count($colors)];
        $variant = $colorSeed % 4;
        $titleSvg = '';
        $titleX = $variant === 1 ? 560 : 100;
        $titleY = $variant === 2 ? 295 : 330;
        $y = $titleY;

        foreach ($titleLines as $line) {
            $titleSvg .= '<text x="' . $titleX . '" y="' . $y . '" fill="#ffffff" font-family="Arial, sans-serif" font-size="54" font-weight="800">'
                . htmlspecialchars($line, ENT_QUOTES, 'UTF-8')
                . '</text>';
            $y += 66;
        }

        $visualBlocks = [
            '<g transform="translate(760 285)"><rect x="0" y="0" width="260" height="230" rx="30" fill="rgba(255,255,255,0.22)" stroke="rgba(255,255,255,0.35)"/><rect x="45" y="52" width="170" height="24" rx="12" fill="rgba(255,255,255,0.78)"/><rect x="45" y="100" width="130" height="18" rx="9" fill="rgba(255,255,255,0.48)"/><rect x="45" y="140" width="170" height="46" rx="16" fill="' . $palette[2] . '" opacity="0.9"/></g>',
            '<g transform="translate(105 285)"><rect x="0" y="0" width="330" height="250" rx="34" fill="rgba(255,255,255,0.20)" stroke="rgba(255,255,255,0.34)"/><circle cx="90" cy="86" r="42" fill="' . $palette[2] . '" opacity="0.92"/><rect x="155" y="62" width="120" height="18" rx="9" fill="rgba(255,255,255,0.76)"/><rect x="155" y="98" width="84" height="16" rx="8" fill="rgba(255,255,255,0.42)"/><rect x="48" y="164" width="235" height="42" rx="16" fill="rgba(255,255,255,0.22)"/></g>',
            '<g transform="translate(770 250)"><circle cx="145" cy="145" r="142" fill="rgba(255,255,255,0.17)" stroke="rgba(255,255,255,0.34)"/><path d="M80 150 L130 200 L222 92" fill="none" stroke="' . $palette[2] . '" stroke-width="28" stroke-linecap="round" stroke-linejoin="round"/><rect x="28" y="318" width="235" height="26" rx="13" fill="rgba(255,255,255,0.34)"/></g>',
            '<g transform="translate(735 255)"><rect x="0" y="28" width="310" height="235" rx="32" fill="rgba(255,255,255,0.19)" stroke="rgba(255,255,255,0.35)"/><rect x="42" y="0" width="226" height="72" rx="26" fill="' . $palette[2] . '" opacity="0.94"/><rect x="48" y="112" width="210" height="18" rx="9" fill="rgba(255,255,255,0.72)"/><rect x="48" y="154" width="155" height="18" rx="9" fill="rgba(255,255,255,0.42)"/><rect x="48" y="196" width="235" height="38" rx="16" fill="rgba(255,255,255,0.22)"/></g>'
        ];

        $subtitleX = $variant === 1 ? 560 : 100;
        $subtitleY = $variant === 2 ? 585 : 590;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800" viewBox="0 0 1200 800">'
            . '<defs>'
            . '<linearGradient id="g" x1="0" x2="1" y1="0" y2="1"><stop offset="0" stop-color="' . $palette[0] . '"/><stop offset="1" stop-color="' . $palette[1] . '"/></linearGradient>'
            . '<filter id="shadow" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="18" stdDeviation="22" flood-color="#000000" flood-opacity="0.24"/></filter>'
            . '</defs>'
            . '<rect width="1200" height="800" fill="url(#g)"/>'
            . '<circle cx="1030" cy="130" r="210" fill="rgba(255,255,255,0.15)"/>'
            . '<circle cx="1010" cy="625" r="250" fill="rgba(255,255,255,0.10)"/>'
            . '<circle cx="120" cy="705" r="230" fill="rgba(255,255,255,0.10)"/>'
            . '<rect x="70" y="78" width="1060" height="644" rx="36" fill="rgba(255,255,255,0.13)" stroke="rgba(255,255,255,0.28)" filter="url(#shadow)"/>'
            . '<rect x="100" y="120" width="250" height="58" rx="29" fill="' . $palette[2] . '" opacity="0.95"/>'
            . '<text x="130" y="158" fill="' . $palette[0] . '" font-family="Arial, sans-serif" font-size="25" font-weight="800">' . $category . '</text>'
            . '<rect x="935" y="120" width="150" height="58" rx="29" fill="rgba(255,255,255,0.22)" stroke="rgba(255,255,255,0.35)"/>'
            . '<text x="982" y="158" fill="#ffffff" font-family="Arial, sans-serif" font-size="25" font-weight="800">IA</text>'
            . $titleSvg
            . '<text x="' . $subtitleX . '" y="' . $subtitleY . '" fill="rgba(255,255,255,0.86)" font-family="Arial, sans-serif" font-size="30" font-weight="600">Miniature professionnelle generee automatiquement</text>'
            . '<rect x="' . $subtitleX . '" y="' . ($subtitleY + 40) . '" width="360" height="10" rx="5" fill="rgba(255,255,255,0.45)"/>'
            . '<rect x="' . $subtitleX . '" y="' . ($subtitleY + 66) . '" width="250" height="10" rx="5" fill="rgba(255,255,255,0.28)"/>'
            . $visualBlocks[$variant]
            . '</svg>';

        if (file_put_contents($uploadDir . $fileName, $svg) !== false) {
            return $fileName;
        }

        return null;
    }

    private function wrapSvgText($text, $maxChars, $maxLines)
    {
        $words = preg_split('/\s+/', trim((string) $text));
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = trim($current . ' ' . $word);
            if (mb_strlen($candidate) > $maxChars && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }

            if (count($lines) === $maxLines) {
                break;
            }
        }

        if ($current !== '' && count($lines) < $maxLines) {
            $lines[] = $current;
        }

        if (count($lines) === $maxLines && count($words) > 0) {
            $lastIndex = $maxLines - 1;
            if (mb_strlen($lines[$lastIndex]) > $maxChars - 3) {
                $lines[$lastIndex] = mb_substr($lines[$lastIndex], 0, $maxChars - 3) . '...';
            }
        }

        return $lines ?: ['Service freelance'];
    }
}
