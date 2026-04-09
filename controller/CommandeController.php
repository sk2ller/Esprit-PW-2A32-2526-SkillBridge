<?php
declare(strict_types=1);

namespace App\Controller;

use App\Modele\Commande;
use App\Modele\CommandeRepository;
use App\Modele\CommandeValidator;

final class CommandeController
{
    public function __construct(private CommandeRepository $repository)
    {
    }

    public function list(string $space): void
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render($space, [
            'page' => 'list',
            'commandes' => $this->repository->findAll(),
            'flash' => $flash,
            'csrf' => $this->getCsrfToken(),
        ]);
    }

    public function create(string $space, array $formData = [], array $errors = []): void
    {
        if (($formData['date_creation'] ?? '') === '') {
            $formData['date_creation'] = date('Y-m-d');
        }

        if (($formData['statut'] ?? '') === '') {
            $formData['statut'] = 'en_cours';
        }

        $this->render($space, [
            'page' => 'form',
            'mode' => 'create',
            'formData' => $formData,
            'errors' => $errors,
            'csrf' => $this->getCsrfToken(),
        ]);
    }

    public function store(string $space, array $input): void
    {
        if (!$this->isValidCsrf($input['_csrf'] ?? null)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Jeton CSRF invalide.'];
            $this->redirectToList($space);
        }

        $result = CommandeValidator::validate($input);
        if ($result['errors'] !== []) {
            $this->create($space, $input, $result['errors']);
            return;
        }

        $commande = new Commande(
            null,
            $result['data']['statut'],
            $result['data']['prix_total'],
            $result['data']['date_creation'],
            $result['data']['date_livraison'],
            $result['data']['id_service'],
            $result['data']['id_client']
        );

        $this->repository->create($commande);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Commande ajoutee avec succes.'];

        $this->redirectToList($space);
    }

    public function edit(string $space, int $id, array $errors = []): void
    {
        $commande = $this->repository->findById($id);
        if ($commande === null) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Commande introuvable.'];
            $this->redirectToList($space);
        }

        $this->render($space, [
            'page' => 'form',
            'mode' => 'edit',
            'commande' => $commande,
            'formData' => [
                'statut' => $commande->getStatut(),
                'prix_total' => (string) $commande->getPrixTotal(),
                'date_creation' => $commande->getDateCreation(),
                'date_livraison' => $commande->getDateLivraison(),
                'id_service' => (string) $commande->getIdService(),
                'id_client' => (string) $commande->getIdClient(),
            ],
            'errors' => $errors,
            'csrf' => $this->getCsrfToken(),
        ]);
    }

    public function update(string $space, int $id, array $input): void
    {
        if (!$this->isValidCsrf($input['_csrf'] ?? null)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Jeton CSRF invalide.'];
            $this->redirectToList($space);
        }

        $existing = $this->repository->findById($id);
        if ($existing === null) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Commande introuvable.'];
            $this->redirectToList($space);
        }

        $result = CommandeValidator::validate($input);
        if ($result['errors'] !== []) {
            $commande = new Commande(
                $id,
                (string) ($input['statut'] ?? ''),
                (float) ($input['prix_total'] ?? 0),
                (string) ($input['date_creation'] ?? ''),
                (string) ($input['date_livraison'] ?? ''),
                (int) ($input['id_service'] ?? 0),
                (int) ($input['id_client'] ?? 0)
            );

            $this->render($space, [
                'page' => 'form',
                'mode' => 'edit',
                'commande' => $commande,
                'formData' => $input,
                'errors' => $result['errors'],
                'csrf' => $this->getCsrfToken(),
            ]);
            return;
        }

        $commande = new Commande(
            $id,
            $result['data']['statut'],
            $result['data']['prix_total'],
            $result['data']['date_creation'],
            $result['data']['date_livraison'],
            $result['data']['id_service'],
            $result['data']['id_client']
        );

        $this->repository->update($commande);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Commande modifiee avec succes.'];

        $this->redirectToList($space);
    }

    public function delete(string $space, int $id, array $input): void
    {
        if (!$this->isValidCsrf($input['_csrf'] ?? null)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Jeton CSRF invalide.'];
            $this->redirectToList($space);
        }

        $this->repository->delete($id);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Commande supprimee avec succes.'];

        $this->redirectToList($space);
    }

    private function render(string $space, array $data): void
    {
        extract($data, EXTR_SKIP);
        $template = __DIR__ . '/../views/' . ($space === 'back' ? 'backoffice' : 'frontoffice') . '/commande.html';
        require $template;
    }

    private function redirectToList(string $space): void
    {
        header('Location: index.php?space=' . $space . '&action=list');
        exit;
    }

    private function getCsrfToken(): string
    {
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(16));
        }

        return $_SESSION['_csrf'];
    }

    private function isValidCsrf(?string $token): bool
    {
        if ($token === null || !isset($_SESSION['_csrf'])) {
            return false;
        }

        return hash_equals($_SESSION['_csrf'], $token);
    }
}
