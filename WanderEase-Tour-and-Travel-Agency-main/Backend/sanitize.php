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
    return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
}

function validateBoolean($value) {
    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
}