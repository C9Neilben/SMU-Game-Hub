<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Memory Match - SMU Game Hub</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body>

    <header>
        <h1>Memory Match</h1>
        <a href="../../hub/index.php" class="back-link">← Back to Hub</a>
    </header>

    <main>
        <div id="status-bar">
            <span id="moves-text">Moves: 0</span>
        </div>

        <div id="board" class="board">
            <?php for ($i = 0; $i < 16; $i++): ?>
                <div class="card" data-index="<?= $i ?>">
                    <div class="card-inner">
                        <div class="card-back"></div>
                        <div class="card-front">
                            <img src="" alt="">
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <button id="restart-btn">New Game</button>
    </main>

    <script src="script.js"></script>
</body>
</html>