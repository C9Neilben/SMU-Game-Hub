<?php
// MAMP defaults: MySQL usually runs on port 8889, Apache on 8888.
// Open the MAMP app > Preferences > Ports to confirm yours, then edit below.

$DB_HOST = '127.0.0.1';
$DB_PORT = '3306';
$DB_NAME = 'avoid_game';
$DB_USER = 'root';
$DB_PASS = 'root';

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(
        "Database connection failed: " . $e->getMessage() .
        "<br><br>Check config.php — make sure the MySQL port matches MAMP's " .
        "(MAMP app &rarr; Preferences &rarr; Ports) and that you've imported schema.sql."
    );
}
