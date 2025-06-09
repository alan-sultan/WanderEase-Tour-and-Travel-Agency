 <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid email address.']);
    exit();
}

try {
    $pdo = getDBConnection();

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM newsletter_subscribers WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'You are already subscribed to our newsletter.']);
        exit();
    }

    // Insert new subscriber
    $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email) VALUES (:email)");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'Thank you for subscribing!']);
} catch (PDOException $e) {
    // Log the error for debugging purposes (optional)
    error_log("Newsletter subscription error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to subscribe. Please try again later.']);
}
