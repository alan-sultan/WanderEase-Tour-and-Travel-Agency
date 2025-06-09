<?php
require_once '../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Check if message_id is provided
if (!isset($_GET['message_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Message ID is required']);
    exit;
}

$message_id = (int)$_GET['message_id'];
$conn = getDBConnection();

// First verify that this message belongs to the current user
$stmt = $conn->prepare("SELECT id FROM support_messages WHERE id = ? AND user_id = ?");
$stmt->execute([$message_id, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Message not found or unauthorized']);
    exit;
}

// Get responses for this message
$stmt = $conn->prepare("
    SELECT sr.*, u.name as responder_name
    FROM support_responses sr
    JOIN users u ON sr.responder_id = u.id
    WHERE sr.message_id = ?
    ORDER BY sr.created_at ASC
");
$stmt->execute([$message_id]);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format the responses for JSON
$formatted_responses = array_map(function ($response) {
    return [
        'response' => $response['response'],
        'created_at' => date('M d, Y H:i', strtotime($response['created_at'])),
        'responder_name' => $response['responder_name']
    ];
}, $responses);

header('Content-Type: application/json');
echo json_encode(['responses' => $formatted_responses]);
