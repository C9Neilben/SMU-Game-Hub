<?php
session_start();
require 'config.php';

$numPlayers = isset($_POST['numPlayers']) ? (int)$_POST['numPlayers'] : 0;
$names = $_POST['playerName'] ?? [];

if ($numPlayers < 1 || $numPlayers > 7 || count($names) !== $numPlayers) {
    die('Invalid setup. <a href="index.php">Go back</a>.');
}

$stmt = $pdo->prepare("INSERT INTO games (num_players) VALUES (?)");
$stmt->execute([$numPlayers]);
$gameId = $pdo->lastInsertId();

$insertPlayer = $pdo->prepare("INSERT INTO players (game_id, seat_order, name) VALUES (?, ?, ?)");
foreach ($names as $i => $name) {
    $name = trim($name) !== '' ? trim($name) : ('Player ' . ($i + 1));
    $insertPlayer->execute([$gameId, $i, $name]);
}

$_SESSION['game_id'] = $gameId;
$_SESSION['stage'] = 'category';
unset($_SESSION['turn_order'], $_SESSION['turn_index'], $_SESSION['current_round_id']);

header('Location: game.php');
exit;
