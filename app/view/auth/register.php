<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
    <link rel="icon" href="images/logo.avif" />
    <title>Register - WanderEase</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .register-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .register-container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .form-group input:focus {
            border-color: #007bff;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn:hover {
            background: #0056b3;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #777;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <?php
    session_start();
    if (isset($_SESSION['error'])) {
        echo '<div class="error" style="color: red; text-align: center; margin-bottom: 15px;">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="success" style="color: green; text-align: center; margin-bottom: 15px;">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    ?>
    <div id="spinner" style="display: none; text-align: center;">
        <div class="spinner"></div>
    </div>
    <div class="register-container">
        <h1>Register</h1>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required aria-label="Username" />
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required aria-label="Email" />
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required aria-label="Password" />
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm your password" required aria-label="Confirm Password" />
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        <div class="footer">
            <p>Already have an account? <a href="login.html">Login here</a></p>
        </div>
    </div>
    <script>
        const form = document.querySelector('form');
        const spinner = document.getElementById('spinner');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm-password');

        form.addEventListener('submit', (e) => {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match.');
            } else {
                spinner.style.display = 'block';
            }
        });

        const passwordInput = document.getElementById('password');
        const strengthIndicator = document.createElement('p');
        strengthIndicator.style.marginTop = '5px';
        passwordInput.parentNode.appendChild(strengthIndicator);

        passwordInput.addEventListener('input', () => {
            const value = passwordInput.value;
            if (value.length < 6) {
                strengthIndicator.textContent = 'Weak';
                strengthIndicator.style.color = 'red';
            } else if (value.length < 10) {
                strengthIndicator.textContent = 'Moderate';
                strengthIndicator.style.color = 'orange';
            } else {
                strengthIndicator.textContent = 'Strong';
                strengthIndicator.style.color = 'green';
            }
        });
        <?php
// filepath: c:\Users\ahmed\2nd Semister php project\WanderEase-Tour-and-Travel-Agency-main\Backend\register.php

session_start();
require_once '../config.php'; // Include your database connection file
require_once 'sanitize.php'; // Include sanitization functions
require_once 'User.php'; // Include the User class

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'Invalid CSRF token.';
        header('Location: ../register.html');
        exit;
    }

    // Retrieve and sanitize form data
    $username = sanitizeString($_POST['username']);
    $email = sanitizeEmail($_POST['email']);
    $password = sanitizePassword($_POST['password']);
    $confirm_password = sanitizePassword($_POST['confirm_password']);

    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: ../register.html');
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: ../register.html');
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters long.';
        header('Location: ../register.html');
        exit;
    }

    // Use the User class to handle registration
    $userModel = new User($conn);
    $result = $userModel->register($username, $email, $password);

    if ($result['status'] === 'success') {
        $_SESSION['success'] = $result['message'];
        header('Location: ../login.html');
        exit;
    } else {
        $_SESSION['error'] = $result['message'];
        header('Location: ../register.html');
        exit;
    }
}
?>

    </script>
</body>

</html>