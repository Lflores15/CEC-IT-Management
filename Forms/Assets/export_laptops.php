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

$now = date('Y-m-d_H-i-s');
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="laptops_export_'.$now.'.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

fwrite($output, "Status,Internet Policy,Asset Tag,CPU,RAM (GB),OS,Username,First Name,Last Name,Employee ID,Phone Number\n");

$query = "
SELECT 
  d.status,
  l.internet_policy,
  d.asset_tag,
  l.cpu,
  l.ram,
  l.os,
  e.username,
  e.first_name,
  e.last_name,
  e.emp_code,
  e.phone_number
FROM Devices d
LEFT JOIN Laptops l ON d.device_id = l.device_id
LEFT JOIN Employees e ON d.assigned_to = e.emp_code
ORDER BY d.asset_tag
";

$result = $conn->query($query);

if (!$result) {
    die('MySQL Error: ' . $conn->error);
}

while ($row = $result->fetch_assoc()) {
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
    fwrite($output, implode(",", $fields) . "\n");
}

fclose($output);
$conn->close();
exit;
?>