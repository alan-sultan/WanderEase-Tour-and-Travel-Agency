<?php
// Sanitize a string by trimming, removing harmful characters, and encoding HTML entities
function sanitizeString($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Validate an email address and sanitize it
function sanitizeEmail($email) {
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

// Validate a URL and sanitize it
function sanitizeURL($url) {
    $url = filter_var(trim($url), FILTER_SANITIZE_URL);
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
}

// Validate if a given value is a valid integer
function validateInteger($number) {
    return filter_var($number, FILTER_VALIDATE_INT) !== false;
}

// Validate if a given value is a valid float
function validateFloat($number) {
    return filter_var($number, FILTER_VALIDATE_FLOAT) !== false;
}

// Sanitize an array of strings
function sanitizeArray(array $data) {
    return array_map('sanitizeString', $data);
}

// Validate and sanitize a phone number (only numbers, 10-15 digits)
function sanitizePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return (strlen($phone) >= 10 && strlen($phone) <= 15) ? $phone : null;
}

// Sanitize and validate a text area (max length 1000 characters)
function sanitizeTextArea($text) {
    $text = htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
    return (strlen($text) <= 1000) ? $text : null;
}

// Validate a boolean value (true or false)
function validateBoolean($value) {
    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
}

// Generate a CSRF token for session security
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a 32-byte CSRF token
    }
    return $_SESSION['csrf_token'];
}

// Validate the CSRF token by using hash_equals to prevent timing attacks
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

// Validate a password (length between 8 and 64 characters)
function isValidPassword($password) {
    return (strlen($password) >= 8 && strlen($password) <= 64);
}

// Sanitize a password by trimming unnecessary spaces
function sanitizePassword($password) {
    return trim($password);
}

// Validate and sanitize a name (only letters and spaces, max 100 characters)
function validateName($name) {
    $name = sanitizeString($name);
    return (preg_match('/^[a-zA-Z\s]+$/', $name) && strlen($name) <= 100) ? $name : null;
}

// Validate a subject (max length 150 characters)
function validateSubject($subject) {
    $subject = sanitizeString($subject);
    return (strlen($subject) <= 150) ? $subject : null;
}

// Validate a date (in the format 'Y-m-d' by default)
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Validate an uploaded file (check type, size, and error status)
function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png'], $maxSize = 2 * 1024 * 1024) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return false;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Check file MIME type (to prevent spoofing)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }

    // Check file size
    if ($file['size'] > $maxSize) {
        return false;
    }

    return true;
}
?>
