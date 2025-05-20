<?php
// filepath: c:\Users\ahmed\2nd Semister php project\WanderEase-Tour-and-Travel-Agency-main\Backend\Booking.php

require_once '../config.php'; // Include database connection
require_once 'sanitize.php'; // Include sanitization functions

class Booking {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Create a new booking
    public function createBooking($userId, $packageId, $bookingDate, $numGuests) {
        $userId = validateInteger($userId);
        $packageId = validateInteger($packageId);
        $bookingDate = validateDate($bookingDate);
        $numGuests = validateInteger($numGuests);

        if (!$userId || !$packageId || !$bookingDate || !$numGuests) {
            return ['status' => 'error', 'message' => 'Invalid input.'];
        }

        if (strtotime($bookingDate) < strtotime(date('Y-m-d'))) {
            return ['status' => 'error', 'message' => 'Booking date cannot be in the past.'];
        }

        $packageStmt = $this->conn->prepare("SELECT id FROM packages WHERE id = ?");
        $packageStmt->bind_param("i", $packageId);
        $packageStmt->execute();
        $packageResult = $packageStmt->get_result();

        if ($packageResult->num_rows === 0) {
            return ['status' => 'error', 'message' => 'Package does not exist.'];
        }

        $stmt = $this->conn->prepare("INSERT INTO bookings (user_id, package_id, booking_date, num_guests) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $userId, $packageId, $bookingDate, $numGuests);

        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Booking created successfully.'];
        } else {
            error_log('Database error: ' . $stmt->error, 3, 'error_log.txt');
            return ['status' => 'error', 'message' => 'Failed to create booking.'];
        }
    }

    // Retrieve booking details by booking ID
    public function getBookingById($bookingId) {
        $bookingId = validateInteger($bookingId);

        $stmt = $this->conn->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        return null; // Booking not found
    }

    // Retrieve all bookings for a specific user
    public function getBookingsByUserId($userId) {
        $userId = validateInteger($userId);

        $stmt = $this->conn->prepare("SELECT * FROM bookings WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        return $bookings;
    }

    // Cancel a booking
    public function cancelBooking($bookingId, $userId) {
        $bookingId = validateInteger($bookingId);
        $userId = validateInteger($userId);

        if (!$bookingId || !$userId) {
            return ['status' => 'error', 'message' => 'Invalid input.'];
        }

        $stmt = $this->conn->prepare("UPDATE bookings SET status = 'canceled' WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $bookingId, $userId);

        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Booking canceled successfully.'];
        } else {
            error_log('Database error: ' . $stmt->error, 3, 'error_log.txt');
            return ['status' => 'error', 'message' => 'Failed to cancel booking.'];
        }
    }

    // Fetch all bookings (Admin only)
    public function getAllBookings($isAdmin, $limit = 10, $offset = 0) {
        if (!$isAdmin) {
            return ['status' => 'error', 'message' => 'Unauthorized access.'];
        }

        $stmt = $this->conn->prepare("SELECT * FROM bookings LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        return $bookings;
    }
}
?>