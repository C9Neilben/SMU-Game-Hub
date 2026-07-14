<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tic-Tac-Toe - SMU Game Hub</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../shared/result-modal.css">
</head>
<body>

    <header>
        <h1>Tic-Tac-Toe</h1>
        <a href="../../hub/index.php" class="back-link">← Back to Hub</a>
    </header>

    <main>
        <!-- Mode selection screen -->
        <div id="mode-select" class="panel">
    <h2>Choose a mode</h2>
    <button id="mode-2player">2 Player (Hotseat)</button>
    <button id="mode-vs-ai">1 Player vs AI</button>

    <h2>Match length</h2>
    <div class="best-of-options">
        <label><input type="radio" name="best_of" value="3" checked> Best of 3</label>
        <label><input type="radio" name="best_of" value="5"> Best of 5</label>
    </div>
</div>

        <!-- Game screen (hidden until mode is chosen) -->
        <div id="game-screen" class="panel hidden">
    <div id="scoreboard">
        <span id="score-x">X: 0</span>
        <span id="round-info">Round 1</span>
        <span id="score-o">O: 0</span>
    </div>

    <p id="status-text">Turn: X</p>

    <div id="board" class="board">
        <?php for ($i = 0; $i < 9; $i++): ?>
            <div class="cell" data-index="<?= $i ?>"></div>
        <?php endfor; ?>
    </div>

    <button id="next-round-btn" class="hidden">Next Round</button>
    <button id="restart-btn">New Match</button>
</div>
        
    </main>
 <script src="../../shared/result-modal.js"></script>
    <script src="script.js"></script>
</body>
</html>