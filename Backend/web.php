<?php

session_start();

// Define routes
$routes = [
    '/' => 'home.php',
    '/about' => 'about.php',
    '/contact' => 'contact.php',
    '/login' => 'login.php',
    '/register' => 'register.php',
    '/packages' => 'packages.php',
    '/dashboard' => 'dashboard.php',
    '/admin' => 'admin.php',
];

// Middleware: Check if the user is authenticated
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Middleware: Check if the user is an admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Get the current request URI
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Protect admin routes
if ($request === '/admin' && !isAdmin()) {
    http_response_code(403);
    echo "403 Forbidden - Admin access only.";
    exit;
}

// Protect dashboard route
if ($request === '/dashboard' && !isAuthenticated()) {
    http_response_code(403);
    echo "403 Forbidden - You must be logged in to access this page.";
    exit;
}

// Handle CSRF token validation for POST requests
if (in_array($request, ['/login', '/register']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(400);
        echo "400 Bad Request - Invalid CSRF token.";
        exit;
    }
}

// Handle dynamic routes (e.g., /package/123)
if (preg_match('#^/package/(\d+)$#', $request, $matches)) {
    $packageId = $matches[1];
    require 'package_details.php';
    exit;
}

if (preg_match('#^/user/(\d+)$#', $request, $matches)) {
    $userId = $matches[1];
    require 'user_details.php';
    exit;
}

if (preg_match('#^/booking/(\d+)$#', $request, $matches)) {
    $bookingId = $matches[1];
    require 'booking_details.php';
    exit;
}

// Handle API routes
if (strpos($request, '/api/') === 0) {
    require 'api_handler.php';
    exit;
}

// Check if the route exists
if (array_key_exists($request, $routes)) {
    require $routes[$request];
    exit;
}

// Handle 404 Not Found
error_log("Undefined route accessed: $request", 3, 'error_log.txt');
http_response_code(404);
require '404.php';
exit;
?>
