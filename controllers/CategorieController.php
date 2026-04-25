<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../models/Categorie.php');

class CategorieController
{
    public function addCategorie(Categorie $categorie)
    {
        $sql = "INSERT INTO categorie (nom_categorie, description, icone)
                VALUES (?, ?, ?)";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $nom = $categorie->getNom();
            $description = $categorie->getDescription();
            $icone = $categorie->getIcone();

            $query->bind_param("sss", $nom, $description, $icone);
            return $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function listCategories()
    {
        $sql = "SELECT c.*, COUNT(s.id_service) as nb_services
                FROM categorie c
                LEFT JOIN services s ON c.id_categorie = s.id_categorie AND s.statut = 'actif'
                GROUP BY c.id_categorie
                ORDER BY c.nom_categorie";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function getCategorieById($id)
    {
        $sql = "SELECT * FROM categorie WHERE id_categorie = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->bind_param("i", $id);
            $query->execute();

            $result = $query->get_result()->fetch_assoc();

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
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return null;
        }
    }

    public function updateCategorie(Categorie $categorie)
    {
        $sql = "UPDATE categorie
                SET nom_categorie = ?, description = ?, icone = ?
                WHERE id_categorie = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $id = $categorie->getId();
            $nom = $categorie->getNom();
            $description = $categorie->getDescription();
            $icone = $categorie->getIcone();

            $query->bind_param("sssi", $nom, $description, $icone, $id);
            return $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function deleteCategorie($id)
    {
        $sql = "DELETE FROM categorie WHERE id_categorie = ?";
        $db = getDB();

        try {
            $query = $db->prepare($sql);
            $query->bind_param("i", $id);
            return $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function adminIndex()
    {
        $categories = $this->listCategories();
        require_once(__DIR__ . '/../views/BackOffice/categories.php');
    }

    public function adminCreate()
    {
        $errors = [];
        $categorie = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation
            if (empty($_POST['nom_categorie'] ?? '')) {
                $errors['nom_categorie'] = 'Le nom de la catégorie est obligatoire.';
            } elseif (strlen($_POST['nom_categorie']) < 3) {
                $errors['nom_categorie'] = 'Le nom doit contenir au moins 3 caractères.';
            } elseif (strlen($_POST['nom_categorie']) > 100) {
                $errors['nom_categorie'] = 'Le nom ne doit pas dépasser 100 caractères.';
            }

            if (!empty($_POST['description'] ?? '') && strlen($_POST['description']) > 500) {
                $errors['description'] = 'La description ne doit pas dépasser 500 caractères.';
            }

            if (empty($errors)) {
                $categorie = new Categorie(
                    htmlspecialchars($_POST['nom_categorie']),
                    htmlspecialchars($_POST['description'] ?? ''),
                    htmlspecialchars($_POST['icone'] ?? 'fas fa-folder')
                );

                $this->addCategorie($categorie);
                header("Location: index.php?page=admin_categories&success=1");
                exit;
            } else {
                // Garder les données saisies en cas d'erreur
                $categorie = [
                    'nom_categorie' => htmlspecialchars($_POST['nom_categorie'] ?? ''),
                    'description' => htmlspecialchars($_POST['description'] ?? ''),
                    'icone' => htmlspecialchars($_POST['icone'] ?? 'fas fa-folder')
                ];
            }
        }

        require_once(__DIR__ . '/../views/BackOffice/categorie_form.php');
    }

    public function adminEdit($id)
    {
        $categorieObject = $this->getCategorieById($id);
        $errors = [];

        if (!$categorieObject) {
            header("Location: index.php?page=admin_categories");
            exit;
        }

        $categorie = [
            'id_categorie' => $categorieObject->getId(),
            'nom_categorie' => $categorieObject->getNom(),
            'description' => $categorieObject->getDescription(),
            'icone' => $categorieObject->getIcone()
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation
            if (empty($_POST['nom_categorie'] ?? '')) {
                $errors['nom_categorie'] = 'Le nom de la catégorie est obligatoire.';
            } elseif (strlen($_POST['nom_categorie']) < 3) {
                $errors['nom_categorie'] = 'Le nom doit contenir au moins 3 caractères.';
            } elseif (strlen($_POST['nom_categorie']) > 100) {
                $errors['nom_categorie'] = 'Le nom ne doit pas dépasser 100 caractères.';
            }

            if (!empty($_POST['description'] ?? '') && strlen($_POST['description']) > 500) {
                $errors['description'] = 'La description ne doit pas dépasser 500 caractères.';
            }

            if (empty($errors)) {
                $updatedCategorie = new Categorie(
                    htmlspecialchars($_POST['nom_categorie']),
                    htmlspecialchars($_POST['description'] ?? ''),
                    htmlspecialchars($_POST['icone'] ?? 'fas fa-folder')
                );

                $updatedCategorie->setId($id);
                $this->updateCategorie($updatedCategorie);

                header("Location: index.php?page=admin_categories&success=2");
                exit;
            } else {
                // Garder les données saisies en cas d'erreur
                $categorie = [
                    'id_categorie' => $id,
                    'nom_categorie' => htmlspecialchars($_POST['nom_categorie'] ?? ''),
                    'description' => htmlspecialchars($_POST['description'] ?? ''),
                    'icone' => htmlspecialchars($_POST['icone'] ?? 'fas fa-folder')
                ];
            }
        }

        require_once(__DIR__ . '/../views/BackOffice/categorie_form.php');
    }

    public function adminDelete($id)
    {
        $this->deleteCategorie($id);
        header("Location: index.php?page=admin_categories&success=3");
        exit;
    }
}
