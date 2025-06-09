<?php
require_once 'auth_check.php';

// Get current user data
$user = getCurrentUser();

// Get newsletter subscribers
$conn = getDBConnection();
$stmt = $conn->query("SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC");
$subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$greeting = '';
$hour = date('G');
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
    <title>Newsletter Subscribers - WonderEase Travel Admin</title>
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
                    <li><a href="bookings.php"><i class="fas fa-calendar-check"></i> Manage Bookings</a></li>
                    <li><a href="packages.php"><i class="fas fa-suitcase"></i> Manage Packages</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li><a href="support.php"><i class="fas fa-headset"></i> Support Messages</a></li>
                    <li><a href="newsletter.php" class="active"><i class="fas fa-envelope"></i> Newsletter Subscribers</a></li>
                    <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <div class="header-content">
                    <h1>Newsletter Subscribers</h1>
                    <div class="welcome-message">
                        <p><?php echo $greeting; ?>, <?php echo htmlspecialchars($user['name']); ?>!</p>
                        <p class="date"><?php echo date('l, F j, Y'); ?></p>
                    </div>
                </div>
            </header>

            <section class="section-block">
                <h2>All Newsletter Subscribers</h2>
                <?php if (empty($subscribers)): ?>
                    <p>No newsletter subscribers found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Subscription Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subscribers as $subscriber): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subscriber['id']); ?></td>
                                        <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($subscriber['subscribed_at'])); ?></td>
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