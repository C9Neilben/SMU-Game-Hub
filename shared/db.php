<?php
// shared/db.php — single DB connection reused across hub + all games

$host = '127.0.0.1';
$db   = 'smu_game_hub';
$user = 'root';      // MAMP default
$pass = 'root';      // MAMP default
$port = '3306';      // MAMP default MySQL port — check yours in MAMP preferences

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}