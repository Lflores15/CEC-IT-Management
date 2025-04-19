<?php
require_once("../../PHP/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["csv_file"])) {
    $file = $_FILES["csv_file"]["tmp_name"];
    if (!file_exists($file)) {
        die("File not found.");
    }

    $handle = fopen($file, "r");
    if (!$handle) {
        die("Failed to open file.");
    }

    fgetcsv($handle); // Skip dummy row
    $headers = fgetcsv($handle);
    $inserted = 0;

    while (($data = fgetcsv($handle)) !== false) {
        $row = array_combine($headers, $data);
        $backup_type = "None";
        $device_id = $row["user_id"];

        echo "Trying to import: CPU={$row["cpu"]}, RAM={$row["ram"]}, Internet Policy={$row["internet_policy"]}, User ID={$row["user_id"]}<br>";

        // First, make sure device_id exists in Devices
        $device_check = $conn->prepare("SELECT device_id FROM Devices WHERE device_id = ?");
        if ($device_check) {
            $device_check->bind_param("i", $device_id);
            $device_check->execute();
            $device_check->store_result();
            if ($device_check->num_rows === 0) {
                $insert_device = $conn->prepare("INSERT INTO Devices (device_id) VALUES (?)");
                if ($insert_device) {
                    $insert_device->bind_param("i", $device_id);
                    $insert_device->execute();
                }
            }
        }

        // Insert into Laptops
        $stmt = $conn->prepare("INSERT INTO Laptops (device_id, cpu, ram, internet_policy, backup_type) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed (Laptops): " . $conn->error);
        }

        $stmt->bind_param(
            "isiss",
            $device_id,
            $row["cpu"],
            $row["ram"],
            $row["internet_policy"],
            $backup_type
        );

        if ($stmt->execute()) {
            $inserted++;
        } else {
            echo "<span style='color:red;'>Insert failed: " . $stmt->error . "</span><br>";
        }

        // Insert employee if needed
        if (!empty($row["user_id"])) {
            $check_emp = $conn->prepare("SELECT employee_id FROM Employees WHERE employee_id = ?");
            if ($check_emp) {
                $check_emp->bind_param("s", $row["user_id"]);
                $check_emp->execute();
                $check_emp->store_result();
                if ($check_emp->num_rows === 0) {
                    $emp_stmt = $conn->prepare("INSERT INTO Employees (employee_id, first_name, last_name, login_id, phone_number) VALUES (?, ?, ?, ?, ?)");
                    if ($emp_stmt) {
                        $emp_stmt->bind_param(
                            "sssss",
                            $row["user_id"],
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
    }

    fclose($handle);
    echo "<br><strong>Imported $inserted laptops.</strong>";
} else {
    echo "No file uploaded.";
}
?>