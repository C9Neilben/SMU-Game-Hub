<?php
// delete_game.php — removes a game from the catalog
require '../shared/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['game_id'])) {
    header('Location: admin.php');
    exit;
}

$gameId = (int)$_POST['game_id'];

// Optional: delete the thumbnail file too, so orphaned images don't pile up
$stmt = $pdo->prepare("SELECT thumbnail FROM games WHERE game_id = ?");
$stmt->execute([$gameId]);
$game = $stmt->fetch();

if ($game && $game['thumbnail'] && file_exists('../' . $game['thumbnail'])) {
    unlink('../' . $game['thumbnail']);
}

$stmt = $pdo->prepare("DELETE FROM games WHERE game_id = ?");
$stmt->execute([$gameId]);

header('Location: admin.php');
exit;