<?php
header('Content-Type: application/json');
require 'db_connect.php';

$user_id = $_GET['user_id'] ?? 0;

if ($user_id == 0) {
    die(json_encode(["status" => "error", "message" => "User ID required"]));
}

$stmt = $conn->prepare("SELECT app_name, usage_time, progress FROM app_usage WHERE user_id = ? ORDER BY progress DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$usage = [];
while ($row = $result->fetch_assoc()) {
    $usage[] = [
        "appName" => $row['app_name'],
        "usageTime" => $row['usage_time'],
        "progress" => (int) $row['progress']
    ];
}

echo json_encode(["status" => "success", "data" => $usage]);

$stmt->close();
$conn->close();
?>