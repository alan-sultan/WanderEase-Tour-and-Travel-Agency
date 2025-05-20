<?php

session_start();

require_once '../models/Booking.php'; // Include the Booking model
require_once '../Backend/validation.php'; // Include validation functions

class BookingController {
    private $dbConnection;

    public function __construct($dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    // Create a new booking
    public function createBooking() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token.']);
                exit;
            }

            // Retrieve and validate input
            $userId = $_SESSION['user_id'];
            $packageId = validateInteger($_POST['package_id']);
            $bookingDate = validateBookingDate($_POST['booking_date']);
            $numGuests = validateInteger($_POST['num_guests']);

            if (!$packageId || !$bookingDate || !$numGuests) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
                exit;
            }

            // Call the Booking model
            $bookingModel = new Booking($this->dbConnection);
            $result = $bookingModel->createBooking($userId, $packageId, $bookingDate, $numGuests);

            echo json_encode($result);
            exit;
        }
    }

    // Get all bookings for the logged-in user
    public function getUserBookings() {
        $userId = $_SESSION['user_id'];

        $bookingModel = new Booking($this->dbConnection);
        $bookings = $bookingModel->getBookingsByUserId($userId);

        echo json_encode(['status' => 'success', 'data' => $bookings]);
        exit;
    }

    // Get details of a specific booking
    public function getBookingDetails($bookingId) {
        $userId = $_SESSION['user_id'];

        $bookingModel = new Booking($this->dbConnection);
        $booking = $bookingModel->getBookingById($bookingId);

        if (!$booking || $booking['user_id'] !== $userId) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
            exit;
        }

        echo json_encode(['status' => 'success', 'data' => $booking]);
        exit;
    }

    // Cancel a booking
    public function cancelBooking() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token.']);
                exit;
            }

            // Retrieve and validate input
            $bookingId = validateInteger($_POST['booking_id']);
            $userId = $_SESSION['user_id'];

            $bookingModel = new Booking($this->dbConnection);
            $result = $bookingModel->cancelBooking($bookingId, $userId);

            echo json_encode($result);
            exit;
        }
    }

    // Admin: Get all bookings
    public function getAllBookings() {
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
            exit;
        }

        $bookingModel = new Booking($this->dbConnection);
        $bookings = $bookingModel->getAllBookings();

        echo json_encode(['status' => 'success', 'data' => $bookings]);
        exit;
    }
}

// Example usage
// Assuming $dbConnection is your database connection object
// $controller = new BookingController($dbConnection);
// $controller->createBooking(); // Call this method based on the request
?>