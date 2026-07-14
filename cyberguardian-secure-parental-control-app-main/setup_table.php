<?php
require 'db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS `family_links` (
  `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` int(6) UNSIGNED NOT NULL,
  `child_id` int(6) UNSIGNED NOT NULL,
  `linked_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`child_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_link` (`parent_id`, `child_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Table family_links created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>