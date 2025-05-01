<?php
session_start();
require_once("../../PHP/config.php");

if (!isset($_SESSION['username']) && isset($_SESSION['user_id'])) {
    // Attempt to fetch username from database using user_id
    $user_result = $conn->query("SELECT login FROM Users WHERE user_id = " . intval($_SESSION['user_id']));
    if ($user_result && $user_result->num_rows > 0) {
        $user_row = $user_result->fetch_assoc();
        $_SESSION['username'] = $user_row['login'];
    }
}
$user = $_SESSION['username'] ?? 'unknown';

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
                // Log deletion event(s)
                require_once "../../includes/log_event.php";
                $actor = $_SESSION['login'] ?? $_SESSION['username'] ?? $_SESSION['user'] ?? 'unknown';
                $timestamp = date("Y-m-d H:i:s");
                foreach ($ids as $empCode) {
                    $message = "Employee '$empCode' was deleted";
                    logUserEvent("DELETE_EMPLOYEE", $message, $actor);
                }
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