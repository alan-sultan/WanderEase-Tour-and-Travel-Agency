<?php
require_once '../includes/auth.php';

// Perform logout
logoutUser();

// Redirect to homepage
header('Location: ../index.php');
exit;
