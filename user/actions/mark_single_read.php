<?php
session_start();
require_once '../../includes/auth.php'; // Corrected path
require_once '../../config/database.php'; // Corrected path

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$notification_id = $data['notification_id'] ?? null;

if (!is_numeric($notification_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid notification ID.']);
    exit();
}

try {
    $pdo = getDBConnection(); // Initialize the $pdo connection
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $notification_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Notification marked as read.']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error marking notification as read: ' . $e->getMessage()]);
}
exit();
