<?php
session_start();
echo json_encode([
    'logged_in' => isset($_SESSION['hotel_user_logged_in']) && $_SESSION['hotel_user_logged_in'] === true,
    'user_name' => $_SESSION['hotel_user_name'] ?? 'Guest'
]);
