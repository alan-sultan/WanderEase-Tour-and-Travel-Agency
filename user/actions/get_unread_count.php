<?php
session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

$unread_count = 0;
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $unread_count = $stmt->fetchColumn();

    echo json_encode(['status' => 'success', 'unread_count' => $unread_count]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching unread count: ' . $e->getMessage()]);
}
exit();
