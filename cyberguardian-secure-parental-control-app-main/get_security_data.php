<?php
header('Content-Type: application/json');
require 'db_connect.php';

$user_id = $_GET['user_id'] ?? 0;

if ($user_id == 0) {
    die(json_encode(["status" => "error", "message" => "User ID required"]));
}

$response = ["status" => "success"];

// Get Security Apps
$stmt = $conn->prepare("SELECT app_name, is_safe, status_text FROM security_apps WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$apps = [];
while ($row = $result->fetch_assoc()) {
    $apps[] = [
        "appName" => $row['app_name'],
        "isSafe" => (bool) $row['is_safe'],
        "statusText" => $row['status_text']
    ];
}
$response['apps'] = $apps;
$stmt->close();

// Get Blocked Sites
$stmt = $conn->prepare("SELECT url, reason, blocked_at FROM blocked_sites WHERE user_id = ? ORDER BY blocked_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$blocked = [];
while ($row = $result->fetch_assoc()) {
    // TimeAgo logic
    $timeAgo = "Just now";
    $alert_time = strtotime($row['blocked_at']);
    $diff = time() - $alert_time;
    if ($diff < 60)
        $timeAgo = "Just now";
    elseif ($diff < 3600)
        $timeAgo = floor($diff / 60) . "m ago";
    elseif ($diff < 86400)
        $timeAgo = floor($diff / 3600) . "h ago";
    else
        $timeAgo = floor($diff / 86400) . "d ago";

    $blocked[] = [
        "url" => $row['url'],
        "reason" => $row['reason'],
        "timeAgo" => $timeAgo
    ];
}
$response['blocked_sites'] = $blocked;
$stmt->close();

// Get History
$stmt = $conn->prepare("SELECT title, description, event_time, status FROM security_history WHERE user_id = ? ORDER BY event_time DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = [
        "title" => $row['title'],
        "description" => $row['description'],
        "time" => date("h:i A", strtotime($row['event_time'])), // Format like "10:00 AM"
        "status" => $row['status']
    ];
}
$response['history'] = $history;
$stmt->close();

echo json_encode($response);
$conn->close();
?>