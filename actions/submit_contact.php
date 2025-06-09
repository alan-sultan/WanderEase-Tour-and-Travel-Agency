<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php'; // Include auth.php to check login status

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars(trim($_POST['subject'] ?? ''), ENT_QUOTES, 'UTF-8');
$message_content = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8'); // Renamed to avoid conflict with table name

if (empty($name) || empty($email) || empty($subject) || empty($message_content)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid email address.']);
    exit();
}

// Determine user_id: if logged in, use session user_id, otherwise NULL
$user_id = isLoggedIn() ? $_SESSION['user_id'] : NULL;

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("INSERT INTO support_messages (user_id, name, email, subject, message) VALUES (:user_id, :name, :email, :subject, :message_content)");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':message_content', $message_content);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'Your message has been sent successfully!']);
} catch (PDOException $e) {
    error_log("Contact form submission error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to send message. Please try again later.']);
}
