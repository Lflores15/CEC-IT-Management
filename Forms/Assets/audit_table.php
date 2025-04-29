<?php
session_start();
require_once "../../PHP/config.php";

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'CSV file upload error.']);
    exit;
}

$csvPath = $_FILES['csv_file']['tmp_name'];
$csvFile = fopen($csvPath, "r");
if (!$csvFile) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to open CSV file.']);
    exit;
}

// Parse CSV header
$header = fgetcsv($csvFile);
if ($header === false) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'CSV file is empty or invalid.']);
    exit;
}

$header = array_map('trim', $header);
if (isset($header[0])) {
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]); // Remove BOM
}
$empIndex = array_search("emp_code", $header);

if ($empIndex === false) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => "Missing 'emp_code' column in CSV."]);
    exit;
}

// Parse employee codes from CSV
$employeeIds = [];
while (($row = fgetcsv($csvFile)) !== false) {
    if (!isset($row[$empIndex])) continue;
    $empCode = trim($row[$empIndex]);
    if ($empCode !== '') {
        $employeeIds[] = $empCode;
    }
}
fclose($csvFile);

// Mark all existing employees inactive
$conn->query("UPDATE Employees SET active = 0");

// Activate matching employees
$activatedCount = 0;
if (!empty($employeeIds)) {
    $placeholders = implode(',', array_fill(0, count($employeeIds), '?'));
    $types = str_repeat('s', count($employeeIds));
    $stmt = $conn->prepare("UPDATE Employees SET active = 1 WHERE emp_code IN ($placeholders)");
    if ($stmt) {
        $stmt->bind_param($types, ...$employeeIds);
        if ($stmt->execute()) {
            $activatedCount = $stmt->affected_rows;
        }
        $stmt->close();
    }
}

// Return to JS
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Audit processed successfully.',
    'count' => count($employeeIds)
]);
exit;
?>
