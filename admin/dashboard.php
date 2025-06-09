<?php
require_once 'auth_check.php';

// Get current user data
$user = getCurrentUser();

// Get statistics
$conn = getDBConnection();

// Total bookings
$stmt = $conn->query("SELECT COUNT(*) FROM bookings");
$total_bookings = $stmt->fetchColumn();

// Pending bookings
$stmt = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'");
$pending_bookings = $stmt->fetchColumn();

// Total users
$stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$total_users = $stmt->fetchColumn();

// Total packages
$stmt = $conn->query("SELECT COUNT(*) FROM packages");
$total_packages = $stmt->fetchColumn();

// Total newsletter subscribers
$stmt = $conn->query("SELECT COUNT(*) FROM newsletter_subscribers");
$total_subscribers = $stmt->fetchColumn();

// Recent bookings
$stmt = $conn->query("
    SELECT b.*, u.name as user_name, p.title as package_title, p.destination
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN packages p ON b.package_id = p.id
    ORDER BY b.created_at DESC
    LIMIT 5
");
$recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent support messages
$stmt = $conn->query("
    SELECT m.*, u.name as registered_user_name, u.email as registered_user_email
    FROM support_messages m
    LEFT JOIN users u ON m.user_id = u.id
    WHERE m.id NOT IN (SELECT message_id FROM support_responses)
    ORDER BY m.created_at DESC
    LIMIT 5
");
$pending_support = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current time for greeting
$hour = date('G');
$greeting = '';
if ($hour >= 5 && $hour < 12) {
    $greeting = 'Good morning';
} elseif ($hour >= 12 && $hour < 18) {
    $greeting = 'Good afternoon';
} else {
    $greeting = 'Good evening';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - WonderEase Travel</title>
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
                    <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="bookings.php"><i class="fas fa-calendar-check"></i> Manage Bookings</a></li>
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
                    <h1>Admin Dashboard</h1>
                    <div class="welcome-message">
                        <p><?php echo $greeting; ?>, <?php echo htmlspecialchars($user['name']); ?>!</p>
                        <p class="date"><?php echo date('l, F j, Y'); ?></p>
                    </div>
                </div>
            </header>

            <!-- Quick Actions -->
            <section class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="bookings.php?status=pending" class="btn btn-primary">
                        <i class="fas fa-clock"></i> View Pending Bookings
                    </a>
                    <a href="packages.php?action=new" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add New Package
                    </a>
                    <a href="support.php" class="btn btn-warning">
                        <i class="fas fa-headset"></i> Check Support Messages
                    </a>
                </div>
            </section>

            <!-- Statistics -->
            <section class="stats-grid">
                <a href="bookings.php" class="stat-card">
                    <i class="fas fa-calendar-check stat-icon"></i>
                    <h3>Total Bookings</h3>
                    <p class="stat-number"><?php echo $total_bookings; ?></p>
                </a>
                <a href="bookings.php?status=pending" class="stat-card">
                    <i class="fas fa-clock stat-icon"></i>
                    <h3>Pending Bookings</h3>
                    <p class="stat-number"><?php echo $pending_bookings; ?></p>
                </a>
                <a href="users.php" class="stat-card">
                    <i class="fas fa-users stat-icon"></i>
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo $total_users; ?></p>
                </a>
                <a href="packages.php" class="stat-card">
                    <i class="fas fa-suitcase stat-icon"></i>
                    <h3>Total Packages</h3>
                    <p class="stat-number"><?php echo $total_packages; ?></p>
                </a>
                <a href="newsletter.php" class="stat-card">
                    <i class="fas fa-envelope stat-icon"></i>
                    <h3>Newsletter Subscribers</h3>
                    <p class="stat-number"><?php echo $total_subscribers; ?></p>
                </a>
            </section>

            <!-- Recent Bookings -->
            <section class="recent-section">
                <div class="section-header">
                    <h2>Recent Bookings</h2>
                    <a href="bookings.php" class="btn btn-small">View All</a>
                </div>
                <?php if (empty($recent_bookings)): ?>
                    <p>No recent bookings</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Package</th>
                                    <th>Destination</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['package_title']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['destination']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                        <td><span class="status-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                                        <td>
                                            <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-small">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Pending Support -->
            <section class="recent-section">
                <div class="section-header">
                    <h2>Pending Support Messages</h2>
                    <a href="support.php" class="btn btn-small">View All</a>
                </div>
                <?php if (empty($pending_support)): ?>
                    <p>No pending support messages</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_support as $message): ?>
                                    <tr>
                                        <td>
                                            <?php
                                            if (!empty($message['user_id'])) {
                                                echo htmlspecialchars($message['registered_user_name']);
                                            } else {
                                                echo htmlspecialchars($message['name']) . ' (Guest)';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($message['created_at'])); ?></td>
                                        <td>
                                            <a href="view_support.php?id=<?php echo $message['id']; ?>" class="btn btn-small">Respond</a>
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