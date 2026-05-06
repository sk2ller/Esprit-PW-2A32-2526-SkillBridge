<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../models/CandidatProfil.php');
require_once(__DIR__ . '/../controllers/OffreController.php');

class JobMatchController
{
    private $offreCtrl;

    public function __construct()
    {
        $this->offreCtrl = new OffreController();
    }

    public function index()
    {
        if (($_SESSION['role'] ?? '') !== 'freelancer') {
            header('Location: index.php?page=offres');
            exit;
        }
        require_once(__DIR__ . '/../views/FrontOffice/job_match.php');
    }

    public function matchApi()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (($_SESSION['role'] ?? '') !== 'freelancer') {
            echo json_encode(['error' => 'Accès non autorisé.']);
            return;
        }

        $profil = new CandidatProfil(
            '',
            trim($_POST['skills']           ?? ''),
            (int)($_POST['experience_years'] ?? 0),
            trim($_POST['preferred_domain'] ?? '')
        );

        if (empty($profil->getCompetences())) {
            echo json_encode(['error' => 'Veuillez renseigner au moins une compétence.']);
            return;
        }

        $offres = $this->offreCtrl->listAll('actif');

        if (empty($offres)) {
            echo json_encode([]);
            return;
        }

        $results = [];
        foreach ($offres as $offre) {
            $results[] = $this->scoreOffer($offre, $profil);
        }

        usort($results, fn($a, $b) => $b['match_score'] - $a['match_score']);

        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function scoreOffer(array $offre, CandidatProfil $profil): array
    {
        $totalScore = 0;
        $reasons    = [];

        // ── 1. Skill matching (max 45 pts) ──────────────────────────────────
        $candidateSkills = $profil->getCompetencesArray();
        $requiredSkills  = array_filter(array_map('trim', explode(',', mb_strtolower($offre['competences_requises'] ?? ''))));

        $matchedSkills = [];

        if (!empty($requiredSkills)) {
            foreach ($candidateSkills as $cs) {
                foreach ($requiredSkills as $rs) {
                    if ($cs === '' || $rs === '') continue;
                    $isSimilar = (
                        str_contains($rs, $cs) ||
                        str_contains($cs, $rs) ||
                        levenshtein($cs, $rs) <= 2
                    );
                    if ($isSimilar) {
                        $matchedSkills[] = ucfirst($cs);
                        break;
                    }
                }
            }
            $skillRatio = count($matchedSkills) / count($requiredSkills);
            $skillScore = (int)round($skillRatio * 45);
        } else {
            $skillScore = 22;
        }

        $totalScore += $skillScore;

        if (!empty($matchedSkills)) {
            $reasons[] = 'Compétences correspondantes : ' . implode(', ', array_unique($matchedSkills));
        } elseif (!empty($requiredSkills)) {
            $reasons[] = 'Aucune compétence requise ne correspond à votre profil';
        }

        // ── 2. Experience level (max 35 pts) ────────────────────────────────
        $niveau = mb_strtolower(trim($offre['niveau_requis'] ?? 'intermediaire'));

        $levelMap = [
            'debutant'      => [0, 1],
            'junior'        => [0, 2],
            'intermediaire' => [2, 5],
            'confirme'      => [4, 8],
            'senior'        => [5, 99],
            'expert'        => [7, 99],
        ];

        [$minExp, $maxExp] = $levelMap[$niveau] ?? [0, 99];
        $exp = $profil->getAnneesExperience();

        if ($exp >= $minExp && $exp <= $maxExp) {
            $expScore  = 35;
            $reasons[] = "Niveau d'expérience idéal ({$exp} ans pour le niveau " . ucfirst($niveau) . ')';
        } elseif ($exp < $minExp && ($minExp - $exp) <= 1) {
            $expScore  = 22;
            $reasons[] = "Expérience légèrement inférieure au requis ({$exp} ans / min {$minExp} ans)";
        } elseif ($exp > $maxExp && $maxExp < 99) {
            $expScore  = 25;
            $reasons[] = "Profil surqualifié, mais tout à fait capable pour ce niveau";
        } else {
            $expScore = 10;
        }

        $totalScore += $expScore;

        // ── 3. Domain / keyword match (max 20 pts) ──────────────────────────
        $domainScore = 0;

        if (!empty($profil->getDomainePrefere())) {
            $domainLower = mb_strtolower($profil->getDomainePrefere());
            $searchIn    = mb_strtolower(
                ($offre['titre']                ?? '') . ' ' .
                ($offre['description']          ?? '') . ' ' .
                ($offre['competences_requises'] ?? '')
            );

            $keywords = array_filter(array_unique(
                array_merge(
                    array_map('trim', explode(' ', $domainLower)),
                    array_map('trim', explode(',', $domainLower))
                )
            ));

            $hits = 0;
            foreach ($keywords as $kw) {
                if (mb_strlen($kw) >= 3 && str_contains($searchIn, $kw)) {
                    $hits++;
                }
            }

            if ($hits > 0) {
                $domainScore = min(20, $hits * 7);
                $reasons[]   = "Domaine préféré correspond : " . $profil->getDomainePrefere();
            }
        } else {
            $domainScore = 10;
        }

        $totalScore += $domainScore;

        $finalScore = min(100, max(0, $totalScore));

        if ($finalScore >= 80) {
            $label = 'Excellent Match';
        } elseif ($finalScore >= 60) {
            $label = 'Good Match';
        } else {
            $label = 'Partial Match';
        }

        if (empty($reasons)) {
            $reasons[] = "Offre disponible, vérifiez les compétences requises";
        }

        return [
            'offer_id'             => (int)$offre['id_offre'],
            'titre'                => $offre['titre'],
            'budget'               => (float)$offre['budget'],
            'niveau_requis'        => $offre['niveau_requis'],
            'competences_requises' => $offre['competences_requises'],
            'nom_client'           => $offre['nom_client'] ?? 'Client',
            'delai_publication'    => (int)$offre['delai_publication'],
            'match_score'          => $finalScore,
            'match_reasons'        => array_values(array_unique($reasons)),
            'recommendation_label' => $label,
        ];
    }
}
