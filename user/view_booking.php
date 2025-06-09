<?php
require_once '../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// Get current user data
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
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
$stmt = $conn->prepare("
    SELECT b.*, p.title as package_title, p.destination, p.price as package_price,
           u.name as user_name, u.email as user_email, u.phone as user_phone
    FROM bookings b
    JOIN packages p ON b.package_id = p.id
    JOIN users u ON b.user_id = u.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header('Location: bookings.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - WonderEase Travel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="user-info">
                <h3>Welcome, <?php echo htmlspecialchars($user['name']); ?></h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="bookings.php" class="active">My Bookings</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="support.php">Support</a></li>
                    <li><a href="packages.php">Packages</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
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

            <div class="booking-details">
                <div class="detail-section">
                    <h2>Booking ID: #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></h2>
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
                    </div>
                <?php endif; ?>

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
                        <?php if ($booking['user_phone']): ?>
                            <div class="detail-item">
                                <label>Phone:</label>
                                <span><?php echo htmlspecialchars($booking['user_phone']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($booking['status'] === 'pending'): ?>
                    <div class="booking-actions">
                        <a href="payment.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-primary">Complete Payment</a>
                        <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel Booking</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html> 
