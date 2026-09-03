<?php
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['user_id'])) {
    respond([
        'logged_in' => true,
        'user' => [
            'name'  => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'phone' => $_SESSION['user_phone'],
        ],
    ]);
} else {
    respond(['logged_in' => false]);
}
