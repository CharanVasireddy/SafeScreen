<?php
header('Content-Type: application/json');
require 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
if ($input) {
    $_POST = $input;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? ''; // Optional check

if (empty($email) || empty($password)) {
    die(json_encode(["status" => "error", "message" => "Missing email or password"]));
}

$stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        // Optional role check
        if (!empty($role) && strtolower($role) != strtolower($row['role'])) {
            // For now, let's just warn or allow? 
            // "Child login" vs "Parent login" might be enforced here.
            // If user tries to login as Child but account is Parent, maybe allow but return role?
            // Let's strict check if provided.
            if (strtolower($role) == 'child' && strtolower($row['role']) == 'parent') {
                // Maybe allow parents to login as child views?
            }
        }

        echo json_encode([
            "status" => "success",
            "message" => "Login successful",
            "user" => [
                "id" => $row['id'],
                "name" => $row['name'],
                "email" => $email,
                "role" => $row['role']
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid password"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}

$stmt->close();
$conn->close();
?>