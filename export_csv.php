<?php
require_once 'config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    exit('Access Denied');
}

$type = $_GET['type'] ?? '';
$filename = $type . "_report_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

if ($type == 'energy') {
    fputcsv($output, ['Date', 'Electricity (kWh)', 'Water (L)', 'Added By', 'Date Logged']);
    $stmt = $pdo->query("SELECT e.*, u.name as added_by FROM energy_usage e JOIN users u ON e.created_by = u.id ORDER BY created_at DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [$row['date'], $row['electricity_units'], $row['water_usage'], $row['added_by'], $row['created_at']]);
    }
} elseif ($type == 'waste') {
    fputcsv($output, ['Date', 'Dry Waste (kg)', 'Wet Waste (kg)', 'Added By']);
    $stmt = $pdo->query("SELECT w.*, u.name as added_by FROM waste w JOIN users u ON w.created_by = u.id ORDER BY date DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [$row['date'], $row['dry_waste'], $row['wet_waste'], $row['added_by']]);
    }
} elseif ($type == 'plantation') {
    fputcsv($output, ['Tree Name', 'Location', 'Date Planted', 'Added By']);
    $stmt = $pdo->query("SELECT p.*, u.name as added_by FROM plantation p JOIN users u ON p.created_by = u.id ORDER BY date_planted DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [$row['tree_name'], $row['location'], $row['date_planted'], $row['added_by']]);
    }
}

fclose($output);
exit;
?>
