<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cbgd_db";

// Create connection
try {
    $conn = new mysqli($servername, $username, $password);
} catch (Exception $e) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $e->getMessage()]));
}

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    $conn->select_db($dbname);
} else {
    die(json_encode(["status" => "error", "message" => "Error creating database: " . $conn->error]));
}
?>