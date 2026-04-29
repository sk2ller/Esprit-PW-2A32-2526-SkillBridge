<?php

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
