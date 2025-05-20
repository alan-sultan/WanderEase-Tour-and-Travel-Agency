<?php
// filepath: Backend/packageController.php
function jsonResponse($status, $message, $data = null) {
    header('Content-Type: application/json');
    $response = ['status' => $status, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}


require_once '../config.php';
require_once 'sanitize.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Utility function for responses
function respond($status, $message, $data = null) {
    jsonResponse('error', 'Invalid package ID.');

    exit;
}

// Admin check utility
function isAdmin() {
    return isset($_SESSION['user_id'], $_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// CSRF check utility
function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
}

// Handle GET: fetch packages
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    $stmt = $conn->prepare("SELECT * FROM packages LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $packages = [];
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }

    respond('success', 'Packages retrieved successfully', $packages);
}

// Handle POST: create new package
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isAdmin()) {
        respond('error', 'Unauthorized access.');
    }

    if (!verifyCsrf($_POST['csrf_token'])) {
        respond('error', 'Invalid CSRF token.');
    }

    $action = $_POST['action'] ?? '';
    $name = sanitizeString($_POST['name'] ?? '');
    $destination = sanitizeString($_POST['destination'] ?? '');
    $price = validateFloat($_POST['price'] ?? '');
    $description = sanitizeTextArea($_POST['description'] ?? '');

    if (!$name || !$destination || !$price || !$description) {
        respond('error', 'Invalid input.');
    }

    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO packages (name, destination, price, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $name, $destination, $price, $description);
        if ($stmt->execute()) {
            respond('success', 'Package added successfully.');
        } else {
            error_log('Add Error: ' . $stmt->error . "\n", 3, 'error_log.txt');
            respond('error', 'Failed to add package.');
        }
    }

    if ($action === 'update') {
        $id = validateInteger($_POST['id'] ?? '');
        if (!$id) respond('error', 'Invalid package ID.');

        $stmt = $conn->prepare("UPDATE packages SET name = ?, destination = ?, price = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssdsi", $name, $destination, $price, $description, $id);
        if ($stmt->execute()) {
            respond('success', 'Package updated successfully.');
        } else {
            error_log('Update Error: ' . $stmt->error . "\n", 3, 'error_log.txt');
            respond('error', 'Failed to update package.');
        }
    }

    respond('error', 'Invalid action.');
}

// Handle PUT: update package (alternative to POST update)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);

    if (!isAdmin()) {
        respond('error', 'Unauthorized access.');
    }

    if (!verifyCsrf($_PUT['csrf_token'] ?? '')) {
        respond('error', 'Invalid CSRF token.');
    }

    $id = validateInteger($_PUT['id'] ?? '');
    $name = sanitizeString($_PUT['name'] ?? '');
    $destination = sanitizeString($_PUT['destination'] ?? '');
    $price = validateFloat($_PUT['price'] ?? '');
    $description = sanitizeTextArea($_PUT['description'] ?? '');

    if (!$id || !$name || !$destination || !$price || !$description) {
        respond('error', 'Invalid input.');
    }

    $stmt = $conn->prepare("UPDATE packages SET name = ?, destination = ?, price = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssdsi", $name, $destination, $price, $description, $id);
    if ($stmt->execute()) {
        respond('success', 'Package updated successfully (PUT).');
    } else {
        error_log('PUT Update Error: ' . $stmt->error . "\n", 3, 'error_log.txt');
        respond('error', 'Failed to update package.');
    }
}

// Handle DELETE
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);

    if (!isAdmin()) {
        respond('error', 'Unauthorized access.');
    }

    $id = validateInteger($_DELETE['id'] ?? '');
    if (!$id) {
        respond('error', 'Invalid package ID.');
    }

    if (!verifyCsrf($_DELETE['csrf_token'] ?? '')) {
        respond('error', 'Invalid CSRF token.');
    }

    $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        respond('success', 'Package deleted successfully.');
    } else {
        error_log('Delete Error: ' . $stmt->error . "\n", 3, 'error_log.txt');
        respond('error', 'Failed to delete package.');
    }
}
