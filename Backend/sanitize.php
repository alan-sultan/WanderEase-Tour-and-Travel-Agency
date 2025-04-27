<?php
function sanitizeString($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function sanitizeEmail($email) {
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

function sanitizeURL($url) {
    $url = filter_var(trim($url), FILTER_SANITIZE_URL);
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
}

function validateInteger($number) {
    return filter_var($number, FILTER_VALIDATE_INT);
}

function validateFloat($number) {
    return filter_var($number, FILTER_VALIDATE_FLOAT);
}

function sanitizeArray(array $data) {
    return array_map('sanitizeString', $data);
}

function sanitizePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return (strlen($phone) >= 10 && strlen($phone) <= 15) ? $phone : false;
}

function sanitizeTextArea($text) {
    $text = htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
    return (strlen($text) <= 1000) ? $text : false;
}

function validateBoolean($value) {
    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function sanitizePassword($password) {
    $password = trim($password);
    return (strlen($password) >= 8 && strlen($password) <= 64) ? $password : false;
}

function validateName($name) {
    $name = sanitizeString($name);
    return (preg_match('/^[a-zA-Z\s]+$/', $name) && strlen($name) <= 100) ? $name : false;
}

function validateSubject($subject) {
    $subject = sanitizeString($subject);
    return (strlen($subject) <= 150) ? $subject : false;
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

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
?>
