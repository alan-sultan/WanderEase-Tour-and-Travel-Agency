<?php
require_once 'auth_check.php';

// Get current admin user
$user = getCurrentUser();

// Check if message ID is provided
if (!isset($_GET['id'])) {
    header('Location: support.php');
    exit;
}

$message_id = (int)$_GET['id'];
$conn = getDBConnection();

// Fetch support message details
$stmt = $conn->prepare("
    SELECT sm.*, u.name as registered_user_name, u.email as registered_user_email
    FROM support_messages sm
    LEFT JOIN users u ON sm.user_id = u.id
    WHERE sm.id = ?
");
$stmt->execute([$message_id]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    $_SESSION['error'] = 'Support message not found.';
    header('Location: support.php');
    exit;
}

// Fetch responses for this message
$stmt = $conn->prepare("
    SELECT sr.*, u.name as responder_name
    FROM support_responses sr
    JOIN users u ON sr.responder_id = u.id
    WHERE sr.message_id = ?
    ORDER BY sr.created_at ASC
");
$stmt->execute([$message_id]);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle new response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['response_content'])) {
    $response_content = trim($_POST['response_content']);
    $new_status = $_POST['status'] ?? $message['status'];

    if (empty($response_content)) {
        $_SESSION['error'] = 'Response cannot be empty.';
    } else {
        try {
            $conn->beginTransaction();

            // Insert new response
            $stmt = $conn->prepare("INSERT INTO support_responses (message_id, responder_id, response) VALUES (?, ?, ?)");
            $stmt->execute([$message_id, $_SESSION['user_id'], $response_content]);

            // Update message status
            $stmt = $conn->prepare("UPDATE support_messages SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$new_status, $message_id]);

            // Create notification for the user (only if it's a registered user)
            if (!empty($message['user_id'])) {
                $notification_message = "Your support request (#{$message_id}) has been updated. Status: " . ucfirst($new_status) . ".";
                if ($new_status === 'closed') {
                    $notification_message = "Your support request (#{$message_id}) has been closed. Status: " . ucfirst($new_status) . ".";
                }
                $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
                $stmt->execute([$message['user_id'], $notification_message, 'info']);
            }

            $conn->commit();

            $_SESSION['success'] = 'Response sent and message updated successfully.';
            header('Location: view_support.php?id=' . $message_id);
            exit;
        } catch (PDOException $e) {
            $conn->rollBack();
            $_SESSION['error'] = 'Failed to send response or update message: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Support Message - Admin</title>
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
                <h1>Support Message Details</h1>
                <a href="support.php" class="btn btn-secondary btn-small">Back to Support Messages</a>
            </header>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <section class="detail-section">
                <h2>Message #<?php echo htmlspecialchars($message['id']); ?> - <?php echo htmlspecialchars($message['subject']); ?></h2>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>From User:</label>
                        <span>
                            <?php
                            if (!empty($message['user_id'])) {
                                echo htmlspecialchars($message['registered_user_name']) . ' (' . htmlspecialchars($message['registered_user_email']) . ')';
                            } else {
                                echo htmlspecialchars($message['name']) . ' (' . htmlspecialchars($message['email']) . ') (Guest)';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <label>Date:</label>
                        <span><?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Status:</label>
                        <span><span class="status-badge status-<?php echo strtolower($message['status']); ?>"><?php echo htmlspecialchars(ucfirst($message['status'])); ?></span></span>
                    </div>
                    <div class="detail-item full-width">
                        <label>Message:</label>
                        <div class="detail-content">
                            <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="recent-section">
                <h2>Responses</h2>
                <?php if (empty($responses)): ?>
                    <p>No responses yet for this message.</p>
                <?php else: ?>
                    <div class="responses-list">
                        <?php foreach ($responses as $response): ?>
                            <div class="response-item">
                                <div class="response-header">
                                    <strong><?php echo htmlspecialchars($response['responder_name']); ?></strong>
                                    <small><?php echo date('M d, Y H:i', strtotime($response['created_at'])); ?></small>
                                </div>
                                <div class="response-content">
                                    <p><?php echo nl2br(htmlspecialchars($response['response'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="recent-section">
                <h2>Send Response</h2>
                <form method="POST" class="admin-form">
                    <div class="form-group">
                        <label for="response_content">Your Response</label>
                        <textarea id="response_content" name="response_content" rows="8" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="status">Update Status</label>
                        <select id="status" name="status">
                            <option value="open" <?php echo ($message['status'] === 'open') ? 'selected' : ''; ?>>Open</option>
                            <option value="in_progress" <?php echo ($message['status'] === 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="closed" <?php echo ($message['status'] === 'closed') ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Response</button>
                </form>
            </section>
        </div>
    </div>
</body>

</html>