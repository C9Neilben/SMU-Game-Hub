<?php
// new_game.php — starts a new match (mode + best-of)
session_start();
header('Content-Type: application/json');

$mode = $_POST['mode'] ?? '2player';
$bestOf = (int)($_POST['best_of'] ?? 3);

if (!in_array($mode, ['2player', 'vs_ai'])) $mode = '2player';
if (!in_array($bestOf, [3, 5])) $bestOf = 3;

$_SESSION['ttt_mode']            = $mode;
$_SESSION['ttt_best_of']         = $bestOf;
$_SESSION['ttt_score_x']         = 0;
$_SESSION['ttt_score_o']         = 0;
$_SESSION['ttt_round']           = 1;
$_SESSION['ttt_starting_player'] = 'X'; // round 1 always starts with X
$_SESSION['ttt_match_over']      = false;
$_SESSION['ttt_match_winner']    = null;

$_SESSION['ttt_board']      = array_fill(0, 9, '');
$_SESSION['ttt_turn']       = 'X';
$_SESSION['ttt_game_over']  = false;
$_SESSION['ttt_winner']     = null;

echo json_encode([
    'status'  => 'match_started',
    'mode'    => $mode,
    'best_of' => $bestOf,
    'board'   => $_SESSION['ttt_board'],
    'turn'    => $_SESSION['ttt_turn'],
    'round'   => $_SESSION['ttt_round'],
    'score_x' => 0,
    'score_o' => 0,
]);