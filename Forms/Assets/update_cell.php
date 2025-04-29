<?php
session_start();
require_once "../../PHP/config.php";

$deviceId = $_POST['device_id'] ?? '';
$column = $_POST['column'] ?? '';
$value = $_POST['value'] ?? '';

$laptopColumns = ['cpu', 'ram', 'internet_policy', 'os'];
$deviceColumns = ['asset_tag', 'status', 'assigned_to'];
$employeeColumns = ['login_id', 'emp_first_name', 'emp_last_name', 'employee_id', 'phone_number'];

if (in_array($column, $laptopColumns)) {
    $table = "Laptops";
    $recordId = $deviceId;

    $lookup = $conn->prepare("SELECT device_id FROM Laptops WHERE device_id = ?");
    if (!$lookup) {
        echo "prepare_failed: " . $conn->error;
        exit;
    }
    $lookup->bind_param("i", $deviceId);
    $lookup->execute();
    $lookup->store_result();
    if ($lookup->num_rows === 0) {
        $lookup->close();
        // Insert a new Laptops row with this device_id if not exists
        $insertLaptop = $conn->prepare("INSERT INTO Laptops (device_id, internet_policy, cpu, ram, os) VALUES (?, '', '', 0, '')");
        if (!$insertLaptop) {
            echo "laptop_prepare_failed: " . $conn->error;
            exit;
        }
        $insertLaptop->bind_param("i", $deviceId);
        if (!$insertLaptop->execute()) {
            echo "laptop_insert_failed: " . $insertLaptop->error;
            $insertLaptop->close();
            exit;
        }
        $insertLaptop->close();
    } else {
        $lookup->close();
    }
} elseif (in_array($column, $deviceColumns)) {
    $table = "Devices";
    $idField = "device_id";
    $recordId = $deviceId;
} elseif (in_array($column, $employeeColumns)) {
    $table = "Employees";
    $idField = "emp_id";

    $empQuery = $conn->prepare("SELECT assigned_to FROM Devices WHERE device_id = ?");
    $empQuery->bind_param("i", $deviceId);
    $empQuery->execute();
    $empQuery->bind_result($empId);
    $empQuery->fetch();
    $empQuery->close();

    if (!$empId) {
        echo "employee_not_found";
        exit;
    }

    $recordId = $empId; 
} else {
    echo "invalid_column";
    exit;
}

if ($column === 'internet_policy') {
    $allowedPolicies = ['Default', 'Office', 'Admin', 'Accounting', 'Estimating', 'Executive', 'HR'];
    if (!in_array($value, $allowedPolicies)) {
        echo "invalid_internet_policy_value";
        exit;
    }
}

if ($table === "Laptops") {
    $sql = "UPDATE Laptops SET $column = ? WHERE device_id = ?";
} else {
    $sql = "UPDATE $table SET $column = ? WHERE $idField = ?";
}
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "prepare_failed: " . $conn->error . " | SQL: " . $sql . " | ID: " . $recordId . " | Column: " . $column;
    exit;
}

$stmt->bind_param("si", $value, $recordId);

if ($stmt->execute()) {
    $username = $_SESSION['login'] ?? $_SESSION['user'] ?? 'unknown';
    // Fetch asset tag for logging if device_id is known
    $logAssetTag = 'unknown';
    if (!empty($deviceId)) {
        $tagQuery = $conn->prepare("SELECT asset_tag FROM Devices WHERE device_id = ?");
        $tagQuery->bind_param("i", $deviceId);
        $tagQuery->execute();
        $tagQuery->bind_result($logAssetTag);
        $tagQuery->fetch();
        $tagQuery->close();
    }

    $logMessage = "[" . date("Y-m-d H:i:s") . "] [UPDATE] [$username] Updated $table - $column set to '$value' for asset tag: $logAssetTag\n";
    file_put_contents("../../Logs/device_event_log.txt", $logMessage, FILE_APPEND);
    echo "success";

    if ($table === 'Devices' && $column === 'status' && strtolower($value) === 'decommissioned') {
        // Get laptop_id
        $laptopQuery = $conn->prepare("SELECT laptop_id FROM Laptops WHERE device_id = ?");
        $laptopQuery->bind_param("i", $deviceId);
        $laptopQuery->execute();
        $laptopQuery->bind_result($laptopId);
        $laptopQuery->fetch();
        $laptopQuery->close();

        if ($laptopId) {
            // Check if already exists
            $check = $conn->prepare("SELECT decommission_id FROM Decommissioned_Laptops WHERE laptop_id = ?");
            $check->bind_param("i", $laptopId);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $updateDL = $conn->prepare("UPDATE Decommissioned_Laptops SET decommission_status = 'Decommissioned' WHERE laptop_id = ?");
                $updateDL->bind_param("i", $laptopId);
                $updateDL->execute();
                $updateDL->close();
            } else {
                $insertDL = $conn->prepare("INSERT INTO Decommissioned_Laptops (laptop_id, decommission_status) VALUES (?, 'Decommissioned')");
                $insertDL->bind_param("i", $laptopId);
                $insertDL->execute();
                $insertDL->close();
            }

            $check->close();
        }
    }

} else {
    echo "execute_failed: " . $stmt->error . " | SQL: " . $sql . " | ID: " . $recordId . " | Column: " . $column . " | Value: " . $value;
}

$stmt->close();
$conn->close();