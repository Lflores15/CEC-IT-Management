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
        $employee_id = $row["user_id"];

        echo "Trying to import: CPU={$row["cpu"]}, RAM={$row["ram"]}, OS={$row["os"]}, EMP_ID={$employee_id}<br>";

        // Insert into Employees if not exists
        if (!empty($employee_id)) {
            $check_emp = $conn->prepare("SELECT employee_id FROM Employees WHERE employee_id = ?");
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

        // Insert into Laptops
        $stmt = $conn->prepare("INSERT INTO Laptops (status, internet_policy, asset_tag, login_id, employee_id, cpu, ram, os) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed (Laptops): " . $conn->error);
        }

        $stmt->bind_param(
            "ssssssis",
            $row["status"],
            $row["internet_policy"],
            $row["asset_tag"],
            $row["login_id"],
            $employee_id,
            $row["cpu"],
            $row["ram"],
            $row["os"]
        );

        if ($stmt->execute()) {
            $inserted++;
        } else {
            echo "<span style='color:red;'>Insert failed: " . $stmt->error . "</span><br>";
        }
    }

    fclose($handle);
    echo "<br><strong>Imported $inserted laptops.</strong>";
} else {
    echo "No file uploaded.";
}
?>