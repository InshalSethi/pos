<?php
$hosts = ['127.0.0.1', 'localhost', '::1'];
foreach ($hosts as $h) {
    try {
        $pdo = new PDO("mysql:host=$h;port=3306", 'root', '');
        echo "Connected with host: $h\n";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS pos_testing;");
        echo "Database pos_testing created successfully.\n";
        break;
    } catch (Exception $e) {
        echo "Host $h failed: " . $e->getMessage() . "\n";
    }
}
