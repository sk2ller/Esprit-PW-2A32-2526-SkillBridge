<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/OffreController.php';
require_once __DIR__ . '/CandidatureController.php';
require_once __DIR__ . '/PdfExporter.php';

class AdminReportController
{
    private OffreController $offreController;
    private CandidatureController $candidatureController;
    private PdfExporter $pdfExporter;

    public function __construct()
    {
        $this->offreController = new OffreController();
        $this->candidatureController = new CandidatureController();
        $this->pdfExporter = new PdfExporter();
    }

    public function exportDashboardPdf(): void
    {
        $offreStats = $this->offreController->getStats();
        $candidatureStats = $this->candidatureController->getStats();
        $offersByLevel = $this->offreController->getAdminInsights()['by_level'] ?? [];

        $rows = [
            ['Offres total', $offreStats['total'] ?? 0, 'Actives', $offreStats['actif'] ?? 0],
            ['Offres attente', $offreStats['en_attente'] ?? 0, 'Suspendues', $offreStats['suspendu'] ?? 0],
            ['Candidatures total', $candidatureStats['total'] ?? 0, 'Acceptees', $candidatureStats['acceptee'] ?? 0],
            ['Candidatures attente', $candidatureStats['en_attente'] ?? 0, 'Refusees', $candidatureStats['refusee'] ?? 0],
        ];

        foreach ($offersByLevel as $level) {
            $rows[] = ['Niveau', ucfirst($level['niveau_requis']), 'Nombre offres', $level['total']];
        }

        $this->pdfExporter->renderReport(
            'Dashboard SkillBridge',
            'Synthese metier des offres et candidatures',
            [
                'module' => 'Dashboard admin',
                'periode' => 'Etat actuel',
                'export' => date('d/m/Y H:i')
            ],
            ['Indicateur', 'Valeur', 'Indicateur 2', 'Valeur 2'],
            $rows,
            'dashboard_skillbridge.pdf'
        );
    }

    public function exportOffresPdf(): void
    {
        $filter = $_GET['filter'] ?? 'all';
        $search = $_GET['search'] ?? null;
        $sort = $_GET['sort'] ?? 'recent';
        $offres = $filter === 'all'
            ? $this->offreController->listAll(null, $search, $sort)
            : $this->offreController->listAll($filter, $search, $sort);

        $rows = array_map(static function ($offre) {
            return [
                '#' . $offre['id_offre'],
                $offre['titre'],
                $offre['nom_client'] ?? 'Client',
                number_format((float) $offre['budget'], 2) . ' DT',
                ucfirst($offre['niveau_requis']),
                $offre['statut']
            ];
        }, $offres);

        $this->pdfExporter->renderReport(
            'Offres Job',
            'Export filtre de la gestion admin',
            [
                'filtre' => $filter,
                'tri' => $sort,
                'recherche' => $search ?: 'Aucune'
            ],
            ['ID', 'Titre', 'Client', 'Budget', 'Niveau', 'Statut'],
            $rows,
            'offres_job_admin.pdf'
        );
    }

    public function exportCandidaturesPdf(): void
    {
        $filter = $_GET['filter'] ?? 'all';
        $search = $_GET['search'] ?? null;
        $sort = $_GET['sort'] ?? 'recent';
        $candidatures = $this->candidatureController->getAdminList($filter, $search, $sort);

        $rows = array_map(static function ($candidature) {
            return [
                '#' . $candidature['id_candidature'],
                $candidature['nom_freelancer'],
                $candidature['titre_offre'] ?? 'Offre',
                number_format((float) ($candidature['tarif_propose'] ?? 0), 2) . ' DT',
                $candidature['statut'],
                date('d/m/Y', strtotime($candidature['created_at']))
            ];
        }, $candidatures);

        $this->pdfExporter->renderReport(
            'Candidatures',
            'Export filtre de la gestion admin',
            [
                'filtre' => $filter,
                'tri' => $sort,
                'recherche' => $search ?: 'Aucune'
            ],
            ['ID', 'Freelancer', 'Offre', 'Tarif', 'Statut', 'Date'],
            $rows,
            'candidatures_admin.pdf'
        );
    }
}
