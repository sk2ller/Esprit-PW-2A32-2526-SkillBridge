<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/Categorie.php';

class CategorieController
{
    public function addCategorie(Categorie $categorie)
    {
        $db = Config::getConnexion();
        $stmt = $db->prepare("INSERT INTO categorie (nom_categorie, description, icone) VALUES (?, ?, ?)");
        return $stmt->execute([
            $categorie->getNom(),
            $categorie->getDescription(),
            $categorie->getIcone()
        ]);
    }

    public function listCategories()
    {
        $db = Config::getConnexion();
        $sql = "SELECT c.*, COUNT(s.id_service) AS nb_services
                FROM categorie c
                LEFT JOIN services s ON s.id_categorie = c.id_categorie AND s.statut = 'actif'
                GROUP BY c.id_categorie
                ORDER BY c.nom_categorie ASC";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategorieById($id)
    {
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM categorie WHERE id_categorie = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $categorie = new Categorie(
                $result['nom_categorie'],
                $result['description'],
                $result['icone']
            );
            $categorie->setId($result['id_categorie']);
            return $categorie;
        }

        return null;
    }

    public function updateCategorie(Categorie $categorie)
    {
        $db = Config::getConnexion();
        $stmt = $db->prepare("UPDATE categorie SET nom_categorie = ?, description = ?, icone = ? WHERE id_categorie = ?");
        return $stmt->execute([
            $categorie->getNom(),
            $categorie->getDescription(),
            $categorie->getIcone(),
            $categorie->getId()
        ]);
    }

    public function deleteCategorie($id)
    {
        $stmt = Config::getConnexion()->prepare("DELETE FROM categorie WHERE id_categorie = ?");
        return $stmt->execute([$id]);
    }

    public function adminIndex()
    {
        $categories = $this->listCategories();
        require __DIR__ . '/../Views/Backoffice/categoryList.php';
    }

    public function create()
    {
        if (!$this->isAdmin()) {
            header('Location: ?action=home');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom_categorie'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $icone = trim($_POST['icone'] ?? 'fas fa-folder');

            if ($nom !== '') {
                $categorie = new Categorie($nom, $description, $icone);
                $this->addCategorie($categorie);
                header('Location: ?action=categories_admin&success=1');
                exit;
            }
        }

        require __DIR__ . '/../Views/Backoffice/categoryForm.php';
    }

    public function edit($id)
    {
        if (!$this->isAdmin()) {
            header('Location: ?action=home');
            exit;
        }

        $db = Config::getConnexion();
        $categorieObject = $this->getCategorieById($id);
        $categorie = $categorieObject ? [
            'id_categorie' => $categorieObject->getId(),
            'nom_categorie' => $categorieObject->getNom(),
            'description' => $categorieObject->getDescription(),
            'icone' => $categorieObject->getIcone()
        ] : null;

        if (!$categorie) {
            header('Location: ?action=categories_admin');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom_categorie'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $icone = trim($_POST['icone'] ?? 'fas fa-folder');
            $updatedCategorie = new Categorie($nom, $description, $icone);
            $updatedCategorie->setId($id);
            $this->updateCategorie($updatedCategorie);
            header('Location: ?action=categories_admin&success=2');
            exit;
        }

        require __DIR__ . '/../Views/Backoffice/categoryForm.php';
    }

    public function delete($id)
    {
        if ($this->isAdmin()) {
            $this->deleteCategorie($id);
        }
        header('Location: ?action=categories_admin&success=3');
        exit;
    }

    private function isAdmin()
    {
        return isset($_SESSION['user_role']) && (int)$_SESSION['user_role'] === 1;
    }
}
?>
