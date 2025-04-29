<?php
require_once "../../PHP/config.php";
session_start();

function logDeviceImport($message) {
    $user = $_SESSION["username"] ?? "unknown";
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents("../../Logs/device_event_log.txt", "[$timestamp] [IMPORT] [$user] $message\n", FILE_APPEND);
}

if (!isset($_FILES["csv_file"]) || $_FILES["csv_file"]["error"] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "Invalid upload"]);
    exit;
}

$expectedHeaders = ['status', 'internet_policy', 'asset_tag', 'username', 'first_name', 'last_name', 'emp_code', 'phone_number', 'cpu', 'ram', 'os'];

$file = fopen($_FILES["csv_file"]["tmp_name"], "r");
if (!$file) {
    echo json_encode(["status" => "error", "message" => "Failed to open uploaded file"]);
    exit;
}

$header = fgetcsv($file);
$normalizedExpected = array_map('strtolower', $expectedHeaders);
$normalized = array_map(fn($h) => strtolower(trim($h)), $header);
$normalized = array_slice($normalized, 0, count($expectedHeaders));

if (implode(',', $normalized) !== implode(',', $normalizedExpected)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid CSV headers. Expected: " . implode(", ", $expectedHeaders) . "<br>Received: " . implode(", ", $normalized)
    ]);
    exit;
}

$conn->begin_transaction();
$imported = 0;
$errors = [];

// Insert dummy employee (0000) to prevent FK failures
$conn->query("INSERT IGNORE INTO Employees (emp_code, username, first_name, last_name, phone_number) VALUES ('0000','system','Unassigned','Unassigned','')");

while (($row = fgetcsv($file)) !== false) {
    $row = array_pad($row, count($expectedHeaders), '');
    $data = @array_combine($expectedHeaders, $row);
    if (!$data) {
        $errors[] = "Malformed row: " . implode(", ", $row);
        continue;
    }

    $empCode = trim($data["emp_code"]) ?: '0000';
    $status = strtolower(trim($data["status"]));
    $assetTag = trim($data["asset_tag"]);
    $cpu = trim($data["cpu"]) ?: "Unknown";
    $ram = is_numeric($data["ram"]) ? intval($data["ram"]) : 0;
    $os = trim($data["os"]) ?: "Unknown";
    $internetPolicy = trim($data["internet_policy"]) ?: "Default";

    if (!$assetTag) {
        $errors[] = "Missing asset tag in row: " . implode(", ", $row);
        continue;
    }

    // Validate ENUM
    $allowedStatuses = ['active', 'lost', 'shelf-cc', 'shelf-md', 'shelf-hx', 'pending return', 'decommissioned', 'open'];
    if (!in_array($status, $allowedStatuses)) {
        $errors[] = "Invalid status '$status' for asset_tag $assetTag.";
        continue;
    }

    // UPSERT employee
    $stmt = $conn->prepare("
        INSERT INTO Employees (emp_code, username, first_name, last_name, phone_number)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            username = VALUES(username),
            first_name = VALUES(first_name),
            last_name = VALUES(last_name),
            phone_number = VALUES(phone_number)
    ");
    if (!$stmt) {
        $errors[] = "Employee statement error: " . $conn->error;
        continue;
    }
    $stmt->bind_param("sssss", $empCode, $data["username"], $data["first_name"], $data["last_name"], $data["phone_number"]);
    if (!$stmt->execute()) {
        $errors[] = "Employee insert failed: " . $stmt->error;
        logDeviceImport("Employee insert failed: " . $stmt->error);
        $stmt->close();
        continue;
    }
    $stmt->close();

    // UPSERT device
    $stmt = $conn->prepare("
        INSERT INTO Devices (status, asset_tag, assigned_to)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            assigned_to = VALUES(assigned_to)
    ");
    if (!$stmt) {
        $errors[] = "Device statement error: " . $conn->error;
        continue;
    }
    $stmt->bind_param("sss", $status, $assetTag, $empCode);
    if (!$stmt->execute()) {
        $errors[] = "Device insert failed for asset_tag $assetTag: " . $stmt->error;
        logDeviceImport("Device insert failed: " . $stmt->error);
        $stmt->close();
        continue;
    }
    $stmt->close();

    // Get device_id (new or existing)
    $stmt = $conn->prepare("SELECT device_id FROM Devices WHERE asset_tag = ? LIMIT 1");
    $stmt->bind_param("s", $assetTag);
    $stmt->execute();
    $stmt->bind_result($deviceId);
    $stmt->fetch();
    $stmt->close();

    if (!$deviceId) {
        $errors[] = "Could not retrieve device_id for asset_tag $assetTag.";
        continue;
    }

    // UPSERT laptop
    $stmt = $conn->prepare("
        INSERT INTO Laptops (device_id, internet_policy, cpu, ram, os)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            internet_policy = VALUES(internet_policy),
            cpu = VALUES(cpu),
            ram = VALUES(ram),
            os = VALUES(os)
    ");
    if (!$stmt) {
        $errors[] = "Laptop statement error: " . $conn->error;
        continue;
    }
    $stmt->bind_param("issis", $deviceId, $internetPolicy, $cpu, $ram, $os);
    if (!$stmt->execute()) {
        $errors[] = "Laptop insert failed for asset_tag $assetTag: " . $stmt->error;
        logDeviceImport("Laptop insert failed: " . $stmt->error);
        $stmt->close();
        continue;
    }
    $stmt->close();

    $imported++;
}

fclose($file);

if ($imported > 0) {
    $conn->commit();
} else {
    $conn->rollback();
}

header('Content-Type: application/json');
echo json_encode([
    "status" => $imported > 0 ? "success" : "error",
    "message" => $imported > 0
        ? "✅ Imported/updated $imported laptop(s)." . (count($errors) ? "<br>⚠️ Issues: " . implode(" | ", $errors) : "")
        : "❌ No laptops imported. Errors: " . implode(" | ", $errors)
]);
exit;
?>
