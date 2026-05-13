<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/ServiceController.php');
require_once(__DIR__ . '/../services/StripePaymentService.php');

class PaymentController
{
    public function checkout($idService)
    {
        $serviceController = new ServiceController();
        $serviceObject = $serviceController->getServiceById((int) $idService);

        if (!$serviceObject) {
            header('Location: index.php?page=services');
            exit;
        }

        $service = [
            'id_service' => $serviceObject->getId(),
            'titre' => $serviceObject->getTitre(),
            'description' => $serviceObject->getDescription(),
            'prix' => $serviceObject->getPrix()
        ];

        $baseUrl = $this->getCurrentBaseUrl();
        $successUrl = $baseUrl . 'index.php?page=payment_success&id=' . (int) $idService . '&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = $baseUrl . 'index.php?page=service_detail&id=' . (int) $idService . '&payment=cancel';

        $stripe = new StripePaymentService();
        $session = $stripe->createCheckoutSession($service, $successUrl, $cancelUrl);

        if (!$session['success']) {
            $_SESSION['payment_error'] = $session['message'];
            header('Location: index.php?page=service_detail&id=' . (int) $idService . '&payment=error');
            exit;
        }

        header('Location: ' . $session['url']);
        exit;
    }

    public function success($idService)
    {
        $serviceController = new ServiceController();
        $serviceObject = $serviceController->getServiceById((int) $idService);
        $service = $serviceObject ? [
            'id_service' => $serviceObject->getId(),
            'titre' => $serviceObject->getTitre(),
            'prix' => $serviceObject->getPrix(),
            'freelancer_name' => $serviceObject->getFreelancerName()
        ] : null;

        require_once(__DIR__ . '/../views/FrontOffice/payment_success.php');
    }

    private function getCurrentBaseUrl()
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

        return $scheme . '://' . $host . ($scriptDir ? $scriptDir . '/' : '/');
    }
}
