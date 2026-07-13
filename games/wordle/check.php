<?php
// check.php
session_start();
header('Content-Type: application/json');

$words = require 'words.php';

$guess = strtoupper(trim($_POST['guess'] ?? ''));
$secret = $_SESSION['wordle_secret_word'] ?? null;

if (!$secret) {
    echo json_encode(['error' => 'No active game. Start a new game.']);
    exit;
}

if (strlen($guess) !== 5) {
    echo json_encode(['error' => 'Guess must be 5 letters.']);
    exit;
}

if (!in_array($guess, $words)) {
    echo json_encode(['error' => 'Not a valid word.']);
    exit;
}

// --- Wordle-style comparison logic ---
$secretLetters = str_split($secret);
$guessLetters = str_split($guess);
$result = array_fill(0, 5, 'absent');

// First pass: exact matches
$secretRemaining = $secretLetters;
foreach ($guessLetters as $i => $letter) {
    if ($secretLetters[$i] === $letter) {
        $result[$i] = 'correct';
        $secretRemaining[$i] = null; // consumed
    }
}

// Second pass: present but wrong position
foreach ($guessLetters as $i => $letter) {
    if ($result[$i] === 'correct') continue;
    $pos = array_search($letter, $secretRemaining);
    if ($pos !== false) {
        $result[$i] = 'present';
        $secretRemaining[$pos] = null; // consumed
    }
}

$_SESSION['wordle_attempts'][] = ['guess' => $guessLetters, 'result' => $result];

$won = ($guess === $secret);
$attemptsCount = count($_SESSION['wordle_attempts']);
$lost = (!$won && $attemptsCount >= 6);

if ($won || $lost) {
    $_SESSION['wordle_game_over'] = true;
}

echo json_encode([
    'result' => $result,
    'won' => $won,
    'lost' => $lost,
    'attempts_used' => $attemptsCount,
    'wordle_secret_word' => $lost ? $secret : null
]);