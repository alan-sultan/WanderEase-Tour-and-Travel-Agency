<?php
require_once '../includes/db.php';

// Default admin credentials
$admin_email = 'newadmin@wonderease.com';
$admin_password = 'Admin@123';
$admin_name = 'System Administrator';

try {
    // Establish a database connection
    $conn = new PDO('mysql:host=localhost;dbname=wonderease', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
    // Create the first admin
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("
        INSERT INTO users (name, email, password, role)
        VALUES (?, ?, ?, 'admin')
    ");
    $stmt->execute([$admin_name, $admin_email, $hashed_password]);
    
    echo "First admin user created successfully!<br>";
    echo "Email: " . htmlspecialchars($admin_email) . "<br>";
    echo "Password: " . htmlspecialchars($admin_password) . "<br>";
    echo "<br>Please <a href='login.php'>login here</a> and change your password immediately.";
    
} catch (PDOException $e) {
    die('Failed to create admin user: ' . $e->getMessage());
}
?> 