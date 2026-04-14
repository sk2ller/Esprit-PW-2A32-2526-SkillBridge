<?php
require_once __DIR__ . '/../models/Projet.php';

class ProjetController
{
    public function listAction()
    {
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        $projets = Projet::getAll($search);
        $stats = Projet::getDashboardStats();
        require __DIR__ . '/../views/liste_projets.php';
    }

    public function addAction()
    {
        $errors = array();
        $allowedStatuses = array('en_cours', 'termine', 'en_attente');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            $budget = isset($_POST['budget']) ? (float)$_POST['budget'] : 0;
            $dateCreation = isset($_POST['date_creation']) ? trim($_POST['date_creation']) : '';
            $statut = isset($_POST['statut']) ? trim($_POST['statut']) : 'en_cours';

            if ($titre === '') {
                $errors[] = 'Le titre est obligatoire.';
            }
            if ($description === '') {
                $errors[] = 'La description est obligatoire.';
            }
            if ($budget <= 0) {
                $errors[] = 'Le budget doit etre superieur a 0.';
            }
            if ($dateCreation === '') {
                $errors[] = 'La date de creation est obligatoire.';
            }
            if (!in_array($statut, $allowedStatuses, true)) {
                $errors[] = 'Le statut selectionne est invalide.';
            }

            if (count($errors) === 0) {
                Projet::create($titre, $description, $budget, $dateCreation, $statut);
                header('Location: index.php?action=list');
                exit;
            }
        }

        require __DIR__ . '/../views/ajouter_projet.php';
    }

    public function editAction()
    {
        $errors = array();
        $allowedStatuses = array('en_cours', 'termine', 'en_attente');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: index.php?action=list');
            exit;
        }

        $projet = Projet::getById($id);

        if (!$projet) {
            header('Location: index.php?action=list');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            $budget = isset($_POST['budget']) ? (float)$_POST['budget'] : 0;
            $dateCreation = isset($_POST['date_creation']) ? trim($_POST['date_creation']) : '';
            $statut = isset($_POST['statut']) ? trim($_POST['statut']) : 'en_cours';

            if ($titre === '') {
                $errors[] = 'Le titre est obligatoire.';
            }
            if ($description === '') {
                $errors[] = 'La description est obligatoire.';
            }
            if ($budget <= 0) {
                $errors[] = 'Le budget doit etre superieur a 0.';
            }
            if ($dateCreation === '') {
                $errors[] = 'La date de creation est obligatoire.';
            }
            if (!in_array($statut, $allowedStatuses, true)) {
                $errors[] = 'Le statut selectionne est invalide.';
            }

            if (count($errors) === 0) {
                Projet::update($id, $titre, $description, $budget, $dateCreation, $statut);
                header('Location: index.php?action=list');
                exit;
            }

            $projet['titre'] = $titre;
            $projet['description'] = $description;
            $projet['budget'] = $budget;
            $projet['date_creation'] = $dateCreation;
            $projet['statut'] = $statut;
        }

        require __DIR__ . '/../views/modifier_projet.php';
    }

    public function deleteAction()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            Projet::delete($id);
        }

        header('Location: index.php?action=list');
        exit;
    }
}
