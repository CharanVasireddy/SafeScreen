<?php
header('Content-Type: application/json');
require 'db_connect.php';

// Get raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$parent_id = $data['parent_id'] ?? 0;
$child_id = $data['child_id'] ?? 0;

if ($parent_id == 0 || $child_id == 0) {
    die(json_encode(["status" => "error", "message" => "Parent ID and Child ID required"]));
}

$stmt = $conn->prepare("DELETE FROM family_links WHERE parent_id = ? AND child_id = ?");
$stmt->bind_param("ii", $parent_id, $child_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Device disconnected successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Link not found"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>