<?php
$host = "127.0.0.1";
$dbname = "mysql";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on error
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch as associative array
    ]);

    echo "✅ Connected successfully!";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}