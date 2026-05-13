<?php
class ExcelExporter
{
    public static function exportUsersToPDF($users, $filename = 'users.pdf')
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $lines = [];
        $lines[] = 'SkillBridge - Liste des utilisateurs';
        $lines[] = 'Date: ' . date('Y-m-d H:i');
        $lines[] = str_repeat('-', 118);
        $lines[] = sprintf(
            '%-4s %-16s %-16s %-28s %-12s %-12s %-12s %-8s',
            'ID',
            'Nom',
            'Prenom',
            'Email',
            'Role',
            'Niveau',
            'Dispo',
            'Appr.'
        );
        $lines[] = str_repeat('-', 118);

        foreach ($users as $user) {
            $role = '';
            switch ($user->getIdRole()) {
                case 1:
                    $role = 'Admin';
                    break;
                case 2:
                    $role = 'Client';
                    break;
                case 3:
                    $role = 'Freelancer';
                    break;
            }

            $lines[] = sprintf(
                '%-4s %-16s %-16s %-28s %-12s %-12s %-12s %-8s',
                self::limitText((string) $user->getIdUser(), 4),
                self::limitText($user->getNom(), 16),
                self::limitText($user->getPrenom(), 16),
                self::limitText($user->getEmail(), 28),
                self::limitText($role, 12),
                self::limitText(ucfirst($user->getNiveau()), 12),
                self::limitText(ucfirst($user->getAvailability()), 12),
                $user->getIsApproved() ? 'Oui' : 'Non'
            );
        }

        echo self::buildPdfFromLines($lines);
        exit;
    }

    private static function limitText($value, $maxLength)
    {
        $value = trim((string) $value);
        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return mb_substr($value, 0, $maxLength - 3) . '...';
    }

    private static function buildPdfFromLines(array $lines)
    {
        $linesPerPage = 42;
        $pages = array_chunk($lines, $linesPerPage);
        $objects = [];

        $fontObjectId = 1;
        $objects[$fontObjectId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>";

        $pageObjectIds = [];
        $contentObjectIds = [];
        $nextObjectId = 2;

        foreach ($pages as $pageLines) {
            $content = "BT\n/F1 10 Tf\n40 800 Td\n12 TL\n";
            foreach ($pageLines as $index => $line) {
                if ($index > 0) {
                    $content .= "T*\n";
                }
                $content .= '(' . self::escapePdfText($line) . ") Tj\n";
            }
            $content .= "ET";

            $contentObjectId = $nextObjectId++;
            $contentObjectIds[] = $contentObjectId;
            $objects[$contentObjectId] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";

            $pageObjectId = $nextObjectId++;
            $pageObjectIds[] = $pageObjectId;
            $objects[$pageObjectId] = '__PAGE__';
        }

        $pagesObjectId = $nextObjectId++;
        $kids = implode(' ', array_map(function ($id) {
            return $id . ' 0 R';
        }, $pageObjectIds));
        $objects[$pagesObjectId] = "<< /Type /Pages /Kids [ " . $kids . " ] /Count " . count($pageObjectIds) . " >>";

        foreach ($pageObjectIds as $index => $pageObjectId) {
            $objects[$pageObjectId] = "<< /Type /Page /Parent " . $pagesObjectId . " 0 R /MediaBox [0 0 612 842] /Resources << /Font << /F1 " . $fontObjectId . " 0 R >> >> /Contents " . $contentObjectIds[$index] . " 0 R >>";
        }

        $catalogObjectId = $nextObjectId++;
        $objects[$catalogObjectId] = "<< /Type /Catalog /Pages " . $pagesObjectId . " 0 R >>";

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $object . "\nendobj\n";
        }

        $maxObjectId = max(array_keys($objects));
        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . ($maxObjectId + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $maxObjectId; $i++) {
            $offset = $offsets[$i] ?? 0;
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size " . ($maxObjectId + 1) . " /Root " . $catalogObjectId . " 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }

    private static function escapePdfText($text)
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', (string) $text);
        if ($encoded === false) {
            $encoded = (string) $text;
        }

        return str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\\(', '\\)', '', ''],
            $encoded
        );
    }
}
