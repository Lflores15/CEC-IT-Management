<?php
session_start();
require_once "../../PHP/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST["user_id"];
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $role = $_POST["role"];

    if (empty($username) || empty($email) || empty($role)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit();
    }

    $query = "UPDATE Users SET username = ?, email = ?, role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $username, $email, $role, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "User updated successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update user."]);
    }

    $stmt->close();
    $conn->close();
}
?>