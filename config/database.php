<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
// Database configuration
if (!defined('DB_HOST')) define('DB_HOST', $_ENV['DB_HOST']);
if (!defined('DB_USER')) define('DB_USER', $_ENV['DB_USER']);
if (!defined('DB_PASS')) define('DB_PASS', $_ENV['DB_PASS']);
if (!defined('DB_NAME')) define('DB_NAME', $_ENV['DB_NAME']);

// Create database connection
function getDBConnection()
{
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
        );
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
