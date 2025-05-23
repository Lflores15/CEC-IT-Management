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

//headers to use for importing csv
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

$conn->query("
    INSERT IGNORE INTO Employees (emp_code, username, first_name, last_name, phone_number)
    VALUES ('0000', 'system', 'Unassigned', 'Unassigned', '')
");

while (($row = fgetcsv($file)) !== false) {
    $row = array_pad($row, count($expectedHeaders), '');
    $data = @array_combine($expectedHeaders, $row);
    if (!$data) {
        $errors[] = "Malformed row: " . implode(", ", $row);
        continue;
    }

    $empCode = trim($data["emp_code"]);
    $empCode = $empCode !== '' ? $empCode : '0000';  

    $stmt = $conn->prepare("INSERT IGNORE INTO Employees (emp_code, username, first_name, last_name, phone_number) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param(
            "sssss",
            $empCode,
            $data["username"],
            $data["first_name"],
            $data["last_name"],
            $data["phone_number"]
        );
        $stmt->execute();
        $stmt->close();
    }

    // Insert Device
    $assetTag = trim($data["asset_tag"]);
    if (empty($assetTag)) {
        $errors[] = "Missing asset_tag for a device.";
        continue;
    }

    $status = strtolower(trim($data["status"]));
    $allowedStatuses = ['active', 'lost', 'shelf-cc', 'shelf-md', 'shelf-hs', 'pending return', 'decommissioned', 'open'];
    if (!in_array($status, $allowedStatuses)) {
        $errors[] = "Invalid status '$status' for asset_tag $assetTag.";
        continue;
    }

    // Check if device already exists 
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Devices WHERE asset_tag = ?");
    if ($stmt) {
        $stmt->bind_param("s", $assetTag);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
    }

    if ($count > 0) {
        $errors[] = "Duplicate asset_tag: $assetTag already exists.";
        continue;
    } else {
        // Device does not exist, insert it
        $stmt = $conn->prepare("INSERT INTO Devices (status, asset_tag, assigned_to) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $status, $assetTag, $empCode);
            $stmt->execute();
            $deviceId = $conn->insert_id;
            $stmt->close();
        }
    }

    if ($deviceId) {
        // Insert Laptop
        $internetPolicy = trim($data["internet_policy"]) ?: "Default";
        $cpu = trim($data["cpu"]) ?: "Unknown";
        $ram = is_numeric($data["ram"]) ? intval($data["ram"]) : 0;
        $os = trim($data["os"]) ?: "Unknown";

        $stmt = $conn->prepare("INSERT IGNORE INTO Laptops (device_id, internet_policy, cpu, ram, os) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param(
                "issis",
                $deviceId,
                $internetPolicy,
                $cpu,
                $ram,
                $os
            );
            if ($stmt->execute()) {
                $imported++; 
                logDeviceImport("Imported laptop with asset_tag '$assetTag' assigned to emp_code '$empCode'.");
            } else {
                $errors[] = "Failed to insert laptop details for asset_tag $assetTag.";
            }
            $stmt->close();
        } else {
            $errors[] = "Failed to prepare laptop insert for asset_tag $assetTag.";
        }
    } else {
        $errors[] = "Failed to insert or update device for asset_tag $assetTag.";
    }
}

fclose($file);

if ($imported > 0) {
    $conn->commit();
} else {
    $conn->rollback();
}

header('Content-Type: application/json');
echo json_encode([
    "status" => $imported > 0 ? (count($errors) > 0 ? "partial" : "success") : "error",
    "message" => $imported > 0
        ? "✅ Imported $imported laptop(s)." . (count($errors) ? "<br>⚠️ Some issues: " . implode(" | ", $errors) : "")
        : "❌ No laptops imported. Errors: " . implode(" | ", $errors)
]);
exit;
?>