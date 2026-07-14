<?php
header('Content-Type: application/json');
require 'db_connect.php';

// Get raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$user_id = $data['user_id'] ?? 0;
$apps = $data['apps'] ?? [];

if ($user_id == 0) {
    die(json_encode(["status" => "error", "message" => "User ID required"]));
}

// 0. Check if child is linked
$check_link = $conn->prepare("SELECT id FROM family_links WHERE child_id = ?");
$check_link->bind_param("i", $user_id);
$check_link->execute();
$check_link->store_result();

if ($check_link->num_rows == 0) {
    $check_link->close();
    die(json_encode(["status" => "unlinked", "message" => "Device is not linked to any parent"]));
}
$check_link->close();

// Transaction for safety
$conn->begin_transaction();

try {
    // 1. Clear old usage data for this user
    $clear = $conn->prepare("DELETE FROM app_usage WHERE user_id = ?");
    $clear->bind_param("i", $user_id);
    $clear->execute();
    $clear->close();

    // 2. Insert new data
    $stmt = $conn->prepare("INSERT INTO app_usage (user_id, app_name, usage_time, progress) VALUES (?, ?, ?, ?)");

    foreach ($apps as $app) {
        $name = $app['appName'] ?? 'Unknown';
        $time = $app['usageTime'] ?? '0m';
        $progress = $app['progress'] ?? 0;

        $stmt->bind_param("issi", $user_id, $name, $time, $progress);
        $stmt->execute();
    }

    $stmt->close();

    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Usage data updated"]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Failed to update usage: " . $e->getMessage()]);
}

$conn->close();
?>