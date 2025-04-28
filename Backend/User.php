<?php
// filepath: c:\Users\ahmed\2nd Semister php project\WanderEase-Tour-and-Travel-Agency-main\Backend\User.php

require_once '../config.php'; // Include database connection
require_once 'sanitize.php'; // Include sanitization functions

class User {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Authenticate user credentials
    public function authenticate($username, $password) {
        $username = sanitizeString($username);

        $stmt = $this->conn->prepare("SELECT id, username, password, is_admin FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                return $user; // Return user details on successful authentication
            }
        }
        return false; // Authentication failed
    }

    // Register a new user
    public function register($username, $email, $password) {
        $username = sanitizeString($username);
        $email = sanitizeEmail($email);
        $password = sanitizePassword($password);

        if (!$username || !$email || !$password) {
            return ['status' => 'error', 'message' => 'Invalid input.'];
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'User registered successfully.'];
        } else {
            return ['status' => 'error', 'message' => 'Failed to register user.'];
        }
    }

    // Fetch user details by ID
    public function getUserById($id) {
        $id = validateInteger($id);

        $stmt = $this->conn->prepare("SELECT id, username, email, is_admin FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        return null; // User not found
    }

    // Check if a user is an admin
    public function isAdmin($userId) {
        $user = $this->getUserById($userId);
        return $user && $user['is_admin'] == 1;
    }

    // Update user password
    public function updatePassword($userId, $newPassword) {
        $userId = validateInteger($userId);
        $newPassword = sanitizePassword($newPassword);

        if (!$userId || !$newPassword) {
            return ['status' => 'error', 'message' => 'Invalid input.'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);

        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Password updated successfully.'];
        } else {
            return ['status' => 'error', 'message' => 'Failed to update password.'];
        }
    }
}
?>
