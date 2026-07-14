<?php
header("Content-Type: application/json; charset=UTF-8");
require 'db_connect.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit();
}

$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
$item_identifier = isset($_POST['item_identifier']) ? $_POST['item_identifier'] : null; // URL or App Name
$reason = isset($_POST['reason']) ? $_POST['reason'] : "Security Risk";
$action = isset($_POST['action']) ? $_POST['action'] : null; // 'block' or 'unblock'

// Validate
if (!$user_id || !$item_identifier || !$action) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit();
}

if ($action === 'block') {
    // Check if already blocked to avoid duplicates
    $stmt = $conn->prepare("SELECT id FROM blocked_sites WHERE user_id = ? AND url = ?");
    $stmt->bind_param("is", $user_id, $item_identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO blocked_sites (user_id, url, reason, blocked_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $user_id, $item_identifier, $reason);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Item blocked successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "success", "message" => "Item already blocked"]);
    }
} else if ($action === 'unblock') {
    $stmt = $conn->prepare("DELETE FROM blocked_sites WHERE user_id = ? AND url = ?");
    $stmt->bind_param("is", $user_id, $item_identifier);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Item unblocked successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }
    $stmt->close();
} else if ($action === 'uninstall') {
    // Log uninstall event to history
    $title = "App Uninstalled";
    $description = "$item_identifier was uninstalled from device.";
    $status = "Resolved";

    // Ensure table exists (optional safety, or just insert)
    $stmt = $conn->prepare("INSERT INTO security_history (user_id, title, description, event_time, status) VALUES (?, ?, ?, NOW(), ?)");
    $stmt->bind_param("isss", $user_id, $title, $description, $status);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Uninstall event logged"]);
    } else {
        // Fallback: create table if not exists? Assuming it exists.
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action. Use 'block', 'unblock', or 'uninstall'"]);
}

$conn->close();
?>