<?php
header('Content-Type: application/json');
require 'db_connect.php';

$parent_id = $_POST['parent_id'] ?? 0;
$code = $_POST['code'] ?? '';
$child_name_override = $_POST['child_name'] ?? ''; // Optional: Rename child locally? For now let's just link.

if ($parent_id == 0 || empty($code)) {
    die(json_encode(["status" => "error", "message" => "Missing parent ID or code"]));
}

// 1. Verify Code and Get Child ID
$stmt = $conn->prepare("SELECT user_id, expires_at FROM pairing_codes WHERE code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Check expiry
    if (strtotime($row['expires_at']) < time()) {
        die(json_encode(["status" => "error", "message" => "Code expired"]));
    }

    $child_id = $row['user_id'];

    if ($child_id == $parent_id) {
        die(json_encode(["status" => "error", "message" => "Cannot link to yourself"]));
    }

    // 2. Link in family_links
    // Check if already linked
    $check = $conn->prepare("SELECT id FROM family_links WHERE parent_id = ? AND child_id = ?");
    $check->bind_param("ii", $parent_id, $child_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        // Already linked, just success or message
        echo json_encode(["status" => "success", "message" => "Child already linked", "child_id" => $child_id]);
    } else {
        $insert = $conn->prepare("INSERT INTO family_links (parent_id, child_id) VALUES (?, ?)");
        $insert->bind_param("ii", $parent_id, $child_id);

        if ($insert->execute()) {

            // 3. Update Child's Name if provided (Optional, updating users table)
            if (!empty($child_name_override)) {
                $upd = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
                $upd->bind_param("si", $child_name_override, $child_id);
                $upd->execute();
                $upd->close();
            }

            // 4. Cleanup code
            $del = $conn->prepare("DELETE FROM pairing_codes WHERE code = ?");
            $del->bind_param("s", $code);
            $del->execute();
            $del->close();

            echo json_encode(["status" => "success", "message" => "Child linked successfully", "child_id" => $child_id]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to link child"]);
        }
        $insert->close();
    }
    $check->close();

} else {
    echo json_encode(["status" => "error", "message" => "Invalid pairing code"]);
}

$stmt->close();
$conn->close();
?>