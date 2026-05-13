<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Models/Project.php');

class ProjectController
{
    const STATUTS_VALIDES = ['en_cours', 'termine', 'en_attente'];

    // ── Helper ────────────────────────────────────────────────────────
    private function rowToProject($row)
    {
        $project = new Project(
            $row['titre'],
            $row['description'],
            $row['budget'],
            $row['date_creation'],
            $row['statut'],
            $row['etat'] ?? 'publie',
            $row['id_client'] ?? null
        );
        $project->setId($row['id']);
        if (isset($row['avancement'])) $project->setAvancement((int)$row['avancement']);
        return $project;
    }

    // ── VALIDATE ──────────────────────────────────────────────────────
    public function validateProjectInput(array $data, $isUpdate = false)
    {
        $errors = [];
        $titre        = trim($data['titre']         ?? '');
        $description  = trim($data['description']   ?? '');
        $budget       = trim((string)($data['budget'] ?? ''));
        $dateCreation = trim($data['date_creation'] ?? '');
        $statut       = trim($data['statut']        ?? 'en_attente');

        if ($isUpdate) {
            $id = (int)($data['id'] ?? 0);
            if ($id <= 0) $errors[] = 'Identifiant de projet invalide.';
        }

        if ($titre === '')              $errors[] = 'Le titre est obligatoire.';
        elseif (mb_strlen($titre) > 150) $errors[] = 'Le titre ne doit pas dépasser 150 caractères.';

        if ($description === '')               $errors[] = 'La description est obligatoire.';
        elseif (mb_strlen($description) > 2000) $errors[] = 'La description ne doit pas dépasser 2000 caractères.';

        if ($budget === '')          $errors[] = 'Le budget est obligatoire.';
        elseif (!is_numeric($budget)) $errors[] = 'Le budget doit être un nombre valide.';
        elseif ((float)$budget < 0)   $errors[] = 'Le budget ne peut pas être négatif.';

        if ($dateCreation === '') {
            $dateCreation = date('Y-m-d');
        } elseif (!$this->isValidDate($dateCreation)) {
            $errors[] = 'La date de création est invalide.';
        }

        if (!in_array($statut, self::STATUTS_VALIDES, true))
            $errors[] = 'Le statut sélectionné est invalide.';

        return [
            'is_valid' => empty($errors),
            'errors'   => $errors,
            'data'     => [
                'id'            => (int)($data['id'] ?? 0),
                'titre'         => $titre,
                'description'   => $description,
                'budget'        => round((float)$budget, 2),
                'date_creation' => $dateCreation,
                'statut'        => $statut,
            ],
        ];
    }

