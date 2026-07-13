<?php
// new_game.php — shuffles 8 images into 16 card positions
session_start();
header('Content-Type: application/json');

$images = [];
for ($i = 1; $i <= 8; $i++) {
    $images[] = "$i.jpg";
}

// Duplicate each image to form pairs, then shuffle
$cards = array_merge($images, $images);
shuffle($cards);

$_SESSION['mm_cards']      = $cards;       // 16 filenames in shuffled order
$_SESSION['mm_matched']    = [];           // indices that are permanently face-up
$_SESSION['mm_first_pick'] = null;         // index of first flipped card this turn
$_SESSION['mm_moves']      = 0;
$_SESSION['mm_game_over']  = false;

echo json_encode([
    'status'     => 'new_game_started',
    'card_count' => count($cards),
    'moves'      => 0,
]);