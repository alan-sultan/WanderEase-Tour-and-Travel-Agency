<?php
$host = "localhost";
$dbname = "wander_ease";
$username = "root";  // Default in XAMPP
$password = "";      // Leave empty in XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
