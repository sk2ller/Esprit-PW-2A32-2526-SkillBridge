<?php

class PdfExporter
{
    private array $objects = [];
    private array $pages = [];
    private int $currentObjectId = 0;
    private float $pageWidth = 842.0;
    private float $pageHeight = 595.0;
    private string $fontRegular = '/F1';
    private string $fontBold = '/F2';

    public function renderReport(string $title, string $subtitle, array $meta, array $headers, array $rows, string $filename): void
    {
        $content = $this->buildContent($title, $subtitle, $meta, $headers, $rows);
        $pdf = $this->buildPdf($content);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
    }

    private function buildContent(string $title, string $subtitle, array $meta, array $headers, array $rows): string
    {
        $y = $this->pageHeight - 52;
        $content = '';

        $content .= $this->filledRect(32, $this->pageHeight - 84, $this->pageWidth - 64, 44, [224, 112, 32]);
        $content .= $this->text(46, $y, 24, $title, true, [255, 255, 255]);
        $content .= $this->text(46, $y - 18, 11, $subtitle, false, [255, 244, 232]);

        $metaY = $this->pageHeight - 112;
        foreach ($meta as $label => $value) {
            $content .= $this->filledRect(32, $metaY - 18, 180, 26, [250, 246, 240]);
            $content .= $this->strokedRect(32, $metaY - 18, 180, 26, [223, 209, 189]);
            $content .= $this->text(40, $metaY - 2, 9, strtoupper($label), true, [141, 90, 43]);
            $content .= $this->text(112, $metaY - 2, 10, (string) $value, false, [31, 31, 35]);
            $metaY -= 32;
        }

        $tableTop = $this->pageHeight - 238;
        $usableWidth = $this->pageWidth - 64;
        $columnWidth = $usableWidth / max(count($headers), 1);

        $content .= $this->filledRect(32, $tableTop, $usableWidth, 24, [31, 31, 32]);
        foreach ($headers as $index => $header) {
            $x = 38 + ($index * $columnWidth);
            $content .= $this->text($x, $tableTop + 8, 9, strtoupper((string) $header), true, [255, 255, 255]);
        }

        $rowY = $tableTop - 26;
        foreach ($rows as $rowIndex => $row) {
            if ($rowY < 58) {
                break;
            }

            $fill = $rowIndex % 2 === 0 ? [255, 253, 249] : [248, 241, 231];
            $content .= $this->filledRect(32, $rowY, $usableWidth, 24, $fill);
            $content .= $this->strokedRect(32, $rowY, $usableWidth, 24, [223, 209, 189]);

            foreach (array_values($row) as $colIndex => $cell) {
                $x = 38 + ($colIndex * $columnWidth);
                $content .= $this->text($x, $rowY + 8, 9, $this->truncate((string) $cell, 24), false, [31, 31, 35]);
            }

            $rowY -= 24;
        }

        $content .= $this->text(32, 26, 8, 'SkillBridge Admin Report', false, [139, 135, 145]);
        $content .= $this->text($this->pageWidth - 120, 26, 8, 'Genere le ' . date('d/m/Y H:i'), false, [139, 135, 145]);

        return $content;
    }

    private function buildPdf(string $pageContent): string
    {
        $this->objects = [];
        $this->pages = [];
        $this->currentObjectId = 0;

        $catalogId = $this->reserveObject();
        $pagesId = $this->reserveObject();
        $fontRegularId = $this->reserveObject();
        $fontBoldId = $this->reserveObject();
        $contentId = $this->reserveObject();
        $pageId = $this->reserveObject();

        $this->setObject($fontRegularId, "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>");
        $this->setObject($fontBoldId, "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>");
        $stream = "<< /Length " . strlen($pageContent) . " >>\nstream\n" . $pageContent . "\nendstream";
        $this->setObject($contentId, $stream);
        $this->setObject(
            $pageId,
            "<< /Type /Page /Parent {$pagesId} 0 R /MediaBox [0 0 {$this->pageWidth} {$this->pageHeight}] /Resources << /Font << {$this->fontRegular} {$fontRegularId} 0 R {$this->fontBold} {$fontBoldId} 0 R >> >> /Contents {$contentId} 0 R >>"
        );
        $this->setObject($pagesId, "<< /Type /Pages /Kids [{$pageId} 0 R] /Count 1 >>");
        $this->setObject($catalogId, "<< /Type /Catalog /Pages {$pagesId} 0 R >>");

        return $this->compilePdf();
    }

    private function reserveObject(): int
    {
        $this->currentObjectId++;
        $this->objects[$this->currentObjectId] = '';
        return $this->currentObjectId;
    }

    private function setObject(int $id, string $content): void
    {
        $this->objects[$id] = $content;
    }

    private function compilePdf(): string
    {
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($this->objects as $id => $content) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $content . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($this->objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($this->objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size " . (count($this->objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function text(float $x, float $y, int $size, string $text, bool $bold = false, array $rgb = [31, 31, 35]): string
    {
        $font = $bold ? $this->fontBold : $this->fontRegular;
        [$r, $g, $b] = $this->normalizeColor($rgb);
        $escaped = $this->escapeText($text);
        return "BT {$font} {$size} Tf {$r} {$g} {$b} rg 1 0 0 1 {$x} {$y} Tm ({$escaped}) Tj ET\n";
    }

    private function filledRect(float $x, float $y, float $w, float $h, array $rgb): string
    {
        [$r, $g, $b] = $this->normalizeColor($rgb);
        return "{$r} {$g} {$b} rg {$x} {$y} {$w} {$h} re f\n";
    }

    private function strokedRect(float $x, float $y, float $w, float $h, array $rgb): string
    {
        [$r, $g, $b] = $this->normalizeColor($rgb);
        return "{$r} {$g} {$b} RG {$x} {$y} {$w} {$h} re S\n";
    }

    private function normalizeColor(array $rgb): array
    {
        return [
            number_format(($rgb[0] ?? 0) / 255, 3, '.', ''),
            number_format(($rgb[1] ?? 0) / 255, 3, '.', ''),
            number_format(($rgb[2] ?? 0) / 255, 3, '.', '')
        ];
    }

    private function escapeText(string $text): string
    {
        $text = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text) ?: $text;
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        return preg_replace('/[^\x20-\x7E\x80-\xFF]/', ' ', $text) ?? '';
    }

    private function truncate(string $text, int $length): string
    {
        $text = trim($text);
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length - 1) . '…';
    }
}
