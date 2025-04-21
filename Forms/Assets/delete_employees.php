
<?php
require_once("../../PHP/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["employee_ids"])) {
    file_put_contents("debug.log", print_r($_POST, true), FILE_APPEND);

   
    $ids = $_POST["employee_ids"];
    if (is_array($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('s', count($ids));  // using 's' for string since employee_id is varchar
        $stmt = $conn->prepare("DELETE FROM Employees WHERE employee_id IN ($placeholders)");

        if ($stmt) {
            $stmt->bind_param($types, ...$ids);
            if ($stmt->execute()) {
                echo "success";
            } else {
                http_response_code(500);
                echo "Deletion failed: " . $stmt->error;
            }
        } else {
            http_response_code(500);
            echo "Prepare failed: " . $conn->error;
        }
    } else {
        http_response_code(400);
        echo "Invalid request.";
    }
} else {
    http_response_code(405);
    echo "Method not allowed.";
}
?>