<?php
// check.php — handles flipping a card and checking for a match
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['mm_cards'])) {
    echo json_encode(['error' => 'No active game. Start a new game.']);
    exit;
}

if ($_SESSION['mm_game_over']) {
    echo json_encode(['error' => 'Game is already over.']);
    exit;
}

$index = $_POST['index'] ?? null;

if ($index === null || !ctype_digit((string)$index) || $index < 0 || $index > 15) {
    echo json_encode(['error' => 'Invalid card index.']);
    exit;
}
$index = (int)$index;

$cards   = $_SESSION['mm_cards'];
$matched = $_SESSION['mm_matched'];

// Can't flip an already-matched card
if (in_array($index, $matched)) {
    echo json_encode(['error' => 'Card already matched.']);
    exit;
}

$firstPick = $_SESSION['mm_first_pick'];

// Can't flip the same card twice in a row
if ($firstPick === $index) {
    echo json_encode(['error' => 'Card already flipped.']);
    exit;
}

$image = $cards[$index];

// --- Case 1: this is the FIRST card of the turn ---
if ($firstPick === null) {
    $_SESSION['mm_first_pick'] = $index;

    echo json_encode([
        'index'      => $index,
        'image'      => $image,
        'matched'    => false,
        'turn_done'  => false, // waiting for second pick
        'moves'      => $_SESSION['mm_moves'],
        'game_over'  => false,
    ]);
    exit;
}

// --- Case 2: this is the SECOND card of the turn ---
$firstImage = $cards[$firstPick];
$isMatch = ($firstImage === $image);

$_SESSION['mm_moves']++;

if ($isMatch) {
    $matched[] = $firstPick;
    $matched[] = $index;
    $_SESSION['mm_matched'] = $matched;
}

$_SESSION['mm_first_pick'] = null; // turn ends either way

$gameOver = (count($_SESSION['mm_matched']) === 16);
$_SESSION['mm_game_over'] = $gameOver;

echo json_encode([
    'index'          => $index,
    'image'          => $image,
    'first_index'    => $firstPick,
    'first_image'    => $firstImage,
    'matched'        => $isMatch,
    'turn_done'      => true,
    'moves'          => $_SESSION['mm_moves'],
    'game_over'      => $gameOver,
]);