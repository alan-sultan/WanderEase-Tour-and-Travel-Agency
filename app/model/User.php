<?php
// filepath: Backend/User.php

require_once __DIR__ . '/../config.php'; // Adjust path based on your structure
require_once __DIR__ . '/sanitize.php';

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
                return $user; // Successful login
            }
        }
        return false; // Invalid login
    }

    // Register a new user
    public function register($username, $email, $password) {
        $username = sanitizeString($username);
        $email = sanitizeEmail($email);
        $password = sanitizePassword($password);

        if (!$username || !validateEmail($email) || !validatePasswordStrength($password)) {
            return ['status' => 'error', 'message' => 'Invalid input data.'];
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'User registered successfully.'];
        } else {
            return ['status' => 'error', 'message' => 'Registration failed. Username or email might already exist.'];
        }
    }

    // Fetch user details by ID
    public function getUserById($id) {
        $id = validateInteger($id);

        $stmt = $this->conn->prepare("SELECT id, username, email, is_admin, full_name, phone FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        return null;
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

        if (!$userId || !validatePasswordStrength($newPassword)) {
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

    // Update user profile (name, email, phone)
    public function updateProfile($userId, $fullName, $email, $phone) {
        $userId = validateInteger($userId);
        $fullName = sanitizeString($fullName);
        $email = sanitizeEmail($email);
        $phone = sanitizePhone($phone);

        if (!$userId || !$fullName || !validateEmail($email)) {
            return ['status' => 'error', 'message' => 'Invalid profile data.'];
        }

        $stmt = $this->conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $fullName, $email, $phone, $userId);

        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Profile updated successfully.'];
        } else {
            return ['status' => 'error', 'message' => 'Failed to update profile.'];
        }
    }
}
