<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../models/CandidatureScore.php');
require_once(__DIR__ . '/GeminiClient.php');

class CandidatureScoreController
{
    private GeminiClient $gemini;

    private string $geminiPrompt = <<<'PROMPT'
You are an expert recruiter assistant on a freelance platform.

A client wants to evaluate a candidature for their job offer. Based on the information below, generate a detailed score and feedback.

Job Offer:
- Title: {{offer_title}}
- Required Skills: {{required_skills}}
- Experience Required: {{experience_required}}
- Description: {{offer_description}}

Candidate Application:
- Candidate Name: {{candidate_name}}
- Cover Letter / Message: {{cover_letter}}

Return a JSON object with exactly these fields:
- overall_score (integer 0 to 100)
- criteria_scores:
  - skills_match (integer 0 to 100)
  - experience_match (integer 0 to 100)
  - cover_letter_quality (integer 0 to 100)
- strengths (array of 2-4 strong points as strings, in French)
- weaknesses (array of 1-3 weak points as strings, in French)
- recommendation (exactly one of: "Highly Recommended" / "Recommended" / "Maybe" / "Not Recommended")
- summary (string: 2-3 sentence overall feedback in French)

Return ONLY valid JSON, no explanation, no markdown.
PROMPT;

    public function __construct()
    {
        $this->gemini = new GeminiClient(maxRetries: 3, baseDelayMs: 1000);
    }

    // ── Call Gemini via shared client ────────────────────────────────────────

    private function callGemini(array $row): ?array
    {
        if (!$this->gemini->isAvailable()) {
            return null;
        }

        $prompt = strtr($this->geminiPrompt, [
            '{{offer_title}}'         => $row['titre_offre']          ?? 'Non précisé',
            '{{required_skills}}'     => $row['competences_requises']  ?? 'Non précisé',
            '{{experience_required}}' => $row['niveau_requis']         ?? 'Non précisé',
            '{{offer_description}}'   => mb_substr($row['offre_description'] ?? '', 0, 600),
            '{{candidate_name}}'      => $row['nom_freelancer']        ?? 'Candidat',
            '{{cover_letter}}'        => mb_substr($row['message']     ?? '', 0, 1200),
        ]);

        return $this->gemini->generateContent(
            [['role' => 'user', 'parts' => [['text' => $prompt]]]],
            ['temperature' => 0.2]
        );
    }

    // ── Build model from Gemini response ─────────────────────────────────────

    private function buildScore(array $data): CandidatureScore
    {
        $score = new CandidatureScore();
        $score->setOverallScore($data['overall_score'] ?? 0);

        $c = $data['criteria_scores'] ?? [];
        $score->setCriteriaScore('skills_match',         $c['skills_match']         ?? 0);
        $score->setCriteriaScore('experience_match',     $c['experience_match']     ?? 0);
        $score->setCriteriaScore('cover_letter_quality', $c['cover_letter_quality'] ?? 0);

        $score->setStrengths(is_array($data['strengths'])  ? $data['strengths']  : []);
        $score->setWeaknesses(is_array($data['weaknesses']) ? $data['weaknesses'] : []);
        $score->setRecommendation($data['recommendation']  ?? 'Maybe');
        $score->setSummary($data['summary']                ?? '');

        return $score;
    }

    // ── Public API endpoint ──────────────────────────────────────────────────

    public function scoreApi()
    {
        header('Content-Type: application/json; charset=utf-8');

        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin', 'client'], true)) {
            echo json_encode(['error' => 'Accès non autorisé.']);
            return;
        }

        $idCandidature = (int)($_POST['id_candidature'] ?? 0);
        if ($idCandidature <= 0) {
            echo json_encode(['error' => 'ID de candidature invalide.']);
            return;
        }

        if (!$this->gemini->isAvailable()) {
            echo json_encode(['gemini_unavailable' => true]);
            return;
        }

        $db  = getDB();
        $sql = "SELECT c.*,
                       o.titre            AS titre_offre,
                       o.description      AS offre_description,
                       o.competences_requises,
                       o.niveau_requis
                FROM candidatures c
                LEFT JOIN offres o ON c.id_offre = o.id_offre
                WHERE c.id_candidature = ?";
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            echo json_encode(['error' => 'Erreur base de données.']);
            return;
        }
        $stmt->bind_param('i', $idCandidature);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            echo json_encode(['error' => 'Candidature introuvable.']);
            return;
        }

        $geminiData = $this->callGemini($row);
        if ($geminiData === null) {
            echo json_encode(['gemini_unavailable' => true]);
            return;
        }

        $result = $this->buildScore($geminiData);
        echo json_encode($result->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
