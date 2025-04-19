<?php
require_once "../../PHP/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $status = $_POST['status'] ?? '';
    $internet_policy = $_POST['internet_policy'] ?? '';
    $asset_tag = $_POST['asset_tag'] ?? '';
    $login_id = $_POST['login_id'] ?? '';
    $emp_first_name = $_POST['emp_first_name'] ?? '';
    $emp_last_name = $_POST['emp_last_name'] ?? '';
    $employee_id = $_POST['employee_id'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $cpu = $_POST['cpu'] ?? '';
    $ram = $_POST['ram'] ?? '';
    $os = $_POST['os'] ?? '';
    $serial_number = $_POST['serial_number'] ?? '';
    $purchase_date = $_POST['purchase_date'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $model = $_POST['model'] ?? '';

    if (!$status || !$internet_policy || !$asset_tag || !$login_id || !$emp_first_name || !$emp_last_name || !$employee_id || !$phone_number || !$cpu || !$ram || !$os || !$serial_number || !$purchase_date || !$brand || !$model) {
        echo "missing_required_fields";
        exit;
    }

    // Retrieve device_id using employee_id
    $deviceQuery = $conn->prepare("SELECT device_id FROM Devices WHERE assigned_to = (SELECT emp_id FROM Employees WHERE employee_id = ? LIMIT 1)");
    $deviceQuery->bind_param("s", $employee_id);
    $deviceQuery->execute();
    $deviceQuery->bind_result($device_id);
    $deviceQuery->fetch();
    $deviceQuery->close();

    if (!$device_id) {
        echo "device_not_found";
        exit;
    }

    // Update Devices table
    $stmt = $conn->prepare("UPDATE Devices d
        LEFT JOIN Employees e ON d.assigned_to = e.emp_id
        SET d.status = ?, d.asset_tag = ?, d.os = ?, d.serial_number = ?, d.purchase_date = ?, d.brand = ?, d.model = ?, d.category = 'laptop',
            e.login_id = ?, e.first_name = ?, e.last_name = ?, e.employee_id = ?, e.phone_number = ?
        WHERE d.device_id = ?");
    $stmt->bind_param("ssssssssssssi", $status, $asset_tag, $os, $serial_number, $purchase_date, $brand, $model,
        $login_id, $emp_first_name, $emp_last_name, $employee_id, $phone_number, $device_id);

    if (!$stmt->execute()) {
        echo "device_update_failed: " . $stmt->error;
        exit;
    }
    $stmt->close();

    // Update Laptops table
    $stmt = $conn->prepare("UPDATE Laptops SET cpu = ?, ram = ?, internet_policy = ? WHERE device_id = ?");
    $stmt->bind_param("sisi", $cpu, $ram, $internet_policy, $device_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "laptop_update_failed: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "invalid_request";
}
?>