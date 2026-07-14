<?php
// get_leaderboard.php — returns top 10 scores for a given duration
require '../../shared/db.php';
header('Content-Type: application/json');

$duration = (int)($_GET['duration'] ?? 30);

if (!in_array($duration, [15, 30, 60])) {
    $duration = 30;
}

$stmt = $pdo->prepare(
    "SELECT player_name, score, accuracy, created_at 
     FROM whack_scores 
     WHERE duration = ? 
     ORDER BY score DESC, accuracy DESC 
     LIMIT 10"
);
$stmt->execute([$duration]);
$scores = $stmt->fetchAll();

echo json_encode([
    'duration' => $duration,
    'scores'   => $scores,
]);