<?php
require_once '../config/database.php';
require_once 'auth_check.php';

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header('Location: bookings.php');
    exit;
}

$booking_id = (int)$_GET['id'];
$action = $_GET['action'];

if (!in_array($action, ['confirm', 'cancel'])) {
    header('Location: bookings.php');
    exit;
}

$conn = getDBConnection();

try {
    // Get booking details
    $stmt = $conn->prepare("
        SELECT b.*, u.id as user_id, p.title as package_title
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN packages p ON b.package_id = p.id
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception('Booking not found');
    }

    // Update booking status
    $new_status = $action === 'confirm' ? 'confirmed' : 'cancelled';
    $payment_status = $action === 'confirm' ? 'paid' : 'refunded';

    $stmt = $conn->prepare("
        UPDATE bookings 
        SET status = ?,
            payment_status = ?
        WHERE id = ?
    ");
    $stmt->execute([$new_status, $payment_status, $booking_id]);

    // Create notification
    $message = $action === 'confirm'
        ? "Your booking for {$booking['package_title']} has been confirmed!"
        : "Your booking for {$booking['package_title']} has been cancelled.";

    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, message, type)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        $booking['user_id'],
        $message,
        $action === 'confirm' ? 'booking_confirmation' : 'booking_cancellation'
    ]);

    $_SESSION['success'] = "Booking has been " . ($action === 'confirm' ? 'confirmed' : 'cancelled') . " successfully.";
} catch (Exception $e) {
    $_SESSION['error'] = 'Failed to update booking status: ' . $e->getMessage();
}

header('Location: bookings.php');
exit;
