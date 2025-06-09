<?php
require_once '../includes/auth.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        throw new Exception('Please enter both email and password');
    }

    // Log the attempt (without password)
    error_log("Login attempt for email: " . $email);

    $result = loginUser($email, $password);

    if (!$result['success']) {
        error_log("Login failed for email: " . $email . " - " . $result['message']);
    } else {
        error_log("Login successful for email: " . $email);
    }

    echo json_encode($result);
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
