<?php
require_once __DIR__ . '/../models/Offre.php';

class OffreController {
    private $offreModel;

    public function __construct() {
        $this->offreModel = new Offre();
    }

    // FrontOffice: liste les offres actives pour les freelancers
    public function index() {
        $search = $_GET['search'] ?? null;
        $offres = $this->offreModel->getAll('actif', $search);
        require_once __DIR__ . '/../views/FrontOffice/freelancer/offres_list.php';
    }

    // FrontOffice: détail d'une offre
    public function show($id) {
        $offre = $this->offreModel->getById($id);
        if (!$offre) { 
            header("Location: index.php?page=offres");
            exit;
        }
        require_once __DIR__ . '/../views/FrontOffice/freelancer/offre_detail.php';
    }

    // Client: mes offres publiées
    public function myOffres() {
        // In a real app, get from session: $_SESSION['id_client']
        $id_client = $_GET['id_client'] ?? 1;
        $offres = $this->offreModel->getByClient($id_client);
        $stats = $this->offreModel->getClientStats($id_client);
        require_once __DIR__ . '/../views/FrontOffice/client/mes_offres.php';
    }

    // Client: formulaire de création d'offre
    public function create() {
        $error = null;
        $id_client = $_GET['id_client'] ?? 1; // In production, get from session

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Server-side validation
            if (empty($_POST['titre']) || empty($_POST['description']) || empty($_POST['budget'])) {
                $error = "Tous les champs obligatoires doivent être remplis.";
            } else if ($_POST['budget'] <= 0) {
                $error = "Le budget doit être supérieur à 0.";
            } else if (strlen($_POST['titre']) < 5 || strlen($_POST['titre']) > 200) {
                $error = "Le titre doit contenir entre 5 et 200 caractères.";
            } else if (strlen($_POST['description']) < 20) {
                $error = "La description doit contenir au moins 20 caractères.";
            } else {
                $data = [
                    'titre' => htmlspecialchars($_POST['titre']),
                    'description' => htmlspecialchars($_POST['description']),
                    'budget' => (float)$_POST['budget'],
                    'delai_publication' => (int)($_POST['delai_publication'] ?? 30),
                    'niveau_requis' => htmlspecialchars($_POST['niveau_requis'] ?? 'intermediaire'),
                    'competences_requises' => htmlspecialchars($_POST['competences_requises'] ?? ''),
                    'id_client' => $id_client
                ];
                $id = $this->offreModel->create($data);
                header("Location: index.php?page=mes_offres&id_client=$id_client&success=1");
                exit;
            }
        }
        require_once __DIR__ . '/../views/FrontOffice/client/offre_form.php';
    }

    // Client: modifier une offre
    public function edit($id) {
        $offre = $this->offreModel->getById($id);
        if (!$offre) {
            header("Location: index.php?page=mes_offres");
            exit;
        }

        $id_client = $offre['id_client'];
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Server-side validation
            if (empty($_POST['titre']) || empty($_POST['description']) || empty($_POST['budget'])) {
                $error = "Tous les champs obligatoires doivent être remplis.";
            } else if ($_POST['budget'] <= 0) {
                $error = "Le budget doit être supérieur à 0.";
            } else {
                $data = [
                    'titre' => htmlspecialchars($_POST['titre']),
                    'description' => htmlspecialchars($_POST['description']),
                    'budget' => (float)$_POST['budget'],
                    'delai_publication' => (int)($_POST['delai_publication'] ?? 30),
                    'niveau_requis' => htmlspecialchars($_POST['niveau_requis'] ?? 'intermediaire'),
                    'competences_requises' => htmlspecialchars($_POST['competences_requises'] ?? '')
                ];
                $this->offreModel->update($id, $data);
                header("Location: index.php?page=mes_offres&id_client=$id_client&success=2");
                exit;
            }
        }
        require_once __DIR__ . '/../views/FrontOffice/client/offre_form.php';
    }

    // Client: supprimer une offre
    public function delete($id) {
        $offre = $this->offreModel->getById($id);
        if (!$offre) {
            header("Location: index.php?page=mes_offres");
            exit;
        }
        $id_client = $offre['id_client'];
        $this->offreModel->delete($id);
        header("Location: index.php?page=mes_offres&id_client=$id_client&success=3");
        exit;
    }

    // Admin: liste toutes les offres
    public function adminIndex() {
        $filter = $_GET['filter'] ?? 'all';
        $search = $_GET['search'] ?? null;
        
        if ($filter === 'all') {
            $offres = $this->offreModel->getAll(null, $search);
        } else {
            $offres = $this->offreModel->getAll($filter, $search);
        }
        
        $stats = $this->offreModel->getStats();
        require_once __DIR__ . '/../views/BackOffice/admin/offres.php';
    }

    // Admin: changer le statut d'une offre
    public function adminUpdateStatut($id, $statut) {
        $statuts_valides = ['actif', 'suspendu', 'en_attente'];
        if (!in_array($statut, $statuts_valides)) {
            header("Location: index.php?page=admin_offres&error=1");
            exit;
        }
        $this->offreModel->updateStatut($id, $statut);
        header("Location: index.php?page=admin_offres&success=1");
        exit;
    }
}
?>
