<?php
require_once(__DIR__ . '/../../PHP/config.php');
header("Content-Type: application/json");

$response = ["success" => false, "message" => "Unknown error"];

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["csvFile"]) && $_FILES["csvFile"]["error"] === UPLOAD_ERR_OK) {
        $file = $_FILES["csvFile"]["tmp_name"];
        if (($handle = fopen($file, "r")) !== false) {
            fgetcsv($handle); // Skip intro
            $headers = fgetcsv($handle); // Real header

            $expected_headers = ['status', 'asset_tag', 'serial_number', 'os', 'internet_policy', 'cpu', 'ram', 'assigned_to', 'first_name', 'last_name', 'login_id', 'phone_number'];
            if ($headers !== $expected_headers) {
                throw new Exception("Invalid CSV headers. Expected: " . implode(", ", $expected_headers));
            }

            $imported = 0;

            $device_stmt = $conn->prepare("INSERT INTO Devices (status, asset_tag, serial_number, os, assigned_to) VALUES (?, ?, ?, ?, ?)");
            $laptop_stmt = $conn->prepare("INSERT INTO Laptops (device_id, cpu, ram, internet_policy) VALUES (?, ?, ?, ?)");
            $employee_stmt = $conn->prepare("INSERT IGNORE INTO Employees (first_name, last_name, login_id, phone_number, employee_id) VALUES (?, ?, ?, ?, ?)");

            if (!$device_stmt || !$laptop_stmt || !$employee_stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            while (($data = fgetcsv($handle)) !== false) {
                [$status, $asset_tag, $serial_number, $os, $internet_policy, $cpu, $ram, $employee_id, $first_name, $last_name, $login_id, $phone_number] = $data;

                $employee_stmt->bind_param("sssss", $first_name, $last_name, $login_id, $phone_number, $employee_id);
                $employee_stmt->execute();

                $device_stmt->bind_param("ssssi", $status, $asset_tag, $serial_number, $os, $employee_id);
                if ($device_stmt->execute()) {
                    $device_id = $device_stmt->insert_id;

                    $laptop_stmt->bind_param("isis", $device_id, $cpu, $ram, $internet_policy);
                    if ($laptop_stmt->execute()) {
                        $imported++;
                    }
                }
            }

            fclose($handle);
            $response = ["success" => true, "message" => "Imported $imported laptops."];
        } else {
            throw new Exception("Failed to open CSV file.");
        }
    } else {
        throw new Exception("Invalid request. Make sure a CSV file was uploaded.");
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
?>