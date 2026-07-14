<?php
header('Content-Type: application/json');
require 'db_connect.php';

$code = $_POST['code'] ?? '';
$device_name = $_POST['device_name'] ?? 'Unknown Device';
$device_identifier = $_POST['device_identifier'] ?? '';

if (empty($code) || empty($device_identifier)) {
    die(json_encode(["status" => "error", "message" => "Missing code or device identifier"]));
}

// Find code
$stmt = $conn->prepare("SELECT user_id, expires_at FROM pairing_codes WHERE code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (strtotime($row['expires_at']) > time()) {
        $user_id = $row['user_id'];

        // Link device
        $stmt_device = $conn->prepare("INSERT INTO devices (user_id, device_name, device_identifier, last_active) VALUES (?, ?, ?, NOW())");
        $stmt_device->bind_param("iss", $user_id, $device_name, $device_identifier);

        if ($stmt_device->execute()) {
            echo json_encode(["status" => "success", "message" => "Device paired successfully", "user_id" => $user_id]);

            // Cleanup code
            $conn->query("DELETE FROM pairing_codes WHERE code = '$code'");
        } else {
            echo json_encode(["status" => "error", "message" => "Error linking device"]);
        }
        $stmt_device->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Code expired"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid code"]);
}

$stmt->close();
$conn->close();
?>