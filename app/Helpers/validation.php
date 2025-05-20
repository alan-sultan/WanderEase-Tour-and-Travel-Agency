<?php
// filepath: c:\Users\ahmed\2nd Semister php project\WanderEase-Tour-and-Travel-Agency-main\Backend\validation.php

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

// Validate password
function validatePassword($password) {
    if (strlen($password) < 8) {
        return false; // Password must be at least 8 characters long
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false; // Password must contain at least one uppercase letter
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false; // Password must contain at least one lowercase letter
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false; // Password must contain at least one number
    }
    if (!preg_match('/[\W]/', $password)) {
        return false; // Password must contain at least one special character
    }
    return $password;
}

// Validate integer
function validateInteger($number) {
    return filter_var($number, FILTER_VALIDATE_INT) !== false ? (int)$number : false;
}

// Validate float
function validateFloat($number) {
    return filter_var($number, FILTER_VALIDATE_FLOAT) !== false ? (float)$number : false;
}

// Validate date
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date ? $date : false;
}

// Validate booking date (not in the past)
function validateBookingDate($date) {
    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        return false; // Booking date cannot be in the past
    }
    return validateDate($date);
}

// Validate string
function validateString($string, $maxLength = 255) {
    $string = trim($string);
    if (strlen($string) > $maxLength) {
        return false; // String exceeds maximum length
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Validate name
function validateName($name, $maxLength = 100) {
    $name = trim($name);
    if (!preg_match('/^[a-zA-Z\s]+$/', $name) || strlen($name) > $maxLength) {
        return false;
    }
    return htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
}

// Validate subject
function validateSubject($subject, $maxLength = 150) {
    $subject = trim($subject);
    if (strlen($subject) > $maxLength) {
        return false;
    }
    return htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
}

// Validate boolean
function validateBoolean($value) {
    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null ? (bool)$value : false;
}

// Validate URL
function validateURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
}

// Validate phone number
function validatePhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone); // Remove non-numeric characters
    return (strlen($phone) >= 10 && strlen($phone) <= 15) ? $phone : false;
}

// Validate CSRF token
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Validate file upload
function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png'], $maxSize = 2 * 1024 * 1024) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return false;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }

    if ($file['size'] > $maxSize) {
        return false;
    }

    return true;
}

// Validate admin privileges
function validateAdmin($isAdmin) {
    return $isAdmin === true;
}

// Log validation errors
function logValidationError($message) {
    error_log('Validation error: ' . $message, 3, 'validation_errors.log');
}
?>