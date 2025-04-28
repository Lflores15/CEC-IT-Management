<?php
session_start();
require_once "../../PHP/config.php";

// Ensure Employees table has 'active' column
$conn->query("ALTER TABLE Employees ADD COLUMN IF NOT EXISTS active TINYINT(1) DEFAULT 1");

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
// Support either "Employee #" or "Login ID"
$empIndex = array_search("Employee #", $header);
$loginIndex = array_search("Login ID", $header);

if ($empIndex === false && $loginIndex === false) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => "Missing 'Employee #' or 'Login ID' column in CSV."]);
    exit;
}

// Extract employee IDs and/or name pairs from CSV
$employeeIds = [];
$namePairs = [];
while (($row = fgetcsv($csvFile)) !== false) {
    if ($empIndex !== false) {
        $empId = trim(preg_replace('/\s+/', '', $row[$empIndex]));
        if ($empId !== "") $employeeIds[] = $empId;
    }
    // Assume these columns exist based on header
    $firstIndex = array_search("First Name", $header);
    $lastIndex = array_search("Last Name", $header);
    if ($firstIndex !== false && $lastIndex !== false) {
        $firstName = strtolower(trim($row[$firstIndex]));
        $lastName = strtolower(trim($row[$lastIndex]));
        if ($firstName && $lastName) {
            $namePairs[] = ['first_name' => $firstName, 'last_name' => $lastName];
        }
    }
}
fclose($csvFile);

// Insert any new active employees from the CSV into the Employees table if not already present
$csvFile = fopen($csvPath, "r");
$header = fgetcsv($csvFile); // skip header again
$firstIndex = array_search("First Name", $header);
$lastIndex = array_search("Last Name", $header);
$empIndex = array_search("Employee #", $header);

while (($row = fgetcsv($csvFile)) !== false) {
    $empId = isset($row[$empIndex]) ? trim($row[$empIndex]) : null;
    $firstName = isset($row[$firstIndex]) ? trim($row[$firstIndex]) : null;
    $lastName = isset($row[$lastIndex]) ? trim($row[$lastIndex]) : null;

    // Detailed debug logging for each row
    error_log("Processing row for possible insert: " . json_encode($row));
    error_log("Parsed Values - ID: '" . $empId . "', First: '" . $firstName . "', Last: '" . $lastName . "'");

    if (!$empId || !$firstName || !$lastName) {
        error_log("Skipping row due to missing field(s): empId='$empId', firstName='$firstName', lastName='$lastName'");
        continue;
    }

    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM Employees WHERE employee_id = ?");
    if (!$check) {
        error_log("Prepare failed for select: " . $conn->error);
        continue;
    }
    $check->bind_param("s", $empId);
    if (!$check->execute()) {
        error_log("Execute failed for select: " . $check->error);
        $check->close();
        continue;
    }
    $result = $check->get_result()->fetch_assoc();
    $check->close();

    if ((int)$result['cnt'] === 0) {
        error_log("Trying to insert new employee:");
        error_log("Raw Values - ID: '$empId', First: '$firstName', Last: '$lastName'");

        // Check again for empty values
        if (empty($empId) || empty($firstName) || empty($lastName)) {
            error_log("Skipping insert due to empty field(s)");
            continue;
        }

        $insert = $conn->prepare("INSERT INTO Employees (employee_id, first_name, last_name, active) VALUES (?, ?, ?, 1)");
        if ($insert) {
            $insert->bind_param("sss", $empId, $firstName, $lastName);
            if (!$insert->execute()) {
                error_log("Insert failed for employee_id '$empId': " . $insert->error);
            } else {
                error_log("Insert succeeded for '$empId'");
            }
            $insert->close();
        } else {
            error_log("Prepare failed for insert: " . $conn->error);
        }
    }
}
fclose($csvFile);

// Update Employees.active column for all employees
$conn->query("UPDATE Employees SET active = 0");

$activated = [];

if (!empty($employeeIds)) {
    $placeholders = implode(',', array_fill(0, count($employeeIds), '?'));
    $types = str_repeat('s', count($employeeIds));
    $stmt = $conn->prepare("UPDATE Employees SET active = 1 WHERE REPLACE(employee_id, ' ', '') IN ($placeholders)");
    $stmt->bind_param($types, ...$employeeIds);
    $stmt->execute();
    $stmt->close();
    $activated = $employeeIds;
}

foreach ($namePairs as $pair) {
    $stmt = $conn->prepare("UPDATE Employees SET active = 1 WHERE LOWER(first_name) = ? AND LOWER(last_name) = ?");
    $stmt->bind_param("ss", $pair['first_name'], $pair['last_name']);
    $stmt->execute();
    $stmt->close();
}

$_SESSION['active_employee_ids'] = array_merge($employeeIds, array_map(fn($p) => $p['first_name'] . ' ' . $p['last_name'], $namePairs));

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'message' => 'Audit processed successfully.',
    'count' => count($employeeIds) + count($namePairs)
]);
exit;