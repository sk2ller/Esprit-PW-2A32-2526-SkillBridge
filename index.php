<?php
session_start();

require_once __DIR__ . '/Controllers/ServiceController.php';
require_once __DIR__ . '/Controllers/CategorieController.php';
require_once __DIR__ . '/Controllers/ChatController.php';
require_once __DIR__ . '/Controllers/OffreController.php';
require_once __DIR__ . '/Controllers/BrainstormingController.php';
require_once __DIR__ . '/Controllers/IdeeController.php';

$request = $_GET['action'] ?? 'home';
$serviceController = new ServiceController();
$categorieController = new CategorieController();
$chatController = new ChatController();
$offreController = new OffreController();

if ($request === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    setcookie('jwt', '', time() - 3600, '/');
    session_destroy();
    header('Location: ?action=home');
    exit;
}

switch ($request) {
    case 'login':
        require __DIR__ . '/Views/Frontoffice/login.php';
        break;

    case 'register':
        require __DIR__ . '/Views/Frontoffice/register.php';
        break;

    case 'profile':
        require __DIR__ . '/Views/Frontoffice/profile.php';
        break;

    case 'myrating':
        require __DIR__ . '/Views/Frontoffice/myrating.php';
        break;

    case 'freelancers':
        require __DIR__ . '/Views/Frontoffice/freelancers.php';
        break;

    case 'services':
        $serviceController->publicList();
        break;

    case 'service_detail':
        $serviceController->detail((int)($_GET['id'] ?? 0));
        break;

    case 'my_services':
        $serviceController->freelancerList();
        break;

    case 'job_offers':
        $offreController->publicList();
        break;

    case 'job_offer_detail':
        $offreController->detail((int)($_GET['id'] ?? 0));
        break;

    case 'job_offer_apply':
        $offreController->apply((int)($_GET['id'] ?? 0));
        break;

    case 'my_applications':
        $offreController->myApplications();
        break;

    case 'my_job_offers':
        $offreController->clientList();
        break;

    case 'job_offer_create':
        $offreController->save();
        break;

    case 'job_offer_edit':
        $offreController->save((int)($_GET['id'] ?? 0));
        break;

    case 'job_offer_delete':
        $offreController->delete((int)($_GET['id'] ?? 0));
        break;

    case 'job_application_client_status':
        $offreController->updateClientApplicationStatus((int)($_GET['id'] ?? 0), $_GET['status'] ?? '');
        break;

    case 'service_create':
        $serviceController->save();
        break;

    case 'service_edit':
        $serviceController->save((int)($_GET['id'] ?? 0));
        break;

    case 'service_delete':
        $serviceController->delete((int)($_GET['id'] ?? 0));
        break;

    case 'services_admin':
        $serviceController->adminList();
        break;

    case 'service_create_admin':
        $serviceController->adminCreate();
        break;

    case 'service_edit_admin':
        $serviceController->adminEdit((int)($_GET['id'] ?? 0));
        break;

    case 'service_delete_admin':
        $serviceController->adminDelete((int)($_GET['id'] ?? 0));
        break;

    case 'service_status':
        $serviceController->updateStatus((int)($_GET['id'] ?? 0), $_GET['status'] ?? '');
        break;

    case 'job_offers_admin':
        $offreController->adminList();
        break;

    case 'job_applications_admin':
        $offreController->adminApplications();
        break;

    case 'job_offer_status':
        $offreController->updateStatus((int)($_GET['id'] ?? 0), $_GET['status'] ?? '');
        break;

    case 'job_application_status':
        $offreController->updateApplicationStatus((int)($_GET['id'] ?? 0), $_GET['status'] ?? '');
        break;

    case 'chat':
        $chatController->chatPage(
            (int)($_GET['service_id'] ?? 0),
            (int)($_GET['conversation_id'] ?? 0),
            (int)($_GET['offer_id'] ?? 0),
            (int)($_GET['freelancer_id'] ?? 0)
        );
        break;

    case 'chat_send':
        $chatController->sendMessage();
        break;

    case 'chat_messages':
        $chatController->messagesJson((int)($_GET['conversation_id'] ?? 0));
        break;

    case 'categories_admin':
        $categorieController->adminIndex();
        break;

    case 'category_create':
        $categorieController->create();
        break;

    case 'category_edit':
        $categorieController->edit((int)($_GET['id'] ?? 0));
        break;

    case 'category_delete':
        $categorieController->delete((int)($_GET['id'] ?? 0));
        break;

    case 'userlist':
        require __DIR__ . '/Views/Backoffice/userList.php';
        break;

    case 'statistics':
        require __DIR__ . '/Views/Backoffice/statistics.php';
        break;

    case 'brainstorming_add':
        require __DIR__ . '/Views/Frontoffice/addBrainstorming.php';
        break;

    case 'brainstorming_list':
        require __DIR__ . '/Views/Frontoffice/brainstormingList.php';
        break;

    case 'brainstorming_admin':
        require __DIR__ . '/Views/Backoffice/brainstormingAdmin.php';
        break;

    case 'export_brainstorming_excel':
        require __DIR__ . '/Views/Backoffice/exportBrainstormingExcel.php';
        break;

    case 'export_idees_excel':
        require __DIR__ . '/Views/Backoffice/exportIdeesExcel.php';
        break;

    case 'list_idees':
        $ideeController = new IdeeController();
        $ideeController->list($_GET['brainstorming_id'] ?? 0);
        break;

    case 'add_idee':
        $ideeController = new IdeeController();
        $ideeController->add($_POST ?: $_GET);
        break;

    case 'edit_idee':
        $ideeController = new IdeeController();
        $ideeController->edit($_GET['id'] ?? 0, $_POST ?: null);
        break;

    case 'delete_idee':
        $ideeController = new IdeeController();
        $ideeController->delete($_GET['id'] ?? 0, $_GET['brainstorming_id'] ?? 0);
        break;

    case 'idee_admin':
        $ideeController = new IdeeController();
        $ideeController->adminList();
        break;

    case 'all_idees':
        $ideeController = new IdeeController();
        $ideeController->all();
        break;

    case 'home':
    default:
        require __DIR__ . '/Views/Frontoffice/Home.php';
        break;
}
?>
