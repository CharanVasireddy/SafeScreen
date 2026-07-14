<?php
header('Content-Type: application/json');
require 'db_connect.php';

$user_id = $_POST['user_id'] ?? 0;

if ($user_id == 0) {
    die(json_encode(["status" => "error", "message" => "User ID required"]));
}

// Generate random code 6 digits
$code = "CG-" . rand(100000, 999999);
$expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

// Delete old codes for this user
$stmt = $conn->prepare("DELETE FROM pairing_codes WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("INSERT INTO pairing_codes (code, user_id, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param("sis", $code, $user_id, $expires_at);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "code" => $code, "expires_at" => $expires_at]);
} else {
    echo json_encode(["status" => "error", "message" => "Error generating code"]);
}

$stmt->close();
$conn->close();
?>