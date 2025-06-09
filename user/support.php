<?php
require_once '../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// Get current user data
$user = getCurrentUser();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate input
    if (empty($subject) || empty($message)) {
        $error = 'Subject and message are required';
    } else {
        $conn = getDBConnection();

        try {
            // Insert support request into database
            $stmt = $conn->prepare("INSERT INTO support_messages (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $user['name'],
                $user['email'],
                $subject,
                $message
            ]);

            $success = 'Your support request has been submitted successfully';
        } catch (Exception $e) {
            $error = 'An error occurred while submitting your request. Please try again later.';
        }
    }
}

// Get user's previous support messages and their responses
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT sm.*, 
           (SELECT COUNT(*) FROM support_responses WHERE message_id = sm.id) as response_count
    FROM support_messages sm
    WHERE sm.user_id = ?
    ORDER BY sm.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - WonderEase Travel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <li><a href="bookings.php">My Bookings</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="support.php" class="active">Support</a></li>
                    <li><a href="packages.php">Packages</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Support</h1>
            </header>

            <section class="support-section">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" class="support-form">
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>

                <!-- Previous Messages Section -->
                <div class="previous-messages">
                    <h2>Your Previous Messages</h2>
                    <?php if (empty($messages)): ?>
                        <p>No previous messages found.</p>
                    <?php else: ?>
                        <div class="messages-list">
                            <?php foreach ($messages as $msg): ?>
                                <div class="message-card">
                                    <div class="message-header">
                                        <h3><?php echo htmlspecialchars($msg['subject']); ?></h3>
                                        <span class="status-badge status-<?php echo strtolower($msg['status']); ?>">
                                            <?php echo ucfirst($msg['status']); ?>
                                        </span>
                                    </div>
                                    <div class="message-content">
                                        <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                    </div>
                                    <div class="message-footer">
                                        <small>Sent on: <?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></small>
                                        <button class="btn btn-small view-responses" data-message-id="<?php echo $msg['id']; ?>">
                                            View Responses (<?php echo $msg['response_count']; ?>)
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Modal for viewing responses -->
    <div id="responsesModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Support Responses</h2>
                <span class="close-button">&times;</span>
            </div>
            <div class="modal-body" id="responsesContent">
                <!-- Responses will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('responsesModal');
            const closeBtn = modal.querySelector('.close-button');
            const responsesContent = document.getElementById('responsesContent');

            // Close modal when clicking the X
            closeBtn.onclick = function() {
                modal.style.display = "none";
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            // Handle view responses buttons
            document.querySelectorAll('.view-responses').forEach(button => {
                button.onclick = function() {
                    const messageId = this.dataset.messageId;
                    fetch(`get_responses.php?message_id=${messageId}`)
                        .then(response => response.json())
                        .then(data => {
                            let html = '';
                            if (data.responses.length === 0) {
                                html = '<p>No responses yet.</p>';
                            } else {
                                data.responses.forEach(response => {
                                    html += `
                                    <div class="response-item">
                                        <div class="response-header">
                                            <strong>Admin Response</strong>
                                            <small>${response.created_at}</small>
                                        </div>
                                        <div class="response-content">
                                            <p>${response.response}</p>
                                        </div>
                                    </div>
                                `;
                                });
                            }
                            responsesContent.innerHTML = html;
                            modal.style.display = "block";
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            responsesContent.innerHTML = '<p>Error loading responses. Please try again.</p>';
                        });
                };
            });
        });
    </script>
</body>

</html>
