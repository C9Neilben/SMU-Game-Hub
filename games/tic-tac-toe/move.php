<?php
// move.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['ttt_board'])) {
    echo json_encode(['error' => 'No active game. Start a new game.']);
    exit;
}

if ($_SESSION['ttt_game_over']) {
    echo json_encode(['error' => 'Round is already over. Start next round.']);
    exit;
}

$board = $_SESSION['ttt_board'];
$mode  = $_SESSION['ttt_mode'];
$turn  = $_SESSION['ttt_turn'];

$position = $_POST['position'] ?? null;

if ($position === null || !ctype_digit((string)$position) || $position < 0 || $position > 8) {
    echo json_encode(['error' => 'Invalid position.']);
    exit;
}
$position = (int)$position;

if ($board[$position] !== '') {
    echo json_encode(['error' => 'Cell already taken.']);
    exit;
}

$winCombos = [
    [0,1,2], [3,4,5], [6,7,8],
    [0,3,6], [1,4,7], [2,5,8],
    [0,4,8], [2,4,6]
];

function checkWinner($board, $winCombos) {
    foreach ($winCombos as $combo) {
        [$a, $b, $c] = $combo;
        if ($board[$a] !== '' && $board[$a] === $board[$b] && $board[$b] === $board[$c]) {
            return $board[$a];
        }
    }
    if (!in_array('', $board)) return 'draw';
    return null;
}

function pickAiMove($board, $winCombos) {
    foreach (['O', 'X'] as $symbol) {
        foreach ($winCombos as $combo) {
            $cells = array_map(fn($i) => $board[$i], $combo);
            $emptyIndex = array_search('', $cells);
            if ($emptyIndex !== false) {
                $filled = array_filter($cells, fn($v) => $v !== '');
                if (count($filled) === 2 && count(array_unique($filled)) === 1 && array_values($filled)[0] === $symbol) {
                    return $combo[$emptyIndex];
                }
            }
        }
    }
    if ($board[4] === '') return 4;
    $corners = [0, 2, 6, 8];
    shuffle($corners);
    foreach ($corners as $c) {
        if ($board[$c] === '') return $c;
    }
    $empty = array_keys(array_filter($board, fn($v) => $v === ''));
    return $empty[array_rand($empty)];
}

// Apply human move
$board[$position] = $turn;
$winner = checkWinner($board, $winCombos);
$aiMove = null;

if ($winner === null) {
    $turn = ($turn === 'X') ? 'O' : 'X';

    if ($mode === 'vs_ai' && $turn === 'O') {
        $aiMove = pickAiMove($board, $winCombos);
        $board[$aiMove] = 'O';
        $winner = checkWinner($board, $winCombos);
        if ($winner === null) {
            $turn = 'X';
        }
    }
}

$_SESSION['ttt_board']     = $board;
$_SESSION['ttt_turn']      = $turn;
$_SESSION['ttt_winner']    = $winner;
$_SESSION['ttt_game_over'] = ($winner !== null);

$matchOver   = false;
$matchWinner = null;
$scoreX = $_SESSION['ttt_score_x'];
$scoreO = $_SESSION['ttt_score_o'];

if ($winner !== null) {
    if ($winner === 'X') {
        $_SESSION['ttt_score_x']++;
    } elseif ($winner === 'O') {
        $_SESSION['ttt_score_o']++;
    }
    // draws don't add to either score

    $scoreX = $_SESSION['ttt_score_x'];
    $scoreO = $_SESSION['ttt_score_o'];
    $bestOf = $_SESSION['ttt_best_of'];
    $majority = (int)ceil($bestOf / 2);

    if ($scoreX >= $majority) {
        $matchOver = true;
        $matchWinner = 'X';
    } elseif ($scoreO >= $majority) {
        $matchOver = true;
        $matchWinner = 'O';
    } elseif ($_SESSION['ttt_round'] >= $bestOf) {
        $matchOver = true;
        if ($scoreX > $scoreO) $matchWinner = 'X';
        elseif ($scoreO > $scoreX) $matchWinner = 'O';
        else $matchWinner = 'draw';
    }

    $_SESSION['ttt_match_over']   = $matchOver;
    $_SESSION['ttt_match_winner'] = $matchWinner;
}

echo json_encode([
    'board'        => $board,
    'turn'         => $turn,
    'winner'       => $winner,
    'game_over'    => $_SESSION['ttt_game_over'],
    'ai_move'      => $aiMove,
    'score_x'      => $scoreX,
    'score_o'      => $scoreO,
    'round'        => $_SESSION['ttt_round'],
    'best_of'      => $_SESSION['ttt_best_of'],
    'match_over'   => $matchOver,
    'match_winner' => $matchWinner,
]);