<?php
// new_game.php
session_start();

$words = require 'words.php';
$secret = $words[array_rand($words)];

$_SESSION['wordle_secret_word'] = $secret;
$_SESSION['wordle_attempts'] = [];
$_SESSION['wordle_game_over'] = false;

header('Content-Type: application/json');
echo json_encode(['status' => 'new_game_started']);
