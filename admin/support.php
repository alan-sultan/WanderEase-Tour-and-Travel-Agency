<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'auth_check.php';

// Get current admin user
$user = getCurrentUser();

// Get all support messages
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT sm.*, u.name as registered_user_name, u.email as registered_user_email
    FROM support_messages sm
    LEFT JOIN users u ON sm.user_id = u.id
    ORDER BY sm.created_at DESC
");
$stmt->execute();
$support_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Support Messages - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="admin-info">
                <h3>Admin Panel</h3>
                <p><?php echo htmlspecialchars($user['name']); ?></p>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="bookings.php"><i class="fas fa-calendar-check"></i> Manage Bookings</a></li>
                    <li><a href="packages.php"><i class="fas fa-suitcase"></i> Manage Packages</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li><a href="support.php" class="active"><i class="fas fa-headset"></i> Support Messages</a></li>
                    <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Support Messages</h1>
            </header>

            <section class="recent-section">
                <?php if (empty($support_messages)): ?>
                    <p>No support messages received.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($support_messages as $message): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message['id']); ?></td>
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
                                        <td><span class="status-badge status-<?php echo strtolower($message['status']); ?>"><?php echo htmlspecialchars(ucfirst($message['status'])); ?></span></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></td>
                                        <td>
                                            <a href="view_support.php?id=<?php echo $message['id']; ?>" class="btn btn-small">View/Respond</a>
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