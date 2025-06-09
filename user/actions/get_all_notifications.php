<?php
session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php'; // Use the correct database config

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

$notifications = [];
try {
    $pdo = getDBConnection(); // Get the PDO connection
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'notifications' => $notifications]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching notifications: ' . $e->getMessage()]);
}
exit();
