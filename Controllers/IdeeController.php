<?php
require_once(__DIR__ . '/../Models/Idee.php');
require_once(__DIR__ . '/../Controllers/BrainstormingController.php');
require_once(__DIR__ . '/../Services/AiIdeaScoringService.php');

class IdeeController
{
    private $ideeModel;
    private $brainstormingController;
    private $aiIdeaScoringService;

    public function __construct()
    {
        $this->ideeModel = new Idee();
        $this->brainstormingController = new BrainstormingController();
        $this->aiIdeaScoringService = new AiIdeaScoringService();
    }

    private function isAdmin(): bool
    {
        return (int) ($_SESSION['user_role'] ?? 0) === 1;
    }

    private function requireAuthentication(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
    }

    private function canAccessBrainstorming(array $brainstorming): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return (int) $brainstorming['accepted'] === 1;
    }

    private function canManageIdee(array $idee): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] === (int) $idee['user_id'];
    }

    private function jsonResponse(array $payload): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }

    private function buildIdeaPayload(array $data, ?array $existingIdee = null): array
    {
        $payload = [
            'titre' => $data['titre'] ?? '',
            'contenu' => $data['contenu'] ?? '',
            'categorie' => $data['categorie'] ?? 'General',
            'priorite' => $data['priorite'] ?? 'moyenne',
            'brainstorming_id' => $data['brainstorming_id'] ?? 0,
            'user_id' => $existingIdee['user_id'] ?? ($_SESSION['user_id'] ?? 0),
            'votes' => $existingIdee['votes'] ?? 0,
            'statut' => $existingIdee['statut'] ?? 'proposee',
        ];

        if ($this->isAdmin()) {
            $payload['votes'] = $data['votes'] ?? ($existingIdee['votes'] ?? 0);
            $payload['statut'] = $data['statut'] ?? ($existingIdee['statut'] ?? 'proposee');
        }

        return $payload;
    }

    public function list($brainstormingId)
    {
        $brainstormingId = (int) $brainstormingId;
        $brainstorming = $this->brainstormingController->getById($brainstormingId);

        if (!$brainstorming || !$this->canAccessBrainstorming($brainstorming)) {
            header('Location: index.php?action=brainstorming_list');
            exit;
        }

        $idees = $this->ideeModel->getAllByBrainstorming($brainstormingId);
        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);
        $isAdmin = $this->isAdmin();
        include __DIR__ . '/../Views/Frontoffice/ideeList.php';
    }

    public function add($data)
    {
        $this->requireAuthentication();

        $brainstormings = $this->brainstormingController->listVisibleForUser($_SESSION['user_id'], $this->isAdmin());
        $brainstormingId = isset($data['brainstorming_id']) ? (int) $data['brainstorming_id'] : 0;
        $error = '';
        $success = '';
        $validationErrors = [];
        $isAdmin = $this->isAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = $this->buildIdeaPayload($data);
            $result = $this->ideeModel->createIdeeWithValidation($payload);

            if ($result['success']) {
                $success = 'Idee ajoutee avec succes.';
                $_POST = [];
                $brainstormingId = 0;
            } else {
                $error = $result['message'];
                $validationErrors = $result['errors'] ?? [];
            }
        }

        include __DIR__ . '/../Views/Frontoffice/addIdee.php';
    }

    public function edit($id, $data = null)
    {
        $this->requireAuthentication();

        $id = (int) $id;
        $idee = $this->ideeModel->getById($id);

        if (!$idee) {
            header('Location: index.php?action=all_idees');
            exit;
        }

        if (!$this->canManageIdee($idee)) {
            header('Location: index.php?action=all_idees');
            exit;
        }

        $brainstormings = $this->brainstormingController->listVisibleForUser($_SESSION['user_id'], $this->isAdmin());
        $error = '';
        $validationErrors = [];
        $isAdmin = $this->isAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $data) {
            $payload = $this->buildIdeaPayload($data, $idee);
            $result = $this->ideeModel->updateIdeeWithValidation($id, $payload);

            if ($result['success']) {
                header('Location: index.php?action=list_idees&brainstorming_id=' . (int) $result['brainstorming_id']);
                exit;
            }

            $error = $result['message'];
            $validationErrors = $result['errors'] ?? [];
            $idee = array_merge($idee, Idee::sanitizeData($payload), [
                'priorite' => $payload['priorite'],
                'statut' => $payload['statut'],
                'votes' => $payload['votes'],
                'brainstorming_id' => (int) $payload['brainstorming_id'],
            ]);
        }

        include __DIR__ . '/../Views/Frontoffice/editIdee.php';
    }

    public function delete($id, $brainstormingId)
    {
        $this->requireAuthentication();

        $idee = $this->ideeModel->getById((int) $id);
        if (!$idee || !$this->canManageIdee($idee)) {
            header('Location: index.php?action=all_idees');
            exit;
        }

        $this->ideeModel->delete((int) $id);
        header('Location: index.php?action=list_idees&brainstorming_id=' . (int) $brainstormingId);
        exit;
    }

    public function adminList()
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?action=home');
            exit;
        }

        if (isset($_GET['get_details'])) {
            $idee = $this->ideeModel->getById((int) $_GET['get_details']);
            $this->jsonResponse($idee
                ? ['success' => true, 'idee' => $idee]
                : ['success' => false, 'message' => 'Idee introuvable.']
            );
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $id = (int) ($_POST['id'] ?? 0);

            if ($action === 'add_idee_admin') {
                $payload = $this->buildIdeaPayload($_POST);
                $result = $this->ideeModel->createIdeeWithValidation($payload);
                $this->jsonResponse($result);
            }

            if ($action === 'edit_idee_admin' && $id > 0) {
                $idee = $this->ideeModel->getById($id);
                if (!$idee) {
                    $this->jsonResponse(['success' => false, 'message' => 'Idee introuvable.']);
                }

                $payload = $this->buildIdeaPayload($_POST, $idee);
                $result = $this->ideeModel->updateIdeeWithValidation($id, $payload);
                $this->jsonResponse($result);
            }

            if ($action === 'score_idee_ai' && $id > 0) {
                $idee = $this->ideeModel->getById($id);
                if (!$idee) {
                    $this->jsonResponse(['success' => false, 'message' => 'Idee introuvable.']);
                }

                $this->jsonResponse($this->aiIdeaScoringService->scoreIdea($idee));
            }

            if ($action === 'delete_idee' && $id > 0) {
                $success = $this->ideeModel->delete($id);
                $this->jsonResponse([
                    'success' => $success,
                    'message' => $success ? 'Idee supprimee avec succes.' : 'Erreur lors de la suppression.'
                ]);
            }

            if ($action === 'update_status' && $id > 0) {
                $status = $_POST['status'] ?? 'proposee';
                $success = $this->ideeModel->updateStatus($id, $status);
                $this->jsonResponse([
                    'success' => $success,
                    'message' => $success ? 'Statut de l idee mis a jour.' : 'Erreur lors de la mise a jour.'
                ]);
            }
        }

        $searchQuery = trim($_GET['search'] ?? '');
        $orderBy = $_GET['order_by'] ?? 'created_at';
        $orderDirection = $_GET['order_dir'] ?? 'DESC';

        if (!empty($searchQuery)) {
            $idees = $this->ideeModel->search($searchQuery, $orderBy, $orderDirection);
        } else {
            $idees = $this->ideeModel->getAll($orderBy, $orderDirection);
        }
        $brainstormings = $this->brainstormingController->listAll();
        include __DIR__ . '/../Views/Backoffice/ideeAdminList.php';
    }

    public function all()
    {
        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);
        $isAdmin = $this->isAdmin();

        if (isset($_GET['get_details'])) {
            $idee = $this->ideeModel->getById((int) $_GET['get_details']);
            if (!$idee || !$this->canManageIdee($idee)) {
                $this->jsonResponse(['success' => false, 'message' => 'Idee introuvable ou acces refuse.']);
            }

            $this->jsonResponse(['success' => true, 'idee' => $idee]);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_idee_front') {
            $id = (int) ($_POST['id'] ?? 0);
            $idee = $this->ideeModel->getById($id);

            if (!$idee || !$this->canManageIdee($idee)) {
                $this->jsonResponse(['success' => false, 'message' => 'Idee introuvable ou acces refuse.']);
            }

            $payload = $this->buildIdeaPayload($_POST, $idee);
            $result = $this->ideeModel->updateIdeeWithValidation($id, $payload);
            $this->jsonResponse($result + ['message' => $result['success'] ? 'Idee modifiee avec succes.' : ($result['message'] ?? 'Erreur formulaire.')]);
        }

        $idees = $this->ideeModel->getAll();

        if (!$isAdmin) {
            $idees = array_values(array_filter($idees, function ($idee) {
                return (int) ($idee['brainstorming_accepted'] ?? 0) === 1;
            }));
        }

        $brainstormings = $this->brainstormingController->listVisibleForUser($currentUserId, $isAdmin);
        include __DIR__ . '/../Views/Frontoffice/ideeAllList.php';
    }
}