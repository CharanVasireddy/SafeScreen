<?php
header('Content-Type: application/json');
require 'db_connect.php';

$parent_id = $_GET['parent_id'] ?? 0;

if ($parent_id == 0) {
    die(json_encode(["status" => "error", "message" => "Parent ID required"]));
}

$stmt = $conn->prepare("
    SELECT u.id, u.name, u.email 
    FROM family_links fl 
    JOIN users u ON fl.child_id = u.id 
    WHERE fl.parent_id = ?
");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();

$children = [];
while ($row = $result->fetch_assoc()) {
    $children[] = [
        "id" => $row['id'],
        "name" => $row['name'],
        "email" => $row['email']
    ];
}

echo json_encode(["status" => "success", "children" => $children]);

$stmt->close();
$conn->close();
?>