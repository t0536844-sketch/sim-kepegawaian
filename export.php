<?php
// export.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get all data
$query = "SELECT * FROM pegawai";
$stmt = $db->query($query);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Data_Pegawai_RSUD_Mimika_' . date('Ymd') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add headers
$headers = array_keys($data[0]);
fputcsv($output, $headers);

// Add data
foreach ($data as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit();