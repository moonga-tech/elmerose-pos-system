<?php
    if (!defined('DB_SERVER')) {
        define('DB_SERVER', $_ENV['DB_HOST'] ?? 'localhost');
    }
    if (!defined('DB_USERNAME')) {
        define('DB_USERNAME', $_ENV['DB_USER'] ?? 'root');
    }
    if (!defined('DB_PASSWORD')) {
        define('DB_PASSWORD', $_ENV['DB_PASS'] ?? '');
    }
    if (!defined('DB_DATABASE')) {
        define('DB_DATABASE', $_ENV['DB_NAME'] ?? 'elmerose_pos');
    }

    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
?>