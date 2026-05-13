<?php
require_once __DIR__ . '/ServiceController.php';
require_once __DIR__ . '/CategorieController.php';

class SimplePdfBuilder
{
    private $objects = [];
    private $pages = [];
    private $currentContent = '';
    private $pageWidth = 595;
    private $pageHeight = 842;
    private $fontObjectId = null;

    public function __construct()
    {
        $this->fontObjectId = $this->addObject('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>');
    }

    public function beginPage()
    {
        $this->currentContent = '';
    }

    public function endPage()
    {
        $stream = "<< /Length " . strlen($this->currentContent) . " >>\nstream\n" . $this->currentContent . "\nendstream";
        $contentId = $this->addObject($stream);
        $this->pages[] = $contentId;
        $this->currentContent = '';
    }

    public function rect($x, $y, $w, $h, $style = 'S', $fillColor = null, $strokeColor = null, $lineWidth = 1)
    {
        if ($fillColor) {
            $this->currentContent .= $this->fillColor($fillColor);
        }
        if ($strokeColor) {
            $this->currentContent .= $this->strokeColor($strokeColor);
        }
        $this->currentContent .= sprintf("%.2f w %.2f %.2f %.2f %.2f re %s\n", $lineWidth, $x, $y, $w, $h, $style);
    }

    public function line($x1, $y1, $x2, $y2, $color = [0.85, 0.75, 0.63], $lineWidth = 1)
    {
        $this->currentContent .= $this->strokeColor($color);
        $this->currentContent .= sprintf("%.2f w %.2f %.2f m %.2f %.2f l S\n", $lineWidth, $x1, $y1, $x2, $y2);
    }

    public function text($x, $y, $text, $size = 12, $color = [0.12, 0.12, 0.14])
    {
        $escaped = $this->escape($text);
        $this->currentContent .= $this->fillColor($color);
        $this->currentContent .= "BT /F1 {$size} Tf 1 0 0 1 {$x} {$y} Tm ({$escaped}) Tj ET\n";
    }

