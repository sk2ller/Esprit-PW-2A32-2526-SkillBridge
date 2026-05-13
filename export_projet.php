<?php
$files = [
    'Models/Project.php',
    'Controllers/ProjectController.php',
    'Views/Frontoffice/projects.php',
    'Views/Backoffice/projectList.php',
    'alter_projet.sql',
];

$zipName = 'tache_projet.zip';

$zip = new ZipArchive();
$zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

foreach ($files as $file) {
    if (file_exists($file)) {
        $zip->addFile($file, $file);
    }
}

$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($zipName));
readfile($zipName);
unlink($zipName);
exit;
