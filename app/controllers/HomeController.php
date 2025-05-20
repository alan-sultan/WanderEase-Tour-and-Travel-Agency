<?php

class HomeController {
    public function index() {
        session_start();

        // Optional: pass session data to the view
        $user_id = $_SESSION['user_id'] ?? null;
        $error = $_SESSION['error'] ?? null;
        $success = $_SESSION['success'] ?? null;

        // Clear flash messages
        unset($_SESSION['error'], $_SESSION['success']);

        require_once __DIR__ . '/../view/home/index.php';
    }
}
