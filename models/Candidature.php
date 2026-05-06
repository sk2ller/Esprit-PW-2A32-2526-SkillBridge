<?php

class Candidature
{
    private $id_candidature;
    private $id_offre;
    private $id_freelancer;
    private $message;
    private $proposition_budget;
    private $delai_propose;
    private $statut;
    private $created_at;
    private $updated_at;

    public function __construct(
        $id_offre = null,
        $id_freelancer = null,
        $message = null,
        $proposition_budget = null,
        $delai_propose = null,
        $statut = 'en_attente'
    ) {
        $this->id_offre = $id_offre;
        $this->id_freelancer = $id_freelancer;
        $this->message = $message;
        $this->proposition_budget = $proposition_budget;
        $this->delai_propose = $delai_propose;
        $this->statut = $statut;
    }

    public function getIdCandidature()                    { return $this->id_candidature; }
    public function setIdCandidature($id)                 { $this->id_candidature = $id; }

    public function getIdOffre()                          { return $this->id_offre; }
    public function setIdOffre($idOffre)                  { $this->id_offre = $idOffre; }

    public function getIdFreelancer()                     { return $this->id_freelancer; }
    public function setIdFreelancer($idFreelancer)        { $this->id_freelancer = $idFreelancer; }

    public function getMessage()                          { return $this->message; }
    public function setMessage($message)                  { $this->message = $message; }

    public function getPropositionBudget()                { return $this->proposition_budget; }
    public function setPropositionBudget($budget)         { $this->proposition_budget = $budget; }

    public function getDelaiPropose()                     { return $this->delai_propose; }
    public function setDelaiPropose($delai)               { $this->delai_propose = $delai; }

    public function getStatut()                           { return $this->statut; }
    public function setStatut($statut)                    { $this->statut = $statut; }

    public function getCreatedAt()                        { return $this->created_at; }
    public function setCreatedAt($date)                   { $this->created_at = $date; }

    public function getUpdatedAt()                        { return $this->updated_at; }
    public function setUpdatedAt($date)                   { $this->updated_at = $date; }
}
