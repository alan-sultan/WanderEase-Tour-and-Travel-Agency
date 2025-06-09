<?php
require_once '../config/database.php';
require_once 'auth_check.php';

$conn = getDBConnection();

// Get filter parameters
$status = $_GET['status'] ?? 'all';
$sort = $_GET['sort'] ?? 'newest';

// Build query
$query = "
    SELECT b.*, p.title as package_title, p.destination, p.price as package_price,
           u.name as user_name, u.email as user_email
    FROM bookings b
    JOIN packages p ON b.package_id = p.id
    JOIN users u ON b.user_id = u.id
";

if ($status !== 'all') {
    $query .= " WHERE b.status = ?";
}

$query .= " ORDER BY " . ($sort === 'oldest' ? 'b.booking_date ASC' : 'b.booking_date DESC');

// Execute query
$stmt = $conn->prepare($query);
if ($status !== 'all') {
    $stmt->execute([$status]);
} else {
    $stmt->execute();
}
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - WonderEase Travel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="admin-info">
                <h3>Admin Panel</h3>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="bookings.php" class="active">Manage Bookings</a></li>
                    <li><a href="packages.php">Manage Packages</a></li>
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Manage Bookings</h1>

                <!-- Filters -->
                <div class="filters">
                    <form method="GET" class="filter-form">
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select name="status" id="status" onchange="this.form.submit()">
                                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="sort">Sort by:</label>
                            <select name="sort" id="sort" onchange="this.form.submit()">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                            </select>
                        </div>
                    </form>
                </div>
            </header>

            <section class="bookings">
                <?php if (empty($bookings)): ?>
                    <p>No bookings found.</p>
                <?php else: ?>
                    <div class="booking-list">
                        <table>
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Package</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Room Type</th>
                                    <th>Travelers</th>
                                    <th>Total Price</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td>#<?php echo $booking['id']; ?></td>
                                        <td>
                                            <div class="customer-info">
                                                <strong><?php echo htmlspecialchars($booking['user_name']); ?></strong>
                                                <span><?php echo htmlspecialchars($booking['user_email']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="package-info">
                                                <strong><?php echo htmlspecialchars($booking['package_title']); ?></strong>
                                                <span><?php echo htmlspecialchars($booking['destination']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($booking['room_type']); ?></td>
                                        <td><?php echo $booking['travelers']; ?></td>
                                        <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $booking['status']; ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($booking['status'] === 'confirmed'): ?>
                                                <span class="payment-status payment-<?php echo $booking['payment_status']; ?>">
                                                    <?php echo ucfirst($booking['payment_status']); ?>
                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-small">View</a>
                                                <?php if ($booking['status'] === 'pending'): ?>
                                                    <a href="update_booking.php?id=<?php echo $booking['id']; ?>&action=confirm" class="btn btn-small btn-success">Confirm</a>
                                                    <a href="update_booking.php?id=<?php echo $booking['id']; ?>&action=cancel" class="btn btn-small btn-danger">Cancel</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</body>

</html>