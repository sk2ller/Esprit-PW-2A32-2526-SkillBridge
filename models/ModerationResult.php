<?php

class ModerationResult
{
    private $is_approved;
    private $severity;
    private $flagged_words;
    private $reason;
    private $suggestion;

    public function __construct(
        $is_approved   = true,
        $severity      = 'none',
        $flagged_words = [],
        $reason        = '',
        $suggestion    = ''
    ) {
        $this->is_approved   = $is_approved;
        $this->severity      = $severity;
        $this->flagged_words = $flagged_words;
        $this->reason        = $reason;
        $this->suggestion    = $suggestion;
    }

    public function getIsApproved()                    { return $this->is_approved; }
    public function setIsApproved($is_approved)        { $this->is_approved = (bool)$is_approved; }

    public function getSeverity()                      { return $this->severity; }
    public function setSeverity($severity)             { $this->severity = $severity; }

    public function getFlaggedWords()                  { return $this->flagged_words; }
    public function setFlaggedWords($flagged_words)    { $this->flagged_words = $flagged_words; }
    public function addFlaggedWord($word)              { $this->flagged_words[] = $word; }

    public function getReason()                        { return $this->reason; }
    public function setReason($reason)                 { $this->reason = $reason; }

    public function getSuggestion()                    { return $this->suggestion; }
    public function setSuggestion($suggestion)         { $this->suggestion = $suggestion; }

    public function toArray(): array
    {
        return [
            'is_approved'   => $this->is_approved,
            'severity'      => $this->severity,
            'flagged_words' => array_values(array_unique($this->flagged_words)),
            'reason'        => $this->reason,
            'suggestion'    => $this->suggestion,
        ];
    }
}
