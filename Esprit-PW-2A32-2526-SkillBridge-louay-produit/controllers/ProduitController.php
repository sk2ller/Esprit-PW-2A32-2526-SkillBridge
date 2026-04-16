<?php
require_once __DIR__ . '/../models/Produit.php';
require_once __DIR__ . '/../models/CategorieProduit.php';

class ProduitController {
    private $produitModel;
    private $categorieModel;

    public function __construct() {
        $this->produitModel = new Produit();
        $this->categorieModel = new CategorieProduit();
    }

    // FrontOffice : liste des produits disponibles
    public function index() {
        $search = $_GET['search'] ?? null;
        $id_categorie = $_GET['categorie'] ?? null;
        $produits = $this->produitModel->getAll('disponible', $id_categorie, $search);
        $categories = $this->categorieModel->getAll();
        require_once __DIR__ . '/../views/FrontOffice/client/produits_list.php';
    }

    // FrontOffice : détail d'un produit
    public function show($id) {
        $produit = $this->produitModel->getById($id);
        if (!$produit) { header("Location: index.php?page=produits"); exit; }
        require_once __DIR__ . '/../views/FrontOffice/client/produit_detail.php';
    }

    // Vendeur : mes produits
    public function mesProduits() {
        $produits = $this->produitModel->getAllForVendeur();
        $categories = $this->categorieModel->getAll();
        require_once __DIR__ . '/../views/FrontOffice/vendeur/mes_produits.php';
    }

    // Vendeur : formulaire création
    public function create() {
        $categories = $this->categorieModel->getAll();
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['nom']) || empty($_POST['description']) || empty($_POST['prix'])) {
                $error = "Tous les champs obligatoires doivent être remplis.";
            } else {
                $data = [
                    'nom' => htmlspecialchars($_POST['nom']),
                    'description' => htmlspecialchars($_POST['description']),
                    'prix' => (float)$_POST['prix'],
                    'quantite' => (int)$_POST['quantite'],
                    'id_categorie' => (int)$_POST['id_categorie']
                ];
                $id = $this->produitModel->create($data);
                header("Location: index.php?page=mes_produits&success=1"); exit;
            }
        }
        require_once __DIR__ . '/../views/FrontOffice/vendeur/produit_form.php';
    }

    // Vendeur : modifier un produit
    public function edit($id) {
        $produit = $this->produitModel->getById($id);
        $categories = $this->categorieModel->getAll();
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => htmlspecialchars($_POST['nom']),
                'description' => htmlspecialchars($_POST['description']),
                'prix' => (float)$_POST['prix'],
                'quantite' => (int)$_POST['quantite'],
                'id_categorie' => (int)$_POST['id_categorie']
            ];
            $this->produitModel->update($id, $data);
            header("Location: index.php?page=mes_produits&success=2"); exit;
        }
        require_once __DIR__ . '/../views/FrontOffice/vendeur/produit_form.php';
    }

    // Vendeur : supprimer
    public function delete($id) {
        $this->produitModel->delete($id);
        header("Location: index.php?page=mes_produits&success=3"); exit;
    }

    // Admin : liste tous les produits
    public function adminIndex() {
        $produits = $this->produitModel->getAll();
        $stats = $this->produitModel->getStats();
        require_once __DIR__ . '/../views/BackOffice/admin/produits.php';
    }

    // Admin : approuver / suspendre
    public function adminUpdateStatut($id, $statut) {
        $this->produitModel->updateStatut($id, $statut);
        header("Location: index.php?page=admin_produits&success=1"); exit;
    }
}
?>
