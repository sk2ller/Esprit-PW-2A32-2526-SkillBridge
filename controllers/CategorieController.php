<?php
require_once __DIR__ . '/../models/Categorie.php';

class CategorieController {
    private $categorieModel;

    public function __construct() {
        $this->categorieModel = new Categorie();
    }

    public function adminIndex() {
        $categories = $this->categorieModel->getAll();
        require_once __DIR__ . '/../views/BackOffice/admin/categories.php';
    }

    public function adminCreate() {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['nom_categorie'])) {
                $error = "Le nom est requis.";
            } else {
                $this->categorieModel->create([
                    'nom_categorie' => htmlspecialchars($_POST['nom_categorie']),
                    'description' => htmlspecialchars($_POST['description'] ?? ''),
                    'icone' => htmlspecialchars($_POST['icone'] ?? 'fas fa-folder')
                ]);
                header("Location: index.php?page=admin_categories&success=1"); exit;
            }
        }
        require_once __DIR__ . '/../views/BackOffice/admin/categorie_form.php';
    }

    public function adminEdit($id) {
        $categorie = $this->categorieModel->getById($id);
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->categorieModel->update($id, [
                'nom_categorie' => htmlspecialchars($_POST['nom_categorie']),
                'description' => htmlspecialchars($_POST['description'] ?? ''),
                'icone' => htmlspecialchars($_POST['icone'] ?? 'fas fa-folder')
            ]);
            header("Location: index.php?page=admin_categories&success=2"); exit;
        }
        require_once __DIR__ . '/../views/BackOffice/admin/categorie_form.php';
    }

    public function adminDelete($id) {
        $this->categorieModel->delete($id);
        header("Location: index.php?page=admin_categories&success=3"); exit;
    }
}
?>
