<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/OffreController.php';
require_once __DIR__ . '/controllers/CandidatureController.php';
require_once __DIR__ . '/controllers/AdminReportController.php';
require_once __DIR__ . '/controllers/JobMatchController.php';
require_once __DIR__ . '/controllers/ModerationController.php';
require_once __DIR__ . '/controllers/CandidatureScoreController.php';

$page = $_GET['page'] ?? 'home';
$role = $_GET['role'] ?? 'client';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$offreCtrl = new OffreController();
$candidatureCtrl = new CandidatureController();
$adminReportCtrl = new AdminReportController();
$jobMatchCtrl    = new JobMatchController();
$moderationCtrl       = new ModerationController();
$candidatureScoreCtrl = new CandidatureScoreController();

// Set role in session
if (isset($_GET['role'])) {
    $_SESSION['role'] = $_GET['role'];
}
$currentRole = $_SESSION['role'] ?? 'client';

switch ($page) {
    case 'home':
        require_once __DIR__ . '/views/FrontOffice/home.php';
        break;

    // ========== OFFRES JOB ROUTES ==========
    
    // Freelancer: Browse job offers
    case 'offres':
        $offreCtrl->index();
        break;
    
    // Freelancer: View job offer details
    case 'offre_detail':
        $offreCtrl->show($id);
        break;

    // Freelancer: Apply to a job offer
    case 'candidater':
        $candidatureCtrl->create($id);
        break;
    
    // Client: My published offers
    case 'mes_offres':
        $offreCtrl->myOffres();
        break;
    
    // Client: Create new offer
    case 'create_offre':
        $offreCtrl->create();
        break;
    
    // Client: Edit offer
    case 'edit_offre':
        $offreCtrl->edit($id);
        break;
    
    // Client: Delete offer
    case 'delete_offre':
        $offreCtrl->delete($id);
        break;

    // Client: Received candidatures on offers
    case 'client_candidatures':
        $candidatureCtrl->clientIndex();
        break;

    // Client: Update candidature status
    case 'client_candidature_statut':
        $statut = $_GET['statut'] ?? 'en_attente';
        $candidatureCtrl->clientUpdateStatut($id, $statut);
        break;
    
    // Admin: View all offers
    case 'admin_offres':
        $offreCtrl->adminIndex();
        break;
    
    // Admin: Update offer status
    case 'admin_offre_statut':
        $statut = $_GET['statut'] ?? 'en_attente';
        $offreCtrl->adminUpdateStatut($id, $statut);
        break;

    // Admin: View all candidatures
    case 'admin_candidatures':
        $candidatureCtrl->adminIndex();
        break;

    // Admin: Update candidature status
    case 'admin_candidature_statut':
        $statut = $_GET['statut'] ?? 'en_attente';
        $candidatureCtrl->adminUpdateStatut($id, $statut);
        break;

    // Admin: Dashboard
    case 'admin_dashboard':
        require_once __DIR__ . '/views/BackOffice/dashboard.php';
        break;

    case 'admin_dashboard_export_pdf':
        $adminReportCtrl->exportDashboardPdf();
        break;

    case 'admin_offres_export_pdf':
        $adminReportCtrl->exportOffresPdf();
        break;

    case 'admin_candidatures_export_pdf':
        $adminReportCtrl->exportCandidaturesPdf();
        break;

    // ========== JOB MATCHING IA ==========

    case 'job_match':
        $jobMatchCtrl->index();
        break;

    case 'job_match_api':
        $jobMatchCtrl->matchApi();
        break;

    // ========== MODERATION IA ==========

    case 'moderation':
        $moderationCtrl->index();
        break;

    case 'moderation_api':
        $moderationCtrl->moderateApi();
        break;

    case 'moderation_by_id_api':
        $moderationCtrl->moderateByIdApi();
        break;

    // ========== CANDIDATURE SCORE IA ==========

    case 'candidature_score_api':
        $candidatureScoreCtrl->scoreApi();
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Page non trouvée</h1>";
}
?>
