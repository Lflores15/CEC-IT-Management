<?php
require_once "../../PHP/config.php";
session_start();

function logDeviceImport($message) {
    $user = $_SESSION["user_id"] ?? "unknown";
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
    $empId = trim($data["user_id"]);
    $empIdParam = is_numeric($empId) ? (int)$empId : null;
    $assetTag = trim($data["asset_tag"]);

    logDeviceImport("Trying to import: CPU=$cpu, RAM=$ram, OS=$os, EMP_ID=$empId");

    // Insert into Employees if not exists
    if (!empty($empId)) {
        $empCheck = $conn->prepare("SELECT emp_id FROM Employees WHERE emp_id = ?");
        $empCheck->bind_param("s", $empId);
        $empCheck->execute();
        $empCheck->store_result();
        if ($empCheck->num_rows === 0) {
            $empInsert = $conn->prepare("INSERT INTO Employees (emp_id, first_name, last_name, login_id, phone_number) VALUES (?, ?, ?, ?, ?)");
            $empInsert->bind_param("sssss", $empId, $data["first_name"], $data["last_name"], $data["login_id"], $data["phone_number"]);
            $empInsert->execute();
            $empInsert->close();
        }
        $empCheck->close();
    }

    // Insert into Devices
    $deviceInsert = $conn->prepare("INSERT INTO Devices (asset_tag, status, os, internet_policy, assigned_to, category) VALUES (?, ?, ?, ?, ?, 'laptop')");
    if (!$deviceInsert) {
        $errors[] = "Device insert prepare failed: " . $conn->error;
        logDeviceImport("Device insert prepare failed: " . $conn->error);
        continue;
    }
    $deviceInsert->bind_param("ssssi", $assetTag, $data["status"], $os, $data["internet_policy"], $empIdParam);
    if (!$deviceInsert->execute()) {
        $errors[] = "Device insert failed: " . $deviceInsert->error;
        logDeviceImport("Device insert failed: " . $deviceInsert->error);
        $deviceInsert->close();
        continue;
    }
    $deviceId = $deviceInsert->insert_id;
    $deviceInsert->close();

    // Insert into Laptops
    $laptopInsert = $conn->prepare("INSERT INTO Laptops (device_id, cpu, ram, internet_policy) VALUES (?, ?, ?, ?)");
    $laptopInsert->bind_param("isis", $deviceId, $cpu, $ram, $data["internet_policy"]);
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

if ($imported > 0) {
    echo json_encode(["status" => "success", "message" => "Import attempted. $imported of " . ($imported + count($errors)) . " laptop(s) successfully imported. Reason(s): " . implode(" | ", $errors)]);
} else {
    echo json_encode(["status" => "error", "message" => "Import attempted. 0 laptops imported. " . implode(" | ", $errors)]);
}
?>