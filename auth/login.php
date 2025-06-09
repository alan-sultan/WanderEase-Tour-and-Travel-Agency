<?php
require_once '../includes/auth.php';

// Get redirect_to parameter
$redirectTo = $_GET['redirect_to'] ?? null;

// Construct full absolute URL for redirection if $redirectTo is provided
$fullRedirectTo = null;
if ($redirectTo) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    // Ensure $redirectTo starts with a '/' for correct path resolution
    if (strpos($redirectTo, '/') !== 0) {
        $redirectTo = '/' . $redirectTo;
    }
    $fullRedirectTo = $protocol . "://" . $host . $redirectTo;
}

// Redirect if already logged in
if (isLoggedIn()) {
    if ($fullRedirectTo) {
        header('Location: ' . $fullRedirectTo);
        exit;
    }
    $role = getUserRole();
    switch ($role) {
        case 'admin':
            header('Location: ../admin/index.php');
            break;
        case 'staff':
            header('Location: ../staff/index.php');
            break;
        default:
            header('Location: ../index.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WonderEase Travel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <div class="auth-form">
            <h2>Login to Your Account</h2>

            <div id="error-message" class="alert alert-error" style="display: none;"></div>

            <form id="login-form" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <p class="auth-links">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const errorMessage = document.getElementById('error-message');
            errorMessage.style.display = 'none';

            // Get redirect_to parameter from URL if it exists
            const urlParams = new URLSearchParams(window.location.search);
            const redirectTo = urlParams.get('redirect_to');

            // Log the form data for debugging
            console.log('Attempting login with:', {
                email: formData.get('email'),
                password: '***' // Don't log the actual password
            });

            fetch('ajax_login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        console.log('Login successful.');
                        if (redirectTo) {
                            // JavaScript side will now construct the full URL to ensure correct redirection
                            const fullRedirectUrl = window.location.origin + redirectTo; // Correctly forms http://localhost/wonderease-ip-project/user/packages.php
                            console.log('Redirecting to:', fullRedirectUrl);
                            window.location.href = fullRedirectUrl;
                        } else {
                            window.location.reload(); // Default reload
                        }
                    } else {
                        errorMessage.textContent = data.message || 'Login failed. Please try again.';
                        errorMessage.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Login error:', error);
                    errorMessage.textContent = 'An error occurred. Please try again.';
                    errorMessage.style.display = 'block';
                });
        });
    </script>
</body>

</html>
