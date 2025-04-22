<?php
session_start();
require_once "../../PHP/config.php";

// Ensure no output before header
if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'CSV file upload error.']);
    exit;
}

$csvPath = $_FILES['csv_file']['tmp_name'];
$csvFile = fopen($csvPath, "r");

if (!$csvFile) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Failed to open CSV file.']);
    exit;
}

// Read the header row
$header = fgetcsv($csvFile);
if ($header === false) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => "CSV file is empty or invalid."]);
    exit;
}
// Remove UTF-8 BOM if present from the first header element
if (isset($header[0])) {
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
}
$header = array_map('trim', $header);
// Logging/debugging: log the parsed headers
error_log("CSV Header: " . implode(", ", $header));
$empIndex = array_search("Employee #", $header);
if ($empIndex === false) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => "Missing 'Employee #' column in CSV."]);
    exit;
}

$activeIDs = [];
while (($row = fgetcsv($csvFile)) !== false) {
    $empId = trim($row[$empIndex]);
    if ($empId !== "") {
        $activeIDs[] = $empId;
    }
}
fclose($csvFile);

$_SESSION['active_employee_ids'] = $activeIDs;

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'message' => 'Audit processed successfully.',
    'count' => count($activeIDs)
]);
exit;