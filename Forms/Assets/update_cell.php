<?php
require_once "../../PHP/config.php";

// Get inputs
$deviceId = $_POST['device_id'] ?? '';
$column = $_POST['column'] ?? '';
$value = $_POST['value'] ?? '';

// Whitelist allowed columns and determine which table to update
$deviceColumns = ['asset_tag', 'status', 'cpu', 'ram', 'os', 'assigned_to']; // ✅ Added 'assigned_to'
$laptopColumns = ['internet_policy'];
$employeeColumns = ['login_id', 'emp_first_name', 'emp_last_name', 'employee_id', 'phone_number'];

if (in_array($column, $deviceColumns)) {
    $table = "Devices";
    $idField = "device_id";

} elseif (in_array($column, $laptopColumns)) {
    $table = "Laptops";
    $idField = "device_id";

} elseif (in_array($column, $employeeColumns)) {
    $table = "Employees";
    $idField = "emp_id";

    // Get emp_id from Devices table (for updates to Employees)
    $empQuery = $conn->prepare("SELECT assigned_to FROM Devices WHERE device_id = ?");
    $empQuery->bind_param("i", $deviceId);
    $empQuery->execute();
    $empQuery->bind_result($deviceId);
    $empQuery->fetch();
    $empQuery->close();

} else {
    echo "invalid_column";
    exit;
}

// Prepare dynamic update
$sql = "UPDATE $table SET $column = ? WHERE $idField = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "prepare_failed: " . $conn->error;
    exit;
}

$stmt->bind_param("si", $value, $deviceId);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "execute_failed: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>