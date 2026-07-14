<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aim Trainer - SMU Game Hub</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../shared/result-modal.css">
</head>
<body>

    <header>
        <h1>Aim Trainer</h1>
        <a href="../../hub/index.php" class="back-link">← Back to Hub</a>
    </header>

    <main>
        <!-- Start screen -->
        <div id="start-screen" class="panel">
            <h2>Choose session length</h2>
            <button class="duration-btn" data-duration="15">15 seconds</button>
            <button class="duration-btn" data-duration="30">30 seconds</button>
            <button class="duration-btn" data-duration="60">60 seconds</button>

            <h2>Leaderboard</h2>
            <div id="leaderboard-list">Loading...</div>
        </div>

        <!-- Game screen -->
        <div id="game-screen" class="hidden">
            <div id="hud">
                <span id="timer-text">Time: 30</span>
                <span id="score-text">Score: 0</span>
            </div>
            <div id="range" class="range"></div>
        </div>

        <!-- Results screen -->
        <div id="results-screen" class="panel hidden">
            <h2>Session Complete</h2>
            <p id="results-summary"></p>

            <label>Enter your name for the leaderboard:
                <input type="text" id="player-name" maxlength="20" placeholder="Your name">
            </label>
            <button id="save-score-btn">Save Score</button>
            <button id="play-again-btn">Play Again</button>
        </div>
    </main>

    <script src="../../shared/result-modal.js"></script>
    <script src="script.js"></script>
</body>
</html>