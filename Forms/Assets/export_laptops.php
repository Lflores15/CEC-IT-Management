<?php
$host = 'cec_it_management';
$db = 'cec_it_management';
$user = 'root';
$pass = 'root';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    die('Database connection failed: ' . $conn->connect_error);
}

// Dynamic filename
$now = date('Y-m-d_H-i-s');
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="laptops_export_'.$now.'.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// Write CSV header manually
fwrite($output, "status,internet_policy,asset_tag,username,first_name,last_name,emp_code,phone_number,cpu,ram,os\n");

$query = "
SELECT 
  Devices.status,
  Laptops.internet_policy,
  Devices.asset_tag,
  Employees.username,
  Employees.first_name,
  Employees.last_name,
  Employees.emp_code,
  Employees.phone_number,
  Laptops.cpu,
  Laptops.ram,
  Laptops.os
FROM 
  Devices
LEFT JOIN 
  Laptops ON Devices.device_id = Laptops.device_id
LEFT JOIN 
  Employees ON Devices.assigned_to = Employees.emp_code
";

$result = $conn->query($query);

if (!$result) {
    die('MySQL Error: ' . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    // Clean phone number
    $phone_number = isset($row['phone_number']) ? preg_replace('/\D/', '', $row['phone_number']) : 'N/A';

    // Always fill missing fields with N/A
    $username = !empty(trim($row['username'])) ? trim($row['username']) : 'N/A';
    $first_name = !empty(trim($row['first_name'])) ? trim($row['first_name']) : 'N/A';
    $last_name = !empty(trim($row['last_name'])) ? trim($row['last_name']) : 'N/A';
    $emp_code = !empty(trim($row['emp_code'])) ? trim($row['emp_code']) : 'N/A';
    $phone_number = !empty($phone_number) ? $phone_number : 'N/A';

    // Other fields
    // Match dashboard export column order:
    // Status,Internet Policy,Asset Tag,CPU,RAM (GB),OS,Username,First Name,Last Name,Employee ID,Phone Number
    $fields = [
        $row['status'] ?? 'N/A',
        $row['internet_policy'] ?? 'N/A',
        $row['asset_tag'] ?? 'N/A',
        $row['cpu'] ?? 'N/A',
        $row['ram'] !== null ? $row['ram'] : 'N/A',
        $row['os'] ?? 'N/A',
        $row['username'] ?? 'N/A',
        $row['first_name'] ?? 'N/A',
        $row['last_name'] ?? 'N/A',
        $row['emp_code'] ?? 'N/A',
        isset($row['phone_number']) ? (preg_replace('/\D/', '', $row['phone_number']) ?: 'N/A') : 'N/A'
    ];
    // Remove \n, \r from fields
    foreach ($fields as &$field) {
        $field = str_replace(["\r", "\n"], '', $field);
    }
    unset($field);

    // Write clean CSV line
    fwrite($output, implode(",", $fields) . "\n");
}

fclose($output);
$conn->close();
exit;
?>

