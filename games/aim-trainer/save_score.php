<?php
// save_score.php — inserts a finished session's result into the leaderboard
require '../../shared/db.php';
header('Content-Type: application/json');

$playerName = trim($_POST['player_name'] ?? '');
$score      = $_POST['score'] ?? null;
$accuracy   = $_POST['accuracy'] ?? null;
$duration   = $_POST['duration'] ?? null;

// --- Validation ---
if ($playerName === '') {
    $playerName = 'Anonymous';
}
$playerName = substr($playerName, 0, 20); // enforce DB column limit

if (!is_numeric($score) || !is_numeric($accuracy) || !is_numeric($duration)) {
    echo json_encode(['error' => 'Invalid score data.']);
    exit;
}

$score    = (int)$score;
$accuracy = round((float)$accuracy, 2);
$duration = (int)$duration;

if (!in_array($duration, [15, 30, 60])) {
    echo json_encode(['error' => 'Invalid duration.']);
    exit;
}

if ($score < 0 || $accuracy < 0 || $accuracy > 100) {
    echo json_encode(['error' => 'Score out of valid range.']);
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO aim_scores (player_name, score, accuracy, duration) VALUES (?, ?, ?, ?)"
);
$stmt->execute([$playerName, $score, $accuracy, $duration]);

echo json_encode(['status' => 'saved']);