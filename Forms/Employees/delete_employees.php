
<?php
require_once("../../PHP/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data["employee_ids"]) && is_array($data["employee_ids"])) {
        $ids = $data["employee_ids"];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('s', count($ids));
        $stmt = $conn->prepare("DELETE FROM Employees WHERE emp_code IN ($placeholders)");

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