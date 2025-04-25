<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$month = isset($_GET['month']) ? intval($_GET['month']) : null;
$year = isset($_GET['year']) ? intval($_GET['year']) : null;

// Build query with filters
$where = "WHERE user_id = $user_id";
if ($month) $where .= " AND MONTH(created_at) = $month";
if ($year) $where .= " AND YEAR(created_at) = $year";

$query = "SELECT type, category, amount, description, created_at 
          FROM transactions $where ORDER BY created_at DESC";
$result = $conn->query($query);

// Set headers for download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="transactions_'.date('Y-m-d').'.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Write headers
fputcsv($output, ['Type', 'Category', 'Amount', 'Description', 'Date']);

// Write data
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['type'],
        $row['category'],
        $row['amount'],
        $row['description'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
?>