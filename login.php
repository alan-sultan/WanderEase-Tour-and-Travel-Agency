<?php
// filepath: c:\Users\ahmed\2nd Semister php project\WanderEase-Tour-and-Travel-Agency-main\Backend\login.php

session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php'); // Redirect to homepage or dashboard
    exit;
}

require_once '../config.php'; // Include your database connection file

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: ../login.html'); // Redirect back to the login page
        exit;
    }

    // Prepare SQL query to fetch user data
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Handle "Remember Me" functionality
            if (isset($_POST['remember_me'])) {
                setcookie('user_id', $user['id'], time() + (86400 * 30), "/"); // 30 days
                setcookie('username', $user['username'], time() + (86400 * 30), "/");
            }

            // Redirect to the previous page or dashboard
            $redirect_to = isset($_SESSION['redirect_to']) ? $_SESSION['redirect_to'] : '../dashboard.php';
            unset($_SESSION['redirect_to']);
            header("Location: $redirect_to");
            exit;
        } else {
            $_SESSION['error'] = 'Invalid username or password.';
        }
    } else {
        $_SESSION['error'] = 'Invalid username or password.';
    }

    // Redirect back to login page with error
    header('Location: ../login.html');
    exit;
}
?>
