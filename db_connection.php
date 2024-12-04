<?php
// db_connection.php

// Database configuration
$host = 'localhost';       // Your database host (e.g., localhost)
$dbname = 'u937022582_guesspiece'; // Replace with your database name
$username = 'u937022582_admin';   // Replace with your database username
$password = 'qwWanokuni999@@'; // Replace with your database password

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set PDO error mode to exception for debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection errors
    die("Database connection failed: " . $e->getMessage());
}
?>
