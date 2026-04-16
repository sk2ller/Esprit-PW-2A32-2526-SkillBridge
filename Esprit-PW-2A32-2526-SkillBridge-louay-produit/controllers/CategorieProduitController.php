<?php
require_once __DIR__ . '/../models/CategorieProduit.php';

class CategorieProduitController {
    private $categorieModel;

    public function __construct() {
        $this->categorieModel = new CategorieProduit();
    }

    // Admin : liste des catégories
    public function adminIndex() {
        $categories = $this->categorieModel->getAll();
        require_once __DIR__ . '/../views/BackOffice/admin/categories.php';
    }

    // Admin : créer une catégorie
    public function adminCreate() {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['nom_categorie'])) {
                $error = "Le nom de la catégorie est requis.";
            } else {
                $data = [
                    'nom_categorie' => htmlspecialchars($_POST['nom_categorie']),
                    'description' => htmlspecialchars($_POST['description'] ?? ''),
                    'icone' => htmlspecialchars($_POST['icone'] ?? 'fas fa-folder')
                ];
                $this->categorieModel->create($data);
                header("Location: index.php?page=admin_categories&success=1"); exit;
            }
        }
        require_once __DIR__ . '/../views/BackOffice/admin/categorie_form.php';
    }

    // Admin : modifier une catégorie
    public function adminEdit($id) {
        $categorie = $this->categorieModel->getById($id);
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom_categorie' => htmlspecialchars($_POST['nom_categorie']),
                'description' => htmlspecialchars($_POST['description'] ?? ''),
                'icone' => htmlspecialchars($_POST['icone'] ?? 'fas fa-folder')
            ];
            $this->categorieModel->update($id, $data);
            header("Location: index.php?page=admin_categories&success=2"); exit;
        }
        require_once __DIR__ . '/../views/BackOffice/admin/categorie_form.php';
    }

    // Admin : supprimer une catégorie
    public function adminDelete($id) {
        $this->categorieModel->delete($id);
        header("Location: index.php?page=admin_categories&success=3"); exit;
    }
}
?>
