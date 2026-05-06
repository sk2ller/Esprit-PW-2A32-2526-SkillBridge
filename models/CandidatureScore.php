<?php

class CandidatureScore
{
    private $overall_score;
    private $criteria_scores;
    private $strengths;
    private $weaknesses;
    private $recommendation;
    private $summary;

    public function __construct(
        $overall_score   = 0,
        $criteria_scores = ['skills_match' => 0, 'experience_match' => 0, 'cover_letter_quality' => 0],
        $strengths       = [],
        $weaknesses      = [],
        $recommendation  = 'Maybe',
        $summary         = ''
    ) {
        $this->overall_score   = $overall_score;
        $this->criteria_scores = $criteria_scores;
        $this->strengths       = $strengths;
        $this->weaknesses      = $weaknesses;
        $this->recommendation  = $recommendation;
        $this->summary         = $summary;
    }

    public function getOverallScore()                       { return $this->overall_score; }
    public function setOverallScore($v)                     { $this->overall_score = (int) max(0, min(100, $v)); }

    public function getCriteriaScores()                     { return $this->criteria_scores; }
    public function setCriteriaScores($v)                   { $this->criteria_scores = $v; }
    public function setCriteriaScore($key, $val)            { $this->criteria_scores[$key] = (int) max(0, min(100, $val)); }

    public function getStrengths()                          { return $this->strengths; }
    public function setStrengths($v)                        { $this->strengths = $v; }

    public function getWeaknesses()                         { return $this->weaknesses; }
    public function setWeaknesses($v)                       { $this->weaknesses = $v; }

    public function getRecommendation()                     { return $this->recommendation; }
    public function setRecommendation($v)                   { $this->recommendation = $v; }

    public function getSummary()                            { return $this->summary; }
    public function setSummary($v)                          { $this->summary = $v; }

    public function toArray(): array
    {
        return [
            'overall_score'   => $this->overall_score,
            'criteria_scores' => $this->criteria_scores,
            'strengths'       => array_values($this->strengths),
            'weaknesses'      => array_values($this->weaknesses),
            'recommendation'  => $this->recommendation,
            'summary'         => $this->summary,
        ];
    }
}
