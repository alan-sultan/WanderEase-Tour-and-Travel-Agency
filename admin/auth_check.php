<?php
require_once '../includes/auth.php';

// Function to require admin access
function requireAdmin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Please log in to access the admin panel.';
        header('Location: ../auth/login.php');
        exit;
    }
    
    if (!isAdmin()) {
        $_SESSION['error'] = 'You do not have permission to access the admin panel.';
        header('Location: ../index.php');
        exit;
    }
}

// Check admin access for all admin pages
requireAdmin();
?> 