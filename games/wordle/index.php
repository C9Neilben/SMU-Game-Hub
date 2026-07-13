<?php
session_start();
if (!isset($_SESSION['secret_word'])) {
    $words = require 'words.php';
    $_SESSION['secret_word'] = $words[array_rand($words)];
    $_SESSION['attempts'] = [];
    $_SESSION['game_over'] = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wordle Clone</title>
    <a href="../../hub/index.php" class="back-link">← Back to Hub</a>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body>
    <h1>WORDLE ni NEILBEN</h1>
    <div id="board"></div>
    <div id="message"></div>
    <div id="keyboard"></div>
    <button id="new-game-btn">New Game</button>

    <!-- Modal overlay, shared by win and lose states -->
    <div id="modal-overlay" class="hidden">
        <div id="modal-card">
            <div id="modal-content"></div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
