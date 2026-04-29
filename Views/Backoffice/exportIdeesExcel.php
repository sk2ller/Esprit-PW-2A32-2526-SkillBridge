<?php
// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header('Location: ?action=home');
    exit;
}

require_once __DIR__ . '/../../Models/Idee.php';

$ideeModel = new Idee();

// Check if search parameter is provided
$searchQuery = trim($_GET['search'] ?? '');
$orderBy = $_GET['order_by'] ?? 'created_at';
$orderDirection = $_GET['order_dir'] ?? 'DESC';

if (!empty($searchQuery)) {
    $idees = $ideeModel->search($searchQuery, $orderBy, $orderDirection);
} else {
    $idees = $ideeModel->getAll($orderBy, $orderDirection);
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=idees_' . date('Y-m-d_H-i-s') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8 encoding (helps Excel display special characters correctly)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write CSV headers
fputcsv($output, [
    'ID',
    'Titre',
    'Contenu',
    'Catégorie',
    'Priorité',
    'Statut',
    'Votes',
    'Brainstorming',
    'Auteur (Prénom)',
    'Auteur (Nom)',
    'Date de création'
], ';');

// Status mapping
$statusLabels = [
    'proposee' => 'Proposée',
    'en_etude' => 'En étude',
    'approuvee' => 'Approuvée',
    'rejetee' => 'Rejetée'
];

$priorityLabels = [
    'faible' => 'Faible',
    'moyenne' => 'Moyenne',
    'haute' => 'Haute'
];

// Write data rows
foreach ($idees as $item) {
    fputcsv($output, [
        $item['id'],
        $item['titre'],
        $item['contenu'],
        $item['categorie'],
        $priorityLabels[$item['priorite']] ?? $item['priorite'],
        $statusLabels[$item['statut']] ?? $item['statut'],
        $item['votes'],
        $item['brainstorming_titre'],
        $item['user_prenom'] ?? '',
        $item['user_nom'] ?? '',
        $item['created_at']
    ], ';');
}

fclose($output);
exit;
?>