<?php

class CandidatProfil
{
    private $nom;
    private $competences;
    private $annees_experience;
    private $domaine_prefere;

    public function __construct(
        $nom               = null,
        $competences       = null,
        $annees_experience = 0,
        $domaine_prefere   = null
    ) {
        $this->nom               = $nom;
        $this->competences       = $competences;
        $this->annees_experience = $annees_experience;
        $this->domaine_prefere   = $domaine_prefere;
    }

    public function getNom()                         { return $this->nom; }
    public function setNom($nom)                     { $this->nom = $nom; }

    public function getCompetences()                 { return $this->competences; }
    public function setCompetences($competences)     { $this->competences = $competences; }

    public function getAnneesExperience()            { return $this->annees_experience; }
    public function setAnneesExperience($annees)     { $this->annees_experience = (int)$annees; }

    public function getDomainePrefere()              { return $this->domaine_prefere; }
    public function setDomainePrefere($domaine)      { $this->domaine_prefere = $domaine; }

    public function getCompetencesArray(): array
    {
        if (empty($this->competences)) {
            return [];
        }
        return array_filter(array_map('trim', explode(',', mb_strtolower($this->competences))));
    }
}
