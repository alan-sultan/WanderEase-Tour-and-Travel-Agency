<?php
require_once 'auth_check.php';

// Get admin user information
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'admin'");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: ../auth/login.php');
    exit;
}

// Check if booking ID is provided
if (!isset($_GET['id'])) {
    header('Location: bookings.php');
    exit;
}

$booking_id = (int)$_GET['id'];

// Get booking details
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
           p.title as package_title, p.destination, p.price as package_price
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN packages p ON b.package_id = p.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    $_SESSION['error'] = 'Booking not found.';
    header('Location: bookings.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];

    try {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $booking_id]);

        // Create notification
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, message)
            VALUES (?, ?)
        ");
        $stmt->execute([
            $booking['user_id'],
            "Your booking for {$booking['package_title']} has been {$new_status}."
        ]);

        $_SESSION['success'] = 'Booking status updated successfully.';
        header("Location: view_booking.php?id=$booking_id");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to update booking status.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Booking - WonderEase Travel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="user-info">
                <h3>Admin Panel</h3>
                <p><?php echo htmlspecialchars($user['name']); ?></p>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="bookings.php" class="active"><i class="fas fa-calendar-check"></i> Manage Bookings</a></li>
                    <li><a href="packages.php"><i class="fas fa-suitcase"></i> Manage Packages</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li><a href="support.php"><i class="fas fa-headset"></i> Support Messages</a></li>
                    <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <div class="header-content">
                    <h1>Booking Details</h1>
                    <a href="bookings.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Bookings
                    </a>
                </div>
            </header>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success'];
                                                    unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error'];
                                                unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="booking-details">
                <div class="detail-section">
                    <h2>Booking ID: #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></h2>
                </div>

                <div class="detail-section">
                    <h2>Customer Information</h2>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Name:</label>
                            <span><?php echo htmlspecialchars($booking['user_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($booking['user_email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Phone:</label>
                            <span><?php echo htmlspecialchars($booking['user_phone']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Package Information</h2>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Package Title:</label>
                            <span><?php echo htmlspecialchars($booking['package_title']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Destination:</label>
                            <span><?php echo htmlspecialchars($booking['destination']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Package Price:</label>
                            <span>$<?php echo number_format($booking['package_price'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Booking Information</h2>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Booking Date:</label>
                            <span><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Check-in Date:</label>
                            <span><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Check-out Date:</label>
                            <span><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Number of Travelers:</label>
                            <span><?php echo $booking['travelers']; ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Total Price:</label>
                            <span>$<?php echo number_format($booking['total_price'], 2); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Status:</label>
                            <span class="status-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Booked On:</label>
                            <span><?php echo date('M d, Y H:i', strtotime($booking['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Accommodation Details</h2>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Room Type:</label>
                            <span><?php echo htmlspecialchars($booking['room_type'] ?? 'Standard'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Number of Rooms:</label>
                            <span><?php echo $booking['number_of_rooms']; ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Number of Guests:</label>
                            <span><?php echo $booking['travelers']; ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($booking['status'] === 'confirmed'): ?>
                    <div class="detail-section">
                        <h2>Payment Information</h2>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Payment Status:</label>
                                <span class="payment-status payment-<?php echo $booking['payment_status']; ?>">
                                    <?php echo ucfirst($booking['payment_status']); ?>
                                </span>
                            </div>
                            <?php if ($booking['payment_method']): ?>
                                <div class="detail-item">
                                    <label>Payment Method:</label>
                                    <span><?php echo ucfirst(str_replace('_', ' ', $booking['payment_method'])); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($booking['payment_status'] === 'paid'): ?>
                            <form method="POST" action="process_refund.php" class="refund-form" onsubmit="return confirm('Are you sure you want to process this refund?');">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <div class="form-group">
                                    <label for="refund_amount">Refund Amount:</label>
                                    <input type="number" name="refund_amount" id="refund_amount"
                                        value="<?php echo $booking['total_price']; ?>"
                                        step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="refund_notes">Refund Notes:</label>
                                    <textarea name="refund_notes" id="refund_notes" rows="3"
                                        placeholder="Enter reason for refund..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger">Process Refund</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($booking['refund_status'] !== 'none'): ?>
                    <div class="detail-section">
                        <h2>Refund Information</h2>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Refund Status:</label>
                                <span class="refund-status refund-<?php echo $booking['refund_status']; ?>">
                                    <?php echo ucfirst($booking['refund_status']); ?>
                                </span>
                            </div>
                            <?php if ($booking['refund_amount']): ?>
                                <div class="detail-item">
                                    <label>Refund Amount:</label>
                                    <span>$<?php echo number_format($booking['refund_amount'], 2); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($booking['refund_notes']): ?>
                                <div class="detail-item full-width">
                                    <label>Refund Notes:</label>
                                    <span><?php echo nl2br(htmlspecialchars($booking['refund_notes'])); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Transaction History -->
                <div class="detail-section">
                    <h2>Transaction History</h2>
                    <?php
                    $stmt = $conn->prepare("
                        SELECT * FROM transactions 
                        WHERE booking_id = ? 
                        ORDER BY created_at DESC
                    ");
                    $stmt->execute([$booking['id']]);
                    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php if (empty($transactions)): ?>
                        <p>No transactions found.</p>
                    <?php else: ?>
                        <div class="transaction-list">
                            <?php foreach ($transactions as $transaction): ?>
                                <div class="transaction-item">
                                    <div class="transaction-header">
                                        <span class="transaction-type <?php echo $transaction['type']; ?>">
                                            <?php echo ucfirst($transaction['type']); ?>
                                        </span>
                                        <span class="transaction-date">
                                            <?php echo date('M d, Y H:i', strtotime($transaction['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="transaction-details">
                                        <p><strong>Amount:</strong> $<?php echo number_format($transaction['amount'], 2); ?></p>
                                        <p><strong>Status:</strong> <?php echo ucfirst($transaction['status']); ?></p>
                                        <p><strong>Reference:</strong> <?php echo htmlspecialchars($transaction['transaction_reference']); ?></p>
                                        <?php if ($transaction['notes']): ?>
                                            <p><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($transaction['notes'])); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="detail-section">
                    <h2>Special Requests</h2>
                    <div class="detail-content">
                        <p><?php echo nl2br(htmlspecialchars($booking['special_requests'] ?? 'No special requests')); ?></p>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Cancellation Policy</h2>
                    <div class="detail-content">
                        <p><?php echo nl2br(htmlspecialchars($booking['cancellation_policy'] ?? 'Standard cancellation policy applies.')); ?></p>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Admin Notes</h2>
                    <div class="detail-content">
                        <p><?php echo nl2br(htmlspecialchars($booking['admin_notes'] ?? 'No admin notes')); ?></p>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Update Status</h2>
                    <form method="POST" class="status-form">
                        <div class="form-group">
                            <select name="status" class="status-select">
                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>