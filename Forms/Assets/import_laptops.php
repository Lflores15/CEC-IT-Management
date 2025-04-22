<?php
require_once "../../PHP/config.php";
session_start();

function logDeviceImport($message) {
    $user = $_SESSION["username"] ?? ($_SESSION["user_id"] ?? "unknown");
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents("../../Logs/device_event_log.txt", "[$timestamp] [IMPORT] [$user] $message\n", FILE_APPEND);
}

if (!isset($_FILES["csv_file"]) || $_FILES["csv_file"]["error"] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "Invalid upload"]);
    exit;
}

$expectedHeaders = ['status', 'internet_policy', 'asset_tag', 'login_id', 'first_name', 'last_name', 'user_id', 'phone_number', 'cpu', 'ram', 'os'];

$file = fopen($_FILES["csv_file"]["tmp_name"], "r");
$header = fgetcsv($file);

$normalizedExpected = array_map('strtolower', $expectedHeaders);
$normalized = array_map(function($h) {
    return strtolower(trim($h));
}, $header);
$normalized = array_slice($normalized, 0, count($expectedHeaders));

if (implode(',', $normalized) !== implode(',', $normalizedExpected)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid CSV headers. Expected: " . implode(", ", $expectedHeaders) . "<br>Received: " . implode(", ", $normalized)
    ]);
    exit;
}

$imported = 0;
$errors = [];

while (($row = fgetcsv($file)) !== false) {
    if (count($row) < count($expectedHeaders)) {
        $row = array_pad($row, count($expectedHeaders), '');
    }
    $data = @array_combine($expectedHeaders, array_slice($row, 0, count($expectedHeaders)));
    if ($data === false) {
        $errors[] = "Malformed row: " . implode(", ", $row);
        continue;
    }
    $cpu = trim($data["cpu"]);
    $ram = trim($data["ram"]);
    $os = trim($data["os"]);
    $employeeId = trim($data["user_id"]);
    $assetTag = trim($data["asset_tag"]);

    logDeviceImport("Trying to import: CPU=$cpu, RAM=$ram, OS=$os, EMP_ID=$employeeId");

    $assignedTo = null;

    if (!empty($employeeId)) {
        $empCheck = $conn->prepare("SELECT emp_id FROM Employees WHERE employee_id = ?");
        $empCheck->bind_param("s", $employeeId);
        $empCheck->execute();
        $empCheck->bind_result($empIdFound);
        if ($empCheck->fetch()) {
            $assignedTo = $empIdFound;
        }
        $empCheck->close();
    }

    if ($assignedTo === null && !empty($data["login_id"])) {
        $empCheck = $conn->prepare("SELECT emp_id FROM Employees WHERE login_id = ?");
        $empCheck->bind_param("s", $data["login_id"]);
        $empCheck->execute();
        $empCheck->bind_result($empIdFound);
        if ($empCheck->fetch()) {
            $assignedTo = $empIdFound;
        }
        $empCheck->close();
    }

    if ($assignedTo === null && ($data["first_name"] || $data["last_name"] || $data["login_id"])) {
        $empInsert = $conn->prepare("INSERT INTO Employees (employee_id, first_name, last_name, login_id, phone_number) VALUES (?, ?, ?, ?, ?)");
        $empInsert->bind_param("sssss", $employeeId, $data["first_name"], $data["last_name"], $data["login_id"], $data["phone_number"]);
        if ($empInsert->execute()) {
            $assignedTo = $empInsert->insert_id;
        }
        $empInsert->close();
    }

    // Insert into Devices
    if ($assignedTo !== null) {
        $deviceInsert = $conn->prepare("INSERT INTO Devices (asset_tag, status, os, assigned_to) VALUES (?, ?, ?, ?)");
        $deviceInsert->bind_param("sssi", $assetTag, $data["status"], $os, $assignedTo);
    } else {
        $deviceInsert = $conn->prepare("INSERT INTO Devices (asset_tag, status, os) VALUES (?, ?, ?)");
        $deviceInsert->bind_param("sss", $assetTag, $data["status"], $os);
    }
    if (!$deviceInsert) {
        $errors[] = "Device insert prepare failed: " . $conn->error;
        logDeviceImport("Device insert prepare failed: " . $conn->error);
        continue;
    }
    if (!$deviceInsert->execute()) {
        $errors[] = "Device insert failed: " . $deviceInsert->error;
        logDeviceImport("Device insert failed: " . $deviceInsert->error);
        $deviceInsert->close();
        continue;
    }
    $deviceId = $deviceInsert->insert_id;
    $deviceInsert->close();

    // Insert into Laptops
    $internet_policy = trim($data["internet_policy"]);
    if (empty($cpu)) { $cpu = "N/A"; }
    if (empty($ram)) { $ram = "N/A"; }
    if (empty($internet_policy)) { $internet_policy = "N/A"; }
    $laptopInsert = $conn->prepare("INSERT INTO Laptops (device_id, cpu, ram, internet_policy) VALUES (?, ?, ?, ?)");
    $laptopInsert->bind_param("isis", $deviceId, $cpu, $ram, $internet_policy);
    if (!$laptopInsert->execute()) {
        $errors[] = "Laptop insert failed: " . $laptopInsert->error;
        logDeviceImport("Laptop insert failed: " . $laptopInsert->error);
        $laptopInsert->close();
        continue;
    }
    $laptopInsert->close();

    $imported++;
    logDeviceImport("Successfully imported laptop with asset_tag: $assetTag");
}

fclose($file);

header('Content-Type: application/json');
echo json_encode([
    "status" => $imported > 0 ? "success" : "error",
    "message" => $imported > 0
        ? "✅ Import attempted. $imported of " . ($imported + count($errors)) . " laptop(s) successfully imported."
            . (count($errors) > 0 ? " Issues: " . implode(" | ", $errors) : "")
        : "❌ Import failed. " . implode(" | ", $errors)
]);
exit;
?>