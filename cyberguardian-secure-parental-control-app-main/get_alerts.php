<?php
header('Content-Type: application/json');
require 'db_connect.php';

$user_id = $_GET['user_id'] ?? 0;

if ($user_id == 0) {
    die(json_encode(["status" => "error", "message" => "User ID required"]));
}

$stmt = $conn->prepare("SELECT title, description, created_at, is_unread FROM alerts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$alerts = [];
while ($row = $result->fetch_assoc()) {
    // Calculate timeAgo roughly
    $timeAgo = "Just now"; // Simplification for now, better to send timestamp and let App calculate
    $alert_time = strtotime($row['created_at']);
    $diff = time() - $alert_time;

    if ($diff < 60) {
        $timeAgo = "Just now";
    } elseif ($diff < 3600) {
        $timeAgo = floor($diff / 60) . "m ago";
    } elseif ($diff < 86400) {
        $timeAgo = floor($diff / 3600) . "h ago";
    } else {
        $timeAgo = floor($diff / 86400) . "d ago";
    }

    $alerts[] = [
        "title" => $row['title'],
        "description" => $row['description'],
        "timeAgo" => $timeAgo,
        "isUnread" => (bool) $row['is_unread'],
        // Default icons for now, client side can map logic based on title
        "type" => "default"
    ];
}

echo json_encode(["status" => "success", "data" => $alerts]);

$stmt->close();
$conn->close();
?>