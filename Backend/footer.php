<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<footer>
    <div class="footer-content">
        <p>&copy; <?php echo date("Y"); ?> WanderEase Tour and Travel Agency. All rights reserved.</p>
        <ul class="footer-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="packages.html">Packages</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="privacy.php">Privacy Policy</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.html">Login</a></li>
                <li><a href="register.html">Register</a></li>
            <?php endif; ?>
        </ul>
        <ul class="footer-socials">
            <li><a href="https://twitter.com" target="_blank"><i class="ri-twitter-fill"></i></a></li>
            <li><a href="https://facebook.com" target="_blank"><i class="ri-facebook-fill"></i></a></li>
            <li><a href="https://linkedin.com" target="_blank"><i class="ri-linkedin-fill"></i></a></li>
        </ul>
    </div>
    <script src="path/to/your/script.js"></script>
</footer>

<style>
.footer-content {
    text-align: center;
    padding: 20px;
    background-color: #f3f4f6;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 10px 0;
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.footer-links li {
    margin: 0;
}

.footer-links a {
    text-decoration: none;
    color: #007bff;
    transition: color 0.3s;
}

.footer-links a:hover {
    color: #0056b3;
}

@media (max-width: 540px) {
    .footer-links {
        flex-direction: column;
        gap: 10px;
    }
}
</style>
