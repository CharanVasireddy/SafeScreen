<?php
header('Content-Type: application/json');
require 'db_connect.php';

// Support both JSON and POST
$input = json_decode(file_get_contents('php://input'), true);
if ($input) {
    $_POST = $input;
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'parent';

if (empty($name) || empty($email) || empty($password)) {
    die(json_encode(["status" => "error", "message" => "Missing required fields"]));
}

// Check if email exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die(json_encode(["status" => "error", "message" => "Email already registered"]));
}
$stmt->close();

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "User registered successfully", "user_id" => $stmt->insert_id]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>