<?php
// next_round.php — advances within the same match; alternates starting player
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['ttt_mode'])) {
    echo json_encode(['error' => 'No active match.']);
    exit;
}
if ($_SESSION['ttt_match_over']) {
    echo json_encode(['error' => 'Match already over. Start a new match.']);
    exit;
}

$_SESSION['ttt_round']++;
$_SESSION['ttt_starting_player'] = ($_SESSION['ttt_starting_player'] === 'X') ? 'O' : 'X';

$_SESSION['ttt_board']     = array_fill(0, 9, '');
$_SESSION['ttt_turn']      = $_SESSION['ttt_starting_player'];
$_SESSION['ttt_game_over'] = false;
$_SESSION['ttt_winner']    = null;

$aiMove = null;

// If AI (always 'O') starts this round on an empty board, center is the standard best opening
if ($_SESSION['ttt_mode'] === 'vs_ai' && $_SESSION['ttt_turn'] === 'O') {
    $board = $_SESSION['ttt_board'];
    $aiMove = 4;
    $board[$aiMove] = 'O';
    $_SESSION['ttt_board'] = $board;
    $_SESSION['ttt_turn'] = 'X';
}

echo json_encode([
    'board'   => $_SESSION['ttt_board'],
    'turn'    => $_SESSION['ttt_turn'],
    'round'   => $_SESSION['ttt_round'],
    'ai_move' => $aiMove,
]);