    // ── ADD ───────────────────────────────────────────────────────────
    public function addProject(Project $project)
    {
        $sql = "INSERT INTO projet (titre, description, budget, date_creation, statut, etat, id_client)
                VALUES (:titre, :description, :budget, :date_creation, :statut, :etat, :id_client)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre'         => $project->getTitre(),
                'description'   => $project->getDescription(),
                'budget'        => $project->getBudget(),
                'date_creation' => $project->getDateCreation(),
                'statut'        => $project->getStatut(),
                'etat'          => $project->getEtat(),
                'id_client'     => $project->getIdClient(),
            ]);
            return true;
        } catch (Exception $e) {
            error_log('Erreur addProject: ' . $e->getMessage());
            return false;
        }
    }

    // ── LIST (frontoffice — publiés uniquement) ───────────────────────
    public function listProjects($search = '', $statut = '', $budget_min = '', $budget_max = '')
    {
        $db = Config::getConnexion();
        try {
            $where  = ["etat = 'publie'"];
            $params = [];

            if ($search !== '') {
                $where[]        = "(titre LIKE :q OR description LIKE :q)";
                $params['q']    = '%' . $search . '%';
            }
            if ($statut !== '') {
                $where[]           = "statut = :statut";
                $params['statut']  = $statut;
            }
            if ($budget_min !== '' && is_numeric($budget_min)) {
                $where[]               = "budget >= :budget_min";
                $params['budget_min']  = (float)$budget_min;
            }
            if ($budget_max !== '' && is_numeric($budget_max)) {
                $where[]               = "budget <= :budget_max";
                $params['budget_max']  = (float)$budget_max;
            }

            $sql   = "SELECT * FROM projet WHERE " . implode(' AND ', $where) . " ORDER BY date_creation DESC, id DESC";
            $query = $db->prepare($sql);
            $query->execute($params);
            return array_map([$this, 'rowToProject'], $query->fetchAll());
        } catch (Exception $e) {
            error_log('Erreur listProjects: ' . $e->getMessage());
            return [];
        }
    }

    // ── LIST ALL (backoffice) ─────────────────────────────────────────
    public function listAllProjects($search = '', $statut = '', $etat = '', $budget_min = '', $budget_max = '')
    {
        $db = Config::getConnexion();
        try {
            $where  = ["1=1"];
            $params = [];

            if ($search !== '') {
                $where[]     = "(p.titre LIKE :q OR p.description LIKE :q)";
                $params['q'] = '%' . $search . '%';
            }
            if ($statut !== '') {
                $where[]          = "p.statut = :statut";
                $params['statut'] = $statut;
            }
            if ($etat !== '') {
                $where[]        = "p.etat = :etat";
                $params['etat'] = $etat;
            }
            if ($budget_min !== '' && is_numeric($budget_min)) {
                $where[]              = "p.budget >= :budget_min";
                $params['budget_min'] = (float)$budget_min;
            }
            if ($budget_max !== '' && is_numeric($budget_max)) {
                $where[]              = "p.budget <= :budget_max";
                $params['budget_max'] = (float)$budget_max;
            }

            $sql   = "SELECT p.*, u.nom AS nom_client, u.prenom AS prenom_client
                      FROM projet p
                      LEFT JOIN user u ON u.id = p.id_client
                      WHERE " . implode(' AND ', $where) . " ORDER BY p.date_creation DESC, p.id DESC";
            $query = $db->prepare($sql);
            $query->execute($params);
            $rows = $query->fetchAll();
            return array_map(function($row) {
                $p = $this->rowToProject($row);
                $p->setNomClient(($row['prenom_client'] ?? '') . ' ' . ($row['nom_client'] ?? ''));
                return $p;
            }, $rows);
        } catch (Exception $e) {
            error_log('Erreur listAllProjects: ' . $e->getMessage());
            return [];
        }
    }

    // ── LIST EN ATTENTE DE VALIDATION ─────────────────────────────────
    public function listPendingProjects()
    {
        $db = Config::getConnexion();
        try {
            $sql = "SELECT * FROM projet WHERE etat = 'en_attente_validation' ORDER BY id DESC";
            $query = $db->prepare($sql);
            $query->execute();
            return array_map([$this, 'rowToProject'], $query->fetchAll());
        } catch (Exception $e) {
            error_log('Erreur listPendingProjects: ' . $e->getMessage());
            return [];
        }
    }

    // ── CHANGER ETAT (accepter / refuser) ─────────────────────────────
    public function changerEtat($id, $etat)
    {
        $db = Config::getConnexion();
        try {
            $query = $db->prepare("UPDATE projet SET etat = :etat WHERE id = :id");
            $query->execute(['etat' => $etat, 'id' => $id]);
            return true;
        } catch (Exception $e) {
            error_log('Erreur changerEtat: ' . $e->getMessage());
            return false;
        }
    }

    // ── GET BY ID ─────────────────────────────────────────────────────
    public function getProjectById($id)
    {
        $db = Config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM projet WHERE id = :id");
            $query->execute(['id' => $id]);
            $row = $query->fetch();
            return $row ? $this->rowToProject($row) : null;
        } catch (Exception $e) {
            error_log('Erreur getProjectById: ' . $e->getMessage());
            return null;
        }
    }

    // ── UPDATE ────────────────────────────────────────────────────────
    public function updateProject(Project $project)
    {
        $sql = "UPDATE projet SET titre=:titre, description=:description, budget=:budget,
                date_creation=:date_creation, statut=:statut WHERE id=:id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre'         => $project->getTitre(),
                'description'   => $project->getDescription(),
                'budget'        => $project->getBudget(),
                'date_creation' => $project->getDateCreation(),
                'statut'        => $project->getStatut(),
                'id'            => $project->getId(),
            ]);
            return true;
        } catch (Exception $e) {
            error_log('Erreur updateProject: ' . $e->getMessage());
            return false;
        }
    }

    // ── DELETE ────────────────────────────────────────────────────────
    public function deleteProject($id)
    {
        $db = Config::getConnexion();
        try {
            $query = $db->prepare("DELETE FROM projet WHERE id = :id");
            $query->execute(['id' => $id]);
            return true;
        } catch (Exception $e) {
            error_log('Erreur deleteProject: ' . $e->getMessage());
            return false;
        }
    }

    // ── STATS ─────────────────────────────────────────────────────────
    public function getStats()
    {
        $db = Config::getConnexion();
        try {
            $query = $db->prepare("SELECT
                COUNT(*) AS total,
                COALESCE(SUM(budget), 0) AS budget_total,
                SUM(CASE WHEN statut='en_cours'  THEN 1 ELSE 0 END) AS en_cours,
                SUM(CASE WHEN statut='termine'   THEN 1 ELSE 0 END) AS termine,
                SUM(CASE WHEN statut='en_attente' THEN 1 ELSE 0 END) AS en_attente,
                SUM(CASE WHEN etat='en_attente_validation' THEN 1 ELSE 0 END) AS en_attente_validation
                FROM projet");
            $query->execute();
            $row = $query->fetch();
            return [
                'total'                  => (int)($row['total'] ?? 0),
                'budget_total'           => (float)($row['budget_total'] ?? 0),
                'en_cours'               => (int)($row['en_cours'] ?? 0),
                'termine'                => (int)($row['termine'] ?? 0),
                'en_attente'             => (int)($row['en_attente'] ?? 0),
                'en_attente_validation'  => (int)($row['en_attente_validation'] ?? 0),
            ];
        } catch (Exception $e) {
            error_log('Erreur getStats: ' . $e->getMessage());
            return ['total'=>0,'budget_total'=>0,'en_cours'=>0,'termine'=>0,'en_attente'=>0,'en_attente_validation'=>0];
        }
    }

    // ── EXPORT PDF ────────────────────────────────────────────────────
    public function exportProjectsPdf(array $projects)
    {
        $lines   = [];
        $lines[] = 'Export projets SkillBridge';
        $lines[] = 'Date: ' . date('d/m/Y H:i');
        $lines[] = 'Nombre de projets: ' . count($projects);
        $lines[] = str_repeat('-', 85);
        foreach ($projects as $project) {
            $lines[] = sprintf('#%d | %s | Budget: %.2f TND | Date: %s | Statut: %s',
                $project->getId(), $project->getTitre(), (float)$project->getBudget(),
                $project->getDateCreation(), $project->getStatut());
            $lines[] = 'Description: ' . $project->getDescription();
            $lines[] = str_repeat('-', 85);
        }
        $pdfContent = $this->buildSimplePdf($lines);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="projets_' . date('Ymd_His') . '.pdf"');
        header('Content-Length: ' . strlen($pdfContent));
        echo $pdfContent;
        exit;
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────────────
    private function isValidDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function toPdfText($text)
    {
        $value = str_replace(["\r","\n","\t"], ' ', (string)$text);
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $value);
            if ($converted !== false) $value = $converted;
        }
        return str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $value);
    }

    private function buildSimplePdf(array $lines)
    {
        $maxLinesPerPage = 44;
        $pages   = array_chunk($lines, $maxLinesPerPage);
        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $pageRefs = [];
        $nextObj  = 4;
        foreach ($pages as $pageLines) {
            $content = "BT\n/F1 10 Tf\n14 TL\n40 800 Td\n";
            foreach ($pageLines as $i => $line) {
                if ($i > 0) $content .= "T*\n";
                $content .= '(' . $this->toPdfText($line) . ") Tj\n";
            }
            $content .= "ET";
            $contentObj = $nextObj++;
            $pageObj    = $nextObj++;
            $objects[$contentObj] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
            $objects[$pageObj]    = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents " . $contentObj . " 0 R >>";
            $pageRefs[] = $pageObj . ' 0 R';
        }
        if (empty($pageRefs)) {
            $contentObj = $nextObj++;
            $pageObj    = $nextObj++;
            $content    = "BT\n/F1 10 Tf\n40 800 Td\n(Aucun projet a exporter.) Tj\nET";
            $objects[$contentObj] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
            $objects[$pageObj]    = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents " . $contentObj . " 0 R >>";
            $pageRefs[] = $pageObj . ' 0 R';
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $pageRefs) . '] /Count ' . count($pageRefs) . ' >>';
        ksort($objects);
        $pdf     = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= $num . " 0 obj\n" . $body . "\nendobj\n";
        }
        $xrefOffset = strlen($pdf);
        $maxObj     = max(array_keys($objects));
        $pdf .= 'xref' . "\n" . '0 ' . ($maxObj + 1) . "\n" . "0000000000 65535 f \n";
        for ($i = 1; $i <= $maxObj; $i++) {
            $pdf .= isset($offsets[$i]) ? sprintf("%010d 00000 n \n", $offsets[$i]) : "0000000000 65535 f \n";
        }
        $pdf .= 'trailer' . "\n" . '<< /Size ' . ($maxObj + 1) . ' /Root 1 0 R >>' . "\n" . 'startxref' . "\n" . $xrefOffset . "\n" . '%%EOF';
        return $pdf;
    }
}
