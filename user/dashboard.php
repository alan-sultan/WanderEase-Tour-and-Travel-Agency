<?php
require_once '../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// Get current user data
$user = getCurrentUser();

// Get user's bookings
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT b.*, p.title, p.destination, p.price 
    FROM bookings b 
    JOIN packages p ON b.package_id = p.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's notifications
$stmt = $conn->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 1
"); // Limit to 1 for the latest notification on dashboard
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unread notification count for the badge
$unread_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread_count = $stmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WonderEase Travel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="bookings.php">My Bookings</a></li>
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
                    <h1>Dashboard</h1>
                    <div class="header-actions">
                        <div class="notifications-icon-wrapper">
                            <button class="notifications-toggle" id="notificationsToggleBtn">
                                <i class="fas fa-bell"></i>
                                <?php if ($unread_count > 0): ?>
                                    <span class="notification-badge"><?php echo $unread_count; ?></span>
                                <?php endif; ?>
                            </button>
                        </div>
                        <a href="../index.php" class="btn btn-primary">
                            <i class="fas fa-home"></i> Back to Home
                        </a>
                    </div>
                </div>
            </header>

            <div class="content-wrapper">
                <div class="notifications">
                    <div class="notifications-header">
                        <h3><i class="fas fa-bell"></i> Recent Notifications</h3>
                        <?php if (!empty($notifications)): ?>
                            <div class="notification-actions">
                                <button class="btn btn-sm btn-primary" id="viewAllNotificationsBtn">
                                    <i class="fas fa-list"></i> View all
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($notifications)): ?>
                        <div class="no-notifications">
                            <i class="fas fa-bell-slash"></i>
                        <p>No new notifications</p>
                            <small>We'll notify you when there's something important.</small>
                        </div>
                    <?php else: ?>
                        <?php
                        $latestNotification = $notifications[0]; // Get the very last (most recent) notification 
                        ?>
                        <div class="notification-latest <?php echo isset($latestNotification['type']) ? $latestNotification['type'] : 'info'; ?>">
                            <div class="notification-icon">
                                <?php
                                $icon = 'info-circle';
                                if (isset($latestNotification['type'])) {
                                    switch ($latestNotification['type']) {
                                        case 'success':
                                            $icon = 'check-circle';
                                            break;
                                        case 'warning':
                                            $icon = 'exclamation-triangle';
                                            break;
                                        case 'error':
                                            $icon = 'times-circle';
                                            break;
                                    }
                                }
                                ?>
                                <i class="fas fa-<?php echo $icon; ?>"></i>
                            </div>
                            <div class="notification-content">
                                <p><?php echo htmlspecialchars($latestNotification['message']); ?></p>
                                <small>
                                    <i class="far fa-clock"></i>
                                    <?php echo date('M d, Y', strtotime($latestNotification['created_at'])); ?>
                                </small>
                                <?php if (isset($latestNotification['action_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($latestNotification['action_url']); ?>" class="btn btn-sm btn-primary">
                                        <?php echo htmlspecialchars($latestNotification['action_text'] ?? 'View Details'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            <section class="bookings">
                <h2>Recent Bookings</h2>
                <?php if (empty($bookings)): ?>
                    <p>No bookings found. <a href="../packages.php">Browse packages</a></p>
                <?php else: ?>
                    <div class="booking-list">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="booking-card">
                                <h3><?php echo htmlspecialchars($booking['title']); ?></h3>
                                <p class="destination"><?php echo htmlspecialchars($booking['destination']); ?></p>
                                <div class="booking-details">
                                    <p><strong>Booking Date:</strong> <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></p>
                                    <p><strong>Travelers:</strong> <?php echo $booking['travelers']; ?></p>
                                    <p><strong>Total Price:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                                    <p><strong>Status:</strong> <span class="status-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span></p>
                                </div>
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <div class="booking-actions">
                                        <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel Booking</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
    </div>

    <!-- The Modal Structure for All Notifications -->
    <div id="allNotificationsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>All Notifications</h2>
                <button id="markAllReadBtn" class="btn btn-sm btn-light">Mark all as read</button>
                <span class="close-button">&times;</span>
            </div>
            <div class="modal-body">
                <div id="allNotificationsList" class="notifications-list"></div>
                <div id="noMoreNotifications" class="no-notifications" style="display:none;">
                    <i class="fas fa-bell-slash"></i>
                    <p>No more notifications to show.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationsToggleBtn = document.getElementById('notificationsToggleBtn');
            const viewAllNotificationsBtn = document.getElementById('viewAllNotificationsBtn');
            const modal = document.getElementById('allNotificationsModal');
            const closeButton = document.querySelector('.modal .close-button');
            const allNotificationsList = document.getElementById('allNotificationsList');
            const noMoreNotifications = document.getElementById('noMoreNotifications');
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            const notificationBadge = document.querySelector('.notification-badge');

            // Event listener for the bell icon (to open the modal)
            if (notificationsToggleBtn) {
                notificationsToggleBtn.addEventListener('click', function() {
                    modal.style.display = 'flex'; // Use flex to center content
                    fetchAllNotifications();
                });
            }

            // Event listener for the 'View All' button (also opens modal)
            if (viewAllNotificationsBtn) {
                viewAllNotificationsBtn.addEventListener('click', function() {
                    modal.style.display = 'flex'; // Use flex to center content
                    fetchAllNotifications();
                });
            }

            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    modal.style.display = 'none';
                    allNotificationsList.innerHTML = ''; // Clear content on close
                    updateNotificationBadge(); // Refresh badge on close
                });
            }

            // Close modal when clicking outside of the modal content
            window.addEventListener('click', function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                    allNotificationsList.innerHTML = ''; // Clear content on close
                    updateNotificationBadge(); // Refresh badge on close
                }
            });

            // Event listener for the 'Mark All as Read' button
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', async function() {
                    try {
                        const response = await fetch('actions/mark_all_read.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        });
                        const data = await response.json();

                        if (data.status === 'success') {
                            // Update UI: mark all currently displayed notifications as read
                            const notifications = document.querySelectorAll('#allNotificationsList .notification');
                            notifications.forEach(notificationElement => {
                                notificationElement.classList.remove('unread');
                                notificationElement.classList.add('read');
                                const markReadButton = notificationElement.querySelector('.mark-read');
                                if (markReadButton) {
                                    markReadButton.remove();
                                }
                            });
                            updateNotificationBadge(0); // Set badge to 0
                            console.log(data.message);
                        } else {
                            console.error('Error marking all as read:', data.message);
                            alert('Failed to mark all notifications as read: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Fetch error marking all as read:', error);
                        alert('Network error. Could not mark all notifications as read.');
                    }
                });
            }

            async function fetchAllNotifications() {
                try {
                    const response = await fetch('actions/get_all_notifications.php');
                    const data = await response.json();

                    allNotificationsList.innerHTML = ''; // Clear existing content
                    noMoreNotifications.style.display = 'none';

                    if (data.status === 'success' && data.notifications.length > 0) {
                        data.notifications.forEach(notification => {
                            const notificationElement = document.createElement('div');
                            notificationElement.classList.add('notification', notification.is_read ? 'read' : 'unread', notification.type || 'info');

                            let icon = 'info-circle';
                            switch (notification.type) {
                                case 'success':
                                    icon = 'check-circle';
                                    break;
                                case 'warning':
                                    icon = 'exclamation-triangle';
                                    break;
                                case 'error':
                                    icon = 'times-circle';
                                    break;
                            }

                            notificationElement.innerHTML = `
                                <div class="notification-icon">
                                    <i class="fas fa-${icon}"></i>
                                </div>
                                <div class="notification-content">
                                    <p>${notification.message}</p>
                                    <small>
                                        <i class="far fa-clock"></i>
                                        ${new Date(notification.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                                    </small>
                                    ${notification.action_url ? `<a href="${notification.action_url}" class="btn btn-sm btn-primary">${notification.action_text || 'View Details'}</a>` : ''}
                                </div>
                            `;

                            if (!notification.is_read) {
                                notificationElement.style.cursor = 'pointer'; // Indicate clickable
                                notificationElement.addEventListener('click', function(event) {
                                    // Prevent clicks on action links/buttons inside from marking as read
                                    if (event.target.tagName === 'A' || event.target.tagName === 'BUTTON') {
                                        return; // Do nothing if a link or button was clicked
                                    }
                                    markNotificationRead(notification.id, notificationElement); // Pass the element for UI update
                                });
                            }

                            allNotificationsList.appendChild(notificationElement);
                        });
                    } else {
                        noMoreNotifications.style.display = 'block';
                    }
                } catch (error) {
                    console.error('Error fetching notifications:', error);
                    noMoreNotifications.style.display = 'block';
                    noMoreNotifications.querySelector('p').textContent = 'Failed to load notifications.';
                }
            }

            async function markNotificationRead(id, element) {
                try {
                    const response = await fetch('actions/mark_single_read.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            notification_id: id
                        })
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        if (element) {
                            element.classList.remove('unread');
                            element.classList.add('read');
                            element.style.cursor = 'default'; // Change cursor back
                            // Remove the click listener after it's read to prevent multiple calls
                            // For simplicity, we'll just re-render or let it be if it's already read
                            element.removeEventListener('click', arguments.callee); // Remove the event listener for this specific notification
                        }
                        updateNotificationBadge(); // Update badge after single read
                    } else {
                        console.error('Error:', data.message);
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                }
            }

            // Function to update the notification badge count
            async function updateNotificationBadge() {
                try {
                    const response = await fetch('actions/get_unread_count.php'); // Assuming a new endpoint for just the count
                    const data = await response.json();

                    if (data.status === 'success') {
                        const count = parseInt(data.unread_count);
                        if (notificationBadge) {
                            if (count <= 0) {
                                notificationBadge.remove();
                            } else {
                                notificationBadge.textContent = count;
                                // Re-add if it was removed earlier
                                if (!document.querySelector('.notification-badge')) {
                                    const notificationsToggleBtn = document.getElementById('notificationsToggleBtn');
                                    const span = document.createElement('span');
                                    span.classList.add('notification-badge');
                                    span.textContent = count;
                                    notificationsToggleBtn.appendChild(span);
                                }
                            }
                        } else if (count > 0) {
                            // If badge doesn't exist but count > 0, create it
                            const notificationsToggleBtn = document.getElementById('notificationsToggleBtn');
                            if (notificationsToggleBtn) {
                                const span = document.createElement('span');
                                span.classList.add('notification-badge');
                                span.textContent = count;
                                notificationsToggleBtn.appendChild(span);
                            }
                        }
                    } else {
                        console.error('Error fetching unread count:', data.message);
                    }
                } catch (error) {
                    console.error('Fetch error updating badge:', error);
                }
            }
        });
    </script>
</body>

</html>
