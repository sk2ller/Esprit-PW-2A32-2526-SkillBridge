<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/Project.php';

class ProjectController
{
    private const VALID_STATUS = ['en_cours', 'termine', 'en_attente'];

    public function validateProjectData(array $input, $allowCustomStatus = false)
    {
        $titre = trim((string)($input['titre'] ?? ''));
        $description = trim((string)($input['description'] ?? ''));
        $budgetRaw = $input['budget'] ?? null;
        $dateCreation = trim((string)($input['date_creation'] ?? date('Y-m-d')));
        $statut = trim((string)($input['statut'] ?? 'en_attente'));

        if ($titre === '' || $description === '') {
            return ['valid' => false, 'message' => 'Titre et description obligatoires'];
        }

        if (!is_numeric($budgetRaw)) {
            return ['valid' => false, 'message' => 'Le budget doit etre numerique'];
        }

        $budget = (float)$budgetRaw;
        if ($budget < 0) {
            return ['valid' => false, 'message' => 'Le budget doit etre positif'];
        }

        if (!$this->isValidDate($dateCreation)) {
            return ['valid' => false, 'message' => 'Date de creation invalide'];
        }

        if (!$allowCustomStatus) {
            $statut = 'en_attente';
        } else {
            $statut = $this->normalizeStatus($statut);
        }

        return [
            'valid' => true,
            'data' => [
                'titre' => $titre,
                'description' => $description,
                'budget' => $budget,
                'date_creation' => $dateCreation,
                'statut' => $statut,
            ],
        ];
    }

    public function addProject(Project $project)
    {
        $sql = "INSERT INTO Projet (titre, description, budget, date_creation, statut)
                VALUES (:titre, :description, :budget, :date_creation, :statut)";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $project->getTitre(),
                'description' => $project->getDescription(),
                'budget' => $project->getBudget(),
                'date_creation' => $project->getDateCreation(),
                'statut' => $this->normalizeStatus($project->getStatut()),
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function listProjects($search = '')
    {
        $db = Config::getConnexion();
        $search = trim((string)$search);

        try {
            if ($search !== '') {
                $sql = "SELECT * FROM Projet
                        WHERE titre LIKE :search
                           OR description LIKE :search
                           OR statut LIKE :search
                        ORDER BY date_creation DESC, id DESC";
                $query = $db->prepare($sql);
                $query->execute(['search' => '%' . $search . '%']);
            } else {
                $sql = "SELECT * FROM Projet ORDER BY date_creation DESC, id DESC";
                $query = $db->prepare($sql);
                $query->execute();
            }

            $rows = $query->fetchAll();
            $projects = [];
            foreach ($rows as $row) {
                $project = new Project(
                    $row['titre'],
                    $row['description'],
                    $row['budget'],
                    $row['date_creation'],
                    $row['statut']
                );
                $project->setId($row['id']);
                $projects[] = $project;
            }
            return $projects;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function getProjectById($id)
    {
        $sql = "SELECT * FROM Projet WHERE id = :id";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => (int)$id]);
            $row = $query->fetch();

            if (!$row) {
                return null;
            }

            $project = new Project(
                $row['titre'],
                $row['description'],
                $row['budget'],
                $row['date_creation'],
                $row['statut']
            );
            $project->setId($row['id']);
            return $project;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return null;
        }
    }

    public function updateProject(Project $project)
    {
        $sql = "UPDATE Projet
                SET titre = :titre,
                    description = :description,
                    budget = :budget,
                    date_creation = :date_creation,
                    statut = :statut
                WHERE id = :id";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $project->getTitre(),
                'description' => $project->getDescription(),
                'budget' => $project->getBudget(),
                'date_creation' => $project->getDateCreation(),
                'statut' => $this->normalizeStatus($project->getStatut()),
                'id' => $project->getId(),
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function deleteProject($id)
    {
        $sql = "DELETE FROM Projet WHERE id = :id";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => (int)$id]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function getStats()
    {
        $db = Config::getConnexion();

        $stats = [
            'total' => 0,
            'budget_total' => 0.0,
            'status' => [
                'en_cours' => 0,
                'termine' => 0,
                'en_attente' => 0,
            ],
        ];

        try {
            $query = $db->query("SELECT COUNT(*) AS total, COALESCE(SUM(budget), 0) AS budget_total FROM Projet");
            $row = $query->fetch();
            if ($row) {
                $stats['total'] = (int)$row['total'];
                $stats['budget_total'] = (float)$row['budget_total'];
            }

            $query = $db->query("SELECT statut, COUNT(*) AS total FROM Projet GROUP BY statut");
            $rows = $query->fetchAll();
            foreach ($rows as $row) {
                $status = $row['statut'];
                if (array_key_exists($status, $stats['status'])) {
                    $stats['status'][$status] = (int)$row['total'];
                }
            }
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }

        return $stats;
    }

    public function renderProjectsPdf(array $projects)
    {
        $lines = [];
        $lines[] = 'SkillBridge - Export des projets freelance';
        $lines[] = 'Date export: ' . date('Y-m-d H:i');
        $lines[] = 'Total projets: ' . count($projects);
        $lines[] = str_repeat('-', 80);

        foreach ($projects as $project) {
            $lines[] = sprintf(
                '#%d | %s | Budget: %.2f TND | Date: %s | Statut: %s',
                $project->getId(),
                $project->getTitre(),
                $project->getBudget(),
                $project->getDateCreation(),
                $project->getStatut()
            );
            $lines[] = 'Description: ' . $project->getDescription();
            $lines[] = str_repeat('-', 80);
        }

        return $this->buildSimplePdf($lines);
    }

    private function normalizeStatus($status)
    {
        $status = (string)$status;
        return in_array($status, self::VALID_STATUS, true) ? $status : 'en_attente';
    }

    private function isValidDate($date)
    {
        $date = trim((string)$date);
        $parsed = DateTime::createFromFormat('Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    private function buildSimplePdf(array $lines)
    {
        $maxLines = 45;
        $lines = array_slice($lines, 0, $maxLines);

        $stream = "BT\n/F1 10 Tf\n50 800 Td\n";
        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $stream .= "0 -16 Td\n";
            }
            $stream .= '(' . $this->escapePdfText($line) . ") Tj\n";
        }
        $stream .= "ET";

        $objects = [];
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>";
        $objects[] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }

    private function escapePdfText($text)
    {
        $text = trim(strip_tags((string)$text));
        $text = preg_replace('/\s+/', ' ', $text);

        $encoded = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        if ($encoded !== false) {
            $text = $encoded;
        }

        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);

        return $text;
    }
}
?>