    public function output($filename = 'report.pdf')
    {
        $pageIds = [];
        $pagesKids = '';

        foreach ($this->pages as $contentId) {
            $pageObject = "<< /Type /Page /Parent 0 0 R /MediaBox [0 0 {$this->pageWidth} {$this->pageHeight}] /Resources << /Font << /F1 {$this->fontObjectId} 0 R >> >> /Contents {$contentId} 0 R >>";
            $pageIds[] = $this->addObject($pageObject);
        }

        $pagesRootIndex = count($this->objects) + 1;

        foreach ($pageIds as $pageId) {
            $pagesKids .= $pageId . " 0 R ";
        }

        $this->objects[$pagesRootIndex - 1] = "<< /Type /Pages /Kids [ {$pagesKids}] /Count " . count($pageIds) . " >>";

        foreach ($pageIds as $pageId) {
            $this->objects[$pageId - 1] = str_replace('/Parent 0 0 R', '/Parent ' . $pagesRootIndex . ' 0 R', $this->objects[$pageId - 1]);
        }

        $catalogId = $this->addObject("<< /Type /Catalog /Pages {$pagesRootIndex} 0 R >>");

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($this->objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $objNumber = $index + 1;
            $pdf .= "{$objNumber} 0 obj\n{$object}\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 " . (count($this->objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i < count($offsets); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer << /Size " . (count($this->objects) + 1) . " /Root {$catalogId} 0 R >>\n";
        $pdf .= "startxref\n{$xrefPosition}\n%%EOF";

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
    }

    private function addObject($content)
    {
        $this->objects[] = $content;
        return count($this->objects);
    }

    private function fillColor($rgb)
    {
        return sprintf("%.3f %.3f %.3f rg\n", $rgb[0], $rgb[1], $rgb[2]);
    }

    private function strokeColor($rgb)
    {
        return sprintf("%.3f %.3f %.3f RG\n", $rgb[0], $rgb[1], $rgb[2]);
    }

    private function escape($text)
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\(', $text);
        $text = str_replace(')', '\)', $text);
        return preg_replace('/[^\x20-\x7E]/', '', $text);
    }
}

class AdminReportController
{
    private $serviceController;
    private $categorieController;

    public function __construct()
    {
        $this->serviceController = new ServiceController();
        $this->categorieController = new CategorieController();
    }

    public function exportPdf($type)
    {
        $report = $this->buildReport($type);

        if ($report === null) {
            http_response_code(404);
            echo '<h1>Rapport introuvable</h1>';
            return;
        }

        $this->renderPdfFile($report);
    }

    private function renderPdfFile($report)
    {
        $pdf = new SimplePdfBuilder();
        $pdf->beginPage();

        $accent = [0.878, 0.439, 0.125];
        $dark = [0.137, 0.137, 0.153];
        $muted = [0.49, 0.42, 0.32];
        $soft = [0.98, 0.945, 0.894];
        $border = [0.89, 0.82, 0.72];
        $green = [0.184, 0.49, 0.341];

        $pdf->rect(32, 735, 531, 82, 'f', $dark);
        $pdf->rect(32, 732, 531, 3, 'f', $accent);
        $pdf->text(48, 796, 'SKILLBRIDGE ADMIN', 11, [1, 1, 1]);
        $pdf->text(48, 767, $report['title'], 22, [1, 1, 1]);
        $pdf->text(48, 746, $report['subtitle'], 10, [0.92, 0.92, 0.92]);
        $pdf->text(450, 796, date('d/m/Y'), 10, [0.88, 0.88, 0.9]);
        $pdf->text(450, 780, date('H:i'), 10, [0.88, 0.88, 0.9]);

        $metaX = 42;
        foreach (array_slice($report['meta'], 0, 4) as $meta) {
            $pdf->rect($metaX, 660, 120, 56, 'B', $soft, $border);
            $pdf->text($metaX + 10, 698, strtoupper($meta['label']), 7, $muted);
            $value = is_string($meta['value']) ? $meta['value'] : (string) $meta['value'];
            $value = mb_strimwidth($value, 0, 22, '...');
            $pdf->text($metaX + 10, 674, $value, 15, $dark);
            $metaX += 128;
        }

        $pdf->text(42, 635, 'Synthese detaillee', 16, $dark);
        $pdf->line(42, 628, 553, 628, $border, 1);

        $headers = $report['headers'];
        $rows = $report['rows'];
        $colWidths = $this->columnWidths(count($headers));
        $x = 42;
        $y = 604;

        foreach ($headers as $index => $header) {
            $pdf->rect($x, $y, $colWidths[$index], 22, 'f', [0.98, 0.945, 0.894]);
            $pdf->text($x + 6, $y + 7, strtoupper($header), 7, $muted);
            $x += $colWidths[$index];
        }

        $y -= 28;
        $rowCount = 0;
        foreach ($rows as $row) {
            if ($y < 82 || $rowCount >= 14) {
                break;
            }

            $x = 42;
            $fill = ($rowCount % 2 === 0) ? [1, 1, 1] : [0.996, 0.98, 0.956];
            $pdf->rect(42, $y - 4, 511, 24, 'f', $fill);

            foreach ($row as $index => $cell) {
                $cellText = mb_strimwidth((string) $cell, 0, $this->cellLimit($colWidths[$index]), '...');
                $color = $index === 2 ? $green : $dark;
                $pdf->text($x + 6, $y + 5, $cellText, 9, $color);
                $x += $colWidths[$index];
            }

            $y -= 26;
            $rowCount++;
        }

        $pdf->rect(42, 48, 180, 26, 'B', $soft, $border);
        $pdf->text(50, 58, 'Rapport genere le ' . date('d/m/Y a H:i'), 8, $dark);
        $pdf->rect(232, 48, 321, 26, 'B', $soft, $border);
        $pdf->text(240, 58, 'Export PDF SkillBridge - document telecharge automatiquement.', 8, $dark);

        $pdf->endPage();
        $fileName = 'skillbridge-' . strtolower(preg_replace('/[^a-z0-9]+/i', '-', $report['title'])) . '.pdf';
        $pdf->output($fileName);
    }

    private function columnWidths($count)
    {
        if ($count === 5) {
            return [190, 88, 70, 65, 98];
        }

        if ($count === 4) {
            return [150, 215, 86, 60];
        }

        if ($count === 3) {
            return [240, 95, 176];
        }

        return array_fill(0, $count, floor(511 / max(1, $count)));
    }

    private function cellLimit($width)
    {
        if ($width >= 180) {
            return 34;
        }
        if ($width >= 140) {
            return 26;
        }
        if ($width >= 100) {
            return 18;
        }

        return 12;
    }

    private function buildReport($type)
    {
        if ($type === 'dashboard') {
            $stats = $this->serviceController->getStats();
            $insights = $this->serviceController->getAdminInsights();
            $categories = array_slice($this->serviceController->getCategoryPerformance(), 0, 8);

            $rows = [];
            foreach ($categories as $category) {
                $rows[] = [
                    $category['nom_categorie'],
                    (int) $category['total_services'],
                    (int) $category['actifs'],
                    (int) $category['en_attente'],
                    number_format((float) ($category['average_price'] ?? 0), 2) . ' DT'
                ];
            }

            return [
                'title' => 'Rapport Dashboard Admin',
                'subtitle' => 'Vue d ensemble de la marketplace et des performances services.',
                'meta' => [
                    ['label' => 'Services total', 'value' => $stats['total'] ?? 0],
                    ['label' => 'Services actifs', 'value' => $stats['actif'] ?? 0],
                    ['label' => 'Prix moyen', 'value' => number_format($insights['average_price'] ?? 0, 2) . ' DT'],
                    ['label' => 'Categorie phare', 'value' => $insights['top_category']]
                ],
                'headers' => ['Categorie', 'Total', 'Actifs', 'En attente', 'Prix moyen'],
                'rows' => $rows
            ];
        }

        if ($type === 'services') {
            $services = $this->serviceController->listAll($_GET['statut'] ?? null, null, $_GET['search'] ?? null, $_GET['sort'] ?? 'recent');
            $insights = $this->serviceController->getAdminInsights();

            $rows = [];
            foreach ($services as $service) {
                $rows[] = [
                    $service['titre'],
                    $service['nom_categorie'],
                    number_format((float) $service['prix'], 2) . ' DT',
                    (int) $service['delai_livraison'] . ' j',
                    $service['statut']
                ];
            }

            return [
                'title' => 'Rapport Services',
                'subtitle' => 'Export admin des services avec filtres et etat operationnel.',
                'meta' => [
                    ['label' => 'Lignes exportees', 'value' => count($services)],
                    ['label' => 'Avec miniature', 'value' => $insights['with_thumbnail'] ?? 0],
                    ['label' => 'Sans miniature', 'value' => $insights['without_thumbnail'] ?? 0],
                    ['label' => 'Freelancer principal', 'value' => $insights['top_freelancer']]
                ],
                'headers' => ['Service', 'Categorie', 'Prix', 'Delai', 'Statut'],
                'rows' => $rows
            ];
        }

        if ($type === 'categories') {
            $categories = $this->categorieController->listCategories();
            $insights = $this->categorieController->getCategoryInsights();

            $rows = [];
            foreach ($categories as $category) {
                $rows[] = [
                    $category['nom_categorie'],
                    $category['description'] ? mb_strimwidth($category['description'], 0, 65, '...') : '-',
                    (int) $category['nb_services'],
                    $category['icone'] ?? '-'
                ];
            }

            return [
                'title' => 'Rapport Categories',
                'subtitle' => 'Export admin des categories et de leur niveau d activite.',
                'meta' => [
                    ['label' => 'Categories total', 'value' => $insights['total'] ?? 0],
                    ['label' => 'Avec services', 'value' => $insights['with_services'] ?? 0],
                    ['label' => 'Sans service', 'value' => $insights['empty'] ?? 0],
                    ['label' => 'Plus utilisee', 'value' => $insights['most_used']]
                ],
                'headers' => ['Categorie', 'Description', 'Services actifs', 'Icone'],
                'rows' => $rows
            ];
        }

        return null;
    }
}
