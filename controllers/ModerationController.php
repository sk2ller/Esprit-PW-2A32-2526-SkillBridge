<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../models/ModerationResult.php');
require_once(__DIR__ . '/GeminiClient.php');

class ModerationController
{
    private GeminiClient $gemini;

    private string $geminiPrompt = <<<'PROMPT'
You are a content moderation assistant for a professional freelance platform.

Analyze the job offer text below and detect any inappropriate, offensive, discriminatory, or unprofessional content.

Categories to detect:
1. Expressions targeting an ethnicity, religion or nationality (e.g. "réservé aux arabes / blancs / noirs", "pas de femmes", "hommes seulement", "no blacks", "muslims only")
2. Insults or offensive language: idiot, imbécile, crétin, abruti, con, connard, salaud, bastard, stupid, dumbass, and similar terms in French or English
3. Any word with explicit sexual connotation
4. Financial scam indicators: "argent rapide garanti", "100% profit", "envoie ton RIB", "Western Union", "offre limitée clique ici", and similar phrases
5. Direct contact bypassing the platform: phone numbers embedded in text, personal email addresses (not platform links), direct WhatsApp or Telegram links (wa.me/, t.me/)

Job Offer Text:
{{OFFER_TEXT}}

Return a JSON object with exactly these fields:
- is_approved (boolean: true if content is acceptable, false otherwise)
- severity ("none" if clean / "low" if minor style issues / "medium" if problematic but not hate speech / "high" if discriminatory, offensive, or scam)
- flagged_words (array of strings: exact problematic words or phrases found in the text, empty array if none)
- reason (string: short explanation in French if not approved, empty string if approved)
- suggestion (string: how to professionally rewrite the flagged parts, in French, empty string if approved)

Return ONLY valid JSON, no explanation, no markdown.
PROMPT;

    public function __construct()
    {
        $this->gemini = new GeminiClient(maxRetries: 0, baseDelayMs: 300);
    }

    // ── Pre-filter: fast local regex for obvious violations ──────────────────
    // Returns a ModerationResult if a clear violation is detected,
    // or null if the text needs deeper Gemini analysis.

    private function preFilter(string $text): ?ModerationResult
    {
        $flags = [];

        // ── Platform bypass: phone numbers ───────────────────────────────────
        if (preg_match_all(
            '/(?<!\d)(\+?(\d[\s\-.]?){7,14}\d)(?!\d)/u',
            $text, $m
        )) {
            foreach ($m[0] as $v) { $flags[] = trim($v); }
        }

        // ── Platform bypass: personal email addresses ────────────────────────
        if (preg_match_all(
            '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/u',
            $text, $m
        )) {
            foreach ($m[0] as $v) { $flags[] = trim($v); }
        }

        // ── Platform bypass: WhatsApp / Telegram direct links ────────────────
        if (preg_match_all(
            '/\b(wa\.me\/|t\.me\/|whatsapp\.com\/|telegram\.me\/)\S*/ui',
            $text, $m
        )) {
            foreach ($m[0] as $v) { $flags[] = trim($v); }
        }

        if (empty($flags)) {
            return null;
        }

        $result = new ModerationResult();
        $result->setIsApproved(false);
        $result->setSeverity('high');
        $result->setFlaggedWords($flags);
        $result->setReason(
            'L\'offre contient des coordonnées directes qui contournent la plateforme : ' .
            'numéro de téléphone, adresse email ou lien WhatsApp/Telegram détecté.'
        );
        $result->setSuggestion(
            'Supprimez toutes les coordonnées directes (téléphone, email personnel, liens de messagerie). ' .
            'Les candidats doivent contacter via la plateforme uniquement.'
        );
        return $result;
    }

    // ── Call Gemini via shared client ────────────────────────────────────────

    private function callGemini(string $offerText): ?array
    {
        if (!$this->gemini->isAvailable()) {
            return null;
        }

        $prompt = str_replace('{{OFFER_TEXT}}', $offerText, $this->geminiPrompt);

        return $this->gemini->generateContent(
            [['role' => 'user', 'parts' => [['text' => $prompt]]]],
            ['temperature' => 0.1]
        );
    }

    // ── Build ModerationResult from Gemini response ──────────────────────────

