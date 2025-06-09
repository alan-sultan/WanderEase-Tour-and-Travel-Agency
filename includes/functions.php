<?php
require_once 'db.php';

// Sanitize input
function sanitize($input) {
    $db = Database::getInstance();
    return $db->escape(trim($input));
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Redirect to a specific page
function redirect($page) {
    header("Location: " . BASE_URL . "/" . $page);
    exit();
}

// Display error message
function showError($message) {
    return "<div class='error-message'>" . htmlspecialchars($message) . "</div>";
}

// Display success message
function showSuccess($message) {
    return "<div class='success-message'>" . htmlspecialchars($message) . "</div>";
}

// Format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Format price
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Get featured packages
function getFeaturedPackages($limit = 3) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM packages WHERE featured = 1 LIMIT " . (int)$limit;
    $result = $db->query($sql);
    
    $packages = [];
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
    
    return $packages;
}

// Search packages
function searchPackages($query) {
    $db = Database::getInstance();
    $search = sanitize($query);
    
    $sql = "SELECT * FROM packages 
            WHERE title LIKE '%$search%' 
            OR description LIKE '%$search%' 
            OR destination LIKE '%$search%'";
            
    $result = $db->query($sql);
    
    $packages = [];
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
    
    return $packages;
}
?> 
