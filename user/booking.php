<?php
require_once '../config/database.php';
require_once 'auth_check.php';

$conn = getDBConnection();

// Get user's bookings
$stmt = $conn->prepare("
    SELECT b.*, p.title as package_title, p.destination, p.price as package_price
    FROM bookings b
    JOIN packages p ON b.package_id = p.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - WonderEase Travel</title>
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
            <h2>My Bookings</h2>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Your booking has been confirmed!</div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (empty($bookings)): ?>
                <div class="no-bookings">
                    <p>You haven't made any bookings yet.</p>
                    <a href="packages.php" class="btn btn-primary">Browse Packages</a>
                </div>
            <?php else: ?>
                <div class="bookings-list">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <h3><?php echo htmlspecialchars($booking['package_title']); ?></h3>
                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>

                            <div class="booking-details">
                                <div class="detail-row">
                                    <div class="detail-item">
                                        <label>Destination:</label>
                                        <span><?php echo htmlspecialchars($booking['destination']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Booking Date:</label>
                                        <span><?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></span>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-item">
                                        <label>Travelers:</label>
                                        <span><?php echo $booking['travelers']; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Total Price:</label>
                                        <span>$<?php echo number_format($booking['total_price'], 2); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="booking-actions">
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <a href="payment.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-primary">Complete Payment</a>
                                    <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel</a>
                                <?php elseif ($booking['status'] === 'confirmed'): ?>
                                    <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="btn">View Details</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
