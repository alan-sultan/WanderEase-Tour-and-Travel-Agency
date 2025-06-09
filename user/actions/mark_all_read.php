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

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'All notifications marked as read.']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error marking all notifications as read: ' . $e->getMessage()]);
}
exit();
