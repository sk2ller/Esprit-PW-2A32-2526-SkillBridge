<?php
// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header('Location: ?action=home');
    exit;
}

require_once __DIR__ . '/../../Controllers/BrainstormingController.php';

$brainstormController = new BrainstormingController();
$brainstormings = $brainstormController->listAll();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=brainstormings_' . date('Y-m-d_H-i-s') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8 encoding (helps Excel display special characters correctly)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write CSV headers
fputcsv($output, [
    'ID',
    'Titre',
    'Description',
    'Date de début',
    'Statut',
    'Auteur (Prénom)',
    'Auteur (Nom)',
    'Email Auteur',
    'Date de création'
], ';');

// Write data rows
foreach ($brainstormings as $item) {
    $status = $item['accepted'] == 1 ? 'Accepté' : 'En attente';

    fputcsv($output, [
        $item['id'],
        $item['titre'],
        $item['description'],
        $item['date_debut'],
        $status,
        $item['user_prenom'] ?? '',
        $item['user_nom'] ?? '',
        $item['user_email'] ?? '',
        $item['created_at']
    ], ';');
}

fclose($output);
exit;
?>