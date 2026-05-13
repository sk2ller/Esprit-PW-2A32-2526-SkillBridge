<?php
require_once __DIR__ . '/../Models/Offre.php';

class OffreController
{
    private $model;

    public function __construct()
    {
        $this->model = new Offre();
    }

    public function clientList()
    {
        $this->requireRole(2);
        $offres = $this->model->listByClient((int) $_SESSION['user_id']);
        $stats = $this->model->getClientStats((int) $_SESSION['user_id']);
        require __DIR__ . '/../Views/Frontoffice/my_job_offers.php';
    }

    public function save($id = null)
    {
        $this->requireRole(2);
        $offre = null;
        $error = null;

        if ($id) {
            $offre = $this->model->findOwnedByClient((int) $id, (int) $_SESSION['user_id']);
            if (!$offre) {
                header('Location: ?action=my_job_offers');
                exit;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $budget = (float) ($_POST['budget'] ?? 0);
            $delai = (int) ($_POST['delai_jours'] ?? 0);
            $executionMode = trim($_POST['execution_mode'] ?? 'full_project');
            $milestonePlan = trim($_POST['milestone_plan'] ?? '');
            $niveau = trim($_POST['niveau_requis'] ?? 'intermediaire');
            $competences = trim($_POST['competences_requises'] ?? '');

            if ($titre === '' || $description === '' || $budget <= 0 || $delai <= 0) {
                $error = 'Veuillez remplir correctement tous les champs obligatoires.';
            } elseif (mb_strlen($titre) < 5) {
                $error = 'Le titre doit contenir au moins 5 caracteres.';
            } elseif (mb_strlen($description) < 20) {
                $error = 'La description doit contenir au moins 20 caracteres.';
            } elseif (!in_array($executionMode, ['full_project', 'milestone'], true)) {
                $error = 'Le mode de realisation choisi est invalide.';
            } elseif ($executionMode === 'milestone' && mb_strlen($milestonePlan) < 10) {
                $error = 'Ajoutez un plan milestones plus detaille pour cette offre.';
            } else {
                $data = [
                    ':titre' => $titre,
                    ':description' => $description,
                    ':budget' => $budget,
                    ':delai_jours' => $delai,
                    ':execution_mode' => $executionMode,
                    ':milestone_plan' => $executionMode === 'milestone' ? $milestonePlan : null,
                    ':niveau_requis' => $niveau,
                    ':competences_requises' => $competences,
                    ':id_client' => (int) $_SESSION['user_id']
                ];

                if ($id) {
                    unset($data[':id_client']);
                    $this->model->update((int) $id, $data);
                    header('Location: ?action=my_job_offers&success=2');
                } else {
                    $this->model->create($data);
                    header('Location: ?action=my_job_offers&success=1');
                }
                exit;
            }

            $offre = [
                'id_offre' => $id,
                'titre' => $titre,
                'description' => $description,
                'budget' => $budget,
                'delai_jours' => $delai,
                'execution_mode' => $executionMode,
                'milestone_plan' => $milestonePlan,
                'niveau_requis' => $niveau,
                'competences_requises' => $competences
            ];
        }

        require __DIR__ . '/../Views/Frontoffice/job_offer_form.php';
    }

    public function delete($id)
    {
        $this->requireRole(2);
        $offre = $this->model->findOwnedByClient((int) $id, (int) $_SESSION['user_id']);
        if ($offre) {
            $this->model->delete((int) $id);
        }
        header('Location: ?action=my_job_offers&success=3');
        exit;
    }

    public function publicList()
    {
        $this->requireRole(3);
        $search = trim($_GET['search'] ?? '');
        $offres = $this->model->listActive($search);
        $stats = $this->model->getFreelancerStats((int) $_SESSION['user_id']);
        require __DIR__ . '/../Views/Frontoffice/job_offers.php';
    }

    public function detail($id)
    {
        $offre = $this->model->find((int) $id);
        if (!$offre) {
            header('Location: ?action=job_offers');
            exit;
        }

        $hasApplied = false;
        $applications = [];
        $isOwner = false;

        if ((int) ($_SESSION['user_role'] ?? 0) === 3) {
            $hasApplied = $this->model->hasApplied((int) $id, (int) $_SESSION['user_id']);
        }

        if ((int) ($_SESSION['user_role'] ?? 0) === 2 && (int) $_SESSION['user_id'] === (int) $offre['id_client']) {
            $isOwner = true;
            $applications = $this->model->listApplicationsByOffer((int) $id);
        }

        require __DIR__ . '/../Views/Frontoffice/job_offer_detail.php';
    }

    public function updateClientApplicationStatus($idCandidature, $status)
    {
        $this->requireRole(2);

        if (!in_array($status, ['acceptee', 'refusee'], true)) {
            header('Location: ?action=my_job_offers&error=4');
            exit;
        }

        $applications = $this->model->listAllApplications();
        $target = null;
        foreach ($applications as $application) {
            if ((int) $application['id_candidature'] === (int) $idCandidature) {
                $target = $application;
                break;
            }
        }

        if (!$target) {
            header('Location: ?action=my_job_offers&error=5');
            exit;
        }

        $offre = $this->model->find((int) $target['id_offre']);
        if (!$offre || (int) $offre['id_client'] !== (int) $_SESSION['user_id']) {
            header('Location: ?action=my_job_offers&error=6');
            exit;
        }

        $this->model->updateApplicationStatus((int) $idCandidature, $status);
        header('Location: ?action=job_offer_detail&id=' . (int) $offre['id_offre'] . '&success=2');
        exit;
    }

    public function apply($id)
    {
        $this->requireRole(3);
        $offre = $this->model->find((int) $id);
        if (!$offre || $offre['statut'] !== 'actif') {
            header('Location: ?action=job_offers&error=1');
            exit;
        }

        if ($this->model->hasApplied((int) $id, (int) $_SESSION['user_id'])) {
            header('Location: ?action=job_offer_detail&id=' . (int) $id . '&error=2');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = trim($_POST['message'] ?? '');
            $budget = (float) ($_POST['budget_propose'] ?? 0);
            $days = (int) ($_POST['disponibilite_jours'] ?? 0);
            $executionMode = trim($_POST['execution_mode'] ?? 'full_project');
            $milestonePlan = trim($_POST['milestone_plan'] ?? '');
            $cv = trim($_POST['cv_url'] ?? '');
            $portfolio = trim($_POST['portfolio_url'] ?? '');

            if (
                $message !== '' &&
                mb_strlen($message) >= 20 &&
                $budget > 0 &&
                $days > 0 &&
                in_array($executionMode, ['full_project', 'milestone'], true) &&
                ($executionMode !== 'milestone' || mb_strlen($milestonePlan) >= 10)
            ) {
                $this->model->createApplication([
                    ':id_offre' => (int) $id,
                    ':id_freelancer' => (int) $_SESSION['user_id'],
                    ':message' => $message,
                    ':budget_propose' => $budget,
                    ':disponibilite_jours' => $days,
                    ':execution_mode' => $executionMode,
                    ':milestone_plan' => $executionMode === 'milestone' ? $milestonePlan : null,
                    ':cv_url' => $cv !== '' ? $cv : null,
                    ':portfolio_url' => $portfolio !== '' ? $portfolio : null
                ]);
                header('Location: ?action=job_offer_detail&id=' . (int) $id . '&success=1');
                exit;
            }
        }

        header('Location: ?action=job_offer_detail&id=' . (int) $id . '&error=3');
        exit;
    }

    public function myApplications()
    {
        $this->requireRole(3);
        $applications = $this->model->listApplicationsByFreelancer((int) $_SESSION['user_id']);
        $stats = $this->model->getFreelancerStats((int) $_SESSION['user_id']);
        require __DIR__ . '/../Views/Frontoffice/my_applications.php';
    }

    public function adminList()
    {
        $this->requireRole(1);
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? 'all');
        $offres = $this->model->listAll($search, $status);
        $stats = $this->model->getAdminStats();
        require __DIR__ . '/../Views/Backoffice/offerList.php';
    }

    public function adminApplications()
    {
        $this->requireRole(1);
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? 'all');
        $applications = $this->model->listAllApplications($search, $status);
        require __DIR__ . '/../Views/Backoffice/applicationList.php';
    }

    public function updateStatus($id, $status)
    {
        $this->requireRole(1);
        if (in_array($status, ['actif', 'en_attente', 'suspendu', 'fermee'], true)) {
            $this->model->updateStatus((int) $id, $status);
        }
        header('Location: ?action=job_offers_admin&success=1');
        exit;
    }

    public function updateApplicationStatus($id, $status)
    {
        $this->requireRole(1);
        header('Location: ?action=job_applications_admin&success=2');
        exit;
    }

    private function requireRole($role)
    {
        if (!isset($_SESSION['user_id']) || (int) $_SESSION['user_role'] !== (int) $role) {
            header('Location: ?action=login');
            exit;
        }
    }
}
?>
