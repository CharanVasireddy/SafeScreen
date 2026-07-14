<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cbgd_db"; // Database to create/use

// Connect to MySQL server first (without selecting DB)
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read the SQL file
$sqlFile = 'cbgd_db.sql';
if (!file_exists($sqlFile)) {
    die("Error: cbgd_db.sql file not found.");
}

$sqlContent = file_get_contents($sqlFile);

// Execute multi_query
// Note: multi_query executes strictly sequentially.
if ($conn->multi_query($sqlContent)) {
    do {
        // We must consume all results or errors
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    echo "<h1>Database Setup Complete</h1>";
    echo "<p>Successfully imported structure from <code>cbgd_db.sql</code>.</p>";
    echo "<p>Users table, Alerts, App Usage, etc. have been reset.</p>";
} else {
    echo "<h1>Error</h1>";
    echo "<p>Error executing SQL: " . $conn->error . "</p>";
}

$conn->close();
?>