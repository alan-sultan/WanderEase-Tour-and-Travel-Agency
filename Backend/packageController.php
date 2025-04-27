<?php

require_once '../config.php'; // Include database connection file
require_once 'sanitize.php'; // Include sanitization functions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check the request method and perform the corresponding action
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
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

    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $packages]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
        exit;
    }

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token.']);
        exit;
    }

    $name = sanitizeString($_POST['name']);
    $destination = sanitizeString($_POST['destination']);
    $price = validateFloat($_POST['price']);
    $description = sanitizeTextArea($_POST['description']);

    if (!$name || !$destination || !$price || !$description) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        exit;
    }

    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare("INSERT INTO packages (name, destination, price, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $name, $destination, $price, $description);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Package added successfully.']);
        } else {
            error_log('Database error: ' . $stmt->error, 3, 'error_log.txt');
            echo json_encode(['status' => 'error', 'message' => 'Failed to add package.']);
        }
        exit;
    }

    if ($_POST['action'] === 'update') {
        $id = validateInteger($_POST['id']);
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid package ID.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE packages SET name = ?, destination = ?, price = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssdsi", $name, $destination, $price, $description, $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Package updated successfully.']);
        } else {
            error_log('Database error: ' . $stmt->error, 3, 'error_log.txt');
            echo json_encode(['status' => 'error', 'message' => 'Failed to update package.']);
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = validateInteger($_DELETE['id']);

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid package ID.']);
        exit;
    }

    if (!isset($_DELETE['csrf_token']) || $_DELETE['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Package deleted successfully.']);
    } else {
        error_log('Database error: ' . $stmt->error, 3, 'error_log.txt');
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete package.']);
    }
    exit;
}
?>
