<?php
session_start();
require_once("../../PHP/config.php");
require_once("../../includes/log_event.php");

$response = ["status" => "error", "message" => "No file uploaded."];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["csv_file"])) {
    $file = $_FILES["csv_file"]["tmp_name"];
    if (!file_exists($file)) {
        $response = ["status" => "error", "message" => "File not found."];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $username = $_SESSION["username"] ?? 'unknown';

    $handle = fopen($file, "r");
    if (!$handle) {
        $response = ["status" => "error", "message" => "Failed to open file."];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $headers = fgetcsv($handle);
    $headers = array_map('strtolower', array_map('trim', $headers));
    $inserted = 0;
    $attempted = 0;

    while (($data = fgetcsv($handle)) !== false) {
        $attempted++;
        $row = array_combine($headers, $data);
        $employee_id = $row["assigned_to"] ?? '';

        // Optional debug:
        $cpu = $row["cpu"] ?? '';
        $ram = $row["ram"] ?? '';
        $os = $row["os"] ?? '';
        $asset_tag = $row["asset_tag"] ?? '';
        $serial_number = $row["serial_number"] ?? '';
        
        // Insert into Employees if not exists
        if (!empty($employee_id)) {
            $check_emp = $conn->prepare("SELECT emp_id FROM Employees WHERE employee_id = ?");
            if ($check_emp) {
                $check_emp->bind_param("s", $employee_id);
                $check_emp->execute();
                $check_emp->store_result();
                if ($check_emp->num_rows === 0) {
                    $emp_stmt = $conn->prepare("INSERT INTO Employees (employee_id, first_name, last_name, login_id, phone_number) VALUES (?, ?, ?, ?, ?)");
                    if ($emp_stmt) {
                        $emp_stmt->bind_param(
                            "sssss",
                            $employee_id,
                            $row["first_name"],
                            $row["last_name"],
                            $row["login_id"],
                            $row["phone_number"]
                        );
                        $emp_stmt->execute();
                    }
                }
            }
        }

        // Always retrieve emp_id after attempting to insert or check for existence
        $get_emp_id = $conn->prepare("SELECT emp_id FROM Employees WHERE employee_id = ?");
        $get_emp_id->bind_param("s", $employee_id);
        $get_emp_id->execute();
        $get_emp_id->bind_result($emp_id);
        $get_emp_id->fetch();
        $get_emp_id->close();

        // Insert into Devices
        $device_stmt = $conn->prepare("INSERT INTO Devices (asset_tag, serial_number, os, category, assigned_to) VALUES (?, ?, ?, 'laptop', ?)");
        if (!$device_stmt) {
            $response = ["status" => "error", "message" => "Prepare failed (Devices): " . $conn->error];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        $device_stmt->bind_param(
            "sssi",
            $asset_tag,
            $serial_number,
            $os,
            $emp_id
        );

            if (!$device_stmt->execute()) {
                if (strpos($device_stmt->error, 'Duplicate entry') !== false && strpos($device_stmt->error, 'asset_tag') !== false) {
                    $response = [
                        "status" => "error",
                        "message" => "Import attempted. 0 of 1 laptop(s) successfully imported.\n\n❌ Reason:\nDuplicate asset tag '{$asset_tag}'.\nEach device must have a unique asset tag."
                    ];
            } else {
                $response = ["status" => "error", "message" => "Device insert failed: " . $device_stmt->error];
            }
            continue;
        }

        $device_id = $conn->insert_id;

        if (!$device_id) {
            $response = ["status" => "error", "message" => "Failed to get device_id after inserting device for asset_tag: {$asset_tag}"];
            continue;
        }

        // Ensure CPU, RAM, and internet_policy are explicitly set
        $cpu = $row["cpu"] ?? '';
        $ram = $row["ram"] ?? '';
        $internet_policy = $row["internet_policy"] ?? 'default';

        // Insert into Laptops
        $stmt = $conn->prepare("INSERT INTO Laptops (device_id, cpu, ram, internet_policy) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            $response = ["status" => "error", "message" => "Prepare failed (Laptops): " . $conn->error];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        $internet_policy = $row["internet_policy"] ?? '';
        $stmt->bind_param(
            "isis",
            $device_id,
            $cpu,
            $ram,
            $internet_policy
        );

        if ($stmt->execute()) {
            $inserted++;
            logDeviceEvent("IMPORT", "Imported Laptop with asset tag: {$asset_tag}", $username);
        } else {
            $response = ["status" => "error", "message" => "Insert failed: " . $stmt->error];
            logDeviceEvent("IMPORT_FAILED", "Failed to import Laptop with asset tag: {$asset_tag}. Error: " . $stmt->error, $username);
        }
    }

    fclose($handle);
    if ($inserted === 0) {
        logDeviceEvent("IMPORT_FAILED", "No laptops were imported by {$username}. Possible CSV format issue or data constraint error.", $username);
    }

    $message = $response["message"];

    $response = [
        "status" => $inserted > 0 ? "success" : "error",
        "message" => $message
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>