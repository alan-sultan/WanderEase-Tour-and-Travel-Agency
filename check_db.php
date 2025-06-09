<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';

try {
    $conn = getDBConnection();

    // Check if packages table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'packages'");
    if ($stmt->rowCount() === 0) {
        die("Packages table does not exist. Please run schema.sql first.");
    }

    // Check packages table structure
    $stmt = $conn->query("DESCRIBE packages");
    echo "<h2>Packages Table Structure:</h2>";
    echo "<pre>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";

    // Check if we have any packages
    $stmt = $conn->query("SELECT COUNT(*) as count FROM packages");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<h2>Number of packages in database: " . $count . "</h2>";

    if ($count > 0) {
        // Show first few packages
        $stmt = $conn->query("SELECT * FROM packages LIMIT 3");
        echo "<h2>Sample Packages:</h2>";
        echo "<pre>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "<h2>No packages found. Please run sample_data.sql to populate the database.</h2>";
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
