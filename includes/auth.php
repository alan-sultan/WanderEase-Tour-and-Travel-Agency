<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// Function to register a new user
function registerUser($name, $email, $phone, $password)
{
    $conn = getDBConnection();

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Email already exists'];
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    try {
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $hashedPassword]);
        return ['success' => true, 'message' => 'Registration successful'];
    } catch (   Exception $e) {
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

// Function to login user
function loginUser($email, $password)
{
    try {
        $conn = getDBConnection();

        if (!$conn) {
            error_log("Database connection failed in loginUser");
            return ['success' => false, 'message' => 'Database connection error'];
        }

        // Get user by email
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if (!$stmt) {
            error_log("Failed to prepare statement: " . print_r($conn->errorInfo(), true));
            return ['success' => false, 'message' => 'Database error'];
        }

        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            error_log("No user found for email: " . $email);
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        if (!password_verify($password, $user['password'])) {
            error_log("Invalid password for email: " . $email);
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        error_log("Login successful for user: " . $user['id']);
        return ['success' => true, 'message' => 'Login successful'];
    } catch (PDOException $e) {
        error_log("Database error in loginUser: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    } catch (Exception $e) {
        error_log("Unexpected error in loginUser: " . $e->getMessage());
        return ['success' => false, 'message' => 'An unexpected error occurred'];
    }
}

// Function to check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Function to check user role
function getUserRole()
{
    return $_SESSION['user_role'] ?? null;
}

// Function to check if user is admin
function isAdmin()
{
    return getUserRole() === 'admin';
}

// Function to check if user is staff
function isStaff()
{
    return getUserRole() === 'staff';
}

// Function to logout user
function logoutUser()
{
    session_unset();
    session_destroy();
    return ['success' => true, 'message' => 'Logged out successfully'];
}

// Function to get current user data
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, name, email, phone, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