    private function buildFromGemini(array $data): ModerationResult
    {
        $result = new ModerationResult();

        $approved = isset($data['is_approved']) ? (bool) $data['is_approved'] : true;
        $severity = $data['severity'] ?? 'none';
        $flags    = is_array($data['flagged_words']) ? $data['flagged_words'] : [];
        $reason   = $data['reason']     ?? '';
        $suggest  = $data['suggestion'] ?? '';

        if (in_array($severity, ['medium', 'high'], true)) {
            $approved = false;
        }

        $result->setIsApproved($approved);
        $result->setSeverity($severity);
        $result->setFlaggedWords($flags);
        $result->setReason($reason);
        $result->setSuggestion($suggest);

        return $result;
    }

    // ── Fallback ModerationResult when Gemini is unavailable ────────────────

    private function fallbackResult(): ModerationResult
    {
        $result = new ModerationResult();
        $result->setIsApproved(false);
        $result->setSeverity('unavailable');
        $result->setFlaggedWords([]);
        $result->setReason('');
        $result->setSuggestion('');
        return $result;
    }

    // ── Public actions ───────────────────────────────────────────────────────

    public function index()
    {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?page=home');
            exit;
        }
        require_once(__DIR__ . '/../views/FrontOffice/moderation.php');
    }

    // ── Moderate by offer ID ─────────────────────────────────────────────────

    public function moderateByIdApi()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (($_SESSION['role'] ?? '') !== 'admin') {
            echo json_encode(['error' => 'Accès non autorisé.']);
            return;
        }

        $idOffre = (int)($_POST['id_offre'] ?? 0);
        if ($idOffre <= 0) {
            echo json_encode(['error' => 'ID d\'offre invalide.']);
            return;
        }

        $db   = getDB();
        $stmt = $db->prepare("SELECT titre, description, competences_requises FROM offres WHERE id_offre = ?");
        if ($stmt === false) { echo json_encode(['error' => 'Erreur base de données.']); return; }
        $stmt->bind_param('i', $idOffre);
        $stmt->execute();
        $offre = $stmt->get_result()->fetch_assoc();

        if (!$offre) { echo json_encode(['error' => 'Offre introuvable.']); return; }

        $offerText = "Titre: " . ($offre['titre'] ?? '')
                   . "\nDescription: " . ($offre['description'] ?? '')
                   . "\nCompétences requises: " . ($offre['competences_requises'] ?? '');

        $this->_runModeration($offerText);
    }

    // ── Shared moderation runner ─────────────────────────────────────────────

    private function _runModeration(string $offerText): void
    {
        // Step 1: local pre-filter
        $preResult = $this->preFilter($offerText);

        if ($preResult !== null) {
            $geminiData = $this->callGemini($offerText);
            if ($geminiData !== null) {
                $geminiResult = $this->buildFromGemini($geminiData);
                $mergedFlags  = array_unique(array_merge(
                    $preResult->getFlaggedWords(),
                    $geminiResult->getFlaggedWords()
                ));
                $preResult->setFlaggedWords(array_values($mergedFlags));
                if (!empty($geminiResult->getReason()))      $preResult->setReason($geminiResult->getReason());
                if (!empty($geminiResult->getSuggestion()))  $preResult->setSuggestion($geminiResult->getSuggestion());
            }
            echo json_encode($preResult->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            return;
        }

        // Step 2: Gemini
        $geminiData = $this->callGemini($offerText);
        if ($geminiData !== null) {
            echo json_encode($this->buildFromGemini($geminiData)->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            return;
        }

        // Step 3: Gemini unavailable
        echo json_encode(
            array_merge($this->fallbackResult()->toArray(), [
                'gemini_unavailable' => true,
                'gemini_key_configured' => $this->gemini->isAvailable()
            ]),
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    }

    public function moderateApi()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (($_SESSION['role'] ?? '') !== 'admin') {
            echo json_encode(['error' => 'Accès non autorisé.']);
            return;
        }

        $offerText = trim($_POST['offer_text'] ?? '');

        if (empty($offerText)) {
            echo json_encode(['error' => 'Le texte de l\'offre est vide.']);
            return;
        }
        if (mb_strlen($offerText) > 5000) {
            echo json_encode(['error' => 'Texte trop long (max 5 000 caractères).']);
            return;
        }

        $this->_runModeration($offerText);
    }
}
